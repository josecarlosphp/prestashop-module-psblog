{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<div class="tree-panel-heading-controls clearfix">
    {if isset($title)}<i class="icon-tag"></i>&nbsp;{l s=$title mod='psblog'}{/if}
    {if isset($toolbar)}{$toolbar|escape:'UTF-8'}{/if}
</div>