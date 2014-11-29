<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of page
 *
 * @author thibault
 */
class topicMessage extends DATA_Model {

	private $_topic;

	public function getTableName() {
		return 'topic_messages';
	}

//put your code here

	public function getPrimaryColumns() {
		return array('id');
	}

	public function getMessage($id) {
		return parent::getId($id);
	}

	public function insert($datas) {
		$datas['date_add'] = $currentDate = date('Y-m-d H:i:s');
		$datas['last_modif'] = $currentDate;
		$this->load->model('topic');
		$this->topic->loadRow(array('id' => $datas['id_topic']));
		$this->topic->last_modif = date('Y-m-d H:i:s');
		$this->topic->update();
		return parent::insert($datas);
	}

	public function update($datas = null, $where = null) {
		if ($datas === null) {
			$datas = $this->toArray();
		}
		$currentDate = date('Y-m-d H:i:s');
		$datas['last_modif'] = $currentDate;

		return parent::update($datas, $where);
	}

	public function getMessageWithAuthor($id_message = null) {
		if ($id_message == null) {
			$id_message = $this->id;
		}
		$this->db->select('topic_messages.*, users.login');
		$this->db->join('users', 'users.id=' . $this->getTableName() . '.id_user');
		$query = $this->db->get_where($this->getTableName(), array($this->getTableName() . '.id' => $id_message));
		if ($query->num_rows()) {
			$rows = $query->result();
			return $rows[0];
		}
		return false;
	}
	
	public function getLastMessage($id_topic = null){
		if ($id_topic === null) {
			$id_topic = $this->id_topic;
		}
		$this->db->from($this->getTableName());
		$this->db->where(array('id_topic' => $id_topic));
		$this->db->order_by('id DESC');
		$this->db->limit(1);

		$query = $this->db->get();
		if ($query->num_rows()) {
			$res = $query->result();
			return $res[0];
		}
		return false;
	}

	public function getLastMessageFrom($id_user = null, $id_topic = null) {
		$where = array();
		if ($id_user === null) {
			$id_user = $this->id_user;
		}
		if($id_user) $where['id_user'] = $id_user;
		if ($id_topic === null) {
			$id_topic = $this->id_topic;
		}
		if($id_topic) $where['id_topic'] = $id_topic;
		
		$this->db->from($this->getTableName());
		$this->db->where($where);
		$this->db->order_by('date_add DESC');
		$this->db->limit(1);
		$query = $this->db->get();
		if ($query->num_rows()) {
			$res = $query->result();
			return $res[0];
		}
		return false;
	}

	public function getTopic($id_tuto = null) {
		if (!$this->tuto) {
			$id_tuto = ($id_tuto !== null) ? $id_tuto : (($this->id_tuto) ? $this->id_tuto : null);
			if (!$id_tuto) {
				return null;
			}
			$this->load->model('tutorial');
			return $this->tutorial->getTuto(array('id' => $id_tuto));
		}
	}

	public function hide($messageId, $id = null) {
		if ($id === null) {
			$id = $this->id;
		}
		if ($id) {
			return parent::update(array('id' => $id, 'shown' => false, 'moderation_message' => $messageId));
		}
		return false;
	}

	public function show($id = null) {
		if ($id === null) {
			$id = $this->id;
		} else {
			$this->loadRow(array('id' => $id));
		}
		if ($id) {
			$this->load->model('moderationmessage');
			$this->moderationmessage->loadRow(array('id' => $this->moderation_message));

			if ($this->moderationmessage->type == 'CUSTOM_MESSAGE') {
				$this->moderationmessage->delete();
			}
			$this->shown = true;
			$this->moderation_message = null;
			return $this->update();
		}
		return false;
	}

	public function getLastValidWithTopicTitleAndUserLogin($limit = null, $offset = null) {
		$this->db->select($this->getTableName() . '.*, users.login, users.avatar, topics.title, topic_categories.name as category_name')
				->from($this->getTableName())
				->where($this->getTableName() . '.shown = 1 AND topics.open = 1')
				->join('users', 'users.id=' . $this->getTableName() . '.id_user', 'left')
				->join('topics', 'topics.id=' . $this->getTableName() . '.id_topic', 'left')
				->join('topic_categories', 'topic_categories.id=topics.category', 'left')
				->order_by('id DESC');
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$res = $this->db->get()->result();
//		$this->convertToHtml($res, array('content'));
		return $res;
	}

	public function getMessagesByTopic($id_topic, $limit = null, $offset = null) {

		$this->db->select($this->getTableName() . '.*, users.login, users.avatar, moderation_messages.content as `modMsg`')
				->from($this->getTableName())
				->join('users', 'users.id=' . $this->getTableName() . '.id_user', 'left')
				->join('moderation_messages', 'moderation_messages.id=' . $this->getTableName() . '.moderation_message', 'left')
				->where(array('id_topic' => $id_topic));
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$this->load->library('BBCodeParser', '', 'parser');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			$messages = $query->result();

			foreach ($messages as &$message) {
				$content = $message->shown ? $message->content : $message->modMsg;
				$this->parser->parse($content);
				$message->content = $this->parser->getAsHtml();
				if (isset($message->bigThumb)) {
					$message->bigThumb = to_stdObject(json_decode($message->bigThumb));
				}
				if (isset($message->smallThumb)) {
					$message->smallThumb = to_stdObject(json_decode($message->smallThumb));
				}
			}
			return $messages;
		}
		return false;
	}

	public function getRankInTopic($order, $id_message = null) {
		if ($id_message === null) { 
			//if id is not specified, we look for an id in the object fields
			$id_message = $this->id;
			if($this->{$order} !== null && $this->id_topic){
				//if the object contains all the necessary infos, we use it as
				//reference.
				$ref = $this;
			}
		}
		if(!isset($ref)){
			//if we don't have a reference object, we get it using its id
			$ref = $this->getRow(array('id' => $id_message));
		}

		return $this->db->select()->from($this->getTableName())
						->where(array('id_topic' => $ref->id_topic, $order . ' < ' => $ref->{$order}))
						->count_all_results();
	}

}

?>
