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

define('_RSP_PS16_', version_compare(_PS_VERSION_, '1.7', '<'));

require_once dirname(__FILE__) . '/classes/RecommendSimilarProductsClick.php';
require_once dirname(__FILE__) . '/classes/RecommendSimilarProductsView.php';
require_once dirname(__FILE__) . '/classes/RecommendSimilarProductsBlockView.php';
require_once dirname(__FILE__) . '/thirdparty/CrawlerDetect/CrawlerDetect.php';

class RecommendSimilarProducts extends Module
{
    /**
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = 'recommendsimilarproducts';
        $this->tab = 'advertising_marketing';
        $this->version = '2.1.2';
        $this->author = 'VisualSearch';
        $this->need_instance = 0;
        $this->module_key = 'fdcc6270a1d5c04d86dbe2b4cf4406ef';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Recommend Similar Products');
        $this->description = $this->l('Help customers find the products efficiently and increase your conversion rate!');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Install function
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        Configuration::updateValue('RECOMMEND_SIMILAR_PRODUCTS_API_KEY', '');
        Configuration::updateValue('RECOMMEND_SIMILAR_PRODUCTS_LIVE_MODE', false);
        Configuration::updateValue('RECOMMEND_SIMILAR_PRODUCTS_VERSION', $this->version);

        $host = Context::getContext()->shop->getBaseURL(true);

        $key = $this->uuid();
        Db::getInstance()->execute('
            INSERT INTO ' . _DB_PREFIX_ . 'recommend_similar_products (id_recommend_similar_products)
            VALUES (\''. $key .'\')
        ');

        $this->notification($host, $key, 'prestashop;install');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('apiKeyVerify') &&
            $this->registerHook('deleteCross') &&
            $this->registerHook('statusClicks') &&
            $this->registerHook('statusCross') &&
            $this->registerHook('statusVersion') &&
            $this->registerHook('updateCross') &&
            $this->registerHook('updateAuto') &&
            $this->registerHook('updateCategories') &&
            $this->registerHook('updateOneCategory') &&
            (!_RSP_PS16_ || _RSP_PS16_ && $this->registerHook('displayProductPriceBlock'));
    }

    /**
     * Uninstall function
     */
    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        Configuration::deleteByName('RECOMMEND_SIMILAR_PRODUCTS_API_KEY');
        Configuration::deleteByName('RECOMMEND_SIMILAR_PRODUCTS_LIVE_MODE');
        Configuration::deleteByName('RECOMMEND_SIMILAR_PRODUCTS_VERSION');

        $this->deleteRelated();

        $host = Context::getContext()->shop->getBaseURL(true);

        $this->notification($host, '', 'prestashop;uninstall');

