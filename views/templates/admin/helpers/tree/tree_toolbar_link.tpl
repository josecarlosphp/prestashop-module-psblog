{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<a href="{$link}"{if isset($action)} onclick="{$action|escape:'htmlall':'UTF-8'}"{/if} {if isset($id)} id="{$id|escape:'htmlall':'UTF-8'}"{/if} class="btn btn-default">
    {if isset($icon_class)}<i class="{$icon_class|escape:'htmlall':'UTF-8'}"></i>{/if}
    {l s=$label mod='psblog'}
</a>