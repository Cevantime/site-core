<?php

class MY_Controller extends MX_Controller {

	// Site global layout

	public function __construct() {
		parent::__construct();
		$this->load->library('layout/layout');
		$this->layout->setLayout('default');
		// Layout library loaded site wide
		$this->layout->assign('uri_string', $this->uri->uri_string());
		$this->breadcrumb();
	}

	protected function pagination($model, $start, $offset = 10, $methodName = 'getList', $suffix = '') {
		if (is_string($model)) {
			$this->load->model($model);
			$model = $this->{$model};
		}
		
		$this->load->helper('paginationfront');
		$offset = ($offset !== null) ? $offset : 10;
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

		$datas['start' . $suffix] = $start;
		$datas['max' . $suffix] = isset($max) ? $max : 0;
		$datas['offset' . $suffix] = $offset;
		$this->layout->assign($datas);
		return $models;
	}

	protected function addErrors($message) {
		$this->layout->assign('errors', $message);
	}

	protected function addSuccess($message) {
		$this->layout->assign('success', $message);
	}

	protected function addWarnings($message) {
		$this->layout->assign('warnings', $message);
	}
	
	protected function breadcrumb($breadcrumb = null) {
		if(!$breadcrumb){
			$breadcrumb = array();
			$segments = $this->uri->segment_array();
			$uri = base_url();
			foreach($segments as $segment) {
				$uri .= $segment.'/';
				$breadcrumb[$segment] = $uri;
			}
		}
		
		$this->layout->assign('breadcrumb', $breadcrumb);
	}
	
	protected function isEnv($env){
		return ENVIRONMENT === $env;
	}
}
