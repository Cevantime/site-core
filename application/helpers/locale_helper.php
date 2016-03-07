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

if (!function_exists('locale')) {
	function locale($loc = null) {
		$CI =& get_instance();
		$CI->load->library('session');
		if($loc){
			$loc = strtolower(substr($loc, 0,2));
			$CI->session->set_userdata('user_loc', $loc);
		}
		else if($CI->session->userdata('user_loc')){
			return $CI->session->userdata('user_loc');
		}
		else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			locale($_SERVER['HTTP_ACCEPT_LANGUAGE']);
			return locale();
		} else {
			locale('fr');
			return locale();
		}
	}
}