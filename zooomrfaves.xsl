<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet exclude-result-prefixes="xsl"
  version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:include href="zf-hack.xsl" />
  <xsl:output method="xml"/>

  <xsl:template match="/html">
    <slideshow>
    <xsl:apply-templates select="body/div/div" />
    </slideshow>
  </xsl:template>

  <xsl:template match="div[@id='content']">
    <xsl:for-each select="div/div/div/div/a">
      <xsl:call-template name="zf-hack">
        <xsl:with-param name="link" select="concat('http://beta.zooomr.com',@href)"/>
        <xsl:with-param name="thumb" select="img/@src" />
        <xsl:with-param name="title" select="img/@alt" />
      </xsl:call-template>
    </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>
