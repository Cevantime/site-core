<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Payements
 *
 * @author thibault
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Payements {

	var $site; //id du site auprès de Paysite
	var $test; // est- on en test ?
	var $urlPaysite; //url de la page de paiement
	var $apiKey; // clé de l'API
	var $wait; //transaction validée par API
	var $debug;

	function __construct($test = 0, $wait = 0) {
		$this->site = "7201"; //"7993"; //7201 site réel
		$this->urlPaysite = "https://billing.paysite-cash.biz";
		$this->apiKey = '3941dafe0ef7d6885221ed98be458047'; //api site réel
		//$this->apiKey = '93741e87e249f1924e4e80dfd891a5fd';

		$this->test = $test;

		$this->wait = $wait;
		if ($test == 1)
			$this->debug = 1;
	}

	/*	 * * ce bouton est associé aux ressources *** */

	function createIdButton($id, $alias) {
		$button = '<form name ="form" action="' . base_url() . 'payement/sendata"  method="post">
					<input name="id" type = "hidden" id = "id" value = "' . $id . '">
					<input name="alias" type = "hidden" id = "alias" value = "' . $alias . '">
					<input class="bt" type ="submit" value="Commander"/>
				  </form>';
		return $button;
	}

	/*	 * * bouton associé aux offres premium ** */

	function createIdPremiumButton($id) {
		$button = '<form name ="form" action="' . base_url() . 'premium/sendata"  method="post">
					<input name="id" type = "hidden" id = "id" value = "' . $id . '">
					<input class="bt" type ="submit" value="Payer"/>
				  </form>';
		return $button;
	}

	/*	 * * utilisé avant un envoi en GET vers Paysite, pour récupérer les variables de classe *** */

	function completeData($datasBDD) {

		$paramsGET;
		foreach ($datasBDD as $key => $value) {
			$paramsGET .= $key . '=' . $value . '&';
		}
		$paramsGET .= 'site=' . $this->site . '&wait=' . $this->wait . '&debug=' . $this->debug . '&test=' . $this->test;
		return $paramsGET;
	}

	/*
	 * Si wait = 1 il faut valider manuellement la transaction
	 *  https://billing.paysite-cash.biz/api/transaction_validate.php?id_site=X & key=Y & id_trans=Z
	 *  ==> inutilisée pour l'instant !!
	 */

	function validateTransaction($id_trans) {

		$urlAPIValidate = 'https://billing.paysite-cash.biz/api/transaction_validate.php?id_site=' . $this->site . ' & key=' . $this->apiKey . ' & id_trans=' . $id_trans . '';
		header($urlAPIValidate);
	}

	/*
	 * 	Test le status d'une transaction auprès de Paysite via leur API
	 * 		
	 */

	//type url renvoyée lors d'un test
	//https://billing.paysite-cash.biz/api/transaction_status.php?id_site=7201&key=3941dafe0ef7d6885221ed98be458047&id_trans=PSC-2014-04-6085803&ref=3
	function testStatusTransaction($id_trans, $ref) {
		$isTransExist = false;
		$urlAPITestStatus = 'https://billing.paysite-cash.biz/api/transaction_status.php?id_site=' . $this->site . '&key=' . $this->apiKey . '&id_trans=' . $id_trans . '&ref=' . $ref . '';
		$page = file_get_contents($urlAPITestStatus);

		/*		 * ** recup infos de la page *** */
		//etat=ok&lang=fr&montant=2&devise=EUR&id_client=0&email=gwadaldesign@gmail.com&ip=90.28.129.120&type=0&divers=ressource&date_trans=24/04/2014 01:10:09&id_partner=0&test=1
		$tabParams = explode('&', $page);
		$etat = $tabParams[0];
		$etat = explode('=', $etat);
		$etat = $etat[1]; //valeur de la variable etat

		/*		 * * vérification sur variable etat...on peut contrôler sur d'autres variables pour vérifier exactitude !! *** */
		if ($etat == 'ok' || $etat == 'ko') {
			$isTransExist = true;
		} else if ($html == 'error : transaction not found') {
			$isTransExist = false;
		} else {
			$isTransExist = false;
		}
		return $isTransExist;
	}

}
