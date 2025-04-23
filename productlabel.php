<?php

if (!defined('_PS_VERSION_')) exit;

if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PrestaShop\Module\ProductLabel\Form\Modifier\ProductFormModifier;
use PrestaShop\Module\ProductLabel\Install\Installer;
use PrestaShop\Module\ProductLabel\Repository\ProductLabelRepository;
use PrestaShop\Module\ProductLabel\Service\LabelsFrontProvider;

class ProductLabel extends Module
{
    private array $displayedProducts = [];

    public function __construct()
    {
        $this->name = 'productlabel';
        $this->version = '1.0.0';
        $this->author = 'Test Task';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Custom Labels');
        $this->description = $this->l('Add and manage custom labels for products.');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $installer = new Installer();

        return $installer->install($this);
    }

    public function hookDisplayBackOfficeHeader()
    {
        $controller = Tools::getValue('controller');

        if ($controller === "AdminProductLabel") {
            $this->context->controller->addCSS($this->_path . 'views/css/admin/chosen.min.css');
        }
    }

    public function hookDisplayAfterProductThumbs($params)
    {
        if (!Configuration::get('PRODUCTLABEL_ENABLED')) {
            return;
        }

        if (!isset($params['product']['id_product'])) {
            return;
        }

        $productId = $params['product']['id_product'];

        $labels = $this->getLabelsForProduct($productId);

        $moveToTitle = 0;
        if (Configuration::get('PRODUCTLABEL_POSITION') == 'above_title') {
            $moveToTitle = 1;
        }

        $this->context->smarty->assign([
            'labels' => $labels,
            'moveToTitle' => $moveToTitle
        ]);

        return $this->fetch('module:productlabel/views/templates/hook/product-labels.tpl');
    }

    public function hookDisplayProductPriceBlock($params)
    {
        if (!Configuration::get('PRODUCTLABEL_ENABLED')) {
            return;
        }

        if (!isset($params['product']['id_product'])) {
            return;
        }

        $productId = $params['product']['id_product'];

        if (in_array($productId, $this->displayedProducts)) {
            return;
        }

        $labels = $this->getLabelsForProduct($productId);

        $this->context->smarty->assign([
            'labels' => $labels
        ]);

        $this->displayedProducts[] = $productId;


        return $this->fetch('module:productlabel/views/templates/hook/product-labels-plp.tpl');
    }

    protected function getLabelsForProduct(int $productId)
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');

        $labelsProvider = new LabelsFrontProvider($entityManager);

        return $labelsProvider->getLabelsForProduct($productId);
    }


    public function hookActionProductFormBuilderModifier($params)
    {
        /** @var ProductFormModifier $productFormModifier */
        $productFormModifier = $this->get(ProductFormModifier::class);
        $productId = (int) $params['id'];

        $productFormModifier->modify($productId, $params['form_builder']);
    }

    public function hookActionAfterUpdateProductFormHandler($params)
    {
        $productId = $params['id'];
        $labelIds = $params['form_data']['description']['product_labels'] ?? [];

        /** @var ProductLabelRepository $labelRepository */
        $labelRepository = $this->get(ProductLabelRepository::class);

        $conn = $this->get('doctrine.orm.default_entity_manager')->getConnection();

        try {
            $conn->beginTransaction();
            //Create connections with selected labels
            $labelRepository->deleteExistingConnectionsForProduct($productId);
            $labelRepository->addNewConnectionsForProduct($labelIds, $productId);
            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public function hookHeader()
    {
        if (Configuration::get('PRODUCTLABEL_ENABLED')) {
            $this->context->controller->registerStylesheet(
                'module-productlabel-style',
                'modules/' . $this->name . '/views/css/labels.css',
                ['media' => 'all', 'priority' => 150]
            );
        }
    }


    public function getContent()
    {
        if (Tools::isSubmit('submitProductLabelConfig')) {
            Configuration::updateValue('PRODUCTLABEL_ENABLED', (bool) Tools::getValue('PRODUCTLABEL_ENABLED'));
            Configuration::updateValue('PRODUCTLABEL_POSITION', Tools::getValue('PRODUCTLABEL_POSITION'));
        }

        return $this->renderForm();
    }

    private function renderForm(): string
    {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Product Label Settings'),
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable labels'),
                        'name' => 'PRODUCTLABEL_ENABLED',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'enabled_on', 'value' => 1, 'label' => $this->l('Enabled')],
                            ['id' => 'enabled_off', 'value' => 0, 'label' => $this->l('Disabled')],
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->l('Display position'),
                        'name' => 'PRODUCTLABEL_POSITION',
                        'options' => [
                            'query' => [
                                ['id' => 'above_title', 'name' => $this->l('Above title')],
                                ['id' => 'below_image', 'name' => $this->l('Below image')],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductLabelConfig';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = [
            'PRODUCTLABEL_ENABLED' => (bool) Configuration::get('PRODUCTLABEL_ENABLED'),
            'PRODUCTLABEL_POSITION' => Configuration::get('PRODUCTLABEL_POSITION'),
        ];

        return $helper->generateForm([$fieldsForm]);
    }
}
