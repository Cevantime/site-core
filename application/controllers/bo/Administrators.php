<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of home
 *
 * @author thibault
 */
class Administrators extends BO_Controller {

	public function index() {
		$this->all();
	}
	
	public function all($limit = 0,$offset=10){
		$this->checkIfUserCan('see','admin','*');
		$this->load->model('admin');
		$admins = $this->pagination('admin', $limit, $offset);
		$this->layout->assign('administrators', $admins);
		$this->layout->view('bo/administrators/all');
	}
	
	public function add() {
		$this->save();
	}
	
	public function edit($id) {
		$this->save($id);
	}
	
	public function save($id = null) {
		
		if($this->input->post()){
			
		}
	}

}

?>
