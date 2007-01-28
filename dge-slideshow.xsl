<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template name="dge-ss-begin">
    <xsl:param name="ssid" />
    <xsl:text disable-output-escaping="yes">&lt;script language="javascript"></xsl:text>
    var <xsl:value-of select="$ssid"/> = new DGE_SlideShow();
  </xsl:template>

  <xsl:template name="dge-ss-middle">
    <xsl:param name="ssid" />
    <xsl:text disable-output-escaping="yes">&lt;/script>&lt;div class="dge-slideshow" id="dge-ss-</xsl:text><xsl:value-of select="$ssid"/><xsl:text disable-output-escaping="yes">"></xsl:text>
      <div class="display"><div class="imgwrap"><a href=""><img src="" /></a></div></div>
      <xsl:text disable-output-escaping="yes">&lt;div class="thumbnails">&lt;ul></xsl:text>
  </xsl:template>

  <xsl:template name="dge-ss-end">
    <xsl:param name="ssid" />
    <xsl:text disable-output-escaping="yes">&lt;/ul>&lt;/div>&lt;/div></xsl:text>
    <script language="javascript"><xsl:value-of select="$ssid"/>.attach(document.getElementById("dge-ss-<xsl:value-of select="$ssid"/>"));</script>
  </xsl:template>

  <xsl:template name="dge-ss-jsitem">
    <xsl:param name="limit" select="0" />
    <xsl:param name="ssid" />
    <xsl:param name="url" />
    <xsl:param name="img" />
    <xsl:if test="not($limit) or position()&lt;=$limit">
      <xsl:value-of select="$ssid"/>.addSlide(new DGE_Slide("<xsl:value-of select="$url"/>", "<xsl:value-of select="$img"/>"));
    </xsl:if>
  </xsl:template>

  <xsl:template name="dge-ss-thumb">
    <xsl:param name="limit" select="0" />
    <xsl:param name="ssid" />
    <xsl:param name="src" />
    <xsl:param name="alt" />
    <xsl:if test="not($limit) or position()&lt;=$limit">
      <li><img src="{$src}" alt="{$alt}" title="{$alt}" onclick="{$ssid}.select({position()-1})" /></li>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>
