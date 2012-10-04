<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2012 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\BBCodes;

use InvalidArgumentException;
use RuntimeException;
use s9e\TextFormatter\ConfigBuilder\Helpers\RegexpBuilder;
use s9e\TextFormatter\ConfigBuilder\Items\Attribute;
use s9e\TextFormatter\ConfigBuilder\Items\AttributePreprocessor;
use s9e\TextFormatter\ConfigBuilder\Validators\AttributeName;

abstract class BBCodeMonkey
{
	/**
	* Create a BBCode based on its reference usage
	*
	* @param  string $usage BBCode usage, e.g. [B]{TEXT}[/b]
	* @return array
	*/
	public static function parse($usage)
	{
		// This is the config we will return
		$config = array();

		$regexp = '#^'
		        // [BBCODE
		        . '\\[(?<bbcodeName>.+?)'
		        // ={TOKEN}
		        . '(?<defaultAttribute>=.+?)?'
		        // foo={TOKEN} bar={TOKEN1},{TOKEN2}
		        . '(?<attributes>(?:\\s+[^=]+=\\S+?)*)'
		        // ] or /] or ]{TOKEN}[/BBCODE]
		        . '(?:\\s*/?\\]|\\](?<content>\\S+)?(?<endTag>\\[/\\1]))'
		        // (option1=val;option2=val)
		        . '(?:\\s*\\((?<options>(?:[^=]+=[^;]+;?)+)\\))?'
		        . '$#';

		if (!preg_match($regexp, trim($usage), $m))
		{
			throw new InvalidArgumentException('Cannot interpret the BBCode definition');
		}

		// Save the BBCode's name
		$config['bbcodeName'] = BBCode::normalizeName($m['bbcodeName']);

		// Prepare the attributes definition, e.g. "foo={BAR}"
		$attributes = $m['attributes'];

		if (!empty($m['defaultAttribute']))
		{
			// If there's a default attribute, we prepend it to the list using the BBCode's name as
			// attribute name
			$attributes = $m['bbcodeName'] . $m['defaultAttribute'] . $attributes;
		}

		if (!empty($m['content']))
		{
			// Append the content token to the attributes list, under the name "content"
			$attributes .= ' content=' . $m['content'];
		}

		// Parse the attributes' definitions and add it to the config
		$config += self::parseAttributes($attributes);

		print_r($config);
	}

