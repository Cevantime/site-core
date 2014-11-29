<?php

/*
 * Description du mod�le pour la gestions des administrateurs
 *
 * @author alto971
 */

class Bouser extends DATA_Model {

	public function __construct() {
		parent::__construct();
	}

	public function getTableName() {
		return 'bo_users';
	}

	public function getLinkedAdmin($id_user = null) {
		if ($id_user == null) {
			$id_user = $this->user_id;
		}
		return $this->getRow(array('user_id' => $id_user));
	}

	/*
	 * 	Récupération en BDD d'un administrateur en fonction de son id 
	 */

	public function getAdmin($admin_id) {
		$this->db->select();
		$this->db->from($this->getTableName());
		$this->db->where('id', $admin_id);

		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		} else {
			return false;
		}
	}

	/*
	 * 	Mise à jour en BDD d'un administrateur en fonction de son id 
	 */

	public function updateAdmin($admin, $id) {
		$data = array(
			'login' => $admin['login'],
			'password' => $admin['password'],
			'status' => $admin['status']
		);
		$this->db->where('id', '' . $id . '');
		$this->db->update($this->getTableName(), $data);
	}

	/*
	 * 	Suppression en BDD d'un administrateur en fonction de son id 
	 */

	public function deleteBouser($admin_id) {
		return $this->db->delete($this->getTableName(), array('id' => $admin_id));
	}

}

?>