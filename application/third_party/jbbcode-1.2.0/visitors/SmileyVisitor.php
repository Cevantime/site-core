<?php

namespace JBBCode\visitors;

/**
 *  Visiteur permettant de remplacer tous les tags bbCode des smilies par les images
 *  ...inspir� du visiteur de base
 * @author alto971
 * @since October 2013
 */
class SmileyVisitor implements \JBBCode\NodeVisitor {

	private $tableauCode = array(':angel:' => 'angel.png',
		':)' => 'smile.png',
		':angry:' => 'angry.png',
		'8-)' => 'cool.png',
		":'(" => 'cwy.png',
		':ermm:' => 'ermm.png',
		':D' => 'grin.png',
		'<3' => 'heart.png',
		':(' => 'sad.png',
		':O' => 'shocked.png',
		':P' => 'tongue.png',
		';)' => 'wink.png',
		':alien:' => 'alien.png',
		':blink:' => 'blink.png',
		':blush:' => 'blush.png',
		':cheerful:' => 'cheerful.png',
		':devil:' => 'devil.png',
		':dizzy:' => 'dizzy.png',
		':getlost:' => 'getlost.png',
		':happy:' => 'happy.png',
		':kissing:' => 'kissing.png',
		':ninja:' => 'ninja.png',
		':pinch:' => 'pinch.png',
		':pouty:' => 'pouty.png',
		':sick:' => 'sick.png',
		':sideways:' => 'sideways.png',
		':silly:' => 'silly.png',
		':sleeping:' => 'sleeping.png',
		':unsure:' => 'unsure.png',
		':woot:' => 'w00t.png',
		':wassat:' => 'wassat.png',
		':whistling:' => 'whistling.png',
		':love:' => 'wub.png');

	function visitDocumentElement(\JBBCode\DocumentElement $documentElement) {

		foreach ($documentElement->getChildren() as $child) {
			$child->accept($this);
		}
	}

	function visitTextNode(\JBBCode\TextNode $textNode) {
		/* Conversion tag bbcode en image */
		if($textNode->getParent()->getTagName() != 'code'){
			foreach ($this->tableauCode as $codeSmiley => $nomImage) {
				$textNode->setValue(
						str_replace($codeSmiley, 
								'<img src="' . base_url() .'assets/vendor/images/smilies/'. $nomImage . '" alt="' . $codeSmiley . '" />', 
								$textNode->getValue()
								)
						);
			}

		}
	}
	
	function visitElementNode(\JBBCode\ElementNode $elementNode) {
		/* We only want to visit text nodes within elements if the element's
		 * code definition allows for its content to be parsed.
		 */
		if ($elementNode->getCodeDefinition()->parseContent()) {
			foreach ($elementNode->getChildren() as $child) {
				$child->accept($this);
			}
		}
	}

}
