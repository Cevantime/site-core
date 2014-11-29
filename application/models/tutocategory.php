<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tutoCategory
 *
 * @author thibault
 */
class tutoCategory extends DATA_Model {

	public function getTableName() {
		return 'tuto_categories';
	}

	public function insert($datas = null) {
		if ($datas === null) {
			$datas = $this->toArray();
		}
		if (!isset($datas['alias'])) {
			$datas['alias'] = $this->createAliasFrom($datas['name']);
		}
		parent::insert($datas);
	}
	
	public function update($array = null, $where = null) {
		$array = ($array !== null) ? $array : $this->_datas;
		if(isset($array['title'])){
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
		}
		return $assoc;
	}
	
	public function getAllAsIdNameAssociativeArray2() {
		$all = $this->get(null, 'array');
		if (!$all)
			return array();
		$assoc = array();
		foreach ($all as $category) {
			$assoc[$category['id']] = $category['alias'];			
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
