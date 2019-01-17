<?php
if (!defined('_PS_VERSION_')) {
    exit;
}


use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
use PrestaShop\PrestaShop\Adapter\BestSales\BestSalesProductSearchProvider;

class Postabproductslider extends Module {
	var $_postErrors  = array();
	private $_html = '';
	public function __construct() {
		$this->name 		= 'postabproductslider';
		$this->tab 			= 'front_office_features';
		$this->version 		= '1.7';
		$this->author 		= 'posthemes';
	    $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        
		parent :: __construct();
		
        $this->displayName 	= $this->l('Product Tabs Slider');
		$this->description 	= $this->l('Product Tabs Slider');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
	}
	
	public function install() {
		Configuration::updateValue('POS_HOME_PRODUCTTAB_SALE', 1);
		Configuration::updateValue('POS_HOME_PRODUCTTAB_NEW', 1);
		Configuration::updateValue('POS_HOME_PRODUCTTAB_FEATURE', 1);
		Configuration::updateValue('POS_HOME_PRODUCTTAB_SELLER', 0);
        Configuration::updateValue('POS_HOME_PRODUCTTAB_RANDOMIZE', true);
        Configuration::updateValue('POS_HOME_PRODUCTTAB_SPEED', 3000);
        Configuration::updateValue('POS_HOME_PRODUCTTAB_NAV', true);
        Configuration::updateValue('POS_HOME_PRODUCTTAB_PAGINATION', false);
        Configuration::updateValue('POS_HOME_PRODUCTTAB_ITEMS', 5);
        Configuration::updateValue('POS_HOME_PRODUCTTAB_ROWS', 1);

		return parent :: install()
			&& $this->registerHook('displayBlockPosition1')
			&& $this->registerHook('header')
			&& $this->registerHook('tabsProducts');
	}

      public function uninstall() {
        $this->_clearCache('productab.tpl');
        return parent::uninstall();
    }

  
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
    
    
    public function hookHeader($params){
    	   $config = $this->getConfigFieldsValues();
    		Media::addJsDef(
	            array(
	                'POS_HOME_PRODUCTTAB_ITEMS' => $config['POS_HOME_PRODUCTTAB_ITEMS'],
	                 'POS_HOME_PRODUCTTAB_PAGINATION' =>$config['POS_HOME_PRODUCTTAB_PAGINATION'],
	                 'POS_HOME_PRODUCTTAB_SPEED' => $config['POS_HOME_PRODUCTTAB_SPEED'],
	                 'POS_HOME_PRODUCTTAB_NAV' => $config['POS_HOME_PRODUCTTAB_NAV']
	             )
        	);
			$this->context->controller->addJS($this->_path.'js/producttab.js');
    }
    
	
	protected function getBestSellers()
    {
        if (Configuration::get('PS_CATALOG_MODE')) {
            return false;
        }

        $searchProvider = new BestSalesProductSearchProvider(
            $this->context->getTranslator()
        );

        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery();

        $nProducts = (int) Configuration::get('PS_BLOCK_BESTSELLERS_TO_DISPLAY');

        $query
            ->setResultsPerPage($nProducts)
            ->setPage(1)
        ;

        $query->setSortOrder(SortOrder::random());

        $result = $searchProvider->runQuery(
            $context,
            $query
        );

        $assembler = new ProductAssembler($this->context);

        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        $products_for_template = [];

        foreach ($result->getProducts() as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }

        return $products_for_template;
    }
	
	
	public function getProducts($params=null, $type = null){
		global $cookie;
		$assembler = new ProductAssembler($this->context);

		$presenterFactory = new ProductPresenterFactory($this->context);
		$presentationSettings = $presenterFactory->getPresentationSettings();
		$presenter = new ProductListingPresenter(
			new ImageRetriever(
				$this->context->link
			),
			$this->context->link,
			new PriceFormatter(),
			new ProductColorsRetriever(),
		    $this->context->getTranslator()
		);
		$nb = 12;
		if($type == 1) {
			//Feature Product
			 $query = new ProductSearchQuery();

			   $query
				->setResultsPerPage($nb)
				->setPage(1)
			;
		
			$query->setSortOrder(new SortOrder('product', 'position', 'asc'));
			
			$context = new ProductSearchContext($this->context);
		   $category = new Category((int) Configuration::get('HOME_FEATURED_CAT'));
		   if(!$category) $category = 2;
			$searchProvider = new CategoryProductSearchProvider(
				$this->context->getTranslator(),
				$category
			);

			$result  = $searchProvider->runQuery(
				$context,
				$query
			);
			
			$products = $result->getProducts();
		
		}else if($type == 2) {
			//New Products
			 $products = Product::getNewProducts((int) $this->context->language->id, 0, $nb);
			 
		}else if($type == 3) {
			//Sale Products
			$products = Product::getPricesDrop((int)$cookie->id_lang, 0, ((int)$nb ? $nb : 20), false);	
		}elseif($type ==4) {
			$products = $this->getBestSellers($params);
			//Bestseller Products
		}	
		
		$products_for_template = [];			
		if(isset($products) && $products) {
			foreach($products as $rawProduct) {
				
					 $products_for_template[] = $presenter->present(
						$presentationSettings,
						$assembler->assembleProduct($rawProduct),
						$this->context->language
					);
			}
		}
		return $products_for_template; 
		
	}
    
