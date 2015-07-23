<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Login
 *
 * @author thibault
 */
class Logout extends BO_Controller {
	
	public function index() {
		$this->session->sess_destroy();
		redirect('bo/login');
	}
}
