<?php

namespace JBBCode\codedefinitions;

class FileCodeDefinition extends JBBCode\CodeDefinition {
	public function __construct() {
		parent::__construct();
		$this->tagName = 'file';
	}
	
	public function asHtml(\JBBCode\ElementNode $el) {
		$content = '';
		foreach ($this->getChildren() as $child) {
			$content .= $child->getAsBBCode();
		}
		if($el->getAttribute() == 'image') {
			return '<img src="'.$content.'"/>';
		}
	}
}