    // Hook Home
	public function hookdisplayBlockPosition1($params) {
		
	        $nb = Configuration::get($this->name . '_p_limit');
			$category = new Category(Context::getContext()->shop->getCategory(), (int) Context::getContext()->language->id);
			
			$productTabslider = array();
			if((int) Configuration::get('POS_HOME_PRODUCTTAB_NEW')) {
				$newProducts = $this->getProducts($params,2);
				$productTabslider[] = array('id'=>'new_product', 'name' => $this->l('New Arrival'), 'productInfo' => $newProducts);
			}
			if((int) Configuration::get('POS_HOME_PRODUCTTAB_SALE')) {
				$specialProducts = $this->getProducts($params,3);
				//echo '<pre>'; print_r($specialProducts); die;
				ProductSale::fillProductSales();
				$productTabslider[] = array('id'=> 'special_product','name' => $this->l('OnSale'), 'productInfo' =>  $specialProducts);
			}
			if((int) Configuration::get('POS_HOME_PRODUCTTAB_SELLER')) {
				$bestseller =  $this->getProducts($params,4);
				$productTabslider[] = array('id'=>'besseller_product','name' => $this->l('Bestseller'), 'productInfo' =>  $bestseller);
			}
			if((int) Configuration::get('POS_HOME_PRODUCTTAB_FEATURE')) {
				$featureProduct = $this->getProducts($params,1);
				$productTabslider[] = array('id'=>'feature_product','name' => $this->l('Featured Products'), 'productInfo' =>  $featureProduct);
			}
			$options = $this->getConfigFieldsValues();

            $this->smarty->assign(array(
                'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
				'tab_effect' => Configuration::get($this->name . '_tab_effect'),
	
            ));
			
			if(count($productTabslider) <1) return;
			$this->context->smarty->assign('productTabslider', $productTabslider);
			$this->context->smarty->assign('config', $options);
		return $this->display(__FILE__, 'producttabslider.tpl');
	}
	public function hookDisplayBlockPosition2($params) {
        return $this->hookDisplayBlockPosition1($params);
    }
	
	  public function getContent() {
        $output = '<h2>' . $this->displayName . '</h2>';
        if (Tools::isSubmit('submitPosTabProduct')) {
		if (!sizeof($this->_postErrors)){
			
                $this->_postProcess();
				$output .= $this->displayConfirmation($this->l('Settings updated'));
		}else {
                foreach ($this->_postErrors AS $err) {
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
                }
            }
        }
        return $output . $this->_displayForm();
    }

    public function getSelectOptionsHtml($options = NULL, $name = NULL, $selected = NULL) {
        $html = "";
        $html .='<select name =' . $name . ' style="width:130px">';
        if (count($options) > 0) {
            foreach ($options as $key => $val) {
                if (trim($key) == trim($selected)) {
                    $html .='<option value=' . $key . ' selected="selected">' . $val . '</option>';
                } else {
                    $html .='<option value=' . $key . '>' . $val . '</option>';
                }
            }
        }
        $html .= '</select>';
        return $html;
    }

