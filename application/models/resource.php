<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of video
 *
 * @author thibault
 */
class resource extends DATA_Model {

	const TABLE_NAME = 'resources';
	
	public function getTableName() {
		return self::TABLE_NAME;
	}
	
	public function insert($datas = null) {
		if($datas === null){
			$datas = $this->toArray();
		}
		$datas['alias'] = $this->createAliasFrom($datas['title']);
		parent::insert($datas);
	}
	
	public function delete($where = null) {
		if($where === null){
			$where = $this->buildPrimaryWhere();
		}
		$row= $this->getRow($where);
		unlink($row->src);
		parent::delete($where);
	}
	
	public function download($where = null){
		if($where === null){
			$res = $this;
		} else {
			$res = $this->getRow($where);
		}
		$CI =& get_instance();
		$CI->load->helper('download');
		$ext = array_pop(explode('.', $res->src));
		force_download($res->alias.'.'.$ext, file_get_contents($res->src));
	}
}