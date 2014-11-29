<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * CodeIgniter XML Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/helpers/xml_helper.html
 */
// ------------------------------------------------------------------------

/**
 * Convert Reserved XML characters to Entities
 *
 * @access	public
 * @param	string
 * @return	string
 */
if (!function_exists('is_allowed')) {
	function is_allowed($aclAction) {
		$CI = & get_instance();
		if(!isset($CI->acl)){
			$CI->load->model('acl');
		}
		if (isset($CI) && $CI->bo_user && $CI->acl) {
			return $CI->acl->check($aclAction, $CI->bo_user->status);
		}

		return false;
	}

}

if (!function_exists('is_premium')) {

	function is_premium() {
		$CI = & get_instance();
		if(isset($CI) && $CI->isPremium()){
			return true;
		}

		return false;
	}

}

if (!function_exists('is_connected')) {

	function is_connected() {
		$CI = & get_instance();
		$CI->load->library('session');
		if($CI->session->userdata('user')){
			return true;
		}

		return false;
	}

}
// ------------------------------------------------------------------------

/* End of file xml_helper.php */
/* Location: ./system/helpers/xml_helper.php */