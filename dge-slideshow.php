<?PHP
/*
Plugin Name: DGE_SlideShow
Plugin URI: http://dave.stufftoread.net/
Description: Turns a Flickr or Zooomr image feed into a slideshow. Requires <a href="http://dave.stufftoread.net/2006/07/13/scratch-that-xslt-is-the-way-forward/">this modified version</a> of the <a href="http://www.iconophobia.com/wordpress/?page_id=55">inlineRSS</a> plugin.
Version: 0.1
Author: Dave Elcock
Author URI: http://dave.stufftoread.net/
*/

function DGE_SlideShow_format($ssid, $url, $timeout=30)
{
    // Oh bugger, these are just cut-and-pasted from inlineRSS.php.
    // It'd be better if there was some interface to ask inlineRSS
    // what its settings were.
    $path = 'wp-content/plugins/';   // Where to look for all config files
    $paramfile = 'inlineRSS.txt';    // Configuration file of feeds
    $fileprefix = 'in_';             // What feed casual names get prefixed with
	
    // Some other variables.
    $inlineRSSname = "dge-slideshow-".$ssid;
    $cachefile = ABSPATH . "wp-content/" . $fileprefix . $inlineRSSname . ".html";

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

    if ( $exists == FALSE or $age > $timeout * 60 )
    {   // If there's no file, or it's old
	$params = array('ssid'=>$ssid);
	$inlineRSSout = inlineRSSparserWithParams(
		$inlineRSSname, $url, 1,
		"dge-slideshow/dge-slideshow.xslt",
		$params);
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

// This is a Wordpress content filter that replaces calls to inlineRSS
// Any entries like !DGE_SlideShow!id!url! will be replaced
// with the rss feed reformatted for the slideshow javascript.
function DGE_SlideShow_contentFilter($content = '')
{
    $find[] = "//";
    $replace[] = "";

    preg_match_all('/!DGE_SlideShow!([^!]+)!([^!]+)!([0-9]+!)?/', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $val)
    {
	$find[] = "^" . $val[0] . "^";
	if ($val[3] != '') $timeout = intval($val[3]);
	else $timeout = 30;
	$replace[] = DGE_SlideShow_format($val[1], $val[2], $timeout);
    }
    return preg_replace($find, $replace, $content);
}

// This is for the php template-calling support:
function DGE_SlideShow($ssid, $url, $timeout=30)
{
    echo DGE_SlideShow_format($ssid, $url, $timeout);
}

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

// This adds the filter and action to Wordpress.
add_filter('the_content', 'DGE_SlideShow_contentFilter');
add_action('wp_head', 'DGE_SlideShow_insertHeader');

?>
