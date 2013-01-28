<?php

namespace s9e\TextFormatter\Tests\Configurator\RendererGenerators;

use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\Items\UnsafeTemplate;
use s9e\TextFormatter\Configurator\RendererGenerators\PHP;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Configurator\RendererGenerators\PHP
*/
class PHPTest extends Test
{
	/**
	* @testdox Ignores comments
	*/
	public function testComment()
	{
		$generator = new PHP;
		$xsl = 
			'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:output method="html" encoding="utf-8" />
				<xsl:template match="FOO"><!-- Nothing here --></xsl:template>
			</xsl:stylesheet>';

		$this->assertNotContains(
			'Nothing',
			$generator->generate($xsl)
		);
	}

	/**
	* @testdox Throws an exception if a template contains a processing instruction
	* @expectedException RuntimeException
	*/
	public function testPI()
	{
		$generator = new PHP;
		$xsl = 
			'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:output method="html" encoding="utf-8" />
				<xsl:template match="FOO"><?pi ?></xsl:template>
			</xsl:stylesheet>';

		$generator->generate($xsl);
	}

	/**
	* @testdox Throws an exception when encountering unsupported XSL elements
	* @expectedException RuntimeException
	* @expectedExceptionMessage Element 'xsl:foo' is not supported
	*/
	public function testUnsupported()
	{
		$generator = new PHP;
		$xsl = 
			'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:output method="html" encoding="utf-8" />
				<xsl:template match="FOO"><xsl:foo/></xsl:template>
			</xsl:stylesheet>';

		$generator->generate($xsl);
	}

	/**
	* @testdox Throws an exception when encountering namespaced elements
	* @expectedException RuntimeException
	* @expectedExceptionMessage Namespaced element 'x:x' is not supported
	*/
	public function testUnsupportedNamespace()
	{
		$generator = new PHP;
		$xsl = 
			'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:output method="html" encoding="utf-8" />
				<xsl:template match="FOO"><x:x xmlns:x="urn:x"/></xsl:template>
			</xsl:stylesheet>';

		$generator->generate($xsl);
	}

	/**
	* @testdox Throws an exception on <xsl:copy-of/> that does not copy an attribute
	* @expectedException RuntimeException
	* @expectedExceptionMessage Unsupported <xsl:copy-of/> expression 'current()'
	*/
	public function testUnsupportedCopyOf()
	{
		$generator = new PHP;
		$xsl = 
			'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:output method="html" encoding="utf-8" />
				<xsl:template match="FOO"><xsl:copy-of select="current()"/></xsl:template>
			</xsl:stylesheet>';

		$generator->generate($xsl);
	}

	/**
	* @testdox Throws an exception on unterminated strings in XPath expressions
	* @expectedException RuntimeException
	* @expectedExceptionMessage Unterminated string literal
	*/
	public function testUnterminatedStrings()
	{
		$generator = new PHP;
		$xsl =
			'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
				<xsl:output method="html" encoding="utf-8" />
				<xsl:template match="X"><xsl:value-of select="&quot;"/></xsl:template>
			</xsl:stylesheet>';

		$generator->generate($xsl);
	}

	/**
	* @dataProvider getHTMLData
	* @testdox HTML rendering
	*/
	public function testHTML($xml, $xsl, $html)
	{
		$generator = new PHP;
		$php = $generator->generate($xsl);

		$renderer = eval('?>' . $php);

		$this->assertSame(
			$html,
			$renderer->render($xml)
		);
	}

	/**
	* @dataProvider getXHTMLData
	* @testdox XHTML rendering
	*/
	public function testXHTML($xml, $xsl, $xhtml)
	{
		$generator = new PHP;
		$php = $generator->generate($xsl);

		$renderer = eval('?>' . $php);

		$this->assertSame(
			$xhtml,
			$renderer->render($xml)
		);
	}

	public function getHTMLData()
	{
		return $this->getRendererData('e*', 'html');
	}

	public function getXHTMLData()
	{
		return $this->getRendererData('e*', 'xhtml');
	}

	public function getRendererData($pattern, $outputMethod)
	{
		$testCases = array();
		foreach (glob(__DIR__ . '/data/' . $pattern . '.xml') as $filepath)
		{
			$testCases[] = array(
				file_get_contents($filepath),
				file_get_contents(substr($filepath, 0, -3) . $outputMethod . '.xsl'),
				file_get_contents(substr($filepath, 0, -3) . $outputMethod)
			);
		}

		return $testCases;
	}

	/**
	* @testdox BBCodes from repository.xml render nicely
	* @dataProvider getBBCodesData
	*/
	public function testBBCodes($xml, $xsl, $html)
	{
		$generator = new PHP;
		$php = $generator->generate($xsl);

		$renderer = eval('?>' . $php);

		$this->assertSame(
			$html,
			$renderer->render($xml)
		);
	}

	public function getBBCodesData()
	{
		return $this->getRendererData('b*', 'html');
	}

