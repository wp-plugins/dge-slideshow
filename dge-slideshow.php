<?php
/*
Plugin Name: DGE_SlideShow
Plugin URI: http://dev.wp-plugins.org/wiki/dge-slideshow
Description: Turns a Flickr or Zooomr image feed into a slideshow. Requires <a href="http://dave.stufftoread.net/2006/07/13/scratch-that-xslt-is-the-way-forward/">this modified version</a> of the <a href="http://www.iconophobia.com/wordpress/?page_id=55">inlineRSS</a> plugin.
Version: 0.2
Author: Dave Elcock
Author URI: http://dave.stufftoread.net/
*/

// This is the main slideshow function that does the real work. See
// DGE_SlideShow_contentFilter() below for what to put in the $params
// array.
function DGE_SlideShow_format($ssid, $url, $params=array())
{
    // Oh bugger, these are just cut-and-pasted from inlineRSS.php.
    // It'd be better if there was some interface to ask inlineRSS
    // what its settings were.
    $paramfile = 'inlineRSS.txt';    // Configuration file of feeds
    $fileprefix = 'in_';             // What feed casual names get prefixed with
	
    // Some other variables.
    $xsltParams = array();
    $xsltParams['ssid'] = $ssid;
    $inlineRSSname = "dge-slideshow-".$ssid;
    $cachefile = ABSPATH . "wp-content/" . $fileprefix . $inlineRSSname . ".html";

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
	$xsltFile = "dge-slideshow/reverse.xslt";
    else
	$xsltFile = "dge-slideshow/forward.xslt";
    if (array_key_exists('limit', $params))
	$xsltParams['limit'] = $params['limit'];

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
	$inlineRSSout = inlineRSSparserWithParams(
		$inlineRSSname, $url, 1,
		$xsltFile, $xsltParams);
        if (empty($inlineRSSout))
	{
	    if ($exists == FALSE)
	    {
		die("Error creating slideshow $ssid: inlineRSS failed.");
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
		$output = preg_replace('/_[mo]\.jpg/', '.jpg', $inlineRSSout);
	    else
		$output = $inlineRSSout;
	    $writefile = TRUE;
        }

	if ($writefile)
	{
	    if (!($handle = fopen($cachefile,'w')))
		    die ("Error opening $cachefile - possible permissions issue - directory permissions are " . substr(sprintf('%o', fileperms(ABSPATH . "wp-content/")), -4));
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

function DGE_SlideShow_explodeParams($paramString)
{
    $params = array();
    foreach (explode(';', $paramString) as $param)
    {
	list($arg,$v) = explode('=', $param);
	$params[$arg] = $v;
    }
    return $params;
}

function DGE_SlideShow_implodeParams($params)
{
    $result = array();
    foreach ($params as $key=>$val)
    {
	if ($val == '') $result[]=$key;
	else $result[] = "$key=$val";
    }
    return implode(';', $result);
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
//  reverse
//         Optional field. Reverses the order of the images in the
//         feed. Default is to not reverse the feed.
//  timeout=<minutes>
//         Optional field. The time in minutes before the cached html
//         is refreshed. Default is 60 minutes.
function DGE_SlideShow_contentFilter($content = '')
{
    $find[] = "//";
    $replace[] = "";

    preg_match_all('/!slideshow!([^!]+)!([^!]+)!([^\n!]+!)?/', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $val)
    {
	$find[] = "^".str_replace('?','\?',$val[0])."^";
	$params = array();
	if ($val[3] != '')
	{
	    // knock off trailing '!'
	    $val[3] = substr($val[3], 0, strlen($val[3])-1);
	    $params = DGE_SlideShow_explodeParams($val[3]);
	}
	$val[2] = str_replace('&#038;','&',$val[2]);
	$replace[] = DGE_SlideShow_format($val[1], $val[2], $params);
    }
    return preg_replace($find, $replace, $content);
}

// Filters out inline calls to the slideshow.
function DGE_SlideShow_securityFilter($content = '')
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

// This is for the php template-calling support. See the comments
// above the content filter function for details of the possible
// values for $params. Just use corresponding keys and values in
// $params to get the same result, e.g.
// 
// DGE_SlideShow('ss1','http://blah.com/feed', array('limit'=>5));
//
function DGE_SlideShow($ssid, $url, $params=array())
{
    echo DGE_SlideShow_format($ssid, $url, $params);
}

// This is a Wordpress action to insert the javascript and css into
// the page header.
function DGE_SlideShow_insertHeader()
{
    $path = '/wordpress/wp-content/plugins/dge-slideshow/';
    echo "\n<!-- DGE_SlideShow includes -->\n";
    if (get_option('dge_ss_inc_css'))
    {
	echo '<link rel="stylesheet" href="'.$path.
	    'dge-slideshow.css" type="text/css" media="screen" />'.
	    "\n";
    }
    echo '<script type="text/javascript" src="'.$path.
	'dge-slideshow.js"></script>'."\n\n";
}

function DGE_SlideShow_admin()
{
    if (function_exists('add_options_page'))
    {
	add_options_page('Slideshow Options', 'Slideshow', 8, basename(__FILE__), 'DGE_SlideShow_subpanel');
    }
}

function DGE_SlideShow_posttoggle($postvar, $option, $onString, $offString)
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

function DGE_SlideShow_subpanel()
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
	$updateText .= DGE_SlideShow_posttoggle(
	    'inc_css', 'dge_ss_inc_css',
	    "<p>Including default CSS rules in header.</p>\n",
	    "<p>No longer including default CSS rules in header.</p>\n");
	// Standard image size
	$updateText .= DGE_SlideShow_posttoggle(
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
		$presets[$name] = DGE_SlideShow_explodeParams($value);
		$updateText .= "<p>New preset '$name' added.</p>\n";
		$updatepresets = 1;
	    }
	}
	// Check for updates to existing presets.
	foreach ($presets as $name=>$preset)
	{
	    $postkey = "pre_upd_".$name;
	    $current = DGE_SlideShow_implodeParams($preset);
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
		    $presets[$name]=DGE_SlideShow_explodeParams($update);
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
	     DGE_SlideShow_implodeParams($value).'"/>';
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

function DGE_SlideShow_activate()
{
    // This is version...
    $nversion = 0.2; // (n for new)
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
add_filter('comment_author', 'DGE_SlideShow_securityFilter');
add_filter('comment_email', 'DGE_SlideShow_securityFilter');
add_filter('comment_text', 'DGE_SlideShow_securityFilter');
add_filter('comment_url', 'DGE_SlideShow_securityFilter');
add_filter('the_content', 'DGE_SlideShow_contentFilter');
add_action('wp_head', 'DGE_SlideShow_insertHeader');
add_action('admin_menu', 'DGE_SlideShow_admin');
add_action('activate_dge-slideshow/dge-slideshow.php', 'DGE_SlideShow_activate');
?>
