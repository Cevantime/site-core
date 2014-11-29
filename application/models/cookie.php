<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of articles
 *
 * @author thibault
 */
class cookie extends DATA_Model {

	const TABLE_NAME = 'cookies';

	public function getTableName() {
		return self::TABLE_NAME;
	}
	
	public function getPrimaryColumns() {
		return array();
	}

	public function insertCookie($userId = null, $key = null) {
		if ($userId === null) {
			$userId = $this->user_id;
		}
		if ($key === null) {
			$key = $this->key;
		}
		return parent::insert(array('user_id' => $userId, 'key' => $key));
	}

	public function deleteCookie($userId = null, $key = null) {
		if ($userId === null) {
			$userId = $this->user_id;
		}

		if ($key === null) {
			$key = $this->key;
		}
		return parent::delete(array('user_id' => $userId, 'key' => $key));
	}

	public function checkCookie($userId = null, $key = null) {
		if ($userId === null) {
			$userId = $this->user_id;
		}
		if ($key === null) {
			$key = $this->key;
		}
		$where = array('user_id' => $userId, 'key' => $key);
		$cookie = parent::getRow($where);
		if ($cookie) {
			return true;
		}
		return false;
	}

}

?>
