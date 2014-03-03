<?php

namespace s9e\TextFormatter\Tests\Configurator\Helpers;

use DOMDocument;
use DOMXPath;
use Exception;
use RuntimeException;
use s9e\TextFormatter\Tests\Test;
use s9e\TextFormatter\Configurator\Items\Tag;
use s9e\TextFormatter\Configurator\Items\Template;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;

/**
* @covers s9e\TextFormatter\Configurator\Helpers\TemplateHelper
*/
class TemplateHelperTest extends Test
{
	/**
	* @testdox loadTemplate() can load 'foo'
	*/
	public function testLoadText()
	{
		$text = 'foo';

		$dom = TemplateHelper::loadTemplate($text);
		$this->assertInstanceOf('DOMDocument', $dom);

		$this->assertContains($text, $dom->saveXML());
	}

	/**
	* @testdox saveTemplate() correctly handles 'foo'
	*/
	public function testSaveText()
	{
		$text = 'foo';

		$this->assertSame($text, TemplateHelper::saveTemplate(TemplateHelper::loadTemplate($text)));
	}

	/**
	* @testdox loadTemplate() can load '<xsl:value-of select="@foo"/>'
	*/
	public function testLoadXSL()
	{
		$xsl = '<xsl:value-of select="@foo"/>';

		$dom = TemplateHelper::loadTemplate($xsl);
		$this->assertInstanceOf('DOMDocument', $dom);

		$this->assertContains($xsl, $dom->saveXML());
	}

	/**
	* @testdox saveTemplate() correctly handles '<xsl:value-of select="@foo"/>'
	*/
	public function testSaveXSL()
	{
		$xsl = '<xsl:value-of select="@foo"/>';

		$this->assertSame($xsl, TemplateHelper::saveTemplate(TemplateHelper::loadTemplate($xsl)));
	}

	/**
	* @testdox saveTemplate() correctly handles an empty string
	*/
	public function testSaveXSLEmpty()
	{
		$xsl = '';

		$this->assertSame($xsl, TemplateHelper::saveTemplate(TemplateHelper::loadTemplate($xsl)));
	}

	/**
	* @testdox loadTemplate() can load '<ul><li>one<li>two</ul>'
	*/
	public function testLoadHTML()
	{
		$html = '<ul><li>one<li>two</ul>';
		$xml  = '<ul><li>one</li><li>two</li></ul>';

		$dom = TemplateHelper::loadTemplate($html);
		$this->assertInstanceOf('DOMDocument', $dom);

		$this->assertContains($xml, $dom->saveXML());
	}

	/**
	* @testdox loadTemplate() can load '<ul><li>one<li>two</ul>'
	* @depends testLoadHTML
	*/
	public function testLoadHTMLInNamespace()
	{
		$html = '<ul><li>one<li>two</ul>';

		$this->assertSame(
			'http://www.w3.org/1999/XSL/Transform',
			TemplateHelper::loadTemplate($html)->lookupNamespaceURI('xsl')
		);
	}

	/**
	* @testdox saveTemplate() correctly handles '<ul><li>one<li>two</ul>'
	*/
	public function testSaveHTML()
	{
		$html = '<ul><li>one<li>two</ul>';
		$xml  = '<ul><li>one</li><li>two</li></ul>';

		$this->assertSame($xml, TemplateHelper::saveTemplate(TemplateHelper::loadTemplate($html)));
	}

	/**
	* @testdox loadTemplate() throws an exception on malformed XSL
	* @expectedException s9e\TextFormatter\Configurator\Exceptions\InvalidXslException
	* @expectedExceptionMessage Premature end of data
	*/
	public function testLoadInvalidXSL()
	{
		$xsl = '<xsl:value-of select="@foo">';
		TemplateHelper::loadTemplate($xsl);
	}

	/**
	* @testdox asXPath('foo') returns 'foo'
	*/
	public function testAsXPathSingleQuotes()
	{
		$this->assertSame("'foo'", TemplateHelper::asXPath('foo'));
	}

	/**
	* @testdox asXPath("d'oh") returns "d'oh"
	*/
	public function testAsXPathDoubleQuotes()
	{
		$this->assertSame('"d\'oh"', TemplateHelper::asXPath("d'oh"));
	}

