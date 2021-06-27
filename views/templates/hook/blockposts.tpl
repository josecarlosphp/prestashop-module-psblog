{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<!-- Block psblog module -->
{if $posts_list && $posts_list|@count > 0}
    <div class="block posts_block blog_{$block_type|escape:'html':'UTF-8'}">

        <p class="title_block">
            {if $posts_conf.rss_active}<a href="{$posts_rss_url|escape:'html':'UTF-8'}" title="RSS">
                <img src="{$smarty.const._MODULE_DIR_|escape:'html':'UTF-8'}psblog/views/img/rss.png" alt="RSS"/></a>
            {/if}
            &nbsp; <a href="{$linkPosts|escape:'html':'UTF-8'}">{$posts_title|escape:'html':'UTF-8'}</a>
        </p>
            <div class="block_content">
            <ul>
                {foreach from=$posts_list item=post name=posts_list}
                    <li class="clearfix {if $smarty.foreach.posts_list.last}last_item{elseif $smarty.foreach.posts_list.first}first_item{/if}">

                        {if $post.default_img && $posts_conf.block_display_img}
                            <a class="post_img" href="{$post.link|escape:'html':'UTF-8'}">
                                <img src="{$posts_img_path}list/{$post.default_img_name|escape:'html':'UTF-8'}" width="{$posts_conf.img_block_width|escape:'html':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}"/>
                            </a>
                        {/if}

                        <div class="post_title">
                            <h5><a href="{$post.link}">{$post.title|escape:'html':'UTF-8'}</a></h5>
                            {if $posts_conf.block_display_date && $post.date_on != "" && $post.date_on != "0000-00-00"}
                                <span>{dateFormat date=$post.date_on|escape:'html':'UTF-8' full=0}</span>
                            {/if}
                        </div>

                        <div class="clear"></div>

                    </li>
                {/foreach}
            </ul>

            <p><a href="{$linkPosts|escape:'htmlall':'UTF-8'}" title="{l s='View all posts' mod='psblog'}">{l s='View all posts' mod='psblog'} &raquo;</a></p>

            </div>
    </div>
{/if}
<!-- /Block psblog module -->