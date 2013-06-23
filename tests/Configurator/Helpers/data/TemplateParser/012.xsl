<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="utf-8" />

	<xsl:template match="QUOTE">
		<blockquote>
			<xsl:if test="not(@author)">
				<xsl:attribute name="class">uncited</xsl:attribute>
			</xsl:if>
			<div>
				<xsl:if test="@author">
					<cite><xsl:value-of select="@author"/> wrote:</cite>
				</xsl:if>
				<xsl:apply-templates />
			</div>
		</blockquote>
	</xsl:template>

</xsl:stylesheet>