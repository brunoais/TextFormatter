<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="utf-8" />

	<xsl:template match="B">
		<b title="{{foo}}{@bar}&lt;baz&gt;">
			<xsl:apply-templates/>
		</b>
	</xsl:template>

</xsl:stylesheet>