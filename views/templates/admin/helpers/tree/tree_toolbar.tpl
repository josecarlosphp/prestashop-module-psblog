{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<div class="tree-actions pull-right">
    {if isset($actions)}
        {foreach from=$actions item=action}
            {$action->render()|escape:'UTF-8'}
        {/foreach}
    {/if}
</div>