<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xsl"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output omit-xml-declaration="yes" indent="no" method="xml"/>
  <xsl:strip-space elements="*"/>
  <xsl:param name="order" select="'ascending'" />
  <xsl:param name="limit" select="0" />

  <xsl:template match="/slideshow">
      <xsl:for-each select="slide">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:if test="not($limit) or position()&lt;=$limit">
          <li><div class="ss-preview" rel="ss-preview"><img src="{@thumb}" alt="{@title}" title="{@title}" /></div><div class="ss-slide" rel="FeedImage"><a href="{@link}"><xsl:value-of select="@image"/></a></div></li>
        </xsl:if>
      </xsl:for-each>
  </xsl:template>

</xsl:stylesheet>
