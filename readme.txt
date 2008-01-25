=== DGE_Slideshow ===
Tags: slideshow, zooomr, flickr, xsl, xslt, javascript, dhtml, images
Contributors: delcock
Requires at least: 2.0?
Tested up to: 2.2
Stable tag: 0.4

Turns a collection of images (e.g. Flickr or Zooomr image feed) into a javascript-based slideshow within a Wordpress post or page.

== Description ==

The plugin takes an image source (e.g. RSS feed or web page), passes
it through XSLT via the 
[DGE_InlineRSS](http://wordpress.org/extend/plugins/dge-inlinerss/)
plugin, then uses javascript and css in the client to do the actual
slideshow.

Because of the use of XSLT, it's fairly flexible, allowing new image
sources to be made available quickly.

It's still under development, but working nicely on Firefox 1.5, 2.0,
IE 6 (Win) and 7. Please feel free to submit bugs and suggest features
by posting questions etc on the
[home page](http://dave.coolhandmook.com/slideshow/).

== Installation ==

1. Install and activate at least version 0.93 of
   [DGE_InlineRSS](http://wordpress.org/extend/plugins/dge-inlinerss/).
2. Make sure inlinerss' cache directory is set up properly. This
   plugin uses its settings.
3. Add `wp-content/plugins` to the inlinerss `XSLT path`
   setting. This plugin's xslt won't be found otherwise.
4. Upload this plugin to the `/wp-content/plugins/`, making sure it
   is all contained within a sub-folder called `dge-slideshow`.
5. Activate the plugin from your wordpress admin menu.
6. See the usage instructions below for how to actually get a
   slideshow up and running.

== Screenshots ==

1. The default style (taken from version 0.31)

== Requirements ==

* Javascript.
* [DGE_InlineRSS](http://wordpress.org/extend/plugins/dge-inlinerss/)
  plugin version 0.93 or greater.
* Only tested with Wordpress 2.0 and 2.1. I don't know if it'll work
  with earlier versions.
* PHP 5.

== Examples ==

The latest stable version with various applications of the slideshow
is [here](http://dave.coolhandmook.com/slideshow/).

The latest trunk is up and (probably) running
[here](http://dave.coolhandmook.com/testwp/).

== Usage ==

You can invoke a slideshow by either placing a specially formatted
string into any page or post, or from your theme templates with a call
to `DGE_SlideShow` function. For both methods, the arguments are the
same, but the call is different.

Both methods of invokation require:
* a unique id
* a url to fetch
* options

The id must be unique for each slideshow, and must be a valid
javascript variable name, with the exception that it may start with a
number. So it must not contain spaces or dashes, for example.

#### Filter method

When you activate the plugin, it installs a content filter into
wordpress. This filter looks for strings beginning with `!slideshow!`,
followed by the 2 required parameters, and a list of options. Each of
these parts of the filtered string must be separated by an exclamation
mark. Options must be separated by a semi-colon. If you want to
include more than one slideshow in succession, make sure they are
defined on a new line for each call.

Here's the syntax:

	!slideshow!<id>!<url>[!<option1=val>;<option2=val>...]!

Here's a few examples:

	!slideshow!ss1!http://beta.zooomr.com/bluenote/feeds:rss/recent/!
	!slideshow!ss2!http://beta.zooomr.com/bluenote/feeds:rss/recent/!limit=5!
	!slideshow!ss3!http://beta.zooomr.com/bluenote/feeds:rss/recent/!limit=5;reverse!

#### From php (your theme templates)

The `DGE_SlideShow` function takes three parameters. The first is the
unique id for your slideshow, followed by the url of the desired feed,
followed by an array of optional arguments. The function returns a
string with the necessary javascript and html to set up the slideshow,
so just `echo` it.

For example:

	// From version 0.3 and above
	echo DGE_SlideShow('ss1', 'http://beta.zooomr.com/bluenote/feeds:rss/recent/', array('limit'=>5,'reverse'=>1));

== Options ==

**limit**

* Limits the number of images extracted from the feed. Default is 0,
  implying no limit, or everything in the feed.

**preset**

* Applies the options defined in the named preset, before overriding
  them with any other options passed.

**reverse**

* This option doesn't need any value. Just by being present, it tells
  the plugin to reverse the order of the images in the feed.

**timeout**

* Specify the time in minutes before the cached html is refreshed. Set
  the default in the options pane. It is 1 hour by default.

**xslt**

* Specify a particular xsl translation file

**html**

* Assume input url is HTML, rather than XML. (As of v0.392)

== Presets ==

On the slideshow options page, you can set up presets to save you
typing the same options in for different slideshow calls. Just give
the preset a name in the left-hand field, and fill in the right-hand
field with the desired options in the same format as the filter
call. That is, separate each option with a semi-colon.

For example, the name of your preset might be `preset1`, and you want
to always reverse the feed, and have a timeout of only 1 minute. Fill
in the left-hand field with `preset1`, and the right hand field with
`reverse;timeout=1`.

Another example would be to add a shortcut for Zooomr favourites. Put
`zooomrfaves` as the name, and `xslt=dge-slideshow/xsl/zooomrfaves.xsl`
in the value field, again omitting the quotes. You could then invoke a
slideshow of a Zooomr set with:

	!slideshow!zfaves!http://www.zooomr.com/photos/davee/favorites/!preset=zooomrfaves!

== License ==

This work is licensed under a [Creative Commons
Attribution-Noncommercial-Share Alike 2.5 License](http://creativecommons.org/licenses/by-nc-sa/2.5/).

== History ==

[Full changelog here](http://dev.wp-plugins.org/log/dge-slideshow/)

#### changes in 0.4

 * Bug fixes, mostly for IE.
 * Added a menu bar for navigation etc.
 * Choose number of thumbnails via parameters.
 * Zooomr mark III compatibility fixes.
 * Full-screen mode.
 * Updated options page for specifying defaults.
 * Simplified XSL, and reorganised XSL files.
 * Structural changes under the hood to separate behaviour from content.
 * Easier to write new data sources, with introduction of data handlers.

#### changes in 0.3

 * General clean-up and some bug fixes.
 * New `xslt` parameter to use custom XSL transformations.
 * Cleaned up, modularised XSL files.
 * New XSL files for Zooomr SmartSets, Zooomr favourites, and Flickr photosets.

#### changes in 0.2

 * Optionally reverse the order of images in the feed.
 * Security enhancements.
 * Indicates which images are still loading in the background (modifiable with CSS)
 * Admin panel for setting defaults and defining presets.
 * Presets allow quick changing of settings across several slideshows.
 * New options to influence image size, and skip default css rules.

#### 0.1

 * Flickr or Zoomr feeds
 * Displays thumbnails of images in the feed.
 * Background loading of images for visible thumbnails.
 * Caches xsl translation of feed, with configurable refresh period.
 * Customisable look&feel via CSS.

== Plans ==

#### Before we reach 0.5

 * auto filters to select a preset when source matches particular regex.
 * show image title somewhere
 * Event handler to allow changing slides with left and right arrow keys.
 * Optionally display position in sequence and/or total images.

#### After 0.5

 * set up inlinerss settings like XSLT path automatically
 * warn if cache directory isn't set up properly.
 * abstraction of the back end so we don't rely on DGE_InlineRSS calls.
 * auto generation of slideshow id
 * Next/previous 5 or some sort of paging
 * Optionally use flickr or zooomr api for speed
 * Fading transitions
 * Pull multiple feeds into one slideshow
