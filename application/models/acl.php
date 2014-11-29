<?php

/*
 * Description du mod�le Acl charg� de v�rifier les droits
 * Script inspir� de : http://www.developer.com/lang/php/creating-a-custom-acl-in-php.html
 *
 * @author alto971
 */

class acl extends DATA_Model {

	public $_permissions = array();

	public function __construct() {
		parent::__construct();
		$this->_permissions = $this->getList();
	}

	public function getTableName() {
		return 'user_permissions';
	}

	/*
	 * 	V�rification en BDD que le statut en cours poss�de bien la permission demand�e
	 * 	table :user_permissions 
	 */

	function check($permission, $statut) {

		//echo '<br/>on check l\'utilisateur avec groupe-status : '.$statut;
		//echo '<br/>a-t-il la permission : '.$permission;
		//on v�rifie la permission
		if (!$this->statut_permissions($permission, $statut)) {
			//echo '<br/>vous navez pas les droits !';
			return false;
		}
		//echo '<br/>vous avez bien les droits : OK !';
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

	public function isOK($permission, $statut, $permType = 1) {
		
				
		foreach ($this->_permissions as $perm) {
			if ($perm->permName == $permission && $statut == $perm->status && $permType == $perm->permType) {
				return true;
			}
		}
		return false;
	}

	/*
	 * 	V�rification en BDD que l'article en cours est bien de l'auteur
	 * 	table : articles
	 */

	public function isAuthor($userID, $articleID) {
		$this->db->select();
		$this->db->from('articles');
		$this->db->where('id', $articleID);
		$this->db->where('user_id', $userID);

		$query = $this->db->get();
		if ($query->num_rows()) {
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
		$this->db->select();
		$this->db->from('tutorials');
		$this->db->where('id', $articleID);
		$this->db->where('user_id', $userID);

		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		} else {
			return false;
		}
	}
	
	/*
	 * 	V�rification en BDD que la video en cours est bien de l'auteur
	 * 	table : tutorials
	 */

	public function isAuthorVideo($userId, $videoId) {
		$this->load->model('video');
		return $this->video->getRow(array('id'=>$videoId, 'user_id'=>$userId));
	}

	/*
	 * 	Vérification que l'on peut montrer les boutons d'action de la vue pour les articles
	 * 	
	 */

	public function isVisible($permissions, $status, $userID, $articleID) {
		if ($this->checkMultiple($permissions, $status)) {
			if ($this->isAuthor($userID, $articleID) || $status == 'superadmin') {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/*
	 * 	Vérification que l'on peut montrer les boutons d'action de la vue pour les tutos
	 * 	
	 */

	public function isVisibleTuto($permissions, $status, $userID, $articleID) {
		if ($this->checkMultiple($permissions, $status)) {
			if ($this->isAuthorTuto($userID, $articleID) || $status == 'superadmin') {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/** permet de vérifier un tableau de permissions en un seul appel ** */
	public function checkMultiple($permissions, $status) {
		$isOk = false;
		$i = 0;
		for ($i; $i < count($permissions); $i++) {
			$isOk = $this->check($permissions[$i], $status);
			if ($isOk)
				break;
		}
		return $isOk;
	}

}

?>