	/**
	* @testdox asXPath("'\"") returns concat("'",'"')
	*/
	public function testAsXPathBothQuotes1()
	{
		$this->assertSame("concat(\"'\",'\"')", TemplateHelper::asXPath("'\""));
	}

	/**
	* @testdox asXPath('"\'') returns concat('"',"'")
	*/
	public function testAsXPathBothQuotes2()
	{
		$this->assertSame("concat('\"',\"'\")", TemplateHelper::asXPath('"\''));
	}

	/**
	* @testdox parseAttributeValueTemplate() tests
	* @dataProvider getAVT
	*/
	public function testParseAttributeValueTemplate($attrValue, $expected)
	{
		if ($expected instanceof Exception)
		{
			$this->setExpectedException(get_class($expected), $expected->getMessage());
		}

		$this->assertSame(
			$expected,
			TemplateHelper::parseAttributeValueTemplate($attrValue)
		);
	}

	public function getAVT()
	{
		return [
			[
				'',
				[]
			],
			[
				'foo',
				[
					['literal', 'foo']
				]
			],
			[
				'foo {@bar} baz',
				[
					['literal',    'foo '],
					['expression', '@bar'],
					['literal',    ' baz']
				]
			],
			[
				'foo {{@bar}} baz',
				[
					['literal', 'foo '],
					['literal', '{'],
					['literal', '@bar} baz']
				]
			],
			[
				'foo {@bar}{baz} quux',
				[
					['literal',    'foo '],
					['expression', '@bar'],
					['expression', 'baz'],
					['literal',    ' quux']
				]
			],
			[
				'foo {"bar"} baz',
				[
					['literal',    'foo '],
					['expression', '"bar"'],
					['literal',    ' baz']
				]
			],
			[
				"foo {'bar'} baz",
				[
					['literal',    'foo '],
					['expression', "'bar'"],
					['literal',    ' baz']
				]
			],
			[
				'foo {"\'bar\'"} baz',
				[
					['literal',    'foo '],
					['expression', '"\'bar\'"'],
					['literal',    ' baz']
				]
			],
			[
				'foo {"{bar}"} baz',
				[
					['literal',    'foo '],
					['expression', '"{bar}"'],
					['literal',    ' baz']
				]
			],
			[
				'foo {"bar} baz',
				new RuntimeException('Unterminated XPath expression')
			],
			[
				'foo {bar',
				new RuntimeException('Unterminated XPath expression')
			],
			[
				'<foo> {"<bar>"} &amp;',
				[
					['literal',    '<foo> '],
					['expression', '"<bar>"'],
					['literal',    ' &amp;']
				]
			],
		];
	}

	/**
	* @testdox getParametersFromXSL() tests
	* @dataProvider getParametersTests
	*/
	public function testGetParametersFromXSL($xsl, $expected)
	{
		if ($expected instanceof Exception)
		{
			$this->setExpectedException(get_class($expected), $expected->getMessage());
		}

		$this->assertSame(
			$expected,
			TemplateHelper::getParametersFromXSL($xsl)
		);
	}

	public function getParametersTests()
	{
		return [
			[
				'',
				[]
			],
			[
				'<b><xsl:value-of select="concat($Foo, $BAR, $Foo)"/></b>',
				['BAR', 'Foo']
			],
			[
				'<b>
					<xsl:variable name="FOO"/>
					<xsl:value-of select="$FOO"/>
				</b>',
				[]
			],
			[
				'<b>
					<xsl:variable name="FOO"/>
					<xsl:if test="$BAR">
						<xsl:value-of select="$FOO"/>
					</xsl:if>
				</b>',
				['BAR']
			],
			[
				'<b>
					<xsl:value-of select="$FOO"/>
					<xsl:variable name="FOO"/>
					<xsl:if test="$BAR">
						<xsl:value-of select="$FOO"/>
					</xsl:if>
				</b>',
				['BAR', 'FOO']
			],
			[
				'<b title="$FOO{$BAR}$BAZ"/>',
				['BAR']
			],
			[
				'<b title="{concat($Foo, $BAR, $Foo)}"/>',
				['BAR', 'Foo']
			],
			[
				'<div>
					<xsl:variable name="S_TEST"/>
					<xsl:if test="$S_TEST">
						<b title="{$FOO}"/>
					</xsl:if>
				</div>',
				['FOO']
			],
			[
				'<div>
					<xsl:if test="$S_TEST">
						<b title="{$FOO}"/>
					</xsl:if>
					<xsl:variable name="S_TEST"/>
					<xsl:if test="$S_TEST">
						<b title="{$FOO}"/>
					</xsl:if>
				</div>',
				['FOO', 'S_TEST']
			],
		];
	}

