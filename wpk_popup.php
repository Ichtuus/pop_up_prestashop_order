<?php

if(!defined('_PS_VERSION_')){
    exit;
}

class Wpk_Popup extends Module{
    public function __construct(){
        $this->name = 'wpk_popup';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Wepika';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Pop up my client');
        $this->description = $this->l('Display pop up when orders come');
        $this->confirmUninstall = $this->l('Do you really want desinstall this module ?');
    }


    /*
    * START INSTALLATION AND DESINSTALLATION
    */
    public function install(){
        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayHome') &&
            Configuration::updateValue('wpk_popup_conf', 1);
    }

    public function uninstall(){
        return parent::uninstall() &&
        Configuration::deleteByName('wpk_popup_conf');
    }
    /*
    * END INSTALLATION AND DESINSTALLATION
    */

    /*
    * START METHODE IN LINK WITH HOOK
    */
    public function hookDisplayHeader(){
        $smarty = new Smarty();

        $this->context->controller->addCSS($this->_path.'views/css/wpk_popup.css');
        $this->context->controller->addJS($this->_path.'views/js/wpk_popup_bootstrap.js');
        $this->context->controller->addJS($this->_path.'views/js/wpk_popup.js');

        $query =
        '
            SELECT
                o.id_order, o.id_customer, o.date_add , od.product_name,
                od.product_price, c.id_customer, c.firstname, c.lastname, a.id_customer, a.city,
                i.id_image, p.id_product
            FROM
                `'._DB_PREFIX_.'orders` o
            INNER JOIN
                `'._DB_PREFIX_.'order_detail` od
            ON
                o.id_order = od.id_order
            INNER JOIN
                `'._DB_PREFIX_.'customer` c
            ON
                o.id_customer = c.id_customer
            INNER JOIN
                `'._DB_PREFIX_.'address` a
            ON
                c.id_customer = a.id_customer
            INNER JOIN
                `'._DB_PREFIX_.'product` p
            ON
                od.product_id = p.id_product
            INNER JOIN
                `'._DB_PREFIX_.'image` i
            ON
                p.id_product = i.id_product
            WHERE
                o.date_add > DATE_SUB(CURDATE(), INTERVAL '.Configuration::get('wpk_popup_conf').' HOUR)
        ';
        // $query =
        // '
        //     SELECT
        //         o.id_order, o.date_add
        //     FROM
        //         `'._DB_PREFIX_.'orders` o
        //     WHERE
        //         o.date_add > DATE_SUB(CURDATE(), INTERVAL '.Configuration::get('wpk_popup_conf').' HOUR)
        // ';
        $orders = Db::getInstance()->ExecuteS($query);

        // $arrayOrders = [];
        // foreach ($orders as $v) {
        //     array_push($arrayOrders, $v['id_order']);
        // }
        // echo '<pre>';
        // print_r(array_rand($arrayOrders));
        // die();
        // $order = new Order(array_rand($arrayOrders));
        // $product = new Product($v['id_product'], false, Context::getContext()->language->id);
        // $link = new Link;
        // $imagePath = $link->getImageLink($product->link_rewrite, $v['id_image']);
        //
        // $products = $order->getProductsDetail();

        // echo '<pre>';
        // print_r($products);
        // die();
        // if(empty($order)){
        //     return false;
        // }


            // foreach ($products as $product){
            //     echo '<pre>';
            //     print_r($product);
            //     die();
            //
            // }

        $randOrders = $orders[array_rand($orders)];
        if(!empty($randOrders)){
            $this->context->smarty->assign(array(
                'randOrders' => $randOrders
                // 'products' => $products
            ));

        return $this->display(__FILE__, 'wpk_popup.tpl');
        }
        return $this->display(__FILE__, 'wpk_popup.tpl');
    }

    /*
    * END METHODE IN LINK WITH HOOK
    */

    /*
    * START CONFIG FORM IN FRONT DASHBOARD FOR MANAGE PRODUCT AND CARRIER
    */
    public function getContent(){
        $output = null;
        if(Tools::isSubmit('submit' . $this->name)){
            $wpk_popup_conf = Tools::getValue('wpk_popup_conf');
            if(!$wpk_popup_conf || empty($wpk_popup_conf) || !Validate::isGenericName($wpk_popup_conf)){
                $output .= $this->displayError($this->l('Configuration failed'));
            }
            else{
                Configuration::updateValue('wpk_popup_conf', $wpk_popup_conf);
                $output .= $this->displayConfirmation($this->l('Update successful'));
            }
        }
        return $output . $this->displayForm();
    }

    public function displayForm(){

        $fields_form = array();
            $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
            $fields_form[0]['form'] = array(
                'legend' => array(
                    'title' => $this->l('interval'),
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('hours'),
                        'name' => 'wpk_popup_conf',
                        'options' => array(
                            'query' => array(
                                array('id' => 24, 'name' => '24h'),
                                array('id' => 48, 'name' => '48h'),
                                array('id' => 72, 'name' => '72h')
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                        'required' => true
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default'
                )
            );

            $helper = new HelperForm();
            $helper->module = $this;
            $helper->name_controller = $this->name;
            $helper->token = Tools::getAdminTokenLite('AdminModules');
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->default_form_language = $default_lang;
            $helper->allow_employee_form_lang = $default_lang;
            $helper->title = $this->displayName;
            $helper->show_toolbar = true;
            $helper->toolbar_scroll = true;
            $helper->submit_action = 'submit' . $this->name;
            $helper->toolbar_btn = array(
               'save' =>
                   array(
                       'desc' => $this->l('Save'),
                       'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                           '&token=' . Tools::getAdminTokenLite('AdminModules'),
                   ),
               'back' => array(
                   'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                   'desc' => $this->l('Back to list')
               )
            );
            $helper->tpl_vars = array(
                'fields_value' => $this->getConfigFieldsValues()
            );

            return $helper->generateForm($fields_form);
    }
    /*
    * END CONFIG FORM IN FRONT DASHBOARD FOR MANAGE PRODUCT AND CARRIER
    */

    public function getConfigFieldsValues(){
        return array(
            'wpk_popup_conf' => Configuration::get('wpk_popup_conf')
        );
    }
}
