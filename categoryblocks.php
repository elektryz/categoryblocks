<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Categoryblocks extends Module implements WidgetInterface
{
    private $templateFile;

    public function __construct()
    {
        $this->name = 'categoryblocks';
        $this->version = '1.0.0';
        $this->author = '...';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Category blocks');
        $this->description = $this->l('Select categories to display two blocks with products');

        $this->ps_versions_compliancy = [
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_,
        ];

        $this->templateFile = 'module:'.$this->name.'/views/templates/hook/'.$this->name.'.tpl';
    }

    public function install() : bool
    {
        return parent::install() && $this->registerHook('header');
    }

    public function hookHeader($params)
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'views/css/front.css');
    }

    public function renderWidget($hookName, array $params)
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId($this->name))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $params));
        }

        return $this->fetch($this->templateFile, $this->getCacheId($this->name));
    }

    public function getWidgetVariables($hookName, array $params)
    {
        $firstCategoryObject = new Category(
            (int)Configuration::get('CATEGORYBLOCKS_ID_1'),
            Context::getContext()->language->id
        );

        $secondCategoryObject = new Category(
            (int)Configuration::get('CATEGORYBLOCKS_ID_2'),
            Context::getContext()->language->id
        );

        $hide = false;

        if (Validate::isLoadedObject($firstCategoryObject)) {
            $firstUrl = Context::getContext()->link->getCategoryLink($firstCategoryObject);
        } else {
            $hide = true;
        }

        if (Validate::isLoadedObject($secondCategoryObject)) {
            $secondUrl = Context::getContext()->link->getCategoryLink($secondCategoryObject);
        } else {
            $hide = true;
        }

        // If at least one category is not set - do not display front content
        if ($hide) {
            return [
                'categoryblocks_hide' => true
            ];
        } else {
            return [
                'categoryblocks_first_products' => $this->getProductsFromCategory(
                    (int)Configuration::get('CATEGORYBLOCKS_ID_1')
                ),
                'categoryblocks_first_url' => $firstUrl,
                'categoryblocks_first_name' => $firstCategoryObject->name,
                'categoryblocks_second_products' => $this->getProductsFromCategory(
                    (int)Configuration::get('CATEGORYBLOCKS_ID_2')
                ),
                'categoryblocks_second_url' => $secondUrl,
                'categoryblocks_second_name' => $secondCategoryObject->name,
                'categoryblocks_cart_url' => Context::getContext()->link->getPageLink('cart', true),
                'categoryblocks_token' => Tools::getToken(false),
                'categoryblocks_productbox' => _PS_MODULE_DIR_.$this->name.'/views/templates/includes/productbox.tpl'
            ];
        }
    }

    public function getContent()
    {
        $output = '';

        if (((bool)Tools::isSubmit('submit' . ucfirst($this->name) . 'Module')) == true) {
            $this->postProcess();
            $output .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Notifications.Success'));
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . ucfirst($this->name) . 'Module';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    protected function getConfigForm() : array
    {
        $list = array_merge(
            [['id_category' => 0, 'name' => '---']],
            Category::getCategories(Context::getContext()->language->id, true, false, '', 'ORDER BY cl.`name` ASC')
        );

        foreach ($list as &$item) {
            if ((int)$item['id_category'] > 0) {
                $item['name'] .= ' (ID '.$item['id_category'].')';
            }
        }

        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('First category'),
                        'name' => 'CATEGORYBLOCKS_ID_1',
                        'options' => [
                            'query' => $list,
                            'id' => 'id_category',
                            'name' => 'name',
                        ],
                    ],
                    [
                    'type' => 'select',
                    'label' => $this->l('Second category'),
                    'name' => 'CATEGORYBLOCKS_ID_2',
                    'options' => [
                        'query' => $list,
                        'id' => 'id_category',
                        'name' => 'name',
                    ],
                ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    protected function getConfigFormValues() : array
    {
        return [
            'CATEGORYBLOCKS_ID_1' => Configuration::get('CATEGORYBLOCKS_ID_1'),
            'CATEGORYBLOCKS_ID_2' => Configuration::get('CATEGORYBLOCKS_ID_2'),
        ];
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Generates and returns array with product data
     * @param int $idCategory
     * @return array
     */
    private function getProductsFromCategory(int $idCategory) : array
    {
        if ($idCategory <= 0) {
            return [];
        }

        $categoryProducts = Db::getInstance()->executeS(
            'SELECT * FROM '._DB_PREFIX_.'category_product 
            WHERE id_category='.$idCategory.' 
            ORDER BY `position` ASC'
        );

        if (count($categoryProducts) == 0) {
            return [];
        }

        $productData = [];
        $found = 0;
        $i = 0;

        foreach ($categoryProducts as $categoryProduct) {
            if ($found < 10) {
                $idProduct = (int)$categoryProduct['id_product'];
                $product = new Product(
                    $idProduct,
                    false,
                    Context::getContext()->language->id,
                    Context::getContext()->shop->id
                );

                if (Validate::isLoadedObject($product) && $product->active) {
                    $defaultCombination = $product->getDefaultIdProductAttribute();
                    $quantity = $this->getQuantity($idProduct, $defaultCombination);

                    if ($quantity > 0) {
                        $found++;
                        $image = Image::getCover($product->id);
                        $productData[$i]['id_product'] = $idProduct;
                        $productData[$i]['name'] = $product->name;
                        $productData[$i]['qty'] = $quantity;

                        if ((int)$image['id_image'] > 0) {
                            $productData[$i]['cover'] = Context::getContext()->link->getImageLink(
                                $product->link_rewrite,
                                $image['id_image'],
                                'cart_default'
                            );
                        } else {
                            $productData[$i]['cover'] = rtrim(
                                Context::getContext()->shop->getBaseURL(true, true),
                                "/"
                            ).'modules/'.$this->name.'/views/img/no-image.jpg';
                        }

                        $productData[$i]['url'] = Context::getContext()->link->getProductLink($product);
                        $i++;
                    }
                }
            }
        }

        return $productData;
    }

    /**
     * Returns product quantity based on its default combination - if exists
     * @param int $idProduct
     * @param int $defaultCombination
     * @return int
     */
    public function getQuantity(int $idProduct, int $defaultCombination) : int
    {
        return (int)StockAvailable::getQuantityAvailableByProduct(
            $idProduct,
            $defaultCombination > 0 ? $defaultCombination : null,
            Context::getContext()->shop->id
        );
    }
}
