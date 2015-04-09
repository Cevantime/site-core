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
		if($this->user->can('access','backoffice')){
			redirect('bo/home');
		}
		if($this->input->post()){
			$this->load->library('form_validation');
			$this->form_validation->set_rules('login','login','required');
			$this->form_validation->set_rules('password','password','required|md5');
			
			if($this->form_validation->run()) {
				$post = $this->input->post();
				if($user = $this->user->checkUser($post['login'], $post['password'])){
					
					$this->session->user_id = $user->id;
					$this->user->load($user->id);
					if($this->user->can('access','backoffice')){
						redirect('bo/home');
					} else {
						$this->addErrors('you can\'t access backoffice');
					}
				} else {
					$this->addErrors('login or password not found');
				}
			} else {
				die($this->form_validation->error_string());
			}
			
		}
		$this->layout->view('bo/login');
	}
}
