<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of articles
 *
 * @author thibault
 */
class captcha extends DATA_Model {

	const TABLE_NAME = 'captcha';
	const TIME_EXPIRATION = 7200;

	public function getTableName() {
		return self::TABLE_NAME;
	}

	public function createCaptcha(){
		$this->load->helper('captcha');
		$captcha_config = array(
			'img_path' => './images/captchas/',
			'img_url' => base_url().'images/captchas/',
			'expiration' => self::TIME_EXPIRATION
		);
		$cap = create_captcha($captcha_config);
		$this->captcha_time = $cap['time'];
		$this->ip_address = $this->input->ip_address();
		$this->word = $cap['word'];
		$this->src = $cap['image'];
		$this->insert();
		
		return $cap['image'];
	}
	
	public function checkCaptcha($word, $ip_address = null) {
		if($ip_address === null){
			$ip_address = $this->input->ip_address();
		}
		$this->cleanOldCaptchas();
		
		return (bool) $this->count(
				array(
					'word' => $word,
					'ip_address' => $ip_address,
					'captcha_time > ' => time() - self::TIME_EXPIRATION
				)
		);
	}
	
	public function cleanOldCaptchas(){
		$this->delete(array('captcha_time <' => time()-self::TIME_EXPIRATION));
	}
	
}

?>
