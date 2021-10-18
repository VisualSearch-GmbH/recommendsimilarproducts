<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 * @author VisualSearch GmbH
 * @copyright VisualSearch GmbH
 * @license MIT License
 */

function getFirstCategory($products): int
{
    $category_ID = -1;

    foreach ($products as $key => $prod) {
        // Get cover image for your product
        $image = Image::getCover($prod['id_product']);

        // Categories
        $categories = Product::getProductCategoriesFull($prod['id_product']);

        // product must an image and child category(ies). products only with root category are not admitted.
        if ($image['id_image'] > 0 && count($categories) > 1) {
            $last_category = end($categories);
            $category_ID = $last_category['id_category'];

            $related = Db::getInstance()->ExecuteS('
                SELECT *
                FROM ' . _DB_PREFIX_ . 'accessory
                WHERE `id_product_1` = ' . $prod['id_product']);

            if (empty($related)) {
                break;
            } else {
                $category_ID = -1;
            }
        }
    }
    return $category_ID;
}
