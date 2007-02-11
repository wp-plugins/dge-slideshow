// ----------------------------------------------------------------------
// constructor
// ----------------------------------------------------------------------
function FeedImage()
{
    this.node = null;
}

FeedImage.prototype.prepare = function(slideshow, liNode, index)
{
    this.slideshow = slideshow;
    this.node = liNode;

    this.wrapper = document.createElement('div');
    this.link = document.createElement('a');
    this.imageEl = document.createElement('img');
    this.image = new Image();

    // set up hierarchy
    this.wrapper.appendChild(this.link);
    this.link.appendChild(this.imageEl);

    // rip out the anchor
    var link = liNode.getElementsByTagName('a').item(0);
    // grab the image source for later (contents of the anchor)
    this.imagesrc = link.firstChild.nodeValue;
    // finish setting up the link
    this.link.setAttribute('href',link.getAttribute('href'));

    // Defer setting the image source until we get a display or
    // preload call
    this.image.slide = this;
    this.image.onerror = FeedImage.prototype.onImageError;
    this.image.onabort = FeedImage.prototype.onImageAbort;
    this.imageLoading = false;
    this.imageLoaded = false;
    this.imageError = false;
    this.imageAborted = false;
}

FeedImage.prototype.previewVisible = function()
{
    // nothing
}

FeedImage.prototype.previewHidden = function()
{
    this.image.onload = FeedImage.prototype.onImagePreload;
}

FeedImage.prototype.display = function()
{
    if (this.imageLoaded)
    {
	var p = this.slideshow;
	this.imageEl.src = this.imagesrc;
	if (p.displayRatio >= this.ratio)
	{
	    var width = parseInt(p.displayHeight*this.ratio);
	    this.imageEl.style.height = p.displayHeight+'px';
	    this.imageEl.style.width = width+'px';
	    this.wrapper.style.top = 0;
//	    this.wrapper.style.left = parseInt((p.displayWidth-width)/2)+'px';
	}
	else
	{
	    var height = parseInt(p.displayWidth/this.ratio);
	    this.imageEl.style.height = height+'px';
	    this.imageEl.style.width = p.displayWidth+'px';
	    this.wrapper.style.top = parseInt((p.displayHeight-height)/2)+'px';
// 	this.wrapper.style.left = 0;
	}
	this.wrapper.style.width = this.imageEl.style.width;
	this.wrapper.style.height = this.imageEl.style.height;

	this.slideshow.display(this.wrapper);
    }
    else
    {
	this.image.onload = FeedImage.prototype.onImageLoad;
	this.startLoading();
    }
}

// paginator page implementation
FeedImage.prototype.preload = function()
{
    if (!this.imageLoaded)
    {
	this.image.onload = FeedImage.prototype.onImagePreload;
	this.startLoading();
    }
}

// paginator page implementation
FeedImage.prototype.loading = function()
{
    return this.imageLoading;
}

FeedImage.prototype.startLoading = function()
{
    if (!this.imageLoading)
    {
	this.imageLoading = true;
	this.image.src = this.imagesrc; // start it loading
//	DGE_applyClass(this.node.firstChild, 'loading');
    }
}

FeedImage.prototype.loadingFinished = function()
{
     this.imageLoading = false;
     DGE_revokeClass(this.node, 'loading');
     this.ratio = this.image.width/this.image.height;
}

FeedImage.prototype.onImagePreload = function()
{
    this.slide.loadingFinished();
    this.slide.imageLoaded = true;
}

FeedImage.prototype.onImageLoad = function()
{
    this.slide.loadingFinished();
    this.slide.imageLoaded = true;
    this.slide.display();
}

FeedImage.prototype.onImageError = function()
{
    this.slide.loadingFinished();
    this.slide.imageError = true;
}

FeedImage.prototype.onImageAbort = function()
{
    this.slide.loadingFinished();
    this.slide.imageAborted = true;
}
