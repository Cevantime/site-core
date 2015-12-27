<?php

// backward compatibility
if ( !function_exists('sys_get_temp_dir'))
{
    function sys_get_temp_dir()
    {
        if ($temp = getenv('TMP')) return $temp;
        if ($temp = getenv('TEMP')) return $temp;
        if ($temp = getenv('TMPDIR')) return $temp;
        $temp = tempnam(__FILE__, '');
        if (file_exists($temp))
        {
          unlink($temp);
          return dirname($temp);
        }
        return '/tmp'; // the best we can do
    }
}

class Core_utils {

    private static $buffer = false;
    private static $lines = array();

    static function get_lines()
    {
        return self::$lines;
    }

    static function buffer()
    {
        self::$buffer = true;
    }

    static function full_move($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::full_move($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    rename($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    static function remove_full_directory($dir, $vocally = false)
    {
        if (is_dir($dir))
        {
            $objects = scandir($dir);
            foreach ($objects as $object)
            {
                if ($object != '.' && $object != '..')
                {
                    if (filetype($dir . '/' . $object) == "dir")
                    {
                        self::remove_full_directory($dir . '/' . $object, $vocally);
                    }
                    else
                    {
                        if ($vocally) self::notice("Removing $dir/$object");
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            return rmdir($dir);
        }
    }

    static function notice($msg)
    {
        self::line($msg, 'MODULE', '[1;36m');
    }

    static function error($msg)
    {
        self::line($msg, 'ERROR', '[1;31m');
    }

    static function warning($msg)
    {
        self::line($msg, 'WARNING', '[1;33m');
    }

    static function line($msg = '', $s = null, $color = null)
    {
        foreach(explode("\n", $msg) as $line)
        {
            if (self::$buffer)
            {
                self::$lines[] = $line;
            }
            else
            {
                echo !$s ? "$line\n" : chr(27) . $color . "[ $s ]" . chr(27) . "[0m" . " $line\n"; 
            }
        }
    }
	
	static function is_os($osname){
		$osname = strtolower($osname);
		$uname = php_uname('s');
		$os_len = strlen($osname);
		if(substr(strtolower($uname), 0,$os_len) === $osname){
			return true;
		}
		return false;
	}
	
	static function list_files($dir){
		$scan = scandir($dir);
		$files = array();
		foreach($scan as $file){
			if($file === '..' || $file === '.' || is_dir($dir.'/'.$file)){
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
	
	static function sed($filename, $pattern, $replace) {
		$file_contents = file_get_contents($filename);
		$rep = preg_replace($pattern, $replace, $file_contents);
		
		if($rep) {
			$new = $rep;
		} else {
			$new = $file_contents;
		}
		file_put_contents($filename, $new);
	}
}
