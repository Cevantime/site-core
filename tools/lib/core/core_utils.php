<?php

// backward compatibility
if (!function_exists('sys_get_temp_dir')) {

	function sys_get_temp_dir() {
		if ($temp = getenv('TMP'))
			return $temp;
		if ($temp = getenv('TEMP'))
			return $temp;
		if ($temp = getenv('TMPDIR'))
			return $temp;
		$temp = tempnam(__FILE__, '');
		if (file_exists($temp)) {
			unlink($temp);
			return dirname($temp);
		}
		return '/tmp'; // the best we can do
	}

}

class Core_utils {

	private static $buffer = false;
	private static $lines = array();

	static function get_lines() {
		return self::$lines;
	}

	static function buffer() {
		self::$buffer = true;
	}

	static function full_move($src, $dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src . '/' . $file)) {
					self::full_move($src . '/' . $file, $dst . '/' . $file);
				} else {
					rename($src . '/' . $file, $dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

	static function full_copy($src, $dst) {
		if (is_dir($source)) {
			$dir_handle = opendir($source);
			while ($file = readdir($dir_handle)) {
				if ($file != "." && $file != "..") {
					if (is_dir($source . "/" . $file)) {
						if (!is_dir($dest . "/" . $file)) {
							mkdir($dest . "/" . $file);
						}
						self::full_copy($source . "/" . $file, $dest . "/" . $file);
					} else {
						copy($source . "/" . $file, $dest . "/" . $file);
					}
				}
			}
			closedir($dir_handle);
		} else {
			copy($source, $dest);
		}
	}

	static function list_files_and_directories($dir) {
		$scan = scandir($dir);
		$files = array();
		foreach ($scan as $file) {
			if ($file === '..' || $file === '.') {
				continue;
			}
			$files[] = $file;
		}
		return $files;
	}

	static function remove_full_directory($dir, $vocally = false) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != '.' && $object != '..') {
					if (filetype($dir . '/' . $object) == "dir") {
						self::remove_full_directory($dir . '/' . $object, $vocally);
					} else {
						if ($vocally)
							self::notice("Removing $dir/$object");
						unlink($dir . '/' . $object);
					}
				}
			}
			reset($objects);
			return rmdir($dir);
		}
	}

	static function notice($msg) {
		self::line($msg, 'MODULE', '[1;36m');
	}

	static function error($msg) {
		self::line($msg, 'ERROR', '[1;31m');
	}

	static function warning($msg) {
		self::line($msg, 'WARNING', '[1;33m');
	}

	static function line($msg = '', $s = null, $color = null) {
		foreach (explode("\n", $msg) as $line) {
			if (self::$buffer) {
				self::$lines[] = $line;
			} else {
				echo!$s ? "$line\n" : chr(27) . $color . "[ $s ]" . chr(27) . "[0m" . " $line\n";
			}
		}
	}

	static function is_os($osname) {
		$osname = strtolower($osname);
		$uname = php_uname('s');
		$os_len = strlen($osname);
		if (substr(strtolower($uname), 0, $os_len) === $osname) {
			return true;
		}
		return false;
	}

	static function os_sep() {
		return Core_utils::is_os('linux') || Module_utils::is_os('mac') ? ';' : '&';
	}

	static function list_files($dir) {
		$scan = scandir($dir);
		$files = array();
		foreach ($scan as $file) {
			if ($file === '..' || $file === '.' || is_dir($dir . '/' . $file)) {
				continue;
			}
			$files[] = $file;
		}
		return $files;
	}

	static function scan($label) {
		$input = readline($label);
		readline_add_history($input);
		return $input;
	}

	static function scan_silent($label) {
		if (self::is_os('windows')) {
			return self::scan($label);
		}
		if (preg_match('/^win/i', PHP_OS)) {

			// for windows xp and windows 2003
			$vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
			file_put_contents(
					$vbscript, 'wscript.echo(InputBox("'
					. addslashes($label)
					. '", "", "password here"))');
			$command = "cscript //nologo " . escapeshellarg($vbscript);
			$password = rtrim(shell_exec($command));
			unlink($vbscript);
			return $password;
		} else {
			$command = "/usr/bin/env bash -c 'echo OK'";
			if (rtrim(shell_exec($command)) !== 'OK') {
				trigger_error("Can't invoke bash");
				return;
			}
			$command = "/usr/bin/env bash -c 'read -s -p \""
					. addslashes($label)
					. "\" mypassword && echo \$mypassword'";
			$password = rtrim(shell_exec($command));
			echo "\n";
			return $password;
		}
	}

	static function sed($filename, $pattern, $replace) {
		$file_contents = file_get_contents(realpath($filename));
		$rep = preg_replace($pattern, $replace, $file_contents);

		if ($rep) {
			$new = $rep;
		} else {
			$new = $file_contents;
		}
		file_put_contents($filename, $new);
	}

}