	/**
	* Parse a string of attribute definitions
	*
	* Attributes come in two forms. Most commonly, in the form of a single token, e.g.
	*   [a href={URL} title={TEXT}]
	*
	* Sometimes, however, we need to parse more than one single token. For instance, the phpBB
	* [FLASH] BBCode uses two tokens separated by a comma:
	*   [flash={NUMBER},{NUMBER}]{URL}[/flash]
	*
	* In addition, some custom BBCodes circulating for phpBB use a combination of token and static
	* text such as:
	*   [youtube]http://www.youtube.com/watch?v={SIMPLETEXT}[/youtube]
	*
	* Any attribute that is not a single token is implemented as an attribute preprocessor, with
	* each token generating a matching attribute. Tentatively, those  of those attributes are
	* created by taking the attribute preprocessor's name and appending a unique number counting the
	* number of created attributes. In the [FLASH] example above, an attribute preprocessor named
	* "flash" would be created as well as two attributes named "flash0" and "flash1" respectively.
	*
	* @link https://www.phpbb.com/community/viewtopic.php?f=46&t=2127991
	* @link https://www.phpbb.com/community/viewtopic.php?f=46&t=579376
	*
	* @param  string $str
	* @return array
	*/
	protected static function parseAttributes($str)
	{
		/**
		* @var array
		*/
		$config = array();

		/**
		* @var array 
		*/
		$attributes = array();

		/**
		* @var array
		*/
		$attributePreprocessors = array();

		/**
		* @var array Array of [tokenId => attrName]
		*/
		$tokens = array();

		foreach (preg_split('#\\s+#', trim($str)) as $pair)
		{
			// The name at the left of the equal sign is the key, the rest is the definition. The
			// key will eventually become the name of the attribute or attribute preprocessor
			$key        = trim(substr($pair, 0, strpos($pair, '=')));
			$definition = trim(substr($pair, 1 + strpos($pair, '=')));

			// Now capture the content of every token in that attribute's definition. Usually there
			// will only be one, as in "foo={URL}" but some older BBCodes use a form of composite
			// attributes such as [FLASH={NUMBER},{NUMBER}]
			preg_match_all(
				'#\\{(?<tokenId>(?<tokenType>[A-Z_]+)[0-9]*)(?:=[^}]+)?\\}#',
				$definition,
				$matches,
				PREG_SET_ORDER | PREG_OFFSET_CAPTURE
			);

			if (empty($matches))
			{
				throw new RuntimeException("No tokens found in '" . $key . "' definition");
			}

			// Test whether the definition is a single token and nothing else
			$isComposite = (bool) ($matches[0][0][0] !== $definition);

			// We create the attribute preprocessor's regexp either way
			$regexp  = '#^';
			$lastPos = 0;

			foreach ($matches as $k => $match)
			{
				$tokenId      = $match['tokenId'][0];
				$tokenType    = $match['tokenType'][0];
				$tokenContent = substr($match[0][0], 1, -1);
				$tokenValues  = self::parseToken($tokenContent);

				$isPreprocessor = (bool) ($tokenType === 'PARSE');

				if ($isComposite && $isPreprocessor)
				{
					// Disallow {PARSE} tokens because attribute preprocessors cannot feed into
					// other attribute preprocessors
					throw new RuntimeException('{PARSE} tokens can only be used has the sole content of an attribute');
				}

				// Determine the name of this attribute
				if (isset($tokenValues['attrName']))
				{
					// The name was explicitely given
					$attrName = $tokenValues['attrName'];
					unset($tokenValues['attrName']);
				}
				elseif ($isComposite)
				{
					// The name of the named subpattern and the corresponding attribute is based on
					// the attribute preprocessor's name, with an incremented ID that ensures we
					// don't overwrite existing attributes
					$i = 0;
					do
					{
						$attrName = AttributeName::normalize($key . $i);
						++$i;
					}
					while (isset($attributes[$attrName]));
				}
				else
				{
					// One single token and no name given means we use the key, e.g.
					// "[foo bar={INT}]" will create an attribute named "bar"
					$attrName = $key;
				}

				// Normalize the attribute name (attribute preprocessors follow the same rules)
				$attrName = AttributeName::normalize($attrName);

				// Remove the "useContent" option and add the attribute's name
				if (isset($tokenValues['useContent']))
				{
					if (!empty($tokenValues['useContent']))
					{
						$config['contentAttributes'][] = $attrName;
					}

					unset($tokenValues['useContent']);
				}

				// Set the "required" option if "required" or "optional" is set, then remove
				// the "optional" option
				if (isset($tokenValues['optional']))
				{
					$tokenValues['required'] = !$tokenValues['optional'];
					unset($tokenValues['optional']);
				}
				elseif (isset($tokenValues['required']))
				{
					$tokenValues['required'] = (bool) $tokenValues['required'];
				}

				if ($isPreprocessor)
				{
					$attributePreprocessors[$attrName][]
						= new AttributePreprocessor($tokenValues['regexp']);
				}
				else
				{
					if (isset($attributes[$attrName]))
					{
						throw new RuntimeException("Attribute '" . $attrName . "' is defined twice");
					}

					// Record the attribute's config
					$attributes[$attrName] = self::generateAttribute($tokenType, $tokenValues);

					// Record the token's attribute
					if (isset($tokens[$tokenId]))
					{
						// Set to false if the name is not unique, so that we can remove ambiguous
						// tokens later
						$tokens[$tokenId] = false;
					}
					else
					{
						$tokens[$tokenId] = $attrName;
					}
				}

				// Append the literal text between the last position and current position
				$pos = $match[0][1];
				$regexp .= preg_quote(substr($definition, $lastPos, $pos - $lastPos), '#');

				// Append the named subpattern
				$regexp .= '(?<' . $attrName . '>.+?)';

				// Update the last position
				$lastPos = $pos + strlen($match[0][0]);
			}

			if ($isComposite)
			{
				// Append the literal text that follows the last token and finish the regexp
				$regexp .= preg_quote(substr($definition, $lastPos), '#') . '$#D';

				// Add the attribute preprocessor to the config
				$attributePreprocessors[$key][] = $regexp;
			}
		}

		$config['tokens'] = array_filter($tokens);
		$config['attributes'] = $attributes;
		$config['attributePreprocessors'] = $attributePreprocessors;

		// TODO: look into preprocessors and create the corresponding attributes

		return $config;
	}

