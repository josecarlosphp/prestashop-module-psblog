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
 * BlogCategory class
 */

require_once(_PS_MODULE_DIR_.'psblog/classes/BlogShop.php');
require_once(_PS_MODULE_DIR_.'psblog/classes/BlogCategoryRelation.php');
require_once(_PS_MODULE_DIR_.'psblog/psblog.php');

class BlogCategory extends ObjectModel
{
	public $name;
	public $description;
	public $link_rewrite;
	public $meta_description;
	public $meta_keywords;
	public $active = 1;
	public $position;
	public $parent;
	public $id_blog_category_parent = 0;
	public static $definition = array(
		'table' => 'blog_category',
		'primary' => 'id_blog_category',
		'multilang' => true,
		'multishop' => true,
		'fields' => array(
			'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'meta_description' => array(
				'type' => self::TYPE_STRING,
				'validate' => 'isGenericName',
				'size' => 255,
				'lang' => true
			),
			'meta_keywords' => array(
				'type' => self::TYPE_STRING,
				'validate' => 'isGenericName',
				'size' => 255,
				'lang' => true
			),
			'link_rewrite' => array(
				'type' => self::TYPE_STRING,
				'validate' => 'isLinkRewrite',
				'size' => 128,
				'lang' => true
			),
			'position' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_blog_category_parent' => array(
				'type' => self::TYPE_INT,
				'validate' => 'isUnsignedInt',
				'required' => false
			),
			'name' => array(
				'type' => self::TYPE_STRING,
				'validate' => 'isGenericName',
				'required' => true,
				'size' => 255,
				'lang' => true
			),
			'description' => array(
				'type' => self::TYPE_HTML,
				'validate' => 'isString',
				'size' => 3999999999999,
				'lang' => true
			)
		)
	);

	/*
	 * @param null $id
	 * @param null $id_lang
	 * @param null $id_shop
	 */
	public function __construct($id = null, $id_lang = null, $id_shop = null)
	{
		Shop::addTableAssociation('blog_category', array('type' => 'shop'));
		parent::__construct($id, $id_lang, $id_shop);
	}

	/*
	 * @param bool $autodate
	 * @param bool $nullValues
	 * @return bool
	 */
	public function add($autodate = true, $nullValues = false)
	{
		return parent::add($autodate, true);
	}

	/*
	 * @return bool
	 */
	public function delete()
	{
		BlogCategoryRelation::cleanCategoryRelations($this->id);
		BlogPostRelation::cleanPostRelationKey('category', $this->id);
		return parent::delete();
	}

	/*
	 * @return bool
	 */
	public function isAllowed()
	{
		$context = Context::getContext();
		$id_lang = $context->language->id;
		$id_shop = $context->shop->id;
		$category_langs = $this->getLangs(true);
		$category_groups = $this->getGroups();
		$current_groups = FrontController::getCurrentCustomerGroups();

		$group_found = false;
		if (empty($current_groups)) {
			$current_groups[] = (int)Group::getCurrent()->id;
		}
		foreach ($current_groups as $group) {
			if (in_array($group, $category_groups)) {
				$group_found = true;
			}
		}

		if (!$this->active || !$group_found || !in_array($id_lang, $category_langs) || !$this->isAssociatedToShop($id_shop)) {
			return false;
		}

		return true;
	}

	/*
	 * @param $category_id
	 * @param $currentContext
	 * @param bool $active
	 * @return array
	 */
	public static function getSubCategories($category_id, $currentContext, $active = true)
	{
		$context = null;
		if ($currentContext instanceof Context) {
			$context = $currentContext;
		} elseif (is_bool($currentContext) && $currentContext === true) {
			$context = Context::getContext();
		}

		$id_lang = (is_null($context) || !isset($context->language)) ? Context::getContext()->language->id : $context->language->id;

		$query = 'SELECT DISTINCT(c.id_blog_category), cl.name, cl.link_rewrite, c.position, c.id_blog_category_parent,
                    (SELECT GROUP_CONCAT(l.iso_code) FROM '._DB_PREFIX_.'blog_category_relation crl
                                   LEFT JOIN `'._DB_PREFIX_.'lang` l ON (crl.`key` = "lang" AND l.`id_lang`= crl.`value`)
                                   WHERE  crl.id_blog_category = c.id_blog_category) as iso_code
                                   FROM  '._DB_PREFIX_.'blog_category c ';

		$query .= ' LEFT JOIN `'._DB_PREFIX_.'blog_category_lang` cl ON cl.`id_blog_category` = c.`id_blog_category` ';

		if ($context) {
			$query .= BlogShop::addShopAssociation('blog_category', 'c', $context);

			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '='.(int)Group::getCurrent()->id);

			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_category_relation` cr2 ON (cr2.`id_blog_category` = c.`id_blog_category` AND cr2.`key` = "group" AND cr2.`value` '.$sql_groups.') ';
		}

		if ($context && isset($context->language)) {
			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_category_relation` cr ON (cr.`id_blog_category` = c.`id_blog_category` AND cr.`key` = "lang" AND cr.`value` = "'.(int)$context->language->id.'") ';
		}

		$query .= ' WHERE cl.`id_lang` = '.(int)$id_lang.' AND c.id_blog_category != 1 ';

		if ($active) {
			$query .= ' AND c.`active` = 1';
		}

		$query .= ' AND c.`id_blog_category_parent` =  '.(int)$category_id.' ';
		$query .= ' GROUP BY c.`id_blog_category` ORDER BY c.`position` ASC, cl.`name` ASC';
		$result = Db::getInstance()->ExecuteS($query);

		if (!$result) {
			return array();
		}

		$i = 0;
		foreach ($result as $val) {
			$result[$i]['link'] = BlogLink::linkCategory($val['id_blog_category'], $val['link_rewrite']);
			$i++;
		}

		return $result;
	}

	/*
	 * @param $currentContext
	 * @param bool $active
	 * @param bool $onlyParents
	 * @param bool $count
	 * @param null $exclude_defaults
	 * @return array
	 */
	public static function listCategories($currentContext, $active = true, $onlyParents = false, $count = false, $exclude_defaults = null)
	{
		if (!is_null($exclude_defaults)) {
			$exclude_defaults = (array)$exclude_defaults;
		}

		$context = null;
		if ($currentContext instanceof Context) {
			$context = $currentContext;
		} elseif (is_bool($currentContext) && $currentContext === true) {
			$context = Context::getContext();
		}

		$id_lang = (is_null($context) || !isset($context->language)) ? Context::getContext()->language->id : $context->language->id;

		if ($count) {
			$select = ' COUNT(DISTINCT(c.`id_blog_category`)) as nb ';
		} else {
			$select = ' DISTINCT(c.id_blog_category), c.id_blog_category, cl.name, cl.link_rewrite, c.position, c.id_blog_category_parent,
                        (SELECT GROUP_CONCAT(l.iso_code) FROM '._DB_PREFIX_.'blog_category_relation crl
                        LEFT JOIN `'._DB_PREFIX_.'lang` l ON (crl.`key` = "lang" AND l.`id_lang`= crl.`value`)
                        WHERE  crl.id_blog_category = c.id_blog_category) as iso_code ';
		}

		$query = 'SELECT '.$select.' FROM  '._DB_PREFIX_.'blog_category c ';
		$query .= ' LEFT JOIN `'._DB_PREFIX_.'blog_category_lang` cl ON cl.`id_blog_category` = c.`id_blog_category` ';

		if ($context) {
			$query .= BlogShop::addShopAssociation('blog_category', 'c', $context);
			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '='.(int)Group::getCurrent()->id);
			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_category_relation` cr2 ON (cr2.`id_blog_category` = c.`id_blog_category` AND cr2.`key` = "group" AND cr2.`value` '.$sql_groups.') ';
			if (isset($context->language)) {
				$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_category_relation` cr ON (cr.`id_blog_category` = c.`id_blog_category` AND cr.`key` = "lang" AND cr.`value` = "'.(int)$context->language->id.'") ';
			}
		}

		$query .= ' WHERE cl.`id_lang` = '.(int)$id_lang;

		if (!is_null($exclude_defaults) && is_array($exclude_defaults)) {
			$query .= '  AND c.id_blog_category NOT IN ('.implode(',', $exclude_defaults).') ';
		}

		if ($active) {
			$query .= ' AND c.`active` = 1';
		}

		if ($onlyParents) {
			$query .= ' AND (c.`id_blog_category_parent` IS NULL OR c.`id_blog_category_parent` = 0) ';
		}

		if ($count) {
			$result = Db::getInstance()->getRow($query);
			return $result['nb'];
		} else {

			$query .= ' GROUP BY c.`id_blog_category` ORDER BY c.`position` ASC, cl.`name` ASC ';
			$result = Db::getInstance()->ExecuteS($query);
			if (!$result) {
				return array();
			}

			$i = 0;
			foreach ($result as $val) {
				$result[$i]['link'] = BlogLink::linkCategory($val['id_blog_category'], $val['link_rewrite']);
				$result[$i]['subcategories'] = BlogCategory::getSubCategories(
					$val['id_blog_category'],
					$currentContext,
					true
				);
				$i++;
			}
			return $result;
		}
	}

	/*
	 * @param bool $checkContext
	 * @param bool $publish
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public function getPosts($checkContext = true, $publish = true, $start = 0, $limit = 5)
	{
		return BlogPost::listPosts($checkContext, $publish, $start, $limit, false, $this->id);
	}

	/*
	 * @param bool $checkContext
	 * @param bool $publish
	 * @return array
	 */
	public function nbPosts($checkContext = true, $publish = true)
	{
		return BlogPost::listPosts($checkContext, $publish, null, null, true, $this->id);
	}

	/*
	 * @param null $id_lang
	 * @param null $except_id
	 * @return array
	 */
	public static function getParents($id_lang = null, $except_id = null)
	{
		if (is_null($id_lang)) {
			$id_lang = Context::getContext()->language->id;
		}

		$query = "SELECT * FROM `"._DB_PREFIX_."blog_category` c
                    LEFT JOIN `"._DB_PREFIX_."blog_category_lang` l ON l.`id_blog_category` = c.`id_blog_category`
                    WHERE l.`id_lang` = ".(int)$id_lang."
                    AND c.`id_blog_category` != 1
                    AND c.`id_blog_category` != 2
                    AND (c.`id_blog_category_parent` IS NULL OR c.`id_blog_category_parent` = 0)";

		if (!is_null($except_id) && is_numeric($except_id)) {
			$query .= ' AND c.`id_blog_category` != '.(int)$except_id;
		}

		$result = Db::getInstance()->ExecuteS($query);

		$list = array();
		foreach ($result as $category) {
			$list[] = array('name' => $category['name'], 'id' => $category['id_blog_category']);
		}
		return $list;
	}

	/*
	 * @param bool $onlyIds
	 * @return array
	 */
	public function getLangs($onlyIds = false)
	{
		$query = 'SELECT l.* FROM `'._DB_PREFIX_.'blog_category_relation` cr
                    INNER JOIN `'._DB_PREFIX_.'lang` l ON (cr.`key` = "lang" AND l.`id_lang`= cr.`value`)
                    WHERE cr.`id_blog_category` = '.(int)$this->id;

		$result = Db::getInstance()->ExecuteS($query);

		if ($result && $onlyIds) {
			$resultIds = array();
			foreach ($result as $group) {
				$resultIds[] = $group['id_lang'];
			}

			return $resultIds;
		}

		return $result;
	}

	/*
	 * @param null $id_lang
	 * @return bool
	 */
	public function isAssociatedToLang($id_lang = null)
	{
		if ($id_lang === null) {
			$id_lang = Context::getContext()->language->id;
		}

		$query = 'SELECT l.* FROM `'._DB_PREFIX_.'blog_category_relation` cr
			      INNER JOIN `'._DB_PREFIX_.'lang` l ON (cr.`key` = "lang" AND l.`id_lang`= cr.`value`)
                  WHERE cr.`id_blog_category` = '.(int)$this->id.' AND cr.`value` = "'.(int)$id_lang.'"';

		return (bool)Db::getInstance()->getValue($query);
	}

	/*
	 * @return array
	 */
	public function getGroups()
	{
		return BlogCategoryRelation::getRelation($this->id, 'group');
	}
}
