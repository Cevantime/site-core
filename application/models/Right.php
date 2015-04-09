<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Right
 *
 * @author thibault
 */
class Right extends DATA_Model {
	
	public static $TABLE_NAME = 'rights';

	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getUserRights($user_id = NULL) {
		if(!$user_id) {
			$user_id = $this->getData('user_id');
		}
		if($user_id){
			return $this->getTrough(User::$TABLE_NAME, $user_id);
		}
		return false;
	}
}
