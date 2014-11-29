<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tutorials
 *
 * @author thibault
 */
class tutorial extends DATA_Model {

	public function getTableName() {
		return 'tutorials';
	}

	public function insert($array) {
		if ($array === null) {
			$array = $this->toArray();
		}
		if (!isset($array['alias'])) {
			$array['alias'] = $this->createAliasFrom($array['title']);
		}
		if(!isset($array['locale'])) {
			$array['locale'] = locale();
		}
		$this->load->library('BBCodeParser', '', 'parser');
		$this->parser->parse($array['content']);
		$anchors = array();
		$rootNode = $this->parser->getTreeRoot();
		$i = 0;
		foreach ($rootNode->getChildren() as $child) {
			if ($child instanceof \JBBCode\ElementNode && $child->getTagName() == 'section1') {
				$anchors['tuto-section-' . $i++] = $child->getAsText();
			}
		}

		$array['anchors'] = json_encode($anchors);
		$currentDate = date('Y-m-d H:i:s');
		$array['creation'] = $currentDate;
		$array['hits'] = 0;
		if (!isset($array['online'])) {
			$array['online'] = false;
		}
		return parent::insert($array);
	}

	public function update($array = null, $where = null) {
		$currentDate = date('Y-m-d H:i:s');
		if ($array === null) {
			$array = $this->toArray();
		}
		$array['modification'] = $currentDate;
		$this->load->library('BBCodeParser', '', 'parser');
		if(isset($array['content']))$this->parser->parse($array['content']);
		$anchors = array();
		$rootNode = $this->parser->getTreeRoot();
		$i = 0;
		foreach ($rootNode->getChildren() as $child) {
			if ($child instanceof \JBBCode\ElementNode && $child->getTagName() == 'section1') {
				$anchors['tuto-section-' . $i++] = $child->getAsText();
			}
		}
		$array['anchors'] = json_encode($anchors);
		if(isset($array['title'])){
			$array['alias'] = $this->createAliasFrom($array['title'], true);
		}
		$this->unlink(array('bigThumb', 'smallThumb'), $array);
		return parent::update($array, $where);
	}

	public function deleteTuto($id_tuto) {
		$this->loadRow(array('id' => $id_tuto));
		unlink($this->bigThumb);
		unlink($this->smallTumb);
		return $this->delete();
	}

