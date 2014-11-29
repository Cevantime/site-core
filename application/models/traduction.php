<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of traduction
 *
 * @author thibault
 */
class traduction extends DATA_Model{
	
	const TABLE_NAME = 'traductions';
	
	private $traductions = array();
	
	public function getTableName() {
		return self::TABLE_NAME;
	}
	
	public function __construct() {
		parent::__construct();
//		$locale = locale();
		//if($locale !== 'fr'){
			$rows = $this->getList();
			foreach ($rows as $row) {
				$this->traductions[trim($row->fr)] = (array)$row;
			}
		//}
	}
	
	public function registerTrad($lang, $key, $value=""){
		$key = trim($key);
		if(!isset($this->traductions[$key])){
			$this->traductions[$key] = array();
		}
		$this->traductions[$key][$lang] = $value;
	}
	
	public function save() {
		$this->updateGroup($this->traductions);
	}

	public function translate($french){
		$french = trim($french);
		$locale = locale();
		//if($locale ==='fr') return $french;
		if(!isset($this->traductions[$french])){
			$this->insert(array('fr'=> $french));
			$this->traductions[$french] = array();
			$this->traductions[$french][$locale] = '';
			return $french;
		}
		return $this->traductions[$french][$locale];
	}
	
	public function getTraductions() {
		return $this->traductions;
	}

}
