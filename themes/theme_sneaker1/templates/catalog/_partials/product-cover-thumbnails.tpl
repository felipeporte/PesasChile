<div class="images-container">
  {block name='product_cover'}
    <div class="product-cover">
      <img class="js-qv-product-cover" src="{$product.cover.bySize.medium_default.url}" alt="{$product.cover.legend}" title="{$product.cover.legend}" style="width:100%;" itemprop="image">
      <div class="layer hidden-sm-down" data-toggle="modal" data-target="#product-modal">
        <i class="material-icons zoom-in">&#xE8FF;</i>
      </div>
    </div>
  {/block}

  {block name='product_images'}
    <div class="js-qv-mask mask pos_content">
      <div class="product-images js-qv-product-images  owl-carousel">
        {foreach from=$product.images item=image}
          <div class="thumb-container">
            <img
              class="thumb js-thumb {if $image.id_image == $product.cover.id_image} selected {/if}"
              data-image-medium-src="{$image.bySize.medium_default.url}"
              data-image-large-src="{$image.bySize.medium_default.url}"
              src="{$image.bySize.small_default.url}"
              alt="{$image.legend}"
              title="{$image.legend}"
              width="100"
              itemprop="image"
            >
          </div>
        {/foreach}
      </div>
    </div>
  {/block}
</div>
{hook h='displayAfterProductThumbs'}
<script type="text/javascript"> 
		$(document).ready(function() {
			var owl = $("#product .images-container .product-images");
			owl.owlCarousel({
				autoPlay : false ,
				smartSpeed: 1000,
				autoplayHoverPause: true,
				nav: true,
				dots : false,	
				responsive:{
					0:{
						items:2,
					},
					480:{
						items:3,
					},
					768:{
						items:2,
						nav:false,
					},
					992:{
						items:3,
					},
					1200:{
						items:3,
					}
				}
			}); 
			var owl = $(".quickview .images-container .product-images");
			owl.owlCarousel({
				autoPlay : false ,
				smartSpeed: 1000,
				autoplayHoverPause: true,
				nav: true,
				dots : false,	
				responsive:{
					0:{
						items:2,
					},
					480:{
						items:3,
					},
					768:{
						items:2,
						nav:false,
					},
					992:{
						items:3,
					},
					1200:{
						items:3,
					}
				}
			}); 
		});
</script>

