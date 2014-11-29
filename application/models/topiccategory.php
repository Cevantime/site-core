<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tutoCategory
 *
 * @author thibault
 */
class topicCategory extends DATA_Model {

	public function getTableName() {
		return 'topic_categories';
	}

	public function insert($datas = null) {
		if ($datas === null) {
			$datas = $this->toArray();
		}
		if (!isset($datas['alias'])) {
			$datas['alias'] = $this->createAliasFrom($datas['name']);
		}
		parent::insert($datas);
	}
	
	public function update($array = null, $where = null) {
		$array = ($array !== null) ? $array : $this->_datas;
		if(isset($array['name'])){
			$array['alias'] = $this->createAliasFrom($array['name'], true);
		}
		$this->unlink('icon', $array);
		parent::update($array, $where);
	}

	public function getAllAsIdNameAssociativeArray() {
		$all = $this->get(null, 'array');
		if (!$all)
			return array();
		$assoc = array();
		foreach ($all as $category) {
			$assoc[$category['id']] = $category['name'];
		}
		return $assoc;
	}

	public function getRelatedTopics($limit = null, $offset = null, $id = null) {
		if ($id === null) {
			$id = $this->id;
		}
		$this->load->model('topic');
		$topics = $this->topic->getTopicsByCategoryWithAuthors($id, $limit, $offset);
		return $topics;
	}

	public function getListWithRelatedTopicsAndLastRelatedMessageWithAuthorAndCounts($limit = null, $offset = null, $id = null) {

		$query_str = '
			SELECT *, SUM(nb_messages_by_topic) as nb_posts, COUNT(*) as nb_topics 
			FROM (
				SELECT *, COUNT(*) as nb_messages_by_topic FROM(
					SELECT `topic_categories`.`id` as id, `topic_categories`.`alias` as alias, `name`, `icon`, `title`, `topic_messages`.`last_modif` as last_modif, `topics`.`id` as topic_id, `content`, `login`, `intro`, `topic_messages`.`date_add` as `date_add`
					FROM (`topic_categories`)
					LEFT JOIN `topics` ON `topics`.`category`=`topic_categories`.`id`
					LEFT JOIN `topic_messages` ON `topic_messages`.`id_topic`=`topics`.`id`
					LEFT JOIN `users` ON `users`.`id`=`topic_messages`.`id_user`
					ORDER BY `topic_messages`.`date_add` DESC)
				as derived1 GROUP BY topic_id ORDER BY `date_add` DESC) 
			as derived2 GROUP BY `id` ORDER BY `date_add` DESC';

		if ($limit !== null) {
			$query_str .= ' LIMIT ' . $limit . (($offset != null) ? ',' . $offset : '');
		}

		$query = $this->db->query($query_str);

		if ($query->num_rows() > 0) {
			$res = $query->result();
			$this->convertToHtml($res, array('content'));
			return $res;
		} else {
			return false;
		}
	}

}

?>
