{**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<table id="addresses-tab" cellspacing="0" cellpadding="0">
	<tr>
		<td width="50%">{if $delivery_address}<span class="bold">{l s='Delivery Address' d='Shop.Pdf' pdf='true'}</span><br/><br/>
	        
	       {* {$delivery_address}*}
	     
               

			{/if} 
			<span style="text-transform: uppercase;" >{$addresses.invoice->dni}<br/>	 
		        {$addresses.invoice->firstname} {$addresses.invoice->lastname}<br/>
			    {$addresses.invoice->address1}, {State::getNameById({$addresses.invoice->id_state})}<br/>
				{$addresses.invoice->city}<br/>
			    {$addresses.invoice->phone}</span>
		</td>
		<td width="50%">
{if $addresses.invoice->vat_number eq ''} {* {l s='Billing Address' d='Shop.Pdf' pdf='true'} *} {else} Datos para Facturacion <br/><br/> {/if}
			{*	{$invoice_address} *}
			<span style="text-transform: uppercase;" >{$addresses.invoice->vat_number}<br/>{$addresses.invoice->company}<br/>
			{$addresses.invoice->address2}</span>
		</td>
	</tr>
</table>
