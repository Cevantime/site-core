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
class Login extends BO_Controller {
	
	public $layout_view = 'layout/login_bo';
	
	public function index() {
		// TODO implement admin connect
		
		$this->layout->view('bo/login');
	}
}
