<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DATA_Model
 *
 * @author thibault
 */
abstract class DATA_Model extends CI_Model {

	protected $_datas = array();
	protected $_schema;

	public function getSchema() {
		if ($this->_schema === null) {
			$querySchema = $this->db->query("select * from  information_schema.columns"
					. " where table_name = '" . $this->getTableName() . "'");
			$res = $querySchema->result();
			$this->_schema = $res;
		}
		return $this->_schema;
	}

	public function buildRow($post) {
		$cols = $this->getSchema();
		$datas = array();
		foreach ($cols as $col) {
			$colName = $col->COLUMN_NAME;
			if (isset($post[$colName]) && $post[$colName]) {
				$datas[$colName] = $post[$colName];
			}
		}
		return $datas;
	}
	
	public function getData($name){
		if(isset($this->_datas[$name])){
			return $this->_datas[$name];
		}
		else if(isset($this->_datas[$this->getTableName ().'.'.$name])){
			return $this->_datas[$this->getTableName ().'.'.$name];
		}
		return null;
	}

	public function build($post) {
		$this->setDatas($this->buildRow($post));
		return $this;
	}

	public function search($limit = null, $offset = null, $search = null, $columns = null) {
		$this->prepareSearch($limit, $offset, $search, $columns);
		$this->db->select()->from($this->getTableName());
		return $this->db->get()->result();
	}

	protected function prepareSearch($limit = null, $offset = null, $search = null, $columns = null) {

		if ($columns === null && !$this->getData('columns')) {
			$columns = $this->getDataColumns();
		} else if ($columns === null) {
			$columns = $this->columns;
		}

		if ($search === null) {
			$search = $this->search;
		}

		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}

		$this->db->where(array('locale'=>locale()));
		foreach ($columns as $col) {
			$this->db->or_like($col, $search);
		}
	}

	protected function getDataColumns() {
		$schema = $this->getSchema();
		$columns = array();
		foreach ($schema as $col) {
			if ($col->DATA_TYPE === 'varchar' || $col->DATA_TYPE === 'text') {
				$columns[] = $this->getTableName().'.'.$col->COLUMN_NAME;
			}
		}
		return $columns;
	}

