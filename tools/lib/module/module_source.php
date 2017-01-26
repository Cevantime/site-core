<?php
require_once 'module_type.php';
require_once 'module_types/git_module.php';

class Module_source {

    public $url;

    function __construct($url)
    {
        $this->url = $url;
    }

    function get_url()
    {
        return $this->url;
    }
	
	function get_module($module_name, $version) {
		
		$module = new Git_module($module_name, $version, $this->url);
		if($module->exists()) return $module;
		Module_utils::error('ooops. It seems that the module or this version of module doesn\'t exists' );
		return null;
	}

	
}