	/**
	* Parse the content of a token and return it as a [key => value] array
	*
	* @param  string Token's content, e.g. "INT;optional"
	* @return array
	*/
	protected static function parseToken($tokenContent)
	{
		// This regexp should match a regexp, including its delimiters and optionally the "i" flag
		$regexpMatcher = '(?<regexp>(?<delim>.).*?(?<!\\\\)(?:\\\\\\\\)*(?P=delim)i?)';

		$tokenTypes = array(
			'regexp' => 'REGEXP[0-9]*=' . $regexpMatcher,
			'parse'  => 'PARSE=' . $regexpMatcher,
			'range'  => 'RANGE[0-9]*=(?<min>-?[0-9]+),(?<max>-?[0-9]+)',
			'choice' => 'CHOICE[0-9]*=(?<choices>.+?)',
			'other'  => '[A-Z_]+[0-9]*'
		);

		foreach ($tokenTypes as $regexp)
		{
			// The regexp should match the beginning of the token up till a semicolon or the end of
			// the token
			if (preg_match('#^' . $regexp . '(?:;|$)#', $tokenContent, $m))
			{
				break;
			}
		}

		if (!$m)
		{
			throw new RuntimeException("Cannot parse token '" . $tokenContent . "'");
		}

		$values = array();
		foreach ($m as $k => $v)
		{
			if (!is_numeric($k) && $k !== 'delim')
			{
				$values[$k] = $v;
			}
		}

		if ($m[0] !== $tokenContent)
		{
			// Now capture all key=value pairs that are separated with a semicolon
			$pairs = explode(';', substr($tokenContent, strlen($m[0])));

			foreach ($pairs as $pair)
			{
				$pos = strpos($pair, '=');

				if ($pos === false)
				{
					// Options with no value are set to true, e.g. {FOO;useContent}
					$k = $pair;
					$v = true;
				}
				else
				{
					$k = substr($pair, 0, $pos);
					$v = substr($pair, 1 + $pos);
				}

				$values[$k] = $v;
			}
		}

		return $values;
	}

	/**
	* Generate an attribute based on a token type and options
	*
	* @param  string    $tokenType
	* @param  array     $tokenValues
	* @return Attribute
	*/
	protected static function generateAttribute($tokenType, array $tokenValues)
	{
		$attribute = new Attribute;

		if (isset($tokenValues['preFilter']))
		{
			foreach ($tokenValues['preFilter'] as $filter)
			{
				$attribute->filterChain->append($filter);
			}
			unset($tokenValues['preFilter']);
		}

		if ($tokenType === 'REGEXP')
		{
			$attribute->filterChain->append('#regexp', array('regexp' => $tokenValues['regexp']));
			unset($tokenValues['regexp']);
		}
		elseif ($tokenType === 'RANGE')
		{
			$attribute->filterChain->append('#range', array(
				'min' => $tokenValues['min'],
				'max' => $tokenValues['max']
			));
			unset($tokenValues['min']);
			unset($tokenValues['max']);
		}
		elseif ($tokenType === 'CHOICE')
		{
			// Build a regexp from the list of choices then add a "#regexp" filter
			$regexp = RegexpBuilder::fromList(
				explode(',', $tokenValues['choices']),
				array('specialChars' => array('/' => '\\/'))
			);
			$regexp = '/^' . $regexp . '$/D';

			// Add the case-insensitive flag until specified otherwise
			if (empty($tokenValues['caseSensitive']))
			{
				$regexp .= 'i';
			}

			// Add the Unicode flag if the regexp isn't purely ASCII
			if (preg_match('#[^\\x00-\\x7f]#', $regexp))
			{
				$regexp .= 'u';
			}

			$attribute->filterChain->append('#regexp', array('regexp' => $regexp));
			unset($tokenValues['caseSensitive']);
			unset($tokenValues['choices']);
		}
		elseif ($tokenType !== 'TEXT')
		{
			$attribute->filterChain->append('#' . strtolower($tokenType));
		}

		if (isset($tokenValues['postFilter']))
		{
			foreach ($tokenValues['postFilter'] as $filter)
			{
				$attribute->filterChain->append($filter);
			}
			unset($tokenValues['postFilter']);
		}

		foreach ($tokenValues as $k => $v)
		{
			$attribute->set($k, $v);
		}

		return $attribute;
	}
}