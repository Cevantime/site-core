<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BO_Controller
 *
 * @author thibault
 */
class BO_Controller extends CI_Controller {

	public $layout_view = 'layout/bo';
	
	protected $userId;

	public function __construct() {
		parent::__construct();
		$status = $this->requireBoLogin();
		$this->load->library('layout');
		$this->load->library('session');
		
		$errors = $this->session->flashdata('errors');
		if ($errors) {
			$this->layout->assign('errors', $errors);
		}
		$warnings = $this->session->flashdata('warnings');
		if ($warnings) {
			$this->layout->assign('warnings', $warnings);
		}
		$success = $this->session->flashdata('success');
		if ($success) {
			$this->layout->assign('success', $success);
		}
	}
	protected function pagination($model, $start, $offset = 10, $methodName = 'getList') {
		$this->load->model($model);
		$this->load->helper('pagination');
		$offset = ($offset !== null) ? $offset : 10;
		$dep = ($start !== null) ? $start * $offset : null;


		$models = $this->{$model}->$methodName($dep, $offset);
		if (!$models) {
			$models = array();
		}
		//Trying (desperately) to retrieve the query returning the number of 
		//all elements for the corresponding methodName, without limit and offset
		//other methods are likely to offer terrible performances with large datasets
		$lastQuery = $this->{$model}->db->last_query();
		$endPos = strpos($lastQuery, 'LIMIT ');
		$from = substr($lastQuery, 0, $endPos);

		$queryCount = 'SELECT COUNT(*) as c FROM (' . $from . ') as lastquery';

		//retrieving total number of elements
		$resCount = $this->{$model}->db->query($queryCount)->result('array');
		if ($resCount) {
			$max = $resCount[0]['c'];
		}

		$datas['start'] = $start;
		$datas['max'] = isset($max) ? $max : 0;
		$datas['offset'] = $offset;
		$this->layout->assign($datas);
		return $models;
	}

	protected function addErrors($message) {
		$this->session->set_flashdata('errors', $message);
		$this->layout->assign('errors', $message);
	}

	protected function addSuccess($message) {
		$this->session->set_flashdata('success', $message);
		$this->layout->assign('success', $message);
	}

	protected function addWarnings($message) {
		$this->session->set_flashdata('success', $message);
		$this->layout->assign('warnings', $message);
	}

}

?>
