<?php

/**
 * Description of BBCodeParser
 *
 * @author thibault
 */
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

require_once APPPATH . '/third_party/jbbcode-1.2.0/Parser.php';
require_once APPPATH . '/third_party/jbbcode-1.2.0/SpecialCode.php';
require_once APPPATH . '/third_party/jbbcode-1.2.0/visitors/SmileyVisitor.php';

class BBCodeParser extends JBBCode\Parser {

	public function __construct() {
		parent::__construct();
		$this->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
		
		$builder = new JBBCode\CodeDefinitionBuilder('sectioncode', '<pre class="brush: {option}; toolbar: false; first-line: 1; class-name: \'my_personnal_code\'">{param}</pre>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());

		$builder = new JBBCode\CodeDefinitionBuilder('code', '<pre class="brush: {option}; toolbar: false; first-line: 1; class-name: \'my_personnal_code\'">{param}</pre>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('image', '<img src="{option}" alt="{param}"/>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('size','<span>{param}</span>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('font','<span style="font-family={option}">{param}</span>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('internallink', '<a href="{option}" target="_self">{param}</a>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('legend', '<p class="code-legend">{param}</p>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('externalink', '<a href="{option}" target="_blank">{param}</a>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('zip', '<a href="{option}" target="_blank" class="ziplink resourcelink">{param}</a>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('pdf', '<a href="{option}" target="_blank" class="pdflink resourcelink">{param}</a>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('quote', '<blockquote><cite class="quoteFrom">{option}</cite>{param}</blockquote>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());

		$builder = new JBBCode\CodeDefinitionBuilder('becareful', '<becareful class="alert alert-warning">{param}</becareful>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('info', '<info class="alert alert-info">{param}</info>');
		$this->addCodeDefinition($builder->build());

		$builder = new JBBCode\CodeDefinitionBuilder('retourligne', '<br/>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('left', '<div align="left">{param}</div>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('leftedCode', '<div class="code-left">{param}</div>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('center', '<div align="center">{param}</div>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('justify', '<div align="justify">{param}</div>');
		$this->addCodeDefinition($builder->build());

		/*		 * ********** la suite est non implémentée à ce jour côté javascript !! ** */
		$builder = new JBBCode\CodeDefinitionBuilder('section1', '<h2 class="section" id="{option}">{param}</h1>');
		//$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());

		$builder = new JBBCode\CodeDefinitionBuilder('section2', '<h3 class="section" >{param}</h2>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('section3', '<h4 class="section" >{param}</h2>');
		$this->addCodeDefinition($builder->build());

		$builder = new JBBCode\CodeDefinitionBuilder('p', '<p>{param}</p>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('ol', '<ol>{param}</ol>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('list', '<ul>{param}</ul>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('list', '<ol>{param}</ol>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('*', '<li>{param}</li>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('youtube', '<iframe width="560" height="315" src="{param}" frameborder="0" allowfullscreen></iframe>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('video', '<iframe id="player" type="text/html" \n\
                width="560" height="315" src="http://www.youtube.com/embed/{param}?enablejsapi=1" \n\
                frameborder="0"></iframe>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('a', '<a href="{option}" target="_blank">{param}</a>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('table', '<table class="{option}">{param}</table>');
		$builder->setUseOption(true);
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('td', '<td>{param}</td>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('tr', '<tr>{param}</tr>');
		$this->addCodeDefinition($builder->build());
		
		$builder = new JBBCode\CodeDefinitionBuilder('br', '<br/>');
		$this->addCodeDefinition($builder->build());
	}

	public function parse($str) {
		$str = str_replace("\t", "    ", $str);
		$str = str_replace("\r\n", '[br][/br]', $str);
		$str = html_escape($str);
		parent::parse($str);
		$treeRoot = &$this->treeRoot;
		$i = 0;
		$chren = $treeRoot->getChildren();
		$children = &$chren;
		foreach ($children as &$child) {
			if ($child instanceof \JBBCode\ElementNode && $child->getTagName() == 'section1') {
				$child->setAttribute('tuto-section-' . $i++);
			}
		}
		/*		 * * on convertit les smilies ** */
		$smileyVisitor = new \JBBCode\visitors\SmileyVisitor();
		$this->accept($smileyVisitor);
	}

	public function getTreeRoot() {
		return $this->treeRoot;
	}

	public function convertToHtml(&$content) {
		$this->parse($content);
		$content = $this->getAsHTML();
	}

	public function convertToText(&$content) {
		$this->parse($content);
		$content = $this->getAsText();
	}

	public function convertToBBCode(&$content) {
		$this->parse($content);
		$content = $this->getAsBBCode();
	}

	/*	 * ** fonction qui permet de convertir le html re�u en BBCode ** */
	/*	 * * utile pour changement de mode : passage en mode bbCode * */

	public function decode($texte) {
		$test = 'test';
		//$texte = nl2br($texte);  
		//$texte = str_replace("\r\n", '[br][/br]', $texte);
		//$class = "brush: java; toolbar: false; first-line: 1; class-name: \'my_personnal_code\'";

		$html = array("<strong>", "</strong>", "<b>", "</b>", "<em>", "</em>", "<i>", "</i>", "<u>", "</u>",
			"<br>", "<br />", "\r\n",
			"<p>", "</p>", '<h2 class="section">', "</h2>", '<h1 class="section">', "</h1>",
			"</pre>", '<pre class="brush: java; toolbar: false; first-line: 1; class-name: \'my_personnal_code\'">');
		$bbcode = array("[b]", "[/b]", "[b]", "[/b]", "[i]", "[/i]", "[i]", "[/i]", "[u]", "[/u]",
			"[br][/br]", "[br]", "[br][/br]",
			"[p]", "[/p]", "[section2]", "[/section2]", "[section]", "[/section]",
			"[/code]", "[code=java]");
		$texte = str_replace($html, $bbcode, $texte);

		/*		 * ***** balise sp�ciales avec attribut ****** */
		//$texte = preg_replace('#<a href="(.+)" target="_blank">#i', '[url=$1]', $texte); //oui si un seul lien !!
		//$texte = preg_replace("/<a href(.*?)<\/a>/si", "", $texte);//remplace tous les liens par du vide !
		//$texte = preg_replace('#<a href="(.*?)">#i', '[url=$1]', $texte); //ok
		//$texte = preg_replace('#</a>#i', '[/url]', $texte); //ok
		$texte = preg_replace('#<a href="(.*?)">#i', '[advancedlink=$1]', $texte); //ok
		$texte = preg_replace('#</a>#i', '[/advancedlink]', $texte); //ok		

		/*		 * * oui si une seule balise de ce type *** */
		//$texte = preg_replace('#<h1 class="section">(.+)</h1>#i', '[section]$1[/section]', $texte); //Regexp transformant les titre h1 en lien BBCode.
		//$texte = preg_replace('#<h2>(.+)</h2>#i', '[section2]$1[/section2]', $texte); //Regexp transformant les titre h2 en lien BBCode.
		//$texte = preg_replace('#<h1>(.+)</h1>#i', '[section]$1[/section]', $texte); //Regexp transformant les titre h1 en lien BBCode.
		//$texte = preg_replace('#<h2 class="section">(.+)</h2>#i', '[section2]$1[/section2]', $texte); //Regexp transformant les titre h2 en lien BBCode.
		//$texte = preg_replace('#<p>(.+)</p>#i', '[p]$1[/p]', $texte); //Regexp transformant les paragraphes p en lien BBCode.
		//$texte = preg_replace('#<pre class="(.+)">(.+)</pre>#i', '[code]$2[/code]', $texte); //Regexp transformant les titre h2 en lien BBCode.
		//
	 //$class = "brush: java; toolbar: false; first-line: 1; class-name: \'my_personnal_code\'";
		//$texte = preg_replace('#<pre class="'.$class.'">(.+)</pre>#i', '[code=java]$1[/code]', $texte); //Regexp transformant les balises de code en lien BBCode.
		//$texte = preg_replace('#<pre class="(.+)">(.+)</pre>#i', '[code=java]$2[/code]', $texte); //Regexp transformant les balises de code en lien BBCode.
		return $texte;
	}

}

?>
