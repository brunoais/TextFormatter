<?xml version="1.0" encoding="utf-8"?><xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:foo="urn:s9e:TextFormatter:foo" exclude-result-prefixes="foo"><xsl:output method="html" encoding="utf-8" indent="no"/><xsl:template match="br"><br/></xsl:template><xsl:template match="et|i|st"/><xsl:template match="foo:*"><xsl:element name="{local-name()}"><xsl:copy-of select="@*"/><xsl:apply-templates/></xsl:element></xsl:template></xsl:stylesheet>