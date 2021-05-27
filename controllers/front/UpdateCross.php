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

class RecommendSimilarProductsUpdateCrossModuleFrontController extends RecommendSimilarProductsFrontController
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

        $data = json_decode(Tools::file_get_contents('php://input'), true);
        if (!is_array($data) ||
            !isset($data['products']) ||
            !is_array($data['products'])) {
            return;
        }
        
        foreach ($data['products'] as $productId => $relatedProducts) {
            if (!is_array($relatedProducts) ||
                !count($relatedProducts) ||
                !Validate::isLoadedObject($product = new Product((int)$productId))) {
                continue;
            }
            
            $product->deleteAccessories();
            $product->changeAccessories(array_unique($relatedProducts));
        }

        die("Success");
    }
}
