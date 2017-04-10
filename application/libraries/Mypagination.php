<?php

/**
 * CodeIgnighter layout support library
 *  with Twig like inheritance blocks
 *
 * v 1.0
 *
 *
 * @author Constantin Bosneaga
 * @email  constantin@bosneaga.com
 * @url    http://a32.me/
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

 
class Mypagination {
	
	private $paginations = array();
	private $config = array();

	public function __construct($config = null) {
		if($config){
			$this->initialize($config);
		}
	}
	
	public function initialize($config) {
		$this->config = $config;
	}
	
	public function paginate($id, $model, $start = null, $offset = null, $methodName = 'getList') {

		$dep = ($start !== null) ? $start * $offset : null;
		$model->compileQueryOnly(true);
		$query = $model->$methodName($dep, $offset);
		$model->compileQueryOnly(false);
		// adding the SQL_CALC_FOUND_ROWS option
		$query = substr($query, 0, 6).' SQL_CALC_FOUND_ROWS '.substr($query, 7);
		$models = $model->db->query($query)->result('object');
		if(!$models) {
			$models = array();
		}
		$queryCount = 'SELECT FOUND_ROWS() as c';
		//retrieving total number of elements
		$resCount = $model->db->query($queryCount)->result('array');
		if ($resCount) {
			$max = $resCount[0]['c'];
		} else {
			$max = 0;
		}
		$this->paginations[$id] = array(
			'start' => $start,
			'offset' => $offset,
			'max' => $max
		);
		return $models;
	}
	
	
	public function getPagination($id, $target_action = null, $amplitude = 2, $jump = 1,$mainWraper='ul', $subWrapper = 'li', $class = 'pagination'){
		if(!isset($this->paginations[$id])) return '';
		if(!$target_action){
			$target_action = current_url();
		}
		$pagination = $this->paginations[$id];
		$start = $pagination['start'];
		$offset = $pagination['offset'];
		$max = $pagination['max'];
		$target = $target_action;
//		$hasStart = strpos($target_action, '/start/');
//		if($hasStart !== FALSE) $target = substr($target_action, 0, $hasStart);
//		else $target = $target_action;
		if ($max > 0) {
			$html = '<'.$mainWraper.' class="'.$class.'" id="pagination-'.$id.'"><'.$subWrapper.'><a href="' . $target . '?page_start=' . max(0, $start - $amplitude - $jump) . '">&laquo;</a></'.$subWrapper.'>';
			for ($i = max(0, $start - $amplitude); $i <= min($max / $offset, $start + $amplitude); $i++) {
				$html .= '<'.$subWrapper.' ' . (($i == $start) ? 'class="active"' : '') . '><a href="' . $target . '?page_start=' . $i . '">' . ($i + 1) . '</a></'.$subWrapper.'>';
			}
			$html .= '<'.$subWrapper.'><a href="' . $target . '?page_start=' . min(intval($max / $offset), $max + $amplitude + $jump) . '">&raquo;</a></'.$subWrapper.'></'.$mainWraper.'>';
		}
		else return '';
		return $html;
	}
	public function getPaginationAjax($id, $container = null, $target_action = null, $amplitude = 2, $jump = 1,$mainWraper='ul', $subWrapper = 'li', $class='pagination paginationAjax'){
		if(!isset($this->paginations[$id])) return '';
		if(!$target_action){
			$target_action = current_url();
		}
		$data_container = '';
		if($container){
			$data_container = ' data-container="'.$container.'"';
		}
		$pagination = $this->paginations[$id];
		$start = $pagination['start'];
		$offset = $pagination['offset'];
		$max = $pagination['max'];
		$target = $target_action;
		if ($max > 0) {
			$html = '<'.$mainWraper.' data-module="compiled/pagination" class="'.$class.'"'.$data_container.' id="pagination-'.$id.'"><'.$subWrapper.'><a href="' . $target . '?page_start=' . max(0, $start - $amplitude - $jump) . '">&laquo;</a></'.$subWrapper.'>';
			for ($i = max(0, $start - $amplitude); $i <= min($max / $offset, $start + $amplitude); $i++) {
				$html .= '<'.$subWrapper.' ' . (($i == $start) ? 'class="active"' : '') . '><a href="' . $target . '?page_start=' . $i . '">' . ($i + 1) . '</a></'.$subWrapper.'>';
			}
			$html .= '<'.$subWrapper.'><a href="' . $target . '?page_start=' . min(intval($max / $offset), $max + $amplitude + $jump) . '">&raquo;</a></'.$subWrapper.'></'.$mainWraper.'>';
		}
		else return '';
		return $html;
	}
	
}
