<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="xsl xhtml">
  <xsl:include href="zf-hack.xsl" />
  <xsl:output omit-xml-declaration="yes" method="xml"/>

  <xsl:template match="/xhtml:html">
    <slideshow xmlns="">
    <xsl:apply-templates select="xhtml:body/xhtml:div/xhtml:div/xhtml:div/xhtml:div/xhtml:div[@id='right_area']" />
    </slideshow>
  </xsl:template>

  <xsl:template match="*">
      <xsl:for-each select="xhtml:div/xhtml:div/xhtml:a">
        <xsl:call-template name="zf-hack">
          <xsl:with-param name="link" select="concat('http://beta.zooomr.com',@href)"/>
          <xsl:with-param name="thumb" select="xhtml:img/@src" />
          <xsl:with-param name="title" select="xhtml:img/@alt" />
        </xsl:call-template>
      </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>
