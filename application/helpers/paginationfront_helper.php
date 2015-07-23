<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

if (!function_exists('pagination')) {

	function pagination($total_number_of_elements, $target_action, $container, $current_index = 0, $number_of_element_per_page = 10, $amplitude = 2, $jump = 1) {
		$html = '';
		if ($total_number_of_elements > 0) {
			$html .= '<div class="pagination"> <ul>';
			if ($current_index > $amplitude) {
				$html .= '<li><a class="prev" onclick="page(\'' . base_url() . $target_action . '/' . max(0, $current_index - $amplitude - $jump) . '\', \'' . $container . '\')"></a></li>';
			}
			for ($i = max(0, $current_index - $amplitude); $i <= min(($total_number_of_elements - 1) / $number_of_element_per_page, $current_index + $amplitude); $i++) {
				$html .= '<li ' . (($i == $current_index) ? 'class="active"' : '') . '><a onclick="page(\'' . base_url() . $target_action . '/' . $i . '\', \'' . $container . '\')">' . ($i + 1) . '</a></li>';
			}
			$nbPage = intval($total_number_of_elements / $number_of_element_per_page);
			if ($nbPage > $amplitude && $current_index + $amplitude < $nbPage) {
				$html .= '<li><a class="next" onclick="page(\'' . base_url() . $target_action . '/' . min($nbPage, $current_index + $amplitude + $jump) . '\', \'' . $container . '\')"></a></li>';
			}
			$html .= '</ul></div>';
		}
		return $html;
	}

}
?>
