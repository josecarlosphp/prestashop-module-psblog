{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<div class="panel">
    {if isset($header)}{$header|escape:'UTF-8'}{/if}
    {if isset($nodes)}
        <ul id="{$id|escape:'htmlall':'UTF-8'}" class="tree">
            {$nodes|escape:'UTF-8'}
        </ul>
    {/if}
</div>
<script type="text/javascript">
    function checkAllAssociatedShops($tree) {
        $tree.find(":input[type=checkbox]").each(
                function () {
                    $(this).prop("checked", true);
                    $(this).parent().addClass("tree-selected");
                }
        );
    }

    function uncheckAllAssociatedShops($tree) {
        $tree.find(":input[type=checkbox]").each(
                function () {
                    $(this).prop("checked", false);
                    $(this).parent().removeClass("tree-selected");
                }
        );
    }

    $(document).ready(function () {
        $("#{$id|escape:'htmlall':'UTF-8'}").tree("expandAll");
        $("#{$id|escape:'htmlall':'UTF-8'}").find(":input[type=checkbox]").click(
                function () {
                    if ($(this).is(':checked')) {
                        $(this).parent().addClass("tree-selected");
                        $(this).parent().parent().find("ul").find(":input[type=checkbox]").each(
                                function () {
                                    $(this).prop("checked", true);
                                    $(this).parent().addClass("tree-selected");
                                }
                        );
                    }
                    else {
                        $(this).parent().removeClass("tree-selected");
                        $(this).parent().parent().find("ul").find(":input[type=checkbox]").each(
                                function () {
                                    $(this).prop("checked", false);
                                    $(this).parent().removeClass("tree-selected");
                                }
                        );
                    }
                }
        );

        {if isset($selected_shops)}
        {assign var=imploded_selected_shops value='","'|implode:$selected_shops}
        var selected_shops = new Array("{$imploded_selected_shops|escape:'UTF-8'}");

        $("#{$id|escape:'htmlall':'UTF-8'}").find(".tree-item :input").each(
                function () {
                    if ($.inArray($(this).val(), selected_shops) != -1) {
                        $(this).prop("checked", true);
                        $(this).parent().addClass("tree-selected");
                        $(this).parents("ul.tree").each(
                                function () {
                                    $(this).children().children().children(".icon-folder-close")
                                            .removeClass("icon-folder-close")
                                            .addClass("icon-folder-open");
                                    $(this).show();
                                }
                        );
                    }
                }
        );
        {/if}
    });
</script>