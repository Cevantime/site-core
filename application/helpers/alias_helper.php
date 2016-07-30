<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (!function_exists('alias')) {

	function alias($str) {
		$str = str_replace(array('\'', '"'), array('-', '-'), $str);
		$str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('/\s+/', '-', $str);
		$str = trim($str, '-');
		return strtolower($str);

	}

}