<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Generic;

use s9e\TextFormatter\Plugins\ParserBase;

class Parser extends ParserBase
{
	/**
	* {@inheritdoc}
	*/
	public function parse($text, array $matches)
	{
		foreach ($this->config['generics'] as $generic)
		{
			list($tagName, $regexp) = $generic;
			preg_match_all($regexp, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

			foreach ($matches as $m)
			{
				$tag = $this->parser->addSelfClosingTag($tagName, $m[0][1], strlen($m[0][0]));

				foreach ($m as $k => $v)
				{
					if (!is_numeric($k))
					{
						$tag->setAttribute($k, $v[0]);
					}
				}
			}
		}
	}
}