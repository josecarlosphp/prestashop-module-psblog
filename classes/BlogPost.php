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
 * BlogPost class
 */

require_once(_PS_MODULE_DIR_.'psblog/classes/BlogShop.php');
require_once(_PS_MODULE_DIR_.'psblog/classes/BlogPostRelation.php');
require_once(_PS_MODULE_DIR_.'psblog/classes/BlogComment.php');
require_once(_PS_MODULE_DIR_.'psblog/psblog.php');

class BlogPost extends ObjectModel
{
	public $title;
	public $content;
	public $link_rewrite;
	public $meta_description;
	public $meta_keywords;
	public $excerpt;
	public $status;
	public $date_on;
	public $allow_comments;
	public $default_img;

	public static $definition = array(
		'table' => 'blog_post',
		'primary' => 'id_blog_post',
		'multilang' => true,
		'multishop' => true,
		'fields' => array(
			'status' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
			'allow_comments' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'date_on' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
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
			'title' => array(
				'type' => self::TYPE_STRING,
				'validate' => 'isGenericName',
				'size' => 128,
				'required' => true,
				'lang' => true
			),
			'excerpt' => array(
				'type' => self::TYPE_HTML,
				'validate' => 'isString',
				'size' => 3999999999999,
				'lang' => true
			),
			'content' => array(
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
		Shop::addTableAssociation('blog_post', array('type' => 'shop'));
		parent::__construct($id, $id_lang, $id_shop);
		if ($this->id) {
			$this->default_img = $this->getDefaultImage();
		}
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
		$this->cleanImages();
		$this->cleanComments();
		BlogPostRelation::cleanPostRelations($this->id);
		return parent::delete();
	}

	/*
	 * Get posts
	 * @param $currentContext
	 * @param bool $publish
	 * @param int $start
	 * @param int $limit
	 * @param bool $count
	 * @param null $category_id
	 * @param null $product_id
	 * @param null $except_id
	 * @param null $year
	 * @param null $month
	 * @return array
	 */
	public static function listPosts($currentContext, $publish = true, $start = 0, $limit = 5, $count = false, $category_id = null, $product_id = null, $except_id = null, $year = null, $month = null)
	{
		$context = null;
		if ($currentContext instanceof Context) {
			$context = $currentContext;
		} elseif (is_bool($currentContext) && $currentContext === true) {
			$context = Context::getContext();
		}

		$id_lang = (is_null($context) || !isset($context->language)) ? Context::getContext()->language->id : $context->language->id;

		if ($count) {
			$select = ' COUNT(DISTINCT(p.`id_blog_post`)) as nb ';
		} else {
			$select = ' DISTINCT(p.id_blog_post), pl.title, pl.excerpt, pl.link_rewrite, p.date_on, p.allow_comments,
                       pi.id_blog_image as default_img, pi.img_name as default_img_name, COUNT(bc.id_blog_comment) as nb_comments,
                        (SELECT GROUP_CONCAT(l.iso_code) FROM '._DB_PREFIX_.'blog_post_relation cpl
                             LEFT JOIN `'._DB_PREFIX_.'lang` l ON (cpl.`key` = "lang" AND l.`id_lang`= cpl.`value`)
                             WHERE  cpl.id_blog_post = p.id_blog_post) as iso_code ';
		}

		$query = 'SELECT '.$select.' FROM  '._DB_PREFIX_.'blog_post p
                  LEFT JOIN `'._DB_PREFIX_.'blog_post_lang` pl ON pl.`id_blog_post` = p.`id_blog_post` ';

		if ($context) {
			$query .= BlogShop::addShopAssociation('blog_post', 'p', $context);
		}

		if (!$count) {
			$query .= ' LEFT JOIN '._DB_PREFIX_.'blog_image pi ON p.id_blog_post = pi.id_blog_post AND pi.default = 1
                        LEFT JOIN '._DB_PREFIX_.'blog_comment bc ON p.id_blog_post = bc.id_blog_post AND bc.active = '.BlogComment::APPROVED_STATUS;
			if ($context) {
				if (isset($context->language)) {
					$query .= ' AND bc.id_lang = '.$context->language->id;
				}
				$query .= ' AND bc.id_shop = '.$context->shop->id;
			}
		}

		if ($context && isset($context->language)) {
			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_post_relation` pr ON pr.`id_blog_post` = p.`id_blog_post` AND pr.`key` = "lang" AND pr.`value` = "'.$context->language->id.'"';
		}

		if (!is_null($category_id)) {
			$query .= ' INNER JOIN '._DB_PREFIX_.'blog_post_relation pr2 ON pr2.id_blog_post = p.id_blog_post AND  pr2.`key` = "category" AND pr2.`value` = "'.$category_id.'"';
		}

		if (!is_null($product_id)) {
			$query .= ' INNER JOIN '._DB_PREFIX_.'blog_post_relation pr3 ON pr3.`id_blog_post` = p.`id_blog_post` AND  pr3.`key` = "product" AND pr3.`value` = "'.$product_id.'"';
		}

		$query .= ' WHERE pl.`id_lang` = '.$id_lang;

		if (!is_null($except_id) && is_numeric($except_id)) {
			$query .= ' AND p.id_blog_post != '.$except_id;
		}

		if ($publish) {
			$query .= ' AND p.status = "published" AND NOW() >= p.date_on';
		}

		if (!is_null($year) && is_numeric($year)) {
			$query .= ' AND YEAR(p.`date_on`) = '.$year;
		}

		if (!is_null($month) && is_numeric($month)) {
			$query .= ' AND (MONTH(p.`date_on`) = '.$month.' AND YEAR(p.`date_on`) = '.date('Y').') ';
		}

		if ($count) {
			$result = Db::getInstance()->getRow($query);
			return $result['nb'];
		} else {
			$query .= ' GROUP BY p.id_blog_post ';
			$query .= ' ORDER BY p.date_on DESC, p.id_blog_post DESC ';
			if (!is_null($limit)) {
				$query .= ' LIMIT '.$start.','.$limit;
			}

			$result = Db::getInstance()->ExecuteS($query);

			if ($result) {
				$i = 0;
				foreach ($result as $val) {
					$result[$i]['link'] = BlogLink::linkPost($val['id_blog_post'], $val['link_rewrite']);
					$i++;
				}
			}
			return $result;
		}
	}

	/*
	 * @return array
	 */
	public static function getArchives()
	{
		$context = Context::getContext();
		$query = 'SELECT YEAR(p.date_on) as `year`, MONTH(p.date_on) as `month`, COUNT(p.`id_blog_post`) as nb FROM  '._DB_PREFIX_.'blog_post p ';

		if ($context) {
			$query .= BlogShop::addShopAssociation('blog_post', 'p', $context);
		}

		if ($context && isset($context->language)) {
			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_post_relation` pr ON pr.`id_blog_post` = p.`id_blog_post` AND pr.`key` = "lang" AND pr.`value` = "'.$context->language->id.'"';
		}

		$query .= ' WHERE 1 ';
		$query .= ' AND p.status = "published" AND NOW() >= p.date_on';
		$query .= ' GROUP BY `year`,`month` ';
		$query .= ' ORDER BY p.date_on DESC, p.id_blog_post DESC ';
		$result = Db::getInstance()->ExecuteS($query);
		return $result;
	}

	/*
	 * @param $search_query
	 * @param bool $checkContext
	 * @param bool $publish
	 * @param bool $count
	 * @param int $start
	 * @param int $limit
	 * @param null $category_id
	 * @return array
	 */
	public static function searchPosts($search_query, $checkContext = true, $publish = true, $count = false, $start = 0, $limit = 5, $category_id = null)
	{
		$context = null;
		if ($checkContext) {
			$context = Context::getContext();
			$id_shop = $context->shop->id;
		}

		$id_lang = (is_null($context) || !isset($context->language)) ? Context::getContext()->language->id : $context->language->id;

		if ($count) {
			$select = ' COUNT(DISTINCT(p.`id_blog_post`)) as nb ';
		} else {
			$select = ' DISTINCT(p.id_blog_post), pl.title, pl.excerpt, pl.link_rewrite, pl.meta_description,
                       p.date_on, pi.id_blog_image as default_img, pi.img_name as default_img_name, 
                       p.allow_comments, COUNT(bc.id_blog_comment) as nb_comments  ';
		}

		$query = 'SELECT '.$select.' FROM  '._DB_PREFIX_.'blog_post p
				  LEFT JOIN `'._DB_PREFIX_.'blog_post_lang` pl ON pl.`id_blog_post` = p.`id_blog_post` ';

		if ($checkContext) {
			$query .= BlogShop::addShopAssociation('blog_post', 'p');
		}

		if (!$count) {
			$query .= ' LEFT JOIN '._DB_PREFIX_.'blog_image pi ON p.id_blog_post = pi.id_blog_post AND pi.default = 1
						LEFT JOIN '._DB_PREFIX_.'blog_comment bc ON p.id_blog_post = bc.id_blog_post AND bc.active = '.BlogComment::APPROVED_STATUS;

			if ($checkContext) {
				$query .= ' AND bc.id_lang = '.(int)$id_lang;
				$query .= ' AND bc.id_shop = '.(int)$id_shop;
			}
		}

		if ($context && isset($context->language)) {
			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_post_relation` pr ON (pr.`id_blog_post` = p.`id_blog_post` AND pr.`key` = "lang" AND pr.`value` = "'.$context->language->id.'") ';
		}

		if (!is_null($category_id)) {
			$query .= ' INNER JOIN '._DB_PREFIX_.'blog_categories pc ON pc.id_blog_post = p.id_blog_post AND pc.id_blog_category = '.$category_id;
		}

		$query .= ' WHERE pl.`id_lang` = '.$id_lang;

		if ($publish) {
			$query .= ' AND p.status = "published" AND NOW() >= p.date_on';
		}

		$query .= ' AND ( pl.title LIKE \'%'.pSQL($search_query).'%\' OR pl.excerpt LIKE \'%'.pSQL($search_query).'%\' OR pl.meta_keywords LIKE \'%'.pSQL($search_query).'%\' )';

		if ($count) {
			$result = Db::getInstance()->getRow($query);
			return $result['nb'];
		} else {
			$query .= ' GROUP BY p.id_blog_post ';
			$query .= ' ORDER BY p.date_on DESC, p.id_blog_post DESC ';
			if (!is_null($limit)) {
				$query .= ' LIMIT '.$start.','.$limit;
			}

			$result = Db::getInstance()->ExecuteS($query);

			if ($result) {
				$i = 0;
				foreach ($result as $val) {
					$result[$i]['link'] = BlogLink::linkPost($val['id_blog_post'], $val['link_rewrite']);
					$i++;
				}
			}

			return $result;
		}
	}

	/*
	 * @param bool $count
	 * @param bool $exclude_default
	 * @return array|int
	 */
	public function getImages($count = false, $exclude_default = false)
	{
		$select = $count ? "count(*) as nb" : "*";
		$query = 'SELECT '.$select.' FROM  '._DB_PREFIX_.'blog_image pi WHERE pi.id_blog_post = '.(int)$this->id;

		if ($exclude_default) {
			$query .= ' AND pi.default = 0';
		}
		if (!$count) {
			$query .= ' ORDER BY position ASC, id_blog_image DESC ';
		}

		if ($count) {
			$result = Db::getInstance()->getRow($query);
			return (int)$result['nb'];
		} else {
			$result = Db::getInstance()->ExecuteS($query);
		}

		return $result;
	}

	/*
	 * @return array
	 */
	public static function getAllImages()
	{
		$query = 'SELECT * FROM  '._DB_PREFIX_.'blog_image';
		return Db::getInstance()->ExecuteS($query);
	}

	/*
	 * @param $images_position
	 * @return bool
	 */
	public function setImagesPosition($images_position)
	{
		if (is_array($images_position) && count($images_position)) {
			$query = ' UPDATE '._DB_PREFIX_.'blog_image SET `position` = CASE `id_blog_image` ';
			$ids = array();
			foreach ($images_position as $id => $pos) {
				if (Validate::isUnsignedInt($id) && Validate::isUnsignedInt($pos)) {
					$query .= ' WHEN '.$id.' THEN '.$pos;
					$ids[] = $id;
				}
			}
			if (count($ids)) {
				$query .= ' END WHERE `id_blog_image` IN ('.implode(',', $ids).')';
				return Db::getInstance()->Execute($query);
			}
		}
		return false;
	}

	/*
	 * @param $image_id
	 * @return bool
	 */
	public static function generateImageThumbs($image_id)
	{
		$image = self::getImage($image_id);
		if (!$image) {
			return false;
		}

		$conf = Psblog::getPreferences();
		$dest = _PS_ROOT_DIR_.'/'.rtrim($conf['img_save_path'], '/')."/";
		$img_name = $image['img_name'];

		//thumbs
		$img_list_width = $conf['img_list_width'];
		$img_thumb_width = $conf['img_width'];

		$size = getimagesize($dest.$img_name);

		$ratio_list = $img_list_width / $size[0];
		$img_list_height = $size[1] * $ratio_list;

		$ratio_thumb = $img_thumb_width / $size[0];
		$img_thumb_height = $size[1] * $ratio_thumb;

		ImageManager::resize($dest.$img_name, $dest.'list/'.$img_name, $img_list_width, $img_list_height);
		ImageManager::resize($dest.$img_name, $dest.'thumb/'.$img_name, $img_thumb_width, $img_thumb_height);
	}

	/*
	 * @param $name
	 * @param int $default
	 * @return bool
	 */
	public function addImage($name, $default = 0)
	{
		$nb = $this->getImages(true) + 1;
		$result = Db::getInstance()->Execute(
			'INSERT INTO '._DB_PREFIX_.'blog_image(`id_blog_post`,`img_name`,`default`,`position`)
            VALUES('.(int)$this->id.',"'.pSQL($name).'","'.pSQL($default).'",' .(int)$nb.')');
		if ($result) {
			return Db::getInstance()->Insert_ID();
		}
		return $result;
	}

	/*
	 * @param $prestablog_image_id
	 */
	public function setImageDefault($prestablog_image_id)
	{
		if ($prestablog_image_id != 0) {
			Db::getInstance()->Execute(
				'UPDATE '._DB_PREFIX_.'blog_image SET `default` = 0 WHERE id_blog_post = '.(int)$this->id
			);
			Db::getInstance()->Execute(
				'UPDATE '._DB_PREFIX_.'blog_image SET `default` = 1 WHERE id_blog_image = '.(int)$prestablog_image_id
			);
		} else {
			Db::getInstance()->Execute(
				'UPDATE '._DB_PREFIX_.'blog_image SET `default` = 1 WHERE id_blog_post = '.(int)$this->id.' ORDER BY id_blog_image ASC LIMIT 1'
			);
		}
	}

	/*
	 * @return array
	 */
	public function getDefaultImage()
	{
		$result = Db::getInstance()->getRow('SELECT * FROM  '._DB_PREFIX_.'blog_image pi WHERE pi.default = 1 AND pi.id_blog_post = '.(int)$this->id);
		return $result;
	}

	/*
	 * @param $image_id
	 * @return array
	 */
	public static function getImage($image_id)
	{
		$result = Db::getInstance()->getRow('SELECT * FROM  '._DB_PREFIX_.'blog_image pi WHERE pi.id_blog_image = '.(int)$image_id);
		return $result;
	}

	/*
	 * @param $image_id
	 * @return bool
	 */
	public function removeImage($image_id)
	{
		$conf = Psblog::getPreferences();
		$save_path = _PS_ROOT_DIR_.'/'.rtrim($conf['img_save_path'], '/')."/";
		$image = self::getImage($image_id);

		if ($image) {

			$result = Db::getInstance()->Execute('DELETE FROM  '._DB_PREFIX_.'blog_image WHERE id_blog_image = '.(int)$image_id);

			if ($result) {
				$filename = $image['img_name'];
				if (file_exists($save_path.$filename)) {
					@unlink($save_path.$filename);
				}
				if (file_exists($save_path.'thumb/'.$filename)) {
					@unlink($save_path.'thumb/'.$filename);
				}
				if (file_exists($save_path.'list/'.$filename)) {
					@unlink($save_path.'list/'.$filename);
				}

				if ($image['default'] == 1) {
					$this->setImageDefault(0);
				}
				return $result;
			}
		}
		return false;
	}

	/*
	 * @return bool
	 */
	public function cleanImages()
	{
		$conf = Psblog::getPreferences();
		$save_path = rtrim($conf['img_save_path'], '/')."/";

		$psblog_images = $this->getImages();
		foreach ($psblog_images as $img) {
			$filename = $img['img_name'];
			if (file_exists(_PS_ROOT_DIR_."/".$save_path.$filename)) {
				@unlink(_PS_ROOT_DIR_."/".$save_path.$filename);
			}
			if (file_exists(_PS_ROOT_DIR_."/".$save_path.'list/'.$filename)) {
				@unlink(_PS_ROOT_DIR_."/".$save_path.'list/'.$filename);
			}
			if (file_exists(_PS_ROOT_DIR_."/".$save_path.'thumb/'.$filename)) {
				@unlink(_PS_ROOT_DIR_."/".$save_path.'thumb/'.$filename);
			}
		}

		$result = Db::getInstance()->Execute(
			'DELETE FROM  '._DB_PREFIX_.'blog_image WHERE id_blog_post = '.(int)$this->id
		);
		return $result;
	}

	/*
	 * @param null $id_lang
	 * @param bool $checkContext
	 * @param bool $active
	 * @param bool $onlyIds
	 * @return array
	 */
	public function listCategories($id_lang = null, $checkContext = true, $active = true, $onlyIds = false)
	{
		$context = null;
		if ($checkContext) {
			$context = Context::getContext();
		}

		$id_lang = (is_null($context) || !isset($context->language)) ? Context::getContext()->language->id : $context->language->id;

		$query = 'SELECT DISTINCT(c.`id_blog_category`), cl.`name`, cl.`link_rewrite`, c.id_blog_category_parent
                    FROM '._DB_PREFIX_.'blog_post_relation pr
                    INNER JOIN `'._DB_PREFIX_.'blog_category` c ON (pr.`key` = "category" AND c.`id_blog_category`= pr.`value`)
                    LEFT JOIN `'._DB_PREFIX_.'blog_category_lang` cl ON cl.`id_blog_category` = c.`id_blog_category` ';

		if ($checkContext) {
			$query .= BlogShop::addShopAssociation('blog_category', 'c');
			$groups = FrontController::getCurrentCustomerGroups();
			$sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '='.(int)Group::getCurrent()->id);
			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_category_relation` cr2 ON (cr2.`id_blog_category` = c.`id_blog_category` AND cr2.`key` = "group" AND cr2.`value` '.$sql_groups.') ';
		}

		if ($checkContext && isset($context->language)) {
			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_category_relation` cr ON (cr.`id_blog_category` = c.`id_blog_category` AND cr.`key` = "lang" AND cr.`value` = "'.(int)$context->language->id.'") ';
		}

		$query .= ' WHERE pr.`id_blog_post` = '.(int)$this->id.' AND cl.`id_lang` = '.(int)$id_lang.' AND c.`id_blog_category` != 1 ';

		if ($active) {
			$query .= ' AND c.`active` = 1 ';
		}

		$query .= ' GROUP BY c.`id_blog_category` ORDER BY pr.`id_blog_post_relation` ASC, cl.`name` ASC';

		$result = Db::getInstance()->ExecuteS($query);

		if (!$result) {
			return array();
		}

		if ($checkContext) {
			$new_result = array();
			foreach ($result as $cat) {
				if (!is_null($cat['id_blog_category_parent']) && !empty($cat['id_blog_category_parent'])) {
					$parentCategory = new BlogCategory((int)$cat['id_blog_category_parent']);
					if ($parentCategory->isAllowed()) {
						$new_result[] = $cat;
					}
				} else {
					$new_result[] = $cat;
				}
			}
			$result = $new_result;
		}

		if (!$onlyIds) {
			$i = 0;
			foreach ($result as $cat) {
				$result[$i]['link'] = BlogLink::linkCategory($cat['id_blog_category'], $cat['link_rewrite']);
				$i++;
			}
			return $result;
		} else {

			$resultIds = array();
			foreach ($result as $group) {
				$resultIds[] = $group['id_blog_category'];
			}

			return $resultIds;
		}
	}

	/*
	 * @param bool $checkContext
	 * @param bool $publish
	 * @param bool $count
	 * @return array|int
	 */
	public function getComments($checkContext = true, $publish = true, $count = false)
	{
		if ($checkContext) {
			$context = Context::getContext();
			$id_lang = $context->language->id;
			$id_shop = $context->shop->id;
		}

		$select = $count ? "count(*) as nb" : "c.*";
		$query = 'SELECT '.$select.' FROM  '._DB_PREFIX_.'blog_comment c
                  INNER JOIN '._DB_PREFIX_.'blog_post p ON p.id_blog_post = c.id_blog_post
                  WHERE c.id_blog_post = '.(int)$this->id;

		if ($publish) {
			$query .= ' AND active = '.BlogComment::APPROVED_STATUS;
		}

		if ($checkContext) {
			$query .= ' AND (c.id_lang = '.(int)$id_lang.') ';
			$query .= ' AND (c.id_shop = '.(int)$id_shop.') ';
		}

		$query .= ' ORDER BY c.date_add ASC';

		$result = Db::getInstance()->ExecuteS($query);
		if ($count) {
			return (int)$result[0]['nb'];
		}
		return $result;
	}

	/*
	 * @return bool
	 */
	public function cleanComments()
	{
		return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'blog_comment` WHERE `id_blog_post` = '.(int)$this->id);
	}

	/*
	 * @param bool $onlyIds
	 * @return array
	 */
	public function getLangs($onlyIds = false)
	{
		$query = 'SELECT l.* FROM '._DB_PREFIX_.'blog_post_relation pr
                    INNER JOIN `'._DB_PREFIX_.'lang` l ON (pr.`key` = "lang" AND l.`id_lang`= pr.`value`)
                    WHERE pr.`id_blog_post` = '.(int)$this->id;

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
	 * @param bool $checkContext
	 * @param bool $publish
	 * @param bool $onlyIds
	 * @return array
	 */
	public function getRelatedPosts($checkContext = true, $publish = true, $onlyIds = false)
	{
		$context = null;
		if ($checkContext) {
			$context = Context::getContext();
		}

		$id_lang = (is_null($context) || !isset($context->language)) ? Context::getContext()->language->id : $context->language->id;

		$query = 'SELECT DISTINCT(p.`id_blog_post`), pl.`title`, pl.`link_rewrite`
                    FROM `'._DB_PREFIX_.'blog_post_relation` pr
					INNER JOIN '._DB_PREFIX_.'blog_post p ON (pr.`key` = "post" AND p.`id_blog_post`= pr.`value`)
					LEFT JOIN `'._DB_PREFIX_.'blog_post_lang` pl ON pl.`id_blog_post` = p.`id_blog_post` ';

		if ($checkContext) {
			$query .= BlogShop::addShopAssociation('blog_post', 'p');
		}

		if ($checkContext && isset($context->language)) {
			$query .= ' INNER JOIN `'._DB_PREFIX_.'blog_post_relation` pr2 ON (pr2.`id_blog_post` = p.`id_blog_post` AND pr2.`key` = "lang" AND pr2.`value` = "'.(int)$context->language->id.'") ';
		}

		$query .= ' WHERE pl.`id_lang` = '.(int)$id_lang.' AND pr.`id_blog_post` = '.(int)$this->id;

		if ($publish) {
			$query .= ' AND p.`status` = "published" AND p.`date_on` <= NOW()';
		}

		$query .= ' GROUP BY p.`id_blog_post` ORDER BY p.`date_on` DESC, p.`id_blog_post` DESC';

		$result = Db::getInstance()->ExecuteS($query);

		if (!$result) {
			return array();
		}

		if (!$onlyIds) {
			//link
			if ($result) {
				$i = 0;
				foreach ($result as $val) {
					$result[$i]['link'] = BlogLink::linkPost($val['id_blog_post'], $val['link_rewrite']);
					$i++;
				}
			}
			return $result;
		} else {
			$resultIds = array();
			foreach ($result as $group) {
				$resultIds[] = $group['id_blog_post'];
			}
			return $resultIds;
		}
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

		$query = 'SELECT l.* FROM `'._DB_PREFIX_.'blog_post_relation` pr
				  INNER JOIN `'._DB_PREFIX_.'lang` l ON (pr.`key` = "lang" AND l.`id_lang`= pr.`value`)
                  WHERE pr.`id_blog_post` = '.(int)$this->id.' AND pr.`value` = "'.(int)$id_lang.'"';

		return (bool)Db::getInstance()->getValue($query);
	}

	/*
	 * @param bool $checkContext
	 * @param bool $active
	 * @param bool $onlyIds
	 * @return array
	 */
	public function getProducts($checkContext = true, $active = false, $onlyIds = false)
	{
		$context = Context::getContext();
		$id_lang = $context->language->id;

		$query = 'SELECT p.`id_product`, p.`reference`, pl.`name`, i.`id_image`, pl.`description_short`, pl.`link_rewrite`
                    FROM `'._DB_PREFIX_.'blog_post_relation` br
                    INNER JOIN `'._DB_PREFIX_.'product` p ON (br.`key` = "product" AND p.`id_product`= br.`value`)
                    INNER JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                            p.`id_product` = pl.`id_product`
                            AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
                    )
                    LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)';

		if ($checkContext) {
			$query .= BlogShop::addShopAssociation('product', 'p');
		}

		$query .= ' WHERE br.`id_blog_post` = '.(int)$this->id;
		if ($active) {
			$query .= ' AND p.`active` = 1 ';
		}

		$query .= ' ORDER BY br.`id_blog_post_relation` ASC, pl.`name` DESC';
		$result = Db::getInstance()->ExecuteS($query);

		if (!$result) {
			return array();
		}

		if (!$onlyIds) {

			$i = 0;
			$imageType = new ImageType(Psblog::getConfValue('product_img_format'));
			foreach ($result as $p) {
				$product = new Product($p['id_product'], false, $context->language->id);
				$result[$i]['link'] = $context->link->getProductLink($product);
				$result[$i]['imageLink'] = $context->link->getImageLink(
					$p['link_rewrite'],
					$p['id_product'].'-'.$p['id_image'],
					$imageType->name
				);
				$i++;
			}

			return $result;
		} else {
			$resultIds = array();
			foreach ($result as $group) {
				$resultIds[] = $group['id_product'];
			}

			return $resultIds;
		}
	}
}
