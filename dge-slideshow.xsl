<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.0"
	exclude-result-prefixes="xsl"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output omit-xml-declaration="yes" indent="no" method="xml"/>
  <xsl:strip-space elements="*"/>
  <xsl:param name="order" select="'ascending'" />
  <xsl:param name="repeat" select="0" />
  <xsl:param name="delay" select="3000" />
  <xsl:param name="limit" select="0" />
  <xsl:param name="play" select="0" />
  <xsl:param name="ssid" />

  <xsl:template match="/slideshow">
    <script language="javascript">
    var <xsl:value-of select="$ssid"/> = new DGE_SlideShow("<xsl:value-of select="$ssid"/>", <xsl:value-of select="$repeat"/>, <xsl:value-of select="$delay"/>, <xsl:value-of select="$play"/>);
    <xsl:for-each select="slide">
      <xsl:sort select="position()" data-type="number" order="{$order}" />
      <xsl:if test="not($limit) or position()&lt;=$limit">
        <xsl:value-of select="$ssid"/>.addSlide(new DGE_Slide("<xsl:value-of select="@link"/>", "<xsl:value-of select="@image"/>"));
      </xsl:if>
    </xsl:for-each>
    </script>
    <div class="ss-container" id="{concat('ss-',$ssid)}">
    <div class="ss-menu">
      <ul>
	<li class="ss-first"><span>first</span></li>
	<li class="ss-prev"><span>previous</span></li>
	<li class="ss-play"><span>play</span></li>
	<li class="ss-pause"><span>pause</span></li>
	<li class="ss-next"><span>next</span></li>
	<li class="ss-last"><span>last</span></li>
      </ul>
    </div>
    <div class="ss-display">
      <div class="ss-imgwrap"><a href=""><img src="" /></a></div>
    </div>
    <div class="ss-thumbs"><ul>
      <xsl:for-each select="slide">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:if test="not($limit) or position()&lt;=$limit">
          <li><img src="{@thumb}" alt="{@title}" title="{@title}" onclick="{$ssid}.select({position()-1})" /></li>
        </xsl:if>
      </xsl:for-each>
    </ul></div>
    </div>
    <script language="javascript"><xsl:value-of select="$ssid"/>.attach(document.getElementById("ss-<xsl:value-of select="$ssid"/>"));</script>
  </xsl:template>

</xsl:stylesheet>
