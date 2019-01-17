<?php

class posrotatorimg extends Module {
	public static $animation = array(
        1 => array('id' =>1 , 'name' => 'Fade'),
        2 => array('id' =>2 , 'name' => 'Slide from left'),
        3 => array('id' =>3 , 'name' => 'Slide from right'),
        4 => array('id' =>4 , 'name' => 'Slide from top'), 
        5 => array('id' =>5 , 'name' => 'Slide from bottom'),     
        6 => array('id' =>6 , 'name' => 'Open from left'),
        7 => array('id' =>7 , 'name' => 'Open from right'),
        8 => array('id' =>8 , 'name' => 'Flip'),
        9 => array('id' =>9 , 'name' => 'Scale'),
        10 => array('id' =>10, 'name' => 'Rollin from left'),
        11 => array('id' =>11 , 'name' => 'Rollin from right'),
    );
	protected static $override = array(
		'controllers/admin/templates/products/images.tpl'
	);
	public function __construct() {
		$this->name 		= 'posrotatorimg';
		$this->tab 			= 'front_office_features';
		$this->version 		= '1.1';
		$this->author 		= 'posthemes';
		$this->displayName 	= $this->l('Rotator img');
		$this->description 	= $this->l('Rotator img');
		$this->bootstrap    = true;
        
		parent :: __construct();
       
	}
	
