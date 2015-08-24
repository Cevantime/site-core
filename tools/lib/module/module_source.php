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
	
	function get_module() {
		return new Git_module($this->url);
	}

}
