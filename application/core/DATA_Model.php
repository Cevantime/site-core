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
	protected $_extendedSchema;
	protected $_modelName;
	protected $_extendingTables;

	protected function getModelName() {
		if(!$this->_modelName){
			$class = get_class();
			$this->_modelName = strtolower($class);
		}
		return $this->_modelName;
	}

	public function getSchema($extended = true) {
		if($extended){
			if ($this->_extendedSchema === null) {
				$this->_extendedSchema = array();
				$tables = $this->getExtendingTables();
				foreach ($tables as $table){
					$querySchema = $this->db->query('DESC '. $table);
					$res = $querySchema->result();
					$cols = array();
					foreach($res as $colInfo){
						$cols[] = $colInfo->Field;
					}
					$this->_extendedSchema = array_merge($this->_extendedSchema, $cols);
				}
				$this->_extendedSchema = array_unique($this->_extendedSchema);
			}
			return $this->_extendedSchema;
		} else {
			if ($this->_schema === null) {
				$this->_schema = array();
				$querySchema = $this->db->query('DESC ' . $this->getTableName());
				$res = $querySchema->result();
				$cols = array();
				foreach($res as $colInfo){
					$cols[] = $colInfo->Field;
				}
				$this->_schema = array_merge($this->_schema, $cols);
				
			}
			return $this->_schema;
		}
	}
	/**
	 * if table name is users_admin_root, extended tables will be :
	 * [users, users_admin, user_admin_root]
	 * @return type
	 */
	public function getExtendingTables() {
		if($this->_extendingTables === null) {
			$tableName = $this->getTableName();
			$segments = explode('_', $tableName);
			$curTable = $segments[0];
			$this->_extendingTables[] = $curTable;
			$nbSegs = count($segments);
			for($i=1;$i<$nbSegs; $i++){
				$seg = $segments[$i];
				$curTable .= '_'.$seg;
				$this->_extendingTables[] = $curTable;
			}
		}
		return $this->_extendingTables;
		
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
		return $this->get();
	}
	
	protected function makeExtendingJoins() {
		if(!$this->isExtendingModel()){
			return;
		}
		$extendingTables = $this->getExtendingTables();
		$baseTable = $extendingTables[0];
		$baseModel = substr(strtolower($baseTable), 0,  strlen($baseTable)-1);
		$this->load->model($baseModel);
		$primaryColumns = $this->$baseModel->getPrimaryColumns();
		if(count($primaryColumns)>1) {
			//multi primary column link
			// not supported yet
			return;
		}
		$key = $primaryColumns[0];
		$this->db->join($baseTable, $baseTable.'.'.$key.' = '.$this->getTableName().'.'.$key, 'left');
		for($i = 1; $i < count($extendingTables) - 1; $i++){
			$table = $extendingTables[$i];
			$this->db->join($table, $table.'.'.$key.' = '.$this->getTableName().'.'.$key, 'left');
		}
	}
	
	protected function isExtendingModel() {
		return count($this->getExtendingTables()) > 1;
	}

	protected function getBaseTableName() {
		return $this->getExtendingTables()[0];
	}
	
	protected function getBaseModelName() {
		$baseTable = $this->getBaseTableName();
		return substr(strtolower($baseTable), 0,  strlen($baseTable)-1);
	}

	protected function prepareSearch($limit = null, $offset = null, $search = null, $columns = null) {
		$this->makeExtendingJoins();
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
		$this->makeExtendingJoins();
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
		$this->makeExtendingJoins();
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
		$this->makeExtendingJoins();
		if ($columns !== null)
			$this->db->select($columns);
		$this->db->from($this->getTableName());
		if ($where) {
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
		$this->makeExtendingJoins();
		$query = $this->db->get_where($this->getTableName(), array($this->getTableName().'.id' => $id));
		if ($query->num_rows()) {
			$rows = $query->result($type);
			return $rows[0];
		}
		return false;
	}

	public function getAlias($alias = null, $type = 'object', $columns = null) {
		$this->makeExtendingJoins();
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
		return $this->db->delete($this->getBaseTableName(), $where);
	}

	public function deleteId($id) {
		return $this->delete(array($this->getBaseTableName().'.id' => $id));
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
		$baseModel = $this->getBaseModelName();
		$this->load->model($baseModel);
		$specificSchema = $this->$baseModel->getSchema(false);
		$datasToInsert = array();
		foreach($specificSchema as $col){
			if(array_key_exists($col, $datas))$datasToInsert[$col] = $datas[$col];
		}
		$this->db->insert($this->getBaseTableName(), $datasToInsert);
		
		$insertedId =  $this->db->insert_id();
		$extendingTables = $this->getExtendingTables();
		$primaryCols = $this->$baseModel->getPrimaryColumns();
		if(count($primaryCols) > 1){
			// insert on multi primary cols
			// not supported yet
			return $insertedId;
		}
		$key = $primaryCols[0];
		$datas[$key] =  $insertedId;
		for($i=1; $i<count($extendingTables); $i++){
			$table = $extendingTables[$i];
			$model = end(explode('_', $table));
			$this->load->model($model);
			$specificSchema = $this->$model->getSchema(false);
			$datasToInsert = array();
			foreach($specificSchema as $col){
				if(array_key_exists($col, $datas))$datasToInsert[$col] = $datas[$col];
			}
			$this->db->insert($table, $datasToInsert);
		}
		return $insertedId;
	}

	public function update($datas = null, $where = null) {
		if ($datas == null) {
			$datas = $this->toArray();
			$this->clear();
		}
		$this->convertArrayColumnsToJson($datas);
		$primaries = $this->getPrimaryColumns();
		if ($where === null) {
			$where = array();
			foreach ($primaries as $col) {
				$where[$this->getTableName().'.'.$col] = $datas[$col];
			}
		}
		foreach ($primaries as $col) {
			unset($datas[$col]);
		}
		
		if($this->isExtendingModel() && count($primaries)==1) {
			$baseModel = $this->getBaseModelName();
			$baseTable = $this->getBaseTableName();
			$this->load->model($baseModel);
			$cols = $this->$baseModel->getSchema();
			$datasToUpdate = array();
			foreach ($cols as $col) {
				if(isset($datas[$col])) {
					if(array_key_exists($col, $datas))$datasToUpdate[$col] = $datas[$col];
				}
			}
			$key = $primaries[0];
			$idsToUpdateRow = $this->get($where, 'object', array($this->getTableName().'.'.$key));
			$idsToUpdate = array();
			foreach ($idsToUpdateRow as $row){
				$idsToUpdate[] = $row->id;
			}
			if(!$idsToUpdate)return false;
			$this->db->where_in($key, $idsToUpdate);
			$ret = $this->db->update($baseTable, $datasToUpdate);
			$extendingTables = $this->getExtendingTables();
			$nbExtendingTables = count($extendingTables);
			
			for($i = 1; $i < $nbExtendingTables; $i++){
				$table = $extendingTables[$i];
				$model = end(explode('_',$table));
				$cols = $this->$model->getSchema(false);
				$datasToUpdate = array();
				foreach ($cols as $col) {
					if (isset($datas[$col])) {
						if(array_key_exists($col, $datas))$datasToUpdate[$col] = $datas[$col];
					}
				}
				$this->db->where_in($key, $idsToUpdate);
				$this->db->update($table, $datasToUpdate);
			}
			return $ret;
		} else {
			return $this->db->update($this->getTableName(), $datas, $where);
		}
		
	}
	
	protected function buildWhere() {
		
	}

	public function save($datas = null, $where = null) {
		if ($datas == null) {
			$datas = $this->toArray();
		}
		$this->convertArrayColumnsToJson($datas);
		if ($this->updateDatas($datas)) {
			return $this->update($datas);
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
				$where[$this->getTableName() . '.'.$col] = $datas[$col];
			} else if (isset($datas[$this->getTableName() . '.' . $col])) {
				$where[$this->getTableName() . '.'.$col] = $datas[$this->getTableName() . '.' . $col];
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
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		return $this->get('1', $type);
	}

	public function getListOrderBy($order, $limit = null, $offset = null, $type = 'object') {
		$this->db->order_by($order);
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		return $this->get('1',$type);
	}

	public function count($where = null) {
		$this->makeExtendingJoins();
		if ($where !== null) {
			$this->db->where($where);
			$this->db->from($this->getTableName());
			return $this->db->count_all_results();
		}

		return $this->db->count_all($this->getTableName());
	}

	public function insertGroup($group) {
		if (count($group)) {
			$concernedTables = $this->getExtendingTables();
			$models = array($this->getBaseModelName());
			for($i=1; $i<count($concernedTables); $i++){
				$models[] = end(explode('_', $concernedTables[$i]));
			}
			$lastInsertedId = null;
			foreach($models as $model){
				$this->load->model($model);
				$schema = $this->$model->getSchema(false);
				$subGroup = array();
				foreach ($group as $datas) {
					$associatedDatas = array();
					foreach ($schema as $col){
						if(array_key_exists($col, $datas))$associatedDatas[$col] = $datas[$col];
					}
					$subGroup[] = $associatedDatas;
				}
				$this->insertSubGroup($model, $subGroup, $lastInsertedId);
				$lastInsertedId = $this->db->insert_id();
			}
		}
	}
	
	private function insertSubGroup($model, $subGroup, $lastInsertedId) {
		if (count($subGroup)) {
			$keys = array_keys($subGroup[0]);
			if($lastInsertedId !== null){
				$keys[] = $this->getPrimaryColumns()[0];
			}
			
			$this->load->model($model);
			$values = array();
			foreach ($subGroup as $datas) {
				foreach ($datas as $key => $data){
					$datas[$key] = $this->db->escape($data);
				}
				if($lastInsertedId !== null){
					$datas[$this->getPrimaryColumns()[0]] = $lastInsertedId++;
				}
				$values[] = implode(',', $datas);
			}
			$sql = 'INSERT INTO ' . $this->$model->getTableName() . '(`' . implode('`,`', $keys) . '`) VALUES (' . implode('),(', $values) . ');';
			return $this->db->query($sql);
		}
	}

	//A grouped update using mysql 'on duplicate key' key words...

	public function updateGroup($group) {
		if (count($group)) {
			$concernedTables = $this->getExtendingTables();
			$models = array($this->getBaseModelName());
			for($i=1; $i<count($concernedTables); $i++){
				$models[] = end(explode('_', $concernedTables[$i]));
			}
			foreach($models as $model){
				$this->load->model($model);
				$schema = $this->$model->getSchema(false);
				$subGroup = array();
				foreach ($group as $datas) {
					$associatedDatas = array();
					foreach ($schema as $col){
						if(array_key_exists($col, $datas))$associatedDatas[$col] = $datas[$col];
					}
					$subGroup[] = $associatedDatas;
				}
				$this->updateSubGroup($model, $subGroup);
				
			}
		}
		
	}
	
	private function updateSubGroup($model, $group) {
		if (count($group)) {
			$keys = array_keys(array_shift(array_values($group)));
			$values = array();
			foreach ($group as $datas) {
				foreach ($datas as $key => $data){
					$datas[$key] = $this->db->escape($data);
				}
				$values[] = implode(',', $datas);
			}
			$this->load->model($model);
			$dataColumns = array_diff($keys, $this->$model->getPrimaryColumns());
			$on_duplicate_col = array();
			foreach ($dataColumns as $dataColumn) {
				$on_duplicate_col[] = '`' . $dataColumn . '`=VALUES(`' . $dataColumn . '`)';
			}
			$sql = 'INSERT INTO ' . $this->$model->getTableName() . '(`' . implode('`,`', $keys) . '`) VALUES (' . implode('),(', $values) . ')'
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
		$this->makeExtendingJoins();
		$hasId = $id !== null;
		if (!$hasId) {
			$id = $this->_datas['id'];
		} else {
			$this->loadRow(array('id' => $id));
		}
		$query = 'SELECT COUNT(*) as count
        FROM ' . $this->getTableName() . '
		WHERE ' . $this->getTableName() . '.' . $order . ' >= ' . $this->db->escape($this->{$order}) . '';
		$res = $this->db->query($query)->result();
		if (!$hasId) {
			$this->clear();
		}
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
	
	public function getTrough($table, $value, $key = 'id'){
		$curTable = $this->getTableName();
		$linkTable = 'links_'.$table.'_'.$curTable;
		return $this->get($key.' IN ('
				. 'SELECT '.substr(strtolower($curTable), 0, strlen($curTable) - 1).'_'.$key.' '
				. 'FROM '.$linkTable.' '
				. 'WHERE '.substr(strtolower($table), 0, strlen($table) - 1).'_'.$key.' '
				. '= '. $this->db->escape($value).')');
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
