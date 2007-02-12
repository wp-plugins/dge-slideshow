<?php
/*
Plugin Name: DGE_SlideShow
Plugin URI: http://dave.stufftoread.net/slideshow/
Description: Turns online images (e.g. Flickr or Zooomr image feeds) into a slideshow. Fairly flexible, due to use of XSLT. Requires <a href="http://dev.wp-plugins.org/wiki/dge-inlinerss">DGE_InlineRSS</a> 0.92.
Version: 0.39
Author: Dave E
Author URI: http://dave.stufftoread.net/
*/

// This is the main slideshow function that does the real work. See
// dge_ss_contentFilter() below for what to put in the $params
// array. Just use corresponding keys and values in $params to get the
// same result, e.g.
// 
// DGE_SlideShow('ss1','http://blah.com/feed', array('limit'=>5));
//
function DGE_SlideShow($ssid, $url, $params=array())
{
    // Steal these settings from inlinerss
    $cacheprefix = get_option('dge_irss_cacheprefix');
    $cachepath = get_option('dge_irss_cachepath');

    // Some other variables.
    $xsltParams = array();
    $xsltParams['ssid'] = $ssid;
    $inlineRSSname = 'dge-ss-'.$ssid;
    $cachefile = ABSPATH . $cachepath . '/' . $cacheprefix . $inlineRSSname . '.html';
    $stage1xsl = "dge-slideshow/rssfeed.xsl";
    $stage2xsl = "dge-slideshow/dge-slideshow.xsl";

    // defaults
    $repeat = 0;
    $delay = 3000;
    $play = 0;

    // First up must be a check for a preset so that other parameters
    // passed to this function can override the preset.
    if (array_key_exists('preset', $params))
    {
	if (($presets = get_option('dge_ss_presets')) &&
	    ($preset = $presets[$params['preset']]))
	{
	    $params = array_merge($preset, $params);
	}
    }
    if (array_key_exists('timeout', $params))
	$timeout = intval($params['timeout']);
    else
	$timeout = get_option('dge_ss_def_timeout');
    if (array_key_exists('reverse', $params))
	$xsltParams['order'] = "descending";
    if (array_key_exists('limit', $params))
	$xsltParams['limit'] = $params['limit'];
    if (array_key_exists('delay', $params))
	$delay = $params['delay'];
    if (array_key_exists('repeat', $params))
	$repeat = 1;
    if (array_key_exists('norepeat', $params))
	$repeat = 0;
    if (array_key_exists('play', $params))
	$play = 1;
    if (array_key_exists('pause', $params))
	$play = 0;
    // This must go last, to override any preset xsl files
    if (array_key_exists('xslt', $params))
	$stage1xsl = $params['xslt'];

    // Look for an existing cache and find out how old it is
    if ( file_exists($cachefile))
    {
	$age = time() - filectime($cachefile);
	$exists = TRUE;  
    }
    else
    {
	$age = 0;
	$exists = FALSE;
    }

    // If there's no file, or the existing one's old, run through
    // inlineRSS and create a new cache file.
    if ( $exists == FALSE or $age > $timeout * 60 )
    {
	$stage1xml = "<?xml version=\"1.0\"?>\n";
	$stage1xml .= DGE_InlineRSS($inlineRSSname, $url,
				    array('timeout'=>0,
					  'xslt'=>$stage1xsl));
	$stage2xml = DGE_InlineRSS($inlineRSSname, '',
				   array('xml'=>$stage1xml,
					 'xslt'=>$stage2xsl),
				   $xsltParams);
        if (empty($stage2xml))
	{
	    if ($exists == FALSE)
	    {
		return "<!-- Error creating slideshow $ssid: inlineRSS failed. -->\n";
	    } 
	    $writefile = FALSE;
        }
	else
	{
	    // This is a hack to replace the _m.jpg in the filenames
	    // supplied in the feed with just .jpg. This is so that we
	    // get a bigger, better quality image in the slideshow.
	    // The _o.jpg filter cuts out loading of enormous original
	    // photos from Flickr streams.
	    if (get_option('dge_ss_mo_snip'))
		$output = preg_replace('/_[mo]\.jpg/', '.jpg', $stage2xml);
	    else
		$output = $stage2xml;
	    $writefile = TRUE;
        }

	$output = "
    <!-- DGE_SlideShow ".get_option('dge_ss_version')." -->
    <div class=\"ss-container\" rel=\"ss-instance\" id=\"ss-$ssid\">
    <div class=\"ss-menu\" rel=\"ss-menu\">
      <ul>
	<li class=\"ss-first\"><div><span>first</span></div></li>
	<li class=\"ss-prev\"><div><span>previous</span></div></li>
	<li class=\"ss-play\"><div><span>play</span></div></li>
	<li class=\"ss-pause\"><div><span>pause</span></div></li>
	<li class=\"ss-next\"><div><span>next</span></div></li>
	<li class=\"ss-last\"><div><span>last</span></div></li>
      </ul>
    </div>
    <div class=\"ss-display\" rel=\"ss-display\"><p></p></div>
    <div class=\"ss-thumbs\" rel=\"ss-thumbs\"><ul>
      $output
    </ul></div>
    </div>
    <script type=\"text/javascript\">
    new DGE_Paginator(\"ss-$ssid\", $repeat, $delay, $play);
    </script>
    <!-- end DGE_SlideShow -->\n";

	if ($writefile)
	{
	    if (!($handle = fopen($cachefile,'w')))
		    return "<!-- Error opening $cachefile - possible permissions issue - directory permissions are " . substr(sprintf('%o', fileperms(ABSPATH . "wp-content/")), -4) ." -->\n";
	    fwrite($handle,$output);
	    fclose($handle);
	}
    }
    else
    {
	// We have a local copy, so fetch it.
	$output = file_get_contents($cachefile);
    }
    return $output;
}

// This is a Wordpress content filter that replaces occurrences of the
// following format:
//
//  !slideshow!<id>!<url>[!<option>=<val>;<option>=<val>...]!
//
// with the rss feed reformatted for the slideshow javascript.
// 
// Parameters:
//  <id>   Required field. Must be unique for each different
//         slideshow.
//  <url>  Required field. The url of the image feed.
//  limit=<number>
//         Optional field. Limits the number of images
//         displayed. Default is 0, indicating no limit.
//  preset=<preset name>
//         Optional field. Applies the options defined in the named
//         preset, before overriding them with any other options
//         passed.
//  reverse
//         Optional field. Reverses the order of the images in the
//         feed. Default is to not reverse the feed.
//  timeout=<minutes>
//         Optional field. The time in minutes before the cached html
//         is refreshed. Default is 60 minutes.
function dge_ss_contentFilter($content = '')
{
    $find[] = "//";
    $replace[] = "";

    preg_match_all('/!slideshow!([^!]+)!([^!]+)!([^\n!]+!)?/', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $val)
    {
	if (is_feed() || $doing_rss)
	{
	    $find[] = "^$val[0]^";
	    $replace[] = "<i>[Slideshow removed from feed]</i>";
	}
	else
	{
	    $find[] = "^".str_replace('?','\?',$val[0])."^";
	    $params = array();
	    if ($val[3] != '')
	    {
		// knock off trailing '!'
		$val[3] = substr($val[3], 0, strlen($val[3])-1);
		$params = dge_irss_explodeParams($val[3]);
	    }
	    $val[2] = str_replace('&#038;','&',$val[2]);
	    $replace[] = DGE_SlideShow($val[1], $val[2], $params);
	}
    }
    return preg_replace($find, $replace, $content);
}

// Filters out inline calls to the slideshow.
function dge_ss_securityFilter($content = '')
{
    $find[] = "//";
    $replace[] = "";

    preg_match_all('/!slideshow!([^!]+)!([^!]+)!([^\n!]+!)?/', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $val)
    {
	$find[] = "^$val[0]^";
	$replace[] = "<!-- slideshow call removed -->";
    }
    return preg_replace($find, $replace, $content);
}

// This is a Wordpress action to insert the javascript and css into
// the page header.
function dge_ss_insertHeader()
{
    $path = get_option('siteurl').'/'.PLUGINDIR.'/dge-slideshow/';
    echo "\n<!-- DGE_SlideShow includes -->\n";
    if (get_option('dge_ss_inc_css'))
    {
	echo '<link rel="stylesheet" href="'.$path.
	    'dge-slideshow.css" type="text/css" media="screen" />'.
	    "\n";
    }
    echo '<script type="text/javascript" src="'.$path.
	'js/dge-xp.js"></script>'."\n";
    echo '<script type="text/javascript" src="'.$path.
	'js/dge-paginator.js"></script>'."\n";
    echo "<!-- page handlers -->\n";
    echo '<script type="text/javascript" src="'.$path.
	'handlers/FeedImage.js"></script>'."\n";
    echo "<!-- end DGE_SlideShow includes -->\n";
}

function dge_ss_admin()
{
    if (function_exists('add_options_page'))
    {
	add_options_page('Slideshow Options', 'Slideshow', 8, basename(__FILE__), 'dge_ss_subpanel');
    }
}

function dge_ss_posttoggle($postvar, $option, $onString, $offString)
{
    if (isset($_POST[$postvar]))
    {
	if (!get_option($option))
	{
	    update_option($option, 1);
	    return $onString;
	}
    }
    else if (get_option($option))
    {
	update_option($option, 0);
	return $offString;
    }
    return '';
}

function dge_ss_subpanel()
{
    $presets = get_option('dge_ss_presets');
    if (!$presets) $presets = array();

    // ----------------------------------------------------------------
    // PARSE $_POST PARAMETERS
    // ----------------------------------------------------------------
    if (isset($_POST['info_update']))
    {
	$updateText = '';
	// ------------------------------------------------------------
	// DEFAULTS
	// ------------------------------------------------------------
	if (isset($_POST['def_timeout']))
	{
	    $timeout = $_POST['def_timeout'];
	    if ($timeout == '')
	    {
		$updateText .= "<p><strong>Default timeout not updated. Invalid input.</strong></p>\n";
	    }
	    else
	    {
		$timeout = intval($timeout);
		if ($timeout != get_option('dge_ss_def_timeout'))
		{
		    update_option('dge_ss_def_timeout', $timeout);
		    $updateText .= "<p>Default timeout updated.</p>\n";
		}
	    }
	}
	// Include CSS in header toggled?
	$updateText .= dge_ss_posttoggle(
	    'inc_css', 'dge_ss_inc_css',
	    "<p>Including default CSS rules in header.</p>\n",
	    "<p>No longer including default CSS rules in header.</p>\n");
	// Standard image size
	$updateText .= dge_ss_posttoggle(
	    'mo_snip', 'dge_ss_mo_snip',
	    "<p>Using standard image size by default.</p>\n",
	    "<p>Using feed's image size by default.</p>\n");

	// ------------------------------------------------------------
	// PRESETS
	// ------------------------------------------------------------
	$updatepresets = 0;
	// Check for new presets
	if ($_POST['pre_new_name']!='' && $_POST['pre_new_value']!='')
	{
	    $name = $_POST['pre_new_name'];
	    $value = $_POST['pre_new_value'];
	    if (array_key_exists($name, $presets))
	    {
		$updateText .= "<p><strong>New preset '$name' already exists. Not updated.</strong></p>\n";
	    }
	    else
	    {
		$presets[$name] = dge_irss_explodeParams($value);
		$updateText .= "<p>New preset '$name' added.</p>\n";
		$updatepresets = 1;
	    }
	}
	// Check for updates to existing presets.
	foreach ($presets as $name=>$preset)
	{
	    $postkey = "pre_upd_".$name;
	    $current = dge_irss_implodeParams($preset);
	    if (array_key_exists($postkey,$_POST) &&
		$_POST[$postkey] != $current)
	    {
		$update = $_POST[$postkey];
		if ($update == '')
		{
		    unset($presets[$name]);
		    $updateText .= "<p>Preset '$name' removed.</p>\n";
		}
		else
		{
		    $presets[$name]=dge_irss_explodeParams($update);
		    $updateText .= "<p>Preset '$name' updated.</p>\n";
		}
		$updatepresets = 1;
	    }
	}
	// Do all updates to the presets in one go.
	if ($updatepresets)
	{
	    update_option('dge_ss_presets', $presets);
	}
	// Output $updateText
	if ($updateText != '')
	    echo "<div class=\"updated\">\n$updateText</div>";
    }

    // ----------------------------------------------------------------
    // DISPLAY FORM
    // ----------------------------------------------------------------
 ?>
<div class="wrap">
  <form method="post">
    <h2>Slideshow Options</h2>

    <h3>Defaults</h3>
    <div><table>
    <tr><td>Timeout (mins)</td><td><input type="text" name="def_timeout" value="<?php echo get_option('dge_ss_def_timeout'); ?>"/></td><td><i>The default time to wait before refreshing the feed cache.</i></td></tr>
    <tr><td>Include CSS</td><td><input type="checkbox" name="inc_css" value="1"<?php if (get_option('dge_ss_inc_css')) echo ' checked="true"'; ?>/></td><td><i>Includes default CSS rules in the header of each page.</i></td></tr>
    <tr><td>Standard sizes</td><td><input type="checkbox" name="mo_snip" value="1"<?php if (get_option('dge_ss_mo_snip')) echo ' checked="true"'; ?>/></td><td><i>Tries to pick the standard size image (usuall max 500 in any dimension). Basically, it avoids loading of enormous images from Flickr feeds, or too small images from Zooomr feeds.</i></td></tr>
    </table></div>

    <h3>Presets</h3>
    <div><table>
<?php
if ($presets && count($presets)>0)
{
    echo "        <tr><td colspan=\"3\"><b>Existing Presets</b></td><tr>\n";
    echo "        <tr><td colspan=\"3\"><i>To remove an existing preset, just delete the settings in the text field.</i></td></tr>\n";
    foreach ($presets as $name=>$value)
    {
	echo "      <tr><td>$name</td>";
	echo "<td colspan=\"2\"><input type=\"text\" name=\"pre_upd_$name\" size=\"50\" value=\"".
	     dge_irss_implodeParams($value).'"/>';
	echo "</td></tr>\n";
    }
}
?>
        <tr><td colspan="3"><b>Add preset</b></td><tr>
        <tr>
          <td>Name</td>
          <td><input type="text" name="pre_new_name"/></td>
	</tr>
        <tr>
          <td>Value</td>
          <td colspan="2"><input type="text" size="50" name="pre_new_value"/></td>
        </tr>
    </table></div>
    <div class="submit">
      <input type="submit" name="info_update" value="Update options &raquo;" />
    </div>
  </form>
</div>
<?php
}

function dge_ss_activate()
{
    // This is version...
    $nversion = 0.39; // (n for new)
    // Get the previous version
    $pversion = get_option('dge_ss_version');

    // Check for first-time install
    if (!$pversion)
	add_option('dge_ss_version', $nversion, 'Version', 'no');
    else
	update_option('dge_ss_version', $nversion);

    $pversion = floatval($pversion);

    // Version 0.2 options
    if ($pversion < 0.2)
    {
	add_option('dge_ss_def_timeout', 60, 'Default timeout', 'yes');
	add_option('dge_ss_inc_css', 1, 'Include CSS', 'yes');
	add_option('dge_ss_mo_snip', 1, 'Standard image', 'no');
	add_option('dge_ss_presets', array(), 'Presets', 'no');
    }
}

// These add the filters and action to Wordpress.
add_filter('comment_author', 'dge_ss_securityFilter');
add_filter('comment_email', 'dge_ss_securityFilter');
add_filter('comment_text', 'dge_ss_securityFilter');
add_filter('comment_url', 'dge_ss_securityFilter');
add_filter('the_content', 'dge_ss_contentFilter');
add_action('wp_head', 'dge_ss_insertHeader');
add_action('admin_menu', 'dge_ss_admin');
add_action('activate_dge-slideshow/dge-slideshow.php', 'dge_ss_activate');
?>
