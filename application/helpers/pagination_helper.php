<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

if (!function_exists('pagination')) {

	function pagination($id, $target_action = null, $amplitude = 2, $jump = 1,$mainWraper='ul', $subWrapper = 'li', $class = 'pagination') {
		$CI =& get_instance();
		if(!isset($CI->mypagination)){
			return translate('Erreur de pagination');
		}
		return $CI->mypagination->getPagination($id,$target_action, $amplitude, $jump,$mainWraper, $subWrapper, $class);
	}
	
	function pagination_ajax($id, $container = null, $target_action = null, $amplitude = 2, $jump = 1,$mainWraper='ul', $subWrapper = 'li', $class='pagination paginationAjax') {
		$CI =& get_instance();
		if(!isset($CI->mypagination)){
			return translate('Erreur de pagination');
		}
		return $CI->mypagination->getPaginationAjax($id,$container,$target_action,$amplitude,$jump,$mainWraper,$subWrapper, $class); 
	}
}
?>
