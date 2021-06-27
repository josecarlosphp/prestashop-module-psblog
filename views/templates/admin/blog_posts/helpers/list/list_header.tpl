{*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 *}

{extends file="helpers/list/list_header.tpl"}

{block name="override_header"}

    {if $comments_active}
        <a href="{$blog_comments_link|escape:'html':'UTF-8'}">{$l.comments_waiting|escape:'html':'UTF-8'}</a>
        <a href="{$blog_comments_link|escape:'html':'UTF-8'}" style="color:#008000;"> : <strong>{$nb_comments|escape:'html':'UTF-8'}</strong></a>
    {/if}
    &nbsp;&nbsp;
    {$l.number_of_articles|escape:'html':'UTF-8'} :
    <strong><span style="color:#008000;">{$post_nb|escape:'html':'UTF-8'}</span></strong>
    &nbsp;&nbsp;
    {$l.published_articles|escape:'html':'UTF-8'} :
    <strong><span style="color:#008000;">{$post_nb_active|escape:'html':'UTF-8'}</span></strong>
    &nbsp;&nbsp;
    <span><a href="{$blog_conf_link|escape:'html':'UTF-8'}">{$l.configuration|escape:'html':'UTF-8'}</a></span>
    <br/>
    <br/>
{/block}