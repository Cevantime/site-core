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
class video extends DATA_Model {

	const TABLE_NAME = 'videos';
	
	public function getTableName() {
		return self::TABLE_NAME;
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
		$currentDate = date('Y-m-d H:i:s');
		$array['creation'] = $currentDate;
		$array['hits'] = 0;
		if(isset($array['bigThumb']))$array['smallThumb'] = $array['bigThumb'];
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
		if(isset($array['bigThumb']))$array['smallThumb'] = $array['bigThumb'];
		if(isset($array['title'])){
			$array['alias'] = $this->createAliasFrom($array['title'], true);
		}
		$this->unlink(array('src','bigThumb', 'smallThumb'), $array);
		return parent::update($array, $where);
	}
	
	public function getCatLike($limit = null, $offset = null, $cat = null){
		return $this->listingCatLike(null, $limit, $offset, $cat);
	}
	
	public function getLocaleCatLike($limit = null, $offset = null, $cat = null) {
		return $this->listingCatLike(array('locale'=>locale()), $limit, $offset, $cat);
	}
	
	public function listingCatLike($where = null, $limit = null, $offset = null, $cat = null) {
		if($cat === null) {
			$cat = $this->categorie_id_tuts;
		}
		$this->db->select(self::TABLE_NAME.'.*');
		$this->db->join('tuto_categories', 'tuto_categories.id='.$this->getTableName().'.categorie_id_tuts');
		$this->db->where('LOWER(tuto_categories.name) LIKE '
				. '\'%'.$this->db->escape_like_str(strtolower($cat)).'%\'');
		$this->db->order_by('RAND()');
		if($where){
			$this->db->where($where);
		}
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		return $this->get();
	}

	public function deleteVideo($id_video) {
		$this->loadRow(array('id' => $id_video));
		unlink($this->bigThumb);
		unlink($this->smallTumb);
		unlink($this->src);
		return $this->delete();
	}

	public function getVideo($where) {
		$query = $this->db->get_where($this->getTableName(), $where);
		if ($query->num_rows() == 1) {
			$res = $query->result();
			$video = $res[0];
			if($video->spots){
				$video->spots = json_decode($video->spots);
			}
			return $video;
		}
		return false;
	}

	public function getVideoWithChapter($where) {
		$query = $this->db->get_where($this->getTableName(), $where);
		if ($query->num_rows() === 1) {
			$res = $query->result();
			$video = $res[0];
			if($video->spots){
				$video->spots = json_decode($video->spots);
			}
			$queryPages = $this->db->select('*')
							->where($where)
							->order_by('order')->from('video_chapters')->get();

			$pages = $queryPages->result();
			$video->chapters = $pages;
			return $video;
		}
		return false;
	}

