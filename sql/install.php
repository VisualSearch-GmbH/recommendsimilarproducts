<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 * @author VisualSearch GmbH
 * @copyright VisualSearch GmbH
 * @license MIT License
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'recommend_similar_products` (
    `id_recommend_similar_products` varchar(255) NOT NULL,
    PRIMARY KEY  (`id_recommend_similar_products`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'recommend_similar_products_clicks` (
    `id_recommend_similar_products_clicks` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` INT(10) UNSIGNED NOT NULL,
    `id_product_attribute` INT(10) UNSIGNED NOT NULL,
    `id_source_product` INT(10) UNSIGNED NOT NULL,
    `id_customer` INT(10) UNSIGNED NOT NULL,
    `remote_ip_address` VARCHAR(64) NOT NULL,
    `date` DATETIME NOT NULL,
    PRIMARY KEY (`id_recommend_similar_products_clicks`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'recommend_similar_products_views` (
    `id_recommend_similar_products_views` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` INT(10) UNSIGNED NOT NULL,
    `id_product_attribute` INT(10) UNSIGNED NOT NULL,
    `id_customer` INT(10) UNSIGNED NOT NULL,
    `date` DATETIME NOT NULL,
    PRIMARY KEY (`id_recommend_similar_products_views`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'recommend_similar_products_block_views` (
    `id_recommend_similar_products_block_views` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_product` INT(10) UNSIGNED NOT NULL,
    `id_customer` INT(10) UNSIGNED NOT NULL,
    `date` DATETIME NOT NULL,
    PRIMARY KEY (`id_recommend_similar_products_block_views`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
