// ----------------------------------------------------------------------
// cross-browser stuff
// ----------------------------------------------------------------------

function DGE_getStyle(el,styleProp)
{
    // Based on code at http://www.quirksmode.org/dom/getstyles.html
    if (el.currentStyle)
	var y = el.currentStyle[styleProp];
    else if (window.getComputedStyle)
	var y = document.defaultView.getComputedStyle(el,null).getPropertyValue(styleProp);
    return y;
}

function DGE_applyClass(el, className)
{
    if (el.className.length == 0)
	el.className = className;
    else if (el.className.indexOf(className) < 0)
	el.className += ' ' + className;
}

function DGE_revokeClass(el, className)
{
    var result = '';
    var remain = el.className;
    var pos = remain.indexOf(className);
    while (pos >= 0)
    {
	// todo - this isn't robust enough. It could mangle other
	// class names beginning with the supplied class.
        result += remain.substr(0, pos);
        remain = remain.substr(pos+className.length);
	pos = remain.indexOf(className);
    }
    el.className = result + remain;
}

function DGE_pageHeight()
{
    // Based on code at
    // http://www.quirksmode.org/viewport/compatibility.html
    var test1 = document.body.scrollHeight;
    var test2 = document.body.offsetHeight;
    if (test1 > test2) // all but Explorer Mac
    {
	return document.body.scrollHeight;
    }
    else // Explorer Mac;
	//would also work in Explorer 6 Strict, Mozilla and Safari
    {
	return document.body.offsetHeight;
    }
}

function DGE_getWidth(el)
{
    if (el.currentStyle) // explorer
    {
	return (el.clientWidth
		- parseFloat(el.currentStyle['paddingLeft'])
		- parseFloat(el.currentStyle['paddingRight']));
    }
    else if (window.getComputedStyle) // moz and opera
    {
	return parseFloat(document.defaultView.getComputedStyle(el,null).getPropertyValue('width'));
    }
    else
    {
	// fallback for everything else
	return parseFloat(el.clientWidth);
    }
}

function DGE_getHeight(el)
{
    if (el.currentStyle) // explorer
    {
	return (el.clientHeight
		- parseFloat(el.currentStyle['paddingTop'])
		- parseFloat(el.currentStyle['paddingBottom']));
    }
    else if (window.getComputedStyle) // moz and opera
    {
	return parseFloat(document.defaultView.getComputedStyle(el,null).getPropertyValue('height'));
    }
    else
    {
	// fallback for everything else
	return parseFloat(el.clientHeight);
    }
}

function DGE_windowHeight()
{
    if (document.documentElement && document.documentElement.clientHeight)
	return document.documentElement.clientHeight;
    else if (document.body)
	return document.body.clientHeight;
    else
	return ''; // YIKES!
}

function DGE_scrollTop()
{
    if (document.documentElement && document.documentElement.scrollTop)
	return document.documentElement.scrollTop;
    else if (document.body)
	return document.body.scrollTop;
    else
	return ''; // YIKES!
}

/**
 * @param dest
 *   The destination object. If this is undefined, it will be created
 *   for you.
 * @param orig
 *   The origin object to copy
 * @param attribs
 *   If an array of attribute names is passed, only these attributes
 *   of orig are copied into dest. Otherwise, copy everything.
 * @return dest
 */
function DGE_copyObject(dest,orig,attribs)
{
    var copy;
    if (dest == undefined)
	dest = new Object;
    if (attribs == undefined)
	for (var i in orig) copy[i] = orig[i];
    else
	for (var i=0; i<attribs.length; i++) dest[attribs[i]] = orig[attribs[i]];
    return dest;
}
