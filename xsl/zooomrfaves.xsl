<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="xsl xhtml">
  <xsl:include href="zf-hack.xsl" />
  <xsl:output omit-xml-declaration="yes" method="xml"/>

  <xsl:template match="/html">
    <slideshow xmlns="">
    <xsl:apply-templates select="id('content')" />
    </slideshow>
  </xsl:template>

  <xsl:template match="div">
    <xsl:for-each select="table/tr/td/table/tr/td/div/div/a">
        <xsl:call-template name="zf-hack">
          <xsl:with-param name="link" select="concat('http://www.zooomr.com',@href)"/>
          <xsl:with-param name="thumb" select="img/@src" />
          <xsl:with-param name="title" select="img/@alt" />
        </xsl:call-template>
    </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>
