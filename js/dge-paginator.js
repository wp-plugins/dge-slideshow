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
	    switch (child.getAttribute && child.getAttribute('rel'))
	    {
	    case 'ss-menu': this.menu = child; break;
	    case 'ss-display': this.displayNode = child; break;
	    case 'ss-thumbs': this.thumbs = child; break;
	    }
	}

	// and some info for resizing
	this.displayWidth = parseFloat(DGE_getStyle(this.displayNode, 'width'));
	this.displayHeight = parseFloat(DGE_getStyle(this.displayNode, 'height'));
	this.displayRatio = this.displayWidth/this.displayHeight;

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

DGE_Paginator.prototype.display = function(node)
{
    if (this.displayNode.firstChild)
        this.displayNode.removeChild(this.displayNode.firstChild);
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
