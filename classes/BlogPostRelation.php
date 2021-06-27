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
 * BlogPostRelation class
 */

class BlogPostRelation extends ObjectModel
{
	public $id_blog_post;
	public $key;
	public $value;

	public static $definition = array(
		'table' => 'blog_post_relation',
		'primary' => 'id_blog_post_relation',
		'fields' => array(
			'id_blog_post' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true),
			'key' => array(
				'type' => self::TYPE_STRING,
				'validate' => 'isGenericName',
				'required' => true,
				'size' => 24
			),
			'value' => array(
				'type' => self::TYPE_STRING,
				'validate' => 'isGenericName',
				'required' => true,
				'size' => 255
			),
		)
	);

	/*
	 * @param $id_blog_post
	 * @param $key
	 * @param $data
	 * @return bool
	 */
	public static function saveRelation($id_blog_post, $key, $data)
	{
		if (!is_numeric($id_blog_post) || empty($id_blog_post)) {
			return false;
		}

		self::cleanRelation($id_blog_post, $key);

		if (is_array($data) && count($data)) {
			foreach ($data as $value) {
				$row = array('id_blog_post' => (int)$id_blog_post, 'key' => pSQL($key), 'value' => pSQL($value));
				Db::getInstance()->insert('blog_post_relation', $row);
			}
		}
		return true;
	}

	/*
	 * @param $id_blog_post
	 * @param $key
	 * @return bool
	 */
	public static function cleanRelation($id_blog_post, $key)
	{
		if (!is_numeric($id_blog_post) || empty($id_blog_post)) {
			return false;
		}
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'blog_post_relation`
                                           WHERE `id_blog_post` = '.(int)$id_blog_post.' AND `key` = "'.pSQL($key).'"');
	}

	/*
	 * @param $id_blog_post
	 * @return bool
	 */
	public static function cleanPostRelations($id_blog_post)
	{
		if (!is_numeric($id_blog_post) || empty($id_blog_post)) {
			return false;
		}
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'blog_post_relation` WHERE `id_blog_post` = '.(int)$id_blog_post);
	}

	/*
	 * @param $key
	 * @param $id
	 * @return bool
	 */
	public static function cleanPostRelationKey($key, $id)
	{
		if (!is_numeric($id) || empty($id)) {
			return false;
		}
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'blog_post_relation` WHERE `key` = "'.pSQL($key).'" AND `value` = '.(int)$id);
	}

	/*
	 * @param $id_blog_post
	 * @param $key
	 * @return array
	 */
	public static function getRelation($id_blog_post, $key)
	{
		$values = array();
		if (!is_numeric($id_blog_post) || empty($id_blog_post)) {
			return $values;
		}

		$query = 'SELECT * FROM `'._DB_PREFIX_.'blog_post_relation` pr
                  WHERE pr.`key` = "'.pSQL($key).'" AND pr.`id_blog_post` = '.(int)$id_blog_post;

		$result = Db::getInstance()->ExecuteS($query);

		if (is_array($result) && count($result)) {
			foreach ($result as $row) {
				$values[] = $row['value'];
			}
		}
		return $values;
	}
}