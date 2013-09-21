<?php

namespace s9e\TextFormatter\Tests\Configurator\TemplateNormalizations;

use DOMDocument;
use s9e\TextFormatter\Configurator\TemplateNormalizations\Custom;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Configurator\TemplateNormalizations\Custom
*/
class CustomTest extends Test
{
	/**
	* @testdox Constructor expects a valid callback
	* @expectedException PHPUnit_Framework_Error
	* @expectedExceptionMessage Argument 1 passed to s9e\TextFormatter\Configurator\TemplateNormalizations\Custom::__construct() must be callable, string given
	*/
	public function testInvalidCallback()
	{
		new Custom('*invalid*');
	}

	/**
	* @testdox normalize() calls the user-defined callback with a DOMNode as argument
	*/
	public function testNormalize()
	{
		$dom = new DOMDocument;
		$dom->loadXML('<x/>');

		$mock = $this->getMock('stdClass', ['foo']);
		$mock->expects($this->once())
		     ->method('foo')
		     ->with($dom->documentElement);

		$normalizer = new Custom([$mock, 'foo']);
		$normalizer->normalize($dom->documentElement);
	}
}