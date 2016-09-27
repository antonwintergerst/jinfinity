/**
 * @version     $Id: jquery.jigrid.js 067 2014-11-05 13:20:00Z Anton Wintergerst $
 * @package     JiGrid for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiGrid = function(container, options)
    {
        var self = this;
        this.setDefaultOptions = function() {
            this.setminheight = true;
            this.equalizeheights = true;
            this.autospan = true;
            this.hidesmall = true;
            this.screentype = 'desktop';
            this.tvwidth = 1920;
            this.desktopwidth = 980;
            this.tabletwidth = 768;
            this.phonewidth = 480;
            this.grid = null;
            this.cells = null;
            this.activerebuild = true;
            this.rebuildlimit = 250;
            this.iterationspeed = 10;
            this.debug = false;
        }
        this.setDefaultOptions();

        // Set User Options
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }
        this.log = function(data, stamp) {
            if(this.debug && typeof console!=undefined) {
                if(stamp) {
                    var timestamp = '[' + Date.now() + '] ';
                    console.log(timestamp+data);
                } else {
                    console.log(data);
                }
            }
        };
        this.setPrivateOptions = function() {
            this.currentbuild = [];
            this.windowwidthDx = 0;
            this.windowwidth = jQuery(window).width();
            this.jidcount = 0;
        }
        this.setPrivateOptions();

        this.rebuild = function() {
            this.log('Starting Rebuild', true);

            if(this.debug) var t1 = Date.now();
            this.lastwindowwidth = this.windowwidth;
            this.windowwidth = jQuery(window).width();
            this.windowwidthDx = this.windowwidth - this.lastwindowwidth;

            this.setScreenType();

            this.rebuildRows();
            if(this.debug) var timeelapsed = Date.now() - t1;
            this.log('Finished Rebuild in '+timeelapsed+'ms', true);
        };
        this.limitedRebuild = function() {
            jQuery('.gridloaded').addClass('gridreload').removeClass('gridloaded');
            if(!this.activerebuild) {
                if(this.rebuildTimer!=null) clearTimeout(this.rebuildTimer);
                this.rebuildTimer = null;
            }
            if(this.rebuildTimer==null) {
                this.rebuildTimer = setTimeout(function() {
                    self.log('Delayed Rebuild');
                    self.rebuild();
                    clearTimeout(self.rebuildTimer);
                    self.rebuildTimer = null;
                }, this.rebuildlimit);
            }
        };
        /**
         * Does the heavy dom crawling to set this.grid
         */
        this.getRowsInDocument = function() {
            if(this.grid==null) {
                this.log('Finding Rows', true);
                this.grid = {
                    'rows':[],
                    'total':0
                };
                jQuery('.jirow').each(function(index, rowelement) {
                    // Build row object
                    var jid = self.getJID(rowelement);
                    var row = {
                        'e':'.jid'+jid,
                        'jid':jid,
                        'width':jQuery(rowelement).outerWidth(),
                        'cols':12,
                        'ypercent':100
                    };
                    // Get row variables attached to class
                    var classes = jQuery(rowelement).attr('class').split(' ');
                    jQuery.each(classes, function(index, classname) {
                        if(classname.substr(0, 5)=='cols-') {
                            row.cols = parseInt(classname.replace('cols-', ''));
                        }
                        if(classname.substr(0, 7)=='colstv-') {
                            row.colstv = parseInt(classname.replace('colstv-', ''));
                        }
                        if(classname.substr(0, 11)=='colstablet-') {
                            row.colstablet = parseInt(classname.replace('colstablet-', ''));
                        }
                        if(classname.substr(0, 10)=='colsphone-') {
                            row.colsphone = parseInt(classname.replace('colsphone-', ''));
                        }
                        if(classname.substr(0, 9)=='ypercent-') {
                            row.ypercent = parseInt(classname.replace('ypercent-', ''));
                        }
                        if(classname.substr(0, 11)=='ypercenttv-') {
                            row.ypercenttv = parseInt(classname.replace('ypercenttv-', ''));
                        }
                        if(classname.substr(0, 15)=='ypercenttablet-') {
                            row.ypercenttablet = parseInt(classname.replace('ypercenttablet-', ''));
                        }
                        if(classname.substr(0, 14)=='ypercentphone-') {
                            row.ypercentphone = parseInt(classname.replace('ypercentphone-', ''));
                        }
                    });
                    row.cells = [];
                    var cells = self.closestChildren(rowelement, '.jicell');
                    //console.log(cells);
                    //var cells = jQuery(rowelement).children('.outer').children('.jicell');
                    //if(cells.length==0) cells = jQuery(rowelement).children('.jicell');
                    jQuery.each(cells, function(c, cellelement) {
                        // Build cell object
                        var jid = self.getJID(cellelement);
                        var cell = {
                            'e':'.jid'+jid,
                            'jid':jid,
                            'span':1,
                            'minwidth':0,
                            'autospan':false
                        };
                        jQuery(cell.e).addClass('pjid'+row.jid);
                        // Get cell variables attached to class
                        var classes = jQuery(cellelement).attr('class').split(' ');
                        jQuery.each(classes, function(index, classname) {
                            if(classname.substr(0, 5)=='span-') {
                                cell.span = parseInt(classname.replace('span-', ''));
                            }
                            if(classname.substr(0, 5)=='minw-') {
                                cell.minwidth = parseInt(classname.replace('minw-', ''));
                            }
                            if(classname=='autospan') cell.autospan = true;
                        });
                        row.cells.push(cell);
                    });
                    row.total = row.cells.length;
                    self.grid.rows.push(row);
                });
                this.grid.total = this.grid.rows.length;
                this.log('Found '+this.grid.total+' Rows', true);
            }
        };
        /**
         * Method to get and set (if required) a unique JID for a HTML element
         * @param e
         * @returns int JID
         */
        this.getJID = function(e) {
            var jid = 0;
            var allclasses = jQuery(e).attr('class');
            if(allclasses!=null) {
                var classparts = allclasses.split(' ');
                jQuery.each(classparts, function(index, classname) {
                    if(classname.substr(0, 3)=='jid') {
                        jid = parseInt(classname.replace('jid', ''));
                    }
                });
            }
            if(jid==0) {
                this.jidcount++;
                jQuery(e).addClass('jid'+this.jidcount);
                jid = this.jidcount;
            }
            return jid;
        };
        /**
         * Handle iteration asynchronously
         * @param array
         * @param process
         * @param context
         */
        this.asyncloop = function(array, process, context){
            var items = array.concat(); //clone the array
            // cancel previous build
            if(this.currentbuild.length>0) {
                for(var i=0; i<this.currentbuild.length; i++) {
                    clearTimeout(this.currentbuild.splice(i, 1));
                }
            }
            this.currentbuild.push(setTimeout(function(){
                var item = items.shift();
                process.call(context, item);

                if (items.length > 0){
                    self.currentbuild.push(setTimeout(arguments.callee, self.iterationspeed));
                }
            }, self.iterationspeed));
        };
        this.rebuildRows = function() {
            this.getRowsInDocument();
            if(this.grid!=null) {
                this.asyncloop(this.grid.rows, function(row) {
                    if(row!=null) {
                        row.width = jQuery(row.e).outerWidth();
                        // Only process rows that will be visible
                        if(this.windowwidthDx<0 && row.width<20 && this.hidesmall) {
                            jQuery(row.e+':visible').addClass('hidesmall');
                        } else {
                            this.rebuildCells(row);
                        }
                    }
                }, this);
            }
        };
        this.rebuildCells = function(row) {
            var cols = row.cols;
            var ypercent = row.ypercent;
            // Screen context overrides
            if(this.screentype=='tv') {
                if(row.colstv!=null) cols = row.colstv;
                if(row.ypercenttv!=null) ypercent = row.ypercenttv;
            } else if(this.screentype=='tablet') {
                if(row.colstablet!=null) cols = row.colstablet;
                if(row.ypercenttablet!=null) ypercent = row.ypercenttablet;
            } else if(this.screentype=='phone') {
                if(row.colsphone!=null) cols = row.colsphone;
                if(row.ypercentphone!=null) ypercent = row.ypercentphone;
            }

            var cellsize = Math.floor(row.width/cols);
            var cellheight = cellsize*0.01*ypercent;

            // remove old cell height
            jQuery('.pjid'+row.jid+':visible').css('height', '');

            // Equalize cell heights across columns and calculate span
            //var tallest = (self.setminheight)? cellheight : 0;
            var tallest = 0;
            var multicol = false;
            var totalwidth = 0;
            var totalwidthb = 0;

            var total = row.total;
            var i = 0;
            while(i<total) {
                var cell = row.cells[i];
                cell.width = (cols>1)? (cell.span/cols)*100 : 100;
                if(cell.width>100) cell.width = 100;
                if(cell.width==100) {
                    jQuery(cell.e).addClass('fullwidth');
                } else {
                    multicol = true;
                    jQuery(cell.e).removeClass('fullwidth');
                }
                var cellwidthpx = (row.width/100)*cell.width;
                if(self.equalizeheights && cell.width<100 && total>1) {
                    var height = jQuery(cell.e).outerHeight();
                    if(height>tallest) tallest = height;
                }
                if(cellwidthpx<cell.minwidth) {
                    jQuery(cell.e+':visible').addClass('hidesmall');
                } else {
                    jQuery(cell.e).css('width', cell.width+'%').removeClass('hidesmall');
                    totalwidth+= cell.width;
                    if(!cell.autospan) totalwidthb+= cell.width;
                }
                i++;
            }
            // Set autospan
            if(Math.ceil(totalwidth)<100) {
                var autospancell = this.closestChildren(jQuery(row.e), '.jicell.autospan');
                //var autospancell = jQuery(row.e).children('.outer').children('.autospan');
                //if(autospancell.length==0) autospancell = jQuery(row.e).children('.autospan');
                jQuery(autospancell).css('width', (100-totalwidthb)+'%');
            }
            // Re-equalize with accurate tallest
            if(self.equalizeheights && multicol) {
                jQuery('.pjid'+row.jid+':visible').css('height', Math.ceil(tallest)+'px');
            }
            if(cell) jQuery(cell.e).closest('.jigrid').trigger('didrebuild', {'screentype':this.screentype});

            // let other scripts know document is ready
            jQuery('.gridload, .gridreload').addClass('gridloaded').removeClass('gridload gridreload');
        };
        this.closestChildren = function(e, selector) {
            var found = jQuery();
            var elements = jQuery(e);
            while(jQuery(elements).length) {
                found = jQuery(elements).filter(selector);
                if(jQuery(found).length) break;
                elements = jQuery(elements).children();
            }
            return jQuery(found);
        };
        this.closestChild = function(e, selector) {
            var found = jQuery();
            var elements = jQuery(e);
            while(jQuery(elements).length) {
                found = jQuery(elements).filter(selector);
                if(jQuery(found).length) break;
                elements = jQuery(elements).children();
            }
            return jQuery(found).first();
        };
        this.setScreenType = function() {
            var width = jQuery(container).width();

            var screentype = '';
            if(width>=this.tvwidth) {
                screentype = 'tv';
            } else if(width>=this.desktopwidth) {
                screentype = 'desktop';
            } else if(width>=this.tabletwidth) {
                screentype = 'tablet';
            } else {
                screentype = 'phone';
            }
            if(this.screentype!=screentype) {
                jQuery(container).removeClass('tv desktop tablet phone');
                jQuery(container).addClass(screentype);
                jQuery(container).trigger('screenchanged', {'screentype':screentype});
                this.screentype = screentype;
            }
        };
        this.imageLoaded = function() {
            this.imgsloaded++;
            //if(this.imgsloaded>=this.imgcount) this.rebuild();
        };
        this.windowResizeHandler = function(e) {self.limitedRebuild();};
        jQuery(window).on('resize', this.windowResizeHandler);
        this.init = function() {
            this.log('Initialising Grid', true);
            jQuery(container).addClass(this.screentype);
            this.rebuild();

            // rebuild again after images load
            var imgs = jQuery('img');
            if(imgs.length>0) {
                this.imgcount = imgs.length;
                this.imgsloaded = 0;
                jQuery(imgs).each(function() {
                    if(this.complete) {
                        self.imageLoaded();
                    } else {
                        jQuery(this).on('load', function() {
                            self.imageLoaded();
                            self.rebuild();
                        });
                    }
                });
            }
        };
        this.init();
    };
    jQuery.fn.jigrid = function(options) {
        var element = jQuery(this);
        if(element.data('jigrid')) return element.data('jigrid');
        var jigrid = new JiGrid(this, options);
        element.data('jigrid', jigrid);
        return jigrid;
    };
})(jQuery);