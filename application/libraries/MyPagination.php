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

 
class Pagination {
	
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
		$models = $model->$methodName($dep, $offset);
		if (!$models) {
			$models = array();
		}
		//Trying (desperately) to retrieve the query returning the number of 
		//all elements for the corresponding methodName, without limit and offset
		//other methods are likely to offer terrible performances with large datasets
		$lastQuery = $model->db->last_query();
		$endPos = strpos($lastQuery, 'LIMIT ');
		$from = substr($lastQuery, 0, $endPos);
		$queryCount = 'SELECT COUNT(*) as c FROM (' . $from . ') as lastquery';

		//retrieving total number of elements
		$resCount = $model->db->query($queryCount)->result('array');
		if ($resCount) {
			$max = $resCount[0]['c'];
		}
		$this->paginations[$id] = array(
			'start' => $start,
			'offset' => $offset,
			'max' => $max
		);
		return $models;
	}
	
	public function getPagination($id, $target_action = null, $amplitude = 2, $jump = 1,$mainWraper='ul', $subWrapper = 'li'){
		if(!isset($this->paginations[$id])) return '';
		if(!$target_action){
			$target_action = current_url();
		}
		$start = $this->paginations['start'];
		$offset = $this->paginations['offset'];
		$max = $this->paginations['max'];
		$target = base_url($target_action);
		
		if ($max > 0) {
			$html = '<'.$mainWraper.' class="pagination" id="'.$id.'"><'.$subWrapper.'><a href="' . $target . '/start/' . max(0, $start - $amplitude - $jump) . '">&laquo;</a></'.$subWrapper.'>';
			for ($i = max(0, $start - $amplitude); $i <= min($max / $offset, $max + $amplitude); $i++) {
				$html .= '<'.$subWrapper.' ' . (($i == $start) ? 'class="active"' : '') . '><a href="' . $target . '/start/' . $i . '">' . ($i + 1) . '</a></'.$subWrapper.'>';
			}
			$html .= '<'.$subWrapper.'><a href="' . $target . '/' . min(intval($max / $offset), $max + $amplitude + $jump) . '">&raquo;</a></'.$subWrapper.'></'.$mainWraper.'>';
		}
	}
	
}
