<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Users
 *
 * @author thibault
 */
class User extends DATA_Model {
	
	public static $TABLE_NAME = 'users';
	
	protected $rights = array();

	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function clear() {
		parent::clear();
		$this->rights = array();
	}


	public function load($id){
		$this->loadRow(array('id'=>$id));
	}
	
	public function checkUser($login, $password){
		return $this->getRow(array('login'=>$login,'password'=>$password));
	}

	public function can($action, $type, $value = NULL){
		$this->loadRights();
		if($value){
			if($this->hasARightTo($action, $type.'s')){
				return true;
			}
			foreach($this->rights as $right){
				if($right->name === $action && $right->type === $type && $right->value===$value){
					return true;
				}
			}
			return false;
		}
		if($this->hasARightTo($action, $type)){
			return true;
		}
		return false;
		
	}

	public function hasARightTo($action, $type){
		$this->loadRights();
		foreach($this->rights as $right){
			if($right->name === $action && $right->type === $type){
				return true;
			}
		}
		return false;
	}
	
	public function loadRights($force = false) {
		if($force || !$this->rights){
			if(!$this->getData('id')) {
				$this->rights = array();
			} else {
				$this->load->model('right');
				$this->rights = $this->right->getUserRights($this->id);
			}
		}
	}
	
	public function getUserRights($user_id){
		$this->load->model('right');
		return $this->right->getUserRights($user_id);
	}

}
