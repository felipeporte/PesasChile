<!-- Block categories module -->
{if $blockCategTree != ''}
	<div class="ma-nav-mobile-container hidden-desktop">
		<div class="navbar">
			<div id="navbar-inner" class="navbar-inner navbar-inactive">
				<a class="btn btn-navbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<span class="brand">{l s='Category'}</span>
				<ul id="ma-mobilemenu" class="tree   mobilemenu nav-collapse collapse">
					{foreach from=$blockCategTree.children item=child name=blockCategTree}
						{if $smarty.foreach.blockCategTree.last}
							{include file="$branche_tpl_path" node=$child last='true'}
						{else}
							{include file="$branche_tpl_path" node=$child}
						{/if}
					{/foreach}
				</ul>
			</div>
		</div>
</div>
{/if}
<!-- /Block categories module -->
