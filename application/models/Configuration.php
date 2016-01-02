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
class Configuration extends DATA_Model {
	
	private $_configDatas;

	public function getTableName() {
		return 'configurations';
	}

	public function getPrimaryColumns() {
		return array('key');
	}
	
	private function initConfigDatas() {
		$datas = $this->getList();
		$this->_configDatas = array();
		if($datas){
			foreach ($datas as $value) {
				$this->_configDatas[$value->key] = $value->value;
			}
			
		}
		return $this->_configDatas;
	}
	
	private function getConfigDatas() {
		if(!$this->_configDatas){
			$this->initConfigDatas();
		}
		return $this->_configDatas;
	}
	
	public function getValues($cols = null) {
		$values = $this->getConfigDatas();
		if($cols){
			$wantedValues = array();
			if(!is_array($cols)){
				$cols = array($cols);
			}
			foreach ($cols as $key => $value) {
				if(is_int($value)){
					$wantedValues[$value] = isset($values[$value]) ? $values[$value] : null;
				} else {
					$wantedValues[$key] = isset($values[$key]) ? $values[$key] : $value;
				}
			}
			return $wantedValues;
		}
		return $values;
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

	public function getValue($key, $default = null) {
		$configDatas = $this->getConfigDatas();
		
		return isset($configDatas[$key]) ? $configDatas[$key] : ($default ? $default : false); 
	}

}

?>
