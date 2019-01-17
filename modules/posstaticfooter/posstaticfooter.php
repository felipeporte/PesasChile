<?php

// Security
if (!defined('_PS_VERSION_'))
    exit;

// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
    define('_MYSQL_ENGINE_', 'MyISAM');

// Loading Models
require_once(_PS_MODULE_DIR_ . 'posstaticfooter/models/Staticfooter.php');
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
class posstaticfooter extends Module {
    public  $hookAssign   = array();
    public $_staticModel =  "";
    public function __construct() {
        $this->name = 'posstaticfooter';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'posthemes';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7');
        $this->hookAssign = array('footer','displayFooter');
        $this->_staticModel = new Staticfooter();
        parent::__construct();

        $this->displayName = $this->l('Pos Static Footer');
        $this->description = $this->l('Manager Static blocks');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->admin_tpl_path = _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/';
        
    }

    public function install() {

        // Install SQL
        include(dirname(__FILE__) . '/sql/install.php');
        foreach ($sql as $s)
            if (!Db::getInstance()->execute($s))
                return false;
		$tab = new Tab();
		$tab->active = 1;
        // Need a foreach for the language
		$tab->name = array();
		$tab->class_name = 'AdminPosStaticFooter';
		foreach (Language::getLanguages() as $language) {
				$tab->name[$language['id_lang']] = $this->l('Manage Staticblocks');
		}
		$tab->module = $this->name;
        $tab->add();
        // Set some defaults
        return parent::install() &&
                $this->registerHook('footer') &&
		$this->_installHookCustomer()&&
		$this->registerHook('displayBlockFooter1')&&
		$this->registerHook('displayBlockFooter2')&&
		$this->registerHook('displayBlockFooter3')&&
		$this->registerHook('displayBlockFooter4')&&
		$this->registerHook('displayFooterBefore')&&
		$this->registerHook('displayFooterAfter')&&
		$this->registerHook('displayBlockFooterExtra')&&
        $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall() {

        Configuration::deleteByName('POSSTATICFOOTER');

        // Uninstall Tabs
        //$tab = new Tab((int) Tab::getIdFromClassName('AdminPosstaticblocksMain'));
        //$tab->delete();
        $sql = array();
        include (dirname(__file__) . '/sql/uninstall_sql.php');
        foreach ($sql as $s) {
            if (!Db::getInstance()->Execute($s)) {
                return FALSE;
            }
        }
        // Uninstall Module
        if (!parent::uninstall())
            return false;
        return true;
    }
    public function getContent()
    {	
	    $tab = new Tab((int) Tab::getIdFromClassName('AdminPosStaticFooter'));
        $tab->delete();
        $tab = new Tab();
		$tab->active = 1;
        // Need a foreach for the language
		$tab->name = array();
		$tab->class_name = 'AdminPosStaticFooter';
		foreach (Language::getLanguages() as $language) {
				$tab->name[$language['id_lang']] = $this->l('Manage Static Footer');
		}
        $tab->module = $this->name;
        $tab->add();
		
		$url  = 'index.php?controller=AdminPosStaticFooter';
		$url .= '&token='.Tools::getAdminTokenLite('AdminPosStaticFooter');
		Tools::redirectAdmin($url);
	} 
    public function hookFooter($param) { 
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'footer');
        if(count($staticBlocks)<1) return null;
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    
    public function hookDisplayFooter($param) { 
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayFooter');
        if(count($staticBlocks)<1) return null;
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
	
	
     public function hookDisplayBackOfficeHeader($params) {
		if (method_exists($this->context->controller, 'addJquery'))
		 {        
		  $this->context->controller->addJquery();
		 
		  if(Tools::getValue('controller')=='AdminPosStaticFooter'){
			$this->context->controller->addJS(($this->_path).'js/staticblock.js');
		  }
		  
		 }
    }	
    /* define some hook customer */
	public function hookdisplayBlockFooter1($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayBlockFooter1');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    
	public function hookdisplayBlockFooter2($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayBlockFooter2');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    
	public function hookdisplayBlockFooter3($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayBlockFooter3');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
	
	public function hookdisplayBlockFooter4($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayBlockFooter4');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
	
	public function hookDisplayFooterBefore($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayFooterBefore');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
	
	public function hookDisplayFooterAfter($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayFooterAfter');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
	
	public function hookdisplayBlockFooterExtra($param) {
        $id_shop = (int)Context::getContext()->shop->id;
        $staticBlocks = $this->_staticModel->getStaticfooterLists($id_shop,'displayBlockFooterExtra');
        if(count($staticBlocks)<1) return null;
        //if(is_array($staticBlocks))
        $this->smarty->assign(array(
            'staticblocks' => $staticBlocks,
        ));
       return $this->display(__FILE__, 'block_footer.tpl');
    }
    
    
    public function getModulById($id_module) {
        return Db::getInstance()->getRow('
            SELECT m.*
            FROM `' . _DB_PREFIX_ . 'module` m
            JOIN `' . _DB_PREFIX_ . 'module_shop` ms ON (m.`id_module` = ms.`id_module` AND ms.`id_shop` = ' . (int) ($this->context->shop->id) . ')
            WHERE m.`id_module` = ' . $id_module);
    }
   public function getHooksByModuleId($id_module) {
		$id_shop = (int)Context::getContext()->shop->id;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'hook_module AS `ps` LEFT JOIN '._DB_PREFIX_.'hook AS `ph` ON `ps`.`id_hook` = `ph`.`id_hook`  WHERE `ps`.`id_module`='.$id_module .' AND `ps`.`id_shop` = '.$id_shop;
		$hooks = array();
	     if($object = Db::getInstance()->ExecuteS($sql)){
			 if(count($object)>0) {
				 foreach($object as $module_hook) {
					if(isset($module_hook['name']))
						$hooks[] = $module_hook['name'];
				 }
			 }
			
		 }
		 return $hooks; 
   }
    public function getHooksByModuleId1($id_module) { 
        $module = self::getModulById($id_module);
        $moduleInstance = Module::getInstanceByName($module['name']);
		
        $hooks = array();
        if ($this->hookAssign)
            foreach ($this->hookAssign as $hook) { 
                    $retro_hook_name = Hook::getRetroHookName($hook);
                    if (is_callable(array($moduleInstance, 'hook' . $hook)) || is_callable(array($moduleInstance, 'hook' . $retro_hook_name))) {
                        $hooks[] = $retro_hook_name;
                    } else {
						  if ($moduleInstance instanceof WidgetInterface) { 
						    $hooks[] = $retro_hook_name;
						  }
					}
            }
			
        $results = self::getHookByArrName($hooks);
        return $results;
    }
	public function getPosCurrentHook($name_module) {
		$id_shop = (int)Context::getContext()->shop->id;
		$sql = 'SELECT psb.`hook_module` FROM '._DB_PREFIX_.'pos_staticfooter AS psb LEFT JOIN '._DB_PREFIX_.'pos_staticfooter_shop AS pss ON psb.`id_posstaticblock`= pss.`id_posstaticblock` WHERE  psb.`name_module` ="'.$name_module.'" AND pss.`id_shop` = "'.$id_shop.'"';
		return Db::getInstance()->getRow($sql);
   }

    public static function getHookByArrName($arrName) {
        $result = Db::getInstance()->ExecuteS('
		SELECT `id_hook`, `name`
		FROM `' . _DB_PREFIX_ . 'hook` 
		WHERE `name` IN (\'' . implode("','", $arrName) . '\')');
        return $result;
    }
  //$hooks = $this->getHooksByModuleId(10);
    public function getListModuleInstalled() {
        $mod = new posstaticfooter();
        $modules = $mod->getModulesInstalled(0);
        $arrayModule = array();
        foreach($modules as $key => $module) {
            if($module['active']==1) {
                $arrayModule[0] = array('id_module'=>0, 'name'=>'Chose Module');
                $arrayModule[$key] = $module;
            }
        }
        if ($arrayModule)
            return $arrayModule;
        return array();
    }
	
	private function _installHookCustomer(){
		$hookspos = array(
				'displayBlockFooter1',
				'displayBlockFooter2',
				'displayBlockFooter3',
				'displayBlockFooter4',
				'displayFooterBefore',
				'displayFooterAfter',
				'displayBlockFooterExtra',
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


}