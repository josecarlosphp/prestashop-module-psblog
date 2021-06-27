<?php
/**
 * 2010 - 2015 Appside
 *
 * Psblog module
 *
 * @author    Appside
 * @copyright Copyright (c) Appside
 * @license   Addons PrestaShop license
 *
 * BlogLink class
 */

require_once(_PS_CLASS_DIR_.'Link.php');

class BlogLink extends LinkCore
{
	/*
	 * @param $post_id
	 * @param $rewrite
	 * @param null $context
	 * @return string
	 */
	public static function linkPost($post_id, $rewrite, $context = null)
	{
		if (is_null($context) || !($context instanceof Context)) {
			$context = Context::getContext();
		}

		$languages = Language::getLanguages(true, $context->shop->id);
		$shop_url = self::getBaseURL();

		if (Configuration::get('PS_REWRITING_SETTINGS')) {
			$lang_rewrite = Psblog::getRewriteCode($context->language->id);
			$iso = (isset($context->language) && count($languages) > 1) ? $context->language->iso_code.'/' : '';

			if (is_null($rewrite) || trim($rewrite) == '') {
				$post = new BlogPost($post_id, (int)Configuration::get('PS_LANG_DEFAULT'));
				$rewrite = $post->link_rewrite;
			}

			return $shop_url.$iso.$lang_rewrite.'/'.(int)$post_id.'-'.$rewrite;
		} else {
			$param_lang = (isset($context->language) && count($languages) > 1) ? '&id_lang='.$context->language->id : '';
			return $shop_url.'index.php?fc=module&module=psblog&controller=posts&post='.(int)$post_id.$param_lang;
		}
	}


	public static function getBaseUrl($id_shop = null, $ssl = null)
	{
		static $force_ssl = null;
		$ssl_enable = Configuration::get('PS_SSL_ENABLED');
		if ($ssl === null) {
			if ($force_ssl === null)
				$force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
			$ssl = $force_ssl;
		}

		if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null)
			$shop = new Shop($id_shop);
		else
			$shop = Context::getContext()->shop;

		$base = (($ssl && $ssl_enable) ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);

		return $base.$shop->getBaseURI();
	}


	/*
	 * @return string
	 */
	public static function linkRss()
	{
		$context = Context::getContext();
		$languages = Language::getLanguages(true, $context->shop->id);
		$shop_url = self::getBaseURL();
		$rss_url = $shop_url.'modules/psblog/rss.php';

		if (isset($context->language) && count($languages) > 1) {
			$rss_url .= '?id_lang='.(int)$context->language->id;
		}
		return $rss_url;
	}

	/*
	 * @param null $p
	 * @param null $context
	 * @param array $params
	 * @return string
	 */
	public static function linkList($p = null, $context = null, $params = array())
	{
		require_once(_PS_MODULE_DIR_."psblog/classes/BlogCategory.php");
		return self::linkCategory(1, null, $p, $context, $params);
	}

	/*
	 * @param $category_id
	 * @param null $rewrite
	 * @param null $p
	 * @param null $context
	 * @param array $params
	 * @return string
	 */
	public static function linkCategory($category_id, $rewrite = null, $p = null, $context = null, $params = array())
	{
		if (is_null($context) || !($context instanceof Context)) {
			$context = Context::getContext();
		}

		$languages = Language::getLanguages(true, $context->shop->id);
		$shop_url = self::getBaseURL();

		if (!is_null($p)) {
			$params[] = 'p='.$p;
		}

		if (Configuration::get('PS_REWRITING_SETTINGS')) {

			$lang_rewrite = Psblog::getRewriteCode($context->language->id);
			$iso = (isset($context->language) && count($languages) > 1) ? $context->language->iso_code.'/' : '';
			$param_str = count($params) ? '?'.implode('&', $params) : '';

			if ($category_id == 1) {
				return $shop_url.$iso.$lang_rewrite.$param_str;
			} else {
				if (is_null($rewrite) || trim($rewrite) == '') {
					$category = new BlogCategory($category_id, (int)Configuration::get('PS_LANG_DEFAULT'));
					$rewrite = $category->link_rewrite;
				}

				return $shop_url.$iso.$lang_rewrite.'/category/'.$category_id.'-'.$rewrite.$param_str;
			}

		} else {

			$param_lang = (isset($context->language) && count($languages) > 1) ? '&id_lang='.$context->language->id : '';
			$param_str = count($params) ? '&'.implode('&', $params) : '';
			if ($category_id == 1) {
				return $shop_url.'index.php?fc=module&module=psblog&controller=posts'.$param_lang.$param_str;
			} else {
				return $shop_url.'index.php?fc=module&module=psblog&controller=posts&category='.$category_id.$param_lang.$param_str;
			}
		}
	}
}