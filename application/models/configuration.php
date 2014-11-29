<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of configuration
 *
 * @author thibault
 */
class configuration extends DATA_Model {
	
	private $_configDatas;

	public function getTableName() {
		return 'configuration';
	}

	public function getPrimaryColumns() {
		return array('key');
	}
	
	private function initConfigDatas() {
		$datas = $this->getList();
		$this->_configDatas = array();
		foreach ($datas as $value) {
			$this->_configDatas[$value->key] = $value->value;
		}
		return $datas;
	}
	
	private function getConfigDatas() {
		if(!$this->_configDatas){
			$this->initConfigDatas();
		}
		return $this->_configDatas;
	}
	
	public function getValues() {
		return $this->getConfigDatas();
	}
	
	public function setValues($values){
		$group = array();
		foreach($values as $key => $value){
			if(is_int($key) && is_array($value) && isset($value['key']) && isset($value['value'])){
				$group[] = $value;
			}
			else if(is_string($key) && is_string($value)){
				$group[] = array('key' => $key, 'value' => $value);
			}
		}
		$this->updateGroup($group);
	}

	public function setValue($key, $value, $description = null) {
		$saved = array('key' => $key, 'value' => $value);
		if ($description) {
			$saved['description'] = $description;
		}
		$this->save($saved);
	}

	public function getValue($key) {
		if($this->_configDatas){
			return isset($this->_configDatas[$key]) ? $this->_configDatas[$key] : false;
		}
		$row = $this->getRow(array('key' => $key));
		if ($row) {
			return $row->value;
		}
		return false;
	}

}

?>
