<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of youtubecomments
 *
 * @author thibault
 */
class youtubecomment extends DATA_Model {
	
	const TABLE_NAME = 'youtube_comments';
	
	public function getTableName() {
		return self::TABLE_NAME;
	}
	
	public function getRandomComment($excluded_ids = null) {
		if($excluded_ids){
			$this->db->where_not_in('id', $excluded_ids);
		}
		$this->db->order_by('RAND()');
		return $this->getRow();
	}
}
