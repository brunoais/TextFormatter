<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;

use DOMNode;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;

class DisallowCopy extends TemplateCheck
{
	/**
	* Check for <xsl:copy/> elements
	*
	* @param  DOMNode $template <xsl:template/> node
	* @param  Tag     $tag      Tag this template belongs to
	* @return void
	*/
	public function check(DOMNode $template, Tag $tag)
	{
		$nodes = $template->getElementsByTagNameNS('http://www.w3.org/1999/XSL/Transform', 'copy');
		$node  = $nodes->item(0);

		if ($node)
		{
			throw new UnsafeTemplateException("Cannot assess the safety of an '" . $node->nodeName . "' element", $node);
		}
	}
}