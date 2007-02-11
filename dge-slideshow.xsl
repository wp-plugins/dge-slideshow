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
    <div class="ss-container" id="{concat('ss-',$ssid)}">
    <div class="ss-menu">
      <ul>
	<li class="ss-first"><div><span>first</span></div></li>
	<li class="ss-prev"><div><span>previous</span></div></li>
	<li class="ss-play"><div><span>play</span></div></li>
	<li class="ss-pause"><div><span>pause</span></div></li>
	<li class="ss-next"><div><span>next</span></div></li>
	<li class="ss-last"><div><span>last</span></div></li>
      </ul>
    </div>
    <div class="ss-display"><p>preparing...</p></div>
    <div class="ss-thumbs"><ul>
      <xsl:for-each select="slide">
        <xsl:sort select="position()" data-type="number" order="{$order}" />
        <xsl:if test="not($limit) or position()&lt;=$limit">
          <li><div class="ss-preview" rel="ss-preview"><img src="{@thumb}" alt="{@title}" title="{@title}" /></div><div class="ss-slide" rel="FeedImage"><a href="{@link}"><xsl:value-of select="@image"/></a></div></li>
        </xsl:if>
      </xsl:for-each>
    </ul></div>
    </div>
    <script type="text/javascript">
new DGE_Paginator("ss-<xsl:value-of select="$ssid"/>", <xsl:value-of select="$repeat"/>, <xsl:value-of select="$delay"/>, <xsl:value-of select="$play"/>);
</script>
  </xsl:template>

</xsl:stylesheet>
