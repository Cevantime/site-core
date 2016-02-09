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
	
	public function getScriptPaginationForId($id_pagination) {
		return "<script type='text/javascript'>\n"
				. "var pagination = $('.pagination#pagination-$id_pagination');\n"
				. "var dataContainer = pagination.data('container');\n"
				. "if(dataContainer === 'undefined') {\n"
					. "container = pagination.parent();\n"
				. "} else {\n"
					. "container = $(dataContainer);\n"
				. "}"
				. "var linksPagination = pagination.find('a');\n"
				. "linksPagination.click(function(e){\n"
					. "e.preventDefault();"
					. "var target_action = $(this).attr('href');\n"
					. "$.ajax({\n"
						. "url: target_action,\n"
						. "success : function(html){\n"
							. "var height = container.height();"
							. "container.replaceWith(html);\n"
							. "if(dataContainer === 'undefined') {\n"
								. "container = pagination.parent();\n"
							. "} else {\n"
								. "container = $(dataContainer);\n"
							. "}"
							. "$('html,body').animate({"
								. "scrollTop :$('html,body').scrollTop() + container.height() - height"
							. "}, 'fast');"
						. "}\n"
					. "});\n"
				. "})\n"
		. "</script>\n";
	}
	
	public function getPagination($id, $target_action = null, $amplitude = 2, $jump = 1,$mainWraper='ul', $subWrapper = 'li'){
		if(!isset($this->paginations[$id])) return '';
		if(!$target_action){
			$target_action = current_url();
		}
		$pagination = $this->paginations[$id];
		$start = $pagination['start'];
		$offset = $pagination['offset'];
		$max = $pagination['max'];
		$target = $target_action;
		if ($max > 0) {
			$html = '<'.$mainWraper.' class="pagination" id="pagination-'.$id.'"><'.$subWrapper.'><a href="' . $target . '/start/' . max(0, $start - $amplitude - $jump) . '">&laquo;</a></'.$subWrapper.'>';
			for ($i = max(0, $start - $amplitude); $i <= min($max / $offset, $max + $amplitude); $i++) {
				$html .= '<'.$subWrapper.' ' . (($i == $start) ? 'class="active"' : '') . '><a href="' . $target . '/start/' . $i . '">' . ($i + 1) . '</a></'.$subWrapper.'>';
			}
			$html .= '<'.$subWrapper.'><a href="' . $target . '/start/' . min(intval($max / $offset), $max + $amplitude + $jump) . '">&raquo;</a></'.$subWrapper.'></'.$mainWraper.'>';
		}
		else return '';
		return $html;
	}
	public function getPaginationAjax($id, $container = null, $target_action = null, $amplitude = 2, $jump = 1,$mainWraper='ul', $subWrapper = 'li'){
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
			$html = '<'.$mainWraper.' class="pagination paginationAjax"'.$data_container.' id="pagination-'.$id.'"><'.$subWrapper.'><a href="' . $target . '/start/' . max(0, $start - $amplitude - $jump) . '">&laquo;</a></'.$subWrapper.'>';
			for ($i = max(0, $start - $amplitude); $i <= min($max / $offset, $max + $amplitude); $i++) {
				$html .= '<'.$subWrapper.' ' . (($i == $start) ? 'class="active"' : '') . '><a href="' . $target . '/start/' . $i . '">' . ($i + 1) . '</a></'.$subWrapper.'>';
			}
			$html .= '<'.$subWrapper.'><a href="' . $target . '/start/' . min(intval($max / $offset), $max + $amplitude + $jump) . '">&raquo;</a></'.$subWrapper.'></'.$mainWraper.'>';
		}
		else return '';
		$html .= $this->getScriptPaginationForId($id);
		return $html;
	}
	
}
