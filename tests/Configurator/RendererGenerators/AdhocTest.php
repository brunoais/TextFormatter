<?php

namespace s9e\TextFormatter\Tests\Configurator\RendererGenerators;

use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Configurator\RendererGenerators\Adhoc;
use s9e\TextFormatter\Tests\Test;

/**
* @covers s9e\TextFormatter\Configurator\RendererGenerators\Adhoc
*/
class AdhocTest extends Test
{
	/**
	* @dataProvider getData
	*/
	public function test($xml, $setup)
	{
		$configurator = new Configurator;
		call_user_func($setup, $configurator);

		$expected = $configurator->getRenderer()->render($xml);

		$php = Adhoc::generate($configurator->stylesheet->get());
		$renderer = eval('?>' . $php);

		$this->assertSame(
			$expected,
			$renderer->render($xml)
		);
	}

	public function getData()
	{
		return array(
			array(
				'<pt>Plain text</pt>',
				function ($configurator)
				{
				}
			),
			array(
				"<rt>Multi<br/>\nline</rt>",
				function ($configurator)
				{
				}
			),
			array(
				'<rt>xxx <B><st>[b]</st>bold<et>[/b]</et></B> text</rt>',
				function ($configurator)
				{
					$configurator->tags->add('B')->defaultTemplate
						= '<b><xsl:apply-templates/></b>';
				}
			),
			array(
				'<rt>xxx <B><st>[b]</st>bold<B><st>[b]</st>er<et>[/b]</et></B><et>[/b]</et></B> text</rt>',
				function ($configurator)
				{
					$configurator->tags->add('B')->defaultTemplate
						= '<b><xsl:apply-templates/></b>';
				}
			),
			array(
				'<rt>xxx <A href="http://example.org">link</A>.</rt>',
				function ($configurator)
				{
					$tag = $configurator->tags->add('A');
					$tag->attributes->add('href')->filterChain->append('#url');
					$tag->defaultTemplate
						= '<a><xsl:copy-of select="@href"/><xsl:apply-templates/></a>';
				}
			),
			array(
				'<rt>xx<QUOTE author="foo"><st>[quote]</st>my quote<et>[/quote]</et></QUOTE>yy</rt>',
				function ($configurator)
				{
					$tag = $configurator->tags->add('QUOTE');
					$tag->attributes->add('author');
					$tag->defaultTemplate = '
						<blockquote>
							<xsl:if test="not(@author)">
								<xsl:attribute name="class">uncited</xsl:attribute>
							</xsl:if>
							<div>
								<xsl:if test="@author">
									<cite><xsl:value-of select="@author" /> wrote:</cite>
								</xsl:if>
								<xsl:apply-templates />
							</div>
						</blockquote>
					';
				}
			)
		);
	}
}