<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 * @author VisualSearch GmbH
 * @copyright VisualSearch GmbH
 * @license MIT License
 */

require_once 'category.php';
require_once dirname(__FILE__).'/../../classes/RecommendSimilarProductsFrontController.php';

class RecommendSimilarProductsUpdateCategoriesModuleFrontController extends RecommendSimilarProductsFrontController
{

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();

        if (!$this->checkAuthorization()) {
            die("Authorization failed");
        }

        if (!$this->isLiveMode()) {
            die("Not in live mode");
        }

        //
        // Find first category with at least one product with empty related products
        //
        $products = Product::getProducts($this->context->language->id, 0, -1, 'id_product', 'ASC');

        //
        // Prepare the products for curl request
        //
        $products_list = array();
        if (!empty($products)) {
            foreach ($products as $key => $prod) {
                // Get cover image for your product
                $image = Image::getCover($prod['id_product']);
                // Load Product Object
                $product = new Product($prod['id_product']);
                // Initialize the link object
                $link = new Link;
                // Categories
                $categories = Product::getProductCategoriesFull($prod['id_product']);

                $category_list = array();
                if (!empty($categories)) {
                    foreach ($categories as $cat) {
                        $category_list[] = $cat['name'];
                    }
                }

                $product_ID = $prod['id_product'];
                $product_name = $prod['name'];
                $product_category = $category_list;

                // Only products with valid images
                if ($image['id_image'] > 0) {
                    $image_name = $product->link_rewrite[Context::getContext()->language->id];
                    $product_image = $link->getImageLink(
                        $image_name,
                        $image['id_image'],
                        ImageType::{_RSP_PS16_ ? 'getFormatedName' : 'getFormattedName'}('home')
                    );
                    array_push($products_list, [$product_ID, $product_name, $product_category, '', $product_image]);
                }
            }
        } else {
            die("No products found");
        }

        $data = ["products" => $products_list];
        //echo json_encode($data);

        //
        // Send curl request
        //
        // Create a connection
        $url = 'https://api.visualsearch.wien/similar_compute';
        $ch = curl_init($url);

        // Form data string
        $postString = json_encode($data);
        // $postString = http_build_query($data);

        // hosts
        $systemHosts = Context::getContext()->shop->getBaseURL(true);

        // Setting our options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',
            'Vis-API-KEY:'.$this->getApiKey(),
            'Vis-SYSTEM-HOSTS:'.$systemHosts,
            'Vis-SYSTEM-TYPE:prestashop'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Get the response
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);
        die($response->{'message'});
    }
}
