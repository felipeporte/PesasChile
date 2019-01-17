<?php
if (!defined('_PS_VERSION_'))
	exit;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Adapter\Translator;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class PosSpecialProducts extends Module implements WidgetInterface{
	private $token = '';
	private $_html = '';

	public static $sort_by = array(
        1 => array('id' =>1 , 'name' => 'Product Name'),
        2 => array('id' =>2 , 'name' => 'Price'),
        3 => array('id' =>3 , 'name' => 'Product ID'),       
        4 => array('id' =>4 , 'name' => 'Position'),
        5 => array('id' =>5 , 'name' => 'Date updated'),
        6 => array('id' =>6 , 'name' => 'Date added')
    );

    public static $order_by = array(
        1 => array('id' =>1 , 'name' => 'Descending'),
        2 => array('id' =>2 , 'name' => 'Ascending'),
    );
	public function __construct() {
		$this->name 		= 'posspecialproducts';
		$this->tab 			= 'front_office_features';
		$this->version 		= '2.0';
		$this->author 		= 'posthemes';
		$this->bootstrap = true;
		$this->_html        = '';
		$this->displayName 	= $this->l('Pos Special Products module');
		$this->description 	= $this->l('Show special products on homepage.');
		parent :: __construct();
		$this->templateFile = 'module:posspecialproducts/views/templates/hook/posspecialproducts.tpl';
       
	}
	
	public function install() {
        Configuration::updateValue($this->name . '_limit', 20);
        Configuration::updateValue($this->name . '_row', 1);
        Configuration::updateValue($this->name . '_items', 6);
		Configuration::updateValue($this->name . '_speed', 1000);
        Configuration::updateValue($this->name . '_auto', 0);
		Configuration::updateValue($this->name . '_pause', 3000);
        Configuration::updateValue($this->name . '_arrow', 1);
        Configuration::updateValue($this->name . '_pagi', 0);
        Configuration::updateValue($this->name . '_per_lg', 5);
        Configuration::updateValue($this->name . '_per_md', 4);
        Configuration::updateValue($this->name . '_per_sm', 3);
        Configuration::updateValue($this->name . '_per_xs', 2);
        Configuration::updateValue($this->name . '_per_xxs', 1);
        Configuration::updateValue($this->name . '_sort', 7);
        Configuration::updateValue($this->name . '_order', 1);
        
		
		return parent :: install()
			&& $this->registerHook('displayBlockPosition1')
			&& $this->registerHook('displayRightColumnProduct')
			&& $this->registerHook('addproduct')
			&& $this->registerHook('updateproduct')
			&& $this->registerHook('deleteproduct')
			&& $this->registerHook('header')
			&& $this->installFixtures();
	}
	public function hookDisplayHeader()
	{ 
		$this->context->controller->addJS($this->_path.'js/posspecialproducts.js');
	}
	protected function installFixtures()
	{
		$languages = Language::getLanguages(false);
		foreach ($languages as $lang){
			$this->installFixture((int)$lang['id_lang'], 'cms.jpg');
		}

		return true;
	}

	protected function installFixture($id_lang, $image = null)
	{	
		$values['posspecialproducts_img'][(int)$id_lang] = $image;
		$values['posspecialproducts_link'][(int)$id_lang] = '#';
		$values['posspecialproducts_title'][(int)$id_lang] = 'Week Special Products';
		Configuration::updateValue($this->name . '_title', $values['posspecialproducts_title']);
		Configuration::updateValue($this->name . '_img', $values['posspecialproducts_img']);
		Configuration::updateValue($this->name . '_link', $values['posspecialproducts_link']);

	}
	
    public function uninstall() {
        $this->_clearCache('*');

		Configuration::deleteByName($this->name . '_limit');
        Configuration::deleteByName($this->name . '_row');
        Configuration::deleteByName($this->name . '_items');
		Configuration::deleteByName($this->name . '_speed');
        Configuration::deleteByName($this->name . '_auto');
		Configuration::deleteByName($this->name . '_pause');
        Configuration::deleteByName($this->name . '_arrow');
        Configuration::deleteByName($this->name . '_pagi');
        Configuration::deleteByName($this->name . '_per_lg');
        Configuration::deleteByName($this->name . '_per_md');
        Configuration::deleteByName($this->name . '_per_sm');
        Configuration::deleteByName($this->name . '_per_xs');
        Configuration::deleteByName($this->name . '_sort');
        Configuration::deleteByName($this->name . '_order');
        Configuration::deleteByName($this->name . '_img');
        Configuration::deleteByName($this->name . '_link');
		
        return parent::uninstall();
    }

  
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
	
    private function postProcess() {
		if (Tools::isSubmit('submitposspecialproducts'))
		{
			if($this->_postValidation()){
				$languages = Language::getLanguages(false);
				$values = array();
				$update_images_values = false;
		        
				
				foreach ($languages as $lang){
					if (isset($_FILES['poss_img_'.$lang['id_lang']])
					&& isset($_FILES['poss_img_'.$lang['id_lang']]['tmp_name'])
					&& !empty($_FILES['poss_img_'.$lang['id_lang']]['tmp_name']))
					{
						if ($error = ImageManager::validateUpload($_FILES['poss_img_'.$lang['id_lang']], 4000000))
							return $error;
						else
						{
							$ext = substr($_FILES['poss_img_'.$lang['id_lang']]['name'], strrpos($_FILES['poss_img_'.$lang['id_lang']]['name'], '.') + 1);
							$file_name = md5($_FILES['poss_img_'.$lang['id_lang']]['name']).'.'.$ext;

							if (!move_uploaded_file($_FILES['poss_img_'.$lang['id_lang']]['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$file_name))
								return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
							else
							{
								if (Configuration::hasContext('poss_img', $lang['id_lang'], Shop::getContext())
									&& Configuration::get('poss_img', $lang['id_lang']) != $file_name)
									@unlink(dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.Configuration::get('poss_img', $lang['id_lang']));

								$values[$this->name . '_img'][$lang['id_lang']] = $file_name;
								
							}
						}

						$update_images_values = true;
					}
					$values[$this->name . '_link'][$lang['id_lang']] = Tools::getValue('poss_link_'.$lang['id_lang']);
					$values[$this->name . '_title'][$lang['id_lang']] = Tools::getValue('poss_title_'.$lang['id_lang']);
				}
				if ($update_images_values)
				Configuration::updateValue($this->name . '_img', $values[$this->name . '_img']);
				Configuration::updateValue($this->name . '_link', $values[$this->name . '_link']);
				Configuration::updateValue($this->name . '_title', $values[$this->name . '_title']);

				Configuration::updateValue($this->name . '_row', Tools::getValue('poss_row'));
				Configuration::updateValue($this->name . '_items', Tools::getValue('poss_items'));
				Configuration::updateValue($this->name . '_speed', Tools::getValue('poss_speed'));
				Configuration::updateValue($this->name . '_auto', Tools::getValue('poss_auto'));
				Configuration::updateValue($this->name . '_pause', Tools::getValue('poss_pause'));
				Configuration::updateValue($this->name . '_arrow', Tools::getValue('poss_arrow'));
				Configuration::updateValue($this->name . '_pagi', Tools::getValue('poss_pagi'));
				Configuration::updateValue($this->name . '_move', Tools::getValue('poss_move'));
				Configuration::updateValue($this->name . '_pausehover', Tools::getValue('poss_pausehover'));
				Configuration::updateValue($this->name . '_limit', Tools::getValue('poss_limit'));
				Configuration::updateValue($this->name . '_sort', Tools::getValue('poss_sort'));
				Configuration::updateValue($this->name . '_order', Tools::getValue('poss_order'));
				Configuration::updateValue($this->name . '_per_lg', Tools::getValue($this->name . '_per_lg'));
				Configuration::updateValue($this->name . '_per_md', Tools::getValue($this->name . '_per_md'));
				Configuration::updateValue($this->name . '_per_sm', Tools::getValue($this->name . '_per_sm'));
				Configuration::updateValue($this->name . '_per_xs', Tools::getValue($this->name . '_per_xs'));
				Configuration::updateValue($this->name . '_per_xxs', Tools::getValue($this->name . '_per_xxs'));
				
				
				return $this->displayConfirmation($this->l('The settings have been updated.'));
			}else{
				return $this->_html;
			}
		}
		
		return '';
    }
	
	public function getContent()
	{		
		return $this->postProcess().$this->renderForm();
	}

	protected function _postValidation()
	{
		$errors = array();
		if (Tools::isSubmit('submitposspecialproducts'))
		{

			if (!Validate::isInt(Tools::getValue('poss_row')) || !Validate::isInt(Tools::getValue('poss_items')) ||
				!Validate::isInt(Tools::getValue('poss_speed')) || !Validate::isInt(Tools::getValue('poss_pause')) || !Validate::isInt(Tools::getValue('poss_limit'))
			)
				$errors[] = $this->l('Invalid values');
		} 
		/* Returns if validation is ok */
		if (count($errors))
		{
			$this->_html .= $this->displayError(implode('<br />', $errors));

			return false;
		}

		return true;
	}
	public function renderForm()
	{	
		
        $id_lang = (int) Context::getContext()->language->id;
        //echo '<pre>';print_r($test);die;
			$fields_form[0]['form'] = array(
				'legend' => array(
					'title' => $this->l('Module Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
						 array(
							'type' => 'text',
							'lang' => true,
							'label' => $this->l('Module title'),
							'name' => 'poss_title',
							'desc' => $this->l('This title will be displayed on front-office.')
						 ),
				
						 // array(
							// 'type' => 'file_lang',
							// 'label' => $this->l('Banner image'),
							// 'name' => 'poss_img',
							// 'desc' => $this->l('Upload an image for your banner. The recommended dimensions are 770 x 500px.'),
							// 'lang' => true,
						// ),
						array(
							'type' => 'text',
							'lang' => true,
							'label' => $this->l('Banner Link'),
							'name' => 'poss_link',
							'desc' => $this->l('Enter the link associated to your banner. When clicking on the banner, the link opens in the same window.')
						),
						array(
		                    'type' => 'select',
		                    'label' => $this->l('Sort by:'),
		                    'name' => 'poss_sort',
		                    'options' => array(
		                        'query' => self::$sort_by,
		                        'id' => 'id',
		                        'name' => 'name',
		                    ),
		                    'validation' => 'isUnsignedInt',
		                ),
		                array(
		                    'type' => 'select',
		                    'label' => $this->l('Order by:'),
		                    'name' => 'poss_order',
		                    'options' => array(
		                        'query' => self::$order_by,
		                        'id' => 'id',
		                        'name' => 'name',
		                    ),
		                    'validation' => 'isUnsignedInt',
		                ), 
						array(
							'type' => 'text',
							'label' => $this->l('Products limit :'),
							'name' => 'poss_limit',
							'class' => 'fixed-width-sm',
							'desc' => $this->l('Set the number of products which you would like to see displayed in this module')
						),
						
						
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			);
			$fields_form[1]['form'] = array(
				'legend' => array(
					'title' => $this->l('Slider configurations'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
							'type' => 'text',
							'label' => $this->l('Rows'),
							'name' => 'poss_row',
							'class' => 'fixed-width-sm',
							'desc' => $this->l('Number rows of module')
					),
					array(
							'type' => 'text',
							'label' => $this->l('Number of Items:'),
							'name' => 'poss_items',
							'class' => 'fixed-width-sm',
							'desc' => $this->l('Show number of product visible.')
					),
					array(
							'type' => 'text',
							'label' => $this->l('Slide speed:'),
							'name' => 'poss_speed',
							'class' => 'fixed-width-sm',
							'suffix' => 'milliseconds',
							'desc' => $this->l('')
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Auto play'),
						'name' => 'poss_auto',
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Default is 1000ms'),
						'values' => array(
							array(
								'id' => 'poss_auto_on',
								'value' => 1,
								'label' => $this->l('Enabled')
								),
							array(
								'id' => 'poss_auto_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						)
					),
					array(
							'type' => 'text',
							'label' => $this->l('Time auto'),
							'name' => 'poss_pause',
							'class' => 'fixed-width-sm',
							'suffix' => 'milliseconds',
							'desc' => $this->l('This field only is value when auto play function is enable. Default is 3000ms.')
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show Next/Back control:'),
						'name' => 'poss_arrow',
						'class' => 'fixed-width-xs',
						'desc' => $this->l(''),
						'values' => array(
							array(
								'id' => 'poss_arrow_on',
								'value' => 1,
								'label' => $this->l('Enabled')
								),
							array(
								'id' => 'poss_arrow_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						)
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Show pagination control:'),
						'name' => 'poss_pagi',
						'class' => 'fixed-width-xs',
						'desc' => $this->l(''),
						'values' => array(
							array(
								'id' => 'poss_pagi_on',
								'value' => 1,
								'label' => $this->l('Enabled')
								),
							array(
								'id' => 'poss_pagi_off',
								'value' => 0,
								'label' => $this->l('Disabled')
							)
						)
					),
					array(
						'type' => 'radio',
						'label' => $this->l('Scroll number:'),
						'name' => 'poss_move',
	                    'default_value' => 0,
						'values' => array(
							array(
								'id' => 'poss_move_on',
								'value' => 1,
								'label' => $this->l('1 item')),
							array(
								'id' => 'poss_move_off',
								'value' => 0,
								'label' => $this->l('All visible items')),
						),
	                    'validation' => 'isBool',
					),
					 array(
						'type' => 'switch',
						'label' => $this->l('Pause On Hover:'),
						'name' => 'poss_pausehover',
	                    'default_value' => 1,
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'poss_pausehover_on',
								'value' => 1,
								'label' => $this->l('Yes')),
							array(
								'id' => 'poss_pausehover_off',
								'value' => 0,
								'label' => $this->l('No')),
						),
	                    'validation' => 'isBool',
					),
					 'pos_fp_pro' => array(
	                    'type' => 'html',
	                    'id' => 'pos_fp_pro',
	                    'label'=> $this->l('Responsive:'),
	                    'name' => '',
	                ),
					
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)		
			);
		$fields_form[1]['form']['input']['pos_fp_pro']['name'] = $this->BuildDropListGroup($this->findCateProPer());
		
		$helper = new HelperForm();
		$helper->show_toolbar = true;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->module = $this;
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitposspecialproducts';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$module = _PS_MODULE_DIR_ ;
		$helper->tpl_vars = array(
			'module' =>$module,
			'uri' => $this->getPathUri(),
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
		);

		return $helper->generateForm($fields_form);
	}
	
	public function getConfigFieldsValues()
	{
		$languages = Language::getLanguages(false);
		$fields = array(
			'poss_row'        => Configuration::get($this->name . '_row'),
			'poss_items'      => Configuration::get($this->name . '_items'),
			'poss_speed'      => Configuration::get($this->name . '_speed'),
			'poss_auto'       => Configuration::get($this->name . '_auto'),
			'poss_pause'      => Configuration::get($this->name . '_pause'),
			'poss_arrow'      => Configuration::get($this->name . '_arrow'),
			'poss_pagi'       => Configuration::get($this->name . '_pagi'),
			'poss_move'       => Configuration::get($this->name . '_move'),
			'poss_pausehover' => Configuration::get($this->name . '_pausehover'),
			'poss_sort'       => Configuration::get($this->name . '_sort'),
			'poss_order'      => Configuration::get($this->name . '_order'),
			'poss_limit'      => Configuration::get($this->name . '_limit'),

		);
		
		
		foreach ($languages as $lang)
		{	
			$fields['poss_title'][$lang['id_lang']] = Tools::getValue('posspecialproducts_title_'.$lang['id_lang'], Configuration::get($this->name . '_title', $lang['id_lang']));
			$fields['poss_img'][$lang['id_lang']] = Tools::getValue('posspecialproducts_img_'.$lang['id_lang'], Configuration::get($this->name . '_img', $lang['id_lang']));
			$fields['poss_link'][$lang['id_lang']] = Tools::getValue('posspecialproducts_link_'.$lang['id_lang'], Configuration::get($this->name . '_link', $lang['id_lang']));
		}
		
		return $fields;
	}

	public function hookHeader($params){
		$this->context->controller->addCSS(($this->_path).'posspecialproducts.css', 'all');
    }
        
     protected function getProducts()
    {   
        $random = false;
        $sortby = Configuration::get($this->name . '_sort');
        switch($sortby)
        {
            case 1:
            $sortby = 'name';
            break;
            case 2:
            $sortby = 'price';
            break;
            case 3:
            $sortby = 'id_product';
            break;
            case 4:
            $sortby = 'position';
            break;
            case 5:
            $sortby = 'date_upd';
            break;
            case 6:
            $sortby = 'date_add';
            break;
        }
        $orderby = Configuration::get($this->name . '_order');
        if($orderby == 1) {
            $orderby = 'DESC';
        } else {
            $orderby = 'ASC';
        };
        
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

        $nProducts = Configuration::get($this->name . '_limit');

        $products = Product::getPricesDrop((int) Context::getContext()->language->id, 0, ($nProducts ? $nProducts : 8), false,  $sortby , $orderby);

        foreach ($products as $rawProduct) {
            $products_for_template[] = $presenter->present(
                $presentationSettings,
                $assembler->assembleProduct($rawProduct),
                $this->context->language
            );
        }
    
        return $products_for_template;
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('posspecailproducts'))) {
            $variables = $this->getWidgetVariables($hookName, $configuration);

            if (empty($variables)) {
                return false;
            }

            $this->smarty->assign($variables);
        }

        return $this->fetch($this->templateFile, $this->getCacheId('posspecailproducts'));
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $products = $this->getProducts();

        $title = Configuration::get($this->name . '_title', $this->context->language->id);
        $slider_options = array(
            'rows' => (int)Configuration::get($this->name . '_row'),
            'number_item' => (int)Configuration::get($this->name . '_items'),
            'speed_slide' => (int)Configuration::get($this->name . '_speed'),
            'auto_play' => (int)Configuration::get($this->name . '_auto'),
            'auto_time' => (int)Configuration::get($this->name . '_pause'),
            'show_arrow' => (int)Configuration::get($this->name . '_arrow'),
            'show_pagination' => (int)Configuration::get($this->name . '_pagi'),
            'move' => (int)Configuration::get($this->name . '_move'),
            'pausehover' => (int)Configuration::get($this->name . '_pausehover'),
            'items_lg' => (int)Configuration::get($this->name . '_per_lg'), 
            'items_md' => (int)Configuration::get($this->name . '_per_md'), 
            'items_sm' => (int)Configuration::get($this->name . '_per_sm'), 
            'items_xs' => (int)Configuration::get($this->name . '_per_xs'), 
            'items_xxs' => (int)Configuration::get($this->name . '_per_xxs'),       
        );
        $imgname = Configuration::get($this->name . '_img', $this->context->language->id);
        $banner_image= '';
		if ($imgname && file_exists(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.$imgname))
			$banner_image = $this->context->link->protocol_content.Tools::getMediaServer($imgname).$this->_path.'img/'.$imgname;
        if (!empty($products)) {
            return array(
                'products' => $products,
                'title' => $title,
                'slider_options' => $slider_options,
                'image_link' => Configuration::get($this->name . '_link', $this->context->language->id),
                'banner_img' =>  $banner_image
            );
        }
        return false;
    }
	
	public function hookAddProduct($params)
	{
		$this->_clearCache('*');
	}

	public function hookUpdateProduct($params)
	{
		$this->_clearCache('*');
	}

	public function hookDeleteProduct($params)
	{
		$this->_clearCache('*');
	}

	public function BuildDropListGroup($group)
    {
        if(!is_array($group) || !count($group))
            return false;

        $html = '<div class="row">';
        foreach($group AS $key => $k)
        {
             if($key==5)
                 $html .= '</div><div class="row">';

             $html .= '<div class="col-xs-4 col-sm-3">'.$k['label'].'</label>'.
             '<select name="'.$k['id'].'" 
             id="'.$k['id'].'" 
             class="'.(isset($k['class']) ? $k['class'] : 'fixed-width-md').'"'.
             (isset($k['onchange']) ? ' onchange="'.$k['onchange'].'"':'').' >';
            
            for ($i=1; $i < 7; $i++){
                $html .= '<option value="'.$i.'" '.(Configuration::get($k['id']) == $i ? ' selected="selected"':'').'>'.$i.'</option>';
            }
                                
            $html .= '</select></div>';
        }

        return $html.'</div>';
    }
    public function findCateProPer()
    {
        return array(
			array(
                'id' => 'posspecialproducts_per_lg',
                'label' => $this->l('Desktops (>1200 pixels)'),
            ),
            array(
                'id' => 'posspecialproducts_per_md',
                'label' => $this->l('Desktops (>991 pixels)'),
            ),
            array(
                'id' => 'posspecialproducts_per_sm',
                'label' => $this->l('Tablets (>767 pixels)'),
            ),
            array(
                'id' => 'posspecialproducts_per_xs',
                'label' => $this->l('Phones (>480 pixels)'),
            ),
            array(
                'id' => 'posspecialproducts_per_xxs',
                'label' => $this->l('Small phones (>320 pixels)'),
            ),
        );
    }
    protected function getCacheId($name = null)
	{
		if ($name === null)
		$name = 'posspecialproducts';
		return parent::getCacheId($name.'|'.date('Ymd'));
	}

	public function _clearCache($template, $cache_id = null, $compile_id = null)
	{
		parent::_clearCache('posspecialproducts.tpl');
	}
}