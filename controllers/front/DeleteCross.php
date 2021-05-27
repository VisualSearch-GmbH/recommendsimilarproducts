<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 * @author VisualSearch GmbH
 * @copyright VisualSearch GmbH
 * @license MIT License
 */

require_once dirname(__FILE__).'/../../classes/RecommendSimilarProductsFrontController.php';

class RecommendSimilarProductsDeleteCrossModuleFrontController extends RecommendSimilarProductsFrontController
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

        $products = Product::getProducts($this->context->language->id, 0, -1, 'id_product', 'ASC');

        foreach ($products as $key => $prod) {
            if (!Validate::isLoadedObject($product = new Product((int)$key))) {
                continue;
            }

            $product->deleteAccessories();
        }

        die("Success");
    }
}
