// ----------------------------------------------------------------------
// DGE_SlideShow constructor and methods
// ----------------------------------------------------------------------

function DGE_SlideShow(name, repeat, delay, autoplay)
{
    // setup member variables
    this.name = name;
    this.repeat = repeat;
    this.delay = delay;
    this.autoplay = autoplay;
    this.slides = new Array();
    this.current = -1;
    this.clock = null;
    this.count = 1;
}

DGE_SlideShow.prototype.playing = function()
{
    return this.clock != null;
}

DGE_SlideShow.prototype.play = function()
{
    if (this.current < this.slides.length-1 || this.repeat)
    {
	DGE_revokeClass(this.node,'ss-paused');
	DGE_applyClass(this.node,'ss-playing');
	clearInterval(this.clock);
	this.clock = setInterval(this.name+'.nextSlide()', this.delay);
    }
}

DGE_SlideShow.prototype.pause = function()
{
    DGE_revokeClass(this.node,'ss-playing');
    DGE_applyClass(this.node,'ss-paused');
    clearInterval(this.clock);
    this.clock = null;
}

DGE_SlideShow.prototype.nextSlide = function()
{
    if (this.repeat)
    {
	if (this.current == this.slides.length-1) this.select(0);
	else this.select(this.current+1);
    }
    else if (this.current < this.slides.length-1)
    {
	this.select(this.current+1);
	if (this.current == this.slides.length-1) this.pause();
    }
}

DGE_SlideShow.prototype.prevSlide = function()
{
    this.select(this.current-1);
}

DGE_SlideShow.prototype.firstSlide = function()
{
    this.select(0);
}

DGE_SlideShow.prototype.lastSlide = function()
{
    this.select(this.slides.length-1);
}

DGE_SlideShow.prototype.addSlide = function(slide)
{
    this.slides.push(slide);
}

DGE_SlideShow.prototype.menuHandler = function(event)
{
    // 'this' is the <li> element that was clicked
    var ss = this.parentNode.parentSlideshow;
    switch(this.className)
    {
    case 'ss-play': ss.play(); break;
    case 'ss-pause': ss.pause(); break;
    case 'ss-next': ss.nextSlide(); ss.pause(); break;
    case 'ss-prev': ss.prevSlide(); ss.pause(); break;
    case 'ss-first': ss.firstSlide(); ss.pause(); break;
    case 'ss-last': ss.lastSlide(); ss.pause(); break;
    }
}

DGE_SlideShow.prototype.attach = function(node)
{
    // Finish off grabbing nodes
    this.node = node;
    this.menu = node.childNodes[0];
    this.display = node.childNodes[1];
    this.thumbs = node.childNodes[2];
    this.imgwrap = this.display.getElementsByTagName('div').item(0);
    this.link = this.imgwrap.getElementsByTagName('a').item(0);
    this.image = this.link.getElementsByTagName('img').item(0);
    this.displayWidth = parseFloat(DGE_getStyle(this.display, 'width'));
    this.displayHeight = parseFloat(DGE_getStyle(this.display, 'height'));
    this.displayRatio = this.displayWidth/this.displayHeight;

    // Hook up play, pause, etc.
    var menuUL = this.menu.getElementsByTagName('ul').item(0);
    var menuLIs = menuUL.getElementsByTagName('li');
    menuUL.parentSlideshow = this;
    for (i=0;i<menuLIs.length;i++)
        menuLIs[i].onclick = this.menuHandler;

    // Attach li elements to slides
    var liEls = this.thumbs.getElementsByTagName('li');
    var count = liEls.length;
    if (count > this.slides.length) count = this.slides.length;
    else this.slides.length = count;
    for (s=0;s<count;s++) this.slides[s].attach(this, liEls[s], s);

    this.select(0);
    if (this.autoplay) this.play();
    else this.pause();
}

