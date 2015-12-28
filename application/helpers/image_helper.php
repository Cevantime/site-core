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
 * CodeIgniter Language Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/helpers/language_helper.html
 */
// ------------------------------------------------------------------------

/**
 * Lang
 *
 * Fetches a language variable and optionally outputs a form label
 *
 * @access	public
 * @param	string	the language line
 * @param	string	the id of the form element
 * @return	string
 */
if (!function_exists('imageresize')) {
	// very useful
	function imageresize($src, $width, $height, $crop = true) {
		$path_request = $src;
		$remote = true;
		
		//verification qu'on charge bien une image
		$ext_allowed = array('jpg','jpeg','gif','png');
		
		$explode = explode('.',$path_request);
		if(!in_array(strtolower(end($explode)), $ext_allowed)) return $src;

		$source = $path_request;
		$CI =& get_instance();
		$CI->load->library('Picture', null,'picture');
		$pict = $CI->picture;
		//emplacement de mise en cache
		$filename = $pict->makeFilename( str_replace(base_url(), '', $width.'x'.$height.(($crop)?'.crop.':'.').$source )) ;
		$searchfilename = 'application/cache/' . $filename;
		
		if( !file_exists( $searchfilename ) ){
			$pict->init(array('filename'=>$source,'remote'=>$remote));
			if(!$pict->getWidth()) return $src;
			if( $crop )
				$pict->cropTo( $width, $height );
			else
				$pict->dimensionTo( $width, $height );

			$pict->toFile($searchfilename, $quality=90 );
		}

		$output = base_url() . 'application/cache/'.$filename;
		
		return $output;
	}

}

// ------------------------------------------------------------------------
/* End of file language_helper.php */
/* Location: ./system/helpers/language_helper.php */