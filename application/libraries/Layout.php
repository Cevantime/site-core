<?php

/**
 * CodeIgnighter layout support library
 *  with Twig like inheritance blocks
 *
 * v 1.0
 *
 *
 * @author Constantin Bosneaga
 * @email  constantin@bosneaga.com
 * @url    http://a32.me/
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Layout {

	private $obj;
	private $title = '';
	private $metas = array();
	private $css_list = array(), $js_list = array(), $js_script_list = array(), $datas = array();
	private $block_list, $block_new, $block_replace = false;
	private $layout_view;

	function Layout() {
		$this->obj = & get_instance();
		$this->layout_view = 'default';
		// Grab layout from called controller
	}

	function view($view, $data = null, $return = false) {
		// Render template
		if ($data !== null)
			$this->datas = array_merge($this->datas, $data);
		$this->datas['content_for_layout'] = $this->obj->load->view($view, $this->datas, true);
		$this->datas['title_for_layout'] = $this->title;
		$this->datas['meta_for_layout'] = $this->metas;

		// Render resources
		$this->datas['js_for_layout'] = '';
		foreach ($this->js_list as $v)
			$this->datas['js_for_layout'] .= sprintf('<script type="text/javascript" src="%s"></script>', base_url() . $v);

		foreach ($this->js_script_list as $v)
			$this->datas['js_for_layout'] .= sprintf('<script type="text/javascript">%s</script>', $v);

		$this->datas['css_for_layout'] = '';
		foreach ($this->css_list as $v)
			$this->datas['css_for_layout'] .= sprintf('<link rel="stylesheet" type="text/css"  href="%s" />', base_url() . $v);
		// Render template
		$this->block_replace = true;
		$output = $this->obj->load->view($this->layout_view, $this->datas, $return);
		return $output;
	}

	function assign($datas, $value = null) {
		if (is_array($datas)) {
			$this->datas = array_merge($this->datas, $datas);
		} else if ($value !== null && is_string($datas)) {
			$this->datas[$datas] = $value;
		}
	}

	function unassign($key) {
		unset($this->datas[$key]);
	}

	function getAssignedValue($name) {
		if(isset($this->datas[$name])){
			return $this->datas[$name];
		}
		return false;
	}

	/**
	 * Set page metas  
	 *
	 * @param $metasTableau
	 */
	function setMetas($metasTableau) {
		$this->metas = $metasTableau;
	}

	/**
	 * Set page title
	 *
	 * @param $title
	 */
	function title($title) {
		$this->title = $title;
	}

	/**
	 * Adds Javascript resource to current page
	 * @param $item
	 */
	function js($item) {
		$this->js_list[] = $item;
	}

	/**
	 * Adds direct JS script code
	 * @param $code the javascript code to add
	 */
	function jscript($code) {
		$this->js_script_list[] = $code;
	}

	/**
	 * Adds CSS resource to current page
	 * @param $item
	 */
	function css($item) {
		$this->css_list[] = $item;
	}

	/**
	 * Twig like template inheritance
	 *
	 * @param string $name
	 */
	function block($name = '') {
		if ($name != '') {
			$this->block_new = $name;
			ob_start();
		} else {
			if ($this->block_replace) {
				// If block was overriden in template, replace it in layout
				if (!empty($this->block_list[$this->block_new])) {
					ob_end_clean();
					echo $this->block_list[$this->block_new];
				}
			} else {
				$this->block_list[$this->block_new] = ob_get_clean();
			}
		}
	}

	/**
	 * reset the layout view to the one specified in parameter
	 * 
	 * @param string $layoutName The name of the layout to be set
	 */
	public function setLayout($layoutName) {
		$this->layout_view = 'layout/' . $layoutName;
	}

	function addSyntaxHighlighting() {
		$this->css('css/syntaxhighlighter/shCoreRDark.css');
		$this->css('css/syntaxhighlighter/shThemeRDark.css');
		//$this->js('js/jquery-1.9.1.js'); useless since jQuery is already loaded in layout views
		$this->js('js/syntaxhighlighter/scripts/shCore.js');
		$this->js('js/syntaxhighlighter/scripts/shAutoloader.js');
		$baseUrl = base_url();
		$this->jscript("$(document).ready(function () {
				SyntaxHighlighter.autoloader(
					'applescript            " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushAppleScript.js',
					'actionscript3 as3      " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushAS3.js', 
					'bash shell             " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushBash.js', 
					'coldfusion cf          " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushColdFusion.js', 
					'cpp c                  " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushCpp.js', 
					'c# c-sharp csharp      " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushCSharp.js', 
					'css                    " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushCss.js', 
					'delphi pascal          " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushDelphi.js', 
					'diff patch pas         " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushDiff.js', 
					'erl erlang             " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushErlang.js', 
					'groovy                 " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushGroovy.js', 
					'java                   " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushJava.js', 
					'jfx javafx             " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushJavaFX.js', 
					'js jscript javascript  " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushJScript.js', 
					'perl pl                " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushPerl.js', 
					'php                    " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushPhp.js', 
					'text plain             " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushPlain.js', 
					'py python              " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushPython.js', 
					'ruby rails ror rb      " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushRuby.js', 
					'sass scss              " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushSass.js', 
					'scala                  " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushScala.js', 
					'sql                    " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushSql.js', 
					'vb vbnet               " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushVb.js', 
					'xml xhtml xslt html    " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushXml.js'
				);
				SyntaxHighlighter.all();
				SyntaxHighlighter.config.bloggerMode = true;//BloggerMode();
				SyntaxHighlighter.config.stripBrs = false;
			});");
	}

	/**
	 * to use in ajax context when addSyntaxHighLighting has already been called
	 */
	function highlight() {
		$baseUrl = base_url();
		$this->jscript("$(document).ready(function () {
				SyntaxHighlighter.autoloader(
					'applescript            " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushAppleScript.js',
					'actionscript3 as3      " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushAS3.js', 
					'bash shell             " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushBash.js', 
					'coldfusion cf          " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushColdFusion.js', 
					'cpp c                  " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushCpp.js', 
					'c# c-sharp csharp      " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushCSharp.js', 
					'css                    " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushCss.js', 
					'delphi pascal          " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushDelphi.js', 
					'diff patch pas         " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushDiff.js', 
					'erl erlang             " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushErlang.js', 
					'groovy                 " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushGroovy.js', 
					'java                   " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushJava.js', 
					'jfx javafx             " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushJavaFX.js', 
					'js jscript javascript  " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushJScript.js', 
					'perl pl                " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushPerl.js', 
					'php                    " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushPhp.js', 
					'text plain             " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushPlain.js', 
					'py python              " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushPython.js', 
					'ruby rails ror rb      " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushRuby.js', 
					'sass scss              " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushSass.js', 
					'scala                  " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushScala.js', 
					'sql                    " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushSql.js', 
					'vb vbnet               " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushVb.js', 
					'xml xhtml xslt html    " . $baseUrl . "js/syntaxhighlighter/scripts/shBrushXml.js'
				);
				SyntaxHighlighter.all();
			});");
	}

}
