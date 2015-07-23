<?php

if (!function_exists('to_stdObject')) {

	function to_stdObject($array, $recursive = false) {
		if (is_array($array)) {
			$object = new stdClass();
			foreach ($array as $key => $value) {
				if ($recursive) {
					$value = to_stdObject($value, true);
				}
				$object->$key = $value;
			}
			return $object;
		}
		return $array;
	}

}