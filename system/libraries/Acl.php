<?php

/*
 * Description de la classe Acl charg�e de v�rifier les droits
 * Script inspir� de : http://www.developer.com/lang/php/creating-a-custom-acl-in-php.html
 *
 * @author alto971
 */

class Acl {

	private $CI;
	private $table;

	/*	 * *****  constructeur : on r�cup�re une instance de l'application  **** */

	function __construct() {
		$this->CI = & get_instance();
		$this->table = 'user_permissions';
	}

	/*
	 * 	V�rification en BDD que le statut en cours poss�de bien la permission demand�e
	 * 	table :user_permissions 
	 */

	function check($permission, $statut) {

		echo '<br/>on check l\'utilisateur avec groupe-status : ' . $statut;
		echo '<br/>a-t-il la permission : ' . $permission;
		//on v�rifie la permission
		if (!$this->statut_permissions($permission, $statut)) {
			echo '<br/>vous navez pas les droits !';
			return false;
		}
		echo '<br/>vous avez bien les droits : OK !';
		return true;
	}

	/*
	 * 	V�rification sur la permission en cours
	 * 	table :user_permissions 
	 */

	function statut_permissions($permission, $statut) {

		$ok = $this->isOK($permission, $statut);
		if ($ok) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * 	V�rification en BDD que le statut en cours poss�de bien la permission demand�e
	 * 	table :user_permissions 
	 */

	public function isOK($permission, $statut) {
		$this->CI = & get_instance();
		$this->CI->db->select();
		$this->CI->db->from($this->table);
		$this->CI->db->where('permName', $permission);
		$this->CI->db->where('status', $statut);

		$query = $this->CI->db->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		} else {
			return false;
		}
	}

	/*
	 * 	V�rification en BDD que l'article en cours est bien de l'auteur
	 * 	table : articles
	 */

	public function isAuthor($userID, $articleID) {
		$this->CI = & get_instance();
		$this->CI->db->select();
		$this->CI->db->from('articles');
		$this->CI->db->where('id', $articleID);
		$this->CI->db->where('user_id', $userID);

		$query = $this->CI->db->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		} else {
			return false;
		}
	}

	/*
	 * 	V�rification en BDD que l'article en cours est bien de l'auteur
	 * 	table : tutorials
	 */

	public function isAuthorTuto($userID, $articleID) {
		$this->CI = & get_instance();
		$this->CI->db->select();
		$this->CI->db->from('tutorials');
		$this->CI->db->where('id', $articleID);
		$this->CI->db->where('user_id', $userID);

		$query = $this->CI->db->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		} else {
			return false;
		}
	}
	
	public function isAuthorVideo($userID, $videoID) {
		$this->CI = & get_instance();
		$this->CI->db->select();
		$this->CI->db->from('videos');
		$this->CI->db->where('id', $videoID);
		$this->CI->db->where('user_id', $userID);

		$query = $this->CI->db->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		} else {
			return false;
		}
	}

}

?>