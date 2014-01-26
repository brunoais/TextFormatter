<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2014 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;

use DOMNode;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\TemplateCheck;

class DisallowDynamicElementNames extends TemplateCheck
{
	/**
	* Test for the presence of an <xsl:element/> node using a dynamic name
	*
	* @param  DOMNode $template <xsl:template/> node
	* @param  Tag     $tag      Tag this template belongs to
	* @return void
	*/
	public function check(DOMNode $template, Tag $tag)
	{
		$nodes = $template->getElementsByTagNameNS(
			'http://www.w3.org/1999/XSL/Transform',
			'element'
		);

		foreach ($nodes as $node)
		{
			if (strpos($node->getAttribute('name'), '{') !== false)
			{
				throw new UnsafeTemplateException('Dynamic <xsl:element/> names are disallowed', $node);
			}
		}
	}
}