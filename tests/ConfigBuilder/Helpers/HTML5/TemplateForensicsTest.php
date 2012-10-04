<?php

namespace s9e\TextFormatter\Tests\ConfigBuilder\Helpers\HTML5;

use s9e\TextFormatter\Tests\Test;
use s9e\TextFormatter\ConfigBuilder\Helpers\HTML5\TemplateForensics;

/**
* @covers s9e\TextFormatter\ConfigBuilder\Helpers\HTML5\TemplateForensics
*/
class TemplateForensicsTest extends Test
{
	public function runCase($title, $xslSrc, $rule, $xslTrg)
	{
		$st = '<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">';
		$et = '</xsl:template>';

		$src = new TemplateForensics($st . $xslSrc . $et);
		$trg = new TemplateForensics($st . $xslTrg . $et);

		$methods = array(
			'allowChild'      => array('assertTrue',  'allowsChild'),
			'allowDescendant' => array('assertTrue',  'allowsDescendant'),
			'denyChild'       => array('assertFalse', 'allowsChild'),
			'denyDescendant'  => array('assertFalse', 'allowsDescendant'),
			'closeParent'     => array('assertTrue',  'closesParent'),
			'!closeParent'    => array('assertFalse', 'closesParent'),
			'isTransparent'   => array('assertTrue',  'isTransparent'),
			'!isTransparent'  => array('assertFalse', 'isTransparent'),
		);

		list($assert, $method) = $methods[$rule];

		$this->$assert($src->$method($trg));
	}

	// Start of content generated by ../../../../scripts/patchTemplateForensicsTest.php
	/**
	* @testdox <span> does not allow <div> as child
	*/
	public function testD335F821()
	{
		$this->runCase(
			'<span> does not allow <div> as child',
			'<span><xsl:apply-templates/></span>',
			'denyChild',
			'<div><xsl:apply-templates/></div>'
		);
	}

	/**
	* @testdox <span> does not allow <div> as child even with a <span> sibling
	*/
	public function test114C6685()
	{
		$this->runCase(
			'<span> does not allow <div> as child even with a <span> sibling',
			'<span><xsl:apply-templates/></span>',
			'denyChild',
			'<span>xxx</span><div><xsl:apply-templates/></div>'
		);
	}

	/**
	* @testdox <span> and <div> does not allow <span> and <div> as child
	*/
	public function testE416F9F5()
	{
		$this->runCase(
			'<span> and <div> does not allow <span> and <div> as child',
			'<span><xsl:apply-templates/></span><div><xsl:apply-templates/></div>',
			'denyChild',
			'<span/><div/>'
		);
	}

	/**
	* @testdox <li> closes parent <li>
	*/
	public function test93A27904()
	{
		$this->runCase(
			'<li> closes parent <li>',
			'<li/>',
			'closeParent',
			'<li><xsl:apply-templates/></li>'
		);
	}

	/**
	* @testdox <div> closes parent <p>
	*/
	public function test1D189E22()
	{
		$this->runCase(
			'<div> closes parent <p>',
			'<div/>',
			'closeParent',
			'<p><xsl:apply-templates/></p>'
		);
	}

	/**
	* @testdox <p> closes parent <p>
	*/
	public function test94ADCE2C()
	{
		$this->runCase(
			'<p> closes parent <p>',
			'<p/>',
			'closeParent',
			'<p><xsl:apply-templates/></p>'
		);
	}

	/**
	* @testdox <div> does not close parent <div>
	*/
	public function test80EA2E75()
	{
		$this->runCase(
			'<div> does not close parent <div>',
			'<div/>',
			'!closeParent',
			'<div><xsl:apply-templates/></div>'
		);
	}

	/**
	* @testdox <span> does not close parent <span>
	*/
	public function test576AB9F1()
	{
		$this->runCase(
			'<span> does not close parent <span>',
			'<span/>',
			'!closeParent',
			'<span><xsl:apply-templates/></span>'
		);
	}

	/**
	* @testdox <a> denies <a> as descendant
	*/
	public function test176B9DB6()
	{
		$this->runCase(
			'<a> denies <a> as descendant',
			'<a><xsl:apply-templates/></a>',
			'denyDescendant',
			'<a/>'
		);
	}

	/**
	* @testdox <a> allows <img> with no usemap attribute as child
	*/
	public function testFF711579()
	{
		$this->runCase(
			'<a> allows <img> with no usemap attribute as child',
			'<a><xsl:apply-templates/></a>',
			'allowChild',
			'<img/>'
		);
	}

	/**
	* @testdox <a> denies <img usemap="#foo"> as child
	*/
	public function testF13726A8()
	{
		$this->runCase(
			'<a> denies <img usemap="#foo"> as child',
			'<a><xsl:apply-templates/></a>',
			'denyChild',
			'<img usemap="#foo"/>'
		);
	}

	/**
	* @testdox <div><a> allows <div> as child
	*/
	public function test0266A932()
	{
		$this->runCase(
			'<div><a> allows <div> as child',
			'<div><a><xsl:apply-templates/></a></div>',
			'allowChild',
			'<div/>'
		);
	}

	/**
	* @testdox <span><a> denies <div> as child
	*/
	public function test8E52F053()
	{
		$this->runCase(
			'<span><a> denies <div> as child',
			'<span><a><xsl:apply-templates/></a></span>',
			'denyChild',
			'<div/>'
		);
	}

	/**
	* @testdox <audio> with no src attribute allows <source> as child
	*/
	public function test3B294484()
	{
		$this->runCase(
			'<audio> with no src attribute allows <source> as child',
			'<audio><xsl:apply-templates/></audio>',
			'allowChild',
			'<source/>'
		);
	}

