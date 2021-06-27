{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
<div class="post_search_block block blog_{$block_type|escape:'html':'UTF-8'}">

    <p class="title_block">{l s='Search in Blog' mod='psblog'}</p>

    <form action="{$linkPosts|escape:'htmlall':'UTF-8'}" id="psblogsearch" method="get">

        {if !$rewrite}
            <input type="hidden" name="fc" value="module">
            <input type="hidden" name="module" value="psblog">
            <input type="hidden" name="controller" value="posts">
        {/if}

        <div class="block_content">

            {if isset($search_query_nb) && $search_query_nb > 0}
                <p class="results">
                    <a href="{$linkPosts|escape:'htmlall':'UTF-8'}?search={$search_query|escape:'htmlall':'UTF-8'}">{$search_query_nb|escape:'htmlall':'UTF-8'} {l s='Result for the term' mod='psblog'}
                        <br/> "{$search_query|escape:'html':'UTF-8'}"</a>
                </p>
            {/if}

            <div class="form-group">
                <input type="text" class="form-control" name="search"
                       value="{if isset($search_query)}{$search_query|htmlentities:$ENT_QUOTES:'utf-8'|stripslashes}{/if}"/>
                <button class="btn btn-default button button-small" type="submit">
                    <span>{l s='go' mod='psblog'}</span>
                </button>
            </div>
        </div>
    </form>

</div>