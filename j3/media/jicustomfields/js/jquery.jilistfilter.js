/**
 * @version     $Id: jquery.jilistfilter.js 107 2014-04-07 11:37:00Z Anton Wintergerst $
 * @package     JiListFilter for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function($){
    var JiListFilter = function(options)
    {
        var self = this;
        // Set Default Options
        this.container = null;
        this.data = null;
        this.selectfrom = 0;
        this.shiftselect = false;
        this.toggletask = 'include';
        this.url = 'index.php?option=com_jicustomfields&format=json';
        
        // Setup Options
        jQuery.each(options, function(index, value) {
            if(index=='data') {
                self.data = jQuery.parseJSON(value);
            } else {
                self[index] = value;
            }
        });
        if(this.openurl==null) this.openurl = this.url+'&task=mediamanager.open';
        if(this.includeurl==null) this.includeurl = this.url+'&task=mediamanager.includepath';
        if(this.excludeurl==null) this.excludeurl = this.url+'&task=mediamanager.excludepath';
        // Actions
        this.open = function(path) {
            jQuery.ajax({dataType:'json', url:this.openurl,
                type:'post',
                data:{'ffpath':path}
            }).done(function(response) {
                if(response!=null) {
                    if(response.valid==true) {
                        self.data = response;
                        self.buildlist();
                    }
                }
            });
        };
        this.search = function(searchword) {
            jQuery.ajax({dataType:'json', url:this.openurl,
                type:'post',
                data:{'ffsearchword':searchword}
            }).done(function(response) {
                if(response!=null) {
                    if(response.valid==true) {
                        self.data = response;
                        self.buildlist();
                    }
                }
            });
        };
        this.toggle = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var fflink = jQuery(target).closest('a');
            var ffrow = jQuery(fflink).closest('.ffrow');
            var task = '';
            var fpdata = jQuery(fflink).attr('rel');
            if(jQuery(fflink).attr('class').indexOf('active')==-1) {
                task = 'include';
            } else {
                task = 'exclude';
            }
            
            // Find row number
            var rows = jQuery(this.container).find('.ffbody .ffrow');
            var selected = jQuery(rows).index(ffrow);
            this.selectto = selected;
            
            if(this.shiftselect) {
                // Shift select rows
                var fpdata = [];
                jQuery.each(rows, function(index, row) {
                    if((index>=self.selectfrom && index<=self.selectto) || (index>=self.selectto && index<=self.selectfrom)) {
                        var link = jQuery(row).find('.jibtn.toggle');
                        
                        if(self.toggletask=='include') {
                            jQuery(link).children('.jiicon').html('Exclude');
                            jQuery(link).addClass('active');
                            jQuery(row).addClass('active');
                        } else {
                            jQuery(link).children('.jiicon').html('Include');
                            jQuery(link).removeClass('active');
                            jQuery(row).removeClass('active');
                        }
                        fpdata.push(jQuery(link).attr('rel'));
                    }
                });
                if(this.toggletask=='include') {
                    this.include(fpdata);
                } else {
                    this.exclude(fpdata);
                }
            } else {
                if(task=='include') {
                    jQuery(fflink).children('.jiicon').html('Exclude');
                    jQuery(fflink).addClass('active');
                    jQuery(ffrow).addClass('active');
                    this.include(fpdata);
                } else {
                    jQuery(fflink).children('.jiicon').html('Include');
                    jQuery(fflink).removeClass('active');
                    jQuery(ffrow).removeClass('active');
                    this.exclude(fpdata);
                }
            }
            
            
            this.toggletask = task;
            this.selectfrom = selected;
            e.preventDefault();
            e.stopPropagation();
        };
        this.include = function(path) {
            jQuery.ajax({dataType:'json', url:this.includeurl,
                type:'post',
                data:{'ffpath':path}
            }).done(function(response) {
            });
        };
        this.exclude = function(path) {
            jQuery.ajax({dataType:'json', url:this.excludeurl,
                type:'post',
                data:{'ffpath':path}
            }).done(function(response) {
            });
        };
        // Setup Handlers
        this.openHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var link = jQuery(target).closest('a');
            var path = jQuery(link).attr('rel');
            self.open(path);
            e.preventDefault();
            e.stopPropagation();
        };
        this.toggleHandler = function(e) {
            self.toggle(e);
            e.preventDefault();
            e.stopPropagation();
        };
        this.openPathEnterHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if(keycode==13) {
                var input = jQuery(target).closest('.inputbox');
                var path = jQuery(input).val();
                // Remove home path
                //if(path!=null) path = path.substr(5);
                self.open(path);
                e.preventDefault();
                e.stopPropagation();
            }
        };
        this.openPathHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var input = jQuery(self.container).find('.path .inputbox');
            var path = jQuery(input).val();
            // Remove home path
            //if(path!=null) path = path.substr(5);
            self.open(path);
            e.preventDefault();
            e.stopPropagation();
        };
        this.searchEnterHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if(keycode==13) {
                var input = jQuery(target).closest('.inputbox');
                var searchword = jQuery(input).val();
                self.search(searchword);
                e.preventDefault();
                e.stopPropagation();
            }
        };
        this.searchHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var input = jQuery(self.container).find('.search .inputbox');
            var searchword = jQuery(input).val();
            self.search(searchword);
            e.preventDefault();
            e.stopPropagation();
        };
        this.keyDownHandler = function(e) {
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if(keycode==16) {
                self.shiftselect = true;
            }
        };
        this.keyUpHandler = function(e) {
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if(keycode==16) {
                self.shiftselect = false;
            }
        };
        this.buildlist = function() {
            //console.log(this.data);
            if(this.data.items!=null) {
                // Clear old list
                jQuery(this.container).html('');
                // Create inner container
                var ffbox = jQuery(document.createElement('ul')).attr({'class':'ffbox'});
                // Create head
                var ffhead = jQuery(document.createElement('li')).attr({'class':'ffhead'});
                
                // Create title bar
                var ffrow = jQuery(document.createElement('ul')).attr({'class':'ffrow titlebar'});
                // Column 1
                var ffcol = jQuery(document.createElement('li')).attr({'class': 'col1 span8 path'});
                var icontainer = jQuery(document.createElement('div')).attr({'class': 'icontainer'});
                var value = '/'
                for(var i=0; i<this.data.crumbs.length; i++) {
                    var item = this.data.crumbs[i];
                    value+= item.name+'/';
                }
                var input = jQuery(document.createElement('input')).attr({
                    'class': 'inputbox',
                    'name': 'ffpath',
                    'type': 'text'
                }).val(value);
                jQuery(input).on('keypress', this.openPathEnterHandler);
                jQuery(icontainer).append(input);
                
                var link = jQuery(document.createElement('a')).attr({
                    'class': 'jibtn icon16 ui refresh',
                    'href': '#',
                    'title': 'Refresh list'
                });
                var icon = jQuery(document.createElement('span')).attr({'class': 'jiicon refresh'}).html('Refresh');
                jQuery(link).append(icon);
                jQuery(link).on('click', this.openPathHandler);
                jQuery(icontainer).append(link);
                
                jQuery(ffcol).append(icontainer);
                jQuery(ffrow).append(ffcol);
                // Column 2
                ffcol = jQuery(document.createElement('li')).attr({'class': 'col2 span4 search'});
                var icontainer = jQuery(document.createElement('div')).attr({'class': 'icontainer'});
                var value = (this.data.searchword!=null)? this.data.searchword : '';
                var input = jQuery(document.createElement('input')).attr({
                    'class': 'inputbox',
                    'name': 'ffsearchword',
                    'type': 'text'
                }).val(value);
                jQuery(input).on('keypress', this.searchEnterHandler);
                jQuery(icontainer).append(input);
                
                var link = jQuery(document.createElement('a')).attr({
                    'class': 'jibtn icon16 ui search',
                    'href': '#',
                    'title': 'Search'
                });
                var icon = jQuery(document.createElement('span')).attr({'class': 'jiicon search'}).html('Search');
                jQuery(link).append(icon);
                jQuery(link).on('click', this.searchHandler);
                jQuery(icontainer).append(link);
                
                jQuery(ffcol).append(icontainer);
                jQuery(ffrow).append(ffcol);
                jQuery(ffhead).append(ffrow);
                
                // Create column titles
                var ffrow = jQuery(document.createElement('ul')).attr({'class':'ffrow columnnames'});
                // Column 1
                var ffcol = jQuery(document.createElement('li')).attr({'class': 'col1 span1 mimeicon'});
                jQuery(ffrow).append(ffcol);
                // Column 2
                ffcol = jQuery(document.createElement('li')).attr({'class': 'col2 span9 name'}).html('Name');
                jQuery(ffrow).append(ffcol);
                // Column 3
                ffcol = jQuery(document.createElement('li')).attr({'class': 'col3 span1 size'}).html('Size');
                jQuery(ffrow).append(ffcol);
                // Column 4
                ffcol = jQuery(document.createElement('li')).attr({'class': 'col4 span1 actions'}).html('Include');
                jQuery(ffrow).append(ffcol);
                
                jQuery(ffhead).append(ffrow);
                jQuery(ffbox).append(ffhead);
                
                var ffbody = jQuery(document.createElement('li')).attr({'class':'ffbody'});
                var k = 0;
                for(var i=0; i<this.data.items.length; i++) {
                    var item = this.data.items[i];
                    
                    ffrow = jQuery(document.createElement('ul')).attr({'class':'ffrow k'+k});
                    // Column 1
                    var ffcol = jQuery(document.createElement('li')).attr({'class': 'col1 span1 mimeicon'});
                    var link = jQuery(document.createElement('a')).attr({
                        'class': 'jibtn icon16 mime',
                        'href': '#',
                        'rel': item.path,
                        'title': 'Open directory'
                    });
                    var icon = jQuery(document.createElement('span')).attr({'class': 'jiicon '+item.type});
                    jQuery(link).append(icon);
                    jQuery(link).on('click', this.openHandler);
                    jQuery(ffcol).append(link);
                    jQuery(ffrow).append(ffcol);
                    // Column 2
                    ffcol = jQuery(document.createElement('li')).attr({'class': 'col2 span9 name'});
                    link = jQuery(document.createElement('a')).attr({
                        'class': 'openlink',
                        'href': '#',
                        'rel': item.path,
                        'title': 'Open directory'
                    });
                    var span = jQuery(document.createElement('span')).attr({
                        'class':'name'
                    }).html(item.name);
                    jQuery(link).append(span);
                    if(item.childoverrides!=null) {
                        if(item.childoverrides.included>0 || item.childoverrides.excluded>0) {
                            var span = jQuery(document.createElement('span')).attr({
                                'class':'childoverrides'
                            }).html('[Included: '+item.childoverrides.included+', Excluded: '+item.childoverrides.excluded+']');
                            jQuery(link).append(span);
                        }
                    }
                    
                    jQuery(link).on('click', this.openHandler);
                    jQuery(ffcol).append(link);
                    jQuery(ffrow).append(ffcol);
                    // Column 3
                    ffcol = jQuery(document.createElement('li')).attr({'class': 'col3 span1 size'});
                    if(item.size!=null) {
                        var span = jQuery(document.createElement('span')).attr({
                            'href': '#'
                        }).html(item.size);
                        jQuery(ffcol).append(span);
                    }
                    jQuery(ffrow).append(ffcol);
                    // Column 4
                    ffcol = jQuery(document.createElement('li')).attr({'class': 'col4 span1 actions'});
                    if(item.root==null) {
                        link = jQuery(document.createElement('a')).attr({
                            'class': 'jibtn icon16 ui toggle',
                            'href': '#',
                            'rel': item.path
                        });
                        icon = jQuery(document.createElement('span')).attr({'class': 'jiicon toggle'});
                        jQuery(link).append(icon);
                        if(item.state=='include') {
                            jQuery(link).attr('title', 'Exclude item and sub-items');
                            jQuery(link).addClass('active');
                            jQuery(ffrow).addClass('active');
                            jQuery(icon).html('Exclude');
                        } else {
                            jQuery(link).attr('title', 'Include item and sub-items');
                            jQuery(icon).html('Include');
                        }
                        jQuery(link).on('click', this.toggleHandler);
                        jQuery(ffcol).append(link);
                    }
                    jQuery(ffrow).append(ffcol);
                    
                    jQuery(ffbody).append(ffrow);
                    k++;
                    if(k>1) k = 0;
                }
                jQuery(ffbox).append(ffbody);
                jQuery(this.container).append(ffbox);
            }
        };
        // Init
        this.buildlist();
        
        jQuery(document).on('keydown', this.keyDownHandler);
        jQuery(document).on('keyup', this.keyUpHandler);
    };
    $.fn.jilistfilter = function(options) {
        return this.each(function() {
            var element = jQuery(this);
            if(element.data('jilistfilter')) return;
            var jilistfilter = new JiListFilter(this, options);
            element.data('jilistfilter', jilistfilter);
        });
    };
})(jQuery);