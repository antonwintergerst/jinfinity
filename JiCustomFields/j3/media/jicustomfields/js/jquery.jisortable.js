/**
 * @version     $Id: jquery.jisortable.js 136 2014-03-11 11:22:00Z Anton Wintergerst $
 * @package     JiSortable for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiSortable = function(container, options)
    {
        var self = this;
        // Set Default Data
        this.container = container;
        this.btn = null;
        this.tab = null;
        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        this.dragObject = {status:'initialized'};
        this.mousePos = {x:0, y:0};

        // Class Functions
        this.getMouse = function(e) {
            if(this.dragObject.status=='dragging') {
                this.mousePos = {x:e.pageX, y:e.pageY};
                this.clientPos = {x:e.clientX, y:e.clientY};
                this.docScroll = {x:window.pageXOffset, y:window.pageYOffset};
                // Update Drag Element
                if(this.dragObject.element!=null) {
                    var top = Math.round(this.dragObject.startOffset.y - (this.dragObject.size.y/2) + (this.mousePos.y-this.dragObject.startPos.y));
                    this.dragObject.element.style.top = top+'px';

                    // Find Destination Element
                    var that = this;
                    setTimeout(function() {
                        if(that.dragObject!=null && that.dragObject.element!=null) {
                            // Push Drag Element behind other elements
                            that.dragObject.element.style.zIndex = '-999';
                            // Now get the destination element
                            that.dragObject.dest = document.elementFromPoint(that.mousePos.x + that.docScroll.x, that.mousePos.y - that.docScroll.y);
                            // Bring the Drag Element back to the front
                            that.dragObject.element.style.zIndex = '999';
                        }
                    }, 100);
                    var found = this.findDest();
                    if(found==1) {
                        // Remove old dropdests
                        var dropdests = jQuery('.dropdest, .dropbefore, .dropafter');
                        jQuery.each(dropdests, function(index, dropdest) {
                            jQuery(dropdest).removeClass('dropdest dropbefore dropafter');
                        });
                        // Add new dropdest
                        jQuery(this.dragObject.dest).addClass('dropdest');
                        if(this.insertpos=='before') {
                            jQuery(this.dragObject.dest).addClass('dropbefore');
                        } else {
                            jQuery(this.dragObject.dest).addClass('dropafter');
                        }
                    }
                }
            }
        };
        this.findDest = function() {
            var found = 0;
            // Check if destination exists
            if(this.dragObject.dest!=null) {
                // Make sure the destination is in the same container
                if(jQuery(this.dragObject.dest).parent().is(jQuery(this.dragObject.element).parent()) && jQuery(this.dragObject.dest).attr('class').indexOf('nodrop')==-1) {
                    this.insertpos = 'before';
                    found = 1;
                } else {
                    // Check if parents parent is in the same container etc
                    var parents = jQuery(this.dragObject.dest).parents();
                    jQuery.each(parents, function(index, e) {
                        if(jQuery(e).parent()!=null) {
                            if(jQuery(e).parent().is(jQuery(self.dragObject.element).parent()) && jQuery(e).attr('class').indexOf('nodrop')==-1) {
                                self.insertpos = 'before';
                                found = 1;
                                self.dragObject.dest = e;
                            }
                        }
                    });
                }
                if(found==0) {
                    if(this.mousePos.y<jQuery(this.first).offset().top) {
                        this.insertpos = 'before';
                        this.dragObject.dest = this.first;
                        found = 1;
                    } else if(this.mousePos.y>(jQuery(this.last).offset().top+jQuery(this.last).height())) {
                        this.insertpos = 'after';
                        this.dragObject.dest = this.last;
                        found = 1;
                    }
                }
            }
            return found;
        };
        this.dragField = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            var btn = jQuery(sender).closest(this.btn);
            // Prevent IE Actions
            if(this.ie) {
                // Remove IE Border
                btn.hideFocus=true;
                document.ondragstart = function () { return false; };
            }

            /*var fieldid = sender.rel;
             if(fieldid!=null) {
             // Get Field Properties
             var fid = (fieldid.replace('newfield', '')).replace('formfield', '');
             var isNew = (document.getElementById('formfield'+fid)==null)? 'new':'';

             var typeElement = document.getElementById(isNew+'fieldtype'+fid);
             }*/

            //if(this.dragObject.status!='dragging' && typeElement!=null) {
            this.insertpos = 'before';
            this.sortables = jQuery(this.tab);
            var jid = this.findJID(btn);
            var tab = null;
            if(jid!=null) {
                tab = jQuery(this.tab+'.'+jid);
            }
            if(tab!=null && this.dragObject.status!='dragging') {
                /*if(jQuery(sender).parent('table').parent('table')==jQuery('fieldscontainer')) {
                 var type = 'field';
                 } else {
                 var type = jQuery(typeElement).val();
                 }
                 this.dragObject.fieldtype = jQuery(typeElement).val();
                 if(jQuery(typeElement).val()=='textarea') {
                 // Remove tinyMCE before it crashes
                 if(tinyMCE!=null) tinyMCE.execCommand('mceRemoveControl', false, fieldid);
                 }

                 this.dragObject.fieldid = fieldid;
                 this.dragObject.type = type;*/
                //var container = jQuery(sender).parent('table').parent('tr');
                //var parent = jQuery(container).parent('table');

                var tab = jQuery(tab).get(0);
                var parent = jQuery(tab).parent().get(0);
                // Save Parent Styling
                this.dragObject.parentposition = parent.style.position;
                this.dragObject.parentdisplay = parent.style.display;
                // Set Parent Styling
                jQuery(parent).css({
                    'position':'relative',
                    'display':'block'
                });

                var pPos = this.getCoords(parent);
                var ePos = this.getCoords(tab);
                var offsetX = ePos.x - pPos.x;
                var offsetY = ePos.y - pPos.y;
                this.dragObject.startOffset = {x: offsetX, y: offsetY};
                this.dragObject.startPos = ePos;
                var eSize = this.getSize(tab);
                this.dragObject.size = eSize;
                this.dragObject.element = tab;

                // Save Drag Styling
                this.dragObject.css = {
                    'position':jQuery(this.dragObject.element).css('position'),
                    'display':jQuery(this.dragObject.element).css('display'),
                    'background':jQuery(this.dragObject.element).css('background'),
                    'width':jQuery(this.dragObject.element).css('width'),
                    'opacity':jQuery(this.dragObject.element).css('opacity'),
                    'zindex':jQuery(this.dragObject.element).css('zindex'),
                    'top':jQuery(this.dragObject.element).css('top')
                };

                // Set Drag Style
                jQuery(this.dragObject.element).css({
                    'position':'absolute',
                    'display':'block',
                    'background':'#FFF',
                    'width':eSize.x+'px',
                    'opacity':'0.8'
                });
                this.first = jQuery(this.sortables).not(this.dragObject.element).first();
                this.last = jQuery(this.sortables).not(this.dragObject.element).last();

                jQuery(document).on('mouseup', this.dropFieldHandler);
                jQuery(this.dragObject.element).on('mouseup', this.dropFieldHandler);
                this.dragObject.status = 'dragging';
            }
        };
        this.dropField = function(e) {
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            if(this.dragObject.element!=null) {
                // Restore Styling
                jQuery(this.dragObject.element).css({
                    'position':'',
                    'display':'',
                    'background':'',
                    'width':'',
                    'opacity':'',
                    'zindex':'',
                    'top':''
                });
            }

            if((this.dragObject.status=='dragging' || this.dragObject.status=='updating') && this.dragObject.element!=null) {
                this.dragObject.status = 'updating';
                var found = this.findDest();
                if(found==1) {

                    // Insert drag element before dest
                    if(this.insertpos=='before') {
                        jQuery(this.dragObject.element).insertBefore(this.dragObject.dest);
                    } else {
                        jQuery(this.dragObject.element).insertAfter(this.dragObject.dest);
                    }

                }
            }
            if(this.dragObject.element!=null) {
                // Remove old dropdests
                var dropdests = jQuery('.dropdest, .dropbefore, .dropafter');
                jQuery.each(dropdests, function(index, dropdest) {
                    jQuery(dropdest).removeClass('dropdest dropbefore dropafter');
                });
                var parent = jQuery(this.dragObject.element).closest('table').get(0);
                if(parent!=null) {
                    parent.style.position = this.dragObject.parentposition;
                    parent.style.display = this.dragObject.parentdisplay;
                }
                // Dealloc
                jQuery(this.dragObject.element).off('mouseup', this.dropFieldHandler);
                jQuery(document).off('mouseup', this.dropFieldHandler);
                this.dragObject = {status:'deinitialized'};
            }
        };
        this.getSize = function(e) {
            //var width = Math.max(e.scrollWidth, e.offsetWidth, e.clientWidth);
            //var height = Math.max(e.scrollHeight, e.offsetHeight, e.clientHeight);
            var width = jQuery(e).width();
            var height = jQuery(e).height();
            return {x:width, y:height};
        };
        this.getCoords = function(e) {
            var x, y = 0;
            x = e.offsetLeft;
            y = e.offsetTop;
            e = e.offsetParent;
            while(e != null) {
                x = parseInt(x) + parseInt(e.offsetLeft);
                y = parseInt(y) + parseInt(e.offsetTop);
                e = e.offsetParent;
            }
            return {x:x, y:y};
        };
        this.findJID = function(e) {
            var jid = null;
            var classparts = jQuery(e).attr('class').split(' ');
            jQuery.each(classparts, function(index, classname) {
                if(classname.indexOf('jid')!=-1) {
                    jid = classname;
                }
            });
            return jid;
        };
        // Setup Handlers
        this.dragFieldHandler = function(e) {self.dragField(e);};
        this.dropFieldHandler = function(e) {self.dropField(e);};
        this.getMouseHandler = function(e) {self.getMouse(e);};
        this.nothingHandler = function(e) {
            if(self.dragObject.status!='dragging') {
                // Prevent Default Actions
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        };
        // Init
        this.init = function() {
            // Check if we're in IE (That awful relic)
            this.ie = document.all?true:false;
            /*if(!this.ie) {
                // Listen for mouse changes
                document.captureEvents(Event.MOUSEMOVE);
            }*/
            jQuery(document).on('mousemove', this.getMouseHandler);
            this.btns = jQuery(this.btn);
            jQuery.each(this.btns, function(index, btn) {
                jQuery(btn).on({
                    'mousedown': self.dragFieldHandler,
                    // Prevent Text Selections On All Browsers
                    'selectstart': this.nothingHandler,
                    'click': this.nothingHandler,
                    'focus': this.nothingHandler,
                    'dragstart': this.nothingHandler
                });
            });
        };
        this.init();
    };
    jQuery.fn.jisortable = function(options) {
        var element = jQuery(this);
        // Create new class
        var jisortable = new JiSortable(this, options);
        // Set and return class data
        element.data('jisortable', jisortable);
        return jisortable;
    };
})(jQuery);