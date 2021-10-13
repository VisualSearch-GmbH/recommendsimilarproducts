<?php

require_once dirname(__FILE__) . '/../../config/config.inc.php';

if (!Module::isEnabled('recommendsimilarproducts')) {
    die('The module is disabled.');
}

/** @var RecommendSimilarProducts $module */
$module = Module::getInstanceByName('recommendsimilarproducts');
$module->processAjaxCall();
