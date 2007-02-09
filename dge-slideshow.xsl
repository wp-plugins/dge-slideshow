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
    <div class="ss-menu"><ul>
      <li><a href="javascript:void(0)" onclick="{$ssid}.play();return false"><span>play</span></a></li>
      <li><a href="javascript:void(0)" onclick="{$ssid}.pause();return false"><span>pause</span></a></li>
      <li><a href="javascript:void(0)" onclick="{$ssid}.nextSlide();return false"><span>next</span></a></li>
      <li><a href="javascript:void(0)" onclick="{$ssid}.prevSlide();return false"><span>previous</span></a></li>
    </ul></div>
    <div class="ss-display">
      <div class="ss-imgwrap"><a href=""><img src="" /></a></div></div>
      <div class="ss-thumbs"><ul>
      <xsl:for-each select="slide">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:if test="not($limit) or position()&lt;=$limit">
          <li><img src="{@thumb}" alt="{@title}" title="{@title}" onclick="{$ssid}.select({position()-1})" /></li>
        </xsl:if>
      </xsl:for-each>
      </ul></div></div>
    <script language="javascript"><xsl:value-of select="$ssid"/>.attach(document.getElementById("ss-<xsl:value-of select="$ssid"/>"));</script>
  </xsl:template>

</xsl:stylesheet>
