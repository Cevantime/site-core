<?php

class User extends DATA_Model {

	public function __construct() {
		parent::__construct();
	}

	function login($username, $password) {
		$this->db->select('id, username, password');
		$this->db->from($this->getTableName());
		$this->db->where('username', $username);
		$this->db->where('password', MD5($password));
		$this->db->limit(1);

		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function deleteUser($user_id) {
		return $this->db->delete($this->getTableName(), array('id' => $user_id));
	}

	public function getUser($user_id) {
		$this->db->select();
		$this->db->from($this->getTableName());
		$this->db->where('id', $user_id);

		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		} else {
			return false;
		}
	}
	
	public function getUserWithBoughts($id_user = null){
		if($id_user === null){
			$id_user = $this->id_user;
		}
		$user = $this->getUser($id_user);
		$this->load->model('transaction');
		$transactions = $this->transaction->get(array('id_client'=> $id_user,'status'=>'ok'));
		
		$ref_trans_by_type = array();
		if(!$transactions) return $user;
		foreach ($transactions as $transaction) {
			if (!isset($ref_trans_by_type[$transaction->type])) {
				$ref_trans_by_type[$transaction->type] = array();
			}
			$ref_trans_by_type[$transaction->type][] = $transaction->ref;
		}
		
		
		foreach (transaction::$TRANSACTION_TYPES as $type) {
			if (!isset($ref_trans_by_type[$type]) || !$ref_trans_by_type[$type]) {
				continue;
			}
			$this->load->model($type);
			if($type === 'video' || $type === 'tutorial') {
				$join = 'tuto_categories';
				$this->db->join($join, 'tuto_categories.id='.$this->{$type}->getTableName().'.categorie_id_tuts','LEFT');
			}
			$user->{$type.'s'} = $this->{$type}->get($type.'s.id IN ('.implode($ref_trans_by_type[$type],',').')', 'object', '*,videos.alias as alias, tuto_categories.alias as category_alias');
		}
		return $user;
	}

	public function insert($array) {
		$currentDate = date('Y-m-d H:i:s');
		$array['inscription_date'] = $currentDate;
		$array['last_visit'] = $currentDate;
		return parent::insert($array);
	}
	
	/*** permet de mettre à jour la liste des tutos achetés ****/
	public function updateTutosBought($data){
		parent::update(array('achats'=>$data['infosAchats']),array('id'=>$data['idClient']));
	}

	public function getTableName() {
		return 'users';
	}
	
}?>