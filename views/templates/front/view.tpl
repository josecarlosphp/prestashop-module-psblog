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

    {if $post->status == 'published'}

        {capture name=path}
            <a title="{l s='Back home' mod='psblog'}" href="{$smarty.const.__PS_BASE_URI__|escape:'html':'UTF-8'}">{l s='Home' mod='psblog'}</a>
            {if !empty($navigationPipe)}<span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>{/if}
            <a href="{$listLink|escape:'html':'UTF-8'}">{$blog_title|escape:'html':'UTF-8'}</a>
            {if $post->status == 'published'}
                {if !empty($navigationPipe)}<span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>{/if}
                {$post->title|escape:'html':'UTF-8'}
            {/if}
        {/capture}
        <h2>{$post->title|escape:'html':'UTF-8'} {if $blog_conf.view_display_date}{/if}</h2>
        {if $post->date_on != "" && $post->date_on != "0000-00-00"}
            <p>
                <span>{l s='Published on' mod='psblog'} {dateFormat date=$post->date_on|escape:'html':'UTF-8' full=0}</span>
            </p>
        {/if}
        <div id="post_view">
            <div class="rte">
                {if $post_images || $post->default_img}
                    <div class="medias">
                        <div class="media_list">
                            <ul>
                            {if $post_images}
                                {foreach from=$post_images item=img name=images}
                                <li>
                                {if $blog_conf.view_display_popin}<a href="{$posts_img_path|escape:'html':'UTF-8'}{$img.img_name|escape:'html':'UTF-8'}" rel="post" class="postpopin">{/if}
                                    <img src="{$posts_img_path|escape:'html':'UTF-8'}thumb/{$img.img_name|escape:'html':'UTF-8'}" width="{$blog_conf.img_width|intval}" alt="{$post->title|intval}"/>
                                {if $blog_conf.view_display_popin}</a>{/if}
                                </li>
                                {/foreach}
                            {/if}
                            </ul>
                            <div class="clear"></div>
                        </div>
                    </div>
                {/if}

                {$post->content nofilter}

            </div>

            <div class="clear"></div>

            {if $blog_conf.category_active}
                {if $post_categories|@count > 0}
                    <div class="categories">
                        <ul>
                            <li><strong>{l s='Posted on' mod='psblog'}</strong></li>
                            {foreach from=$post_categories item=category name=category_list}
                                <li>
                                    <a href="{$category.link|escape:'html':'UTF-8'}">{$category.name|escape:'html':'UTF-8'}</a>{if !$smarty.foreach.category_list.last},{/if}
                                </li>
                            {/foreach}
                        </ul>
                        <div class="clear"></div>
                    </div>
                {/if}
            {/if}

            {if $blog_conf.share_active}
                <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
                    <a class="addthis_button_email"></a>
                    <a class="addthis_button_facebook"></a>
                    <a class="addthis_button_twitter"></a>
                    <a class="addthis_button_pinterest_share"></a>
                    <a class="addthis_button_google_plusone_share"></a>
                </div>
                <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4debecd65fac86f2"></script>
            {/if}

            {if $blog_conf.related_active && $post_related|@count > 0}
                <div class="related">
                    <h3>{l s='Related blog posts' mod='psblog'}</h3>
                    <ul>
                        {foreach from=$post_related item=related_post name=related_list}
                            <li>&raquo; <a href="{$related_post.link|escape:'html':'UTF-8'}">{$related_post.title|escape:'html':'UTF-8'}</a></li>
                        {/foreach}
                    </ul>
                </div>
            {/if}

            {if $blog_conf.product_active}
                {if $post_products|@count > 0}
                    <div class="products">
                        <h3>{l s='Post related products' mod='psblog'}</h3>

                        <ul>
                            {foreach from=$post_products item=relatedProduct name=related_product_list}
                                {cycle values='odd,even' assign='alternate'}
                                <li class="{if $smarty.foreach.related_product_list.first}first_item{elseif $smarty.foreach.related_product_list.last}last_item{else}item{/if} {$alternate|escape:'html':'UTF-8'}">
                                    {if isset($relatedProduct.id_image) && $relatedProduct.id_image}
                                        <a href="{$relatedProduct.link|escape:'html':'UTF-8'}" class="content_img">
                                            <img src="{$relatedProduct.imageLink|escape:'html':'UTF-8'}" class="img-responsive" alt="{$relatedProduct.name|escape:'htmlall':'UTF-8'}" />
                                        </a>
                                    {/if}

                                    <h5>
                                        <a href="{$relatedProduct.link|escape:'html':'UTF-8'}">{$relatedProduct.name|truncate:25:'...':true|escape:'htmlall':'UTF-8'}</a>
                                        {if $relatedProduct.reference != ''}
                                            <span>{$relatedProduct.reference|escape:'html':'UTF-8'}</span>
                                        {/if}
                                    </h5>

                                    <p><a href="{$relatedProduct.link|escape:'html':'UTF-8'}">{$relatedProduct.description_short|strip_tags|truncate:58:'...'|escape:'html':'UTF-8'}</a></p>

                                    <div class="clear"></div>
                                </li>
                            {/foreach}
                        </ul>

                        <div class="clear"></div>
                    </div>
                {/if}
            {/if}

            {if $blog_conf.comment_active && $post->allow_comments}
                <div class="comments" id="postcomments">
                    <h3>{l s='COMMENTS' mod='psblog'}</h3>
                    {if isset($psblog_confirmation)}
                        <p class="confirmation">
                            {if !$blog_conf.comment_moderate}
                                {l s='Comment posted successfully' mod='psblog'}
                            {else}
                                {l s='Comment posted, awaiting moderator validation' mod='psblog'}
                            {/if}
                        </p>
                    {/if}

                    {if isset($psblog_error) && $psblog_error == 'error_input'}
                        <div class="alert alert-danger">
                            <p>{l s='Your comment has not been sent, name' mod='psblog'}
                                ({$blog_conf.comment_name_min_length|intval} {l s='chars min' mod='psblog'}
                                ) {l s='and comment are required.' mod='psblog'}</p>
                        </div>
                    {/if}

                    {if isset($psblog_error) && $psblog_error == 'error_already'}
                        <p class="alert alert-danger">{l s='This message have been already sent' mod='psblog'}</p>
                    {/if}

                    {if isset($psblog_error) && $psblog_error == 'error_input_invalid'}
                        <p class="alert alert-danger">{l s='HTML tags are not allowed' mod='psblog'}</p>
                    {/if}

                    {if isset($psblog_error) && $psblog_error == 'error_delay'}
                        <p class="alert alert-danger">{$blog_conf.comment_min_time|intval} {l s='seconds required between every comment' mod='psblog'}</p>
                    {/if}

                    {if isset($psblog_error) && $psblog_error == 'antispam_invalid'}
                        <p class="alert alert-danger">{l s='Security question response is not valid' mod='psblog'}</p>
                    {/if}

                    <div class="sheets">

                        {if isset($post_comments) && $post_comments|@count > 0}
                            <table class="table_block" style="width: 100%">
                                <thead>
                                <tr>
                                    <th class="first_item" style="width:100px;">{l s='From' mod='psblog'}</th>
                                    <th class="item">{l s='Comment' mod='psblog'}</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach from=$post_comments item=com name=comments_list}
                                    <tr>
                                        <td>
                                            <span class="bold">{$com.customer_name|escape:'html':'UTF-8'}</span>
                                            <br/><span>{dateFormat date=$com.date_add|escape:'html':'UTF-8' full=0}</span>
                                        </td>
                                        <td><p>{$com.content|nl2br|escape:'html':'UTF-8'}</p></td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        {else}
                            <p>{l s='No customer comments for the moment.' mod='psblog'}</p>
                        {/if}

                    </div>

                    {if !isset($psblog_confirmation)}
                        <div class="box">
                            {if $logged == false && !$blog_conf.comment_guest}
                                <p class="align_center">{l s='Only registered users can post a new comment.' mod='psblog'}</p>
                            {else}
                                <form id="sendComment" class="std" method="post" action="#postcomments">

                                    <h4>{l s='Add a comment' mod='psblog'}</h4>

                                    <div class="form-group">
                                        <label for="blog_comment">{l s='Name:' mod='psblog'}</label>
                                        <input type="text" class="form-control" name="customer_name"
                                               value="{if isset($comment)}{$comment->customer_name|escape:'html':'UTF-8'}{else}{$customerName|escape:'html':'UTF-8'}{/if}"/>
                                    </div>

                                    <div class="form-group">
                                        <label for="blog_comment">{l s='Comment:' mod='psblog'}</label>
                                        <textarea class="form-control" id="blog_comment" name="blog_comment" rows="5"
                                                  cols="46">{if isset($comment)}{$comment->content|escape:'html':'UTF-8'}{/if}</textarea>
                                    </div>

                                    {if $blog_conf.antispam}
                                        <div class="form-group">
                                            <label for="blog_spam">{l s='Security Question:' mod='psblog'} {$blog_spam_operation|escape:'html':'UTF-8'}</label>
                                            <input type="text" class="form-control antispam" id="blog_spam_answer" name="blog_spam_answer"/>
                                            <input type="hidden" name="blog_spam_key" value="{$blog_spam_key|escape:'html':'UTF-8'}"/>
                                        </div>
                                    {/if}

                                    <div class="form-group">
                                        <button class="btn btn-default button button-medium" type="submit" name="submitMessage">
                                            <span>{l s='Send' mod='psblog'}<i class="icon-chevron-right right"></i></span>
                                        </button>
                                    </div>

                                </form>
                            {/if}
                        </div>
                    {/if}
                </div>
            {/if}

        </div>
    {else}
        <p class="warning">{l s='This post is not available' mod='psblog'}</p>
    {/if}
</div>

{/block}