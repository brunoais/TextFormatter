<?php

/**
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2013 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator;

use InvalidArgumentException;
use s9e\TextFormatter\Configurator\Collections\TagCollection;
use s9e\TextFormatter\Configurator\Helpers\TemplateChecker;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\Helpers\TemplateOptimizer;
use s9e\TextFormatter\Configurator\Items\Template;
use s9e\TextFormatter\Configurator\Items\UnsafeTemplate;
use s9e\TextFormatter\Configurator\Validators\TagName;

class Stylesheet
{
	/**
	* @var string Output method
	*/
	protected $outputMethod = 'html';

	/**
	* @var TagCollection
	*/
	protected $tags;

	/**
	* @var array Array of wildcard templates, using prefix as key
	*/
	protected $wildcards = array();

	/**
	* Constructor
	*
	* @param  TagCollection $tags Tag collection from which templates are pulled
	* @return void
	*/
	public function __construct(TagCollection $tags)
	{
		$this->tags = $tags;
	}

	/**
	* Get the finalized XSL stylesheet
	*
	* @return string
	*/
	public function get()
	{
		$prefixes  = array();
		$templates = array(
			'br' => '<br/>',
			'st' => '',
			'et' => '',
			'i'  => ''
		);

		// Iterate over the wildcards to collect their templates and their prefix
		foreach ($this->wildcards as $prefix => $template)
		{
			$checkUnsafe = !($template instanceof UnsafeTemplate);

			// First, normalize the template without asserting its safeness
			$template = TemplateHelper::normalizeUnsafe((string) $template);

			// Then, iterate over tags to assess the template's safeness
			if ($checkUnsafe)
			{
				foreach ($this->tags as $tagName => $tag)
				{
					// Ensure that the tag is in the right namespace
					if (strncmp($tagName, $prefix . ':', strlen($prefix) + 1))
					{
						continue;
					}

					// Only check for safeness if the tag has no default template set
					if ($checkUnsafe && !$tag->templates->exists(''))
					{
						TemplateChecker::checkUnsafe($template, $tag);
					}
				}
			}

			// Record the prefix and template
			$prefixes[$prefix] = 1;
			$templates[$prefix . ':*'] = $template;
		}

		// Iterate over the tags to collect their templates and their prefix
		foreach ($this->tags as $tagName => $tag)
		{
			foreach ($tag->templates as $predicate => $template)
			{
				// Build the match rule used by this template
				$match = $tagName;
				if ($predicate !== '')
				{
					// Minify and append this template's predicate
					$match .= '[' . TemplateOptimizer::minifyXPath($predicate) . ']';
				}

				// Record the tag's prefix
				$pos = strpos($tagName, ':');
				if ($pos !== false)
				{
					$prefixes[substr($tagName, 0, $pos)] = 1;
				}

				// Normalize and record the template
				$templates[$match] = ($template instanceof UnsafeTemplate)
				                   ? TemplateHelper::normalizeUnsafe((string) $template, $tag)
				                   : TemplateHelper::normalize((string) $template, $tag);
			}
		}

		// Declare all the namespaces in use at the top
		$xsl = '<?xml version="1.0" encoding="utf-8"?>'
		     . '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"';

		// Append the namespace declarations to the stylesheet
		$prefixes = array_keys($prefixes);
		sort($prefixes);
		foreach ($prefixes as $prefix)
		{
			$xsl .= ' xmlns:' . $prefix . '="urn:s9e:TextFormatter:' . $prefix . '"';
		}

		/**
		* Exclude those prefixes to keep the HTML neat
		*
		* @link http://lenzconsulting.com/namespaces-in-xslt/#exclude-result-prefixes
		*/
		if ($prefixes)
		{
			$xsl .= ' exclude-result-prefixes="' . implode(' ', $prefixes) . '"';
		}

		// Start the stylesheet with the boilerplate stuff
		$xsl .= '><xsl:output method="' . $this->outputMethod . '" encoding="utf-8" indent="no"/>';

		// Group templates by content so we can deduplicate them
		$groupedTemplates = array();
		foreach ($templates as $match => $template)
		{
			$groupedTemplates[$template][] = $match;
		}

		foreach ($groupedTemplates as $template => $matches)
		{
			// Sort the matches, join them and don't forget to escape special chars
			sort($matches);
			$match = htmlspecialchars(implode('|', $matches));

			// Open the template element
			$xsl .= '<xsl:template match="' . $match . '"';

			// Make it a self-closing element if the template is empty
			if ($template === '')
			{
				$xsl .= '/>';
			}
			else
			{
				$xsl .= '>' . $template . '</xsl:template>';
			}
		}

		$xsl .= '</xsl:stylesheet>';

		return $xsl;
	}

	/**
	* Set a wildcard template for given namespace
	*
	* @param  string                     $prefix   Prefix of the namespace this template applies to
	* @param  string|TemplatePlaceholder $template Template's content
	* @return void
	*/
	public function setWildcardTemplate($prefix, $template)
	{
		// Use the tag name validator to validate the prefix
		if (!TagName::isValid($prefix . ':X'))
		{
			throw new InvalidArgumentException("Invalid prefix '" . $prefix . "'");
		}

		if (!is_string($template)
		 && !($template instanceof Template)
		 && !is_callable($template))
		{
			throw new InvalidArgumentException('Argument 2 passed to ' . __METHOD__ . ' must be a string, a valid callback or an instance of Template');
		}

		$this->wildcards[$prefix] = $template;
	}

	/**
	* Set the output method of this stylesheet
	*
	* @param  string $method Either "html" (default) or "xml"
	* @return void
	*/
	public function setOutputMethod($method)
	{
		if ($method !== 'html' && $method !== 'xml')
		{
			throw new InvalidArgumentException('Only html and xml methods are supported');
		}

		$this->outputMethod = $method;
	}
}