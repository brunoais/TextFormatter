<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="br"><br/></xsl:template><xsl:template match="et|i|st"/><xsl:template match="DAILYMOTION"><object type="application/x-shockwave-flash" typemustmatch="" width="560" height="315" data="http://www.dailymotion.com/swf/{@id}"><param name="allowFullScreen" value="true"/><embed type="application/x-shockwave-flash" src="http://www.dailymotion.com/swf/{@id}" width="560" height="315" allowfullscreen=""/></object></xsl:template></xsl:stylesheet>