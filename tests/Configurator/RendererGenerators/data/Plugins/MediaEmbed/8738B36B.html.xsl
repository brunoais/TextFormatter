<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="p"><p><xsl:apply-templates/></p></xsl:template><xsl:template match="br"><br/></xsl:template><xsl:template match="et|i|st"/><xsl:template match="TWITCH"><object type="application/x-shockwave-flash" typemustmatch="" width="620" height="378" data="http://www.twitch.tv/widgets/{substring('archl',5-4*boolean(@archive_id|@chapter_id),4)}ive_embed_player.swf"><param name="allowfullscreen" value="true"/><param name="flashvars"><xsl:attribute name="value">channel=<xsl:value-of select="@channel"/><xsl:if test="@archive_id">&amp;archive_id=<xsl:value-of select="@archive_id"/></xsl:if><xsl:if test="@chapter_id">&amp;chapter_id=<xsl:value-of select="@chapter_id"/></xsl:if></xsl:attribute></param><embed type="application/x-shockwave-flash" width="620" height="378" src="http://www.twitch.tv/widgets/{substring('archl',5-4*boolean(@archive_id|@chapter_id),4)}ive_embed_player.swf" allowfullscreen=""/></object></xsl:template></xsl:stylesheet>