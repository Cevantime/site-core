<?php

/* * *****************************************************************************
 *                      Youtube Class
 * ******************************************************************************
 *      Author:     Vikas Patial
 *      Email:      admin@ngcoders.com
 *      Website:    http://www.ngcoders.com
 *
 *      File:       youtube.php
 *      Version:    1.0.0
 *      Copyright:  (c) 2008 - Vikas Patial
 *                  You are free to use, distribute, and modify this software 
 *                  under the terms of the GNU General Public License.  See the
 *                  included license.txt file.
 *      
 * ******************************************************************************
 *  VERION HISTORY:
 *
 *       v1.1.0 [19.12.2012] - Fix
 *       v1.1.0 [30.03.2011] - Fix
 *       v1.0.0 [18.9.2008] - Initial Version
 *
 * ******************************************************************************
 *  DESCRIPTION:
 *
 *      NOTE: See www.ngcoders.com for the most recent version of this script 
 *      and its usage.
 *
 * ******************************************************************************
 */

class youtube {

	private $conn = false;
	private $username = "";
	private $password = "";
	private $error = false;

	function getError() {
		return $this->error;
	}

	function get($url, $options = null) {
		$this->conn = new Curl('youtube');

		$html = $this->conn->get($url, $options);

		if (strstr($html, 'verify-age-thumb')) {
			$this->error = "Adult Video Detected";
			return false;
		}

		if (strstr($html, 'das_captcha')) {
			$this->error = "Captcha Found please run on diffrent server";
			return false;
		}

		if (!preg_match('/stream_map=(.[^&]*?)&/i', $html, $match)) {
			$this->error = "Error Locating Download URL's";
			return false;
		}

		if (!preg_match('/stream_map=(.[^&]*?)(?:\\\\|&)/i', $html, $match)) {
			return false;
		}

		$fmt_url = urldecode($match[1]);

		$urls = explode(',', $fmt_url);

		$foundArray = array();

		foreach ($urls as $url) {
			if (preg_match('/itag=([0-9]+)/', $url, $tm) && preg_match('/sig=(.*?)&/', $url, $si) && preg_match('/url=(.*?)&/', $url, $um)) {
				$u = urldecode($um[1]);
				$foundArray[$tm[1]] = $u . '&signature=' . $si[1];
			}
		}

		$typeMap = array();

		$typeMap[13] = array("13", "3GP", "Low Quality - 176x144");
		$typeMap[17] = array("17", "3GP", "Medium Quality - 176x144");
		$typeMap[36] = array("36", "3GP", "High Quality - 320x240");
		$typeMap[5] = array("5", "FLV", "Low Quality - 400x226");
		$typeMap[6] = array("6", "FLV", "Medium Quality - 640x360");
		$typeMap[34] = array("34", "FLV", "Medium Quality - 640x360");
		$typeMap[35] = array("35", "FLV", "High Quality - 854x480");
		$typeMap[43] = array("43", "WEBM", "Low Quality - 640x360");
		$typeMap[44] = array("44", "WEBM", "Medium Quality - 854x480");
		$typeMap[45] = array("45", "WEBM", "High Quality - 1280x720");
		$typeMap[18] = array("18", "MP4", "Medium Quality - 480x360");
		$typeMap[22] = array("22", "MP4", "High Quality - 1280x720");
		$typeMap[37] = array("37", "MP4", "High Quality - 1920x1080");
		$typeMap[33] = array("38", "MP4", "High Quality - 4096x230");


		$videos = array();

		foreach ($typeMap as $format => $meta) {
			if (isset($foundArray[$format])) {
				$videos[] = array('ext' => strtolower($meta[1]), 'type' => $meta[2], 'url' => $foundArray[$format]);
			}
		}

		return $videos;
	}

}

class Curl {

	var $callback = false;
	var $secure = false;
	var $conn = false;
	var $cookiefile = false;
	var $header = false;
	var $cookie = false;
	var $follow = true;

	function Curl($u = false) {
		$this->conn = curl_init();
		if (!$u) {
			$u = rand(0, 100000);
		}

		//$this->cookiefile= APP_PATH.'/cache/'.md5($u);
	}

	function setCallback($func_name) {
		$this->callback = $func_name;
	}

	function close() {
		curl_close($this->conn);
		if (is_file($this->cookiefile)) {
			unlink($this->cookiefile);
		}
	}

	function doRequest($method, $url, $vars) {

		$ch = $this->conn;

		$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

		curl_setopt($ch, CURLOPT_URL, $url);
		if ($this->header) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
		} else {
			curl_setopt($ch, CURLOPT_HEADER, 0);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);



		if ($this->secure) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}

		if ($this->cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
		}

		if ($this->follow) {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		} else {
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);

		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect: ')); // lighttpd fix
		}

		$data = curl_exec($ch);



		if ($data) {
			if ($this->callback) {
				$callback = $this->callback;
				$this->callback = false;
				return call_user_func($callback, $data);
			} else {
				return $data;
			}
		} else {
			return false;
		}
	}

	function get($url) {
		return $this->doRequest('GET', $url, 'NULL');
	}

	function getError() {
		return curl_error($ch);
	}

	function post($url, $params = false) {

		$post_data = '';

		if (is_array($params)) {

			foreach ($params as $var => $val) {
				if (!empty($post_data))
					$post_data.='&';
				$post_data.= $var . '=' . urlencode($val);
			}
		} else {
			$post_data = $params;
		}

		return $this->doRequest('POST', $url, $post_data);
	}

}

function getPage($url, $post = false, $cookie = false) {
	$pURL = parse_url($url);

	$curl = new Curl($pURL['host']);

	if (strstr($url, 'https://')) {
		$curl->secure = true;
	}

	if ($post) {
		return $curl->post($url, $post);
	} else {
		return $curl->get($url);
	}
}