	public function install() {
	   // Install SQL
		 Configuration::updateValue($this->name . '_animation', 1);
        Configuration::updateValue($this->name . '_time', 500);
		include(dirname(__FILE__).'/sql/install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
		foreach(self::$override as $file)
		{
			$explode = explode("/", $file);
			$file_name = $explode[count($explode)-1];
			unset($explode[count($explode)-1]);
			$folder = implode("/", $explode);
			@mkdir (_PS_OVERRIDE_DIR_.$folder, 0777, true);
			@copy ( _PS_MODULE_DIR_.$this->name.'/override/'.$folder."/".$file_name , _PS_OVERRIDE_DIR_.$folder."/".$file_name );
			$old = @umask(0);
			@chmod (_PS_OVERRIDE_DIR_.$folder."/".$file_name, 0777);
			@umask($old);
		}
		return parent :: install()
			&& $this->registerHook('rotatorImg')
            && $this->registerHook('header')
            ;
	}
	
	public function uninstall(){
		Configuration::deleteByName($this->name . '_animation');
        Configuration::deleteByName($this->name . '_time');
		include(dirname(__FILE__).'/sql/uninstall_sql.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
		return parent::uninstall();
	}

  	private function postProcess() {
		if (Tools::isSubmit('submitposrotatorimg'))
		{
			Configuration::updateValue($this->name.'_animation', Tools::getValue($this->name.'_animation'));
			Configuration::updateValue($this->name.'_time', Tools::getValue($this->name.'_time'));
			return $this->displayConfirmation($this->l('The settings have been updated.'));
		}
		
		return '';
    }
    public function getContent()
	{		
		return $this->postProcess().$this->renderForm();
	}
	public function renderForm()
	{	
		$this->context->controller->addCSS(($this->_path).'views/css/admin.css');
		$this->context->controller->addJS(($this->_path).'views/js/admin.js');
        
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Module Settings'),
				'icon' => 'icon-cogs'
			),
			'input' => array(
				array(
                    'type' => 'select',
                    'label' => $this->l('Animation:'),
                    'name' => 'posrotatorimg_animation',
                    'options' => array(
                        'query' => self::$animation,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Transition time:'),
                    'name' => 'posrotatorimg_time',
                    'class' => 'fixed-width-sm',
                    'suffix' => 'milliseconds',
                    'desc' => '<p>'.$this->l('You can see the animation bellow example').'</p><a href="#" class="button-click" onclick="handle_font_style()">'.$this->l('Click to try').'</a><div id="image-box"><img class="first-image" src="'.__PS_BASE_URI__.'/modules/'.$this->name.'/images/1.jpg" alt=""/><img class="second-image" src="'.__PS_BASE_URI__.'/modules/'.$this->name.'/images/2.jpg" alt=""/></div>',
                ),
			),
			'submit' => array(
				'title' => $this->l('Save'),
			)
		);
			
		
		$helper = new HelperForm();
		$helper->show_toolbar = true;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->module = $this;
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitposrotatorimg';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$module = _PS_MODULE_DIR_ ;
		$helper->tpl_vars = array(
			'module' =>$module,
			'uri' => $this->getPathUri(),
			'fields_value' => $this->getConfigFieldsValues(),
		);

		return $helper->generateForm($fields_form);
	}

	public function getConfigFieldsValues()
	{
		$fields = array(
			'posrotatorimg_animation'   => Configuration::get($this->name.'_animation'),
			'posrotatorimg_time'        => Configuration::get($this->name.'_time'),
		);
		return $fields;
	}
	public function psversion() {
		$version=_PS_VERSION_;
		$exp=$explode=explode(".",$version);
		return $exp[1];
	}
	
	public function hookRotatorImg($params) {
			$idproduct = $params['product']['id_product'];	
			$id_shop = (int)Context::getContext()->shop->id;	
			$link = Context::getContext()->link; 
			$sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'image` img'; 
			$sql .= ' LEFT JOIN `'. _DB_PREFIX_ . 'image_shop` imgs';
			$sql .= ' ON img.id_image = imgs.id_image';
			$sql .= ' where imgs.`id_shop` ='.$id_shop ;
			$sql .= ' AND img.`id_product` ='.$idproduct ;
			$sql .= ' AND imgs.`rotator` =1' ;
			//echo $sql;
			$imageNew = Db::getInstance()->ExecuteS($sql);
			if(!$imageNew) {
				  $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'image` img'; 
				  $sql .= ' where img.`rotator` =1';
				  $sql .= ' AND img.`id_product` ='.$idproduct ;
				  $imageNew = Db::getInstance()->ExecuteS($sql);

			}

			switch(Configuration::get('posrotatorimg_animation')){
				case 1:	$class_name = 'animation1'; break;
				case 2:	$class_name = 'animation2'; break;
				case 3:	$class_name = 'animation3'; break;
				case 4:	$class_name = 'animation4'; break;
				case 5:	$class_name = 'animation5'; break;
				case 6:	$class_name = 'animation6'; break;
				case 7:	$class_name = 'animation7'; break;
				case 8:	$class_name = 'animation8'; break;
				case 9:	$class_name = 'animation9'; break;
				case 10: $class_name = 'animation10'; break;
				case 11: $class_name = 'animation11'; break;								
			}
			if(isset($params['imagesize'])) {
				$this->smarty->assign('imagesize', $params['imagesize']);
			};
			$this->smarty->assign(
				array(
				'rotator_img'=>$imageNew,
				'idproduct'=>$idproduct,
				'product'=>$params['product'],
				'class_name' => $class_name,
				'posrotatorimg_time' => Configuration::get('posrotatorimg_time'),
				'link' => $link,
				));

		return $this->display(__FILE__, 'rotator.tpl');
	}    
	
	public function hookdisplayHeader($params)
	{
		switch(Configuration::get('posrotatorimg_animation')){
				case 1:	$class_name = 'animation1'; break;
				case 2:	$class_name = 'animation2'; break;
				case 3:	$class_name = 'animation3'; break;
				case 4:	$class_name = 'animation4'; break;
				case 5:	$class_name = 'animation5'; break;
				case 6:	$class_name = 'animation6'; break;
				case 7:	$class_name = 'animation7'; break;
				case 8:	$class_name = 'animation8'; break;
				case 9:	$class_name = 'animation9'; break;
				case 10: $class_name = 'animation10'; break;
				case 11: $class_name = 'animation11'; break;								
			}
		$this->context->controller->addCSS($this->_path.'css/posrotatorimg.css', 'all');
		$this->smarty->assign(
				array(
				'class_name' => $class_name,
				'posrotatorimg_time' => Configuration::get('posrotatorimg_time'),
				));

		return $this->display(__FILE__, 'rotator_header.tpl');
	}
           
	
}