	public function runTestGetNodes($methodName, $template, $query)
	{
		$dom = new DOMDocument;
		$xsl = '<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">'
		     . $template
		     . '</xsl:template>';
		$dom->loadXML($xsl);

		$xpath = new DOMXPath($dom);
		$nodes = ($query) ? iterator_to_array($xpath->query($query), false) : [];

		$this->assertEquals(
			$nodes,
			TemplateHelper::$methodName($dom)
		);
	}

	/**
	* @testdox getObjectParamsByRegexp() tests
	* @dataProvider getObjectParamsByRegexpTests
	*/
	public function testGetObjectParamsByRegexp($regexp, $template, $query = null)
	{
		$dom = new DOMDocument;
		$xsl = '<xsl:template xmlns:xsl="http://www.w3.org/1999/XSL/Transform">'
		     . $template
		     . '</xsl:template>';
		$dom->loadXML($xsl);

		$xpath = new DOMXPath($dom);
		$nodes = ($query) ? iterator_to_array($xpath->query($query), false) : [];

		$this->assertEquals(
			$nodes,
			TemplateHelper::getObjectParamsByRegexp($dom, $regexp)
		);
	}

	/**
	* @testdox getCSSNodes() tests
	* @dataProvider getCSSNodesTests
	*/
	public function testGetCSSNodes($template, $query = null)
	{
		$this->runTestGetNodes('getCSSNodes', $template, $query);
	}

	/**
	* @testdox getJSNodes() tests
	* @dataProvider getJSNodesTests
	*/
	public function testGetJSNodes($template, $query = null)
	{
		$this->runTestGetNodes('getJSNodes', $template, $query);
	}

	/**
	* @testdox getURLNodes() tests
	* @dataProvider getURLNodesTests
	*/
	public function testGetURLNodes($template, $query = null)
	{
		$this->runTestGetNodes('getURLNodes', $template, $query);
	}

	public function getObjectParamsByRegexpTests()
	{
		return [
			[
				'//',
				'...',
				null
			],
			[
				'/^allowscriptaccess$/i',
				'<embed AllowScriptAccess="always"/>',
				'//@*'
			],
			[
				'/^allowscriptaccess$/i',
				'<div allowscriptaccess="always"/>',
				null
			],
			[
				'/^allowscriptaccess$/i',
				'<embed><xsl:attribute name="AllowScriptAccess"/></embed>',
				'//xsl:attribute'
			],
			[
				'/^allowscriptaccess$/i',
				'<embed><xsl:if test="@foo"><xsl:attribute name="AllowScriptAccess"/></xsl:if></embed>',
				'//xsl:attribute'
			],
			[
				'/^allowscriptaccess$/i',
				'<embed><xsl:copy-of select="@allowscriptaccess"/></embed>',
				'//xsl:copy-of'
			],
			[
				'/^allowscriptaccess$/i',
				'<object><param name="AllowScriptAccess"/><param name="foo"/></object>',
				'//param[@name != "foo"]'
			],
			[
				'/^allowscriptaccess$/i',
				'<object><xsl:if test="@foo"><param name="AllowScriptAccess"/><param name="foo"/></xsl:if></object>',
				'//param[@name != "foo"]'
			],
		];
	}

