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
require_once dirname(__FILE__).'/../../classes/RecommendSimilarProductsBlockView.php';

class RecommendSimilarProductsStatusClicksModuleFrontController extends RecommendSimilarProductsFrontController
{
    public function initContent()
    {
        parent::initContent();

        if (!$this->checkAuthorization()) {
            die("Authorization failed");
        }

        $dateFrom = date('Y-m-d H:i:s', strtotime('-1 day'));
        $tmpClicks = RecommendSimilarProductsClick::getClicks($dateFrom, true);
        $tmpViews = RecommendSimilarProductsView::getViews($dateFrom, true);
        $tmpBlockViews = RecommendSimilarProductsBlockView::getBlockViews($dateFrom, true);
        $clicks = $views = $blockViews = array();

        foreach ($tmpClicks as $click) {
            if (!isset($clicks[$click['date']])) {
                $clicks[$click['date']] = array();
            }

            $clicks[$click['date']][] = array(
                'id_target' => $click['id_product'],
                'id_category_target' => $click['id_category'],
                'id_source' => $click['id_source_product'],
                'id_category_source' => $click['id_source_category'],
                'id_customer' => $click['id_customer'],
            );
        }

        foreach ($tmpViews as $view) {
            if (!isset($views[$view['date']])) {
                $views[$view['date']] = array();
            }

            $views[$view['date']][] = array(
                'id_target' => $view['id_product'],
                'id_customer' => $view['id_customer'],
            );
        }

        foreach ($tmpBlockViews as $blockView) {
            if (!isset($blockViews[$blockView['date']])) {
                $blockViews[$blockView['date']] = array();
            }

            $blockViews[$blockView['date']][] = array(
                'id_source' => $blockView['id_product'],
                'id_customer' => $blockView['id_customer'],
            );
        }

        die(json_encode(array(
            'clicks' => $clicks,
            'views_recommended_products' => $views,
            'views_recommendation_slider' => $blockViews,
        )));
    }
}
