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
class page extends DATA_Model {
	
	const TABLE_NAME = 'tuto_pages';

	public function getTableName() {
		return self::TABLE_NAME;
	}

//put your code here

	public function getPrimaryColumns() {
		return array('id');
	}

	public function getPage($where) {

		return $this->getRow($where);
	}

	public function insert($datas) {
		if ($datas === null) {
			$datas = $this->toArray();
		}
		if (!isset($datas['alias'])) {
			$datas['alias'] = $this->createAliasFrom($datas['title']);
		}
		$this->load->library('BBCodeParser', '', 'parser');
		$this->parser->parse($datas['content']);
		$anchors = array();
		$rootNode = $this->parser->getTreeRoot();
		$i = 0;
		foreach ($rootNode->getChildren() as $child) {
			if ($child instanceof \JBBCode\ElementNode && $child->getTagName() == 'section1') {
				$anchors['tuto-section-' . $i++] = $child->getAsText();
			}
		}
		$datas['anchors'] = json_encode($anchors);
		$datas['order'] = $this->count(array('id_tuto' => $datas['id_tuto']));
		$this->load->model('tutorial');
		$this->tutorial->loadRow(array('id' => $datas['id_tuto']));
		$this->tutorial->modification = date('Y-m-d H:i:s');
		$this->tutorial->update();
		return parent::insert($datas);
	}

	public function update($datas, $where) {
		$this->load->library('BBCodeParser', '', 'parser');
		$this->parser->parse($datas['content']);
		$anchors = array();
		$rootNode = $this->parser->getTreeRoot();
		$i = 0;
		
		if(isset($datas['title'])){
			$datas['alias'] = $this->createAliasFrom($datas['title']);
		}
		
		foreach ($rootNode->getChildren() as $child) {
			if ($child instanceof \JBBCode\ElementNode && $child->getTagName() == 'section1') {
				$anchors['tuto-section-' . $i++] = $child->getAsText();
			}
		}

		$datas['anchors'] = json_encode($anchors);
		$this->load->model('tutorial');
		$this->tutorial->loadRow(array('id' => $datas['id_tuto']));
		$this->tutorial->modification = date('Y-m-d H:i:s');
		$this->tutorial->update();
		return parent::update($datas, $where);
	}

	public function delete($where, $order = null) {
		if ($order == null) {
			$page = parent::getRow($id);
			$order = $page->order;
		}
		$this->loadRow($where);
		$id_tuto = $this->id_tuto;
		parent::delete();

		$pages_changing_their_order = $this->get(array("id_tuto"=>$id_tuto, "order >"=>$order), 'array');
		if ($pages_changing_their_order) {
			foreach ($pages_changing_their_order as &$page_changing_its_order) {
				$page_changing_its_order['order'] = $page_changing_its_order['order'] - 1;
			}
			$this->updateGroup($pages_changing_their_order);
		}
	}

	public function deleteId($id, $order = null) {
		$this->delete(array('id' => $id), $order);
	}

	public function swapOrder($id_tuto, $order_1, $order_2) {
		$page1 = $this->getRow(array('id_tuto' => $id_tuto, 'order' => $order_1), 'array', 'id, id_tuto, order');
		$page2 = $this->getRow(array('id_tuto' => $id_tuto, 'order' => $order_2), 'array', 'id, id_tuto, order');
		$page1['order'] = $order_2;
		$page2['order'] = $order_1;
		$this->updateGroup(array($page1, $page2));
	}

	public function getTuto($id_tuto = null) {
		if (!$this->tuto) {
			$id_tuto = ($id_tuto !== null) ? $id_tuto : (($this->id_tuto) ? $this->id_tuto : null);
			if (!$id_tuto) {
				return null;
			}
			$this->load->model('tutorial');
			return $this->tutorial->getTuto(array('id' => $id_tuto));
		}
	}
	
	public function toggleOnline($id_page = null, $online = null){
		if($id_page === null){
			$id_page = $this->id;
		}
		if($online === null){
			$online = $this->online;
		}
		if(!$online){
			$this->loadRow(array('id' => $id_page));
			$online = $this->online;
		}
		$this->online = !$online;
		$this->save();
	}

}

?>
