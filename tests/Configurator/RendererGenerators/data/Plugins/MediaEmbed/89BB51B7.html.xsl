<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="p"><p><xsl:apply-templates/></p></xsl:template><xsl:template match="br"><br/></xsl:template><xsl:template match="et|i|st"/><xsl:template match="LIVELEAK"><iframe width="560" height="315" src="http://www.liveleak.com/e/{@id}" allowfullscreen="" frameborder="0" scrolling="no"/></xsl:template></xsl:stylesheet>