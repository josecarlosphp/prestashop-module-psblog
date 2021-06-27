{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
{if $posts_list && $posts_list|@count > 0}
    <section class="posts_block blog_{$block_type|escape:'htmlall':'UTF-8'} footer-block col-xs-12 col-sm-3">
        <h4>
            {if $posts_conf.rss_active}
                <a href="{$posts_rss_url|escape:'htmlall':'UTF-8'}" title="RSS"><img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}psblog/views/img/rss.png" alt="RSS"/></a>
            {/if}
            &nbsp; <a href="{$linkPosts|escape:'htmlall':'UTF-8'}">{$posts_title|escape:'htmlall':'UTF-8'}</a>
        </h4>

        <div class="block_content toggle-footer">
            <ul class="tree dynamized">
                {foreach from=$posts_list item=post name=posts_list}
                    <li {if $smarty.foreach.posts_list.last} class="last_item" {/if}>
                        <h5><a href="{$post.link|escape:'htmlall':'UTF-8'}">{$post.title|escape:'htmlall':'UTF-8'}</a></h5>
                        {if $posts_conf.block_display_date && $post.date_on != "" && $post.date_on != "0000-00-00"}
                            <span>{dateFormat date=$post.date_on|escape:'html':'UTF-8' full=0}</span>
                        {/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    </section>
{/if}