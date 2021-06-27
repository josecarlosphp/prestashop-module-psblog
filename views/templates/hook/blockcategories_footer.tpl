{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
{if $post_categories && $post_categories|@count > 0}
    <section class="blog_{$block_type|escape:'html':'UTF-8'} footer-block col-xs-12 col-sm-2 posts_block_categories">
        <h4>{l s='Blog categories' mod='psblog'}</h4>
        <div class="block_content toggle-footer">
            <ul class="tree dynamized">
                {foreach from=$post_categories item=post_category name=post_category_list}
                    <li {if $smarty.foreach.post_category_list.last} class="last_item" {/if}>
                        <a href="{$post_category.link|escape:'html':'UTF-8'}">{$post_category.name|escape:'html':'UTF-8'}</a>
                        {if $blog_conf.block_display_subcategories || (isset($blog_category) &&  $blog_category == $post_category.id_blog_category)}
                            {if isset($post_category.subcategories) && $post_category.subcategories|@count > 0}
                                <ul>
                                    {foreach from=$post_category.subcategories item=sub_category name=sub_category_list}
                                        <li><a href="{$sub_category.link}">{$sub_category.name|escape:'html':'UTF-8'}</a></li>
                                    {/foreach}
                                </ul>
                            {/if}

                        {/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    </section>
{/if}