	/**
	* @testdox Edge cases
	* @dataProvider getEdgeCases
	*/
	public function testEdgeCases($xml, $configuratorSetup, $rendererSetup = null)
	{
		$configurator = new Configurator;
		call_user_func($configuratorSetup, $configurator);

		$expected = $configurator->getRenderer()->render($xml);

		$generator = new PHP;
		$php = $generator->generate($configurator->stylesheet->get());

		$renderer = eval('?>' . $php);
		if ($rendererSetup)
		{
			call_user_func($rendererSetup, $renderer);
		}

		$this->assertSame(
			$expected,
			$renderer->render($xml)
		);
	}

	public function getEdgeCases()
	{
		return array(
			array(
				"<rt>x <B/> y</rt>",
				function ($configurator)
				{
					$configurator->tags->add('B')->defaultTemplate
						= '<b><xsl:apply-templates/></b>';
				}
			),
			array(
				"<rt>x <B/> y</rt>",
				function ($configurator)
				{
					$configurator->tags->add('B')->defaultTemplate = new UnsafeTemplate(
						'<xsl:element name="{translate(name(),\'B\',\'b\')}"><xsl:apply-templates/></xsl:element>'
					);
				}
			),
			array(
				"<rt>x <HR/> y</rt>",
				function ($configurator)
				{
					$configurator->tags->add('HR')->defaultTemplate = new UnsafeTemplate(
						'<xsl:element name="{translate(name(),\'HR\',\'hr\')}" />'
					);
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo', "'FOO'");
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:value-of select="$foo"/>';
				}
			),
			array(
				'<rt><X/><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo', "count(//X)");
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:value-of select="$foo"/>';
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:value-of select="$foo"/>';
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:value-of select="$foo"/>';
				},
				function ($renderer)
				{
					$renderer->setParameter('foo', 15);
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:value-of select="$foo"/>';
				},
				function ($renderer)
				{
					$renderer->setParameter('foo', "'...'");
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:value-of select="$foo"/>';
				},
				function ($renderer)
				{
					$renderer->setParameter('foo', '"..."');
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:value-of select="$foo"/>';
				},
				function ($renderer)
				{
					$renderer->setParameter('foo', '"\'"..."\'"');
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:if test="$foo">!</xsl:if>';
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:if test="not($foo)">!</xsl:if>';
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:if test="$foo">!</xsl:if>';
				},
				function ($renderer)
				{
					$renderer->setParameter('foo', true);
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:if test="not($foo)">!</xsl:if>';
				},
				function ($renderer)
				{
					$renderer->setParameter('foo', true);
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('foo', 3);
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:if test="$foo &lt; 5">!</xsl:if>';
				}
			),
			array(
				'<rt><X/></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('xxx', 3);
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:if test="$xxx &lt; 1">!</xsl:if>';
				}
			),
			array(
				'<rt><X/><Y>1</Y><Y>2</Y></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->parameters->add('xxx', '//Y');
					$configurator->tags->add('X')->defaultTemplate
						= '<xsl:value-of select="$xxx"/>';
				}
			),
			array(
				'<rt xmlns:html="urn:s9e:TextFormatter:html"><html:b>...</html:b></rt>',
				function ($configurator)
				{
					$configurator->tags->add('html:b')->defaultTemplate
						= '<b><xsl:apply-templates /></b>';
				}
			),
			array(
				'<rt xmlns:x="urn:s9e:TextFormatter:x"><x:b>...</x:b><x:c>!!!</x:c></rt>',
				function ($configurator)
				{
					$configurator->tags->add('x:b')->defaultTemplate
						= '<b><xsl:apply-templates /></b>';

					$configurator->stylesheet->setWildcardTemplate(
						'x',
						'<span><xsl:apply-templates /></span>'
					);
				}
			),
			array(
				'<rt><X/><X i="8"/><X i="4"/><X i="2"/></rt>',
				function ($configurator)
				{
					$tag = $configurator->tags->add('X');
					$tag->defaultTemplate = 'default';
					$tag->templates['@i < 5'] = '5';
					$tag->templates['@i < 3'] = '3';
				}
			),
			array(
				'<rt><X/><X i="8"/><X i="4"/><X i="2"/></rt>',
				function ($configurator)
				{
					$tag = $configurator->tags->add('X');
					$tag->defaultTemplate = 'default';
					$tag->templates['@i < 3'] = '3';
					$tag->templates['@i < 5'] = '5';
				}
			),
			array(
				'<rt xmlns:html="urn:s9e:TextFormatter:html"><html:b title="\'&quot;&amp;\'">...</html:b></rt>',
				function ($configurator)
				{
					$configurator->stylesheet->setWildcardTemplate(
						'html',
						new UnsafeTemplate('<xsl:element name="{local-name()}"><xsl:copy-of select="@*"/><xsl:apply-templates/></xsl:element>')
					);
				}
			),
			array(
				'<rt><E>:)</E><E>:(</E></rt>',
				function ($configurator)
				{
					$configurator->tags->add('E')->defaultTemplate
						= '<xsl:choose><xsl:when test=".=\':)\'"><img src="happy.png" alt=":)"/></xsl:when><xsl:when test=".=\':(\'"><img src="sad.png" alt=":("/></xsl:when><xsl:otherwise><xsl:value-of select="."/></xsl:otherwise></xsl:choose>';
				}
			),
		);
	}
}