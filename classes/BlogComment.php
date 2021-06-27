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
 * BlogComment class
 */

require_once(_PS_MODULE_DIR_.'psblog/classes/BlogPost.php');

class BlogComment extends ObjectModel
{
	const APPROVED_STATUS = 2;

	public $content;
	public $customer_name;
	public $id_customer;
	public $id_guest;
	public $id_lang;
	public $id_shop;
	public $id_blog_post;
	public $active; /* 1 waiting, 2 approved, 3 Disapproved */
	public $date_add;
	public static $definition = array(
		'table' => 'blog_comment',
		'primary' => 'id_blog_comment',
		'fields' => array(
			'active' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'customer_name' => array(
				'type' => self::TYPE_STRING,
				'required' => true,
				'validate' => 'isGenericName',
				'size' => 128
			),
			'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_guest' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
			'id_blog_post' => array('type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'),
			'id_lang' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'content' => array('type' => self::TYPE_STRING, 'validate' => 'isMessage', 'size' => 65535)
		)
	);

	/*
	 * Get post article
	 * @return BlogPost
	 */
	public function getArticle()
	{
		return new BlogPost($this->id_blog_post);
	}

	/*
	 * Get customer post comments
	 * @return BlogPost
	 */
	public static function getByCustomer($id_blog_post, $id_customer, $last = false, $id_guest = false)
	{
		$results = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'blog_comment` bc
                WHERE bc.`id_blog_post` = '.(int)$id_blog_post.'
                AND '.(!$id_guest ? 'bc.`id_customer` = '.(int)$id_customer : 'bc.`id_guest` = '.(int)$id_guest).'
                ORDER BY bc.`date_add` DESC '.($last ? 'LIMIT 1' : '')
		);

		if ($last) {
			return array_shift($results);
		}
		return $results;
	}

	/*
	 * @param $active
	 * @param bool $count
	 * @return array
	 */
	public static function getByStatus($active, $count = false)
	{
		//1 waiting, 2 approved, 3 Disapproved

		if ($count) {
			$select = 'COUNT(bc.`id_blog_comment`) as nb';
		} else {
			$select = '*';
		}

		$query = 'SELECT '.$select.' FROM `'._DB_PREFIX_.'blog_comment` bc
                 INNER JOIN `'._DB_PREFIX_.'blog_post` p ON p.`id_blog_post` = bc.`id_blog_post`
                 WHERE 1 '.(!is_null($active) ? ' AND bc.`active` = "'.(int)$active.'"' : '').'
                 ORDER BY bc.`date_add` DESC ';

		if ($count) {
			$result = Db::getInstance()->getRow($query);
			return $result['nb'];
		} else {
			return Db::getInstance()->ExecuteS($query);
		}
	}

	/*
	 * Get more commented posts
	 * @param $limit
	 * @return array
	 */
	public static function getCommentedPosts($limit)
	{

		$id_lang = Context::getContext()->language->id;

		$query = 'SELECT p.`id_blog_post`, pl.`title`, COUNT(bc.id_blog_comment) as nb_comments
                    FROM  `'._DB_PREFIX_.'blog_post` p
                    INNER JOIN `'._DB_PREFIX_.'blog_post_lang` pl ON pl.`id_blog_post` = p.`id_blog_post`
                    INNER JOIN `'._DB_PREFIX_.'blog_comment` bc ON (p.`id_blog_post` = bc.id_blog_post AND bc.active = 1)
                    WHERE pl.id_lang = '.(int)$id_lang.'
                    GROUP BY p.`id_blog_post`
                    ORDER BY `nb_comments` DESC 
                    LIMIT 0,'.(int)$limit;

		return Db::getInstance()->ExecuteS($query);
	}

	/*
	 * @return string
	 */
	public static function getAdminCommentsLink()
	{
		$context = Context::getContext();
		$tokenModules = Tools::getAdminToken('AdminBlogComments'.(int)Tab::getIdFromClassName('AdminBlogComments').(int)$context->employee->id);
		$link = 'index.php?controller=AdminBlogComments&token='.$tokenModules;

		return $link;
	}

	/*
	 * Send new comment notification
	 * @param BlogPost $blogPost
	 * @return mixed
	 */
	public function sendCommentNotification(BlogPost $blogPost)
	{
		$employeeId = Psblog::getConfValue('employee_notification');
		$employee = new Employee($employeeId);
		$email = $employee->email;

		$templateVars = array('{post_title}' => $blogPost->title,
			'{name}' => $this->customer_name,
			'{comment}' => $this->content);

		if ($blogPost && $employee) {
			return Mail::Send(
				Context::getContext()->language->id,
				'comment_notification',
				Mail::l('Blog comment notification', Context::getContext()->language->id),
				$templateVars,
				pSQL($email),
				null,
				null,
				null,
				null,
				null,
				_PS_MODULE_DIR_.'psblog/translations/mails/',
				false,
				Context::getContext()->shop->id
			);
		}
	}
}
