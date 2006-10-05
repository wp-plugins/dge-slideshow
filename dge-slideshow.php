<?php
/*
Plugin Name: DGE_SlideShow
Plugin URI: http://dave.stufftoread.net/
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
	    $output = preg_replace('/_m\.jpg/', '.jpg', $inlineRSSout);
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

    preg_match_all('/!slideshow!([^!]+)!([^!]+)!([^!]+!)?/', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $val)
    {
	$find[] = "^$val[0]^";
	$params = array();
	if ($val[3] != '')
	{
	    // knock off trailing '!'
	    $val[3] = substr($val[3], 0, strlen($val[3])-1);
	    $params = DGE_SlideShow_explodeParams($val[3]);
	}
	$replace[] = DGE_SlideShow_format($val[1], $val[2], $params);
    }
    return preg_replace($find, $replace, $content);
}

// Filters out inline calls to the slideshow.
function DGE_SlideShow_securityFilter($content = '')
{
    $find[] = "//";
    $replace[] = "";

    preg_match_all('/!slideshow!([^!]+)!([^!]+)!([^!]+!)?/', $content, $matches, PREG_SET_ORDER);

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
	echo "<!-- DGE_SlideShow -->\n";
	echo '<link rel="stylesheet" href="'.$path.
		'dge-slideshow.css" type="text/css" media="screen" />'.
		"\n";
	echo '<script type="text/javascript" src="'.$path.
		'dge-slideshow.js"></script>'."\n";
	echo "<!-- DGE_SlideShow end -->\n\n";
}

function DGE_SlideShow_admin()
{
    if (function_exists('add_options_page'))
    {
	add_options_page('Slideshow Options', 'Slideshow', 8, basename(__FILE__), 'DGE_SlideShow_subpanel');
    }
}

function DGE_SlideShow_subpanel()
{
    if (get_option('dge_ss_def_timeout') == '')
    {
	// TODO - this needs to go in an install function
	add_option('dge_ss_def_timeout', 60, 'Default timeout', 'no');
	echo "<div class=\"updated\"><p>Added initial defaults</p></div>\n";
    }

    $presets = get_option('dge_ss_presets');
    if (!$presets) $presets = array();

    // ----------------------------------------------------------------
    // PARSE $_POST PARAMETERS
    // ----------------------------------------------------------------
    if (isset($_POST['info_update']))
    {
	echo '<div class="updated">';
	// ------------------------------------------------------------
	// DEFAULTS
	// ------------------------------------------------------------
	if (isset($_POST['def_timeout']))
	{
	    $timeout = $_POST['def_timeout'];
	    if ($timeout == '')
	    {
		echo "<p><strong>Default timeout not updated. Invalid input.</strong></p>\n";
	    }
	    else
	    {
		$timeout = intval($timeout);
		if ($timeout != get_option('dge_ss_def_timeout'))
		{
		    update_option('dge_ss_def_timeout', $timeout);
		    echo "<p>Default timeout updated.</p>\n";
		}
	    }
	}
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
		echo "<p><strong>New preset '$name' already exists. Not updated.</strong></p>\n";
	    }
	    else
	    {
		$presets[$name] = DGE_SlideShow_explodeParams($value);
		echo "<p>New preset '$name' added.</p>\n";
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
		    echo "<p>Preset '$name' removed.</p>\n";
		}
		else
		{
		    $presets[$name]=DGE_SlideShow_explodeParams($update);
		    echo "<p>Preset '$name' updated.</p>\n";
		}
		$updatepresets = 1;
	    }
	}
	// Do all updates to the presets in one go.
	if ($updatepresets)
	{
	    update_option('dge_ss_presets', $presets);
	}
	echo '</div>';
    }

    // ----------------------------------------------------------------
    // DISPLAY FORM
    // ----------------------------------------------------------------
 ?>
<div class="wrap">
  <form method="post">
    <h2>Slideshow Options</h2>
    <fieldset name="defaults">
    <legend>Defaults</legend>
    <p>Timeout (mins) <input type="text" name="def_timeout" value="<?php echo get_option('dge_ss_def_timeout'); ?>"/></p>
    </fieldset>
    <fieldset name="presets">
      <legend>Presets</legend>
      <table>
<?php
if ($presets && count($presets)>0)
{
    echo "        <tr><td colspan=\"3\">Existing Presets</td><tr>\n";
    foreach ($presets as $name=>$value)
    {
	echo "      <tr><td>$name</td>";
	echo "<td colspan=\"2\"><input type=\"text\" name=\"pre_upd_$name\" size=\"50\" value=\"".
	     DGE_SlideShow_implodeParams($value).'"/>';
	echo "</td></tr>\n";
    }
}
?>
        <tr><td colspan="3">New Preset</td><tr>
        <tr>
          <td>Name</td>
          <td><input type="text" name="pre_new_name"/></td>
	</tr>
        <tr>
          <td>Value</td>
          <td colspan="2"><input type="text" size="50" name="pre_new_value"/></td>
        </tr>
      </table>
    </fieldset>
    <div class="submit">
      <input type="submit" name="info_update" value="Update options &raquo;" />
    </div>
  </form>
</div>
<?php
}

// These add the filters and action to Wordpress.
add_filter('comment_author', 'DGE_SlideShow_securityFilter');
add_filter('comment_email', 'DGE_SlideShow_securityFilter');
add_filter('comment_text', 'DGE_SlideShow_securityFilter');
add_filter('comment_url', 'DGE_SlideShow_securityFilter');
add_filter('the_content', 'DGE_SlideShow_contentFilter');
add_action('wp_head', 'DGE_SlideShow_insertHeader');
add_action('admin_menu', 'DGE_SlideShow_admin');
?>
