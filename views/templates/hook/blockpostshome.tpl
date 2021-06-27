{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<!-- Block psblog module -->
{if $posts_list && $posts_list|@count > 0}
    <div class="clear"></div>
    <div id="posts_home" class="block">
        <h4>{$posts_title|escape:'htmlall':'UTF-8'}
            <span>
                <a href="{$linkPosts|escape:'htmlall':'UTF-8'}">{l s='All posts' mod='psblog'}</a>
                &nbsp;
                {if $posts_conf.rss_active}
                    <a href="{$posts_rss_url|escape:'htmlall':'UTF-8'}" title="RSS">
                        <img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}psblog/views/img/rss.png" alt="RSS"/>
                    </a>
                {/if}
            </span>
        </h4>

        <div class="block_content">
            <ul>
                {foreach from=$posts_list item=post name=posts_list}
                    <li class="{if $smarty.foreach.posts_list.last}last_item{elseif $smarty.foreach.posts_list.first}first_item{/if}">

                        {if $post.default_img}
                            <div class="img_default">
                                <a href="{$post.link|escape:'html':'UTF-8'}">
                                    <img src="{$posts_img_path|escape:'htmlall':'UTF-8'}list/{$post.default_img_name|escape:'html':'UTF-8'}" width="{$posts_conf.img_list_width|escape:'htmlall':'UTF-8'}" alt="{$post.title|escape:'html':'UTF-8'}" />
                                </a>
                            </div>
                        {/if}

                        <div class="{if $post.default_img} detail_left {else} detail_large {/if}">
                            <h3><a href="{$post.link}">{$post.title|escape:'html':'UTF-8'}</a></h3>
                            {if $posts_conf.block_display_date}
                                <span>{dateFormat date=$post.date_on|escape:'html':'UTF-8' full=0}</span>{/if}
                            <div class="excerpt">{$post.excerpt|strip_tags:'UTF-8'|truncate:152:'...'}</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
{/if}
<!-- /Block psblog module -->