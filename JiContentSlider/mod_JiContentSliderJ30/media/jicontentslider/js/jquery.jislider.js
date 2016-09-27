/**
 * @version     $Id: jquery.jislider.js 190 2014-11-05 12:55:00Z Anton Wintergerst $
 * @package     JiContentSlider for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiSlider = function(container, options)
    {
        var self = this;
        this.setDefaultOptions = function() {
            this.width = 400;
            this.height = 200;
            this.speed = 250;
            this.slidersize = null;
            this.imgsize = null;
            this.responsive = 1;
            this.autosizing = 'aspectfill';
            this.verticalAlign = 'middle';
            this.horizontalAlign = 'middle';
            this.autoplay = 1;
            this.delay = 8000;
            this.numberslides = 1;
            this.padding = '0';
            this.transition = 'slideleft';

            this.paddingTop = 0;
            this.paddingRight = 0;
            this.paddingBottom = 0;
            this.paddingLeft = 0;
        };
        this.setDefaultOptions();

        if(!jQuery.isPlainObject(options)) options = jQuery.parseJSON(options);
        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        this.setPrivateOptions = function() {
            this.container = container;
            if(this.width>0) this.width += 'px';
            if(this.height>0) this.height += 'px';
            this.numberslides = parseInt(this.numberslides);
            this.speed = parseInt(this.speed);
            this.speedx = this.speed;
            this.isswiping = false;
            this.updatingLayout = null;

            // Setup padding
            var paddingparts = this.padding.split(' ');
            switch(paddingparts.length) {
                case 1:
                    this.paddingTop = parseInt(paddingparts[0]);
                    this.paddingRight = parseInt(paddingparts[0]);
                    this.paddingBottom = parseInt(paddingparts[0]);
                    this.paddingLeft = parseInt(paddingparts[0]);
                    break;
                case 2:
                    this.paddingTop = parseInt(paddingparts[0]);
                    this.paddingRight = parseInt(paddingparts[1]);
                    this.paddingBottom = parseInt(paddingparts[0]);
                    this.paddingLeft = parseInt(paddingparts[1]);
                    break;
                case 3:
                    this.paddingTop = parseInt(paddingparts[0]);
                    this.paddingRight = parseInt(paddingparts[1]);
                    this.paddingBottom = parseInt(paddingparts[2]);
                    this.paddingLeft = 0;
                    break;
                case 4:
                    this.paddingTop = parseInt(paddingparts[0]);
                    this.paddingRight = parseInt(paddingparts[1]);
                    this.paddingBottom = parseInt(paddingparts[2]);
                    this.paddingLeft = parseInt(paddingparts[3]);
                    break;
            }

            // Set Initial State
            this.current = 1;
            this.totalslides = 1;
            this.totalpages = 1;
            this.timer = null;
            this.hovering = false;
        };
        this.setPrivateOptions();
        
        // Actions
        this.prevSlide = function(e) {
            if(this.current<=1) {
                self.speedx = self.speed;
                this.slideTo(this.totalpages);
            } else {
                this.slideTo(this.current-1);
            }
        };
        this.nextSlide = function(e) {
            if(this.current>=this.totalpages) {
                self.speedx = self.speed;
                this.slideTo(1);
            } else {
                this.slideTo(this.current+1);
            }
        };
        this.gotoSlide = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            if(jQuery(target).attr('tag')=='a') {
                this.slideTo(jQuery(target).attr('rel'));
            } else {
                this.slideTo(jQuery(target).closest('a').attr('rel'));
            }
        };
        this.setSlideToIndicators = function(page)
        {
            page = parseInt(page);

            // Set active btn class
            var activebtns = jQuery('.jislidergotobtn.active.'+this.sliderid);
            jQuery.each(activebtns, function(index, btn) {
                jQuery(btn).removeClass('active');
            });
            var gotobtns = jQuery('.jislidergotobtn.'+this.sliderid);
            jQuery.each(gotobtns, function(index, btn) {
                if(btn.rel==page) jQuery(btn).addClass('active');
            });
            // Set active slidebox class
            var activeslideboxes = jQuery(this.container).find('.slidebox.active');
            jQuery.each(activeslideboxes, function(index, slidebox) {
                jQuery(slidebox).removeClass('active');
            });
            var slideboxes = jQuery(this.container).find('.slidebox.page'+page);
            jQuery.each(slideboxes, function(index, slidebox) {
                jQuery(slidebox).addClass('active');
            });
        };
        this.slideTo = function(page) {
            page = parseInt(page);
            this.setSlideToIndicators(page);

            if(this.current!=page) {
                var currentElement = jQuery(this.container).find('.slidebox.page'+this.current+':first');
                var destElement = jQuery(this.container).find('.slidebox.page'+page+':first');
                this.updateImageLayout(jQuery(destElement).find('img.slideimg'));
                switch(this.transition) {
                    case 'slideleft':
                        var leftval = 0;
                        if(destElement[0]!=null) leftval = -jQuery(destElement).position().left;
                        jQuery('.slidesmask.'+this.sliderid).stop().animate({
                            left: leftval
                        }, this.speedx);
                    break;
                    case 'fade':
                        jQuery(destElement).css('z-index', 1);
                        jQuery(currentElement).css('z-index', 3);
                        jQuery(destElement).fadeIn(self.speedx);
                        jQuery(currentElement).fadeOut(self.speedx, function(){
                            jQuery(currentElement).css('z-index', 1);
                        });
                    break;
                }

                this.current = page;
            }
            
            if(this.autoplay==1) {
                if(this.timer!=null) clearInterval(this.timer);
                this.timer = setInterval(self.animateNext, this.delay);
            }
        };
        this.updateLayout = function() {
            if(this.timer!=null) clearInterval(this.timer);
            this.slideTo(this.current);

            // clear old sizes
            jQuery(this.container).removeAttr('style');
            jQuery(this.slidesmask).removeAttr('style');
            jQuery(this.slidescontainer).removeAttr('style');
            jQuery(this.container).find('.slidebox .slide').removeAttr('style');
            jQuery(this.container).find('img.slideimg').removeAttr('style');

            this.updateSliderSize();

            // Update Slides Mask
            jQuery(this.slidesmask).css({
                'width':this.slidersize.x+'px',
                'height':this.slidersize.y+'px'
            });
            // Update Slides Container
            jQuery(this.slidescontainer).css({
               'width':(this.slidersize.x*this.totalslides),
               'height':this.slidersize.y+'px'
            });
            // Update Slide Boxes
            var slideboxes = jQuery(this.container).find('.slidebox');
            var margin = this.paddingTop+'px '+this.paddingRight+'px '+this.paddingBottom+'px '+this.paddingLeft+'px';
            var k = 1;
            var p = 1;
            jQuery.each(slideboxes, function(index, slidebox) {
                if(self.numberslides>1) {
                    if(k>1) {
                        margin = self.paddingTop+'px '+self.paddingRight+'px '+self.paddingBottom+'px 0';
                    } else {
                        margin = self.paddingTop+'px '+self.paddingRight+'px '+self.paddingBottom+'px '+self.paddingLeft+'px';
                    }
                    k++;
                    if(k>self.numberslides) {
                        k=1;
                        p++;
                    }
                }
                jQuery(slidebox).css({
                    'width': self.imgsize.x+'px',
                    'height': self.imgsize.y+'px',
                    'margin': margin
                });
                
            });
            // Update Slides
            var slides = jQuery(this.container).find('.slidebox .slide');
            jQuery.each(slides, function(index, slide) {
                jQuery(slide).css({
                    'width': self.imgsize.x+'px',
                    'height': self.imgsize.y+'px'
                });
            });

            // Update Images
            this.imgratio = (this.imgsize.x/this.numberslides)/this.imgsize.y;

            // update current img
            this.updateImageLayout(jQuery(this.container).find('.slidebox.page'+this.current+' img.slideimg'));
            /*jQuery.each(this.imgs, function(index, img) {
                self.updateImageLayout(img);
            });*/
        };
        this.updateImageLayout = function(img) {
            this.updateSliderSize();
            this.imgratio = (this.imgsize.x/this.numberslides)/this.imgsize.y;
            // reset css ready for new calculations
            jQuery(img).removeAttr('style');
            //jQuery(img).css({'width':'100px', 'height':'auto'});

            // set on dom load
            if(self.autosizing=='aspectfill') {
                // ensure image fills slider
                var imgbounds = self.getElementSize(img);
                var imgratio = imgbounds.x/imgbounds.y;
                if(imgratio>self.imgratio) {
                    // wide image
                    jQuery(img).css({
                        'width':parseInt(self.imgsize.y*imgratio)+'px',
                        'height':self.imgsize.y+'px'
                    });
                } else if(imgratio<self.imgratio) {
                    // tall image
                    jQuery(img).css({
                        'width':self.imgsize.x+'px',
                        'height':parseInt(self.imgsize.x/imgratio)+'px'
                    });
                }
            } else if(self.autosizing=='aspectfit') {
                // Ensure image fits within slider
                jQuery(img).css({
                    'max-width':self.imgsize.x+'px',
                    'max-height':self.imgsize.y+'px'
                });
            } else if(self.autosizing!='none') {
                jQuery(img).css({
                    'width':self.imgsize.x+'px',
                    'height':self.imgsize.y+'px'
                });
            }

            if(self.verticalAlign=='top') {
                jQuery(img).css({'margin-top':'0'});
            } else if(self.verticalAlign=='middle') {
                var imgbounds = self.getElementSize(img);
                var top = (imgbounds.y<self.imgsize.y)? -(self.imgsize.y - imgbounds.y)/2 : 0;
                jQuery(img).css({'margin-top':top+'px'});
            } else if(self.verticalAlign=='bottom') {
                var imgbounds = self.getElementSize(img);
                var top = imgbounds.y-self.imgsize.y;
                jQuery(img).css({
                    'margin-top':top+'px'
                });
            }
            if(self.horizontalAlign=='left') {
                jQuery(img).css({
                    'float':'left'
                });
            } else if(self.horizontalAlign=='none') {
                var imgbounds = self.getElementSize(img);
                var left = parseInt((self.imgsize.x - imgbounds.x)/2);
                jQuery(img).css({'margin-left':left+'px'});
            } else if(self.horizontalAlign=='right') {
                jQuery(img).css({
                    'float':'right'
                });
            }
        };
        this.playSlides = function(e) {
            this.hovering = false;
            if(this.timer!=null) clearInterval(this.timer);
            this.timer = setInterval(self.animateNext, this.delay);
            
            // Remove hover for container
            jQuery(this.container).removeClass('hover');
            
            // Update paddle buttons
            var paddlebtns = jQuery(this.container).find('.paddelbtn');
            jQuery.each(paddlebtns, function(index, btn) {
                if(jQuery(btn).closest('.paddel').attr('class').indexOf('hover')==-1) {
                    jQuery(btn).animate({
                        opacity: 0
                    }, 250);
                }
            });
        };
        this.pauseSlides = function(e) {
            this.hovering = true;
            if(this.timer!=null) clearInterval(this.timer);
            
            // Set hover for container
            jQuery(this.container).addClass('hover');
            
            // Update paddle buttons
            var paddlebtns = jQuery(this.container).find('.paddelbtn');
            jQuery.each(paddlebtns, function(index, btn) {
                if(jQuery(btn).closest('.paddel').attr('class').indexOf('hover')==-1) {
                    jQuery(btn).animate({
                        opacity: 0.5
                    }, 250);
                }
            });
        };
        this.showPaddle = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            jQuery(this.container).addClass('hoverpaddle');
            
            jQuery(target).closest('.paddel').addClass('hover');
            jQuery(target).closest('.paddel').children().animate({
                opacity: 1
            }, 250);
            
        };
        this.hidePaddle = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            jQuery(this.container).removeClass('hoverpaddle');
            var opacity = (this.hovering)? 0.5:0;
            
            jQuery(target).closest('.paddel').removeClass('hover');
            var opacity = (this.hovering)? 0.5:0;
            jQuery(target).closest('.paddel').children().animate({
                opacity: opacity
            }, 250);
        };
        this.enterSlide = function(e) {
            var target = e.target != null ? e.target : e.srcElement
            // Set hover for container
            jQuery(target).closest('.slidebox').addClass('hover');
        };
        this.leaveSlide = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            // Remove hover for container
            jQuery(target).closest('.slidebox').removeClass('hover');
        };
        
        this.getElementSize = function(e) {
            var parent = jQuery(e).closest('.slidebox');
            var parentcss = jQuery(parent).attr('style');
            jQuery(parent).css({
                'display':'block',
                'opacity':1
            });
            var css = jQuery(e).attr('style');
            jQuery(e).css({
                'display':'block',
                'opacity':1
            });
            var width = jQuery(e).width();
            var height = jQuery(e).height();
            jQuery(e).attr('style', css);
            jQuery(parent).attr('style', parentcss);
            return {x:width, y:height};
        };
        // Setup Handlers
        this.prevSlideHandler = function(e) {
            self.speedx = self.speed;
            self.prevSlide(e);
            e.preventDefault();
            e.stopPropagation();
        };
        this.nextSlideHandler = function(e) {
            self.speedx = self.speed;
            self.nextSlide(e);
            e.preventDefault();
            e.stopPropagation();
        };
        this.gotoSlideHandler = function(e) {
            self.speedx = self.speed;
            self.gotoSlide(e);
            e.preventDefault();
            e.stopPropagation();
        };
        this.mouseLeft = function(e) {
            if(self.isswiping) {
                jQuery('.slidesmask.'+self.sliderid).css('left', self.leftval+'px');
                jQuery(this.container).trigger('swipeUp');
                self.isswiping = false;
            }
        };
        this.playHandler = function(e) {
            self.playSlides(e);
        };
        this.pauseHandler = function(e) {self.pauseSlides(e);};
        this.windowResizeHandler = function(e) {self.updateLayout();};
        this.mouseLeftHandler = function(e) {self.mouseLeft(e);};
        this.showPaddleHandler = function(e) {self.showPaddle(e);};
        this.hidePaddleHandler = function(e) {self.hidePaddle(e);};
        this.enterSlideHandler = function(e) {self.enterSlide(e);};
        this.leaveSlideHandler = function(e) {self.leaveSlide(e);};

        this.updateSliderSize = function()
        {
            //if(this.slidersize==null) {
                this.slidersize = this.getElementSize(this.container);
                if(this.numberslides==1) {
                    this.imgsize = {
                        'x':this.slidersize.x - (this.paddingLeft + this.paddingRight),
                        'y':this.slidersize.y - (this.paddingTop + this.paddingBottom)
                    };
                } else {
                    this.imgsize = {
                        'x':(this.slidersize.x/this.numberslides) - (this.paddingLeft + this.paddingRight) + (this.paddingRight*(this.numberslides-1))/this.numberslides,
                        'y':this.slidersize.y - (this.paddingTop + this.paddingBottom)
                    };
                }
            //}
            jQuery(this.container).removeClass('large');
            jQuery(this.container).removeClass('medium');
            jQuery(this.container).removeClass('small');
            if(this.slidersize.x>980) {
                jQuery(this.container).addClass('large');
            } else if(this.slidersize.x>460) {
                jQuery(this.container).addClass('medium');
            } else {
                jQuery(this.container).addClass('small');
            }

            jQuery(this.slidesmask).css({
                'width':this.slidersize.x+'px',
                'height':this.slidersize.y+'px'
            });

            jQuery(this.slidescontainer).css({
                'width':(this.slidersize.x*this.totalslides),
                'height':this.slidersize.y+'px'
            });
        };
        this.init = function() {
            this.deinit();
            // Init
            if(this.responsive!=false && this.responsive!='false') jQuery(window).on('resize', this.windowResizeHandler);
            //jQuery(window).on('mouseup', this.mouseLeftHandler);
            // Setup Container
            jQuery(this.container).on('mouseenter', this.delayedPause);
            jQuery(this.container).on('mouseleave', this.playHandler);
            
            var classparts = jQuery(this.container).attr('class').replace('jislider').split(' ');
            jQuery.each(classparts, function(index, classname) {
                if(classname.indexOf('slider')!=-1) {
                    self.sliderid = classname;
                }
            });
            jQuery(this.container).css({
                'width':this.width,
                'height':this.height
            });

            jQuery(this.container).addClass('numberslides'+this.numberslides);
            
            // Set Contents
            this.slides = jQuery(this.container).find('.slide');
            this.totalslides = jQuery(this.slides).length;
            this.totalpages = Math.ceil(this.totalslides/this.numberslides);
            // Create Slides Mask
            this.slidesmask = jQuery(document.createElement('div')).attr({'class': 'slidesmask '+this.sliderid});

            jQuery(this.slidesmask).insertAfter(this.slides.first());
            // Create Slides Container
            this.slidescontainer = jQuery(document.createElement('div')).attr({'class': 'slides '+this.sliderid});

            jQuery(this.slidesmask).append(this.slidescontainer);
            this.updateSliderSize();
            
            // Create Slide Boxes
            var k = 1;
            var p = 1;
            var margin = this.paddingTop+'px '+this.paddingRight+'px '+this.paddingBottom+'px '+this.paddingLeft+'px';
            jQuery.each(this.slides, function(index, slide) {
                if(self.numberslides>1) {
                    if(k>1) {
                        margin = self.paddingTop+'px '+self.paddingRight+'px '+self.paddingBottom+'px 0';
                    } else {
                        margin = self.paddingTop+'px '+self.paddingRight+'px '+self.paddingBottom+'px '+self.paddingLeft+'px';
                    }
                }
                var slidebox = jQuery(document.createElement('div')).attr({'class': 'slidebox slide'+(index+1)+' page'+p});
                jQuery(slidebox).css({
                    'width': self.imgsize.x+'px',
                    'height': self.imgsize.y+'px',
                    'margin': margin
                });
                jQuery(self.slidescontainer).append(slidebox);
                jQuery(slide).css({
                    'width':self.imgsize.x+'px',
                    'height': self.imgsize.y+'px'
                });
                jQuery(slide).appendTo(slidebox);
                jQuery(slidebox).on('mouseenter', self.enterSlideHandler);
                jQuery(slidebox).on('mouseleave', self.leaveSlideHandler);
                k++;
                if(k>self.numberslides) {
                    p++;
                    k = 1;
                }
            });
            
            // Setup Images
            this.imgs = jQuery(this.container).find('img.slideimg');
            this.imgratio = (this.imgsize.x/this.numberslides)/this.imgsize.y;
            
            jQuery.each(this.imgs, function(index, img) {
                self.updateImageLayout(img);
                // set on img load
                jQuery(img).load(function(){
                    self.updateImageLayout(img);
                    // reset css ready for new calculations
                    /*jQuery(img).removeAttr('style');

                    if(self.autosizing=='aspectfill') {
                        // Ensure image fills slider
                        // Set size on dom load
                        var imgbounds = self.getElementSize(img);
                        var imgratio = imgbounds.x/imgbounds.y;
                        if(imgratio>self.imgratio) {
                            // wide image
                            jQuery(img).css({
                                'width':parseInt(self.imgsize.x*imgratio)+'px',
                                'height':self.imgsize.x+'px'
                            });
                        } else if(imgratio<self.imgratio) {
                            // tall image
                            jQuery(img).css({
                                'width':self.imgsize.y+'px',
                                'height':parseInt(self.imgsize.y/imgratio)+'px'
                            });
                        }
                    } else if(self.autosizing=='aspectfit') {
                        // Ensure image fits within slider
                        jQuery(img).css({
                            'max-width':self.imgsize.x+'px',
                            'max-height':self.imgsize.y+'px'
                        });
                    } else if(self.autosizing!='none') {
                        jQuery(img).css({
                            'width':self.imgsize.x+'px',
                            'height':self.imgsize.y+'px'
                        });
                    }

                    if(self.verticalAlign=='top') {
                        jQuery(img).css({'margin-top':'0'});
                    } else if(self.verticalAlign=='middle') {
                        // Set size on dom load
                        var imgbounds = self.getElementSize(img);
                        var top = (imgbounds.y<self.imgsize.y)? -(self.imgsize.y - imgbounds.y)/2 : 0;
                        jQuery(img).css({'margin-top':top+'px'});
                    } else if(self.verticalAlign=='bottom') {
                        // Set size on dom load
                        var imgbounds = self.getElementSize(img);
                        var top = imgbounds.y-self.imgsize.y;
                        jQuery(img).css({
                            'margin-top':top+'px'
                        });
                    }
                    if(self.horizontalAlign=='left') {
                        jQuery(img).css({
                            'float':'left'
                        });
                    } else if(self.horizontalAlign=='none') {
                        var imgbounds = self.getElementSize(img);
                        var left = parseInt((self.imgsize.x - imgbounds.x)/2);
                        jQuery(img).css({'margin-left':left+'px'});
                    } else if(self.horizontalAlign=='right') {
                        jQuery(img).css({
                            'float':'right'
                        });
                    }*/
                });
            });
            // Setup Links
            var links = jQuery(this.container).find('a');
            jQuery.each(links, function(index, link) {
                jQuery(link).removeAttr('title');
            });
            
            // Setup Buttons
            this.prevbtns = jQuery('.jisliderprevbtn.'+this.sliderid)
            jQuery.each(this.prevbtns, function(index, btn) {
                // Paddel Buttons
                if(jQuery(btn).closest('.paddel').attr('class').indexOf('paddel')!=-1) {
                    jQuery(btn).closest('.paddel').on('click', self.prevSlideHandler);
                    // Appear/Disappear on hover
                    jQuery(btn).closest('.paddel').on('mouseenter', self.showPaddleHandler);
                    jQuery(btn).closest('.paddel').on('mouseleave', self.hidePaddleHandler);
                } else {
                    jQuery(btn).on('click', self.prevSlideHandler);
                }
            });
            this.nextbtns = jQuery('.jislidernextbtn.'+this.sliderid)
            jQuery.each(this.nextbtns, function(index, btn) {
                // Paddel Buttons
                if(jQuery(btn).closest('.paddel').attr('class').indexOf('paddel')!=-1) {
                    jQuery(btn).closest('.paddel').on('click', self.nextSlideHandler);
                    // Appear/Disappear on hover
                    jQuery(btn).closest('.paddel').on('mouseenter', self.showPaddleHandler);
                    jQuery(btn).closest('.paddel').on('mouseleave', self.hidePaddleHandler);
                } else {
                    jQuery(btn).on('click', self.nextSlideHandler);
                }
            });
            this.gotobtns = jQuery('.jislidergotobtn.'+this.sliderid)
            jQuery.each(this.gotobtns, function(index, btn) {
                jQuery(btn).on('click', self.gotoSlideHandler);
            });
            // Setup Swipe
            jQuery(this.container).swipe({
                swipeStatus:function(event, phase, direction, distance, duration, fingerCount) {
                    if(phase=='start') {
                        self.swipedir = null;
                        self.isswiping = true;
                        self.leftval = parseInt(jQuery('.slidesmask.'+self.sliderid).css('left'));
                        if(self.leftval==null) self.leftval = 0;
                        self.maxpull = parseInt(jQuery('.slidesmask.'+self.sliderid).css('width'));
                    } else if(phase=='move' && (direction=='left' || direction=='right')) {
                        var dirmod = 1;
                        self.swipedir = 'right';
                        if(direction=='left') {
                            dirmod = -1;
                            self.swipedir = 'left';
                        }
                        var dx = (distance<self.maxpull)? distance : self.maxpull;

                        jQuery('.slidesmask.'+self.sliderid).css('left', (self.leftval+dx*dirmod)+'px');
                    } else if(phase=='end') {
                        self.isswiping = false;
                        if(self.swipedir=='left') {
                            self.speedx = 50;
                            self.nextSlide();
                        } else if(self.swipedir=='right') {
                            self.speedx = 50;
                            self.prevSlide();
                        } else {
                            jQuery('.slidesmask.'+self.sliderid).css('left', self.leftval+'px');
                        }
                    } else if(phase=='cancel') {
                        self.isswiping = false;
                        jQuery('.slidesmask.'+self.sliderid).css('left', self.leftval+'px');
                    }
                },
                threshold:0,
                maxTimeThreshold:2500,
                fingers:1,
                allowPageScroll:"vertical",
                triggerOnTouchEnd:true,
                triggerOnTouchLeave:true
            });
            
            // Set First Slide
            if(this.transition=='fade') {
                jQuery(this.container).find('.slidebox').not('.slidebox.page'+this.current).hide();
                this.current = 1;
                this.setSlideToIndicators(1);
            } else {
                this.slideTo(1);
            }

            // update layout after grid has finished
            jQuery(this.container).closest('.jigrid').on('didrebuild', function() {
                self.updateLayout();
            });
        }
        
        this.animateNext = function() {
            self.nextSlide();
        };
        
        // Play/Pause on hover
        this.delayedPause = function(e) {
            setTimeout(function() {self.pauseSlides();}, 50);
        };
        
        // Keyboard controls
        jQuery(document).keydown(function(e){
            if(e.keyCode==37) {
                self.prevSlide();
            } else if(e.keyCode==39) {
                self.nextSlide();
            }
        });
        this.deinit = function() {
            if(this.timer!=null) clearInterval(this.timer);
            this.timer = null;
        };
        this.init();
        // Start Autoplay
        if(this.autoplay==1) {
            if(this.timer!=null) clearInterval(this.timer);
            this.timer = setInterval(self.animateNext, this.delay);
        }
    };
    jQuery.fn.jislider = function(options) {
        var element = jQuery(this);
        if(element.data('jislider')) return element.data('jislider');
        var jislider = new JiSlider(this, options);
        element.data('jislider', jislider);
        return jislider;
    };
})(jQuery);