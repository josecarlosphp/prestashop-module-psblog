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
 * AdminBlogConfController tab for admin panel
 */

require_once(_PS_MODULE_DIR_.'psblog/psblog.php');

class AdminBlogConfController extends ModuleAdminController
{
	/*
	 * Redirect to module configuration
	 */
	public function __construct()
	{
		Tools::redirectAdmin(Psblog::getBlogConfigurationLink());
		parent::__construct();
	}
}