	public function getCSSNodesTests()
	{
		return [
			[
				'...'
			],
			[
				'<b style="1">...<i style="2">...</i></b><b style="3">...</b>',
				'//@style'
			],
			[
				'<b STYLE="">...</b>',
				'//@*'
			],
			[
				'<b><xsl:if test="@foo"><xsl:attribute name="style"/></xsl:if></b>',
				'//xsl:attribute'
			],
			[
				'<b><xsl:if test="@foo"><xsl:attribute name="STYLE"/></xsl:if></b>',
				'//xsl:attribute'
			],
			[
				'<b><xsl:copy-of select="@style"/></b>',
				'//xsl:copy-of'
			],
			[
				'<style/>',
				'*'
			],
			[
				'<STYLE/>',
				'*'
			],
			[
				'<xsl:element name="style"/>',
				'*'
			],
			[
				'<xsl:element name="STYLE"/>',
				'*'
			],
		];
	}

	public function getJSNodesTests()
	{
		return [
			[
				'...'
			],
			[
				'<script/>',
				'*'
			],
			[
				'<SCRIPT/>',
				'*'
			],
			[
				'<xsl:element name="script"/>',
				'*'
			],
			[
				'<xsl:element name="SCRIPT"/>',
				'*'
			],
			[
				'<b onclick=""/><i title=""/><b onfocus=""/>',
				'//@onclick | //@onfocus'
			],
			[
				'<b ONHOVER=""/>',
				'//@*'
			],
			[
				'<b><xsl:if test="@foo"><xsl:attribute name="onclick"/></xsl:if></b>',
				'//xsl:attribute'
			],
			[
				'<b><xsl:if test="@foo"><xsl:attribute name="ONCLICK"/></xsl:if></b>',
				'//xsl:attribute'
			],
			[
				'<b><xsl:copy-of select="@onclick"/></b>',
				'//xsl:copy-of'
			],
			[
				'<b data-s9e-livepreview-postprocess=""/>',
				'//@*'
			],
		];
	}

	public function getURLNodesTests()
	{
		return [
			[
				'...'
			],
			[
				'<form action=""/>',
				'//@action'
			],
			[
				'<body background=""/>',
				'//@background'
			],
			[
				'<blockquote cite=""/>',
				'//@cite',
			],
			[
				'<cite/>',
				null
			],
			[
				'<object classid=""/>',
				'//@classid'
			],
			[
				'<object codebase=""/>',
				'//@codebase'
			],
			[
				'<object data=""/>',
				'//@data'
			],
			[
				'<input formaction=""/>',
				'//@formaction'
			],
			[
				'<a href=""/>',
				'//@href'
			],
			[
				'<command icon=""/>',
				'//@icon'
			],
			[
				'<img longdesc=""/>',
				'//@longdesc'
			],
			[
				'<cache manifest=""/>',
				'//@manifest'
			],
			[
				'<head profile=""/>',
				'//@profile'
			],
			[
				'<video poster=""/>',
				'//@poster'
			],
			[
				'<img src=""/>',
				'//@src'
			],
			[
				'<img lowsrc=""/>',
				'//@lowsrc'
			],
			[
				'<img dynsrc=""/>',
				'//@dynsrc'
			],
			[
				'<input usemap=""/>',
				'//@usemap'
			],
			[
				'<object><param name="movie" value=""/></object>',
				'//@value'
			],
			[
				'<OBJECT><PARAM NAME="MOVIE" VALUE=""/></OBJECT>',
				'//@value'
			],
			[
				'<object><param name="dataurl" value=""/></object>',
				'//@value'
			],
		];
	}

	/**
	* @testdox getElementsByRegexp() can return elements created via <xsl:copy-of/>
	*/
	public function testGetElementsByRegexp()
	{
		$dom = TemplateHelper::loadTemplate('<xsl:copy-of select="x"/><xsl:copy-of select="foo"/>');

		$this->assertSame(
			[$dom->firstChild->firstChild->nextSibling],
			TemplateHelper::getElementsByRegexp($dom, '/^foo$/')
		);
	}

	/**
	* @testdox replaceTokens() tests
	* @dataProvider replaceTokensTests
	*/
	public function testReplaceTokens($template, $regexp, $fn, $expected)
	{
		if ($expected instanceof Exception)
		{
			$this->setExpectedException(get_class($expected), $expected->getMessage());
		}

		$this->assertSame(
			$expected,
			TemplateHelper::replaceTokens($template, $regexp, $fn, $expected)
		);
	}

