<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

if (!function_exists('pagination')) {

	function pagination($total_number_of_elements, $target_action, $current_index = 0, $number_of_element_per_page = 10, $amplitude = 2, $jump = 1) {
		$html = '';
		if ($total_number_of_elements > 0) {
			$html = '<div class="pagination"> <ul> <li><a href="' . base_url() . $target_action . '/' . max(0, $current_index - $amplitude - $jump) . '">&laquo;</a></li>';
			for ($i = max(0, $current_index - $amplitude); $i <= min($total_number_of_elements / $number_of_element_per_page, $current_index + $amplitude); $i++) {
				$html .= '<li ' . (($i == $current_index) ? 'class="active"' : '') . '><a href="' . base_url() . $target_action . '/' . $i . '">' . ($i + 1) . '</a></li>';
			}
			$html .= '<li><a href="' . base_url() . $target_action . '/' . min(intval($total_number_of_elements / $number_of_element_per_page), $current_index + $amplitude + $jump) . '">&raquo;</a></li></ul></div>';
		}
		return $html;
	}

}
?>
