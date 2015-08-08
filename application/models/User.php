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

	public function can($action, $type='*', $value='*'){
		$this->loadRights();
		$rights = $this->rights;
		return $this->checkInRights($rights, 'name', $action) &&
		$this->checkInRights($rights, 'type', $type) &&
		$this->checkInRights($rights, 'object_key', $value);
		
	}

	public function checkInRights(&$rights, $type, $value) {
		foreach($rights as $right) {
			$rightValue = $right->$type;
			if($rightValue === '*' || $rightValue === $value) {
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
	
	public function getUserRights($userId){
		$this->load->model('right');
		return $this->right->getUserRights($userId);
	}
	
	public function allowTo($action, $type, $value){
		if(!$this->getData('id')){
			return false;
		}
		return $this->allowUserTo($this->getData('id'), $action, $type, $value);
		
	}
	
	public function allowUserTo($userId, $action, $type='*', $value='*') {
		$this->load->model('right');
		$this->right->save(array('name'=>$action,'type'=>$type,'object_key'=>$value));
		$right = $this->right->getRow(array('name'=>$action,'type'=>$type,'object_key'=>$value));
		return $this->db->insert(Right::$USERS_RIGHTS_LINK_TABLE_NAME, array('user_id'=>$userId,'right_id'=>$right->id));
	}
	
	public function loadLinkedAdmin() {
		$this->load->model('admin');
		$linkedAdmin = $this->admin->getId($this->id, 'array');
		if($linkedAdmin){
			$this->setDatas($linkedAdmin);
		}
	}

}
