<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 *
 * @author VisualSearch GmbH
 * @copyright VisualSearch GmbH
 * @license MIT License
 */
require_once dirname(__FILE__) . '/../../config/config.inc.php';

if (!Module::isEnabled('recommendsimilarproducts')) {
    exit('The module is disabled.');
}

/** @var RecommendSimilarProducts $module */
$module = Module::getInstanceByName('recommendsimilarproducts');
$module->processAjaxCall();
