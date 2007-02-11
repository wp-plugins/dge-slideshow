<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="replace.xsl" />

  <xsl:template name="zf-hack">
    <xsl:param name="link" />
    <xsl:param name="thumb" />
    <xsl:param name="title" />
    <xsl:variable name="large">
      <xsl:call-template name="replace-string">
        <xsl:with-param name="text" select="$thumb"/>
        <xsl:with-param name="replace" select="'_s.jpg'"/>
        <xsl:with-param name="with" select="'.jpg'"/>
      </xsl:call-template>
    </xsl:variable>
    <slide link="{$link}" image="{$large}" thumb="{$thumb}" title="{$title}" />
  </xsl:template>

</xsl:stylesheet>