	/**
	* @testdox <audio src="..."> denies <source> as child
	*/
	public function testE990B9F2()
	{
		$this->runCase(
			'<audio src="..."> denies <source> as child',
			'<audio src="{@src}"><xsl:apply-templates/></audio>',
			'denyChild',
			'<source/>'
		);
	}

	/**
	* @testdox <a> is considered transparent
	*/
	public function testA57E3A26()
	{
		$this->runCase(
			'<a> is considered transparent',
			'<a><xsl:apply-templates/></a>',
			'isTransparent',
			NULL
		);
	}

	/**
	* @testdox <a><span> is not considered transparent
	*/
	public function test6D41EE34()
	{
		$this->runCase(
			'<a><span> is not considered transparent',
			'<a><span><xsl:apply-templates/></span></a>',
			'!isTransparent',
			NULL
		);
	}

	/**
	* @testdox <span><a> is not considered transparent
	*/
	public function testD1D36C1C()
	{
		$this->runCase(
			'<span><a> is not considered transparent',
			'<span><a><xsl:apply-templates/></a></span>',
			'!isTransparent',
			NULL
		);
	}

	/**
	* @testdox A template composed entirely of a single <xsl:apply-templates/> is considered transparent
	*/
	public function test91DA5BEA()
	{
		$this->runCase(
			'A template composed entirely of a single <xsl:apply-templates/> is considered transparent',
			'<xsl:apply-templates/>',
			'isTransparent',
			NULL
		);
	}

	/**
	* @testdox <span> allows <unknownElement> as child
	*/
	public function test79E09FE9()
	{
		$this->runCase(
			'<span> allows <unknownElement> as child',
			'<span><xsl:apply-templates/></span>',
			'allowChild',
			'<unknownElement/>'
		);
	}

	/**
	* @testdox <unknownElement> allows <span> as child
	*/
	public function test4289BD7D()
	{
		$this->runCase(
			'<unknownElement> allows <span> as child',
			'<unknownElement><xsl:apply-templates/></unknownElement>',
			'allowChild',
			'<span/>'
		);
	}
	// End of content generated by ../../../../scripts/patchTemplateForensicsTest.php

	public function getData()
	{
		return array(
			array(
				'<span> does not allow <div> as child',
				'<span><xsl:apply-templates/></span>',
				'denyChild',
				'<div><xsl:apply-templates/></div>'
			),
			array(
				'<span> does not allow <div> as child even with a <span> sibling',
				'<span><xsl:apply-templates/></span>',
				'denyChild',
				'<span>xxx</span><div><xsl:apply-templates/></div>'
			),
			array(
				'<span> and <div> does not allow <span> and <div> as child',
				'<span><xsl:apply-templates/></span><div><xsl:apply-templates/></div>',
				'denyChild',
				'<span/><div/>'
			),
			array(
				'<li> closes parent <li>',
				'<li/>',
				'closeParent',
				'<li><xsl:apply-templates/></li>'
			),
			array(
				'<div> closes parent <p>',
				'<div/>',
				'closeParent',
				'<p><xsl:apply-templates/></p>'
			),
			array(
				'<p> closes parent <p>',
				'<p/>',
				'closeParent',
				'<p><xsl:apply-templates/></p>'
			),
			array(
				'<div> does not close parent <div>',
				'<div/>',
				'!closeParent',
				'<div><xsl:apply-templates/></div>'
			),
			// This test mainly exist to ensure nothing bad happens with HTML tags that don't have
			// a "cp" value in TemplateForensics::$htmlElements
			array(
				'<span> does not close parent <span>',
				'<span/>',
				'!closeParent',
				'<span><xsl:apply-templates/></span>'
			),
			array(
				'<a> denies <a> as descendant',
				'<a><xsl:apply-templates/></a>',
				'denyDescendant',
				'<a/>'
			),
			array(
				'<a> allows <img> with no usemap attribute as child',
				'<a><xsl:apply-templates/></a>',
				'allowChild',
				'<img/>'
			),
			array(
				'<a> denies <img usemap="#foo"> as child',
				'<a><xsl:apply-templates/></a>',
				'denyChild',
				'<img usemap="#foo"/>'
			),
			array(
				'<div><a> allows <div> as child',
				'<div><a><xsl:apply-templates/></a></div>',
				'allowChild',
				'<div/>'
			),
			array(
				'<span><a> denies <div> as child',
				'<span><a><xsl:apply-templates/></a></span>',
				'denyChild',
				'<div/>'
			),
			array(
				'<audio> with no src attribute allows <source> as child',
				'<audio><xsl:apply-templates/></audio>',
				'allowChild',
				'<source/>'
			),
			array(
				'<audio src="..."> denies <source> as child',
				'<audio src="{@src}"><xsl:apply-templates/></audio>',
				'denyChild',
				'<source/>'
			),
			array(
				'<a> is considered transparent',
				'<a><xsl:apply-templates/></a>',
				'isTransparent',
				null
			),
			array(
				'<a><span> is not considered transparent',
				'<a><span><xsl:apply-templates/></span></a>',
				'!isTransparent',
				null
			),
			array(
				'<span><a> is not considered transparent',
				'<span><a><xsl:apply-templates/></a></span>',
				'!isTransparent',
				null
			),
			array(
				'A template composed entirely of a single <xsl:apply-templates/> is considered transparent',
				'<xsl:apply-templates/>',
				'isTransparent',
				null
			),
			array(
				'<span> allows <unknownElement> as child',
				'<span><xsl:apply-templates/></span>',
				'allowChild',
				'<unknownElement/>'
			),
			array(
				'<unknownElement> allows <span> as child',
				'<unknownElement><xsl:apply-templates/></unknownElement>',
				'allowChild',
				'<span/>'
			),
		);
	}
}