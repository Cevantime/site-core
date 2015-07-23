<?php

/*
 *
 *  Classe test pour impl�menter une balise sp�ciale, avec le parser php
 * 08/10/2013 Alto971
 */

class SpecialCode extends JBBCode\CodeDefinition {

	public function __construct() {
		parent::__construct();
		$this->setTagName("code2 language=java");
		//$this->setAttribute("language");
	}

	public function asHtml(JBBCode\ElementNode $el) {
		$content = "";
		foreach ($el->getChildren() as $child)
			$content .= $child->getAsBBCode();

		echo 'le contenu est ' . $content;
		/* $foundMatch = preg_match('/v=([A-z0-9=\-]+?)(&.*)?$/i', $content, $matches);
		  if(!$foundMatch)
		  return $el->getAsBBCode();
		  else
		  return "<iframe width=\"640\" height=\"390\" src=\"http://www.youtube.com/embed/".$matches[1]."\" frameborder=\"0\" allowfullscreen></iframe>"; */

		return '<b> hello</b>';
	}

}

?>