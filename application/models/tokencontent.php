<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tokencontent
 *
 * @author thibault
 */
class tokencontent extends DATA_Model {
	
	const TABLE_NAME = 'token_contents';
	public static $CONTENT_TYPE_VIDEO = 'video', 
			$CONTENT_TYPE_RESOURCE = 'resource',
			$CONTENT_TYPE_VIDEO_CHAPTER = 'video_chapter',
			$CONTENT_TYPE_VIDEO_EXTRACT = 'video_extract';
	
	public function getTableName() {
		return self::TABLE_NAME;
	}
	
	public function createToken($id, $type){
		$token = uniqid();
		$this->insert(array(
			'id_content' => $id,
			'type' => $type,
			'token' => $token,
			'created_time' => time()
		));
		
		return $token;
	}
	
	public function deleteToken($id = null){
		if(!$id){
			$id = $this->id;
		}
		return $this->db->delete($this->getTableName(), array(
			'id' => $id)
		);
	}
	
	public function fetchToken($token = null){
		$this->cleanTokens();
		if(!$token) {
			$token = $this->token;
		}
		
		$row = $this->getRow(array('token'=>$token));
		return $row;
	}
	
	private function cleanTokens() {
		$this->delete('created_time < '.(time()-30));
	}
}
