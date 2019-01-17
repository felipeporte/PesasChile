
<div class="pos-special-products " 
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
	{if $title}
	<div class="pos_title">
		 <h2>
			<span>{$title}</span>
		</h2>	
		<p class="desc-title"><span>{l s='Browse the collection of our special products.'  d='Shop.Theme.Global'}</span></p>
	</div>
	{/if}
	{$rows= $slider_options.rows}
	<div class="row">
		<!-- <div class="col-lg-3 col-sm-12 col-md-4 col-xs-12">
			<div class="banner-box">
				<a href="{$image_link}"><img class="img-responsive" src="{$banner_img|escape:'htmlall':'UTF-8'}" alt="" title=""/></a>
			</div>
		</div>	 -->
		<div class="special-products">
			<div class="pos_content">
				<div class="special-item owl-carousel">
				{foreach from=$products item=product name=myLoop}
					{if $smarty.foreach.myLoop.index % $rows == 0 || $smarty.foreach.myLoop.first }
					<div class="item-product">
					{/if}			
						<article class="js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
							<div class="img_block">
							{block name='product_thumbnail'}
								<a href="{$product.url}" class="thumbnail product-thumbnail">
								  <img
									src = "{$product.cover.bySize.home_default.url}"
									alt = "{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
									data-full-size-image-url = "{$product.cover.large.url}"
								  >
								  {hook h="rotatorImg" product=$product}	
								</a>
							  {/block}
								{block name='product_flags'}
								<ul class="product-flag">
									{if $product.show_price}
									   {if $product.has_discount}
										   {if $product.discount_type === 'percentage'}
											  <li class="discount-percentage">{$product.discount_percentage} off</li>
											{/if}
										{/if}
									{/if}
									{foreach from=$product.flags item=flag}
										<li class=" {$flag.type}">{$flag.label}</li>
									{/foreach}
								</ul>
							  {/block}
								<div class="countdown" >
									{hook h='timecountdown' product=$product }
									<span 	id="future_date_{$product.id_category_default}_{$product.id_product}"
									class="id_countdown"></span>
									<div class="clearfix"></div>
								</div>
							</div>
							<div class="product_desc">
								<div class="desc_info">
									{block name='product_reviews'}
										<div class="hook-reviews">
										{hook h='displayProductListReviews' product=$product}
										</div>
									{/block}
									{block name='product_name'}
										<h4><a href="{$product.url}" title="{$product.name}" itemprop="name" class="product_name">{$product.name|truncate:30:'...'}</a></h4>
									{/block}
									{if isset($product.id_manufacturer)}
									<div class="manufacturer"><a href="{$link->getManufacturerLink($product.id_manufacturer)}">{$product.manufacturer_name|strip_tags:'UTF-8'|escape:'html':'UTF-8'}</a></div>
									{/if}
									 {block name='product_price_and_shipping'}
									  {if $product.show_price}
										<div class="product-price-and-shipping">
										   <span itemprop="price" class="price {if $product.has_discount} price_sale {/if}">{$product.price}</span>
										  {if $product.has_discount}
											{hook h='displayProductPriceBlock' product=$product type="old_price"}

											<span class="sr-only">{l s='Regular price' d='Shop.Theme.Catalog'}</span>
											<span class="regular-price">{$product.regular_price}</span>
											{if $product.discount_type === 'percentage'}
											  <span class="discount-percentage">{$product.discount_percentage}</span>
											{/if}
										  {/if}

										  {hook h='displayProductPriceBlock' product=$product type="before_price"}

										  <span class="sr-only">{l s='Price' d='Shop.Theme.Catalog'}</span>
									  

										  {hook h='displayProductPriceBlock' product=$product type='unit_price'}

										  {hook h='displayProductPriceBlock' product=$product type='weight'}
										</div>
									  {/if}
									{/block}
							
									{block name='product_description_short'}
										<div class="product-desc" itemprop="description">{$product.description_short nofilter}</div>
									{/block}
									{block name='product_variants'}
										{if $product.main_variants}
										  {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
										{/if}
									{/block}
								</div>	
								<div class="actions">
									<ul class="add-to-links">
										<li class="cart">
											{include file='catalog/_partials/customize/button-cart.tpl' product=$product}
										</li>
										<li>
											<a href="#" class="quick-view" data-link-action="quickview" title="{l s='Quick view' d='Shop.Theme.Actions'}">{l s='Quick view' d='Shop.Theme.Actions'}</a>
										</li>
										<li>
											<a href="{$product.url}" class="links-details" title="{l s='Details' d='Shop.Theme.Actions'}">{l s='Details' d='Shop.Theme.Actions'}</a>
										</li>
									</ul>
								</div>
							</div>
						</article>

					{if $smarty.foreach.myLoop.iteration % $rows == 0 || $smarty.foreach.myLoop.last  }
					</div>
					{/if}
				{/foreach}
				</div>
			</div>
		</div>
	</div>
</div>
