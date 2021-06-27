{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<li class="tree-folder">
	<span class="tree-folder-name{if isset($node['disabled']) && $node['disabled'] == true} tree-folder-name-disable{/if}">
		<input type="checkbox" name="checkBoxShopGroupAsso_{$table|escape:'htmlall':'UTF-8'}[{$node['id']|intval}]"
               value="{$node['id']|intval}"{if isset($node['disabled']) && $node['disabled'] == true} disabled="disabled"{/if} />
		<i class="icon-folder-close"></i>
		<label class="tree-toggler">{l s='Group' mod='psblog'} : {$node['name']|escape:'htmlall':'UTF-8'}</label>
	</span>
    <ul class="tree">
        {$children|escape:'UTF-8'}
    </ul>
</li>