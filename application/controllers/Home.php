<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Home extends MY_Controller {

	public function index() {
		$this->load->spark('example-spark/1.0.0');
		$this->example_spark->printHello();
	}

}
