{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}

{extends file="helpers/form/form.tpl"}

{block name="label"}

    {if $input.type == 'blog_medias'}
    {else}
        {$smarty.block.parent}
    {/if}

{/block}

{block name="field"}

    {if $input.type == 'blog_checkbox'}
        <div class="col-lg-{if isset($input.col)}{$input.col|intval}{else}9{/if} {if !isset($input.label)}col-lg-offset-3{/if}">
            {foreach $input.values.query as $value}
                <div class="checkbox">
                    {assign var=id value=$value.{$input.values.id}}
                    <label for="{$input.name|cat:$id|escape:'html':'UTF-8'}" class="t">
                        <input type="checkbox" name="{$input.name|cat:'[]'}" id="{$input.name|cat:$id|intval}"
                               class="{if isset($input.class)}{$input.class}{/if}" value="{$id|intval}"
                               {if isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && in_array($id,$fields_value[$input.name])}checked="checked"{/if} />
                        {$value[$input.values.name]|escape:'html':'UTF-8'}</label>
                </div>
            {/foreach}
        </div>
    {elseif $input.type == 'blog_checkbox_table'}
        <div class="well margin-form" style="height:150px; overflow-y: auto;">
            {if !empty($input.values.query)}
                <table class="table" cellspacing="0">
                    <thead>
                    <tr>
                        <th style="width:30px;">
                            <input type="checkbox" name="checkAll" onclick="checkDelBoxes(this.form,'{$input.name|cat:'[]'|escape:'html':'UTF-8'}', this.checked)"/>
                        </th>
                        <th style="width:30px;">{l s='ID' mod='psblog'}</th>
                        <th style="width:5px;"></th>
                        <th style="width:200px;">{l s='Name' mod='psblog'}</th>
                        <th style="width:50px;">{l s='Lang' mod='psblog'}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $input.values.query as $value}
                        {assign var=id value=$value.{$input.values.id}}
                        <tr>
                            <td>
                                <input type="checkbox" name="{$input.name|cat:'[]'}" id="{$input.name|cat:$id|escape:'html':'UTF-8'}"
                                       value="{$id|intval}"
                                       {if isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && in_array($id,$fields_value[$input.name])}checked="checked"{/if} />
                            </td>
                            <td>{$value.{$input.id|escape:'html':'UTF-8'}|intval}</td>
                            <td colspan="2">{$value[$input.values.name]|escape:'html':'UTF-8'}</td>
                            <td>{$value.iso_code|escape:'html':'UTF-8'}</td>
                        </tr>
                        {if isset($value.subcategories) && !empty($value.subcategories)}
                            {foreach $value.subcategories as $sub}
                                {assign var=sub_id value=$sub.{$input.values.id}}
                                <tr>
                                    <td>
                                        <input type="checkbox" name="{$input.name|cat:'[]'|escape:'html':'UTF-8'}"
                                               id="{$input.name|cat:$sub_id|escape:'html':'UTF-8'}"
                                               value="{$sub_id|intval}"
                                               {if isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && in_array($sub_id,$fields_value[$input.name])}checked="checked"{/if} />
                                    </td>
                                    <td>{$sub.{$input.id|escape:'html':'UTF-8'}|intval}</td>
                                    <td>-</td>
                                    <td>{$sub[$input.values.name]|escape:'html':'UTF-8'}</td>
                                    <td>{$sub.iso_code|escape:'html':'UTF-8'}</td>
                                </tr>
                            {/foreach}
                        {/if}


                    {/foreach}
                    </tbody>
                </table>
            {else}
                {l s='No items' mod='psblog'}
            {/if}
        </div>
    {elseif $input.type == 'blog_accessories'}

        {assign var="accessories" value=$input.accessories}
        <div class="col-lg-5">
            <input type="hidden" name="inputAccessories" id="inputAccessories" value="{foreach from=$accessories item=accessory}{$accessory.id_product|intval}-{/foreach}"/>

            <div id="ajax_choose_product">
                <div class="input-group">
                    <input type="text" value="" id="product_autocomplete_input"/>
                    <span class="input-group-addon"><i class="icon-search"></i></span>
                </div>
            </div>

            <div id="divAccessories">
                {foreach from=$accessories item=accessory}
                    <div class="form-control-static">
                        <button type="button" class="btn btn-default delAccessory" name="{$accessory.id_product|intval}">
                            <i class="icon-remove text-danger"></i>
                        </button>
                        {$accessory.name|escape:'html':'UTF-8'} {if !empty($accessory.reference)}({$accessory.reference|escape:'html':'UTF-8'}){/if}
                    </div>
                {/foreach}
            </div>
        </div>
    {elseif $input.type == 'blog_medias'}
        <div class="dummyfile input-group">

            <input type="file" accept="gif|jpg|png" maxlength="0" id="{$input.id|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}"/> .jpg, .png,
            .gif

            <div id="blog-img-list" style="margin:10px 0;"></div>

            {if !empty($input.images)}
                <table class="table" cellpadding="0" cellspacing="0" style="min-width:700px;">
                    <thead>
                    <th>{l s='Image' mod='psblog'}</th>
                    <th>{l s='Default' mod='psblog'}</th>
                    <th>{l s='Position' mod='psblog'}</th>
                    <th>{l s='Delete' mod='psblog'}</th>
                    </thead>
                    <tbody>
                    {foreach from=$input.images item=img}
                        <tr>
                            <td><img src="{$input.img_path}{$img.img_name|escape:'html':'UTF-8'}" alt="{$img.img_name|escape:'html':'UTF-8'}"
                                     style="max-height:200px;"/></td>
                            <td><input type="radio" id="blog_img_default_{$img.id_blog_image|intval}" name="blog_img_default"
                                       {if $img.default == 1}checked="checked"{/if} value="{$img.id_blog_image|intval}"/></td>
                            <td><input type="text" name="img_pos[{$img.id_blog_image|intval}]" value="{$img.position|intval}"
                                       size="2"/></td>
                            <td style="width:70px; text-align:center">
                                <a href="{$input.link_delete|escape:'html':'UTF-8'}{$img.id_blog_image|intval}"
                                   onclick="if(!confirm('{l s='Are you sure you want to delete this picture ?' mod='psblog'}')) return false;">
                                    <img src="../img/admin/delete.gif" alt="{l s='Delete' mod='psblog'}"/></a></td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {/if}

        </div>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}

