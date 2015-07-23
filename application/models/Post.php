<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Post
 *
 * @author thibault
 */
class Post extends DATA_Model{
	
	public static $TABLE_NAME = 'posts';

	public function getTableName() {
		return self::$TABLE_NAME;
	}

}
