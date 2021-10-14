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
require_once dirname(__FILE__).'/../../classes/RecommendSimilarProductsClick.php';
require_once dirname(__FILE__).'/../../classes/RecommendSimilarProductsView.php';

class RecommendSimilarProductsStatsClicksModuleFrontController extends RecommendSimilarProductsFrontController
{
    public function initContent()
    {
        parent::initContent();

        if (!$this->checkAuthorization()) {
            die("Authorization failed");
        }

        $dateFrom = date('Y-m-d', strtotime('-6 months'));
        $tmpClicks = RecommendSimilarProductsClick::getClicks($dateFrom);
        $tmpViews = RecommendSimilarProductsView::getViews($dateFrom);
        $clicks = $views = array();
        
        foreach ($tmpClicks as $click) {
            if (!isset($clicks[$click['date']])) {
                $clicks[$click['date']] = array();
            }
            
            $clicks[$click['date']][] = array(
                'url' => $this->context->link->getProductLink(
                    (int)$click['id_product'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    (int)$click['id_product_attribute']
                ),
                'id_customer' => $click['id_customer'],
            );
        }

        foreach ($tmpViews as $view) {
            if (!isset($views[$view['date']])) {
                $views[$view['date']] = array();
            }
            
            $views[$view['date']][] = array(
                'url' => $this->context->link->getProductLink(
                    (int)$click['id_product'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    (int)$click['id_product_attribute']
                ),
                'id_customer' => $view['id_customer'],
            );
        }

        die(json_encode(array('clicks' => $clicks, 'views' => $views)));
    }
}
