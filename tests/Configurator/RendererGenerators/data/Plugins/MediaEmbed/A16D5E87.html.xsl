<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="p"><p><xsl:apply-templates/></p></xsl:template><xsl:template match="br"><br/></xsl:template><xsl:template match="et|i|st"/><xsl:template match="GROOVESHARK"><object type="application/x-shockwave-flash" typemustmatch="" width="250" height="{250-210*boolean(@songid)}" data="http://grooveshark.com/{substring('songWw',6-5*boolean(@songid),5)}idget.swf"><param name="allowfullscreen" value="true"/><param name="flashvars" value="playlistID={@playlistid}&amp;songID={@songid}"/><embed type="application/x-shockwave-flash" src="http://grooveshark.com/{substring('songWw',6-5*boolean(@songid),5)}idget.swf" width="250" height="{250-210*boolean(@songid)}" allowfullscreen="" flashvars="playlistID={@playlistid}&amp;songID={@songid}"/></object></xsl:template></xsl:stylesheet>