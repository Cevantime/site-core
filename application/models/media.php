<?php

/**
 * Description of medias
 *
 * @author alto
 */
class media extends DATA_Model {

	public function getTableName() {
		return 'medias';
	}

	public function insert($datas = null) {
		if ($datas === null) {
			$datas = $this->toArray();
		}
		if (!isset($datas['alias'])) {
			$datas['alias'] = $this->createAliasFrom($datas['name']);
		}
		$datas['date_add'] = date('Y-m-d H:i:s');
		parent::insert($datas);
	}

	public function getListMedias($limit = null, $offset = null) {
		$this->db->select('*');
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		return $query->result();
	}

	public function getListImages($limit = null, $offset = null) {
		$this->db->select('*');
		$this->db->where('type LIKE \'%' . $this->db->escape_like_str('image') . '%\'');
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		return $query->result();
	}

	public function getListVideos($limit = null, $offset = null) {
		$this->db->select('*');
		$this->db->where('type LIKE \'%' . $this->db->escape_like_str('video') . '%\'');
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		return $query->result();
	}
	
	public function getUserMedias($user_id, $limit=null, $offset=null){
		$this->db->select('*, medias.id as id, medias.type as type');
		$this->db->where(array('medias.user_id'=>$user_id));
		$this->db->join('tutorials', 'tutorials.id='.$this->getTableName().'.tuto_id', 'LEFT');
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		return $query->result();
	}
	
	public function getUserVideos($user_id, $limit=null, $offset=null){
		$this->db->select('*');
		$this->db->where('type LIKE \'%' . $this->db->escape_like_str('video') . '%\'');
		$this->db->where(array('medias.user_id'=>$user_id));
		$this->db->join('tutorials', 'tutorials.id='.$this->getTableName().'.tuto_id');
		$this->db->from($this->getTableName());
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		return $query->result();
	}
	
	public function getUserImages($user_id, $limit=null, $offset=null){
		$this->db->select('*');
		$this->db->where('type LIKE \'%' . $this->db->escape_like_str('image') . '%\'');
		$this->db->where(array('medias.user_id'=>$user_id));
		$this->db->from($this->getTableName());
		$this->db->join('tutorials', 'tutorials.id='.$this->getTableName().'.tuto_id');
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		return $query->result();
	}

	public function getRandomVideo() {
		$this->db->select('*');
		$this->db->where('type LIKE \'%' . $this->db->escape_like_str('video') . '%\'');
		$this->db->from($this->getTableName());

		$this->db->limit(1);

		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			$res = $query->result();
			return $res[0];
		} else
			return false;
	}

	public function getListVideosWithYoutubeInfos($limit = null, $offset = null) {
		$this->db->select('*');
		$this->db->where('type LIKE \'%' . $this->db->escape_like_str('video') . '%\'');
		$this->db->from($this->getTableName());
		$this->db->order_by('date_add', 'desc');
		if ($limit !== null) {
			$this->db->limit($offset, $limit);
		}
		$query = $this->db->get();
		$videos = $query->result();
		$ids = array();
		foreach ($videos as $video) {
			$ids[] = $video->src_file;
		}
		$this->load->library('YoutubeService', null, 'youtube');
		$youtubeVideosRes = $this->youtube->videos->listVideos(implode(',', $ids), 'id,snippet,status');
		$youtubeVideos = $youtubeVideosRes['items'];
		foreach ($youtubeVideos as $key => $youtubeVideo) {
			$media = &$videos[$key];
			$media->status = $youtubeVideo['status']['privacyStatus'];
			$media->tags = implode(',', $youtubeVideo['snippet']['tags']);
			$media->uploadStatus = $youtubeVideo['status']['uploadStatus'];
		}
		return $videos;
	}

	public function deleteImageWithId($id) {
		$media = $this->getRow(array('id' => $id));
		unlink($media->src_file);
		return parent::deleteId($id);
	}

	public function deleteVideo($where) {
		$videoToDelete = $this->getVideoWithYoutubeInfos($where);
		$id_youtube = $videoToDelete->src_file;
		$this->load->library('YoutubeService', null, 'youtube');
		$this->youtube->videos->delete($id_youtube);
		return parent::delete($where);
	}

	public function getVideoWithYoutubeInfos($where) {
		$media = $this->getRow($where);

		if (strpos($media->type, 'video') === false) {
			return false;
		}
		$this->load->library('YoutubeService', null, 'youtube');
		$videoRes = $this->youtube->videos->listVideos($media->src_file, 'id,snippet,status');
		if ($videoRes['pageInfo']['totalResults'] == 1) {
			$video = $videoRes['items'][0];
			$media->status = $video['status']['privacyStatus'];
			$media->tags = implode(',', $video['snippet']['tags']);
			$media->uploadStatus = $video['status']['uploadStatus'];
			return $media;
		}
		return false;
	}
	

	/*
	 *
	 * 	r�cup�re les m�dias pr�sents dans un dossier en BDD
	 */

	public function getMediasDir($dirName) {
		$resultats = array();
		$query = $this->db->get_where($this->getTableName());

		if ($query->num_rows() >= 1) {
			$res = $query->result();
			foreach ($res as $image) {
				if (strstr($image->src_file, $dirName)) {
					array_push($resultats, $image);
				}
			}
			return $resultats;
		}
		return false;
	}

}
