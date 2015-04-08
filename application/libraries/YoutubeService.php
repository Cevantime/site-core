<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Youtube
 *
 * @author thibault
 */


require_once APPPATH . '/third_party/google-api-php-client/src/Google_Client.php';
require_once APPPATH . '/third_party/google-api-php-client/src/contrib/Google_YouTubeService.php';
require_once APPPATH . '/third_party/youtube_downloader/Youtube.php';
class YoutubeService extends Google_YouTubeService {

	//put your code here
	public function __construct() {

		$client = new Google_Client(array('authClass' => 'Google_OAuth2'));
		$client->setApplicationName('API Project');
		$client->setClientId('429019538097.apps.googleusercontent.com');
		$client->setClientSecret('_PsM0pMdcFAbvlbhKlTNaI0u');
		$CI = & get_instance();
		$client->setRedirectUri($CI->config->site_url('bo/uploads/listVideos'));
		$client->setDeveloperKey('AI39si7f4ih7yomqZB6pAWnHTkTL-QmJzBpgEqpOPVATHYYj94Y1BM1Wrc0EkyEFEWY1jLieZaRZ5Q4s8Tk8o-xpyVayjWiUbw');

		parent::__construct($client);

		if (isset($_GET['code']) && $_GET['code']) {
			$client->authenticate();
			$this->saveToken($client->getAccessToken());
			redirect('bo/uploads/listVideos', 'refresh');
		}

		$savedToken = $this->getSavedAccessToken();
		if ($savedToken) {
			$client->setAccessToken($savedToken);
		}

		if (!$client->getAccessToken()) {
			redirect($client->createAuthUrl());
		}
	}
	
	public function doUpload($infos){
		$video = $this->createVideo($infos);

		$videoPath = $infos['local_path'];
		$chunkSizeBytes = 1 * 1024 * 1024;
		$media = new Google_MediaFileUpload($infos['type'], null, true, $chunkSizeBytes);
		$media->setFileSize(filesize($videoPath));
		$id = null;
		try {

			$ret = $this->videos->insert("status,snippet", $video, array('mediaUpload' => $media));

			$st = false;
			$handle = fopen($videoPath, "rb");
			while (!$st && !feof($handle)) {
				$chunk = fread($handle, $chunkSizeBytes);
				$uploadSt = $media->nextChunk($ret, $chunk);
			}

			fclose($handle);
			$id = $uploadSt['id'];

		} catch (Google_ServiceException $e) {
			$this->addErrors('Caught google exception : ' . htmlspecialchars($e->getMessage()));
			
		}
		unlink($videoPath);
		return $id;
	}
	
	public function doUpdate($infos){
		$video = $this->createVideo($infos);
		try {
			$this->videos->update("status,snippet", $video);
		} catch (Google_ServiceException $e) {
			$this->addErrors('Caught google exception : ' . htmlspecialchars($e->getMessage()));
		}
	}
	
	public function createSnippet($infos){
		$snippet = new Google_VideoSnippet();
		$snippet->setTitle($infos['title']);
		$snippet->setDescription($infos['description']);
		$snippet->setTags(explode(',', $infos['tags']));
		$snippet->setCategoryId(22);
		return $snippet;
	}
	
	public function createStatus($infos){
		$status = new Google_VideoStatus();
		$status->privacyStatus = $infos['status'];
		return $status;
	}
	
	public function createVideo($infos){
		$status = $this->createStatus($infos);
		$snippet = $this->createSnippet($infos);
		$video = new Google_Video();
		if (isset($infos['id']) && $infos['id']) {
			$video->setId($infos['id']);
		}
		$video->setSnippet($snippet);
		$video->setStatus($status);
		return $video;
	}

	private function saveToken($token) {
		$CI = & get_instance();
		$CI->load->model('Configuration');
		$CI->Configuration->setValue('youtube_access_token', $token);
	}

	private function getSavedAccessToken() {
		$CI = & get_instance();
		$CI->load->model('Configuration');
		return $CI->Configuration->getValue('youtube_access_token');
	}

}

?>
