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
 * BlogShop class
 */

require_once(_PS_CLASS_DIR_.'shop/Shop.php');

class BlogShop extends ShopCore
{
	/*
	 * Add sql join shop association
	 * @param $table
	 * @param $alias
	 * @param null $context
	 * @return string
	 */
	public static function addShopAssociation($table, $alias, $context = null)
	{
		if (is_null($context)) {
			$context = Context::getContext();
		}

		$table_alias = $table.'_shop';
		if (strpos($table, '.') !== false) {
			list($table_alias, $table) = explode('.', $table);
		}

		$sql = ' INNER JOIN '._DB_PREFIX_.$table.'_shop '.$table_alias.' ON ('.$table_alias.'.id_'.$table.' = '.$alias.'.id_'.$table;

		if (isset($context->shop->id)) {
			$sql .= ' AND '.$table_alias.'.id_shop = '.(int)$context->shop->id;
		} elseif (Shop::checkIdShopDefault($table)) {
			$sql .= ' AND '.$table_alias.'.id_shop = '.$alias.'.id_shop_default';
		} else {
			$sql .= ' AND '.$table_alias.'.id_shop IN ('.implode(', ', Shop::getContextListShopID()).')';
		}

		$sql .= ')';

		return $sql;
	}
}