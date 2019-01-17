<?php

// Security
if (!defined('_PS_VERSION_'))
    exit;

// Checking compatibility with older PrestaShop and fixing it
if (!defined('_MYSQL_ENGINE_'))
    define('_MYSQL_ENGINE_', 'MyISAM');
	
require (dirname(__FILE__).'/megatoplinks.class.php');
// Loading Models
class Posmegamenu extends Module {

    private $_html = '';
    private $_postErrors = array();
    private $_show_level = 1;
	private $pattern = '/^([A-Z_]*)[0-9]+/';
	private $_menuLink = '';
	private $spacer_size = '5';
	private $page_name = '';

    public function __construct() {
        $this->name = 'posmegamenu';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'posthemes';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7');
        $this->_show_level = Configuration::get($this->name . '_show_depth');
        parent::__construct();

        $this->displayName = $this->l('Pos Megamenu Customer');
        $this->description = $this->l('block config');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->admin_tpl_path = _PS_MODULE_DIR_ . $this->name . '/views/templates/admin/';
       
    }

    public function install() {
        Configuration::updateGlobalValue('MOD_BLOCKPOSMENU_ITEMS', 'CAT3,CAT4,CAT5');
        Configuration::updateValue($this->name . '_show_homepage', 1);
        Configuration::updateValue($this->name . '_menu_depth', 4);
        Configuration::updateValue($this->name . '_merge_cate', 1);
        Configuration::updateValue($this->name . '_show_depth', 4);
        Configuration::updateValue($this->name . '_top_offset', 55);
        Configuration::updateValue($this->name . '_effect', 0);
		$this->installDb();
        return parent::install() &&
                $this->registerHook('displayHeader')
                &&
                $this->registerHook('displayMegamenu');
    }

    public function uninstall() {
        Configuration::deleteByName('POSMEGAMENU');
        Configuration::updateGlobalValue('MOD_BLOCKPOSMENU_ITEMS', 'CAT1');
		$this->uninstallDb();
        // Uninstall Module
        if (!parent::uninstall())
            return false;
        // !$this->unregisterHook('actionObjectExampleDataAddAfter')
        return true;
    }

    public function getContent() {
        $this->_html .= '<h2>' . $this->displayName . '</h2>';
		
		$id_lang = (int)Context::getContext()->language->id;
		$languages = $this->context->controller->getLanguages();
		$default_language = (int)Configuration::get('PS_LANG_DEFAULT');
		$labels = Tools::getValue('label') ? array_filter(Tools::getValue('label'), 'strlen') : array();
		$links_label = Tools::getValue('link') ? array_filter(Tools::getValue('link'), 'strlen') : array();
		$divLangName = 'link_label';
        if (Tools::isSubmit('submitPosMegamenu')) {
            //$this->_postValidation();

            if (!sizeof($this->_postErrors))
                $this->_postProcess();
            else {
                foreach ($this->_postErrors AS $err) {
                    $this->_html .= '<div class="alert error">' . $err . '</div>';
                }
            }
        } else if (Tools::isSubmit('submitBlockposmenu')) {
		
			if (Configuration::updateValue('MOD_BLOCKPOSMENU_ITEMS', Tools::getValue('items')))
				$this->_html .= $this->displayConfirmation($this->l('The settings have been updated.'));
				
		}else if (Tools::isSubmit('submitBlocktopmegaLinks')){

			if ((!count($links_label)) && (!count($labels)));
			else if (!count($links_label))
				$this->_html .= $this->displayError($this->l('Please complete the "link" field.'));
			else if (!count($labels))
				$this->_html .= $this->displayError($this->l('Please add a label'));
			else if (!isset($labels[$default_language]))
				$this->_html .= $this->displayError($this->l('Please add a label for your default language.'));
			else
			{
				MegaTopLinks::add(Tools::getValue('link'), Tools::getValue('label'), Tools::getValue('new_window', 0), (int)Shop::getContextShopID());
				$this->_html .= $this->displayConfirmation($this->l('The link has been added.'));
			}
		}
		else if (Tools::isSubmit('submitBlocktopmegaRemove'))
		{
			$id_linksmegatop = Tools::getValue('id_linksmegatop', 0);
			MegaTopLinks::remove($id_linksmegatop, (int)Shop::getContextShopID());
			Configuration::updateValue('MOD_BLOCKPOSMENU_ITEMS', str_replace(array('LNK'.$id_linksmegatop.',', 'LNK'.$id_linksmegatop), '', Configuration::get('MOD_BLOCKPOSMENU_ITEMS')));
			$this->_html .= $this->displayConfirmation($this->l('The link has been removed'));
			$update_cache = true;
		}
		
        $this->_displayForm();

        return $this->_html;
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

        Configuration::updateValue($this->name . '_show_homepage', Tools::getValue('show_homepage'));
        Configuration::updateValue($this->name . '_menu_depth', Tools::getValue('menu_depth'));
	if(Tools::getValue('list_cate')) {
	    Configuration::updateValue($this->name . '_list_cate', implode(',', Tools::getValue('list_cate')));
	}
        Configuration::updateValue($this->name . '_merge_cate', Tools::getValue('merge_cate'));
        Configuration::updateValue($this->name . '_show_depth', Tools::getValue('show_depth'));
        Configuration::updateValue($this->name . '_top_offset', Tools::getValue('top_offset'));
        Configuration::updateValue($this->name . '_effect', Tools::getValue('effect'));

        $this->_html .= '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';
    }

