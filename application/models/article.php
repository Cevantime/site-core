<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of articles
 *
 * @author thibault
 */
class article extends DATA_Model {

	public function getTableName() {
		return 'articles';
	}

	public function insert($array) {
		if ($array === null) {
			$array = $this->toArray();
		}
		if (!isset($array['alias'])) {
			$array['alias'] = $this->createAliasFrom($array['title']);
		}
		if(!isset($array['locale'])){
			$array['locale'] = locale();
		}
		$currentDate = date('Y-m-d H:i:s');
		$array['creation'] = $currentDate;
		$array['hits'] = 0;
		if (!isset($array['online'])) {
			$array['online'] = true;
		}

		return parent::insert($array);
	}

	public function update($array = null, $where = null) {
		$array = ($array !== null) ? $array : $this->_datas;
		if(isset($array['title'])){
			$array['alias'] = $this->createAliasFrom($array['title'], true);
		}
		$currentDate = date('Y-m-d H:i:s');
		$array['modification'] = $currentDate;
		$this->unlink(array('bigThumb', 'smallThumb'), $array);
		return parent::update($array, $where);
	}

	public function deleteArticle($id_article) {
		$this->loadRow(array('id' => $id_article));
		$bigThumb = json_decode($this->bigThumb);
		$smallThumb = json_decode($this->smallTumb);
		unlink($bigThumb['full_path']);
		unlink($smallThumb['full_path']);
		return $this->delete();
	}

	public function getArticle($where) {
		$query = $this->db->get_where($this->getTableName(), $where);
		if ($query->num_rows()) {
			$res = $query->result();
			return $res[0];
		}
		return false;
	}

	public function getArticleWithAuthor($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login')->from($this->getTableName())->where($where)
						->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')->get();
		if ($query->num_rows()) {
			$res = $query->result();
			return $res[0];
		}
		return false;
	}

	public function getArticleWithAuthorAndCategory($where) {
		$query = $this->db->select($this->getTableName() . '.*, users.login, articles_categories.name as category_name')
				->from($this->getTableName())
				->where($where)
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('articles_categories', 'articles_categories.id=' . $this->getTableName() . '.categories_id', 'left')
				->get();
		if ($query->num_rows()) {
			$res = $query->result();
			$tuto = $res[0];
			$this->convertToHtml($tuto, 'content');
			return $tuto;
		}
		return false;
	}

	public function getMixedListOrderedByCreationDate($limit = null, $offset = null) {
		$query = $this->getMixedQuery('creation');
		if ($limit !== null) {
			$query .= ' LIMIT ' . $limit . ',' . $offset;
		}
		$res = $this->db->query($query)->result();
		//disabled because too slow for multi dataset
		//$this->convertToHtml($res, array('content', 'page_content'));
		return $res;
	}
	
	public function getMixedListOrderedByWeight($limit = null, $offset = null) {
		$query = $this->getMixedQuery('weight');
		if ($limit !== null) {
			$query .= ' LIMIT ' . $limit . ',' . $offset;
		}
		$res = $this->db->query($query)->result();
		//disabled because too slow for multi dataset
		//$this->convertToHtml($res, array('content', 'page_content'));
		return $res;
	}

	public function getMixedListOrderedByHits($limit = null, $offset = null) {
		$query = $this->getMixedQuery('hits');
		if ($limit !== null) {
			$query .= ' LIMIT ' . $limit . ',' . $offset;
		}
		$res = $this->db->query($query)->result();
		//disabled because too slow for multi dataset
		//$this->convertToHtml($res, array('content', 'page_content'));
		return $res;
	}

