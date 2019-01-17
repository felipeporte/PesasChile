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