    private function _displayForm() {
		$id_lang = (int)Context::getContext()->language->id;
		$languages = $this->context->controller->getLanguages();
		$default_language = (int)Configuration::get('PS_LANG_DEFAULT');
		$spacer = str_repeat('&nbsp;', $this->spacer_size);
		$divLangName = 'link_label';
		
		if (Tools::isSubmit('submitBlocktopmegaEdit'))
		{
			$id_linksmegatop = (int)Tools::getValue('id_linksmegatop', 0);
			
			$id_shop = (int)Shop::getContextShopID();

			if (!Tools::isSubmit('link'))
			{ 
				$tmp = MegaTopLinks::getLinkLang($id_linksmegatop, $id_shop);
				$links_label_edit = $tmp['link'];
				$labels_edit = $tmp['label'];
				$new_window_edit = $tmp['new_window'];
			}
			else
			{ 
				MegaTopLinks::update(Tools::getValue('link'), Tools::getValue('label'), Tools::getValue('new_window', 0), (int)$id_shop, (int)$id_linksmegatop, (int)$id_linksmegatop);
				$this->_html .= $this->displayConfirmation($this->l('The link has been edited'));
			}
		
		}
        $this->_html .= '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                  <fieldset>
                    <legend><img src="../img/admin/edit.gif" alt="" class="middle" />' . $this->l('Settings') . '</legend>
                     <label>' . $this->l('Show Homepage: ') . '</label>
                    <div class="margin-form">';
        $this->_html .= $this->getSelectOptionsHtml(array(0 => 'No', 1 => 'Yes'), 'show_homepage', (Tools::getValue('show_homepage') ? Tools::getValue('show_homepage') : Configuration::get($this->name . '_show_homepage')));

        $this->_html .='
                    </div>
                    <label>' . $this->l('Show number columns: ') . '</label>
                    <div class="margin-form">
                            <input type = "text"  name="menu_depth" value =' . (Tools::getValue('menu_depth') ? Tools::getValue('menu_depth') : Configuration::get($this->name . '_menu_depth')) . ' ></input>
                    </div>';
        $this->_html .= '<label>' . $this->l('Show Levels: ') . '</label>
                    <div class="margin-form">
                            <input type = "text"  name="show_depth" value =' . (Tools::getValue('show_depth') ? Tools::getValue('show_depth') : Configuration::get($this->name . '_show_depth')) . ' ></input>
                    </div>';
        $this->_html .= '<label>' . $this->l('Show Link/Label Category: ') . '</label>';
        $this->_html .= '<div class="margin-form">';
        $this->_html .= '<select multiple="multiple" name ="list_cate[]" style="width: 200px; height: 160px;">';
        // BEGIN Categories
        $id_lang = (int) Context::getContext()->language->id;
        $this->getCategoryOption(1, (int) $id_lang, (int) Shop::getContextShopID());
        $this->_html .= '</select>
            </div>
                <label>' . $this->l('Merge small subcategories: ') . '</label>
                <div class="margin-form">';
        $this->_html .= $this->getSelectOptionsHtml(array(0 => 'No', 1 => 'Yes'), 'merge_cate', (Tools::getValue('merge_cate') ? Tools::getValue('merge_cate') : Configuration::get($this->name . '_merge_cate')));

        $this->_html .='
                </div>';
        $this->_html .= '<label>' . $this->l('Top offset: ') . '</label>
            <div class="margin-form">
                    <input type = "text"  name="top_offset" value =' . (Tools::getValue('top_offset') ? Tools::getValue('top_offset') : Configuration::get($this->name . '_top_offset')) . ' ></input>
            </div>';
        $this->_html .='<label>' . $this->l('Effect Popup: ') . '</label>
                    <div class="margin-form">';
        $this->_html .= $this->getSelectOptionsHtml(array(0 => 'SlideDown', 1 => 'FadeIn', 2 => 'Show'), 'effect', (Tools::getValue('effect') ? Tools::getValue('effect') : Configuration::get($this->name . '_effect')));

        $this->_html .='
                    </div>';
        $this->_html .='<div class ="submit">
                      <input type="submit" name="submitPosMegamenu" value="' . $this->l('Update') . '" class="button" />
            </div>              
                 </fieldset>
            </form>';
			
		
		$this->_html .= '
		<fieldset>
			<div class="multishop_info">
			'.$this->l('The modifications will be applied to').' '.(Shop::getContext() == Shop::CONTEXT_SHOP ? $this->l('shop').' '.$this->context->shop->name : $this->l('all shops')).'.
			</div>
			<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
			<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" id="form">
				<div style="display: none">
				<label>'.$this->l('Items').'</label>
				<div class="margin-form">
					<input type="text" name="items" id="itemsInput" value="'.Tools::safeOutput(Configuration::get('MOD_BLOCKPOSMENU_ITEMS')).'" size="70" />
				</div>
				</div>

				<div class="clear">&nbsp;</div>
				<table style="margin-left: 130px;">
					<tbody>
						<tr>
							<td style="padding-left: 20px;">
								<select multiple="multiple" id="availableItems" style="width: 300px; height: 160px;">';

		// BEGIN CMS
		$this->_html .= '<optgroup label="'.$this->l('CMS').'">';
		$this->getCMSOptions(0, 1, $id_lang);
		$this->_html .= '</optgroup>';

		// BEGIN SUPPLIER
		$this->_html .= '<optgroup label="'.$this->l('Supplier').'">';
		// Option to show all Suppliers
		$this->_html .= '<option value="ALLSUP0">'.$this->l('All suppliers').'</option>';
		$suppliers = Supplier::getSuppliers(false, $id_lang);
		foreach ($suppliers as $supplier)
			$this->_html .= '<option value="SUP'.$supplier['id_supplier'].'">'.$spacer.$supplier['name'].'</option>';
		$this->_html .= '</optgroup>';

		// BEGIN Manufacturer
		$this->_html .= '<optgroup label="'.$this->l('Manufacturer').'">';
		// Option to show all Manufacturers
		$this->_html .= '<option value="ALLMAN0">'.$this->l('All manufacturers').'</option>';
		$manufacturers = Manufacturer::getManufacturers(false, $id_lang);
		foreach ($manufacturers as $manufacturer)
			$this->_html .= '<option value="MAN'.$manufacturer['id_manufacturer'].'">'.$spacer.$manufacturer['name'].'</option>';
		$this->_html .= '</optgroup>';

		// BEGIN Categories
		$this->_html .= '<optgroup label="'.$this->l('Categories').'">';
		$this->getCategoryOptionLink(1, (int)$id_lang, (int)Shop::getContextShopID());
		$this->_html .= '</optgroup>';
		
		// BEGIN Shops
		if (Shop::isFeatureActive())
		{
			$this->_html .= '<optgroup label="'.$this->l('Shops').'">';
			$shops = Shop::getShopsCollection();
			foreach ($shops as $shop)
			{
				if (!$shop->setUrl() && !$shop->getBaseURL())
					continue;
				$this->_html .= '<option value="SHOP'.(int)$shop->id.'">'.$spacer.$shop->name.'</option>';
			}	
			$this->_html .= '</optgroup>';
		}
		
		// BEGIN Products
		$this->_html .= '<optgroup label="'.$this->l('Products').'">';
		$this->_html .= '<option value="PRODUCT" style="font-style:italic">'.$spacer.$this->l('Choose product ID').'</option>';
		$this->_html .= '</optgroup>';
		
		// BEGIN Menu Top Links
		$this->_html .= '<optgroup label="'.$this->l('Mega menu Top Links').'">';
		$links = MegaTopLinks::gets($id_lang, null, (int)Shop::getContextShopID());
		foreach ($links as $link)
		{
			if ($link['label'] == '')
			{
				$link = MegaTopLinks::get($link['id_linksmegatop'], $default_language, (int)Shop::getContextShopID());
				$this->_html .= '<option value="LNK'.(int)$link[0]['id_linksmegatop'].'">'.$spacer.$link[0]['label'].'</option>';
			}
			else
				$this->_html .= '<option value="LNK'.(int)$link['id_linksmegatop'].'">'.$spacer.$link['label'].'</option>';
		}
		$this->_html .= '</optgroup>';

		
		$this->_html .= '</select><br />
								<br />
								<a href="#" id="addItem" style="border: 1px solid rgb(170, 170, 170); margin: 2px; padding: 2px; text-align: center; display: block; text-decoration: none; background-color: rgb(250, 250, 250); color: rgb(18, 52, 86);">'.$this->l('Add').' &gt;&gt;</a>
							</td>
							<td>
								<select multiple="multiple" id="items" style="width: 300px; height: 160px;">';
		$this->makeMenuOption();
		$this->_html .= '</select><br/>
								<br/>
								<a href="#" id="removeItem" style="border: 1px solid rgb(170, 170, 170); margin: 2px; padding: 2px; text-align: center; display: block; text-decoration: none; background-color: rgb(250, 250, 250); color: rgb(18, 52, 86);">&lt;&lt; '.$this->l('Remove').'</a>
							</td>
							<td style="vertical-align:top;padding:5px 15px;">
								<h4 style="margin-top:5px;">'.$this->l('Change position').'</h4> 
								<a href="#" id="menuOrderUp" class="button" style="font-size:20px;display:block;">&uarr;</a><br/>
								<a href="#" id="menuOrderDown" class="button" style="font-size:20px;display:block;">&darr;</a><br/>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="clear">&nbsp;</div>
				<script type="text/javascript">
				function add()
				{
					$("#availableItems option:selected").each(function(i){
						var val = $(this).val();
						var text = $(this).text();
						text = text.replace(/(^\s*)|(\s*$)/gi,"");
						if (val == "PRODUCT")
						{
							val = prompt("'.$this->l('Set ID product').'");
							if (val == null || val == "" || isNaN(val))
								return;
							text = "'.$this->l('Product ID').' "+val;
							val = "PRD"+val;
						}
						$("#items").append("<option value=\""+val+"\">"+text+"</option>");
					});
					serialize();
					return false;
				}

				function remove()
				{
					$("#items option:selected").each(function(i){
						$(this).remove();
					});
					serialize();
					return false;
				}

				function serialize()
				{
					var options = "";
					$("#items option").each(function(i){
						options += $(this).val() + ",";
					});
					$("#itemsInput").val(options.substr(0, options.length - 1));
				}

				function move(up)
				{
					var tomove = $("#items option:selected");
					if (tomove.length >1)
					{
						alert(\''.Tools::htmlentitiesUTF8($this->l('Please select just one item')).'\');
						return false;
					}
					if (up)
						tomove.prev().insertAfter(tomove);
					else
						tomove.next().insertBefore(tomove);
					serialize();
					return false;
				}

				$(document).ready(function(){
					$("#addItem").click(add);
					$("#availableItems").dblclick(add);
					$("#removeItem").click(remove);
					$("#items").dblclick(remove);
					$("#menuOrderUp").click(function(e){
						e.preventDefault();
						move(true);
					});
					$("#menuOrderDown").click(function(e){
						e.preventDefault();
						move();
					});
				});
				</script>
				<p class="center">
					<input type="submit" name="submitBlockposmenu" value="'.$this->l('Save	').'" class="button" />
				</p>
			</form>
		</fieldset><br />';		
		
		$this->_html .= '
		<fieldset>
			<legend><img src="../img/admin/add.gif" alt="" title="" />'.$this->l('Add Menu Top Link').'</legend>
			<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" id="form">

				';
		foreach ($languages as $language)
		{
			$this->_html .= '
					<div id="link_label_'.(int)$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang ? 'block' : 'none').';">
				<label>'.$this->l('Label').'</label>
				<div class="margin-form">
						<input type="text" name="label['.(int)$language['id_lang'].']" id="label_'.(int)$language['id_lang'].'" size="70" value="'.(isset($labels_edit[$language['id_lang']]) ? $labels_edit[$language['id_lang']] : '').'" />
			  </div>
					';

			$this->_html .= '
				  <label>'.$this->l('Link').'</label>
				<div class="margin-form">
					<input type="text" name="link['.(int)$language['id_lang'].']" id="link_'.(int)$language['id_lang'].'" value="'.(isset($links_label_edit[$language['id_lang']]) ? $links_label_edit[$language['id_lang']] : '').'" size="70" />
				</div>
				</div>';
		}

		$this->_html .= '<label>'.$this->l('Language').'</label>
				<div class="margin-form">'.$this->displayFlags($languages, (int)$id_lang, $divLangName, 'link_label', true).'</div><p style="clear: both;"> </p>';

		$this->_html .= '<label style="clear: both;">'.$this->l('New Window').'</label>
				<div class="margin-form">
					<input style="clear: both;" type="checkbox" name="new_window" value="1" '.(isset($new_window_edit) && $new_window_edit ? 'checked' : '').'/>
				</div>
				<div class="margin-form">';

						if (Tools::isSubmit('id_linksmegatop'))
							$this->_html .= '<input type="hidden" name="id_linksmegatop" value="'.(int)Tools::getValue('id_linksmegatop').'" />';

						if (Tools::isSubmit('submitBlocktopmegaEdit'))
							$this->_html .= '<input type="submit" name="submitBlocktopmegaEdit" value="'.$this->l('Edit').'" class="button" />';

						$this->_html .= '
									<input type="submit" name="submitBlocktopmegaLinks" value="'.$this->l('Add	').'" class="button" />
				</div>

			</form>
		</fieldset><br />';
		
			
			$links = MegaTopLinks::gets((int)$id_lang, null, (int)Shop::getContextShopID());

			if (!count($links))
				return $this->_html;

			$this->_html .= '
			<fieldset>
				<legend><img src="../img/admin/details.gif" alt="" title="" />'.$this->l('List Menu Top Link').'</legend>
				<table style="width:100%;">
					<thead>
						<tr style="text-align: left;">
							<th>'.$this->l('Id Link').'</th>
							<th>'.$this->l('Label').'</th>
							<th>'.$this->l('Link').'</th>
							<th>'.$this->l('New Window').'</th>
							<th>'.$this->l('Action').'</th>
						</tr>
					</thead>
					<tbody>';
			foreach ($links as $link)
			{
				$this->_html .= '
						<tr>
							<td>'.(int)$link['id_linksmegatop'].'</td>
							<td>'.Tools::safeOutput($link['label']).'</td>
							<td><a href="'.Tools::safeOutput($link['link']).'"'.(($link['new_window']) ? ' target="_blank"' : '').'>'.Tools::safeOutput($link['link']).'</a></td>
							<td>'.(($link['new_window']) ? $this->l('Yes') : $this->l('No')).'</td>
							<td>
								<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post">
									<input type="hidden" name="id_linksmegatop" value="'.(int)$link['id_linksmegatop'].'" />
									<input type="submit" name="submitBlocktopmegaEdit" value="'.$this->l('Edit').'" class="button" />
									<input type="submit" name="submitBlocktopmegaRemove" value="'.$this->l('Remove').'" class="button" />
								</form>
							</td>
						</tr>';
			}
			$this->_html .= '</tbody>
				</table>
			</fieldset>';
        return $this->_html;
    }
	
    private function getCategoryOption($id_category = 1, $id_lang = false, $id_shop = false, $recursive = true) {
		$cateCurrent = Configuration::get($this->name . '_list_cate');		
		$cateCurrent = explode(',', $cateCurrent);
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
		$category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);

		if (is_null($category->id))
			return;

		if ($recursive)
		{
			$children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
			$spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category->level_depth);
		}
		
