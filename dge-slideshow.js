// ----------------------------------------------------------------------
// DGE_SlideShow constructor and methods
// ----------------------------------------------------------------------

function DGE_SlideShow()
{
    // setup member variables
    this.slides = new Array();
    this.lastSlide = -1;
}

DGE_SlideShow.prototype.addSlide = function(slide)
{
    this.slides.push(slide);
}

DGE_SlideShow.prototype.attach = function(node)
{
    // Finish off grabbing nodes
    this.node = node;
    this.display = node.getElementsByTagName('div').item(0);
    this.imgwrap = this.display.getElementsByTagName('div').item(0);
    this.link = this.imgwrap.getElementsByTagName('a').item(0);
    this.image = this.link.getElementsByTagName('img').item(0);
    this.displayWidth = parseFloat(getStyle(this.display, 'width'));
    this.displayHeight = parseFloat(getStyle(this.display, 'height'));
    this.displayRatio = this.displayWidth/this.displayHeight;

    // Attach li elements to slides
    var liEls = node.getElementsByTagName('li');
    var count = liEls.length;
    if (count > this.slides.length) count = this.slides.length;
    else this.slides.length = count;
    for (s=0;s<count;s++) this.slides[s].attach(this, liEls[s]);

    this.select(0);
}

// slide parameter should be a slide index, so starting at 0
DGE_SlideShow.prototype.select = function(slide)
{
    if (this.lastSlide != slide)
    {
	this.lastSlide = slide;

	// First up, get the main image loading, but hide it for now
	// until it loads.
	this.image.style.display = 'none';
	this.slides[slide].loadImage();

	// Set the link target
	this.link.href = this.slides[slide].href;

	// Now sort out the thumnails
	var i, end, start;
	if (this.slides.length < 5)
	{
	    start = 0;
	    end = this.slides.length;
	}
	else
	{
	    start = slide - 2;
	    if (start < 0) start = 0;
	    end = start + 5;
	    if (end > this.slides.length)
	    {
		end = this.slides.length;
		start = end - 5;
	    }
	}
	// Clear down items outwith range
	for (i = 0; i<start; ++i) this.slides[i].hideThumb();
	for (i=end; i<this.slides.length; ++i) this.slides[i].hideThumb();
	// Show all items in range, and start preloading
	for (i=start; i<end; ++i)
	{
	    if (i==slide) this.slides[i].revealThumb(true);
	    else
	    {
		this.slides[i].revealThumb(false);
		this.slides[i].preloadImage();
	    }
	}
    }
}

DGE_SlideShow.prototype.displaySlide = function(slide)
{
    this.image.src = slide.imagesrc;
    if (this.displayRatio >= slide.ratio)
    {
	var width = parseInt(this.displayHeight*slide.ratio);
	this.image.style.height = this.displayHeight+'px';
	this.image.style.width = width+'px';
 	this.imgwrap.style.top = 0;
	this.imgwrap.style.left = parseInt((this.displayWidth-width)/2)+'px';
    }
    else
    {
	var height = parseInt(this.displayWidth/slide.ratio);
	this.image.style.height = height+'px';
	this.image.style.width = this.displayWidth+'px';
 	this.imgwrap.style.top = parseInt((this.displayHeight-height)/2)+'px';
 	this.imgwrap.style.left = 0;
    }
    this.image.style.display = 'inline';
}

// ----------------------------------------------------------------------
// DGE_Slide constructor and methods
// ----------------------------------------------------------------------

function DGE_Slide(href, imagesrc, width, height)
{
    // setup variables
    this.href = href;
    this.imagesrc = imagesrc;
    this.width = width;
    this.height = height;
    this.ratio = width/height;
    this.image = new Image();
    this.image.slide = this;
    this.image.onerror = DGE_Slide.prototype.onImageError;
    this.image.onabort = DGE_Slide.prototype.onImageAbort;
    this.imageLoading = false;
    this.imageLoaded = false;
    this.imageError = false;
    this.imageAborted = false;
}

DGE_Slide.prototype.attach = function(slideshow, liNode)
{
    this.slideshow = slideshow;
    this.node = liNode;
}

DGE_Slide.prototype.revealThumb = function(selected)
{
    if (selected) this.node.firstChild.className='selected';
    else this.node.firstChild.className='';
    this.node.style.display='inline';
}

DGE_Slide.prototype.hideThumb = function()
{
    this.node.style.display='none';
    this.node.firstChild.className='';
    this.image.onload = DGE_Slide.prototype.onImagePreload;
}

DGE_Slide.prototype.preloadImage = function()
{
    if (!this.imageLoaded)
    {
	this.image.onload = DGE_Slide.prototype.onImagePreload;
	if (!this.imageLoading)
	{
	    // Kick off the loading
	    this.imageLoading = true;
	    this.image.src = this.imagesrc;
	}
    }
}

DGE_Slide.prototype.loadImage = function()
{
    if (this.imageLoaded)
    {
	// Already loaded, whang it up now.
	this.slideshow.displaySlide(this);
    }
    else
    {
	this.image.onload = DGE_Slide.prototype.onImageLoad;
	// Kick off the loading if it's not already.
	if (!this.imageLoading)
	{
	    this.imageLoading = true;
	    this.image.src = this.imagesrc;
	}
    }
}

DGE_Slide.prototype.onImagePreload = function()
{
    this.slide.imageLoaded = true;
    this.slide.imageLoading = false;
}

DGE_Slide.prototype.onImageLoad = function()
{
    this.slide.imageLoaded = true;
    this.slide.imageLoading = false;
    this.slide.slideshow.displaySlide(this.slide);
}

DGE_Slide.prototype.onImageError = function()
{
    this.slide.imageLoading = false;
    this.slide.imageError = true;
}

DGE_Slide.prototype.onImageAbort = function()
{
    this.slide.imageLoading = false;
    this.slide.imageAborted = true;
}

// ----------------------------------------------------------------------
// cross-browser stuff
// ----------------------------------------------------------------------

function getStyle(el,styleProp)
{
    // Based on code at http://www.quirksmode.org/dom/getstyles.html
    if (el.currentStyle)
	var y = el.currentStyle[styleProp];
    else if (window.getComputedStyle)
	var y = document.defaultView.getComputedStyle(el,null).getPropertyValue(styleProp);
    return y;
}