	public function getMixedQuery($order) {
		return '(
			SELECT articles.id as id, articles.alias as alias, title, creation, content, modification, hits, smallThumb, bigThumb, online, login, articles_categories.name as name, articles_categories.icon as icon, \'article\' as type, \'content\' as page_content, weight as weight
			FROM `articles`
			LEFT JOIN `articles_categories` ON `articles_categories`.`id` = `articles`.`categories_id`
			LEFT JOIN `users` ON `users`.`id` = `articles`.`user_id`
			WHERE online = 1 AND articles.locale =\''.locale().'\'
			GROUP BY id
			)
			UNION ( 
			SELECT tutorials.id as id, tutorials.alias as alias, tutorials.title, tutorials.creation, tutorials.content, tutorials.modification, tutorials.hits, tutorials.smallThumb, tutorials.bigThumb, tutorials.online as online, login, tuto_categories.name as name, tuto_categories.icon as icon, \'tutorial\' as type, tuto_pages.content as page_content,weight as weight
			FROM `tutorials`
			LEFT JOIN `tuto_categories` ON `tuto_categories`.`id` = `tutorials`.`categorie_id_tuts`
			LEFT JOIN `users` ON `users`.`id` = `tutorials`.`user_id`
			LEFT JOIN `tuto_pages` ON `tuto_pages`.`id_tuto` = `tutorials`.`id`
			WHERE tutorials.online = 1 AND tutorials.locale =\''.locale().'\'
			GROUP BY tutorials.id
			) UNION (
			SELECT videos.id as id, videos.alias as alias, videos.title, videos.creation, videos.description as content, videos.modification, videos.hits, videos.smallThumb, videos.bigThumb, videos.online as online, login, tuto_categories.name as name, tuto_categories.icon as icon, \'video\' as type, video_chapters.src as page_content,weight as weight
			FROM `videos`
			LEFT JOIN `tuto_categories` ON `tuto_categories`.`id` = `videos`.`categorie_id_tuts`
			LEFT JOIN `users` ON `users`.`id` = `videos`.`user_id`
			LEFT JOIN `video_chapters` ON `video_chapters`.`id_video` = `videos`.`id`
			WHERE videos.online = 1 AND videos.locale =\''.locale().'\'
			GROUP BY videos.id
			)
			ORDER BY ' . $order . ' DESC';
	}

	public function getListWithAuthors($limit = null, $offset = null) {
		return $this->listingWithAuthors(NULL, $limit, $offset);
	}
	
	public function getLocaleListWithAuthors($limit = null, $offset = null){
		return $this->listingWithAuthors(array('locale'=>locale()), $limit, $offset);
	}
	
	private function listingWithAuthors($where = null, $limit= null, $offset = null){
		$this->db->select($this->getTableName() . '.*, users.login')->from($this->getTableName())
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left');
		if($where !== null){
			$this->db->where($where);
		}
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}

		$query = $this->db->get();

		$articles = $query->result();

		return $articles;
	}
	
	private function listingWithAuthorAndCatAlias($where = null, $limit=null, $offset = null){
		$this->db->select($this->getTableName() . '.*, users.login,articles_categories.alias as catalias')->from($this->getTableName())
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')
				->join('articles_categories', 'articles_categories.id=' . $this->getTableName() . '.categories_id', 'left');
		if($where !== null){
			$this->db->where($where);
		}
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}

		$query = $this->db->get();

		$articles = $query->result();

		return $articles;
	}
	
	public function getListWithAuthorsAndCatAlias($limit = null, $offset = null) {
		return $this->listingWithAuthorAndCatAlias(null,$limit, $offset);
	}
	
	public function getLocaleListWithAuthorsAndCatAlias($limit = null, $offset = null){
		return $this->listingWithAuthorAndCatAlias(array('locale'=>locale()), $limit, $offset);
	}
	
	private function listingByCategoryWithAuthors($where = null, $limit = null, $offset = null, $category_id = null){
		if ($category_id === null) {
			$category_id = $this->categories_id;
		}

		$this->db->select($this->getTableName() . '.*, users.login')->from($this->getTableName())
				->where(array('categories_id' => $category_id))
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left');
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		if($where !== null){
			$this->db->where($where);
		}
		$query = $this->db->get();

		$articles = $query->result();
		return $articles;
	}

	public function getListByCategoryWithAuthors($limit = null, $offset = null, $category_id = null) {
		return $this->listingByCategoryWithAuthors(null, $limit,$offset,$category_id);
	}
	
	public function getLocaleListByCategoryWithAuthors($limit= null, $offset = null, $category_id = null){
		return $this->listingByCategoryWithAuthors(array('locale'=>  locale()), $limit, $offset, $category_id);
	}

	public function search($limit = null, $offset = null, $search = null, $columns = null) {
		$this->prepareSearch($limit, $offset, $search, $columns);
		$this->db->join('articles_categories', 'articles_categories.id=' . $this->getTableName() . '.categories_id')
				->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left');
		;
		$this->db->select($this->getTableName() . '.*, articles_categories.alias as category_alias, users.login as login');
		return $this->db->get($this->getTableName())->result();
	}

}

?>
