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
        $dependencies = $this->dependencies;
		
		foreach(array('composer', 'spark', 'module') as $type){
			if(isset($dependencies[$type])&& is_array($dependencies[$type])) {
				foreach ($dependencies[$type] as $dependency) {
					$method = 'install_'.$type.'_dependency';
					$this->$method($dependency);
				}
			}
		}

        @mkdir(MODULE_PATH); // Two steps for windows
        @mkdir(MODULE_PATH . "/$this->name");
        Module_utils::full_move($this->temp_path, $this->installation_path);
		file_put_contents($this->installation_path.'/module.version', $this->version);
		
		if(file_exists($this->installation_path.'/dbchanges.sql')){
			$changeLogPath = $this->installation_path.'/dbchanges.sql';
			$changeLogContent = file_get_contents($changeLogPath);
			$changeLogTargetPath = $dbchanges_path = MODULE_PATH.'/../../dbchanges/liquibase/changeLog.sql';
			$changeLogTargetContent = file_get_contents($changeLogTargetPath);
			$changeToAppend = "$changeLogTargetContent\n\n--changeset module:install_module_$this->name\n\n$changeLogContent";
			file_put_contents($changeLogTargetPath, $changeToAppend);
			
			`php dbchanges/liquibase/update.php`;
			unlink($changeLogPath);		}
		if(file_exists($this->installation_path.'/core')) {
			$core_path = MODULE_PATH.'/../core';
			Module_utils::full_move($this->installation_path.'/core', $core_path);
			Module_utils::remove_full_directory($this->installation_path.'/core');
		}
		if(file_exists($this->installation_path.'/js')) {
			$js_path = MODULE_PATH.'/../../js';
			Module_utils::full_move($this->installation_path.'/js', $js_path);
			Module_utils::remove_full_directory($this->installation_path.'/js');
		}
		if(file_exists($this->installation_path.'/css')) {
			$js_path = MODULE_PATH.'/../../css';
			Module_utils::full_move($this->installation_path.'/css', $js_path);
			Module_utils::remove_full_directory($this->installation_path.'/css');
		}
        $this->installed_path = $this->installation_path;
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
                throw new Module_exception("Already installed.  Try `php tools/spark reinstall $this->name`");
            }
            return false;
        }
        else
        {
            return true;
        }
    }

}
