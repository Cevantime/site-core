<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Admin
 *
 * @author thibault
 */

class Admin extends User {
	
	public static $TABLE_NAME = 'users_admin';

	public function getTableName() {
		return self::$TABLE_NAME;
	}
}