	public function getVideoWithAuthorCategoryAndChapters($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login, tuto_categories.name as category_name')
				->from($this->getTableName())
				->where($where)
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts', 'left')
				->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			$video = $res[0];
			if($video->spots){
				$video->spots = json_decode($video->spots);
			}
			$queryPages = $this->db->select('*')
							->where(array('id_video' => $video->id))
							->order_by('order')->from('video_chapters')->get();
			
			$chapters = $queryPages->result();
			$this->load->model('tokencontent');
			foreach ($chapters as $chapter) {
				$chapter->token = 
					$this->tokencontent->createToken(
							$chapter->id, 
							tokencontent::$CONTENT_TYPE_VIDEO_CHAPTER);
			}
			$video->chapters = $chapters;
			return $video;
		}
		return false;
	}

	public function loadChapters() {
		if ($this->chapters === null && isset($this->id)) {
			$id = $this->id;
			$queryPages = $this->db->select('*')
							->where(array('id_video' => $id))
							->order_by('order')->from('video_chapters')->get();

			$chapters = $queryPages->result();
			$this->chapters = $chapters;
		}
	}

	public function getChapters($id_video = null, $online = false) {
		if($id_video === null){
			$id_video= $this->id_video;
		}
		
		$this->db->select('*')
				->where(array('id_video' => $id_video))
				->order_by('order')->from('video_chapters');
		
		if($online){
			$this->db->where('online', true);
		}
				
		$queryPages = $this->db->get();

		$chapters = $queryPages->result();
		
		$this->chapters= $chapters;
		
		return $this->chapters;
	}
	
	public function getOnlineChapters($id_video = null){
		return $this->getChapters($id_video, true);
	}

	public function countChapters($id_video = null) {
		if ($id_video == null) {
			$id_video = $this->id;
		}
		$this->db->from('video_chapters')
				->where(array('id_video' => $id_video));
		return $this->db->count_all_results();
	}

	public function getVideoWithAuthorAndCategory($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login, tuto_categories.name as category_name')
				->from($this->getTableName())
				->where($where)
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts', 'left')
				->get();
		if ($query->num_rows() > 0) {
			$res = $query->result();
			$video = $res[0];
			if($video->spots){
				$video->spots = json_decode($video->spots);
			}
			$this->convertToHtml($video, 'description');
			return $video;
		}
		return false;
	}
	
	public function getVideosWithAuthorAndCategory($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login, tuto_categories.name as category_name')
				->from($this->getTableName())
				->where($where)
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts', 'left')
				->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			$video = $res[0];
			if($video->spots){
				$video->spots = json_decode($video->spots);
			}
			$this->convertToHtml($video, 'description');
			return $video;
		}
		return false;
	}

	public function getVideoWithAuthor($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login')
						->from($this->getTableName())
						->where($where)
						->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			$video = $res[0];
			if($video->spots){
				$video->spots = json_decode($video->spots);
			}
			return $video;
		}
		return false;
	}
	
	public function getListByDifficulty($limit = null, $offset = null, $difficulty = null){
		return $this->listingByDifficulty($difficulty, null, $limit, $offset);
	}
	
	public function  getLocaleListByDifficulty($limit = null, $offset = null, $difficulty = null) {
		return $this->listingByDifficulty($difficulty, array('locale'=>locale()), $limit, $offset);
	}
	
	private function listingByDifficulty($difficulty, $where =null, $limit = null, $offset = null){
		if($difficulty === null){
			$difficulty = $this->difficulty;
		}
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		if ($where) {
			$this->db->where($where);
		}
		if(!is_array($difficulty)) $difficulty = array($difficulty);
		$where = '`difficulty`='.implode(' OR `difficulty`=', '\''.$difficulty.'\'');
		$this->db->where($where);
		return $this->get();
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

		$videos = $query->result();

		return $videos;
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
		$videos = $query->result();

		return $videos;
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
		
		$videos = $query->result();
		return $videos;
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

		$videos = $query->result();

		return $videos;
	}

	public function getListByCategoryWithAuthors($limit = null, $offset = null, $category_id = null) {
		return $this->listingByCategoryWithAuthors(NULL, $limit, $offset,  $category_id);
	}
	
	public function getLocaleListByCategoryWithAuthors($limit = null, $offset = null, $category_id = null) {
		return $this->listingByCategoryWithAuthors(array('locale'=>locale()), $limit, $offset,  $category_id);
	}

	public function search($limit = null, $offset = null, $search = null, $columns = null) {
		$this->prepareSearch($limit, $offset, $search, $columns);
		$this->db->join('tuto_categories', 'tuto_categories.id=' . $this->getTableName() . '.categorie_id_tuts')
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left');
		$this->db->select($this->getTableName() . '.*, tuto_categories.alias as category_alias, users.login as login');
		return $this->db->get($this->getTableName())->result();
	}
	
	public function getListOrderedByHits($limit = null, $offset = null, $type = 'object') {
		return parent::getListOrderBy('hits', $limit, $offset, $type);
	}
}
