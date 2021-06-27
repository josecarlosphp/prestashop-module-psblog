{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}

{extends file="helpers/form/form.tpl"}

{block name="label"}
    {if $input.type == 'text_label' && !isset($customer)}
        {if isset($input.label)}<label class="control-label col-lg-3">{$input.label|escape:'htmlall':'UTF-8'} </label>{/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="field"}
    {if $input.type == 'text_label'}
        <div class="col-lg-3"><p class="form-control-static">{$fields_value[$input.name]|escape:'UTF-8'}</p></div>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}