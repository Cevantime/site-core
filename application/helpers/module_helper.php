<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!function_exists('requires_module')) {

	function requires_module($moduleName) {
		
		if(!file_exists(APPPATH.'/modules/'.$moduleName)){
			die('This script you want to execute requires the module '.$moduleName.'. Please install this module.');
		}
	}

}

if (!function_exists('is_module_installed')) {

	function is_module_installed($moduleName) {
		
		return file_exists(APPPATH.'/modules/'.$moduleName);
	}

}