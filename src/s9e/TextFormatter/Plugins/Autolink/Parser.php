<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Autolink;

use s9e\TextFormatter\Plugins\ParserBase;

class Parser extends ParserBase
{
	/**
	* {@inheritdoc}
	*/
	public function parse($text, array $matches)
	{
		$tagName  = $this->config['tagName'];
		$attrName = $this->config['attrName'];

		foreach ($matches as $m)
		{
			$url = $m[0][0];

			// Remove trailing punctuation. We preserve right parentheses if there's a balanced
			// number of parentheses in the URL, e.g.
			//   http://en.wikipedia.org/wiki/Mars_(disambiguation) 
			while (1)
			{
				// We remove all Unicode punctuation except dashes (some YouTube URLs end with a
				// dash due to the video ID), equal signs (because of "foo?bar="), trailing slashes,
				// and parentheses, which are balanced separately
				$url = preg_replace('#(?![-=/)])\\pP+$#Du', '', $url);

				if (substr($url, -1) === ')'
				 && substr_count($url, '(') < substr_count($url, ')'))
				{
					$url = substr($url, 0, -1);
					continue;
				}
				break;
			}

			// Create a zero-width start tag right before the URL
			$startTag = $this->parser->addStartTag($tagName, $m[0][1], 0);
			$startTag->setAttribute($attrName, $url);

			// Create a zero-width end tag right after the URL
			$endTag = $this->parser->addEndTag($tagName, $m[0][1] + strlen($url), 0);

			// Pair the tags together
			$startTag->pairWith($endTag);
		}
	}
}