	public function replaceTokensTests()
	{
		return [
			[
				'',
				'/foo/',
				function ($m) {},
				''
			],
			[
				'<br/>',
				'/foo/',
				function ($m) {},
				'<br/>'
			],
			[
				'<b title="$1" alt="$2"/>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['literal', serialize($m)];
				},
				'<b title="a:1:{i:0;s:2:&quot;$1&quot;;}" alt="a:1:{i:0;s:2:&quot;$2&quot;;}"/>'
			],
			[
				'<b title="$1"/>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['expression', '@foo'];
				},
				'<b title="{@foo}"/>'
			],
			[
				'<b title="$1"/>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['passthrough', true];
				},
				'<b title="{.}"/>'
			],
			[
				'<b title="$1"/>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['passthrough', false];
				},
				'<b title="{substring(.,1+string-length(st),string-length()-(string-length(st)+string-length(et)))}"/>'
			],
			[
				'<b>$1</b>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['literal', serialize($m)];
				},
				'<b>a:1:{i:0;s:2:"$1";}</b>'
			],
			[
				'<b>$1</b>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['expression', '@foo'];
				},
				'<b><xsl:value-of select="@foo"/></b>'
			],
			[
				'<b>$1</b>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['passthrough', true];
				},
				'<b><xsl:apply-templates/></b>'
			],
			[
				'<b>$1</b>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['passthrough', false];
				},
				'<b><xsl:apply-templates/></b>'
			],
			[
				'<b id="$1">$1</b>',
				'/\\$[0-9]+/',
				function ($m, $node)
				{
					return ['literal', get_class($node)];
				},
				'<b id="DOMAttr">DOMText</b>'
			],
			[
				'<b>$1</b><i>$$</i>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['literal', 'ONE'];
				},
				'<b>ONE</b><i>$$</i>'
			],
			[
				'<b>foo $1 bar</b>',
				'/\\$[0-9]+/',
				function ($m)
				{
					return ['literal', 'ONE'];
				},
				'<b>foo ONE bar</b>'
			],
		];
	}

	/**
	* @testdox highlightNode() tests
	* @dataProvider getHighlights
	*/
	public function testHighlightNode($query, $template, $expected)
	{
		$dom   = TemplateHelper::loadTemplate($template);
		$xpath = new DOMXPath($dom);

		$this->assertSame(
			$expected,
			TemplateHelper::highlightNode(
				$xpath->query($query)->item(0),
				'<span style="background-color:#ff0">',
				'</span>'
			)
		);
	}

	public function getHighlights()
	{
		return [
			[
				'//xsl:apply-templates',
				'<script><xsl:apply-templates/></script>',
'&lt;script&gt;
  <span style="background-color:#ff0">&lt;xsl:apply-templates/&gt;</span>
&lt;/script&gt;'
			],
			[
				'//@href',
				'<a href="{@foo}"><xsl:apply-templates/></a>',
'&lt;a <span style="background-color:#ff0">href=&quot;{@foo}&quot;</span>&gt;
  &lt;xsl:apply-templates/&gt;
&lt;/a&gt;'
			],
			[
				'//processing-instruction()',
				'<?php foo(); ?>',
				'<span style="background-color:#ff0">&lt;?php foo(); ?&gt;</span>'
			],
			[
				'//comment()',
				'xx<!-- foo -->yy',
				'xx<span style="background-color:#ff0">&lt;!-- foo --&gt;</span>yy'
			],
			[
				'//text()',
				'<b>foo</b>',
				'&lt;b&gt;<span style="background-color:#ff0">foo</span>&lt;/b&gt;'
			],
			[
				'//script/xsl:apply-templates',
				'<b><xsl:apply-templates/></b><script><xsl:apply-templates/></script><i><xsl:apply-templates/></i>',
'&lt;b&gt;
  &lt;xsl:apply-templates/&gt;
&lt;/b&gt;
&lt;script&gt;
  <span style="background-color:#ff0">&lt;xsl:apply-templates/&gt;</span>
&lt;/script&gt;
&lt;i&gt;
  &lt;xsl:apply-templates/&gt;
&lt;/i&gt;'
			],
			[
				'//a[2]/@href',
				'<a href="{@foo}"><xsl:apply-templates/></a><a href="{@foo}"><xsl:apply-templates/></a>',
'&lt;a href=&quot;{@foo}&quot;&gt;
  &lt;xsl:apply-templates/&gt;
&lt;/a&gt;
&lt;a <span style="background-color:#ff0">href=&quot;{@foo}&quot;</span>&gt;
  &lt;xsl:apply-templates/&gt;
&lt;/a&gt;'
			],
			[
				'//processing-instruction()[2]',
				'<?php foo(); ?><?php foo(); ?><?php foo(); ?>',
'&lt;?php foo(); ?&gt;
<span style="background-color:#ff0">&lt;?php foo(); ?&gt;</span>
&lt;?php foo(); ?&gt;'
			],
			[
				'//comment()[2]',
				'xx<!-- foo --><!-- foo --><!-- foo -->yy',
				'xx&lt;!-- foo --&gt;<span style="background-color:#ff0">&lt;!-- foo --&gt;</span>&lt;!-- foo --&gt;yy'
			],
			[
				'//b[2]/text()',
				'<b>foo</b><b>foo</b><b>foo</b>',
'&lt;b&gt;foo&lt;/b&gt;
&lt;b&gt;<span style="background-color:#ff0">foo</span>&lt;/b&gt;
&lt;b&gt;foo&lt;/b&gt;'
			],
		];
	}

	/**
	* @testdox minifyXPath() tests
	* @dataProvider minifyXPathTests
	*/
	public function testMinifyXPath($original, $expected)
	{
		if ($expected instanceof Exception)
		{
			$this->setExpectedException(get_class($expected), $expected->getMessage());
		}

		$this->assertSame(
			$expected,
			TemplateHelper::minifyXPath($original)
		);
	}

	public function minifyXPathTests()
	{
		return [
			[
				'',
				''
			],
			[
				' @foo ',
				'@foo'
			],
			[
				'@ foo',
				'@foo'
			],
			[
				'concat(@foo, @bar, @baz)',
				'concat(@foo,@bar,@baz)'
			],
			[
				"concat(@foo, ' @bar ', @baz)",
				"concat(@foo,' @bar ',@baz)"
			],
			[
				'@foo = 2',
				'@foo=2'
			],
			[
				'substring(., 1 + string-length(st), string-length() - (string-length(st) + string-length(et)))',
				'substring(.,1+string-length(st),string-length()-(string-length(st)+string-length(et)))'
			],
			[
				'@foo - bar = 2',
				'@foo -bar=2'
			],
			[
				'@foo- - 1 = 2',
				'@foo- -1=2'
			],
			[
				' foo or _bar ',
				'foo or _bar'
			],
			[
				'foo = "bar',
				new RuntimeException("Cannot parse XPath expression 'foo = \"bar'")
			]
		];
	}

	/**
	* @testdox getMetaElementsRegexp() tests
	* @dataProvider getMetaElementsRegexpTests
	*/
	public function testMetaElementsRegexp(array $templates, $expected)
	{
		$this->assertSame($expected, TemplateHelper::getMetaElementsRegexp($templates));
	}

	public function getMetaElementsRegexpTests()
	{
		return [
			[
				[],
				'(<[eis]>[^<]*</[^>]+>)'
			],
			[
				['e' => '', 'i' => '', 's' => '', 'B' => '<b>..</b>'],
				'(<[eis]>[^<]*</[^>]+>)'
			],
			[
				['e' => '<xsl:value-of select="."/>', 'i' => '', 's' => '', 'B' => '<b>..</b>'],
				'(<[is]>[^<]*</[^>]+>)'
			],
			[
				['e' => '.', 'i' => '.', 's' => '.', 'B' => '<b>..</b>'],
				'((?!))'
			],
			[
				['X' => '<xsl:value-of select="$s"/>'],
				'(<[eis]>[^<]*</[^>]+>)'
			],
			[
				['X' => '<xsl:value-of select="@s"/>'],
				'(<[eis]>[^<]*</[^>]+>)'
			],
			[
				['X' => '<xsl:value-of select="s"/>'],
				'(<[ei]>[^<]*</[^>]+>)'
			],
			[
				['X' => '<xsl:if test="e">...</xsl:if>'],
				'(<[is]>[^<]*</[^>]+>)'
			],
			[
				['X' => '<hr title="s{i}e"/>'],
				'(<[es]>[^<]*</[^>]+>)'
			],
		];
	}

	/**
	* @testdox replaceHomogeneousTemplates() tests
	* @dataProvider getReplaceHomogeneousTemplatesTests
	*/
	public function testReplaceHomogeneousTemplates($templates, $expected)
	{
		TemplateHelper::replaceHomogeneousTemplates($templates);
		$this->assertSame($expected, $templates);
	}

	public function getReplaceHomogeneousTemplatesTests()
	{
		return [
			[
				// Nothing happens if there's only one template
				[
					'p' => '<p><xsl:apply-templates/></p>'
				],
				[
					'p' => '<p><xsl:apply-templates/></p>'
				]
			],
			[
				[
					'b' => '<b><xsl:apply-templates/></b>',
					'i' => '<i><xsl:apply-templates/></i>',
					'u' => '<u><xsl:apply-templates/></u>'
				],
				[
					'b' => '<xsl:element name="{name()}"><xsl:apply-templates/></xsl:element>',
					'i' => '<xsl:element name="{name()}"><xsl:apply-templates/></xsl:element>',
					'u' => '<xsl:element name="{name()}"><xsl:apply-templates/></xsl:element>'
				]
			],
			[
				// Ensure we don't over-replace
				[
					'b' => '<b><xsl:apply-templates/></b>',
					'i' => '<i><xsl:apply-templates/></i>',
					'u' => '<u><xsl:apply-templates/></u>',
					'p' => '<p><xsl:apply-templates/></p>!'
				],
				[
					'b' => '<xsl:element name="{name()}"><xsl:apply-templates/></xsl:element>',
					'i' => '<xsl:element name="{name()}"><xsl:apply-templates/></xsl:element>',
					'u' => '<xsl:element name="{name()}"><xsl:apply-templates/></xsl:element>',
					'p' => '<p><xsl:apply-templates/></p>!'
				]
			],
			[
				// Test that names are lowercased
				[
					'B' => '<b><xsl:apply-templates/></b>',
					'I' => '<i><xsl:apply-templates/></i>',
					'p' => '<p><xsl:apply-templates/></p>'
				],
				[
					'B' => '<xsl:element name="{translate(name(),\'BI\',\'bi\')}"><xsl:apply-templates/></xsl:element>',
					'I' => '<xsl:element name="{translate(name(),\'BI\',\'bi\')}"><xsl:apply-templates/></xsl:element>',
					'p' => '<xsl:element name="{translate(name(),\'BI\',\'bi\')}"><xsl:apply-templates/></xsl:element>',
				]
			],
			[
				// Test namespaced tags
				[
					'html:b' => '<b><xsl:apply-templates/></b>',
					'html:i' => '<i><xsl:apply-templates/></i>',
					'html:u' => '<u><xsl:apply-templates/></u>'
				],
				[
					'html:b' => '<xsl:element name="{local-name()}"><xsl:apply-templates/></xsl:element>',
					'html:i' => '<xsl:element name="{local-name()}"><xsl:apply-templates/></xsl:element>',
					'html:u' => '<xsl:element name="{local-name()}"><xsl:apply-templates/></xsl:element>'
				]
			],
			[
				// Test namespaced tags
				[
					'html:b' => '<b><xsl:apply-templates/></b>',
					'html:I' => '<i><xsl:apply-templates/></i>',
					'html:u' => '<u><xsl:apply-templates/></u>'
				],
				[
					'html:b' => '<xsl:element name="{translate(local-name(),\'I\',\'i\')}"><xsl:apply-templates/></xsl:element>',
					'html:I' => '<xsl:element name="{translate(local-name(),\'I\',\'i\')}"><xsl:apply-templates/></xsl:element>',
					'html:u' => '<xsl:element name="{translate(local-name(),\'I\',\'i\')}"><xsl:apply-templates/></xsl:element>'
				]
			],
		];
	}
}