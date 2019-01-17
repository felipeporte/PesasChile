
<table id="addresses-tab" cellspacing="0" cellpadding="0">
	<tr>
		<td width="50%">{if $delivery_address}<span class="bold">{l s='Delivery Address' d='Shop.Pdf' pdf='true'}</span><br/><br/>
				{$delivery_address}
			{/if}
		</td>
		<td width="50%"><span class="bold">Datos para Facturacion {* {l s='Billing Address' d='Shop.Pdf' pdf='true'} *}</span><br/><br/>
			{*	{$invoice_address} *}
		</td>
	</tr>
</table>
