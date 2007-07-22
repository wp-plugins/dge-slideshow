// ----------------------------------------------------------------------
// DGE_Paginator constructor and methods
// ----------------------------------------------------------------------

// defaults
DGE_Paginator.prototype.autoPlay = true;
DGE_Paginator.prototype.repeat = false;
DGE_Paginator.prototype.delay = 3;
DGE_Paginator.prototype.nThumbs = 5;

function DGE_Paginator(nodeId, settings)
{
    // setup member variables
    this.nodeId = nodeId;
    this.node = document.getElementById(nodeId);
    this.pages = new Array();
    this.current = -1;
    this.clock = null;

    // override defaults with settings
    if (settings != undefined)
    {
	if (settings.play != undefined) this.autoPlay = settings.play;
	if (settings.repeat != undefined) this.repeat = settings.repeat;
	if (settings.delay != undefined) this.delay = settings.delay*1000;
	if (settings.thumbs != undefined) this.nThumbs = settings.thumbs;
    }

    // Start hooking things up.
    if (this.node && this.node.getAttribute('rel') == 'ss-instance')
    {
	// Install this into the node so the timeout has a handle on it.
	this.node.paginator = this;

	// Grab the constituent nodes
	for (i=0;i<this.node.childNodes.length;i++)
	{
	    var child = this.node.childNodes[i];
	    if (child.getAttribute &&
		child.getAttribute('rel') == 'ss-wrapper')
	    {
		this.wrapper = child;
		for (j=0;j<this.wrapper.childNodes.length;j++)
		{
		    child = this.wrapper.childNodes[j];
		    switch (child.getAttribute && child.getAttribute('rel'))
		    {
		    case 'ss-menu': this.menu = child; break;
		    case 'ss-display': this.displayNode = child; break;
		    case 'ss-thumbs': this.thumbs = child; break;
		    }
		}
	    }
	}

	// and some info for resizing
	this.resizeDisplay();

	// Hook up play, pause, etc.
	var menuUL = this.menu.getElementsByTagName('ul').item(0);
	var menuLIs = menuUL.getElementsByTagName('li');
	menuUL.paginator = this;
	for (i=0;i<menuLIs.length;i++)
	    menuLIs[i].onclick = this.menuHandler;

	// Create pagess
	var liEls = this.thumbs.getElementsByTagName('li');
	for (s=0; s<liEls.length; s++)
	    this.pages.push(new DGE_PaginatorPage(this, liEls[s], s));

	this.select(0);
	if (this.autoPlay) this.play();
	else this.pause();
    }
}

DGE_Paginator.prototype.resizeDisplay = function()
{
    this.displayWidth = DGE_getWidth(this.displayNode);
    this.displayHeight = DGE_getHeight(this.displayNode);
    this.displayRatio = this.displayWidth/this.displayHeight;
}

DGE_Paginator.prototype.refreshDisplay = function()
{
    this.resizeDisplay();
    this.pages[this.current].display();
}

DGE_Paginator.prototype.playing = function()
{
    return this.clock != null;
}

DGE_Paginator.prototype.play = function()
{
    if (this.current < this.pages.length-1 || this.repeat)
    {
	// If it's still loading, delay playing until it loads
	// (i.e. the next display() call).
	if (this.pages[this.current].loading())
	{
	    this.setWaiting();
	}
	else
	{
	    clearInterval(this.clock);
	    this.clock = setInterval('document.getElementById("'+this.nodeId+'").paginator.playTimeout();', this.delay);
	}
	// Either way, apply class now so that the play button changes
	// to pause.
	DGE_revokeClass(this.node,'ss-paused');
	DGE_applyClass(this.node,'ss-playing');
    }
}

DGE_Paginator.prototype.playTimeout = function()
{
    this.nextPage();
    // If it's still loading, suspend playing until it loads (i.e. the
    // next display() call).
    if (this.pages[this.current].loading())
	this.setWaiting();
}

