<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of articleCategory
 *
 * @author thibault
 */
class articlesComment extends DATA_Model {

	public function getTableName() {
		return 'articles_comments';
	}

	public function insert($array = null) {
		if ($array === null) {
			$array = $this->toArray();
		}
		$array['date'] = date('Y-m-d H:i:s');
		$array['locale'] = locale();
		return parent::insert($array);
	}
	
	public function getArticleCommentsWithAuthor($article_id = null){
		if($article_id === null){
			$article_id = $this->article_id;
		}
		$this->db->join('users', 'users.id='.$this->getTableName().'.user_id');
		return $this->get(array('article_id'=>$article_id));
	}
}

?>
