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
function upgrade_module_2_0_1($module)
{
    return Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'recommend_similar_products_clicks`
        ADD `id_category` INT(10) UNSIGNED NOT NULL AFTER `id_product_attribute`,
        ADD `id_source_product` INT(10) UNSIGNED NOT NULL AFTER `id_category`,
        ADD `id_source_category` INT(10) UNSIGNED NOT NULL AFTER `id_source_product`;
    ') && Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'recommend_similar_products_block_views` (
            `id_recommend_similar_products_block_views` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(10) UNSIGNED NOT NULL,
            `id_customer` INT(10) UNSIGNED NOT NULL,
            `date` DATETIME NOT NULL,
            PRIMARY KEY (`id_recommend_similar_products_block_views`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ');
}