DGE_Paginator.prototype.maximise = function()
{
    DGE_revokeClass(this.node,'ss-minimised');
    DGE_applyClass(this.node,'ss-maximised');
    var pageHeight = DGE_pageHeight();
    var windowHeight = DGE_windowHeight();

    // Hide the image in the display to avoid confusing IE. It will be
    // redisplayed at the bottom of this method, at its new size.
    this.undisplay();

    // Resize the wrapper to fill the screen
    this.wrapper.style.position = 'absolute';
    this.wrapper.style.height = (windowHeight-2)+'px';
    this.wrapper.style.width = document.body.scrollWidth+'px';
    this.wrapper.style.top = DGE_scrollTop() + 'px';
    // Resize the main container to fill the entire page, including
    // what's hidden due to scrolling, and width of entire page.
    this.node.style.position = 'absolute';
    this.node.style.margin = 'auto';
    this.node.style.width = document.body.scrollWidth+'px';
    this.node.style.height = pageHeight+'px';
    this.node.style.top = 0;
    this.node.style.left = 0;
    // Refit the display node
    this.displayNode.style.height = (windowHeight - DGE_getHeight(this.menu) - DGE_getHeight(this.thumbs) - 15) + 'px';
    // Redisplay and sort out new aspect ratios etc.
    this.refreshDisplay();
}

DGE_Paginator.prototype.minimise = function()
{
    DGE_revokeClass(this.node,'ss-maximised');
    // Hide the image in the display to avoid confusing IE. It will be
    // redisplayed at the bottom of this method, at its new size.
    this.undisplay();
    // reset display to old size
    this.displayNode.style.height = '';
    // reset wrapper to old size
    this.wrapper.style.position = '';
    this.wrapper.style.height = '';
    this.wrapper.style.width = '';
    this.wrapper.style.top = '';
    // reset container to old size
    this.node.style.position = '';
    this.node.style.margin = '';
    this.node.style.width = '';
    this.node.style.height = '';
    this.node.style.top = '';
    this.node.style.left = '';
    // Redisplay and sort out new aspect ratios etc.
    this.refreshDisplay();
    DGE_applyClass(this.node,'ss-minimised');
}

DGE_Paginator.prototype.setWaiting = function()
{
    DGE_applyClass(this.node,'waiting');
    clearInterval(this.clock);
    this.waiting = true;
}

DGE_Paginator.prototype.clearWaiting = function()
{
    if (this.waiting)
    {
	DGE_revokeClass(this.node,'waiting');
	this.waiting = false;
	// resume play where we left off
	this.play();
    }
}

DGE_Paginator.prototype.pause = function()
{
    this.waiting = false;
    clearInterval(this.clock);
    this.clock = null;
    DGE_revokeClass(this.node,'ss-playing');
    DGE_applyClass(this.node,'ss-paused');
}

DGE_Paginator.prototype.nextPage = function()
{
    if (this.repeat)
    {
	if (this.current == this.pages.length-1) this.select(0);
	else this.select(this.current+1);
    }
    else if (this.current < this.pages.length-1)
    {
	this.select(this.current+1);
	if (this.current == this.pages.length-1) this.pause();
    }
}

DGE_Paginator.prototype.prevPage = function()
{
    this.select(this.current-1);
}

DGE_Paginator.prototype.firstPage = function()
{
    this.select(0);
}

DGE_Paginator.prototype.lastPage = function()
{
    this.select(this.pages.length-1);
}

DGE_Paginator.prototype.menuHandler = function(event)
{
    // 'this' is the <li> element that was clicked
    var p = this.parentNode.paginator;
    switch(this.className)
    {
    case 'ss-play': p.play(); break;
    case 'ss-pause': p.pause(); break;
    case 'ss-next': p.nextPage(); p.pause(); break;
    case 'ss-prev': p.prevPage(); p.pause(); break;
    case 'ss-first': p.firstPage(); p.pause(); break;
    case 'ss-last': p.lastPage(); p.pause(); break;
    case 'ss-maximise': p.maximise(); break;
    case 'ss-minimise': p.minimise(); break;
    }
}

