<?php


/**
 * Description of premium
 *
 * @author alto
 */
class premiumoffer extends DATA_Model {

	public function getTableName() {
		return 'offres_premium';
	}
	
	/* récupération de la liste complète des offres ***/
	public function getListOffers($where = null, $limit = null, $offset = null){
				
		$this->db->select('*');
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		if($where !== null){
			$this->db->where($where);
		}
		$query = $this->db->get();
		$offres = $query->result();

		return $offres;
	}
	
	
}