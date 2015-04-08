<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Home extends MY_Controller {

	public function index() {
		if($this->input->post('username')){
			$username = $this->input->post('username');
			$this->input->set_cookie('username', $username);
			$this->session->username = $username;
		} else if($this->session->has_userdata('username')) {
			$username = $this->session->username;
		} else if($this->input->cookie('username')){
			$username = $this->input->cookie('username');
			$this->session->username = $username;
		} else {
			$username = 'core';
		}
		$this->layout->assign('name', $username);
		$this->layout->view('home/index');
	}

	public function rememberme() {
		$this->index();
	}
}
