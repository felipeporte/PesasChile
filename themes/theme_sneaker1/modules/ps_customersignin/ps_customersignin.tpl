{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<div class="user-info-block dropdown js-dropdown">
<span class="user-text current" data-toggle="dropdown">{l s='Setting' d='Shop.Theme.Customeraccount'}</span>
<ul class="dropdown-menu">
	<li><a href="{$my_account_url}" rel="nofollow">{l s='My account' d='Shop.Theme.Customeraccount'}</a></li>
	<li><a href="{$link->getPageLink("cart", true)|escape:"html":"UTF-8"}">{l s='Checkout' d='Shop.Theme.Actions'}</a>
{if $logged}
  <a
	class="logout hidden-sm-down"
	href="{$logout_url}"
	rel="nofollow"
  >
	{l s='Sign out' d='Shop.Theme.Actions'}
  </a></li>
  <li><a
	class="account"
	href="{$my_account_url}"
	title="{l s='View my customer account' d='Shop.Theme.Customeraccount'}"
	rel="nofollow"
  >
	<i class="material-icons hidden-md-up logged">&#xE7FF;</i>
	<span class="">{$customerName}</span>
  </a></li>
{else}
  <li><a
	href="{$my_account_url}"
	title="{l s='Log in to your customer account' d='Shop.Theme.Customeraccount'}"
	rel="nofollow"
  >
	<span class="">{l s='Sign in' d='Shop.Theme.Actions'}</span>
  </a></li>
{/if}
</ul>
</div>

