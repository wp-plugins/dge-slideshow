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
