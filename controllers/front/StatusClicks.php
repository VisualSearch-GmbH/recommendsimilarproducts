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
        $clicks = $views = $blockViews = $products = array();

        foreach ($tmpClicks as $click) {
            if (!isset($clicks[$click['date']])) {
                $clicks[$click['date']] = array();
            }

            if ($click['id_source_product'] > 0) {
                $clicks[$click['date']][] = array(
                    'id_target' => $click['id_product'],
                    'id_source' => $click['id_source_product'],
                    'id_customer' => $click['id_customer'],
                );
                $products[$click['id_product']] = true;
            }
        }

        foreach ($tmpViews as $view) {
            if (!isset($views[$view['date']])) {
                $views[$view['date']] = array();
            }

            $views[$view['date']][] = array(
                'id_target' => $view['id_product'],
                'id_customer' => $view['id_customer'],
            );

            $products[$view['id_product']] = true;
        }

        foreach ($tmpBlockViews as $blockView) {
            if (!isset($blockViews[$blockView['date']])) {
                $blockViews[$blockView['date']] = array();
            }

            $blockViews[$blockView['date']][] = array(
                'id_source' => $blockView['id_product'],
                'id_customer' => $blockView['id_customer'],
            );

            $products[$blockView['id_product']] = true;
        }

        $products_list = array();

        if (!empty($products)) {
            foreach ($products as $product_ID => $value) {
                // Categories
                $categories = Product::getProductCategoriesFull($product_ID);

                $category_list = array();
                if (!empty($categories)) {
                    foreach ($categories as $category_ID => $cat) {
                        if ((int)$category_ID > 2) {
                            $category_list[] = $cat['name'];
                        }
                    }
                }

                array_push($products_list, [$product_ID, $category_list]);
            }
        } else {
            die(json_encode(array(
                'clicks' => $clicks,
                'views_recommended_products' => $views,
                'views_recommendation_slider' => $blockViews,
            )));
        }

        die(json_encode(array(
            'clicks' => $clicks,
            'views_recommended_products' => $views,
            'views_recommendation_slider' => $blockViews,
            'product_categories' => $products_list,
        )));
    }
}
