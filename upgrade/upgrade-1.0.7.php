<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 * @author VisualSearch GmbH
 * @copyright VisualSearch GmbH
 * @license MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_1_0_7($module)
{
    return Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'recommend_similar_products_clicks` (
            `id_recommend_similar_products_clicks` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_product_attribute` INT(10) UNSIGNED NOT NULL,
            `id_customer` INT(10) UNSIGNED NOT NULL,
            `date` DATETIME NOT NULL,
            PRIMARY KEY (`id_recommend_similar_products_clicks`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ') && Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'recommend_similar_products_views` (
            `id_recommend_similar_products_views` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_product_attribute` INT(10) UNSIGNED NOT NULL,
            `id_customer` INT(10) UNSIGNED NOT NULL,
            `date` DATETIME NOT NULL,
            PRIMARY KEY (`id_recommend_similar_products_views`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ');
}
