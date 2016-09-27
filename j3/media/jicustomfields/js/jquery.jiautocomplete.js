/*
 * @version     $Id: jquery.jiautocomplete.js 110 2013-06-12 11:45:00Z Anton Wintergerst $
 * @package     JiAutocomplete for jQuery
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
(function(jQuery){
    var JiAutocomplete = function(container, options)
    {
        var self = this;
        
        // Set Default Data
        this.container = container;
        this.input = null;
        this.receiver = null;
        this.url = null;
        this.delay = 200;
        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        this.count = 0;
        this.ready = 1;
        
        // Class Functions
        this.listen = function() {
            // Add event listeners
            jQuery(this.input).on({
                focus: this.getAutocompleteFocus,
                click: this.getAutocompleteFocus,
                keydown: this.getAutocompleteKeyup,
                change: this.getAutocompleteChange
            });
            // Click outside to close
            jQuery(document).on('click', this.cancelClick);
        };
        this.getAutocomplete = function() {
            // Split the value into value parts and store for future reference
            this.values = this.trimSplit(',', this.input.value);
            if(this.values!=null) {
                // Get cursor position
                var caret = this.getCaret(this.input);
                // Reset Variables
                var offset = 0;
                var start = 0;
                var end = 0;
                for(var v=0; v<this.values.length; v++) {
                    var length = this.values[v].length;
                    end = start+length;
                    //console.log('caret: '+caret+' offset:'+offset);
                    //console.log('length: '+length+' start: '+start+' end: '+end);
                    if(start<=(caret-offset) && (caret-offset)<=end) this.value = v;
                    start+= length;
                    offset = offset+2;
                }
            }
            //console.log('current: '+this.value);
            
            if(this.ready == 1) {
                this.ready = 0;
                this.waiting = 0;
                this.requestAutocomplete();
            } else {
                this.waiting = 1;
                clearTimeout(this.timer);
                this.timer = this.getReady.delay(100, this);
            }
            
        };
        this.getReady = function() {
            if(this.count<this.delay) {
                this.count=this.count+100;
                clearTimeout(this.timer);
                this.timer = this.getReady.delay(100, this);
            } else {
                //console.log('ready at count: '+this.count+'/'+this.delay);
                if(this.waiting==1) {
                    this.ready = 0;
                    this.requestAutocomplete();
                } else {
                    this.ready = 1;
                }
            }
        };
        this.requestAutocomplete = function() {
            this.waiting = 0;
            this.count = 0;
            // Perform JSON request
            jQuery.ajax({dataType:'json', url:self.url,
                type:'post',
                data:{'value': self.values[self.value]}
            }).done(function(response) {
                if(response!=null && response!="false") {
                    var size = self.getSize(self.input);
                    var coords = self.getCoords(self.input);
                    self.getAutocompleteBox();
                    jQuery(self.autobox).css({
                        'top': size.y + coords.y + "px",
                        'left': coords.x + "px",
                        'width': size.x + "px"
                    });
                    self.choices = [];
                    jQuery.each(response, function(index, object) {
                        var choice = jQuery(document.createElement('li'));
                        var atag = jQuery(document.createElement('a')).attr({
                            'rel': object.fid
                        }).html(object.value);
                        jQuery(atag).on('click', self.selectAutocompleteClick);
                        jQuery(choice).append(atag);
                        jQuery(self.autobox).append(choice);
                        self.choices[index] = atag;
                    });
                    self.totalchoices = response.length;
                    self.activechoice = 0;
                    self.highlightChoice();
                } else {
                    self.removeAutocompleteBox();
                }
            });
        };
        this.highlightChoice = function() {
            var elements = jQuery(this.autobox).children('li');
            jQuery.each(elements, function(index, e) {
                if(index!=this.activechoice) jQuery(e).removeClass('active');
            });
            // Highlight new choice
            jQuery(elements[this.activechoice]).addClass('active');
        };
        this.selectAutocomplete = function() {
            // Update current value
            this.values[this.value] = this.choice.innerHTML;
            // Implode input values
            //console.log(this.values);
            var text = this.values.join(', ');
            //this.input.value = text;
            // Force the input to maintain a consistent format: e.g "choice1, choice2, etc"
            var temp = this.trimSplit(',', text);
            this.input.value = temp.join(', ');
            // restore focus
            this.input.focus();
            if(this.receiver!=null) {
                this.receiver.value = this.choice.rel;
                this.receiver.fireEvent('change');
            }
            this.removeAutocompleteBox();
            // Compatibility for embedded labels
            jQuery(this.input).parent().addClass('hidelabel');
        };
        this.getAutocompleteBox = function() {
            this.active = 1;
            this.autobox = jQuery('#autocompletebox');

            if(jQuery(this.autobox).length) {
                // Remove existing options
                jQuery(this.autobox).children().remove();
            } else {
                // Create new container
                this.autobox = jQuery(document.createElement('ul')).attr({
                    'id': 'autocompletebox',
                    'class': 'jiautocompletebox'
                });
                jQuery(document.body).append(this.autobox);
            }
        };
        this.removeAutocompleteBox = function() {
            this.active = 0;
            jQuery(this.autobox).remove();
            jQuery('#autocompletebox').remove();
        };
        this.cancel = function(e) {
            if(this.active==1) {
                // Get Event Target
                var target = (e && e.target) || (event && event.srcElement);
                // Close tab if outside element, otherwise run actions
                if(this.checkparent(target)) {
                    this.waiting = 0;
                    clearTimeout(this.timer);
                    this.removeAutocompleteBox();
                }
                // Prevent default action
                //if(target == input) e.preventDefault();
            }
        };
        this.checkparent = function(t) {
            // Test if click is inside or outside filter elements
            while(t.parentNode){
                if(t == this.input){
                    return false
                }
                t=t.parentNode
            }
            return true
        };
        this.getSize = function(e) {
            var width = jQuery(e).outerWidth();
            var height = jQuery(e).outerHeight();
            return {x:width, y:height};
        };
        this.getCoords = function(e) {
            var pos = jQuery(e).offset();
            var x = 0;
            var y = 0;
            if(pos!=null) {
                x = pos.left;
                y = pos.top;
            }
            return {x:x, y:y};
        };
        this.trimSplit = function(s, t) {
            return t.replace(/(^\s*)|(\s*$)/g, "").split(new RegExp('\\s*'+s+'\\s*'));
        };
        this.getCaret = function(input) {
            var caret = 0;
            if (document.selection) {
                // IE Support
                // Set focus on the element
                input.focus();
                // To get cursor position, get empty selection range
                var sel = document.selection.createRange ();    
                // Move selection start to 0 position
                sel.moveStart ('character', -input.value.length);   
                // The caret position is selection length
                caret = sel.text.length;
            } else if (input.selectionStart || input.selectionStart == '0') {
                // Firefox support
                caret = input.selectionStart;
            }
            return (caret);
        };
    
        // Setup Handlers
        this.getAutocompleteFocus = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            self.getAutocomplete();
        };
        this.getAutocompleteKeyup = function(e) {
            var keycode = jQuery(e.which);
            if(keycode == 38) {
                // Prevent Default Actions
                e.preventDefault();
                e.stopPropagation();
                // Key is Cursor Up
                if(0<self.activechoice) {
                    self.activechoice--;
                } else {
                    self.activechoice = self.totalchoices-1;
                }
                self.highlightChoice();
                return false;
            } else if(keycode == 40) {
                // Prevent Default Actions
                e.preventDefault();
                e.stopPropagation();
                // Key is Cursor Down
                if(self.activechoice<self.totalchoices-1) {
                    self.activechoice++;
                } else {
                    self.activechoice = 0;
                }
                self.highlightChoice();
            } else if(keycode == 13) {
                // Prevent Default Actions
                e.preventDefault();
                e.stopPropagation();
                // Key is Enter
                self.choice = self.choices[self.activechoice];
                self.selectAutocomplete();
            } else {
                // Other Keys
                if(self.receiver!=null) {
                    self.receiver.value = null;
                    self.receiver.fireEvent('change');
                }
                var target = e.target != null ? e.target : e.srcElement;
                self.getAutocomplete();
                // Fire Event
                jQuery(self.input).trigger('change', e);
            }
        };
        this.getAutocompleteChange = function(e) {
            if(self.receiver!=null) {
                self.receiver.value = null;
                // Fire Event
                jQuery(self.receiver).trigger('change', e);
            }
        };
        this.selectAutocompleteClick = function(e) {
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            var target = e.target != null ? e.target : e.srcElement;
            self.choice = target;
            self.selectAutocomplete();
        };
        this.cancelClick = function(e) {
            self.cancel(e);
        };
        // Init
        this.init = function() {
            // Stop default browser auto completers
            jQuery(this.input).attr("autocomplete", "off");
            // Everything is set so start listening
            this.listen();
        };
        this.init();
    };
    jQuery.fn.jiautocomplete = function(options) {
        var element = jQuery(this);
        // Create new class
        var jiautocomplete = new JiAutocomplete(this, options);
        // Set and return class data
        element.data('jiautocomplete', jiautocomplete);
        return jiautocomplete;
    };
})(jQuery);