<?php
/**
 * (c) VisualSearch GmbH <office@visualsearch.at>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with the source code.
 * @author VisualSearch GmbH
 * @copyright VisualSearch GmbH
 * @license MIT License
 */

require_once 'category.php';
require_once dirname(__FILE__).'/../../classes/RecommendSimilarProductsFrontController.php';

class RecommendSimilarProductsApiKeyVerifyModuleFrontController extends RecommendSimilarProductsFrontController
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

        $apiKey = Configuration::get(
            'RECOMMEND_SIMILAR_PRODUCTS_API_KEY',
            null,
            null,
            $this->context->shop->id
        );

        if (is_string($apiKey) && trim($apiKey)) {
            $handle = curl_init();
            $httpHeader = array(
                'Vis-API-KEY: '.$apiKey,
            );

            curl_setopt($handle, CURLOPT_URL, 'https://api.visualsearch.wien/api_key_verify');
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($handle, CURLOPT_HTTPHEADER, $httpHeader);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($handle);

            curl_close($handle);

            if (($result === false) || !is_array($data = json_decode($result, true))) {
                die(json_encode(['success' => false]));
            }

            if (isset($data['code']) && ((int)$data['code'] === 200)) {
                die(json_encode(['success' => true]));
            }
        }
        die(json_encode(['success' => false]));
    }
}
