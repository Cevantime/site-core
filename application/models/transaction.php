<?php


/**
 * Description of transaction
 *
 * @author alto
 */
class transaction extends DATA_Model {
	
	public static $TRANSACTION_TYPES = array('video', 'tutorial', 'resource');

	public function getTableName() {
		return 'transactions';
	}
	
	public function insert($datas = null){
		if($datas == null){
			$datas = $this->toArray();
		}
		if(!isset($datas['date_trans'])){
			$datas['date_trans'] = date('Y-m-d H:i:s');
		}
		return parent::insert($datas);
	}
	
	/* récupération de la liste complète des transactions ***/
	public function getListTransactions($where = null, $limit = null, $offset = null){
				
		$this->db->select('*');
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		if($where !== null){
			$this->db->where($where);
		}
		$query = $this->db->get();
		$transactions = $query->result();

		return $transactions;
	}
	
	/* récupération des informations du client d'après son email ou son id_client ***/
	public function getInfosClient($where = null, $limit = null, $offset = null){
				
		$this->db->select('*');
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		if($where !== null){
			$this->db->where($where);
		}
		$query = $this->db->get();
		$infos = $query->result();

		return $infos;
	}
	
}