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
		$this->layout->view('bo/home');
	}

}

?>
