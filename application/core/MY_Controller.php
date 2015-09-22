<?php

class MY_Controller extends MX_Controller {

	// Site global layout

	public function __construct() {
		parent::__construct();
		
	}
	
	protected function isEnv($env){
		return ENVIRONMENT === $env;
	}
}
