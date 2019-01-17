
<div class="product-tabs-container-slider product_block_container">
	<div class="tabs_slider"> 
		<ul class="tabs_slider_inner">
		{$count=0}
		{foreach from=$productTabslider item=productTab name=posTabProduct}
			<li class="{$productTab.id} {if $smarty.foreach.posTabProduct.first}first_item{elseif $smarty.foreach.posTabProduct.last}last_item{else}{/if} item {if $count==0} active {/if}" rel="tab_{$productTab.id}"  >
				<span>{$productTab.name}</span>

			</li>
			{$count= $count+1}
		{/foreach}	
		</ul>
		<p class="desc-title">{l s='Typi non habent claritatem insitam est usus legentis in qui facit eorum claritatem, investigationes demonstraverunt lectores legere me lius quod legunt saepius.' d='Shop.Theme.Global'}</p>
	</div>
	{$rows= $config['POS_HOME_PRODUCTTAB_ROWS']}
	<div class="row pos_content"> 
	{$countss=0}
	{foreach from=$productTabslider item=productTab name=posTabProduct}
			<div id="tab_{$productTab.id}" class="tab_content">
				<div class="productTabContent{$countss} owl-carousel">
				{foreach from=$productTab.productInfo item=product name=myLoop}
				{if $smarty.foreach.myLoop.index % $rows == 0 || $smarty.foreach.myLoop.first }
						<div class="item-product">
					{/if}
					
						{include file="catalog/_partials/miniatures/product.tpl" product=$product}
						
					{if $smarty.foreach.myLoop.iteration % $rows == 0 || $smarty.foreach.myLoop.last  }
						</div>
					{/if}	
				{/foreach}
				</div>
			</div>
			{$countss= $countss+1}
	{/foreach}	
	</div>
</div>

