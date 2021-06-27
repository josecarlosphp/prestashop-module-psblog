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
 * AdminBlogController tab for admin panel
 */

class AdminBlogController extends AdminController
{
	/*
	 * Search ajax related products for query
	 * @throws PrestaShopDatabaseException
	 * @return String
	 */
	public function displayAjaxGetproductlinks()
	{
		$query = Tools::getValue('q', false);
		if (!$query || $query == '' || Tools::strlen($query) < 1)
			die();

		/*
		 * In the SQL request the "q" param is used entirely to match result in database.
		 * In this way if string:"(ref : #ref_pattern#)" is displayed on the return list,
		 * they are no return values just because string:"(ref : #ref_pattern#)"
		 * is not write in the name field of the product.
		 * So the ref pattern will be cut for the search request.
		 */
		if ($pos = strpos($query, ' (ref:'))
			$query = Tools::substr($query, 0, $pos);

		$excludeIds = Tools::getValue('excludeIds', false);
		if ($excludeIds && $excludeIds != 'NaN')
			$excludeIds = implode(',', array_map('intval', explode(',', $excludeIds)));
		else
			$excludeIds = '';

		// Excluding downloadable products from packs because download from pack is not supported
		$excludeVirtuals = (bool)Tools::getValue('excludeVirtuals', false);
		$exclude_packs = (bool)Tools::getValue('exclude_packs', false);

		$sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, MAX(image_shop.`id_image`) id_image, il.`legend`
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = '.(int)Context::getContext()->language->id.Shop::addSqlRestrictionOnLang('pl').')
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)' .
			Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)Context::getContext()->language->id.')
		WHERE (pl.name LIKE \'%'.pSQL($query).'%\' OR p.reference LIKE \'%'.pSQL($query).'%\')' .
			(!empty($excludeIds) ? ' AND p.id_product NOT IN ('.$excludeIds.') ' : ' ') .
			($excludeVirtuals ? 'AND p.id_product NOT IN (SELECT pd.id_product FROM `'._DB_PREFIX_.'product_download` pd WHERE (pd.id_product = p.id_product))' : '') .
			($exclude_packs ? 'AND (p.cache_is_pack IS NULL OR p.cache_is_pack = 0)' : '') .
			' GROUP BY p.id_product';

		$items = Db::getInstance()->executeS($sql);

		if ($items)
			foreach ($items as $item)
				echo trim($item['name']).(!empty($item['reference']) ? ' (ref: '.$item['reference'].')' : '').'|'.(int)($item['id_product'])."\n";
	}
}