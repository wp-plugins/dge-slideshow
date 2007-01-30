<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:x="http://www.w3.org/1999/xhtml"
    xmlns="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="xsl x">
  <xsl:include href="dge-slideshow.xsl" />
  <xsl:include href="replace.xsl" />
  <xsl:output omit-xml-declaration="yes" indent="no" method="xml"/>
  <xsl:strip-space elements="*"/>
  <xsl:param name="limit" select="0" />
  <xsl:param name="order" select="'ascending'" />
  <xsl:param name="ssid" />

  <xsl:template match="/x:html">
    <xsl:apply-templates select="id('right_area')" />
  </xsl:template>

  <xsl:template match="x:div">
      <xsl:call-template name="dge-ss-begin">
        <xsl:with-param name="ssid" select="$ssid"/>
      </xsl:call-template>
      <xsl:for-each select="x:div/x:div/x:a">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:variable name="bigimg">
          <xsl:call-template name="replace-string">
            <xsl:with-param name="text" select="x:img/@src"/>
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
      <xsl:for-each select="x:div/x:div/x:a">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:call-template name="dge-ss-thumb">
          <xsl:with-param name="limit" select="$limit"/>
          <xsl:with-param name="ssid" select="$ssid"/>
          <xsl:with-param name="src" select="x:img/@src"/>
          <xsl:with-param name="alt" select="x:img/@alt"/>
        </xsl:call-template>
      </xsl:for-each>
      <xsl:call-template name="dge-ss-end">
        <xsl:with-param name="ssid" select="$ssid"/>
      </xsl:call-template>
  </xsl:template>

</xsl:stylesheet>