        return parent::uninstall();
    }

    /**
     * Send notifaction about installation
     * @param $hosts
     * @param $keys
     * @param $type
     */
    protected function notification($hosts, $key, $type)
    {
        $url = 'https://api.visualsearch.wien/installation_notify';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Vis-API-KEY: marketing',
            'Vis-SYSTEM-HOSTS:'.$hosts,
            'Vis-SYSTEM-KEY:' . $key,
            'Vis-SYSTEM-TYPE: recommend_similar_products;'.$type,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $api_key_verify = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'ApiKeyVerify'
        );

        $delete_cross_link = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'DeleteCross'
        );

        $status_clicks_link = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'StatusClicks'
        );

        $status_cross_link = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'StatusCross'
        );

        $status_version_link = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'StatusVersion'
        );

        $update_cross_link = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'UpdateCross'
        );

        $update_auto_link = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'UpdateAuto'
        );

        $update_categories_link = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'UpdateCategories'
        );

        $update_one_category_link = $this->context->link->getModuleLink(
            'recommendsimilarproducts',
            'UpdateOneCategory'
        );

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitRecommend_similar_productsModule')) == true) {
            $this->postProcess();
        } elseif (Tools::isSubmit('submit_api_credentials_'.$this->name)) {
            $this->processApiCredentialsFormFields();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('api_key_verify', $api_key_verify);
        $this->context->smarty->assign('delete_cross_link', $delete_cross_link);
        $this->context->smarty->assign('status_clicks_link', $status_clicks_link);
        $this->context->smarty->assign('status_cross_link', $status_cross_link);
        $this->context->smarty->assign('status_version_link', $status_version_link);
        $this->context->smarty->assign('update_cross_link', $update_cross_link);
        $this->context->smarty->assign('update_auto_link', $update_auto_link);
        $this->context->smarty->assign('update_categories_link', $update_categories_link);
        $this->context->smarty->assign('update_one_category_link', $update_one_category_link);

        $output = '';

        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                $output .= $this->displayError($error);
            }
        }

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $output .= $this->renderForm();
        $output .= $this->renderApiCredentialsForm();

        return $output;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitRecommend_similar_productsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'RECOMMEND_SIMILAR_PRODUCTS_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'RECOMMEND_SIMILAR_PRODUCTS_LIVE_MODE' => Tools::getValue(
                'RECOMMEND_SIMILAR_PRODUCTS_LIVE_MODE',
                Configuration::get(
                    'RECOMMEND_SIMILAR_PRODUCTS_LIVE_MODE',
                    null,
                    null,
                    $this->context->shop->id
                )
            ),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        foreach (array_keys($this->getConfigFormValues()) as $key) {
            Configuration::updateValue(
                $key,
                Tools::getValue($key),
                false,
                null,
                $this->context->shop->id
            );
        }

        $this->redirectWithConfirmation();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     *
     * @return string
     */
    protected function renderApiCredentialsForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit_api_credentials_'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
            '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getApiCredentialsFormFieldsValue(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getApiCredentialsFormFields()));
    }

    /**
     * Set values for the inputs.
     *
     * @return array
     */
    protected function getApiCredentialsFormFieldsValue()
    {
        return array(
            'RECOMMEND_SIMILAR_PRODUCTS_API_KEY' => Tools::getValue(
                'RECOMMEND_SIMILAR_PRODUCTS_API_KEY',
                Configuration::get(
                    'RECOMMEND_SIMILAR_PRODUCTS_API_KEY',
                    null,
                    null,
                    $this->context->shop->id
                )
            ),
        );
    }

    /**
     * Create the structure of your form.
     *
     * @return array
     */
    protected function getApiCredentialsFormFields()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('API credentials'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('API key'),
                        'name' => 'RECOMMEND_SIMILAR_PRODUCTS_API_KEY',
                        'required' => true,
                        'col' => 3,
                    ),
                    array(
                        'type' => 'html',
                        'name' => 'RECOMMEND_SIMILAR_PRODUCTS_GET_CREDENTIALS_LINK',
                        'html_content' =>
                            '<a href="https://www.visualsearch.at/index.php/credentials/" target="_blank">'.
                                $this->l('Please click here to get your API credentials').
                            '</a>',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Validate API credentials'),
                ),
            ),
        );
    }

    /**
     * Save API credentials.
     */
    protected function processApiCredentialsFormFields()
    {
        if (!$this->validateApiCredentialsFormFields()) {
            return;
        }

        foreach (array_keys($this->getApiCredentialsFormFieldsValue()) as $key) {
            Configuration::updateValue(
                $key,
                Tools::getValue($key),
                false,
                null,
                $this->context->shop->id
            );
        }

        $this->redirectWithConfirmation();
    }

    /**
     * Validate API credentials form fields.
     *
     * @return bool
     */
    protected function validateApiCredentialsFormFields()
    {
        $this->validateApiKey();

        return !count($this->errors);
    }

    /**
     * Validate the API key.
     */
    protected function validateApiKey()
    {
        $apiKey = Tools::getValue('RECOMMEND_SIMILAR_PRODUCTS_API_KEY');

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
                $this->errors[] = $this->l('Failed to validate the API key.');

                return;
            }

            if (isset($data['code']) && ((int)$data['code'] === 200)) {
                return;
            }
        }

        $this->errors[] = $this->l('Invalid API key.');
    }

    /**
     * Redirect with confirmation.
     */
    protected function redirectWithConfirmation()
    {
        Tools::redirectAdmin($this->getModuleSettingsUrl(array(
            'conf' => 6,
            'token' => $this->getToken(),
        )));
    }

    /**
     * Get the module settings URL.
     *
     * @param array $extraParams
     *
     * @return string
     */
    protected function getModuleSettingsUrl(array $extraParams = array())
    {
        $params = array(
            'configure' => $this->name,
            'tab_module' => $this->tab,
            'module_name' => $this->name,
        );

        if ($extraParams) {
            $params = array_merge($params, $extraParams);
        }

        return $this->context->link->getAdminLink('AdminModules', false).'&'.http_build_query($params);
    }

    /**
     * Get a token.
     *
     * @return string|bool
     */
    protected function getToken()
    {
        return Tools::getAdminTokenLite('AdminModules');
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * @return bool
     */
    protected function isBot()
    {
        return (new CrawlerDetect())->isCrawler($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        if (Dispatcher::getInstance()->getController() === 'product') {
            if (Tools::isSubmit('rsp') && !$this->isBot()) {
                $click = new RecommendSimilarProductsClick();
                $click->id_product = (int)Tools::getValue('id_product');
                $click->id_product_attribute = (int)Tools::getValue('id_target_attribute');
                $click->id_source_product = (int)Tools::getValue('id_source_product');
                $click->id_customer = $this->context->customer ? (int)$this->context->customer->id : 0;
                $click->remote_ip_address = $_SERVER['REMOTE_ADDR'];
                $click->date = date('Y-m-d H:i:s');

                if (!$click->save()) {
                    PrestaShopLogger::addLog(
                        'RecommendSimilarProducts::hookHeader - Failed to save a click object',
                        3,
                        null,
                        null,
                        null,
                        true
                    );
                }
            }

            if (_RSP_PS16_) {
                $this->context->controller->addJS($this->_path.'/views/js/front16.js');
            } else {
                /** @var ProductController $controller */
                $controller = $this->context->controller;
                /** @var Product $product */
                $product = $controller->getProduct();

                if (is_array($accessories = $product->getAccessories($this->context->language->id))) {
                    require_once dirname(__FILE__) . '/classes/ProductLazyArray.php';

                    $presentationSettings = (new ProductPresenterFactory(
                        $this->context,
                        new TaxConfiguration()
                    ))->getPresentationSettings();

                    foreach ($accessories as &$accessory) {
                        $accessory = new RecommendSimilarProducts\PrestaShop\ProductLazyArray(
                            $presentationSettings,
                            Product::getProductProperties($this->context->language->id, $accessory, $this->context),
                            $this->context->language,
                            $this->context->link,
                            $this->getTranslator()
                        );
                    }

                    unset($accessory);

                    $this->context->smarty->assign('accessories', $accessories);
                }

                $this->context->controller->addJS($this->_path.'/views/js/front.js');
            }

            $this->context->controller->addCSS($this->_path.'/views/css/front.css');

            Media::addJsDef(array(
                $this->name => array(
                    'id_source_product' => (int)Tools::getValue('id_product'),
                    'ajax_url' => preg_replace('/\/$/', '', $this->context->link->getBaseLink()) .
                        '/modules/' . $this->name . '/' . $this->name . '-ajax.php'
                )
            ));
        }
    }

    /**
     * @param mixed $params
     *
     * @return mixed
     */
    public function hookDisplayProductPriceBlock($params)
    {
        if ((Dispatcher::getInstance()->getController() !== 'product') ||
            !isset($params['type']) ||
            ($params['type'] !== 'after_price') ||
            !isset($params['product']) ||
            !is_array($params['product'])) {
            return;
        }

        ($template = $this->context->smarty->createTemplate(
            $this->local_path . 'views/templates/hook/product-price-block.tpl'
        ))->assign('product', $params['product']);

        return $template->fetch();
    }

    /**
     * Api key verify endpoint
     */
    public function hookApiKeyVerifyRoutes()
    {
        return array(

            'module-'.$this->name.'-api_key_verify' => array(
                'controller' => 'ApiKeyVerify',
                'rule' => 'ApiKeyVerify',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Delete cross-selling endpoint
     */
    public function hookDeleteCrossRoutes()
    {
        return array(

            'module-'.$this->name.'-delete_cross' => array(
                'controller' => 'DeleteCross',
                'rule' => 'DeleteCross',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Status clicks endpoint
     */
    public function hookStatusClicksRoutes()
    {
        return array(

            'module-'.$this->name.'-status_clicks' => array(
                'controller' => 'StatusClicks',
                'rule' => 'StatusClicks',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Status cross-selling endpoint
     */
    public function hookStatusCrossRoutes()
    {
        return array(

            'module-'.$this->name.'-status_cross' => array(
                'controller' => 'StatusCross',
                'rule' => 'StatusCross',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Status version endpoint
     */
    public function hookStatusVersionRoutes()
    {
        return array(

            'module-'.$this->name.'-status_version' => array(
                'controller' => 'StatusVersion',
                'rule' => 'StatusVersion',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Update cross-selling endpoint
     */
    public function hookUpdateCrossRoutes()
    {
        return array(

            'module-'.$this->name.'-update_cross' => array(
                'controller' => 'UpdateCross',
                'rule' => 'UpdateCross',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Update auto endpoint
     */
    public function hookUpdateAutoRoutes()
    {
        return array(

            'module-'.$this->name.'-update_auto' => array(
                'controller' => 'UpdateAuto',
                'rule' => 'UpdateAuto',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Update categories endpoint
     */
    public function hookUpdateCategoriesRoutes()
    {
        return array(

            'module-'.$this->name.'-update_categories' => array(
                'controller' => 'UpdateCategories',
                'rule' => 'UpdateCategories',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Update one category endpoint
     */
    public function hookUpdateOneCategoryRoutes()
    {
        return array(

            'module-'.$this->name.'-update_one_category' => array(
                'controller' => 'UpdateOneCategory',
                'rule' => 'UpdateOneCategory',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    /**
     * Return Uuid identifier
     * @return string Uuid
     */
    private function uuid(): string
    {
        return sprintf(
            '%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Delete related products
     */
    private function deleteRelated()
    {
        $products = Product::getProducts($this->context->language->id, 0, -1, 'id_product', 'ASC');

        foreach ($products as $key => $prod) {
            if (!Validate::isLoadedObject($product = new Product((int)$key))) {
                continue;
            }

            $product->deleteAccessories();
        }
    }

    public function processAjaxCall()
    {
        switch (Tools::getValue('action')) {
            case 'view':
                $view = new RecommendSimilarProductsView();
                $view->id_product = (int)Tools::getValue('id_product');
                $view->id_product_attribute = (int)Tools::getValue('id_product_attribute');
                $view->id_customer = $this->context->customer ? (int)$this->context->customer->id : 0;
                $view->date = date('Y-m-d H:i:s');
                if (!$view->save()) {
                    PrestaShopLogger::addLog(
                        'RecommendSimilarProducts::processAjaxCall - Failed to save a view object',
                        3,
                        null,
                        null,
                        null,
                        true
                    );
                }
                break;

            case 'block_view':
                $blockView = new RecommendSimilarProductsBlockView();
                $blockView->id_product = (int)Tools::getValue('id_product');
                $blockView->id_customer = $this->context->customer ? (int)$this->context->customer->id : 0;
                $blockView->date = date('Y-m-d H:i:s');
                if (!$blockView->save()) {
                    PrestaShopLogger::addLog(
                        'RecommendSimilarProducts::processAjaxCall - Failed to save a block view object',
                        3,
                        null,
                        null,
                        null,
                        true
                    );
                }
                break;

            default:
                exit;
        }
    }
}