// page parameter should be a page index, so starting at 0
DGE_Paginator.prototype.select = function(page)
{
    if (page >= 0 && page < this.pages.length && this.current != page)
    {
	this.current = page;

	// Get the main page loading before we do anything else
	DGE_applyClass(this.displayNode, 'loading');
	this.pages[page].display();

	// Now sort out the previews
	var i, end, start;
	if (this.pages.length < this.nThumbs)
	{
	    start = 0;
	    end = this.pages.length;
	}
	else
	{
	    start = page - parseInt((this.nThumbs-1)/2.0);
	    if (start < 0) start = 0;
	    end = start + this.nThumbs;
	    if (end > this.pages.length)
	    {
		end = this.pages.length;
		start = end - this.nThumbs;
	    }
	}
	// Clear down items outwith range
	for (i = 0; i<start; ++i) this.pages[i].hidePreview();
	for (i=end; i<this.pages.length; ++i) this.pages[i].hidePreview();
	// Show all items in range, and start preloading
	for (i=start; i<end; ++i) this.pages[i].showPreview(i-page);
    }
}

DGE_Paginator.prototype.undisplay = function()
{
    if (this.displayNode.firstChild)
        this.displayNode.removeChild(this.displayNode.firstChild);
}

DGE_Paginator.prototype.display = function(node)
{
    this.undisplay();
    this.displayNode.appendChild(node);
    DGE_revokeClass(this.displayNode, 'loading');
    // Resume playing if we got stuck waiting for an image to load
    this.clearWaiting();
}

// ----------------------------------------------------------------------
// DGE_PaginatorPage constructor and methods
// ----------------------------------------------------------------------

function DGE_PaginatorPage(paginator, liNode, index)
{
    this.liNode = liNode;

    // Grab previews and pages
    for (i=0; i<liNode.childNodes.length; i++)
    {
	var child = liNode.childNodes[i];
	var rel = child.getAttribute('rel');
	switch (rel)
	{
	case 'ss-preview':
	    this.previewNode = child;
	    break;
	case null:
	    // do nothing
	    break;
	default:
	    this.pageNode = child;
	    this.pageHandler = eval('new '+rel+'();');
	    this.pageHandler.prepare(paginator, liNode, index);
	    break;
	}
    }

    // Setup onclick event handler
    liNode.paginator = paginator;
    liNode.ss_index = index;
    liNode.onclick = this.onclick;
}

DGE_PaginatorPage.prototype.onclick = function()
{
    // 'this' is the li element
    this.paginator.select(this.ss_index);
    this.paginator.pause();
}

DGE_PaginatorPage.prototype.showPreview = function(relativePos)
{
    // Apply relative position classes
    if (relativePos==0)
    {
//	DGE_revokeClass(this.liNode, 'before-selected');
//	DGE_revokeClass(this.liNode, 'after-selected');
//	DGE_applyClass(this.liNode, 'selected');
	this.liNode.className = 'selected';
    }
    else
    {
//	DGE_revokeClass(this.liNode, 'selected');
	if (relativePos<0)
	{
//	    DGE_revokeClass(this.liNode, 'after-selected');
//	    DGE_applyClass(this.liNode, 'before-selected');
	    this.liNode.className = 'before-selected';
	}
	else
	{
//	    DGE_revokeClass(this.liNode, 'before-selected');
//	    DGE_applyClass(this.liNode, 'after-selected');
	    this.liNode.className = 'after-selected';
	}
	// make sure it's loading in the background.
	this.pageHandler.preload();
    }
    // make sure we don't wipe out loading class
//    if (this.loading()) DGE_applyClass(this.liNode, ' loading');
    if (this.pageHandler.loading()) this.liNode.className += ' loading';

//    this.liNode.className = 'show';
    this.pageHandler.previewVisible();
}

DGE_PaginatorPage.prototype.hidePreview = function()
{
    this.liNode.className='hide';
    this.pageHandler.previewHidden();
}

DGE_PaginatorPage.prototype.display = function()
{
    if (this.pageHandler) this.pageHandler.display();
}

DGE_PaginatorPage.prototype.loading = function()
{
    return this.pageHandler && this.pageHandler.loading();
}
