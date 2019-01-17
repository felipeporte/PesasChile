<div class="footer-container">
{if $page.page_name == 'index'}
	<div class=" footer-top">
		<div class="container-fluid">
			{hook h='displayFooterBefore'}
		</div>
	</div>
{/if}
<div class="footer-middle">
	<div class="container-fluid">
	  <div class="row">	
			<div class="col-sm-12 col-md-12 col-lg-4 col-xs-12">
				{hook h='displayFooterAfter'}
			</div>
			<div class="col-sm-12 col-md-12 col-lg-8 col-xs-12">
				<div class="row">
				{hook h='displayFooter'}
				</div>
			</div>
	  </div>
	</div>
</div>
<div class="footer-bottom">
	<div class="container-fluid">
		<div class="row">	
		{hook h='displayBlockFooter2'}
		</div>
	</div>
</div>
</div>