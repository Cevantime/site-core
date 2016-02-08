<?php

class Module_type {

    function __construct($name, $version, $base_location)
    {
		
        
        $this->name = $name;
        $this->base_location = $base_location;
        $this->version = $version;

        // used internally
        $this->temp_token = 'module-' . $this->name . '-' . time();
        $this->temp_path = sys_get_temp_dir() . '/' . $this->temp_token;
    }

    final function installed_path()
    {
        return $this->installed_path;
    }

    function location_detail() { }
    function retrieve() { }
	
	function exists(){return false;}

    function install()
    {
		$module = array();
		if(file_exists("$this->temp_path/module.json")) {
			$module = @json_decode(@file_get_contents("$this->temp_path/module.json"), true);
			$dependencies = isset($module['dependencies']) && is_array($module['dependencies']) ? $module['dependencies'] : array();
		}
        else if (file_exists("$this->temp_path/dependencies.json")) {
            $dependencies = @json_decode(@file_get_contents("$this->temp_path/dependencies.json"), true);
        } else {
            $dependencies = array();
        }
		if (file_exists("$this->temp_path/dependencies.json")) unlink("$this->temp_path/dependencies.json");
		foreach(array('composer', 'spark', 'module') as $type){
			if(isset($dependencies[$type])&& is_array($dependencies[$type])) {
				foreach ($dependencies[$type] as $dependency) {
					$method = 'install_'.$type.'_dependency';
					$this->$method($dependency);
				}
			}
		}
		$module['dependencies'] = $dependencies;
		$map = array();
		$module['map'] = $map;
        @mkdir(MODULE_PATH); // Two steps for windows
        @mkdir(MODULE_PATH . "/$this->name");
        Module_utils::full_move($this->temp_path, $this->installation_path);
		
		if(file_exists($this->installation_path.'/prebuild.php')) {
			`php $this->installation_path/prebuild.php`;
			unlink($this->installation_path.'/prebuild.php');
		}
		
		if(file_exists($this->installation_path.'/dbchanges')){
			Module_utils::line('installing dbchanges');
			$dbchangesPath = $this->installation_path.'/dbchanges';
			$changeToAppend = '';
			$files = Module_utils::list_files($dbchangesPath);
			$changeLogTargetPath = $dbchanges_path = MODULE_PATH.'/../../dbchanges/liquibase/changeLog.sql';
			$changeLogTargetContent = file_get_contents($changeLogTargetPath);
			foreach($files as $file){
				$module['map']['dbchanges'][] = $file;
				$filename = basename($file,'.sql');
				$prefix = "--changeset module:install_{$this->name}_$filename\n";
				if(strpos($changeLogTargetContent, $prefix) !== false){
					continue;
				}
				$suffix = "\n";
				$changeToAppend .= $prefix.file_get_contents($dbchangesPath.'/'.$file).$suffix;
			}
			$changeToAppend = $changeLogTargetContent ."\n". $changeToAppend;
			file_put_contents($changeLogTargetPath, $changeToAppend);
			
			`php dbchanges/liquibase/update.php`;
			Module_utils::remove_full_directory($dbchangesPath);	
		}
		if(file_exists($this->installation_path.'/core')) {
			$files = Module_utils::list_files($this->installation_path.'/core');
			foreach ($files as $file){
				$module['map']['core'][] = $file;
			}
			$core_path = MODULE_PATH.'/../core';
			Module_utils::full_move($this->installation_path.'/core', $core_path);
			Module_utils::remove_full_directory($this->installation_path.'/core');
		}
		if(file_exists($this->installation_path.'/js')) {
			$this->put_in_module_map($this->installation_path.'/js', $module['map']);
			$js_path = MODULE_PATH.'/../../js';
			Module_utils::full_move($this->installation_path.'/js', $js_path);
			Module_utils::remove_full_directory($this->installation_path.'/js');
		}
		if(file_exists($this->installation_path.'/css')) {
			$this->put_in_module_map($this->installation_path.'/css', $module['map']);
			$css_path = MODULE_PATH.'/../../css';
			Module_utils::full_move($this->installation_path.'/css', $css_path);
			Module_utils::remove_full_directory($this->installation_path.'/css');
		}
		$module['version'] = $this->version;
		file_put_contents($this->installation_path.'/module.json', json_encode($module,JSON_PRETTY_PRINT));
        $this->installed_path = $this->installation_path;
		if(file_exists($this->installation_path.'/postbuild.php')) {
			`php $this->installation_path/postbuild.php`;
			unlink($this->installation_path.'/postbuild.php');
		}
    }
	
	private function put_in_module_map($filename, &$module_map){
		$basename = basename($filename);
		if($basename === '.' || $basename === '..') return;
		if(is_dir($filename)){
			$module_map[$basename] = array();
			$files = scandir($filename);
			foreach($files as $file){
				$this->put_in_module_map($filename.'/'.$file, $module_map[$basename]);
			}
		} else {
			$module_map[] = $basename;
		}
	}
	
	private function install_composer_dependency($dependency) {
		$module = $dependency['name'];
		$version = $dependency['version'];
		Module_utils::line('installing composer dependency : '.$module);
		$composer_json = json_decode(file_get_contents(MODULE_PATH.'/../composer.json'), true);
		$composer_json['require'][$module] = $version;
		file_put_contents(MODULE_PATH.'/../composer.json', json_encode($composer_json, JSON_PRETTY_PRINT));
		
		`php tools/composer --working-dir=application/ update`;
	}
	private function install_spark_dependency($dependency) {
		$spark = $dependency['name'];
		$version = $dependency['version'];
		Module_utils::line('installing spark dependency : '.$spark);
		`php tools/spark reinstall -v$version $spark`;
		
	}
	private function install_module_dependency($dependency) {
		$module = $dependency['name'];
		$version = $dependency['version'];
		Module_utils::line('installing module dependency : '.$module);
		`php tools/module reinstall -v$version $module`;
	}
	
	public function version() {
		return $this->version;
	}

    function verify($break_on_already_installed = true)
    {
        
        // tell the user if its already installed and throw an error
        $this->installation_path = MODULE_PATH . "/$this->name";
        if (is_dir($this->installation_path))
        {
            if ($break_on_already_installed)
            {
                throw new Module_exception("Already installed.  Try `php tools/module reinstall $this->name`");
            }
            return false;
        }
        else
        {
            return true;
        }
    }

}