//	protected function getFulltextColumns() {
//		
//	}

	/**
	 * This method loads datas in the current object which will next be inserted 
	 * or updated by using a simple ->save() method without parameter
	 * allowing coding facilites like
	 * $this->mymodel->newRow($datas);
	 * $this->mymodel->last_modif = date();
	 * $this->mymodel->save();
	 * @param array $datas an array containing the new object datas.
	 */
	public function newRow($datas = null) {
		if ($datas !== null)
			$this->setDatas($datas);
		return $this;
	}

	/**
	 * This method will execute a simple select to load datas from bdd to the 
	 * current object  which will next be inserted 
	 * or updated by using a simple ->save() method without parameter
	 * allowing coding facilites like
	 * $this->mymodel->loadRow(array('id'=>4));
	 * $this->mymodel->last_modif = date();
	 * $this->mymodel->save();
	 * @param array $where the where sql clause in an array form
	 */
	public function loadRow($where) {
		$rows = $this->get($where, 'array');
		if (count($rows) == 1) {
			$this->setDatas($rows[0]);
			return true;
		}
		return false;
	}

	protected function setDatas($datas) {
		$this->_datas = array();
		foreach ($datas as $key => $value) {
			$this->_datas[$this->getTableName() . '.' . $key] = $value;
		}
	}

	public abstract function getTableName();

	public function getPrimaryColumns() {
		return array('id');
	}

	public function get($where = null, $type = 'object', $columns = null) {
		if ($columns !== null) {
			$this->db->select($columns);
		}
		$this->db->from($this->getTableName());
		if ($where !== null) {
			$this->db->where($where);
		}
		$query = $this->db->get();
		if ($query->num_rows()) {
			return $query->result($type);
		}
		return false;
	}

	public function getRow($where = array(), $type = 'object', $columns = null) {
		if ($columns !== null)
			$this->db->select($columns);
		$this->db->from($this->getTableName());
		if ($where !== null) {
			$this->db->where($where);
		}
		$query = $this->db->get();
		if ($query->num_rows()) {
			$res = $query->result($type);
			return $res[0];
		}
		return false;
	}

	public function getId($id, $type = 'object') {
		$query = $this->db->get_where($this->getTableName(), array('id' => $id));
		if ($query->num_rows()) {
			$rows = $query->result($type);
			return $rows[0];
		}
		return false;
	}

	public function getAlias($alias = null, $type = 'object', $columns = null) {
		if ($alias === null) {
			$alias = $this->alias;
		}
		if ($alias) {
			return $this->getRow(array('alias' => $alias), $type, $columns);
		}
		return false;
	}
	
	public function delete($where = null) {
		if ($where === null) {
			$where = array();
			foreach ($this->getPrimaryColumns() as $col) {
				$where[$col] = $this->{$col};
			}
			$this->clear();
		}
		return $this->db->delete($this->getTableName(), $where);
	}

	public function deleteId($id) {
		return $this->delete(array('id' => $id));
	}

	public function clear() {
		$this->_datas = array();
	}

	public function toArray() {
		$array = array();
		foreach ($this->_datas as $key => $value) {
			$array[array_pop(explode('.', $key))] = $value;
		}
		return $array;
	}

	public function __get($key) {
		if (isset($this->_datas[$this->getTableName() . '.' . $key])) {
			return $this->_datas[$this->getTableName() . '.' . $key];
		}
		return parent::__get($key);
	}

	public function __set($name, $value) {
		$this->_datas[$this->getTableName() . '.' . $name] = $value;
	}

	public function insert($datas = null) {
		if ($datas == null) {
			$datas = $this->toArray();
			$this->clear();
		}
		$this->convertArrayColumnsToJson($datas);
		$this->db->insert($this->getTableName(), $datas);
		return $this->db->insert_id();
	}

	public function update($datas = null, $where = null) {
		if ($datas == null) {
			$datas = $this->toArray();
			$this->clear();
		}
		$this->convertArrayColumnsToJson($datas);
		if ($where == null && $this->updateDatas($datas)) {
			$where = array();
			foreach ($this->getPrimaryColumns() as $col) {
				$where[$col] = $this->getData($col);
			}
			
		}
		foreach ($this->getPrimaryColumns() as $col) {
			unset($datas[$col]);
		}
		
		return $this->db->update($this->getTableName(), $datas, $where);
		
	}
	
	protected function buildWhere() {
		
	}

	public function save($datas = null, $where = null) {
		if ($datas == null) {
			$datas = $this->toArray();
		}
		$this->convertArrayColumnsToJson($datas);
		if ($this->updateDatas($datas)) {
			if ($where === null) {
				$where = $this->buildPrimaryWhere($datas);
			}
			return $this->update($datas, $where);
		} else {
			return $this->insert($datas);
		}
	}

	private function convertArrayColumnsToJson(&$datas) {
		foreach ($datas as $key => $value) {
			if (is_array($value)) { // automatic conversion of an array to json format
				$datas[$key] = json_encode($value);
			}
		}
	}

	protected function buildPrimaryWhere($datas = null) {
		if($datas == null){
			$datas = $this->toArray();
		}
		$where = array();
		foreach ($this->getPrimaryColumns() as $col) {
			if(isset($datas[$this->getTableName().'.'.$col])){
				$where[$this->getTableName() . '.' . $col] = $datas[$this->getTableName().'.'.$col];
			}else if(isset($datas[$col])){
				$where[$this->getTableName() . '.' . $col] = $datas[$col];
			}
		}
		return $where;
	}

	private function updateDatas($datas) {
		$where = array();
		foreach ($this->getPrimaryColumns() as $col) {
			if (isset($datas[$col])) {
				$where[$col] = $datas[$col];
			} else if (isset($datas[$this->getTableName() . '.' . $col])) {
				$where[$col] = $datas[$this->getTableName() . '.' . $col];
			} else {
				return false;
			}
		}
		if ($this->get($where)) {
			return true;
		}
		return false;
	}

	public function getList($limit = null, $offset = null, $type = 'object') {
		$this->db->select();
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		return $query->result($type);
	}

	public function getListOrderBy($order, $limit = null, $offset = null, $type = 'object') {
		$this->db->select();
		$this->db->from($this->getTableName());
		$this->db->order_by($order);
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		return $query->result($type);
	}

	public function count($where = null) {

		if ($where !== null) {
			$this->db->where($where);
			$this->db->from($this->getTableName());
			return $this->db->count_all_results();
		}

		return $this->db->count_all($this->getTableName());
	}

	public function insertGroup($group) {
		if (count($group)) {
			$keys = array_keys($group[0]);
			$values = array();
			foreach ($group as $datas) {
				foreach ($datas as $key => $data){
					$datas[$key] = addslashes($data);
				}
				$values[] = implode('\',\'', $datas);
			}
			$sql = 'INSERT INTO ' . $this->getTableName() . '(`' . implode('`,`', $keys) . '`) VALUES (\'' . implode('\'),(\'', $values) . '\');';
			return $this->db->query($sql);
		}
	}

	//A grouped update using mysql 'on duplicate key' key words...

	public function updateGroup($group) {
		if (count($group)) {
			$keys = array_keys(array_shift(array_values($group)));
			$values = array();
			foreach ($group as $datas) {
				foreach ($datas as $key => $data){
					$datas[$key] = addslashes($data);
				}
				$values[] = implode('\',\'', $datas);
			}
			$dataColumns = array_diff($keys, $this->getPrimaryColumns());
			$on_duplicate_col = array();
			foreach ($dataColumns as $dataColumn) {
				$on_duplicate_col[] = '`' . $dataColumn . '`=VALUES(`' . $dataColumn . '`)';
			}
			$sql = 'INSERT INTO ' . $this->getTableName() . '(`' . implode('`,`', $keys) . '`) VALUES (\'' . implode('\'),(\'', $values) . '\')'
					. ' ON DUPLICATE KEY UPDATE ' . implode(',', $on_duplicate_col) . ';';

			return $this->db->query($sql);
		}
	}

	public function convertToHtml(&$elements, $columns) {
		$this->load->library('BBCodeParser', '', 'parser');
		if (!is_array($elements)) {
			if (!is_array($columns)) {
				$this->parser->convertToHtml($elements->{$columns});
			} else {
				foreach ($columns as $col) {
					$this->parser->convertToHtml($elements->{$col});
				}
			}
		} else {
			foreach ($elements as &$element) {
				if (!is_array($columns)) {
					$this->parser->convertToHtml($element->{$columns});
				} else {
					foreach ($columns as $col) {
						$this->parser->convertToHtml($element->{$col});
					}
				}
			}
		}
	}

	public function getRank($order, $id = null) {
		if ($id === null) {
			$id = $this->_datas['id'];
		} else {
			$this->loadRow(array('id' => $id));
		}
		$query = 'SELECT COUNT(*) as count
        FROM ' . $this->getTableName() . '
		WHERE ' . $this->getTableName() . '.' . $order . ' >= \'' . $this->{$order} . '\'';
		$res = $this->db->query($query)->result();
		return $res[0]->count;
	}

	protected function createAliasFrom($str, $update = FALSE) {
		$str = str_replace('+', 'plus', $str);
		$str = str_replace('#', 'sharp', $str);
		$str = str_replace(array('\'','"'), array('-','-'), $str);
		$str = htmlentities($str, ENT_NOQUOTES, 'utf-8');
		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('/\s+/', '_', $str);
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);
		$clean = trim($clean, '-');
		$alias = strtolower($clean);
		
		$this->db->from($this->getTableName());
		$this->db->where('alias', $alias);
		$count = $this->db->count_all_results();
		$lim = ($update) ? 1 : 0;
		if ($count > $lim) {
			return $alias . '-' . $count;
		}

		return $alias;
	}
	
	public function unlink($columns, $datas = null){
		
		if($datas === null){
			$datas = $this->toArray();
		}
		if($columns && is_string($columns)){
			$columns = array($columns);
		}
		$doUnlink = $columns;
		foreach ($columns as $column){
			$doUnlink |= isset($datas[$column]);
		}
		if($doUnlink) {
			$load = $this->getRow($this->buildPrimaryWhere($datas));
			foreach ($columns as $column) {
				if(isset($datas[$column]) && $datas[$column] != $load->{$column}){
					unlink($load->{$column});
				}
			}
		}
	}

//	private function hasAlias() {
//		$schema = $this->getSchema();
//		foreach($schema as $col){
//			if($col->COLUMN_NAME == 'alias'){
//				return true;
//			}
//		}
//		return false;
//	}
}

?>
