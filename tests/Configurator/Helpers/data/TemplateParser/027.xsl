<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" encoding="utf-8" />

	<!--
		Test that the element is not marked as empty="maybe" because all branches produce output
		The attribute is here to prevent some optimizations from taking place
	-->
	<xsl:template match="FOO">
		<xsl:element name="div">
			<xsl:choose>
				<xsl:when test="@foo">foo</xsl:when>
				<xsl:when test="@bar">bar</xsl:when>
				<xsl:otherwise><xsl:attribute name="id">id</xsl:attribute>baz</xsl:otherwise>
			</xsl:choose>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>