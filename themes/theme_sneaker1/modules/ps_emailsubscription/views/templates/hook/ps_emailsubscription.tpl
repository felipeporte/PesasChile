<div class="ft_newsletter">
	<p class="text">{l s='Join our' d='Shop.Theme.Global'}</p>
	<h2>{l s='Newsletters now!' d='Shop.Theme.Global'}</h2>
	 {if $conditions}
	  <span class="desc">{$conditions}</span>
	{/if}
	<form action="{$urls.pages.index}#footer" method="post">
		<div class="input-wrapper">
		  <input
			name="email"
			class="input_txt"
			type="text"
			value="{$value}"
			placeholder="{l s='Your email address' d='Shop.Forms.Labels'}"
		  >
		</div>
		<button
		  class="btn btn-primary"
		  name="submitNewsletter"
		  type="submit"
		><span>{l s='Subscribe !' d='Shop.Theme.Global'}</span></button>
		<input type="hidden" name="action" value="0">
	</form>
	  {if $msg}
		<p class="alert {if $nw_error}alert-danger{else}alert-success{/if}">
		  {$msg}
		</p>
	 {/if}
</div>