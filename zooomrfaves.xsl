<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet exclude-result-prefixes="xsl"
  version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:include href="ss-includes.xsl" />
  <xsl:include href="replace.xsl" />
  <xsl:output omit-xml-declaration="yes" indent="no" method="xml"/>
  <xsl:strip-space elements="*"/>
  <xsl:param name="limit" select="0" />
  <xsl:param name="order" select="'ascending'" />
  <xsl:param name="ssid" />

  <xsl:template match="/html">
    <xsl:apply-templates select="body/div/div" />
  </xsl:template>

  <xsl:template match="div">
    <xsl:variable name="divid" select="@id" />
    <xsl:if test="$divid = 'content'">
      <xsl:call-template name="dge-ss-begin">
        <xsl:with-param name="ssid" select="$ssid"/>
      </xsl:call-template>
      <xsl:for-each select="div/div/div/div/a">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:variable name="bigimg">
          <xsl:call-template name="replace-string">
            <xsl:with-param name="text" select="img/@src"/>
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
      <xsl:for-each select="div/div/div/div/a/img">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:call-template name="dge-ss-thumb">
          <xsl:with-param name="limit" select="$limit"/>
          <xsl:with-param name="ssid" select="$ssid"/>
          <xsl:with-param name="src" select="@src"/>
          <xsl:with-param name="alt" select="@alt"/>
        </xsl:call-template>
      </xsl:for-each>
      <xsl:call-template name="dge-ss-end">
        <xsl:with-param name="ssid" select="$ssid"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>
