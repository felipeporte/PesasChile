<!-- Block categories module -->
{if $blockCategTree != ''}
	<div class="ma-nav-mobile-container hidden-lg-up">
		<div class="pt_custommenu_mobile">
		<div class="navbar">
			<div id="navbar-inner" class="navbar-inner navbar-inactive">
				<a class="btn-navbar">{l s='Category' mod='posmegamenu'}</a>
				<ul id="pt_custommenu_itemmobile" class="tree {if $isDhtml}dhtml{/if}  mobilemenu nav-collapse collapse">
					{foreach from=$blockCategTree.children item=child name=blockCategTree}
						<li><a href="{$child.link}">{$child.name} </a>
						{if $child.children}
						<ul class="dhtml">
						{foreach from=$child.children item=child2 name=blockCategTree2}
							<li><a href="{$child2.link}">{$child2.name} </a>
								<ul>
								{foreach from=$child2.children item=child3 name=blockCategTree3}
									<li><a href="{$child3.link}">{$child3.name} </a>
								{/foreach}
								</ul>
						{/foreach}
						</ul>
						{/if}
						</li>
					{/foreach}
					    {if isset($cms_link)} 
							{foreach from=$cms_link item=cms_link1}
								<li class="cms">
									<a href="#" title="Contains Subs">{$cms_link1.title}</a>
								</li>
							{/foreach}
						{/if}
						{if isset($cms_cate)} 
							{foreach from=$cms_cate item=cms_cate1}
								<li>
									<a href="{$cms_cate1.link}" title="{$cms_cate1.title}">{$cms_cate1.title}</a>
								</li>
							{/foreach}
						{/if}
						{if isset($manufacture_link)} 
							{foreach from=$manufacture_link item=manufacture_link1}
								<li>
									<a href="{$manufacture_link1.link}" title="{$manufacture_link1.title}">{$manufacture_link1.title}</a>
								</li>
							{/foreach}
						{/if}
						{if isset($supply_link)} 
							{foreach from=$supply_link item=supply_link1}
								<li>
									<a href="{$supply_link1.link nofilter}}" title="{$supply_link1.title nofilter}">{$supply_link1.title}</a>
								</li>
							{/foreach}
						{/if}
						{if isset($custom_link)} 
							{foreach from=$custom_link item=custom_link1}
								<li>
									<a href="{$custom_link1->link}" title="{$custom_link1->title}">{$custom_link1.title}</a>
								</li>
							{/foreach}
						{/if}
						{if isset($product_link)} 
							{foreach from=$product_link item=product_link1}
								<li>
									<a href="{$product_link1->link}" title="{$product_link1->title}">{$product_link1.title}</a>
								</li>
							{/foreach}
						{/if}
						{if isset($all_man_link)} {$all_man_link} {/if}
						{if isset($all_sup_link)} {$all_sup_link} {/if}
				</ul>
			</div>
		</div>
		</div>
</div>
{/if}
<!-- /Block categories module -->
<div class="nav-container hidden-md-down">
	<div class="nav-inner">
		<div id="pt_custommenu" class="pt_custommenu">
		    {$megamenu nofilter}
		</div>
	</div>
</div>
<script type="text/javascript">
//<![CDATA[
var CUSTOMMENU_POPUP_EFFECT = {$effect};
var CUSTOMMENU_POPUP_TOP_OFFSET = {$top_offset};
//]]>
</script>