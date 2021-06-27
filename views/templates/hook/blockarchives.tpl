{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
{if $blog_archives && $blog_archives|@count > 0}
    <div class="posts_archives_block block blog_{$block_type|escape:'htmlall':'UTF-8'}">

        <p class="title_block">
            {if $blog_conf.rss_active}
            <a href="{$posts_rss_url|escape:'htmlall':'UTF-8'}" title="RSS"><img src="{$smarty.const._MODULE_DIR_|escape:'htmlall':'UTF-8'}psblog/views/img/rss.png" alt="RSS"/></a>
            {/if}
            &nbsp; {l s='Blog archives' mod='psblog'}
        </p>

        <div class="block_content list-block">
            <ul>
                {foreach from=$blog_archives key=y item=val name=blog_archive_years}

                {if isset($val.months) && $val.months|is_array && $val.months|@count > 0}

                {foreach from=$val.months key=m item=month name=blog_archive_months}
                    <li><a href="{$linkPosts}y={$y}&m={$m}" class="posts_month">{$month.name|escape:'html':'UTF-8'} ({$month.nb|intval})</a></li>
                {/foreach}

                {else}
                    <li><a href="{$linkPosts}y={$y}" class="posts_year">{$y} ({$val.nb|intval})</a>
                {/if}

                {/foreach}
            </ul>
        </div>

    </div>
{/if}