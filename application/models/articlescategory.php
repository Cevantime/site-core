<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of articleCategory
 *
 * @author thibault
 */
class articlesCategory extends DATA_Model {

	public function getTableName() {
		return 'articles_categories';
	}

	public function insert($array = null) {
		if ($array === null) {
			$array = $this->toArray();
		}
		if (!isset($array['alias'])) {
			$array['alias'] = $this->createAliasFrom($array['name']);
		}
		return parent::insert($array);
	}
	
	public function update($array = null, $where = null) {
		$array = ($array !== null) ? $array : $this->toArray();
		if(isset($array['name'])){
			$array['alias'] = $this->createAliasFrom($array['name'], true);
		}
		
		$this->unlink('icon', $array);
		
		parent::update($array, $where);
	}

	public function getAllAsIdNameAssociativeArray() {
		$all = $this->get(null, 'array');
		if (!$all)
			return array();
		$assoc = array();
		foreach ($all as $category) {
			$assoc[$category['id']] = $category['name'];
			//$assoc['alias'] = $category['alias'];
		}
		return $assoc;
	}
	
	public function getAllAsIdNameAssociativeArray2() {
		$all = $this->get(null, 'array');
		if (!$all)
			return array();
		$assoc = array();
		foreach ($all as $category) {
			$assoc[$category['id']]  = $category['alias'];
		}
		return $assoc;
	}

	public function deleteId($id = null) {
		if ($id == null) {
			$id = $this->id;
		} else {
			$this->loadRow(array('id' => $id));
		}
		unlink($this->icon);
		$this->delete();
	}

}

?>
