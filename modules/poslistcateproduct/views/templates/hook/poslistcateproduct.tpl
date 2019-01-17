{$count=0}
{foreach from=$productCates item=productCate name=poslistcateproduct}
	<div class="poslistcateproduct poslistcateproduct_{$count} product_container"
		data-items="{$slider_options.number_item}" 
		data-speed="{$slider_options.speed_slide}"
		data-autoplay="{$slider_options.auto_play}"
		data-time="{$slider_options.auto_time}"
		data-arrow="{$slider_options.show_arrow}"
		data-pagination="{$slider_options.show_pagination}"
		data-move="{$slider_options.move}"
		data-pausehover="{$slider_options.pausehover}"
		data-lg="{$slider_options.items_lg}"
		data-md="{$slider_options.items_md}"
		data-sm="{$slider_options.items_sm}"
		data-xs="{$slider_options.items_xs}"
		data-xxs="{$slider_options.items_xxs}">
		<div class="row">
			<div class=" col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col">
				<div class="pos_title">
					<div class="row">
						<div class=" col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col">
							<h2>
								<!-- {if $productCate.categorythumb}
								<img src="{$productCate.categorythumb}" alt=""/>
								{else}
								<img src="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`poslistcateproduct/images/")}menu_thumb.png"/>
								{/if} -->
								<span>{$productCate.category_name}</span>
							
							</h2>
						</div>
						<div class=" col-xs-12 col-sm-12 col-md-7 col-lg-8 col-xl-9 col">
							{if $productCate.list_subcategories}
							<ul class="subcategories-list hidden-md-down">
								{foreach from=$productCate.list_subcategories item=subcategories}
								<li><a href="{$link->getCategoryLink($subcategories['id_category'])}" target="_blank">{$subcategories.name}</a></li>
								{/foreach}
							</ul>
							<div class="btn-group hidden-lg-up">
								  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="material-icons">list</i>
								  </button>
								  <ul class="dropdown-menu">
								{foreach from=$productCate.list_subcategories item=subcategories}
									<li><a href="{$link->getCategoryLink($subcategories['id_category'])}" target="_blank">{$subcategories.name}</a><li>
								{/foreach}
								  </ul>
							</div>
							{/if}
						</div>
					</div>
				</div>
				<div class="listcateproduct-products">
					<div class="row pos-content">		
						<div class="listcateSlide owl-carousel">		
							{foreach from=$productCate.product item=product name=myLoop}					
								{if $smarty.foreach.myLoop.index % $slider_options.rows == 0 || $smarty.foreach.myLoop.first }
								<div class="item-product">
								{/if}
									{include file="catalog/_partials/miniatures/product.tpl" product=$product}
								{if $smarty.foreach.myLoop.iteration %  $slider_options.rows == 0 || $smarty.foreach.myLoop.last}
									</div>
								{/if}
							{/foreach}
						</div>
					</div>
				</div>
			</div>		
			<div class=" col-xs-12 col-sm-12 col-md-5 col-lg-4 col-xl-3 col">
				{if $productCate.image}
					<div class="banner-box">
						{if $productCate.url}<a href="{$productCate.url}">{/if}<img src="{$link->getMediaLink("`$smarty.const._MODULE_DIR_`poslistcateproduct/images/`$productCate.image|escape:'htmlall':'UTF-8'`")}" alt="" />{if $productCate.url}</a>{/if}
					</div>
				{/if}
			</div>
		</div>
		
	</div>
	{$count= $count+1}
{/foreach}