// slide parameter should be a slide index, so starting at 0
DGE_SlideShow.prototype.select = function(slide)
{
    if (slide >= 0 && slide < this.slides.length && this.current != slide)
    {
	this.current = slide;

	// First up, get the main image loading, but hide it for now
	// until it loads.
	DGE_applyClass(this.image, 'loading');
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
	    s = this.slides[i];
	    // Apply relative position classes
	    if (i==slide)
	    {
		s.node.firstChild.className = 'selected';
		// make sure we don't wipe out loading class
		if (s.imageLoading) s.node.firstChild.className += ' loading';
	    }
	    else
	    {
		if (i<slide) s.node.firstChild.className = 'before-selected';
		else s.node.firstChild.className = 'after-selected';
		// make sure we don't wipe out loading class
		if (s.imageLoading) s.node.firstChild.className += ' loading';
		s.preloadImage();
	    }
	    // Finally show it
	    s.showThumb();
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
//	this.imgwrap.style.left = parseInt((this.displayWidth-width)/2)+'px';
    }
    else
    {
	var height = parseInt(this.displayWidth/slide.ratio);
	this.image.style.height = height+'px';
	this.image.style.width = this.displayWidth+'px';
 	this.imgwrap.style.top = parseInt((this.displayHeight-height)/2)+'px';
// 	this.imgwrap.style.left = 0;
    }
    this.imgwrap.style.width = this.image.style.width;
    this.imgwrap.style.height = this.image.style.height;
    DGE_revokeClass(this.image, 'loading');
}

// ----------------------------------------------------------------------
// DGE_Slide constructor and methods
// ----------------------------------------------------------------------

function DGE_Slide(href, imagesrc)
{
    // setup variables
    this.href = href;
    this.imagesrc = imagesrc;
    this.image = new Image();
    this.image.slide = this;
    this.image.onerror = DGE_Slide.prototype.onImageError;
    this.image.onabort = DGE_Slide.prototype.onImageAbort;
    this.imageLoading = false;
    this.imageLoaded = false;
    this.imageError = false;
    this.imageAborted = false;
}

DGE_Slide.prototype.attach = function(slideshow, liNode, index)
{
    this.slideshow = slideshow;
    this.node = liNode;
    liNode.slideshow = slideshow;
    liNode.ss_index = index;
    liNode.onclick = this.onclick;
}

DGE_Slide.prototype.onclick = function()
{
    // 'this' is the li element
    this.slideshow.select(this.ss_index);
    this.slideshow.pause();
}

DGE_Slide.prototype.showThumb = function()
{
    this.node.className = 'show';
}

DGE_Slide.prototype.hideThumb = function()
{
    this.node.className='hide';
    this.image.onload = DGE_Slide.prototype.onImagePreload;
}

DGE_Slide.prototype.preloadImage = function()
{
    if (!this.imageLoaded)
    {
	this.image.onload = DGE_Slide.prototype.onImagePreload;
	this.startLoading();
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
	this.startLoading();
    }
}

DGE_Slide.prototype.startLoading = function()
{
    if (!this.imageLoading)
    {
	this.imageLoading = true;
	this.image.src = this.imagesrc;
	DGE_applyClass(this.node.firstChild, 'loading');
    }
}

DGE_Slide.prototype.loadingFinished = function()
{
     this.imageLoading = false;
     DGE_revokeClass(this.node.firstChild, 'loading');
     this.ratio = this.image.width/this.image.height;
}

DGE_Slide.prototype.onImagePreload = function()
{
    this.slide.loadingFinished();
    this.slide.imageLoaded = true;
}

DGE_Slide.prototype.onImageLoad = function()
{
    this.slide.loadingFinished();
    this.slide.imageLoaded = true;
    this.slide.slideshow.displaySlide(this.slide);
}

DGE_Slide.prototype.onImageError = function()
{
    this.slide.loadingFinished();
    this.slide.imageError = true;
}

DGE_Slide.prototype.onImageAbort = function()
{
    this.slide.loadingFinished();
    this.slide.imageAborted = true;
}

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
