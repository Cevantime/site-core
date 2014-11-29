<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!function_exists('config')) {

	function config($key) {
		$CI =& get_instance();
		$CI->load->model('configuration');
		return $CI->configuration->getValue($key);
	}

}