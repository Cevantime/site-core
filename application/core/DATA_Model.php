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
	protected $_lastValidationErrors;
	protected $_extendedTables;
	protected $_extendedClasses;
	protected $_extendedInstances;
	protected $_joins;
	protected $_compileQueryOnly = false;
	protected $_columnsToTranslate;
	protected $_lastSavedDatas;

	protected function getModelName() {
		if (!$this->_modelName) {
			$class = get_class();
			$this->_modelName = strtolower($class);
		}
		return $this->_modelName;
	}

	public function getSchema($extended = true) {
		if ($extended) {
			if ($this->_extendedSchema === null) {
				$this->_extendedSchema = array();
				$tables = $this->getExtendedTables();
				foreach ($tables as $table) {
					$fields = $this->db->list_fields($this->db->dbprefix($table));
					$cols = array();
					foreach ($fields as $field) {
						$cols[$table . '.' . $field] = $field;
					}
					$this->_extendedSchema = array_merge($this->_extendedSchema, $cols);
				}
				$this->_extendedSchema = array_unique($this->_extendedSchema);
			}
			return $this->_extendedSchema;
		} else {
			if ($this->_schema === null) {
				$this->_schema = array();
				$table = $this->getTableName();
				$fields = $this->db->list_fields($this->db->dbprefix($table));
				$cols = array();
				foreach ($fields as $field) {
					$cols[] = $field;
				}
				$this->_schema = array_merge($this->_schema, $cols);
			}
			return $this->_schema;
		}
	}

	public function getExtendedClasses() {
		if (!$this->_extendedClasses) {
			$currentClass = get_class($this);
			$parentClasses = array_values(class_parents($this));
			// put the base table first
			$nb_classes = count($parentClasses);
			for ($i = $nb_classes - 3; $i >= 0; $i--) {
				$parentClass = $parentClasses[$i];
				$this->_extendedClasses[] = $parentClass;
			}
			$this->_extendedClasses[] = $currentClass;
		}
		return $this->_extendedClasses;
	}

	public function compileQueryOnly($compile) {
		$this->_compileQueryOnly = $compile;
	}

	public function getBaseClass() {
		return $this->getExtendedClasses()[0];
	}

	protected function loadExtendedInstance($model) {
		if (!isset($this->$model)) {
			$classname = ucfirst($model);
			$this->$model = new $classname();
		}
	}

	/**
	 * if table name is users_admin_root, extended tables will be :
	 * [users, users_admin, user_admin_root]
	 * @return type
	 */
	public function getExtendedTables() {
		if ($this->_extendedTables === null) {
			$this->_extendedTables = array();
			$extendedClasses = $this->getExtendedClasses();
			foreach ($extendedClasses as $extendedClass) {
				$model = strtolower($extendedClass);
				$this->loadExtendedInstance($model);
				$this->_extendedTables[] = $this->$model->getTableName();
			}
		}

		return $this->_extendedTables;
	}

	public function buildRow($post) {
		$cols = $this->getSchema();
		$datas = array();
		foreach ($cols as $col) {
			$colName = $col;
			if (isset($post[$colName])) {
				$datas[$colName] = $post[$colName];
			}
		}
		return $datas;
	}

	public function getData($name) {
		if (isset($this->_datas[$name])) {
			return $this->_datas[$name];
		} else if (isset($this->_datas[$this->getTableName() . '.' . $name])) {
			return $this->_datas[$this->getTableName() . '.' . $name];
		}
		return null;
	}

	protected function beforeInsert(&$to_insert = null) {
		
	}

	protected function afterInsert($insert_id, &$to_insert = null) {
		
	}

	protected function beforeUpdate(&$datas = null, $where = null) {
		
	}

	protected function afterUpdate(&$datas = null, $where = null) {
		
	}

	public function build($post) {
		$this->setDatas($this->buildRow($post));
		return $this;
	}

	public function search($limit = null, $offset = null, $search = null, $columns = null) {
		$this->prepareSearch($limit, $offset, $search, $columns);
		return $this->get();
	}

	protected function makeExtendedJoins() {
		if ($this->isExtendingModel()) {
			$extendingTables = $this->getExtendedTables();
			$baseTable = $extendingTables[0];
			$baseModel = $this->getBaseModelName();
			$this->loadExtendedInstance($baseModel);
			$primaryColumns = $this->$baseModel->getPrimaryColumns();
			if (count($primaryColumns) > 1) {
				//multi primary column link
				// not supported yet
				return;
			}
			$key = $primaryColumns[0];
			$this->db->join($baseTable, $this->db->dbprefix($baseTable) . '.' . $key . ' = ' . $this->getTableName() . '.' . $key, 'left');
			for ($i = 1; $i < count($extendingTables) - 1; $i++) {
				$table = $extendingTables[$i];
				$this->db->join($table, $this->db->dbprefix($table) . '.' . $key . ' = ' . $this->getTableName() . '.' . $key, 'left');
			}
		}

		$columnsToTranslate = $this->columnsToTranslate();

		if (is_module_installed('traductions') && !empty($columnsToTranslate)) {
			$this->load->helper('locale');
			$locale = locale();
			$tableName = $this->getTableName();
			$tableTranslationsName = $tableName . '_translations';
			$this->db->join($tableTranslationsName, "$tableTranslationsName.id = $tableName.id AND $tableTranslationsName.lang = '$locale'", 'left');
		}

		if ($this->_joins) {
			foreach ($this->_joins as $join) {
				$this->db->join($join['table'], $join['cond'], $join['type'], $join['escape']);
			}
		}
		$this->_joins = array();
	}

	protected function isExtendingModel() {
		return count($this->getExtendedTables()) > 1;
	}

	protected function getBaseTableName() {
		return $this->getExtendedTables()[0];
	}

	protected function getBaseModelName() {
		return strtolower($this->getExtendedClasses()[0]);
	}

	protected function prepareSearch($limit = null, $offset = null, $search = null, $columns = null) {
		if ($columns === null && !$this->getData('columns')) {
			$columns = $this->getDataColumns();
		} else if ($columns === null) {
			$columns = $this->getData('columns');
		}

		if ($search === null) {
			$search = $this->search;
		}

		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$this->db->group_start();
		foreach ($columns as $col) {
			$this->db->or_like($col, $search);
		}
		$this->db->group_end();
	}

	protected function getDataColumns() {
		$schema = $this->getSchema();
		$columns = array();
		foreach ($schema as $col) {
			if ($col->DATA_TYPE === 'varchar' || $col->DATA_TYPE === 'text') {
				$columns[] = $this->getTableName() . '.' . $col->COLUMN_NAME;
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
		if ($rows && count($rows) == 1) {
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

	public function validationRulesForInsert($datas) {
		return array();
	}

	public function validationRulesForUpdate($datas) {
		return array();
	}

	public function uploadPaths() {
		return false;
	}

	public function getPrimaryColumns() {
		return array('id');
	}

	public function fromPost() {
		return $this->fromDatas();
	}

	public function fromDatas($datas = null) {
		$this->resetErrors();
		$primaries = $this->getPrimaryColumns();
		$isUpdate = true;
		$input = $datas ? $datas : $_POST;

		foreach ($primaries as $primary) {
			if (!isset($input[$primary])) {
				$isUpdate = false;
				break;
			}
		}
		$rules = $isUpdate ? $this->validationRulesForUpdate($input) : $this->validationRulesForInsert($input);
		$this->load->library('form_validation');
		$this->form_validation->reset_validation();
		if ($datas) {
			$this->form_validation->set_data($datas);
		}
		$this->form_validation->set_rules($rules);
		if (!$this->form_validation->run()) {
			$this->addErrors($this->form_validation->error_array());
			return false;
		}
		$this->load->library('upload');
		$uploadPaths = $this->uploadPaths();
		if ($uploadPaths) {
			$files = $_FILES;
			$this->load->library('upload');
			foreach ($uploadPaths as $key => $uploadPath) {
				if(!$this->doUpload($datas, $uploadPath, $key)){
					$this->addErrors($this->upload->error_msg);
					return false;
				}
			}
			$this->addErrors($this->upload->error_msg);
		}
		if ($datas) {
			return $this->save($this->filterInvalidFields($datas));
		}
		return $this->save($this->filterInvalidFields($_POST));
	}

	protected function doUpload(&$datas, $uploadPath, $key) {
		if($_FILES[$key]) {
			$this->upload->initialize(array('upload_path' => './' . $uploadPath, 'allowed_types' => '*', 'file_name' => uniqid()));
			if ($this->upload->do_upload($key)) {
				if ($datas) {
					$datas[$key] = $uploadPath . '/' . $this->upload->file_name;
				} else {
					$_POST[$key] = $uploadPath . '/' . $this->upload->file_name;
				}
			} else {
				return false;
			}
			
		}
		return true;
	}

	public function getLastErrors() {
		return $this->_lastValidationErrors;
	}
	
	public function getLastErrorsString($prefix= '<p>',$suffix = '</p>') {
		// Generate the error string
        $str = '';
        foreach ($this->_lastValidationErrors as $val)
        {
            if ($val !== '')
            {
                //if field has more than one error, then all will be listed
                if (is_array($val))
                {
                    foreach ($val as $v)
                    {
                        $str .= $prefix . $v . $suffix . "\n";
                    }
                }
                else
                {
                    $str .= $prefix . $val . $suffix . "\n";
                }

            }
        }

		return $str;
	}
	
	private function addErrors($errors) {
		$this->_lastValidationErrors = array_merge($this->_lastValidationErrors,$errors);
	}
	
	private function resetErrors() {
		$this->_lastValidationErrors = array();
	}

	public function get($where = null, $type = 'object', $columns = null) {
		$this->prepareGet($where, $type, $columns);
		
		if ($this->_compileQueryOnly) {
			return $this->db->get_compiled_select();
		}
		$query = $this->db->get();
		$numRows = $query->num_rows();
		if ($numRows) {
			$res = $query->result($type);
			$res = $this->translate($res);
			return $res;
		}
		return false;
	}

	public function translate($res) {
		if (!is_module_installed('traductions'))
			return $res;
		$numRows = count($res);
		$columnsToTranslate = $this->columnsToTranslate();
		for ($i = 0; $i < $numRows; $i++) {
			$row = $res[$i];
			$isObject = is_object($row);
			if ($isObject)
				$row = (array) $row;
			foreach ($row as $field => $value) {
				if (array_key_exists($field . '_lang', $row)) {
					$translation = $row[$field . '_lang'];
					unset($row[$field . '_lang']);
					if (in_array($field, $columnsToTranslate)) {
						if ($translation) {
							$row[$field] = $translation;
						}
					}
				}
			}
			if ($isObject)
				$row = (object) $row;
			$res[$i] = $row;
		}
		return $res;
	}

	public function prepareGet($where = array(), $type = 'object', $columns = null) {
		$this->makeExtendedJoins();
		if (!$columns) {
			$columns = array($this->db->dbprefix($this->getTableName()).'.*');
		} else if (is_string($columns)) {
			$columns = array($columns);
		}
		if ($this->getTableName() !== $this->getBaseTableName()) {
			$columns = array();
			foreach ($this->getSchema() as $col => $alias) {
				$columns[] = $this->db->dbprefix($col) . ' AS ' . $alias;
			}
		}
		if (is_module_installed('traductions') && $this->columnsToTranslate()) {
			foreach ($this->columnsToTranslate() as $col) {
				$columns[] = $this->db->dbprefix($col . '_lang') . ' AS ' . $col . '_lang';
			}
			$columns[] = $this->db->dbprefix('lang') . ' AS lang';
		}
		$columns = implode(',', $columns);
		$this->db->select($columns);
		$this->db->from($this->getTableName());
		if ($where !== null) {
			$this->db->where($where);
		}
	}

	public function getRow($where = array(), $type = 'object', $columns = null) {

		$this->db->limit(1);

		$get = $this->get($where, $type, $columns);
		if (is_string($get))
			return $get;
		if ($get) {
			return $get[0];
		}

		return false;
	}

	public function getId($id, $type = 'object', $columns = null) {
		return $this->getRow(array($this->db->dbprefix($this->getTableName()) . '.id' => $id), $type, $columns);
	}

	public function getAlias($alias, $type = 'object', $columns = null) {
		return $this->getRow(array($this->db->dbprefix($this->getTableName()) . '.alias' => $alias), $type, $columns);
	}

	public function delete($where = null) {
		if ($where === null) {
			$where = array();
			foreach ($this->getPrimaryColumns() as $col) {
				$where[$col] = $this->{$col};
			}
		}
		return $this->db->delete($this->getBaseTableName(), $where);
	}

	public function deleteId($id) {
		return $this->delete(array($this->db->dbprefix($this->getBaseTableName()) . '.id' => $id));
	}

	public function join($table, $cond, $type = '', $escape = '') {
		$this->_joins[] = array('table' => $table, 'cond' => $cond, 'type' => $type, 'escape' => $escape);
	}

	public function clear() {
		$this->_datas = array();
	}

	public function exists($key = null) {
		$primaryColumns = $this->getPrimaryColumns();
		$where = array();
		if (!$key) {
			$where = $this->buildPrimaryWhere();
		} else {
			if (!is_array($key)) {
				$where = array($this->db->dbprefix($this->getTableName()) . '.' . $primaryColumns[0] => $key);
			} else {
				$where = $key;
			}
		}

		return $this->getRow($where) != false;
	}

	public function toArray() {
		$array = array();
		foreach ($this->_datas as $key => $value) {
			$exploded = explode('.', $key);
			$array[end($exploded)] = $value;
		}
		return $array;
	}
	
	public function toObject() {
		$obj = new stdClass();
		foreach ($this->_datas as $key => $value) {
			$field= array_pop(explode('.', $key));
			$obj->{$field} = $value;
		}
		return $obj;
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

	public function filterInvalidFields(&$datas) {
		$schema = $this->getSchema();
		foreach ($datas as $key => $data) {
			if (!in_array($key, $schema)) {
				unset($datas[$key]);
			}
		}
		return $datas;
	}

	public function extendTo($targetModel, $datas = null) {
		if (!$datas) {
			$datas = $this->toArray();
		}
		$modelName = pathinfo($targetModel)['filename'];
		$this->load->model($targetModel);
		if (!in_array(get_class(), $this->$modelName->getExtendedClasses())) {
			return false;
		}
		$specificSchema = $this->$modelName->getSchema(false);
		$datasToInsert = array();
		foreach ($specificSchema as $col) {
			if (array_key_exists($col, $datas))
				$datasToInsert[$col] = $datas[$col];
		}
		$ret = $this->db->insert($this->$modelName->getTableName(), $datasToInsert);
		$this->_lastSavedDatas = $datasToInsert;
		return $ret;
	}

	public function insert($datas = null) {
		if ($datas == null) {
			$datas = $this->toArray();
			$this->clear();
		}
		$this->beforeInsert($datas);
		$this->convertArrayColumnsToJson($datas);
		$baseModel = $this->getBaseModelName();
		$this->loadExtendedInstance($baseModel);
		$specificSchema = $this->$baseModel->getSchema(false);
		$datasToInsert = array();
		foreach ($specificSchema as $col) {
			if (array_key_exists($col, $datas))
				$datasToInsert[$col] = $datas[$col];
		}
		$this->db->insert($this->getBaseTableName(), $datasToInsert);

		$insertedId = $this->db->insert_id();
		$extendedTables = $this->getExtendedTables();
		$extendedClasses = $this->getExtendedClasses();
		$primaryCols = $this->$baseModel->getPrimaryColumns();
		if (count($primaryCols) > 1) {
			// insert on multi primary cols
			// not supported yet
			$this->_lastSavedDatas = $datas;
			$this->afterInsert($insertedId, $datas);
			return $insertedId;
		}
		$key = $primaryCols[0];
		$datas[$key] = $insertedId;
		
		for ($i = 1; $i < count($extendedTables); $i++) {
			$table = $extendedTables[$i];
			$model = strtolower($extendedClasses[$i]);
			$this->loadExtendedInstance($model);
			$specificSchema = $this->$model->getSchema(false);
			$datasToInsert = array();
			foreach ($specificSchema as $col) {
				if (array_key_exists($col, $datas))
					$datasToInsert[$col] = $datas[$col];
			}
			$this->db->insert($table, $datasToInsert);
		}
		if (is_module_installed('traductions')) {
			$this->load->helper('locale');
			$locale = locale();
			$columsToTranslate = $this->columnsToTranslate();
			if (!empty($columsToTranslate)) {
				$datasTranslate = array('lang' => $locale, $key => $datas[$key]);
				foreach ($datas as $field => $value) {
					if (in_array($field, $columsToTranslate) && isset($datas[$field])) {
						$datasTranslate[$field . '_lang'] = $datas[$field];
					}
				}
				$this->db->insert($this->getTableName() . '_translations', $datasTranslate);
			}
		}
		$this->_lastSavedDatas = $datas;
		$this->afterInsert($insertedId, $datas);
		return $insertedId;
	}

	public function update($datas = null, $where = null) {
		if ($datas == null) {
			$datas = $this->toArray();
			$this->clear();
		}
		$this->beforeUpdate($datas, $where);
		$this->convertArrayColumnsToJson($datas);
		$primaries = $this->getPrimaryColumns();
		if ($where === null) {
			$where = array();
			foreach ($primaries as $col) {
				$where[$this->db->dbprefix($this->getTableName()) . '.' . $col] = $datas[$col];
			}
		}
		$key = $primaries[0];
		if (is_module_installed('traductions')) {
			$this->load->helper('locale');
			$locale = locale();
			$columsToTranslate = $this->columnsToTranslate();
			if (!empty($columsToTranslate)) {
				$datasTranslate = array('lang' => $locale, $key => $datas[$key]);
				foreach ($datas as $field => $value) {
					if (in_array($field, $columsToTranslate) && isset($datas[$field])) {
						$datasTranslate[$field . '_lang'] = $datas[$field];
					}
				}
				$translateTable = $this->getTableName() . '_translations';
				$transWhere = array();
				foreach ($primaries as $col) {
					$transWhere[$this->db->dbprefix($translateTable) . '.' . $col] = $datas[$col];
				}
				$transWhere[$this->db->dbprefix($translateTable) . '.lang'] = $locale;
				$this->db->where($transWhere);
				$result = $this->db->get($translateTable);
				if ($result->num_rows()) {
					$this->db->update($translateTable, $datasTranslate, $transWhere);
				} else {
					$this->db->insert($translateTable, $datasTranslate);
				}
			}
		}
		$fullDatas = $datas;
		foreach ($primaries as $col) {
			unset($datas[$col]);
		}
		if ($this->isExtendingModel() && count($primaries) == 1) {
			$baseModel = $this->getBaseModelName();
			$baseTable = $this->getBaseTableName();
			$this->loadExtendedInstance($baseModel);
			$cols = $this->$baseModel->getSchema();
			$datasToUpdate = array();
			foreach ($cols as $col) {
				if (isset($datas[$col])) {
					if (array_key_exists($col, $datas))
						$datasToUpdate[$col] = $datas[$col];
				}
			}

			$idsToUpdateRow = $this->get($where, 'object', array($this->db->dbprefix($this->getTableName()) . '.' . $key));
			$idsToUpdate = array();
			foreach ($idsToUpdateRow as $row) {
				$idsToUpdate[] = $row->id;
			}
			if (!$idsToUpdate)
				return false;
			if ($datasToUpdate) {
				$this->db->where_in($key, $idsToUpdate);
				$ret = $this->db->update($baseTable, $datasToUpdate);
			}
			$extendingTables = $this->getExtendedTables();
			$nbExtendingTables = count($extendingTables);
			$extendingClasses = $this->getExtendedClasses();
			for ($i = 1; $i < $nbExtendingTables; $i++) {
				$table = $extendingTables[$i];
				$model = strtolower($extendingClasses[$i]);
				$this->loadExtendedInstance($model);
				$cols = $this->$model->getSchema(false);
				$datasToUpdate = array();
				foreach ($cols as $col) {
					if (isset($datas[$col])) {
						if (array_key_exists($col, $datas))
							$datasToUpdate[$col] = $datas[$col];
					}
				}
				if ($datasToUpdate) {
					$this->db->where_in($key, $idsToUpdate);
					$ret = $this->db->update($table, $datasToUpdate);
				}
			}
			$this->_lastSavedDatas = $fullDatas;
			$this->afterUpdate($fullDatas, $where);
			return $ret;
		} else {
			$ret = $this->db->update($this->getTableName(), $datas, $where);
			$this->_lastSavedDatas = $fullDatas;
			$this->afterUpdate($fullDatas, $where);
			return $ret;
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
		if ($datas == null) {
			$datas = $this->toArray();
		}
		$where = array();
		foreach ($this->getPrimaryColumns() as $col) {
			if (isset($datas[$this->getTableName() . '.' . $col])) {
				$where[$this->db->dbprefix($this->getTableName()) . '.' . $col] = $datas[$this->getTableName() . '.' . $col];
			} else if (isset($datas[$col])) {
				$where[$this->db->dbprefix($this->getTableName()) . '.' . $col] = $datas[$col];
			}
		}
		return $where;
	}

	private function updateDatas($datas) {
		$where = array();
		foreach ($this->getPrimaryColumns() as $col) {
			if (isset($datas[$col])) {
				$where[$this->db->dbprefix($this->getTableName()) . '.' . $col] = $datas[$col];
			} else if (isset($datas[$this->getTableName() . '.' . $col])) {
				$where[$this->db->dbprefix($this->getTableName()) . '.' . $col] = $datas[$this->getTableName() . '.' . $col];
			} else {
				return false;
			}
		}
		if ($this->get($where)) {
			return true;
		}
		return false;
	}

	public function getList($limit = null, $offset = null, $type = 'object', $columns = null) {
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		return $this->get(null, $type, $columns);
	}

	public function getListOrderBy($limit = null, $offset = null, $type = 'object', $columns = null, $order = null) {
		if (!$order) {
			$order = $this->getData('order');
		}
		$this->db->order_by($order);

		return $this->getList($limit, $offset, $type, $columns);
	}

	public function count($where = null) {
		$this->makeExtendedJoins();
		if ($where !== null) {
			$this->db->where($where);
			$this->db->from($this->getTableName());
			return $this->db->count_all_results();
		}

		return $this->db->count_all($this->getTableName());
	}

	public function insertGroup($group) {
		if (count($group)) {
			$concernedClasses = $this->getExtendedClasses();
			$models = array($this->getBaseModelName());
			for ($i = 1; $i < count($concernedClasses); $i++) {
				$models[] = strtolower($concernedClasses[$i]);
			}
			$lastInsertedId = null;
			foreach ($models as $model) {
				$this->loadExtendedInstance($model);
				$schema = $this->$model->getSchema(false);
				$subGroup = array();
				foreach ($group as $datas) {
					$associatedDatas = array();
					foreach ($schema as $col) {
						if (array_key_exists($col, $datas))
							$associatedDatas[$col] = $datas[$col];
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
			if ($lastInsertedId !== null) {
				$keys[] = $this->getPrimaryColumns()[0];
			}

			$this->loadExtendedInstance($model);
			$values = array();
			foreach ($subGroup as $datas) {
				foreach ($datas as $key => $data) {
					$datas[$key] = $this->db->escape($data);
				}
				if ($lastInsertedId !== null) {
					$datas[$this->getPrimaryColumns()[0]] = $lastInsertedId++;
				}
				$values[] = implode(',', $datas);
			}
			$sql = 'INSERT INTO {PRE}' . $this->$model->getTableName() . '(`' . implode('`,`', $keys) . '`) VALUES (' . implode('),(', $values) . ');';
			return $this->db->query($sql);
		}
	}

	//A grouped update using mysql 'on duplicate key' key words...

	public function updateGroup($group) {
		if (count($group)) {
			$concernedClasses = $this->getExtendedClasses();
			$models = array($this->getBaseModelName());
			for ($i = 1; $i < count($concernedClasses); $i++) {
				$models[] = strtolower($concernedClasses[$i]);
			}
			foreach ($models as $model) {
				$this->loadExtendedInstance($model);
				$schema = $this->$model->getSchema(false);
				$subGroup = array();
				foreach ($group as $datas) {
					$associatedDatas = array();
					foreach ($schema as $col) {
						if (array_key_exists($col, $datas))
							$associatedDatas[$col] = $datas[$col];
					}
					$subGroup[] = $associatedDatas;
				}
				$this->updateSubGroup($model, $subGroup);
			}
		}
	}

	private function updateSubGroup($model, $group) {
		if (count($group)) {
			$keys = @array_keys(array_shift(array_values($group)));
			$values = array();
			foreach ($group as $datas) {
				foreach ($datas as $key => $data) {
					$datas[$key] = $this->db->escape($data);
				}
				$values[] = implode(',', $datas);
			}
			$this->loadExtendedInstance($model);
			$dataColumns = array_diff($keys, $this->$model->getPrimaryColumns());
			if (!$dataColumns) {
				$sql = 'INSERT IGNORE INTO {PRE}' . $this->$model->getTableName() . '(`' . implode('`,`', $keys) . '`) VALUES (' . implode('),(', $values) . ')';
			} else {
				$on_duplicate_col = array();
				foreach ($dataColumns as $dataColumn) {
					$on_duplicate_col[] = '`' . $dataColumn . '`=VALUES(`' . $dataColumn . '`)';
				}
				$sql = 'INSERT INTO {PRE}' . $this->$model->getTableName() . '(`' . implode('`,`', $keys) . '`) VALUES (' . implode('),(', $values) . ')'
						. ' ON DUPLICATE KEY UPDATE ' . implode(',', $on_duplicate_col) . ';';
			}

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
		$this->makeExtendedJoins();
		$hasId = $id !== null;
		if (!$hasId) {
			$id = $this->_datas['id'];
		} else {
			$this->loadRow(array('id' => $id));
		}
		$query = 'SELECT COUNT(*) as count
        FROM {PRE}' . $this->getTableName() . '
		WHERE {PRE}' . $this->getTableName() . '.' . $order . ' >= ' . $this->db->escape($this->{$order}) . '';
		$res = $this->db->query($query)->result();
		
		return $res[0]->count;
	}

	protected function createAliasFrom($str, $update = FALSE) {
		$str = str_replace('+', 'plus', $str);
		$str = str_replace('#', 'sharp', $str);
		$str = str_replace(array('\'', '"'), array('-', '-'), $str);
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

	public function unlink($columns, $datas = null) {

		if ($datas === null) {
			$datas = $this->toArray();
		}
		if ($columns && is_string($columns)) {
			$columns = array($columns);
		}
		$doUnlink = $columns;
		foreach ($columns as $column) {
			$doUnlink |= isset($datas[$column]);
		}
		if ($doUnlink) {
			$load = $this->getRow($this->buildPrimaryWhere($datas));
			foreach ($columns as $column) {
				if (isset($datas[$column]) && $datas[$column] != $load->{$column}) {
					unlink($load->{$column});
				}
			}
		}
	}
	
	public function getLastSavedDatas() {
		return $this->_lastSavedDatas;
	}

	public function getThrough($table, $model, $value, $key = 'id') {
		$db = $this->db;
		$linkTable = $db->dbprefix($table);
		if (is_array($value)) {
			if (!$value)
				return array();
			$value = array_map(function($e) use ($db) {
				return $db->escape($e);
			}, $value);
		}
		return $this->get($key . ' IN ('
						. 'SELECT ' . strtolower(get_class($this)) . '_' . $key . ' '
						. 'FROM ' . $linkTable . ' '
						. 'WHERE ' . $model . '_' . $key . ' '
						. (is_array($value) ? 'IN (' . implode(',', $value) . ')' : '= ' . $this->db->escape($value)) . ')');
	}

	public function columnsToTranslate() {
		if($this->_columnsToTranslate === null){
			$this->_columnsToTranslate = array();
			$tableTrads = $this->db->dbprefix($this->getTableName().'_translations');
			if($this->db->table_exists($tableTrads)){
				$fieldsTrad = $fields = $this->db->list_fields($tableTrads);
				$schema = $this->getSchema();
				foreach ($schema as $field) {
					if(in_array($field.'_lang', $fieldsTrad)) {
						$this->_columnsToTranslate[] = $field;
					}
				}
			}
		}
		
		return $this->_columnsToTranslate;
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
