{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- pos search module TOP -->
<div id="pos_search_top" class="col-lg-9 col-md-8 col-sm-12 col-xs-12">
	<form method="get" action="{$search_controller_url}" id="searchbox" class="form-inline form_search"  data-search-controller-url="{$url_search|escape:'html'}">
		<label for="pos_query_top"><!-- image on background --></label>
        <input type="hidden" name="controller" value="search">  
		<div class="pos_search form-group">
             {if $cate_on == 1}
                <select class="bootstrap-select" name="poscats">
					<option value="0"><!-- Forzar traduccion no funciona por BO {l s='All categories' mod='blocksearch_mod'}--> General</option>
						{foreach from=$categories_option.children item=child name=categories_option}
							{include file="modules/possearchproducts/category-tree-branch.tpl" node=$child main=true}
						{/foreach}
				</select>
            {/if} 
        </div>
		<input type="text" name="s" value="{$search_string}" placeholder="{l s='Escriba lo que busca ... ' mod='possearchcategories'}" id="pos_query_top" class="search_query form-control ac_input" >
		<button type="submit" class="btn btn-default search_submit">
			<span><!-- Forzar traduccion no funciona por BO{l s='Search' mod='blocksearch_mod'}--> {l s='Buscar' mod='blocksearch_mod'}</span>
		</button>
    </form>
</div>

<!-- /pos search module TOP -->
