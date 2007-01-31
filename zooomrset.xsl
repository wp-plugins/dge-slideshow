<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="xsl xhtml">
  <xsl:include href="dge-slideshow.xsl" />
  <xsl:include href="replace.xsl" />
  <xsl:output omit-xml-declaration="yes" indent="no" method="xml"/>
  <xsl:strip-space elements="*"/>
  <xsl:param name="limit" select="0" />
  <xsl:param name="order" select="'ascending'" />
  <xsl:param name="ssid" />

  <xsl:template match="/xhtml:html">
    <xsl:apply-templates select="xhtml:body/xhtml:div/xhtml:div/xhtml:div/xhtml:div/xhtml:div" />
  </xsl:template>

  <xsl:template match="*">
    <xsl:variable name="divid" select="@id" />
    <xsl:if test="$divid = 'right_area'">
      <xsl:call-template name="dge-ss-begin">
        <xsl:with-param name="ssid" select="$ssid"/>
      </xsl:call-template>
      <xsl:for-each select="xhtml:div/xhtml:div/xhtml:a">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:variable name="bigimg">
          <xsl:call-template name="replace-string">
            <xsl:with-param name="text" select="xhtml:img/@src"/>
            <xsl:with-param name="replace" select="'_s.jpg'"/>
            <xsl:with-param name="with" select="'.jpg'"/>
          </xsl:call-template>
        </xsl:variable>
        <xsl:call-template name="dge-ss-jsitem">
          <xsl:with-param name="limit" select="$limit"/>
          <xsl:with-param name="ssid" select="$ssid"/>
          <xsl:with-param name="url" select="concat('http://beta.zooomr.com',@href)"/>
          <xsl:with-param name="img" select="$bigimg"/>
        </xsl:call-template>
      </xsl:for-each>
      <xsl:call-template name="dge-ss-middle">
        <xsl:with-param name="ssid" select="$ssid"/>
      </xsl:call-template>
      <xsl:for-each select="xhtml:div/xhtml:div/xhtml:a">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:call-template name="dge-ss-thumb">
          <xsl:with-param name="limit" select="$limit"/>
          <xsl:with-param name="ssid" select="$ssid"/>
          <xsl:with-param name="src" select="xhtml:img/@src"/>
          <xsl:with-param name="alt" select="xhtml:img/@alt"/>
        </xsl:call-template>
      </xsl:for-each>
      <xsl:call-template name="dge-ss-end">
        <xsl:with-param name="ssid" select="$ssid"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>
