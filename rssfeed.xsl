<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet exclude-result-prefixes="rss l dc admin content xsl"
  version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:rss="http://purl.org/rss/1.0/"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:media="http://search.yahoo.com/mrss/"
                xmlns:admin="http://webns.net/mvcb/"
                xmlns:l="http://purl.org/rss/1.0/modules/link/"
                xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <xsl:include href="ss-includes.xsl" />
  <xsl:output omit-xml-declaration="yes" indent="no" method="xml"/>
  <xsl:strip-space elements="*"/>
  <xsl:param name="limit" select="0" />
  <xsl:param name="order" select="'ascending'" />
  <xsl:param name="ssid" />

  <xsl:template match="/rss">
    <xsl:apply-templates select="channel" />
  </xsl:template>

  <xsl:template match="channel">
    <xsl:call-template name="dge-ss-begin">
      <xsl:with-param name="ssid" select="$ssid"/>
    </xsl:call-template>
    <xsl:for-each select="item">
      <xsl:sort select="position()" data-type="number" order="{$order}" />
      <xsl:call-template name="dge-ss-jsitem">
        <xsl:with-param name="limit" select="$limit"/>
        <xsl:with-param name="ssid" select="$ssid"/>
        <xsl:with-param name="url" select="link"/>
        <xsl:with-param name="img" select="media:content/@url"/>
      </xsl:call-template>
    </xsl:for-each>
    <xsl:call-template name="dge-ss-middle">
      <xsl:with-param name="ssid" select="$ssid"/>
    </xsl:call-template>
    <xsl:for-each select="item">
      <xsl:sort select="position()" data-type="number" order="{$order}" />
      <xsl:call-template name="dge-ss-thumb">
        <xsl:with-param name="limit" select="$limit"/>
        <xsl:with-param name="ssid" select="$ssid"/>
        <xsl:with-param name="src" select="media:thumbnail/@url"/>
        <xsl:with-param name="alt" select="concat(title,' by ',media:credit)"/>
      </xsl:call-template>
    </xsl:for-each>
    <xsl:call-template name="dge-ss-end">
      <xsl:with-param name="ssid" select="$ssid"/>
    </xsl:call-template>
  </xsl:template>

</xsl:stylesheet>
