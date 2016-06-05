<?php 
if (!function_exists('zero_date')) {

	function zero_date($time) {
		$now = time();
		$diff = $now - $time;
		$dayOfWeek = day_of_week($time);
		if ($diff < 180) {
			return translate('il y a moins de 2 minutes');
		}
		if ($diff < 3600) {
			return translate('il y a').' '. (intval($diff / 60)) . ' '.translate('minutes');
		}
		if ($diff < 86400) { //moins d'une journée
			$day = translate('aujourd\'hui');
			if ($dayOfWeek != day_of_week($now)) {
				$day = translate('hier');
			}
			return $day . ' '.translate('à').' ' . date('H\hi', $time);
		}
		if ($diff < 172800) { // moins de deux jours
			$day = 'hier';
			if (abs(date('w', $time) - date('w', $now)) == 2) {
				$day = translate('avant-hier');
			}
			return $day . ' '.translate('à').' ' . date('H\hi', $time);
		}
		if ($diff < 604800) { // moins d'une semaine
			return $dayOfWeek . ' '.translate('à').' ' . date('H\hi', $time);
		}
		// plus d'une semaine
		return translate('le').' ' . date('d/m/Y', $time);
	}

}

if (!function_exists('day_of_week')) {

	function day_of_week($timestamp) {
		$n = date('w', $timestamp);
		switch ($n) {
			case 0 : return translate('dimanche');
			case 1 : return translate('lundi');
			case 2 : return translate('mardi');
			case 3 : return translate('mercredi');
			case 4 : return translate('jeudi');
			case 5 : return translate('vendredi');
			case 6 : return translate('samedi');
		}
	}

}
if (!function_exists('month')) {

	function month($timestamp = null) {
		if($timestamp === null) {
			$timestamp = time();
		}
		$n = intval(date('m', $timestamp));
		switch ($n) {
			case 1 : return translate('janvier');
			case 2 : return translate('février');
			case 3 : return translate('mars');
			case 4 : return translate('aril');
			case 5 : return translate('mai');
			case 6 : return translate('juin');
			case 7 : return translate('juillet');
			case 8 : return translate('août');
			case 9 : return translate('septembre');
			case 10 : return translate('octobre');
			case 11 : return translate('novembre');
			case 12 : return translate('décembre');
		}
	}

}

?>