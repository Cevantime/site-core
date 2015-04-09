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
class Home extends BO_Controller {

	public function index() {
		if(!$this->user->can('access','backoffice')){
			redirect('bo/login');
		}
		$this->layout->view('bo/home');
	}

}

?>
