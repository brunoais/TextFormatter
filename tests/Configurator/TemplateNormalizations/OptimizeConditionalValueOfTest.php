<?php

namespace s9e\TextFormatter\Tests\Configurator\TemplateNormalizations;

/**
* @covers s9e\TextFormatter\Configurator\TemplateNormalizations\OptimizeConditionalValueOf
*/
class OptimizeConditionalValueOfTest extends AbstractTest
{
	public function getData()
	{
		return [
			[
				'<xsl:if test="@foo"><xsl:value-of select="@foo"/></xsl:if>',
				'<xsl:value-of select="@foo"/>'
			],
			[
				'<div><xsl:attribute name="title"><xsl:if test="@foo"><xsl:value-of select="@foo"/></xsl:if></xsl:attribute></div>',
				'<div><xsl:attribute name="title"><xsl:value-of select="@foo"/></xsl:attribute></div>'
			],
			[
				'<xsl:if test="@foo"><xsl:value-of select="@bar"/></xsl:if>',
				'<xsl:if test="@foo"><xsl:value-of select="@bar"/></xsl:if>'
			],
			[
				'<xsl:if test="1+@foo"><xsl:value-of select="1+@foo"/></xsl:if>',
				'<xsl:if test="1+@foo"><xsl:value-of select="1+@foo"/></xsl:if>'
			],
		];
	}
}