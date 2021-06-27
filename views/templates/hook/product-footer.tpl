{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}
{if $post_product_list && $post_product_list|@count > 0}
    <h3 class="page-product-heading">{l s='Blog' mod='psblog'}</h3>
    <ul class="psblog_posts">
        {foreach from=$post_product_list item=postProduct}
            <li><a href="{$postProduct.link|escape:'htmlall':'UTF-8'}">{$postProduct.title|escape:'html':'UTF-8'}</a></li>
        {/foreach}
    </ul>
{/if}