		$shop = (object) Shop::getShop((int)$category->getShopID());
		        if (in_array('CAT'.(int)$category->id, $cateCurrent)) {
					$this->_html .= '<option value="CAT'.(int)$category->id.'" selected ="selected" >'.(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')</option>';
				} else {
					$this->_html .= '<option value="CAT'.(int)$category->id.'">'.(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')</option>';
				}

		if (isset($children) && count($children))
			foreach ($children as $child)
				$this->getCategoryOption((int)$child['id_category'], (int)$id_lang, (int)$child['id_shop']);
    }

    private function getCategoryOptionLink($id_category = 1, $id_lang = false, $id_shop = false, $recursive = true)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
		$category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);

		if (is_null($category->id))
			return;

		if ($recursive)
		{
			$children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
			$spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category->level_depth);
		}

		$shop = (object) Shop::getShop((int)$category->getShopID());
		$this->_html .= '<option value="CAT'.(int)$category->id.'">'.(isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')</option>';

		if (isset($children) && count($children))
			foreach ($children as $child)
				$this->getCategoryOptionLink((int)$child['id_category'], (int)$id_lang, (int)$child['id_shop']);
	}

    public function getStaticblockLists($id_shop = NULL, $identify= NULL) {
        if (!Combination::isFeatureActive())
            return array();
		$id_lang = (int)$this->context->language->id;
        return Db::getInstance()->executeS('
                        SELECT * FROM ' . _DB_PREFIX_ . 'pos_staticblock AS psb 
							LEFT JOIN ' . _DB_PREFIX_ . 'pos_staticblock_lang AS psl ON psb.id_posstaticblock = psl.id_posstaticblock
							LEFT JOIN ' . _DB_PREFIX_ . 'pos_staticblock_shop AS pss ON psb.id_posstaticblock = pss.id_posstaticblock
						WHERE id_shop =' . $id_shop . ' 
							AND id_lang =' . $id_lang .'
							AND `identify` = "' . $identify . '"
                    ');
    }

    public function getStaticblockCustommerLists($id_shop = NULL) {
        if (!Combination::isFeatureActive())
            return array();
		$id_lang = (int)$this->context->language->id;
        return Db::getInstance()->executeS('
                        SELECT * FROM ' . _DB_PREFIX_ . 'pos_staticblock AS psb 
						LEFT JOIN ' . _DB_PREFIX_ . 'pos_staticblock_lang AS psl ON psb.id_posstaticblock = psl.id_posstaticblock
				        LEFT JOIN ' . _DB_PREFIX_ . 'pos_staticblock_shop AS pss ON psb.id_posstaticblock = pss.id_posstaticblock
                        where id_shop =' . $id_shop . ' 
							AND id_lang =' . $id_lang .'
							AND `identify` like "pt_item_menu' . '%"
                    ');
    }

    public function getStaticBlockContent($blockId = NULL, $task = NULL) {
        $id_shop = (int) Context::getContext()->shop->id;
        $staticBlock = $this->getStaticblockLists($id_shop, $blockId);
		//echo "<pre>"; print_r($staticBlock);
        $html = "";
        if (count($staticBlock) > 0) {
            $description= $staticBlock[0]['description'];
            $description = str_replace('/pos_aboss/',__PS_BASE_URI__,$description);
            $html .= $description;
        }
        if ($task == 'item') {
            $staticBlock = $this->getStaticblockCustommerLists($id_shop);
            return $staticBlock;
        } else {
            return $html;
        }
    }

    public function getCurrentCategoriesId($lang_id = NULL) {
        if (isset($_GET['id_category'])) {
            $lastCateId = $_GET['id_category'];
        } else {
            $lastCateId = 0;
        }

        $lastCate = new Category((int) $lastCateId);
        //echo $lastCate->name[1]; echo '--------';
        $parentCate = $lastCate->getParentsCategories($lang_id);
        $arrayCateCurrent = array();
        foreach ($parentCate as $pcate) {
            $arrayCateCurrent[] = $pcate['id_category'];
        }
        return $arrayCateCurrent;
    }

    public function haveCateChildren($cate_id = NULL, $lang_id = NULL) {
        $cate = new Category();
        $childCates = $cate->getChildren($cate_id, $lang_id);
        if (count($childCates) > 0)
            return true;
        return false;
    }

    public function drawCustomMenuItem($category, $level = 0, $last = false, $item, $lang_id) {
        if ($level > $this->_show_level)
            return;
        $cateCurrent = $this->getCurrentCategoriesId($lang_id);
        $categoryObject = new Category();
        $html = '';
        $blockHtml = '';
        $id_shop = (int) Context::getContext()->shop->id;
        $id = $category;
        $blockId = sprintf('pt_menu_idcat_%d', $id);
        $staticBlock = $this->getStaticBlockContent($blockId);
        $blockIdRight = sprintf('pt_menu_idcat_%d_right', $id);
        $staticBlockRight = $this->getStaticBlockContent($blockIdRight);
        // --- Static Block ---
        $blockHtml = $staticBlock;
        /* check block right */
        $blockHtmlRight = $staticBlockRight;

        if ($blockHtmlRight)
            $blockHtml = $blockHtmlRight;
        // --- Sub Categories ---
        $activeChildren = $categoryObject->getChildren($category, $lang_id);
        $activeChildren = $this->getCategoryByLevelMax($activeChildren);
        // --- class for active category ---
        $active = '';
        if (isset($cateCurrent[0]) && in_array($category, array($cateCurrent[0])))
            $active = ' act';
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        if ($drawPopup) {
            $html  .= '<div id="pt_menu' . $id . '" class="pt_menu' . $active . ' nav-' . $item . '">';
        } else {
            $html  .= '<div id="pt_menu' . $id . '" class="pt_menu' . $active . ' nav-' . $item . ' pt_menu_no_child">';
        }
		//echo $category;
        //$cate = new Category((int) $category);
		$id_lang =  (int)Context::getContext()->language->id;
		$cate = new Category((int)$category,$id_lang,$id_shop);
        //$link = $categoryObject->getLinkRewrite($category, $lang_id);
        $parameters = "";
        $link = Context::getContext()->link->getCategoryLink((int) $category, null, null, ltrim($parameters, '/'));
        // --- Top Menu Item ---
        $html  .= '<div class="parentMenu">';
		$html  .= '<a href="' . $link . '" class="fontcustom2">';
        $name = strip_tags($cate->name);
        $name = str_replace('&nbsp;', ' ', $name);
		$name = $this->l($name);
        $html  .= '<span>' . $name . '</span>';
		if ($drawPopup) {$html  .= '<i class="icon-caret-down"></i>';}
        $html  .= '</a>';
        $html  .= '</div>';

        // --- Add Popup block (hidden) ---
        if ($drawPopup) {
            if ($this->_show_level > 2) {
                // --- Popup function for hide ---
                $html  .= '<div id="popup' . $id . '" class="popup" style="display: none; width: 1228px;">';
                // --- draw Sub Categories ---
                 if (count($activeChildren) || $blockHtml) {
                    $html  .= '<div class="block1" id="block1' . $id . '">';
                    $html  .= $this->drawColumns($activeChildren, $id, $lang_id);
                    if ($blockHtml && $blockHtmlRight) {
                        $html  .= '<div class="column blockright">';
                        $html  .= $blockHtml;
                        $html  .= '</div>';
                    }
                    $html  .= '<div class="clearBoth"></div>';
                    $html  .= '</div>';
                }
                // --- draw Custom User Block ---
                if ($blockHtml && !$blockHtmlRight) {
                    $html  .= '<div class="block2" id="block2' . $id . '">';
                    $html  .= $blockHtml;
                    $html  .= '</div>';
                }
                $html  .= '</div>';
            }
        }

        $html  .= '</div>';
        return $html;
    }

    public function getCategoryByLevelMax($cates = NULL) {
        if (count($cates) < 1)
            return array();
        $cateArray = array();
        foreach ($cates as $key => $cate) {
            $cate_id = $cate['id_category'];
            $cateObject = new Category((int) $cate_id);
            $cate_level = $cateObject->level_depth;
            if ($cate_level <= $this->_show_level) {
                $cateArray[$key] = $cate;
            }
        }

        if ($cateArray)
            return $cateArray;
        return array();
    }

    public function drawMenuItem($children, $level = 1, $columChunk = 0, $lang_id = 1) {
        $html = '<div class="itemMenu level' . $level . '">';
        $keyCurrent = NULL;
        if (isset($_GET['id_category'])) {
            $keyCurrent = $_GET['id_category'];
        }
        $countChildren = 0;
        $ClassNoChildren = '';
        $category = new Category();
        foreach ($children as $child) {
            $activeChildCat = $category->getChildren($child['id_category'], $lang_id);
            $activeChildCat = $this->getCategoryByLevelMax($activeChildCat);
            if ($activeChildCat) {
                $countChildren++;
            }
        }
        if ($countChildren == 0 && $columChunk == 1) {
            $ClassNoChildren = ' nochild';
        }
        $catsid = '';
        $catsid = Configuration::get($this->name . '_list_cate');
		$arr_catsid = array();
        if ($catsid) {
            if (stristr($catsid, ',') === FALSE) {
                $arr_catsid = array(0 => $catsid);
            } else {
                $arr_catsid = explode(",", $catsid);
            }
        }
			$id_shop = (int) Context::getContext()->shop->id;
        foreach ($children as $child) {
             $info = new Category((int) $child['id_category'], $lang_id,$id_shop);
            $level = $info->level_depth;
            $active = '';
            $currentCate = $this->getCurrentCategoriesId($lang_id);
            $cate_id = (int) $child['id_category'];
            if (in_array($cate_id, $currentCate)) {
                if ($this->haveCateChildren($cate_id, $lang_id)) {
                    $active = ' actParent';
                } else {
                    $active = ' act';
                }
            }
            // --- format category name ---
            $name = strip_tags($child['name']);


            if (count($child) > 0) {
                $parameters = null;
                $link = Context::getContext()->link->getCategoryLink((int) $child['id_category'], null, null, ltrim($parameters, '/'));
                if (in_array('CAT'.$child['id_category'], $arr_catsid)) {
                    $html.= '<h4 class="itemMenuName level' . $level . $active . $ClassNoChildren . '"><span>' . $name . '</span></h4>';
                } else {
                    $html.= '<a class="itemMenuName level' . $level . $active . $ClassNoChildren . '" href="' . $link . '"><span>' . $name . '</span></a>';
                }

                $activeChildren = $category->getChildren($child['id_category'], $lang_id);
                $activeChildren = $this->getCategoryByLevelMax($activeChildren);
                if (count($activeChildren) > 0) {
                    $html.= '<div class="itemSubMenu level' . $level . '">';
                    //$html.= $this->drawMenuItem($activeChildren, $level + 1);
					$html.= $this->drawMenuItem($activeChildren, $level + 1,$columChunk, $lang_id);
                    $html.= '</div>';
                }
            }
        }
        $html.= '</div>';
        return $html;
    }

    public function drawColumns($children, $id, $lang_id) {
        $html = '';
        // --- explode by columns ---
        $columns = Configuration::get($this->name . '_menu_depth');
        if ($columns < 1)
            $columns = 1;
        $chunks = $this->seperateColumns($children, $columns, $lang_id);
        $columChunk = count($chunks);
        // --- draw columns ---
        $classSpecial = '';
        $keyLast = 0;
        foreach ($chunks as $key => $value) {
            if (count($value))
                $keyLast++;
        }
        $blockHtml = '';
        $id_shop = (int) Context::getContext()->shop->id;
        $blockId = sprintf('pt_menu_idcat_%d', $id);
        $staticBlock = $this->getStaticBlockContent($blockId);
        $blockIdRight = sprintf('pt_menu_idcat_%d_right', $id);
        $staticBlockRight = $this->getStaticBlockContent($blockIdRight);
        // --- Static Block ---
        $blockHtml = $staticBlock;
        /* check block right */
        $blockHtmlRight = $staticBlockRight;

        foreach ($chunks as $key => $value) {
            if (!count($value))
                continue;
            if ($key == $keyLast - 1) {
                $classSpecial = ($blockHtmlRight && $blockHtml) ? '' : ' last';
            } elseif ($key == 0) {
                $classSpecial = ' first';
            } else {
                $classSpecial = '';
            }
            $html.= '<div class="column' . $classSpecial . ' col' . ($key + 1) . '">';
            $html.= $this->drawMenuItem($value, 1, $columChunk, $lang_id);

            $html.= '</div>';
        }
        return $html;
    }

    public function drawCustomMenuBlock($blockId, $bc) {
        $html = array();
        $id = '_' . $blockId;


        $blockHtml = str_replace('/pos_aboss/',__PS_BASE_URI__,$bc['description']);
        $drawPopup = $blockHtml;
        if ($drawPopup) {
            $html  .= '<div id="pt_menu' . $id . '" class="pt_menu">';
        } else {
            $html  .= '<div id="pt_menu' . $id . '" class="pt_menu">';
        }
        // --- Top Menu Item ---
        $html  .= '<div class="parentMenu">';
//        $html  .= '<a href="#">';
        $name = $this->l($bc['title']);
        $html  .= '<span class="block-title">' . $name . '</span>';
//        $html  .= '</a>';
        $html  .= '</div>';
        // --- Add Popup block (hidden) ---
        if ($drawPopup) {
            // --- Popup function for hide ---
            $html  .= '<div id="popup' . $id . '" class="popup cmsblock" style="display: none; width: 904px;">';
            if ($blockHtml) {
                $html  .= '<div class="block2" id="block2' . $id . '">';
                $html  .= $blockHtml;
                $html  .= '</div>';
            }
            $html  .= '</div>';
        }
        $html  .= '</div>';
        $html = implode("\n", $html);
        return $html;
    }

    private function seperateColumns($parentCates, $num, $lang_id) {
        $category = new Category();
        $countChildren = 0;
        foreach ($parentCates as $cat => $childCat) {
            $activeChildCat = $category->getChildren($childCat['id_category'], $lang_id);
            $activeChildCat = $this->getCategoryByLevelMax($activeChildCat);
            if ($activeChildCat) {
                $countChildren++;
            }
        }
        if ($countChildren == 0) {
            $num = 1;
        }


        $count = count($parentCates);
        if ($count)
            $parentCates = array_chunk($parentCates, ceil($count / $num));

        $parentCates = array_pad($parentCates, $num, array());
        $is_merge = Configuration::get($this->name . '_merge_cate');
        if ($is_merge && count($parentCates)) {
            // --- combine consistently numerically small column ---
            // --- 1. calc length of each column ---
            $max = 0;
            $columnsLength = array();
            foreach ($parentCates as $key => $child) {
                $count = 0;
                $this->_countChild($child, 1, $count, $lang_id);

                if ($max < $count)
                    $max = $count;
                $columnsLength[$key] = $count;
            }
            // --- 2. merge small columns with next ---
            $xColumns = array();
            $column = array();
            $cnt = 0;
            $xColumnsLength = array();
            $k = 0;

            foreach ($columnsLength as $key => $count) {
                $cnt+= $count;
                if ($cnt > $max && count($column)) {
                    $xColumns[$k] = $column;
                    $xColumnsLength[$k] = $cnt - $count;
                    $k++;
                    $column = array();
                    $cnt = $count;
                }
                $column = array_merge($column, $parentCates[$key]);
            }
            $xColumns[$k] = $column;
            $xColumnsLength[$k] = $cnt - $count;
            // --- 3. integrate columns of one element ---
            $parentCates = $xColumns;
            $xColumns = array();
            $nextKey = -1;
            if ($max > 1 && count($parentCates) > 1) {
                foreach ($parentCates as $key => $column) {
                    if ($key == $nextKey)
                        continue;
                    if ($xColumnsLength[$key] == 1) {
                        // --- merge with next column ---
                        $nextKey = $key + 1;
                        if (isset($parentCates[$nextKey]) && count($parentCates[$nextKey])) {
                            $xColumns[] = array_merge($column, $parentCates[$nextKey]);
                            continue;
                        }
                    }
                    $xColumns[] = $column;
                }
                $parentCates = $xColumns;
            }
        }

        return $parentCates;
    }

    private function _countChild($children, $level, &$count, $lang_id) {
        $category = new Category();
        foreach ($children as $child) {

            $count++;
            $activeChildren = $category->getChildren($child['id_category'], $lang_id);
            $activeChildren = $this->getCategoryByLevelMax($activeChildren);
            if (count($activeChildren) > 0)
                $this->_countChild($activeChildren, $level + 1, $count, $lang_id);
        }
    }

    public function hookDisplayHeader() {

        // $this->context->controller->addCSS($this->_path . 'css/custommenu.css');
        $this->context->controller->addJS($this->_path . 'js/mobile_menu.js');
			 $this->context->controller->addJS($this->_path . 'js/custommenu.js');
    }

    //mobile megamenu 
    public function getTree($resultParents, $resultIds, $maxDepth, $id_category = null, $currentDepth = 0) {
        if (is_null($id_category))
            $id_category = $this->context->shop->getCategory();

        $children = array();
        if (isset($resultParents[$id_category]) && count($resultParents[$id_category]) && ($maxDepth == 0 || $currentDepth < $maxDepth))
            foreach ($resultParents[$id_category] as $subcat)
                $children[] = $this->getTree($resultParents, $resultIds, $maxDepth, $subcat['id_category'], $currentDepth + 1);
        if (!isset($resultIds[$id_category]))
            return false;
        $return = array('id' => $id_category, 'link' => $this->context->link->getCategoryLink($id_category, $resultIds[$id_category]['link_rewrite']),
            'name' => $resultIds[$id_category]['name'], 'desc' => $resultIds[$id_category]['description'],
            'children' => $children);
        return $return;
    }

    public function getblockCategTree() {

        // Get all groups for this customer and concatenate them as a string: "1,2,3..."
        $groups = implode(', ', Customer::getGroupsStatic((int) $this->context->customer->id));
        $maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
        if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT DISTINCT c.id_parent, c.id_category, cl.name, cl.description, cl.link_rewrite
				FROM `' . _DB_PREFIX_ . 'category` c
				INNER JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_lang` = ' . (int) $this->context->language->id . Shop::addSqlRestrictionOnLang('cl') . ')
				INNER JOIN `' . _DB_PREFIX_ . 'category_shop` cs ON (cs.`id_category` = c.`id_category` AND cs.`id_shop` = ' . (int) $this->context->shop->id . ')
				WHERE (c.`active` = 1 OR c.`id_category` = ' . (int) Configuration::get('PS_HOME_CATEGORY') . ')
				AND c.`id_category` != ' . (int) Configuration::get('PS_ROOT_CATEGORY') . '
				' . ((int) $maxdepth != 0 ? ' AND `level_depth` <= ' . (int) $maxdepth : '') . '
				AND c.id_category IN (SELECT id_category FROM `' . _DB_PREFIX_ . 'category_group` WHERE `id_group` IN (' . pSQL($groups) . '))
				ORDER BY `level_depth` ASC, ' . (Configuration::get('BLOCK_CATEG_SORT') ? 'cl.`name`' : 'cs.`position`') . ' ' . (Configuration::get('BLOCK_CATEG_SORT_WAY') ? 'DESC' : 'ASC')))
            return;

        $resultParents = array();
        $resultIds = array();

        foreach ($result as &$row) {
            $resultParents[$row['id_parent']][] = &$row;
            $resultIds[$row['id_category']] = &$row;
        }

        $blockCategTree = $this->getTree($resultParents, $resultIds, Configuration::get('BLOCK_CATEG_MAX_DEPTH'));
        unset($resultParents, $resultIds);

        $id_category = (int) Tools::getValue('id_category');
        $id_product = (int) Tools::getValue('id_product');


        if (Tools::isSubmit('id_category')) {
            $this->context->cookie->last_visited_category = $id_category;
            $this->smarty->assign('currentCategoryId', $this->context->cookie->last_visited_category);
        }
        if (Tools::isSubmit('id_product')) {
            if (!isset($this->context->cookie->last_visited_category)
                    || !Product::idIsOnCategoryId($id_product, array('0' => array('id_category' => $this->context->cookie->last_visited_category)))
                    || !Category::inShopStatic($this->context->cookie->last_visited_category, $this->context->shop)) {
                $product = new Product($id_product);
                if (isset($product) && Validate::isLoadedObject($product))
                    $this->context->cookie->last_visited_category = (int) $product->id_category_default;
            }
            $this->smarty->assign('currentCategoryId', (int) $this->context->cookie->last_visited_category);
        }
        return $blockCategTree;
    }
	
	public function getMenuCustomerLink($lang_id = NULL) {
		$menu_items = $this->getMenuItems();
		$item1=0;
		$id_lang = (int)$this->context->language->id;
		$id_shop = (int)Shop::getContextShopID();
		$cms_link = array();
		$cms_cate = array();
		$supply_link = array();
		$manufacture_link = array();
		$custom_link = array();
		$product_link = array();
		$all_man_link =null;
		$all_sup_link = null;
		foreach ($menu_items as $item)
		{
			if (!$item)
				continue;

			preg_match($this->pattern, $item, $value);
			$id = (int)substr($item, strlen($value[1]), strlen($item));
			
			switch (substr($item, 0, strlen($value[1])))
			{
				case 'CAT':
					$item1 = $item1+ 1; 
					$this->_menuLink .= $this->drawCustomMenuItem($id, 0, false, $item1, $lang_id);
					break;
				case 'PRD':
					$selected = ($this->page_name == 'product' && (Tools::getValue('id_product') == $id)) ? ' class="sfHover"' : '';
					$product = new Product((int)$id, true, (int)$id_lang);
					if (!is_null($product->id)){
						$this->_menuLink .= '<div class ="pt_menu pt_menu_product"><div class="parentMenu" ><a class="fontcustom2" href="'.Tools::HtmlEntitiesUTF8($product->getLink()).'"><span>'.$product->name.'</span></a></div></div>'.PHP_EOL;
						
						$product_link[] = array(
							'link' => Tools::HtmlEntitiesUTF8($product->getLink()),
							'title' => $product->name
						);
						
						
					}
					break;
				case 'CMS':
					$selected = ($this->page_name == 'cms' && (Tools::getValue('id_cms') == $id)) ? ' class="sfHover"' : '';
					$cms = CMS::getLinks((int)$id_lang, array($id));
					if (count($cms)) {
						$this->_menuLink .= '<div class ="pt_menu pt_menu_cms"><div class="parentMenu" ><a class="fontcustom2" href="'.Tools::HtmlEntitiesUTF8($cms[0]['link']).'"><span>'.$cms[0]['meta_title'].'</span></a></div></div>'.PHP_EOL;
						$cms_link[] = array(
							'link' => $cms[0]['link'],
							'title' => $cms[0]['meta_title']
						);
				    }
				break;
			    
				case 'CMS_CAT':
					$category = new CMSCategory((int)$id, (int)$id_lang);
					if (Validate::isLoadedObject($category))
					$this->_menuLink .= '<div class ="pt_menu_cms pt_menu"><div class="parentMenu" ><a class="fontcustom2" href="'.Tools::HtmlEntitiesUTF8($category->getLink()).'"><span>'.$category->name.'</span></a>';
					$this->_menuLink .= '</div></div>'.PHP_EOL;
					$this->_menuLink .= $this->getCMSMenuItems($category->id);	
					$cms_cate[] = array(
							'link' =>Tools::HtmlEntitiesUTF8($category->getLink()),
							'title' => $category->name
					);

				break;

				// Case to handle the option to show all Manufacturers
				case 'ALLMAN':
				
					$link = new Link;
					$this->_menuLink .= '<div id ="pt_menu_allsub" class ="pt_menu"><div class="parentMenu" ><a class="fontcustom2" href="'.$link->getPageLink('manufacturer').'" ><span>'.$this->l('All manufacturers').'</span></a></div>'.PHP_EOL;
	
					$manufacturers = Manufacturer::getManufacturers();
					if(count($manufacturers)>0) {
						$this->_menuLink .= '<div class ="popup" style ="display:none">';
						foreach ($manufacturers as $key => $manufacturer)
							$this->_menuLink .= '<div class ="block1"><div class="column col1"><div class="itemSubMenu level3"><a href="'.$link->getManufacturerLink((int)$manufacturer['id_manufacturer'], $manufacturer['link_rewrite']).'">'.$manufacturer['name'].'</a></div></div></div>'.PHP_EOL;
						$this->_menuLink .= '</div></div>';
					}
					$all_man_link .= '<li>	<a href="'.$link->getPageLink('supplier').'" title="'.$this->l('All suppliers').'">'.$this->l('All suppliers').'</a>';
					if(count($manufacturers)>0) {	
						$all_man_link .='<ul>';
					
						foreach ($manufacturers as $key => $manufacturer)
							$all_man_link .= '<li><a href="'.$link->getManufacturerLink((int)$manufacturer['id_manufacturer'], $manufacturer['link_rewrite']).'">'.$manufacturer['name'].'</a></li>'.PHP_EOL;
					
						$all_man_link .='</ul>';
					}
					$all_man_link .='</li>';
					
					break;
				case 'MAN':
					$selected = ($this->page_name == 'manufacturer' && (Tools::getValue('id_manufacturer') == $id)) ? ' class="sfHover"' : '';
					$manufacturer = new Manufacturer((int)$id, (int)$id_lang);
					if (!is_null($manufacturer->id))
					{
						if (intval(Configuration::get('PS_REWRITING_SETTINGS')))
							$manufacturer->link_rewrite = Tools::link_rewrite($manufacturer->name);
						else
							$manufacturer->link_rewrite = 0;
						$link = new Link;
						$this->_menuLink .= '<div id ="pt_menu_man" class ="pt_menu"><div class="parentMenu" ><a class="fontcustom2" href="'.Tools::HtmlEntitiesUTF8($link->getManufacturerLink((int)$id, $manufacturer->link_rewrite)).'"><span>'.$manufacturer->name.'</span></a></div></div>'.PHP_EOL;
						$manufacture_link[] = array(
							'link' => Tools::HtmlEntitiesUTF8($link->getManufacturerLink((int)$id, $manufacturer->link_rewrite)),
							'title' => $manufacturer->name
						);
					}
					break;

				// Case to handle the option to show all Suppliers
				case 'ALLSUP':
					$link = new Link;
					$this->_menuLink .= '<div id ="pt_menu_allsub" class ="pt_menu"><div class="parentMenu" ><a class="fontcustom2" href="'.$link->getPageLink('supplier').'" ><span>'.$this->l('All suppliers').'</span></a></div>'.PHP_EOL;
	
					$suppliers = Supplier::getSuppliers();
					if(count($suppliers) >0 ) {
					$this->_menuLink .= '<div class ="popup" style ="display:none">';
					foreach ($suppliers as $key => $supplier)
						$this->_menuLink .= '<div class ="block1"><div class="column col1"><div class="itemSubMenu level3"><a href="'.$link->getSupplierLink((int)$supplier['id_supplier'], $supplier['link_rewrite']).'">'.$supplier['name'].'</a></div></div></div>'.PHP_EOL;
					$this->_menuLink .= '</div></div>';
					}
					$all_sup_link .= '<li>	<a href="'.$link->getPageLink('supplier').'" title="'.$this->l('All suppliers').'">'.$this->l('All suppliers').'</a>';
					if(count($suppliers) >0 ) {
							$all_sup_link .='<ul>';
							
							foreach ($suppliers as $key => $supplier)
								$all_sup_link .= '<li><a href="'.$link->getSupplierLink((int)$supplier['id_supplier'], $supplier['link_rewrite']).'">'.$supplier['name'].'</a></li>'.PHP_EOL;

							$all_sup_link .='</ul>';
					}
					$all_sup_link .='</li>';
					break;

				case 'SUP':
					$selected = ($this->page_name == 'supplier' && (Tools::getValue('id_supplier') == $id)) ? ' class="sfHover"' : '';
					$supplier = new Supplier((int)$id, (int)$id_lang);
					if (!is_null($supplier->id))
					{
						$link = new Link;
					
						$this->_menuLink .= '<div id ="pt_menu_sub" class ="pt_menu"><div class="parentMenu" ><a class="fontcustom2" href="'.Tools::HtmlEntitiesUTF8($link->getSupplierLink((int)$id, $supplier->link_rewrite)).'"><span>'.$supplier->name.'</span></a></div></div>'.PHP_EOL;
					
						$supply_link[] = array(
							'link' => Tools::HtmlEntitiesUTF8($link->getSupplierLink((int)$id, $supplier->link_rewrite)),
							'title' => $supplier->name
						);
					
					}
					break;

				case 'SHOP':
					$selected = ($this->page_name == 'index' && ($this->context->shop->id == $id)) ? ' class="sfHover"' : '';
					$shop = new Shop((int)$id);
					if (Validate::isLoadedObject($shop))
					{
						$link = new Link;
						$this->_menuLink .= '<div id ="pt_menu_sub" class ="pt_menu"><div class="parentMenu" ><a class="fontcustom2" href="'.Tools::HtmlEntitiesUTF8($shop->getBaseURL()).'"><span>'.$supplier->name.'</span></a></div></div>'.PHP_EOL;
					}
					break;
				case 'LNK':
					$link = MegaTopLinks::get((int)$id, (int)$id_lang, (int)$id_shop);
					if (count($link))
					{
						if (!isset($link[0]['label']) || ($link[0]['label'] == ''))
						{
							$default_language = Configuration::get('PS_LANG_DEFAULT');
							$link = MegaTopLinks::get($link[0]['id_linksmegatop'], $default_language, (int)Shop::getContextShopID());
						}
						$this->_menuLink .= '<div id ="pt_menu_link" class ="pt_menu"><div class="parentMenu" ><a class="fontcustom2" href="'.Tools::HtmlEntitiesUTF8($link[0]['link']).'"'.(($link[0]['new_window']) ? ' target="_blank"': '').'><span>'.$link[0]['label'].'</span></a></div></div>'.PHP_EOL;
						$custom_link[] = array(
							'link' => Tools::HtmlEntitiesUTF8($link[0]['link']) , 
							'title' => $link[0]['label']
						);
					}
					break;
					
					
			}
			
			
			    $this->context->smarty->assign(
						array(
							 'cms_link' =>  $cms_link,
							 'cms_cate' => $cms_cate,
							 'manufacture_link' => $manufacture_link,
							 'supply_link' => $supply_link,
							 'custom_link' => $custom_link,
							 'product_link' => $product_link,
							 'all_man_link' => $all_man_link,
							 'all_sup_link' => $all_sup_link
						)
				);
	
		}

	}
	
    public function hookdisplayMegamenu() {
        //$lang_id = (int) Configuration::get('PS_LANG_DEFAULT');
		$lang_id = (int)Context::getContext()->language->id;
        $category = new Category();
        //$homeCates = $category->getHomeCategories($lang_id);
		$this->getMenuCustomerLink($lang_id);
	    $item = 0;
        $html = "";
        $showhome = Configuration::get($this->name . '_show_homepage');
        if ($showhome) {
            $page_name = Dispatcher::getInstance()->getController();
            $active = null;
            if ($page_name == 'index')
                $active = ' act';
            $id = "_home";
            $html .= '<div id="pt_menu' . $id . '" class="pt_menu' . $active . '">';
            $html .= '<div class="parentMenu">';
            $html .= '<a class="fontcustom2" href="' . __PS_BASE_URI__ . '">';
            $html .= '<span>' . $this->l('Home') . '</span>';
            $html .= '</a>';
            $html .= '</div>';
            $html .= '</div>';
        }
		
        // foreach ($homeCates as $cate) {
            // $item++;
           // // $html .= $this->drawCustomMenuItem($cate['id_category'], 0, false, $item, $lang_id);
        // }
		$html .= $this->_menuLink;

        $blockCustomer = $this->getStaticBlockContent(null, 'item');
        foreach ($blockCustomer as $bc) {
            $html .= $this->drawCustomMenuBlock($bc['identify'], $bc);
        }
        $isDhtml = (Configuration::get('BLOCK_CATEG_DHTML') == 1 ? true : false);
        $blockCategTree = $this->getblockCategTree();
        $this->smarty->assign('blockCategTree', $blockCategTree);
        if (file_exists(_PS_THEME_DIR_ . 'modules/blockcategories/blockcategories.tpl'))
            $this->smarty->assign('branche_tpl_path', _PS_THEME_DIR_ . 'modules/blockcategories/category-tree-branch.tpl');
        else
            $this->smarty->assign('branche_tpl_path', _PS_MODULE_DIR_ . 'blockcategories/category-tree-branch.tpl');
        $this->smarty->assign('isDhtml', $isDhtml);
        $this->context->smarty->assign(
                array(
                    'megamenu' => $html,
                    'top_offset' => Configuration::get($this->name . '_top_offset'),
                    'effect' => Configuration::get($this->name . '_effect'),
					 'menu_link' =>  $this->_menuLink,
                )
        );
        //$test = $this->seperateColumns($homeCates,4, $lang_id);


        return $this->display(__FILE__, 'megamenu.tpl');
    }
	
	private function getCMSMenuItems($parent, $depth = 1, $id_lang = false)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		if ($depth > 3)
			return;

		$categories = $this->getCMSCategories(false, (int)$parent, (int)$id_lang);

		$pages = $this->getCMSPages((int)$parent);
		if (count($categories) || count($pages))
		{

			
			$this->_menuLink .= '<div class ="popup" style ="display:none">';
			
			foreach ($pages as  $page) {
				$cms = new CMS($page['id_cms'], (int)$id_lang);
				$links = $cms->getLinks((int)$id_lang, array((int)$cms->id));
				$selected = ($this->page_name == 'cms' && ((int)Tools::getValue('id_cms') == $page['id_cms'])) ? ' class="sfHoverForce"' : '';
		
				$this->_menuLink .= '<div class ="block1"><div class="column col1"><div class="itemSubMenu level3"><a href="'.$links[0]['link'].'">'.$cms->meta_title.'</a></div></div></div>'.PHP_EOL;
			}	
			$this->_menuLink .= '</div></div>';
	

	
		}
	}
	
		private function getCMSMenuItemsMobile($parent, $depth = 1, $id_lang = false)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		if ($depth > 3)
			return;

		$categories = $this->getCMSCategories(false, (int)$parent, (int)$id_lang);

		$pages = $this->getCMSPages((int)$parent);
		if (count($categories) || count($pages))
		{

			
			$this->_menuLink .= '<div class ="popup" style ="display:none">';
			
			foreach ($pages as  $page) {
				$cms = new CMS($page['id_cms'], (int)$id_lang);
				$links = $cms->getLinks((int)$id_lang, array((int)$cms->id));
				$selected = ($this->page_name == 'cms' && ((int)Tools::getValue('id_cms') == $page['id_cms'])) ? ' class="sfHoverForce"' : '';
		
				$this->_menuLink .= '<div class ="block1"><div class="column col1"><div class="itemSubMenu level3"><a href="'.$links[0]['link'].'">'.$cms->meta_title.'</a></div></div></div>'.PHP_EOL;
			}	
			$this->_menuLink .= '</div></div>';
	

	
		}
	}
	
	private function getCMSOptions($parent = 0, $depth = 1, $id_lang = false)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		$categories = $this->getCMSCategories(false, (int)$parent, (int)$id_lang);
		$pages = $this->getCMSPages((int)$parent, false, (int)$id_lang);

		$spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$depth);

		foreach ($categories as $category)
		{
			$this->_html .= '<option value="CMS_CAT'.$category['id_cms_category'].'" style="font-weight: bold;">'.$spacer.$category['name'].'</option>';
			$this->getCMSOptions($category['id_cms_category'], (int)$depth + 1, (int)$id_lang);
		}

		foreach ($pages as $page)
			$this->_html .= '<option value="CMS'.$page['id_cms'].'">'.$spacer.$page['meta_title'].'</option>';
	}
	
	private function getCMSCategories($recursive = false, $parent = 1, $id_lang = false)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		if ($recursive === false)
		{
			$sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
				FROM `'._DB_PREFIX_.'cms_category` bcp
				INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
				ON (bcp.`id_cms_category` = cl.`id_cms_category`)
				WHERE cl.`id_lang` = '.(int)$id_lang.'
				AND bcp.`id_parent` = '.(int)$parent;

			return Db::getInstance()->executeS($sql);
		}
		else
		{
			$sql = 'SELECT bcp.`id_cms_category`, bcp.`id_parent`, bcp.`level_depth`, bcp.`active`, bcp.`position`, cl.`name`, cl.`link_rewrite`
				FROM `'._DB_PREFIX_.'cms_category` bcp
				INNER JOIN `'._DB_PREFIX_.'cms_category_lang` cl
				ON (bcp.`id_cms_category` = cl.`id_cms_category`)
				WHERE cl.`id_lang` = '.(int)$id_lang.'
				AND bcp.`id_parent` = '.(int)$parent;

			$results = Db::getInstance()->executeS($sql);
			foreach ($results as $result)
			{
				$sub_categories = $this->getCMSCategories(true, $result['id_cms_category'], (int)$id_lang);
				if ($sub_categories && count($sub_categories) > 0)
					$result['sub_categories'] = $sub_categories;
				$categories[] = $result;
			}

			return isset($categories) ? $categories : false;
		}

	}

	private function getCMSPages($id_cms_category, $id_shop = false, $id_lang = false)
	{
		$id_shop = ($id_shop !== false) ? (int)$id_shop : (int)Context::getContext()->shop->id;
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;

		$sql = 'SELECT c.`id_cms`, cl.`meta_title`, cl.`link_rewrite`
			FROM `'._DB_PREFIX_.'cms` c
			INNER JOIN `'._DB_PREFIX_.'cms_shop` cs
			ON (c.`id_cms` = cs.`id_cms`)
			INNER JOIN `'._DB_PREFIX_.'cms_lang` cl
			ON (c.`id_cms` = cl.`id_cms`)
			WHERE c.`id_cms_category` = '.(int)$id_cms_category.'
			AND cs.`id_shop` = '.(int)$id_shop.'
			AND cl.`id_lang` = '.(int)$id_lang.'
			AND c.`active` = 1
			ORDER BY `position`';

		return Db::getInstance()->executeS($sql);
	}
	
	private function makeMenuOption()
	{
		$menu_item = $this->getMenuItems();
		$id_lang = (int)$this->context->language->id;
		$id_shop = (int)Shop::getContextShopID();
		foreach ($menu_item as $item)
		{
			if (!$item)
				continue;

			preg_match($this->pattern, $item, $values);
			$id = (int)substr($item, strlen($values[1]), strlen($item));

			switch (substr($item, 0, strlen($values[1])))
			{
				case 'CAT':
					$category = new Category((int)$id, (int)$id_lang);
					if (Validate::isLoadedObject($category))
						$this->_html .= '<option value="CAT'.$id.'">'.$category->name.'</option>'.PHP_EOL;
					break;

				case 'PRD':
					$product = new Product((int)$id, true, (int)$id_lang);
					if (Validate::isLoadedObject($product))
						$this->_html .= '<option value="PRD'.$id.'">'.$product->name.'</option>'.PHP_EOL;
					break;

				case 'CMS':
					$cms = new CMS((int)$id, (int)$id_lang);
					if (Validate::isLoadedObject($cms))
						$this->_html .= '<option value="CMS'.$id.'">'.$cms->meta_title.'</option>'.PHP_EOL;
					break;

				case 'CMS_CAT':
					$category = new CMSCategory((int)$id, (int)$id_lang);
					if (Validate::isLoadedObject($category))
						$this->_html .= '<option value="CMS_CAT'.$id.'">'.$category->name.'</option>'.PHP_EOL;
					break;

				// Case to handle the option to show all Manufacturers
				case 'ALLMAN':
					$this->_html .= '<option value="ALLMAN0">'.$this->l('All manufacturers').'</option>'.PHP_EOL;
					break;

				case 'MAN':
					$manufacturer = new Manufacturer((int)$id, (int)$id_lang);
					if (Validate::isLoadedObject($manufacturer))
						$this->_html .= '<option value="MAN'.$id.'">'.$manufacturer->name.'</option>'.PHP_EOL;
					break;

				// Case to handle the option to show all Suppliers
				case 'ALLSUP':
					$this->_html .= '<option value="ALLSUP0">'.$this->l('All suppliers').'</option>'.PHP_EOL;
					break;
					
				case 'SUP':
					$supplier = new Supplier((int)$id, (int)$id_lang);
					if (Validate::isLoadedObject($supplier))
						$this->_html .= '<option value="SUP'.$id.'">'.$supplier->name.'</option>'.PHP_EOL;
					break;
				case 'LNK':
					$link = MegaTopLinks::get((int)$id, (int)$id_lang, (int)$id_shop);
					if (count($link))
					{
						if (!isset($link[0]['label']) || ($link[0]['label'] == ''))
						{
							$default_language = Configuration::get('PS_LANG_DEFAULT');
							$link = MegaTopLinks::get($link[0]['id_linksmegatop'], (int)$default_language, (int)Shop::getContextShopID());
						}
						$this->_html .= '<option value="LNK'.$link[0]['id_linksmegatop'].'">'.$link[0]['label'].'</option>';
					}
					break;	

				case 'SHOP':
					$shop = new Shop((int)$id);
					if (Validate::isLoadedObject($shop))
						$this->_html .= '<option value="SHOP'.(int)$id.'">'.$shop->name.'</option>'.PHP_EOL;
					break;
			}
		}
	}
	
	private function getMenuItems(){
		
		return explode(',', Configuration::get('MOD_BLOCKPOSMENU_ITEMS'));
	}
	
	public function installDb(){
		
			return (Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'linksmegatop` (
				`id_linksmegatop` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`id_shop` INT(11) UNSIGNED NOT NULL,
				`new_window` TINYINT( 1 ) NOT NULL,
				INDEX (`id_shop`)
			) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;') &&
				Db::getInstance()->execute('
				 CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'linksmegatop_lang` (
				`id_linksmegatop` INT(11) UNSIGNED NOT NULL,
				`id_lang` INT(11) UNSIGNED NOT NULL,
				`id_shop` INT(11) UNSIGNED NOT NULL,
				`label` VARCHAR( 128 ) NOT NULL ,
				`link` VARCHAR( 128 ) NOT NULL ,
				INDEX ( `id_linksmegatop` , `id_lang`, `id_shop`)
			) ENGINE = '._MYSQL_ENGINE_.' CHARACTER SET utf8 COLLATE utf8_general_ci;'));
	}
	
	private function uninstallDb() {
		Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'linksmegatop`');
		Db::getInstance()->execute('DROP TABLE `'._DB_PREFIX_.'linksmegatop_lang`');
		return true;
	}
    private function _installHookCustomer(){
		$hookspos = array(
				'megamenu',
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