    private function _postProcess() {

				Configuration::updateValue('POS_HOME_PRODUCTTAB_SALE', Tools::getValue('POS_HOME_PRODUCTTAB_SALE'));
                Configuration::updateValue('POS_HOME_PRODUCTTAB_NEW', Tools::getValue('POS_HOME_PRODUCTTAB_NEW'));
                Configuration::updateValue('POS_HOME_PRODUCTTAB_FEATURE', Tools::getValue('POS_HOME_PRODUCTTAB_FEATURE'));
                Configuration::updateValue('POS_HOME_PRODUCTTAB_SELLER', Tools::getValue('POS_HOME_PRODUCTTAB_SELLER'));
				Configuration::updateValue('POS_HOME_PRODUCTTAB_ROWS', Tools::getValue('POS_HOME_PRODUCTTAB_ROWS'));
				Configuration::updateValue('POS_HOME_PRODUCTTAB_ITEMS', Tools::getValue('POS_HOME_PRODUCTTAB_ITEMS'));
				Configuration::updateValue('POS_HOME_PRODUCTTAB_NAV', Tools::getValue('POS_HOME_PRODUCTTAB_NAV'));
				Configuration::updateValue('POS_HOME_PRODUCTTAB_PAGINATION', Tools::getValue('POS_HOME_PRODUCTTAB_PAGINATION'));
				Configuration::updateValue('POS_HOME_PRODUCTTAB_SPEED', Tools::getValue('POS_HOME_PRODUCTTAB_SPEED'));

   
    }
	
	
	public function _displayForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Show New'),
						'name' => 'POS_HOME_PRODUCTTAB_NEW',
						'desc' => $this->l('Show New product hon Home page.'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show Featured'),
						'name' => 'POS_HOME_PRODUCTTAB_FEATURE',
						'desc' => $this->l('Show Featured product hon Home page.'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show Bestseller'),
						'name' => 'POS_HOME_PRODUCTTAB_SELLER',
						'desc' => $this->l('Show Bestseller product hon Home page.'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show Sale'),
						'name' => 'POS_HOME_PRODUCTTAB_SALE',
						'desc' => $this->l('Show sale product hon Home page.'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						),
					),
					array(
                        'type' => 'text',
                        'label' => $this->l('Items display on slide'),
                        'name' => 'POS_HOME_PRODUCTTAB_ITEMS',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l(''),
                    ),
					array(
                        'type' => 'text',
                        'label' => $this->l('Speed'),
                        'name' => 'POS_HOME_PRODUCTTAB_SPEED',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l(''),
                    ),
					array(
                        'type' => 'text',
                        'label' => $this->l('Rows'),
                        'name' => 'POS_HOME_PRODUCTTAB_ROWS',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('Rows products display on this block'),
                    ),
					 array(
                        'type' => 'switch',
                        'label' => $this->l('Pagination'),
                        'name' => 'POS_HOME_PRODUCTTAB_PAGINATION',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('Show Pagination'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
					 array(
                        'type' => 'switch',
                        'label' => $this->l('Next/Back'),
                        'name' => 'POS_HOME_PRODUCTTAB_NAV',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('Show Next/Back'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitPosTabProduct';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}
	
	
	
	public function getConfigFieldsValues()
    {
        return array(
            'POS_HOME_PRODUCTTAB_NBR' => Tools::getValue('POS_HOME_PRODUCTTAB_NBR', (int) Configuration::get('POS_HOME_PRODUCTTAB_NBR')),
            'POS_HOME_PRODUCTTAB_NEW' => Tools::getValue('POS_HOME_PRODUCTTAB_NEW', (int) Configuration::get('POS_HOME_PRODUCTTAB_NEW')),
            'POS_HOME_PRODUCTTAB_SALE' => Tools::getValue('POS_HOME_PRODUCTTAB_SALE', (bool) Configuration::get('POS_HOME_PRODUCTTAB_SALE')),
            'POS_HOME_PRODUCTTAB_FEATURE' => Tools::getValue('POS_HOME_PRODUCTTAB_FEATURE', (bool) Configuration::get('POS_HOME_PRODUCTTAB_FEATURE')),
            'POS_HOME_PRODUCTTAB_SELLER' => Tools::getValue('POS_HOME_PRODUCTTAB_SELLER', (bool) Configuration::get('POS_HOME_PRODUCTTAB_SELLER')),
            'POS_HOME_PRODUCTTAB_NAV' => Tools::getValue('POS_HOME_PRODUCTTAB_NAV', (bool) Configuration::get('POS_HOME_PRODUCTTAB_NAV')),
            'POS_HOME_PRODUCTTAB_PAGINATION' => Tools::getValue('POS_HOME_PRODUCTTAB_PAGINATION', (bool) Configuration::get('POS_HOME_PRODUCTTAB_PAGINATION')),
            'POS_HOME_PRODUCTTAB_ITEMS' => Tools::getValue('POS_HOME_PRODUCTTAB_ITEMS', (int) Configuration::get('POS_HOME_PRODUCTTAB_ITEMS')),
            'POS_HOME_PRODUCTTAB_SPEED' => Tools::getValue('POS_HOME_PRODUCTTAB_SPEED', (int) Configuration::get('POS_HOME_PRODUCTTAB_SPEED')),
            'POS_HOME_PRODUCTTAB_ROWS' => Tools::getValue('POS_HOME_PRODUCTTAB_ROWS', (int) Configuration::get('POS_HOME_PRODUCTTAB_ROWS')),
        );
    }
	

	private function _installHookCustomer(){
		$hookspos = array(
				'tabsProducts',
			); 
		foreach( $hookspos as $hook ){
			if( Hook::getIdByName($hook) ){
				
			} else {
				$new_hook = new Hook();
				$new_hook->name = pSQL($hook);
				$new_hook->title = pSQL($hook);
				$new_hook->add();
				$id_hook = $new_hook->id;
			}
		}
		return true;
	}
	public static function getBestSales($id_lang, $page_number = 0, $nb_products = 10, $order_by = null, $order_way = null)
	{
		if ($page_number < 0) $page_number = 0;
		if ($nb_products < 1) $nb_products = 10;
		$final_order_by = $order_by;
		$order_table = ''; 		
		if (is_null($order_by) || $order_by == 'position' || $order_by == 'price') $order_by = 'sales';
		if ($order_by == 'date_add' || $order_by == 'date_upd')
			$order_table = 'product_shop'; 				
		if (is_null($order_way) || $order_by == 'sales') $order_way = 'DESC';
		$groups = FrontController::getCurrentCustomerGroups();
		$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
		$interval = Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20;
		
		$prefix = '';
		if ($order_by == 'date_add')
			$prefix = 'p.';
		
		$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity,
					pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`,
					pl.`meta_keywords`, pl.`meta_title`, pl.`name`,
					m.`name` AS manufacturer_name, p.`id_manufacturer` as id_manufacturer,
					MAX(image_shop.`id_image`) id_image, il.`legend`,
					ps.`quantity` AS sales, t.`rate`, pl.`meta_keywords`, pl.`meta_title`, pl.`meta_description`,
					DATEDIFF(p.`date_add`, DATE_SUB(NOW(),
					INTERVAL '.$interval.' DAY)) > 0 AS new
				FROM `'._DB_PREFIX_.'product_sale` ps
				LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
				'.Shop::addSqlAssociation('product', 'p', false).'
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
					ON p.`id_product` = pl.`id_product`
					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
				LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
				LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
				LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (product_shop.`id_tax_rules_group` = tr.`id_tax_rules_group`)
					AND tr.`id_country` = '.(int)Context::getContext()->country->id.'
					AND tr.`id_state` = 0
				LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
				'.Product::sqlStock('p').'
				WHERE product_shop.`active` = 1
					AND product_shop.`visibility` != \'none\'
					AND p.`id_product` IN (
						SELECT cp.`id_product`
						FROM `'._DB_PREFIX_.'category_group` cg
						LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
						WHERE cg.`id_group` '.$sql_groups.'
					)
				GROUP BY product_shop.id_product
				ORDER BY '.(!empty($order_table) ? '`'.pSQL($order_table).'`.' : '').'`'.pSQL($order_by).'` '.pSQL($order_way).'
				LIMIT '.(int)($page_number * $nb_products).', '.(int)$nb_products;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		if ($final_order_by == 'price')
			Tools::orderbyPrice($result, $order_way);
		if (!$result)
			return false;
		return Product::getProductsProperties($id_lang, $result);
	}

}