	public function getTuto($where) {
		$query = $this->db->get_where($this->getTableName(), $where);
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		}
		return false;
	}

	public function getTutoWithPages($where) {
		$query = $this->db->get_where($this->getTableName(), $where);
		if ($query->num_rows() == 1) {
			$res = $query->result();
			$tuto = $res[0];
			$queryPages = $this->db->select('*')
							->where($where)
							->order_by('order')->from('tuto_pages')->get();

			$pages = $queryPages->result();
			$tuto->pages = $pages;
			return $tuto;
		}
		return false;
	}

	public function getTutoWithAuthorCategoryAndPages($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login, tuto_categories.name as category_name')
				->from($this->getTableName())
				->where($where)
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts', 'left')
				->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			$tuto = $res[0];
			$queryPages = $this->db->select('*')
							->where(array('id_tuto' => $tuto->id))
							->order_by('order')->from('tuto_pages')->get();

			$pages = $queryPages->result();
			$tuto->pages = $pages;
			return $tuto;
		}
		return false;
	}

	public function loadPages() {
		if ($this->pages === null && isset($this->id)) {
			$id = $this->id;
			$queryPages = $this->db->select('*')
							->where(array('id_tuto' => $id))
							->order_by('order')->from('tuto_pages')->get();

			$pages = $queryPages->result();
			$this->pages = $pages;
		}
	}

	public function getPages($id_tuto = null, $online = false) {
		if($id_tuto === null){
			$id_tuto = $this->id_tuto;
		}
		
		$this->db->select('*')
				->where(array('id_tuto' => $id_tuto))
				->order_by('order')->from('tuto_pages');
		
		if($online){
			$this->db->where('online', true);
		}
				
		$queryPages = $this->db->get();

		$pages = $queryPages->result();
		
		$this->pages= $pages;
		
		return $this->pages;
	}
	
	public function getOnlinePages($id_tuto = null){
		return $this->getPages($id_tuto, true);
	}

	public function countPages($id_tuto = null) {
		if ($id_tuto == null) {
			$id_tuto = $this->id;
		}
		$this->db->from('tuto_pages')
				->where(array('id_tuto' => $id_tuto));
		return $this->db->count_all_results();
	}

	public function getTutoWithAuthorAndCategory($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login, tuto_categories.name as category_name')
				->from($this->getTableName())
				->where($where)
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts', 'left')
				->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			$tuto = $res[0];
			$this->convertToHtml($tuto, 'content');
			return $tuto;
		}
		return false;
	}

	public function getTutoWithAuthor($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login')
						->from($this->getTableName())
						->where($where)
						->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			$tuto = $res[0];
			return $tuto;
		}
		return false;
	}
	
	public function getListByDifficulty($difficulty){
		if(!is_array($difficulty)) $difficulty = array($difficulty);
		$where = '`difficulty`='.implode(' OR `difficulty`=', $difficulty);
		return $this->get($where);
	}
	
	private function listingWithAuthors($where = null, $limit = null, $offset = null){
		$this->db->select($this->getTableName() . '.*, users.login')->from($this->getTableName())
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left');

		if($where !==null){
			$this->db->where($where);
		}
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}

		$query = $this->db->get();

		$tutos = $query->result();

		return $tutos;
	}

	public function getListWithAuthors($limit = null, $offset = null) {
		$this->listingWithAuthors(null, $limit, $offset);
	}
	
	public function getLocaleListWithAuthors($limit = null, $offset = null) {
		$this->listingWithAuthors(array('locale'=>locale()), $limit, $offset);
	}
	
	private function listingWithAuthorsAndCategories($where = null, $limit = null, $offset = null){
		$this->db->select($this->getTableName() . '.*, users.login, tuto_categories.name as category_name')
				->from($this->getTableName())
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts', 'left');
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		if($where !== null){
			$this->db->where($where);
		}
		$query = $this->db->get();
		$tutos = $query->result();

		return $tutos;
	}

	public function getListWithAuthorsAndCategories($limit = null, $offset = null) {
		return $this->listingWithAuthorsAndCategories(NULL, $limit, $offset);
	}
	
	public function getLocaleListWithAuthorsAndCategories($limit = null, $offset = null) {
		return $this->listingWithAuthorsAndCategories(array('locale'=>locale()), $limit, $offset);
	}
	
	private function listingWithAuthorsAndCategorieAlias($where = null, $limit = null, $offset = null){
		$this->db->select($this->getTableName() . '.*, users.login, tuto_categories.name as category_name,tuto_categories.alias as category_alias')
				->from($this->getTableName())
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts', 'left');
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		if($where !== null){
			$this->db->where($where);
		}
		$query = $this->db->get();
		
		$tutos = $query->result();
		return $tutos;
	}
	
	public function getListWithAuthorsAndCategorieAlias($limit = null, $offset = null) {
		return $this->listingWithAuthorsAndCategorieAlias(NULL, $limit, $offset);
	}
	
	public function getLocaleListWithAuthorsAndCategorieAlias($limit = null, $offset = null) {
		return $this->listingWithAuthorsAndCategorieAlias(array('locale'=>locale()), $limit, $offset);
	}
	
	private function listingByCategoryWithAuthors($where = null, $limit = null, $offset = null, $category_id = null){
		if ($category_id === null) {
			$category_id = $this->categorie_id_tuts;
		}

		$this->db->select($this->getTableName() . '.*, users.login')->from($this->getTableName())
				->where(array('categorie_id_tuts' => $category_id))
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left');
		if($where !== null){
			$this->db->where($where);
		}
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}

		$query = $this->db->get();

		$tutorials = $query->result();

		return $tutorials;
	}

	public function getListByCategoryWithAuthors($limit = null, $offset = null, $category_id = null) {
		return $this->listingByCategoryWithAuthors(NULL, $limit, $offset,  $category_id);
	}
	
	public function getLocaleListByCategoryWithAuthors($limit = null, $offset = null, $category_id = null) {
		return $this->listingByCategoryWithAuthors(array('locale'=>locale()), $limit, $offset,  $category_id);
	}

	public function search($limit = null, $offset = null, $search = null, $columns = null) {
		$this->prepareSearch($limit, $offset, $search, $columns);
		$this->db->where('online',1);
		$this->db->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts')
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left');
		$this->db->select($this->getTableName() . '.*, tuto_categories.alias as category_alias, users.login as login');
		return $this->db->get($this->getTableName())->result();
	}
}
