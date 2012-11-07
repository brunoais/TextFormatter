<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2012 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\Generic;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use s9e\TextFormatter\Configurator\Collections\NormalizedCollection;
use s9e\TextFormatter\Configurator\Helpers\RegexpParser;
use s9e\TextFormatter\Plugins\ConfiguratorBase;

/**
* NOTE: does not support duplicate named captures
*/
class Configurator extends ConfiguratorBase
{
	/**
	* @var NormalizedCollection
	*/
	protected $collection;

	/**
	* {@inheritdoc}
	*/
	public function setUp()
	{
		$this->collection = new NormalizedCollection;
	}

	/**
	* Add a generic replacement
	*
	* @param  string $regexp   Regexp to be used by the parser
	* @param  string $template Template to be used for rendering
	* @return string           The name of the tag created to represent this replacement
	*/
	public function add($regexp, $template)
	{
		$valid = false;

		try
		{
			$valid = @preg_match_all($regexp, '', $m);
		}
		catch (Exception $e)
		{
		}

		if ($valid === false)
		{
			throw new InvalidArgumentException('Invalid regexp');
		}

		// Generate a tag name based on the regexp
		$tagName = sprintf('G%08X', crc32($regexp));

		// Create the tag that will represent the regexp
		$tag = $this->configurator->tags->add($tagName);

		// Parse the regexp, and generate an attribute for every named capture
		$regexpInfo = RegexpParser::parse($regexp);

		foreach ($regexpInfo['tokens'] as $tok)
		{
			if ($tok['type'] === 'capturingSubpatternStart'
			 && isset($tok['name']))
			{
				$attrName = $tok['name'];

				if (isset($tag->attributes[$attrName]))
				{
					throw new RuntimeException('Duplicate named subpatterns are not allowed');
				}

				$lpos = $tok['pos'];
				$rpos = $regexpInfo['tokens'][$tok['endToken']]['pos']
				      + $regexpInfo['tokens'][$tok['endToken']]['len'];

				$attrRegexp = $regexpInfo['delimiter']
				            . '^' . substr($regexpInfo['regexp'], $lpos, $rpos - $lpos) . '$'
				            . $regexpInfo['delimiter']
				            . str_replace('D', '', $regexpInfo['modifiers'])
				            . 'D';

				$attribute = $tag->attributes->add($attrName);

				$attribute->required = true;
				$attribute->filterChain->append('#regexp', array('regexp' => $attrRegexp));
			}
		}

		// Now that all attributes have been created we can assign the template
		$tag->defaultTemplate = $template;

		// Finally, record the regexp
		$this->collection[$tagName] = $regexp;

		return $tagName;
	}

	/**
	* {@inheritdoc}
	*/
	public function asConfig()
	{
		if (!count($this->collection))
		{
			return false;
		}

		return array('regexp' => $this->collection->asConfig());
	}
}