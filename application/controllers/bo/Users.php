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
class Users extends BO_Controller {

	public function index() {
		$this->all();
	}
	
	public function all($limit = 0,$offset=10){
		$this->checkIfUserCan('see', 'users');
		$this->load->model('user');
		$users = $this->pagination('user', $limit, $offset);
		$this->layout->assign('users', $users);
		$this->layout->view('bo/users/all');
	}

}

?>
