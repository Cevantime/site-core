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
class topic extends DATA_Model {

	public function getTableName() {
		return 'topics';
	}

	public function insert($array) {
		if ($array === null) {
			$array = $this->_datas;
		}
		if (!isset($array['alias'])) {
			$array['alias'] = $this->createAliasFrom($array['title']);
		}
		$currentDate = date('Y-m-d H:i:s');
		$array['date_add'] = $currentDate;
		$array['last_modif'] = $currentDate;
		return parent::insert($array);
	}

	public function update($array = null, $where = null) {
		$array = ($array !== null) ? $array : $this->_datas;
		if(isset($array['title'])){
			$array['alias'] = $this->createAliasFrom($array['title'],true);
		}
		$currentDate = date('Y-m-d H:i:s');
		$array['last_modif'] = $currentDate;
		return parent::update($array, $where);
	}

	public function getTopic($where = null) {
		if ($where == null) {
			$where = array('id' => $this->id);
		}
		$query = $this->db->get_where($this->getTableName(), $where);
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		}
		return false;
	}

	public function getTopicsByCategoryWithAuthors($id_category, $limit = null, $offset = null) {
		return $this->listingTopicsByCategoryWithAuthors(null, $id_category, $limit, $offset);
	}
	
	public function getLocaleTopicsByCategoryWithAuthors($id_category, $limit = null, $offset = null){
		return $this->listingTopicsByCategoryWithAuthors(array('locale'=>locale()), $id_category, $limit, $offset);
	}

	private function listingTopicsByCategoryWithAuthors($where = null, $id_category=null, $limit = null, $offset = null){
		if($id_category===null){
			$id_category = $this->category;
		}
		$this->db->select($this->getTableName() . '.*, users.login')
				->where(array('category' => $id_category))
				->join('users', 'users.id=' . $this->getTableName() . '.user_id');
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		if($where !== null){
			$this->db->where($where);
		}
		$query = $this->db->get($this->getTableName());
		if ($query->num_rows() != 0) {
			return $query->result();
		}
		return false;
	}
	
	public function getNotPostItTopicsByCategoryWithLastMessageAndAuthor($limit = null, $offset = null, $id_category = null) {
		if ($id_category === null) {
			$id_category = $this->getData('category');
		}
		return $this->listingTopicsWithLastMessageAndAuthors(array('category' => $id_category, 'post_it' => 0, 'locale' => locale()), $limit, $offset);
	}

	public function getPostItTopicsByCategoryWithLastMessageAndAuthor($limit = null, $offset = null, $id_category = null) {
		if ($id_category === null) {
			$id_category = $this->getData('category');
		}
		return $this->listingTopicsWithLastMessageAndAuthors(array('category' => $id_category, 'post_it' => 1, 'locale' => locale()), $limit, $offset);

	}
	public function listingTopicsWithLastMessageAndAuthors($where = null, $limit = null, $offset = null){
		$this->db->select($this->getTableName() . '.*, users.login, users.avatar as avatar, u.login as last_message_login, u.avatar as last_message_avatar, topic_messages.shown as shown,
			topic_messages.content as last_message, topic_messages.date_add as date_add_message, topic_messages.last_modif as last_modif_message, mm.content as mod_msg')
				->join('users', 'users.id=' . $this->getTableName() . '.user_id')
				->join('topic_messages', 'topic_messages.id_topic=' . $this->getTableName() . '.id')
				->join('users as u', 'u.id=topic_messages.id_user')
				->join('moderation_messages as mm', 'mm.id=' . $this->getTableName() . '.moderation_message', 'left')
				->order_by('date_add_message DESC');
		if($where)	$this->db->where ($where);
		$this->db->from($this->getTableName());
		$query = $this->db->_compile_select();
		$this->db->_reset_select();
		$query = 'SELECT *, COUNT(*) as nbMessages FROM (' . $query . ') as derived GROUP BY id ORDER BY last_modif DESC';
		if ($limit !== null) {
			$query .= ' LIMIT ' . $limit . (($offset != null) ? ',' . $offset : '');
		}
		$query = $this->db->query($query);
		if ($query->num_rows() != 0) {
			$res = $query->result();
			$this->convertToHtml($res, array('last_message'));
			return $res;
		}
		return false;
	}
	
	public function getHotTopics($limit = null, $offset = null){
		return $this->listingTopicsWithLastMessageAndAuthors(NULL, $limit, $offset);
	}
	
	public function getLocaleHotTopics($limit = null, $offset = null) {
		return $this->listingTopicsWithLastMessageAndAuthors(array('locale'=>locale()), $limit, $offset);
	}
	
	

	public function close($messageId, $id=null) {
		if ($id === null) {
			$id = $this->id;
		}

		if ($id) {
			return parent::update(array('id' => $id, 'open' => false, 'moderation_message' => $messageId));
		}
		return false;
	}

	public function open($id_topic = null) {
		if ($id_topic === null) {
			$id_topic = $this->id;
		} else {
			$this->loadRow(array('id' => $id_topic));
		}
		if ($id_topic) {
			$this->load->model('moderationmessage');
			$this->moderationmessage->loadRow(array('id' => $this->moderation_message));
			if ($this->moderationmessage->type == 'CUSTOM_MESSAGE') {
				$this->moderationmessage->delete();
			}
			$this->open = true;
			return $this->update();
		}
		return false;
	}

	public function getRelatedMessages($limit = null, $offset = null, $id = null) {
		if ($id === null) {
			$id = $this->id;
		}
		$this->load->model('topicmessage');
		$messages = $this->topicmessage->getMessagesByTopic($id, $limit, $offset);
		return $messages;
	}

	public function getTopicWithAuthor($where = null) {
		if ($where === null) {
			$where = $this->_datas;
		}
		$query = $this->db->select($this->getTableName() . '.*, '
								. 'users.login, users.avatar')->from($this->getTableName())->where($where)
						->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		}
		return false;
	}

	public function getTopicWithAuthorAndCategory($where = null) {
		if ($where === null) {
			$where = $this->_datas;
		}
		$query = $this->db->select($this->getTableName() . '.*, '
								. 'users.login, users.avatar, topic_categories.name as category_name, topic_categories.alias as category_alias')
						->from($this->getTableName())->where($where)
						->join('topic_categories', 'topic_categories.id=' . $this->getTableName() . '.category', 'left')
						->join('users', 'users.id=' . $this->getTableName() . '.user_id', 'left')->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		}
		return false;
	}
	
	private function listingWithAuthors($where = null, $limit = null, $offset = null){
		$this->db->select($this->getTableName() . '.*, users.login, users.avatar')->from($this->getTableName())
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

	public function getListWithAuthors($limit = null, $offset = null) {
		return $this->listingWithAuthors(null, $limit, $offset);
	}
	
	public function getLocaleListWithAuthors($limit = null, $offset = null) {
		return $this->listingWithAuthors(array('locale'=>locale()), $limit, $offset);
	}
	
	public function getCategory($where = null){
		$where = ($where) ? $where : $this->buildPrimaryWhere();
		$this->load->model('topiccategory');
		$topic = $this->getRow($where);
		return $this->topiccategory->getRow(array('id' => $topic->category));
	}

	public function search($limit = null, $offset = null, $search = null, $columns = null) {
		$this->prepareSearch(null, null, $search, $columns);
		$this->db->join('topic_categories', 'topic_categories.id=' . $this->getTableName() . '.category')
				->where(array('open' => 1, 'locale'=>locale()))
				->join('users', 'users.id=' . $this->getTableName() . '.user_id')
				->join('topic_messages', 'topic_messages.id_topic=' . $this->getTableName() . '.id')
				->join('users as u', 'u.id=topic_messages.id_user')
				->join('moderation_messages as mm', 'mm.id=' . $this->getTableName() . '.moderation_message', 'left');
		$this->db->select($this->getTableName() . '.*, topic_categories.alias as category_alias,users.login, u.login as last_message_login, topic_messages.shown as shown,
			topic_messages.content as last_message, topic_messages.date_add as date_add_message, topic_messages.last_modif as last_modif_message,
			mm.content as mod_msg');
		$query = $this->db->get($this->getTableName());
		$query = $this->db->last_query();
		$query = 'SELECT *, COUNT(*) as nbMessages FROM (' . $query . ') as derived GROUP BY id';
		if ($limit !== null) {
			$query .= ' LIMIT ' . $limit . (($offset != null) ? ',' . $offset : '');
		}
		$query = $this->db->query($query);
		return $query->result();
	}

}

?>
