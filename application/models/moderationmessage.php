<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of page
 *
 * @author thibault
 */
class moderationMessage extends DATA_Model {

	public function getTableName() {
		return 'moderation_messages';
	}

//put your code here

	public function getMessage($id) {
		return parent::getId($id);
	}

	public function getListByTypeAsIdAliasAssociativeArray($type) {
		$all = $this->get(array('type' => $type), 'array');
		if (!$all)
			return array();
		$assoc = array();
		foreach ($all as $message) {
			$assoc[$message['id']] = $message['alias'];
		}
		return $assoc;
	}

}

?>
