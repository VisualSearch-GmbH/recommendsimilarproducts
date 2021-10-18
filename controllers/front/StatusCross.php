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

class RecommendSimilarProductsStatusCrossModuleFrontController extends RecommendSimilarProductsFrontController
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

        // Find first category with at least one product with empty related products
        $products = Product::getProducts($this->context->language->id, 0, -1, 'id_product', 'ASC');

        $category_ID = -1;
        if (!empty($products)) {
            $category_ID = getFirstCategory($products);
        }

        //echo "<pre>"; print_r($category_ID); die(" exit...");

        // related products exist for every product -> no update needed
        if ($category_ID == -1) {
            die("Number of products: ". sizeof($products) ."; all products have related products");
        } else {
            die("Number of products: ". sizeof($products) ."; missing products in category with ID: ".$category_ID);
        }
    }
}
