<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of videochapters
 *
 * @author thibault
 */
class videochapter extends DATA_Model {
	
	const TABLE_NAME = 'video_chapters';

	public function getTableName() {
		return self::TABLE_NAME;
	}

//put your code here

	public function getPrimaryColumns() {
		return array('id');
	}
	
	public function getRow($where = array(), $type = 'object', $columns = null) {
		$row = parent::getRow($where, $type, $columns);
		if($row->spots){
			$row->spots = json_decode($row->spots);
		}
		return $row;
	}

	public function getVideoChapter($where) {

		return $this->getRow($where);
	}

	public function insert($datas) {
		if ($datas === null) {
			$datas = $this->toArray();
		}
		if (!isset($datas['alias'])) {
			$datas['alias'] = $this->createAliasFrom($datas['title']);
		}
		
		$datas['order'] = $this->count(array('id_video' => $datas['id_video']));
		$this->load->model('video');
		$this->video->loadRow(array('id' => $datas['id_video']));
		$this->video->modification = date('Y-m-d H:i:s');
		$this->video->update();
		return parent::insert($datas);
	}

	public function update($datas, $where) {
		$this->load->model('video');
		$this->video->loadRow(array('id' => $datas['id_video']));
		$this->video->modification = date('Y-m-d H:i:s');
		if (isset($datas['title'])) {
			$datas['alias'] = $this->createAliasFrom($datas['title']);
		}
		$this->video->update();
		$this->unlink(array('src', 'thumb'), $datas);
		return parent::update($datas, $where);
	}

	public function delete($where, $order = null) {
		if ($order == null) {
			$video = parent::getRow($id);
			$order = $video->order;
		}
		$this->loadRow($where);
		$id_video = $this->id_video;
		parent::delete();

		$chapters_changing_their_order = $this->get(array("id_video"=>$id_video, "order >"=>$order), 'array');
		if ($chapters_changing_their_order) {
			foreach ($chapters_changing_their_order as &$chapter_changing_its_order) {
				$chapter_changing_its_order['order'] = $chapter_changing_its_order['order'] - 1;
			}
			$this->updateGroup($chapters_changing_their_order);
		}
	}

	public function deleteId($id, $order = null) {
		$this->delete(array('id' => $id), $order);
	}

	public function swapOrder($id_video, $order_1, $order_2) {
		$chapter1 = $this->getRow(array('id_video' => $id_video, 'order' => $order_1), 'array', 'id, id_video, order');
		$chapter2 = $this->getRow(array('id_video' => $id_video, 'order' => $order_2), 'array', 'id, id_video, order');
		$chapter1['order'] = $order_2;
		$chapter2['order'] = $order_1;
		$this->updateGroup(array($chapter1, $chapter2));
	}

	public function getVideo($id_video = null) {
		$id_video = ($id_video !== null) ? $id_video : (($this->id_video) ? $this->id_video : null);
		if (!$id_video) {
			return null;
		}
		$this->load->model('video');
		return $this->video->getVideo(array('id' => $id_video));
		
	}
	
	public function toggleOnline($id_chapter = null, $online = null){
		if($id_chapter === null){
			$id_chapter = $this->id;
		}
		if($online === null){
			$online = $this->online;
		}
		if(!$online){
			$this->loadRow(array('id' => $id_chapter));
			$online = $this->online;
		}
		$this->online = !$online;
		$this->save();
	}

}

?>
