<?php

namespace s9e\TextFormatter\Tests\Configurator\TemplateNormalizations;

use DOMDocument;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Tests\Test;

abstract class AbstractTest extends Test
{
	/**
	* @testdox Works
	* @dataProvider getData
	*/
	public function test($template, $expected)
	{
		$xml = '<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">'
		     . $template
		     . '</xsl:template>';

		$dom = new DOMDocument;
		$dom->loadXML($xml);

		$className  = preg_replace(
			'/.*\\\\(.*?)Test$/',
			's9e\\TextFormatter\\Configurator\\TemplateNormalizations\\\\$1',
			get_class($this)
		);

		$normalizer = new $className;
		$normalizer->normalize($dom->documentElement);

		$this->assertSame(
			$expected,
			TemplateHelper::saveTemplate($dom)
		);
	}

	abstract public function getData();
}