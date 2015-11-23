<?php 
if (!function_exists('zero_date')) {

	function zero_date($time) {
		$now = time();
		$diff = $now - $time;
		$dayOfWeek = day_of_week($time);
		if ($diff < 180) {
			return 'il y a moins de 2 minutes';
		}
		if ($diff < 3600) {
			return 'il y a ' . (intval($diff / 60)) . ' minutes';
		}
		if ($diff < 86400) { //moins d'une journée
			$day = 'aujourd\'hui';
			if ($dayOfWeek != day_of_week($now)) {
				$day = 'hier';
			}
			return $day . ' à ' . date('H\hi', $time);
		}
		if ($diff < 172800) { // moins de deux jours
			$day = 'hier';
			if (abs(date('w', $time) - date('w', $now)) == 2) {
				$day = 'avant-hier';
			}
			return $day . ' à ' . date('H\hi', $time);
		}
		if ($diff < 604800) { // moins d'une semaine
			return $dayOfWeek . ' à ' . date('H\hi', $time);
		}
		// plus d'une semaine
		return 'le ' . date('d/m/Y', $time);
	}

}

if (!function_exists('day_of_week')) {

	function day_of_week($timestamp) {
		$n = date('w', $timestamp);
		switch ($n) {
			case 0 : return 'dimanche';
			case 1 : return 'lundi';
			case 2 : return 'mardi';
			case 3 : return 'mercredi';
			case 4 : return 'jeudi';
			case 5 : return 'vendredi';
			case 6 : return 'samedi';
		}
	}

}

?>