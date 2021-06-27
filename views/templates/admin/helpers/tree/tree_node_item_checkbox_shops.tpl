{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<li class="tree-item{if isset($node['disabled']) && $node['disabled'] == true} tree-item-disable{/if}">
    <label class="tree-item-name">
        <input type="checkbox" name="checkBoxShopAsso_{$table|escape:'htmlall':'UTF-8'}[{$node['id_shop']|intval}]"
               value="{$node['id_shop']}"{if isset($node['disabled']) && $node['disabled'] == true} disabled="disabled"{/if} />
        <i class="tree-dot"></i>
        {$node['name']|escape:'htmlall':'UTF-8'}
    </label>
</li>