{block name="input"}
    {if $input.name == "link_rewrite"}
        <script type="text/javascript">
            {if isset($PS_ALLOW_ACCENTED_CHARS_URL) && $PS_ALLOW_ACCENTED_CHARS_URL}
            var PS_ALLOW_ACCENTED_CHARS_URL = 1;
            {else}
            var PS_ALLOW_ACCENTED_CHARS_URL = 0;
            {/if}
        </script>
        {$smarty.block.parent}

    {elseif $input.type == 'blog_date'}
        <div class="row">
            <div class="input-group col-lg-4">
                <input id="{if isset($input.id)}{$input.id}{else}{$input.name|escape:'html':'UTF-8'}{/if}"
                       type="text" data-hex="true"
                       {if isset($input.class)}class="{$input.class|escape:'html':'UTF-8'}"
                       {else}class="blog_datepicker"{/if} name="{$input.name|escape:'html':'UTF-8'}"
                       value="{$fields_value[$input.name]|escape:'html':'UTF-8'}"/>
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="after"}
    <script type="text/javascript">

        <!-- core rewrite -->

        var module_dir = '{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}';
        var id_language = {$defaultFormLanguage|intval};
        var languages = new Array();
        var vat_number = {if $vat_number}1{else}0{/if};
        // Multilang field setup must happen before document is ready so that calls to displayFlags() to avoid
        // precedence conflicts with other document.ready() blocks
        {foreach $languages as $k => $language}
            languages[{$k|intval}] = {
                id_lang: {$language.id_lang|intval},
                iso_code: '{$language.iso_code|escape:'html':'UTF-8'}',
                name: '{$language.name|escape:'html':'UTF-8'}',
                is_default: '{$language.is_default|intval}'
            };
        {/foreach}
        // we need allowEmployeeFormLang var in ajax request
        allowEmployeeFormLang = {$allowEmployeeFormLang|intval};
        displayFlags(languages, id_language, allowEmployeeFormLang);

        <!-- end core rewrite -->

        var lang_remove = '<button type="button" class="btn btn-default"><i class="icon-remove text-danger"></i></button>';
        var lang_denied = '{l s='You cannot select a' mod='psblog'} $ext {l s='file, try again with another type..' mod='psblog'}';
        var ajax_product_link = '{$ajax_product_link|escape:'UTF-8'}';

        {literal}
            if ($('#divAccessories').length) {
                initAccessoriesAutocomplete();
                $('#divAccessories').delegate('.delAccessory', 'click', function () {
                    delAccessory($(this));
                });
            }
            if ($(".blog_datepicker").length > 0)
                $(".blog_datepicker").datepicker({prevText: '', nextText: '', dateFormat: 'yy-mm-dd'});

            $('#blog_img').MultiFile({STRING: {remove: lang_remove, denied: lang_denied}, list: '#blog-img-list'});
        {/literal}

        {$smarty.block.parent}
    </script>
{/block}

