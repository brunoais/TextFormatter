<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="br"><br/></xsl:template><xsl:template match="et|i|st"/><xsl:template match="YOUTUBE"><iframe width="853" height="505" src="http://www.youtube.com/embed/{@id}" frameborder="0" allowfullscreen=""/></xsl:template></xsl:stylesheet>