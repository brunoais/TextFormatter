<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="br"><br/></xsl:template><xsl:template match="et|i|st"/><xsl:template match="E"><xsl:choose><xsl:when test=".=':&quot;)'"><img src="s.png"/></xsl:when><xsl:otherwise><xsl:value-of select="."/></xsl:otherwise></xsl:choose></xsl:template></xsl:stylesheet>