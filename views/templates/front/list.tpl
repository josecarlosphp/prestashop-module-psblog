{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}

{extends file=$layout}

{block name='content'}

<div id="psblog">
    {if isset($notfound) && $notfound == true}
        <p class="warning">{l s='The page was not found' mod='psblog'}</p>
    {else}
        {capture name=path}
            <a title="{l s='Back home' mod='psblog'}" href="{$smarty.const.__PS_BASE_URI__|escape:'html':'UTF-8'}">{l s='Home' mod='psblog'}</a>
            {if !empty($navigationPipe)}<span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>{/if}
            {if isset($post_category) && $post_category->id != 1}
                <a href="{$listLink|escape:'html':'UTF-8'}">{$blog_title|escape:'html':'UTF-8'}</a>
                {if isset($parent_category)}
                    {if !empty($navigationPipe)}<span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>{/if}
                    <a href="{$parent_category_link|escape:'html':'UTF-8'}">{$parent_category->name|escape:'html':'UTF-8'}</a>
                {/if}
                {if !empty($navigationPipe)}<span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>{/if}
                {$post_category->name|escape:'html':'UTF-8'}
            {else}
                {$blog_title|escape:'html':'UTF-8'}
            {/if}
        {/capture}

        {if isset($post_category) && $post_category->id != 1}
            <div id="category_info">

                <h2 class="bt_left">{l s='Posts in category' mod='psblog'} "{$post_category->name|escape:'html':'UTF-8'}"</h2>

                {if $blog_conf.rss_active}
                    <p class="bt_right">
                        <a href="{$posts_rss_url|escape:'html':'UTF-8'}" title="RSS">
                            <img src="{$link->getMediaLink(_MODULE_DIR_|cat:"psblog/views/img/rss.png")|escape:'html':'UTF-8'}" alt="RSS"/>
                        </a>
                    </p>
                {/if}

                <div class="clear"></div>

                <div class="rte">{$post_category->description nofilter}</div>

                {if isset($subcategories) && $subcategories|@count > 0}
                    <p>
                        <span class="bold">{l s='Subcategories :' mod='psblog'}</span>
                        {foreach from=$subcategories item=subcat name=subcatlist}
                            <a href="{$subcat.link|escape:'html':'UTF-8'}">{$subcat.name|escape:'html':'UTF-8'}</a>{if !$smarty.foreach.subcatlist.last}, {/if}
                        {/foreach}
                    </p>
                {/if}

            </div>
        {else}
            <h2 class="bt_left">{l s='Posts' mod='psblog'}</h2>
            {if $blog_conf.rss_active}
                <p class="bt_right">
                    <a href="{$posts_rss_url|escape:'htmlall':'UTF-8'}" title="RSS"><img src="{$link->getMediaLink(_MODULE_DIR_|cat:"psblog/views/img/rss.png")|escape:'html':'UTF-8'}" alt="RSS"/></a>
                </p>
            {/if}
            <div class="clear"></div>
            <div class="rte">{$post_category->description nofilter}</div>
        {/if}
        <div class="clear"></div>
        {if isset($search_query)}
            {if isset($post_list) && $post_list|@count > 0}
                <h3>{l s='Results for' mod='psblog'} "{$search_query|escape:'html':'UTF-8'}"</h3>
            {/if}
        {/if}
        <div id="post_list">
            {if isset($post_list) && $post_list && $post_list|@count > 0}
                <ul>
                    {foreach from=$post_list item=post name=publications}
                        <li {if $smarty.foreach.publications.last} class="last_item" {elseif $smarty.foreach.publications.first} class="first_item" {/if}>
                            {if $post.default_img}
                                <div class="img_default">
                                    <a href="{$post.link|escape:'htmlall':'UTF-8'}">
                                        <img src="{$posts_img_path|escape:'htmlall':'UTF-8'}list/{$post.default_img_name|escape:'html':'UTF-8'}" width="{$blog_conf.img_list_width|intval}" alt="{$post.title|escape:'html':'UTF-8'}"/>
                                    </a>
                                </div>
                            {/if}
                            <div class="{if $post.default_img} detail_left {else} detail_large {/if}">
                                <h3><a href="{$post.link|escape:'html':'UTF-8'}">{$post.title|escape:'html':'UTF-8'}</a></h3>

                                <p>
                                    {if $blog_conf.list_display_date}
                                        <span>{dateFormat date=$post.date_on|escape:'html':'UTF-8' full=0}</span>{/if}
                                    {if $blog_conf.comment_active && $post.allow_comments && $post.nb_comments > 0}
                                        <span> - <a href="{$post.link|escape:'html':'UTF-8'}#postcomments">{$post.nb_comments|escape:'html':'UTF-8'} {if $post.nb_comments > 1}{l s='comments' mod='psblog'}{else}{l s='comment' mod='psblog'}{/if}</a></span>
                                    {/if}
                                </p>

                                <div class="excerpt">{$post.excerpt nofilter}</div>
                                <p>
                                    {if $blog_conf.category_active && isset($post.categories) && $post.categories|@count > 0}
                                        <span>{l s='Posted on' mod='psblog'}
                                            {foreach from=$post.categories item=post_category name=post_category_list}
                                                <a href="{$post_category.link|escape:'html':'UTF-8'}">{$post_category.name|escape:'html':'UTF-8'}</a>{if !$smarty.foreach.post_category_list.last},{/if}
                                            {/foreach}
                                        </span>
                                    {/if}
                                </p>
                            </div>
                            <div class="clear"></div>
                        </li>
                    {/foreach}
                </ul>
                {if isset($paginationLink)}
                    {if isset($back) && $back}
                        <a class="bt_right" href="{$paginationLink|escape:'html':'UTF-8'}{$curr_page+1|intval}">{l s='Previous articles' mod='psblog'} &raquo;</a>
                        &nbsp;&nbsp;&nbsp;{/if}
                    {if isset($next) && $next}
                    <a class="bt_left" href="{$paginationLink|escape:'html':'UTF-8'}{$curr_page-1|intval}">&laquo; {l s='Newest Articles' mod='psblog'}</a>{/if}
                {/if}
                <div class="clear"></div>
            {else}
                {if isset($search_query)}
                    <p class="warning">{l s='No results for' mod='psblog'} "{$search_query|escape:'html':'UTF-8'}"</p>
                {elseif isset($post_category)}
                    <p class="warning">{l s='There is no posts in this category' mod='psblog'}</p>
                {else}
                    <p class="warning">{l s='There is no posts' mod='psblog'}</p>
                {/if}
            {/if}
        </div>
    {/if}
</div>

{/block}