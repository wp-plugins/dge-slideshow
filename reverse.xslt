<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet exclude-result-prefixes="rss l dc admin content xsl"
  version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:rss="http://purl.org/rss/1.0/"
                xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:media="http://search.yahoo.com/mrss"
                xmlns:admin="http://webns.net/mvcb/"
                xmlns:l="http://purl.org/rss/1.0/modules/link/"
                xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <xsl:output omit-xml-declaration="yes" indent="no" method="xml"/>
  <xsl:strip-space elements="*"/>
  <xsl:param name="limit" select="0" />
  <xsl:param name="ssid" />

  <xsl:template match="/rss">
    <xsl:apply-templates select="channel" />
  </xsl:template>

  <xsl:template match="item" mode="jsitem">
    <xsl:if test="not($limit) or position()&lt;=$limit">
      <xsl:value-of select="$ssid"/>.addSlide(new DGE_Slide("<xsl:value-of select="link"/>", "<xsl:value-of select="media:content/@url"/>"));
    </xsl:if>
  </xsl:template>

  <xsl:template match="item" mode="thumbs">
    <xsl:if test="not($limit) or position()&lt;=$limit">
      <li><img src="{media:thumbnail/@url}" alt="{title} by {media:credit}" title="{title} by {media:credit}" onclick="{$ssid}.select({position()-1})" /></li>
    </xsl:if>
  </xsl:template>

  <xsl:template match="channel">
    <script language="javascript">
    var X = 'reverse';
    var <xsl:value-of select="$ssid"/> = new DGE_SlideShow();
    <xsl:apply-templates mode="jsitem" select="item">
      <xsl:sort select="position()" data-type="number" order="descending" />
    </xsl:apply-templates>
    </script>
    <div class="dge-slideshow" id="{$ssid}">
      <div class="display"><div class="imgwrap"><a href=""><img src="" /></a></div></div>
      <div class="thumbnails"><ul>
        <xsl:apply-templates mode="thumbs" select="item">
          <xsl:sort select="position()" data-type="number" order="descending" />
        </xsl:apply-templates>
      </ul></div>
    </div>
    <script language="javascript"><xsl:value-of select="$ssid"/>.attach(document.getElementById("<xsl:value-of select="$ssid"/>"));</script>
  </xsl:template>

</xsl:stylesheet>
