<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet exclude-result-prefixes="rss l dc admin content xsl"
  version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:rss="http://purl.org/rss/1.0/"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:media="http://search.yahoo.com/mrss/"
                xmlns:admin="http://webns.net/mvcb/"
                xmlns:l="http://purl.org/rss/1.0/modules/link/"
                xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <xsl:output omit-xml-declaration="yes" method="xml"/>

  <xsl:template match="/rss">
    <slideshow>
    <xsl:apply-templates select="channel" />
    </slideshow>
  </xsl:template>

  <xsl:template match="*">
    <xsl:for-each select="item">
      <slide
	link="{link}"
	image="{media:content/@url}"
	thumb="{media:thumbnail/@url}"
	title="{concat(title,' by ',media:credit)}"/>
    </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>
