/**
 * @version     $Id: jquery.jimediabrowser.js 082 2015-01-06 08:55:00Z Anton Wintergerst $
 * @package     JiMediaBrowser for jQuery
 * @copyright   Copyright (C) 2015 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiMediaBrowser = function(container, options)
    {
        var self = this;
        // Set Default Options
        this.container = container;
        this.id = 'folder';
        this.data = null;
        this.baseurl = '';
        this.url = '#';
        this.style = 'large grid';
        this.buffertime = 200;

        // Setup Options
        jQuery.each(options, function(index, value) {
            if(index=='data') {
                self.data = jQuery.parseJSON(value);
            } else {
                self[index] = value;
            }
        });
        this.setPrivateOptions = function() {
            var addq = (this.url.indexOf('?')==-1);
            if(this.openurl==null) this.openurl = (addq)? this.url+'?&mbtask=open' : this.url+'&mbtask=open';
            if(this.loadiconurl==null) this.loadiconurl = (addq)? this.url+'?&mbtask=loadicon' : this.url+'&mbtask=loadicon';
            if(this.loadpreviewurl==null) this.loadpreviewurl = (addq)? this.url+'?&mbtask=loadpreview' : this.url+'&mbtask=loadpreview';
            this.styles = ['list', 'small grid', 'medium grid', 'large grid'];
            this.isbuilding = false;
            this.loadcount = 0;
            this.requestqueue = [];
        };
        this.setPrivateOptions();

        // Actions
        this.request = function(url, data, dataType, callback) {
            var request = {
                'url':url,
                'data':data,
                'dataType':dataType,
                'callback':callback
            };
            this.requestqueue.push(request);

            if(this.requesttimer==null) {
                this.requesttimer = setTimeout(function() {
                    self.dorequest();
                }, this.buffertime);
            }
        };
        this.dorequest = function() {
            if(this.requestqueue.length>0) {
                var request = this.requestqueue[0];
                var url = request.url;
                var data = request.data;
                var dataType = request.dataType;
                var callback = request.callback;
                if(dataType==null) dataType = 'default';
                jQuery.ajax({
                    url:url, type:'post', dataType:dataType, data:data
                }).done(function(response) {
                        if(callback!=null) callback.call(undefined, response);
                    }).always(function() {
                        self.requestqueue.shift();
                        if(self.requestqueue.length>0) {
                            self.requesttimer = setTimeout(function() {
                                self.dorequest();
                            }, self.buffertime);
                        } else {
                            if(self.requesttimer!=null) clearTimeout(self.requesttimer);
                            self.requesttimer = null;
                        }
                    });
            } else {
                if(this.requesttimer!=null) clearTimeout(this.requesttimer);
                this.requesttimer = null;
            }
        };
        this.open = function(path, start, e, pushstate) {
            this.isinitializing = false;
            if(start==null) start = 0;
            if(pushstate==null) pushstate = true;
            var data = {
                'mbpath':path,
                'mbid':self.id,
                'mbstart':start
            };
            //data[self.id] = self.data.folder;

            jQuery.ajax({dataType:'json', url:this.openurl+'&'+self.id+'='+self.data.folder,
                type:'post',
                data:data,
                cache:false,
                async:false
            }).done(function(response) {
                if(response!=null) {
                    if(response.valid==true) {
                        if(response.isdir) {
                            if(start==0) {
                                // Open directory
                                self.data = response;
                                if(pushstate) {
                                    var title = self.id+': '+self.data.folder;
                                    window.history.pushState({'path':path, 'title':title}, title, self.data.url);
                                }
                                self.buildlist();
                            } else if(response.items!=null) {
                                // Append new items
                                jQuery(e).closest('.mbrow').remove();
                                if(self.data.items!=null) {
                                    for(var i=0; i<self.data.items.length; i++) {
                                        var item = self.data.items[i];
                                        if(item.type=='more') self.data.items.pop(i);
                                    }
                                } else {
                                    self.data.items = [];
                                }
                                for(var i=0; i<response.items.length; i++) {
                                    var item = response.items[i];
                                    if(item.root==null) self.data.items.push(item);
                                }
                                self.buildlist();
                            }
                        } else {
                            // Fire Open File Event
                            jQuery(self.container).trigger({type:'fileselected', file:response.file});
                        }
                    }
                }
            });
        };
        this.search = function(searchword, start, e) {
            if(start==null) start = 0;
            var data = {
                'mbsearchword':searchword,
                'mbid':self.id,
                'mbstart':start
            };
            data[self.id] = self.data.folder;
            jQuery.ajax({dataType:'json', url:this.openurl,
                type:'post',
                data:data
            }).done(function(response) {
                if(response!=null) {
                    if(response.valid==true) {
                        if(start==0) {
                            self.data = response;
                            self.buildlist();
                        } else {
                            // Append new items
                            jQuery(e).closest('.mbrow').remove();
                            if(self.data.items!=null) {
                                for(var i=0; i<self.data.items.length; i++) {
                                    var item = self.data.items[i];
                                    if(item.type=='more') self.data.items.pop(i);
                                }
                            } else {
                                self.data.items = [];
                            }
                            for(var i=0; i<response.items.length; i++) {
                                var item = response.items[i];
                                if(item.root==null) self.data.items.push(item);
                            }
                            self.buildlist();
                        }
                    }
                }
            });
        };
        this.toggleStyle = function() {
            var totalstyles = this.styles.length;
            for(var i=0; i<totalstyles; i++) {
                var style = this.styles[i];
                var laststyle = (i>0)? this.styles[i-1] : this.styles[totalstyles-1];
                if(laststyle==this.style) {
                    this.style = style;
                    this.buildlist();
                    break;
                }
            }
        };
        /*this.setStyleClass = function() {
            var stylechanger = jQuery(this.container).find('.liststyleicon');
            var styles = '';
            for(var i=0; i<this.styles.length; i++) {
                styles+= this.style[i];
            }
            jQuery(stylechanger).removeClass(styles).addClass(this.style);
        };*/
        // Setup Handlers
        this.openHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var link = jQuery(target).closest('a');
            var path = jQuery(link).attr('rel');
            self.open(path);
            e.preventDefault();
            e.stopPropagation();
        };
        this.openMoreHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var link = jQuery(target).closest('a');
            var path = jQuery(link).attr('rel');
            self.open(path, self.start, target);
            e.preventDefault();
            e.stopPropagation();
        };
        this.openPathEnterHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if(keycode==13) {
                var input = jQuery(target).closest('.inputbox');
                var path = jQuery(input).val();
                // Trim leading and trailing slashes
                if(path.substr(0,1)=='/') path = path.substr(1);
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
        this.searchMoreHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var link = jQuery(target).closest('a');
            var input = jQuery(self.container).find('.search .inputbox');
            var searchword = jQuery(input).val();
            self.search(searchword, self.start, target);
            e.preventDefault();
            e.stopPropagation();
        };
        this.toggleStyleHandler = function(e) {
            self.toggleStyle();
            e.preventDefault();
            e.stopPropagation();
        };
        /**
         * Returns existing element and creates new elements as required
         */
        this.render = function(tag, classname, parent)
        {
            var classnames = classname.split(' ').join('.');
            if(parent==null) parent = this.container;

            // find existing
            var result = jQuery(parent).find('.'+classnames);
            if(jQuery(result).length>0) return result;

            // create a new element
            result = jQuery(document.createElement(tag)).attr('class',classname);
            jQuery(parent).append(result);
            return result;
        };
        this.buildlist = function() {
            if(this.data.items!=null && !this.isbuilding) {
                this.isbuilding = true;
                // Clear old list
                //jQuery(this.container).html('');
                // inner container
                var mbbox = this.render('ul', 'mbbox');
                // head
                var mbhead = this.render('li', 'mbhead', mbbox);

                this.createTitleBar(mbhead);

                if(self.style=='list') {
                    // create column titles
                    var mbrow = this.render('ul', 'mbrow columnnames', mbhead);
                    // column 1
                    this.render('li', 'col1 span1 mimeicon', mbrow);
                    // column 2
                    this.render('li', 'col2 span8 name', mbrow).html('Name');
                    // column 3
                    this.render('li', 'col3 span2 size', mbrow).html('Size');
                    // column 4
                    this.render('li', 'col4 span1 action', mbrow).html('Action');
                }

                var mbbody = null;
                if(self.style=='list') {
                    mbbody = this.createListBody();
                } else {
                    mbbody = this.createGridBody(self.style);
                }
                jQuery(mbbox).append(mbbody);

                jQuery(this.container).append(mbbox);

                if(!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                    jQuery('*[href][rel^=lightbox]').on('click', function(e){
                        var t = this, rel = t.getAttribute('rel'), hrefs = [], links = [], index;
                        if(rel === 'lightbox'){
                            jQuery.slimbox(t.href, t.getAttribute('rev') || '', {});
                        } else {
                            jQuery('*[href][rel="' + rel + '"]').each(function(){
                                if(jQuery.inArray(this.href, hrefs) < 0){
                                    if(t.href === this.href){index = hrefs.length;}
                                    hrefs.push(this.href);
                                    links.push([this.href, this.getAttribute('rev') || '']);
                                }
                            });
                            jQuery.slimbox(links, index, {loop: true});
                        }
                        e.preventDefault();
                        e.stopPropagation();
                    });
                }
                /*var inslimbox = [];
                jQuery("a[rel^='lightbox']").slimbox({}, null, function(el) {
                    if(inslimbox.indexOf(el.href)!=-1) {
                        return true;
                    } else {
                        inslimbox.push(el.href);
                    }
                    return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
                });*/

                this.equalizeHeights('.mbbody .mbrow', true);
                this.isbuilding = false;
            }
        };
        this.createTitleBar = function(mbhead) {
            // Create title bar
            var mbrow = jQuery(document.createElement('ul')).attr({'class':'mbrow titlebar'});
            // Column 1
            var mbcol = jQuery(document.createElement('li')).attr({'class': 'col1 span8 path'});
            var icontainer = jQuery(document.createElement('div')).attr({'class': 'icontainer'});
            var value = '/'
            for(var i=0; i<this.data.crumbs.length; i++) {
                var item = this.data.crumbs[i];
                value+= item.name+'/';
            }
            var input = jQuery(document.createElement('input')).attr({
                'class': 'inputbox',
                'name': 'mbpath',
                'type': 'text'
            }).val(value);
            jQuery(input).on('keypress', this.openPathEnterHandler);
            jQuery(icontainer).append(input);

            var link = jQuery(document.createElement('a')).attr({
                'class': 'jibtn icon16 ui refresh',
                'href': '#',
                'title': 'Refresh list'
            });
            var icon = jQuery(document.createElement('i')).attr({'class': 'jiicon icon-repeat'});
            jQuery(link).append(icon);
            jQuery(link).on('click', this.openPathHandler);
            jQuery(icontainer).append(link);

            jQuery(mbcol).append(icontainer);
            jQuery(mbrow).append(mbcol);

            // Column 2
            mbcol = jQuery(document.createElement('li')).attr({'class': 'col2 span3 search'});
            var icontainer = jQuery(document.createElement('div')).attr({'class': 'icontainer'});
            var value = (this.data.searchword!=null)? this.data.searchword : '';
            var input = jQuery(document.createElement('input')).attr({
                'class': 'inputbox',
                'name': 'mbsearchword',
                'type': 'text'
            }).val(value);
            jQuery(input).on('keypress', this.searchEnterHandler);
            jQuery(icontainer).append(input);

            var link = jQuery(document.createElement('a')).attr({
                'class': 'jibtn icon16 ui search',
                'href': '#',
                'title': 'Search'
            });
            var icon = jQuery(document.createElement('i')).attr({'class': 'jiicon icon-search'});
            jQuery(link).append(icon);
            jQuery(link).on('click', this.searchHandler);
            jQuery(icontainer).append(link);
            jQuery(mbcol).append(icontainer);
            jQuery(mbrow).append(mbcol);

            // Column 3
            mbcol = jQuery(document.createElement('li')).attr({'class': 'col3 span1 liststyle'});

            var link = jQuery(document.createElement('a')).attr({
                'class': 'jibtn icon16 ui liststyle',
                'href': '#',
                'title': 'Toggle list style'
            });
            var iconclass = 'icon-th';
            if(this.style=='small grid') {
                iconclass = 'icon-th-list';
            } else if(this.style=='medium grid') {
                iconclass = 'icon-th';
            } else if(this.style=='large grid') {
                iconclass = 'icon-th-large';
            } else if(this.style=='list') {
                iconclass = 'icon-list';
            }
            var icon = jQuery(document.createElement('i')).attr({'class': 'jiicon liststyleicon '+iconclass});
            jQuery(link).append(icon);
            jQuery(link).on('click', this.toggleStyleHandler);
            jQuery(mbcol).append(link);
            jQuery(mbrow).append(mbcol);

            jQuery(mbhead).append(mbrow);
        };
        this.equalizeHeights = function(target, imgonload) {
            var es = jQuery(this.container).find(target).css('min-height', 0);
            var tallest = 0;
            jQuery.each(es, function(index, e) {
                var height = jQuery(e).height() - (jQuery(e).outerHeight() - jQuery(e).innerHeight());
                if(height>tallest) tallest = height;
            });
            jQuery(es).css('min-height', Math.ceil(tallest)+'px');
            if(imgonload==true) {
                jQuery(target+' img').on('load', function() {
                    if(self.equalizeHeightsTimer!=null) clearTimeout(self.equalizeHeightsTimer);
                    self.equalizeHeightsTimer = setTimeout(function() {
                        self.equalizeHeights(target, false);
                    }, 250);
                });
            }
        };
        this.createGridBody = function(style) {
            if(style=='small grid') {
                cols = 4;
            } else if(style=='medium grid') {
                cols = 6;
            } else if(style=='large grid') {
                cols = 4;
            } else {
                cols = 1;
            }
            this.cols = cols;
            var mbbody = jQuery(document.createElement('li')).attr({'class':'mbbody '+style+' cols'+cols});
            var k = 0;
            var c = 0;
            var r = 0;
            var cellcount = 0;
            for(var i=0; i<this.data.items.length; i++) {
                var item = this.data.items[i];
                var classtext = '';
                if(c==0) classtext = ' first';
                if(c==cols-1) classtext = ' last';
                var mbrowouter = jQuery(document.createElement('div')).attr({'class':'mbrowouter k'+k+classtext});
                var mbrow = jQuery(document.createElement('ul')).attr({'class':'mbrow k'+k});

                self.createCell(item, mbrow);

                jQuery(mbrowouter).append(mbrow);
                jQuery(mbbody).append(mbrowouter);
                k++;
                if(k>1) k = 0;
                c++;
                if(c>=cols) {
                    c = 0;
                    r++;
                    k = r%2;
                }
            }
            return mbbody;
        };
        this.createListBody = function() {
            var mbbody = jQuery(document.createElement('li')).attr({'class':'mbbody list'});
            var k = 0;
            for(var i=0; i<this.data.items.length; i++) {
                var item = this.data.items[i];

                var mbrow = jQuery(document.createElement('ul')).attr({'class':'mbrow k'+k});

                self.createCell(item, mbrow);

                jQuery(mbbody).append(mbrow);
                k++;
                if(k>1) k = 0;
            }
            return mbbody;
        };
        this.createLink = function(item, classtext) {
            var link = null;
            if(item.preview!=null || item.loadpreview!=null) {
                var previewid = null;
                if(item.loadpreview!=null) {
                    previewid = 'jiloadimg'+this.loadcount;
                    var data = {
                        'mbid':self.id,
                        'mbpath':item.path,
                        'e':'#'+previewid
                    };
                    data[self.id] = self.data.folder;
                    this.request(self.loadpreviewurl, data, 'json', function(response) {
                        if(response!=null) {
                            if(response.valid==true) {
                                var linktarget = jQuery(self.container).find(response.e);
                                jQuery(linktarget).attr({
                                    'href':self.baseurl+response.img,
                                    'rel':'lightbox-'+self.id,
                                    'target':'_blank' // open in new window
                                });
                                //jQuery(linktarget).unbind('click');
                            }
                        }
                    });
                    this.loadcount++;
                    link = jQuery(document.createElement('a')).attr({
                        'id':previewid,
                        'class': classtext,
                        'href': '#',
                        'title': 'Preview file',
                        'target':'_blank' // open in new window
                    }).on('click', function(e) {e.preventDefault();});
                } else {
                    link = jQuery(document.createElement('a')).attr({
                        'id':previewid,
                        'class': classtext,
                        'href': this.baseurl+item.preview,
                        'rel': 'lightbox-'+this.id,
                        'title': 'Preview file',
                        'target':'_blank' // open in new window
                    });
                }
            } else {
                link = jQuery(document.createElement('a')).attr({
                    'class': classtext,
                    'href': '#',
                    'rel': item.path,
                    'title': 'Open directory',
                    'target':'_blank' // open in new window
                });
                if(item.start!=null) {
                    this.start = parseInt(item.start);
                    if(item.searchmore!=null) {
                        jQuery(link).on('click', this.searchMoreHandler);
                    } else {
                        jQuery(link).on('click', this.openMoreHandler);
                    }
                } else {
                    jQuery(link).on('click', this.openHandler);
                }
            }
            return link;
        }
        this.createCell = function(item, mbrow) {
            // Column 1
            var mbcol = jQuery(document.createElement('li')).attr({'class': 'col1 span1 thumb'});
            var thumbclass = (item.children!=null)? 'thumblink haschildren' : 'thumblink';
            var link = this.createLink(item, thumbclass);

            var iconid = '';
            if(item.loadicon!=null) {
                iconid = 'jiloadimg'+this.loadcount;
                var data = {
                    'mbid':self.id,
                    'mbpath':item.path,
                    'e':'#'+iconid
                };
                data[self.id] = self.data.folder;
                this.request(self.loadiconurl, data, 'json', function(response) {
                    if(response!=null) {
                        if(response.valid==true) {
                            var img = jQuery(self.container).find(response.e);
                            jQuery(img).attr('src', self.baseurl+response.img);
                        }
                    }
                    var img = jQuery(self.container).find('#'+iconid);
                    jQuery(img).closest('.thumblink').find('.loadingicon').remove();
                });
                this.loadcount++;
            }
            if(item.icon!=null) {
                var icon = jQuery(document.createElement('span')).attr({'class': 'thumbicon '+item.type});
                var iconimg = jQuery(document.createElement('img')).attr({'src': self.baseurl+item.icon, 'id':iconid});
                jQuery(icon).append(iconimg);
                jQuery(link).append(icon);
                if(item.loadicon!=null) {
                    //var loadingicon = jQuery(document.createElement('i')).attr({'class':'jiicon loadingicon icon-spinner icon-spin icon-large'});
                    jQuery(link).append('<div class="loadingicon"><div class="f_circleG frotateG_01"></div><div class="f_circleG frotateG_02"></div><div class="f_circleG frotateG_03"></div><div class="f_circleG frotateG_04"></div><div class="f_circleG frotateG_05"></div><div class="f_circleG frotateG_06"></div><div class="f_circleG frotateG_07"></div><div class="f_circleG frotateG_08"></div></div>');
                }
            }

            if(item.children!=null) {
                var ccontainer = jQuery(document.createElement('span')).attr({'class': 'childcontainer'});
                for(var c=0; c<item.children.length; c++) {
                    var child = item.children[c];
                    var childicon = jQuery(document.createElement('span')).attr({'class': child.type+' childicon child'+c});
                    var childiconimg = jQuery(document.createElement('img')).attr({'src': self.baseurl+child.icon});
                    jQuery(childicon).append(childiconimg);
                    jQuery(ccontainer).append(childicon);
                }
                jQuery(link).append(ccontainer);
            }


            jQuery(mbcol).append(link);
            jQuery(mbrow).append(mbcol);

            // Column 2
            mbcol = jQuery(document.createElement('li')).attr({'class': 'col2 span8 name'});
            link = this.createLink(item, 'namelink');
            var span = jQuery(document.createElement('span')).attr({
                'class':'name'
            }).html(item.name.replace('_', ' '));
            jQuery(link).append(span);

            jQuery(mbcol).append(link);
            jQuery(mbrow).append(mbcol);

            // Column 3
            mbcol = jQuery(document.createElement('li')).attr({'class': 'col3 span2 size'});
            if(item.size!=null) {
                var span = jQuery(document.createElement('span')).attr({
                    'href': '#'
                }).html(item.size);
                jQuery(mbcol).append(span);
            }
            jQuery(mbrow).append(mbcol);
            // Column 4
            mbcol = jQuery(document.createElement('li')).attr({'class': 'col3 span1 action'});
            if(item.dllink!=null) {
                link = jQuery(document.createElement('a')).attr({
                    'class': 'dllink',
                    'href': self.url+item.dllink+'&mbtask=download',
                    'title': 'Download'
                });
                var icon = jQuery(document.createElement('span')).attr({'class': 'dltext'}).html('Download');
                jQuery(link).append(icon);
                var icon = jQuery(document.createElement('i')).attr({'class': 'jiicon icon-download'});
                jQuery(link).append(icon);
                jQuery(mbcol).append(link);
            }
            jQuery(mbrow).append(mbcol);
        };
        this.update = function() {
            this.equalizeHeights('.mbbody .mbrow', true);
        };
        this.resizeWindowHandler = function(e) {self.update;};
        this.windowPopstate = function(jqe) {
            var e = jqe.originalEvent;
            if(e.state){
                document.title = e.state.title;
                if(e.state.path!=self.data.path) {
                    self.open(e.state.path, 0, null, false);
                }
            } else if(self.initialstate!=null && !self.isinitializing) {
                document.title = self.initialstate.title;
                self.open(self.initialstate.path, 0, null, false);
                self.initialstate = null;
            }
            self.isinitializing = false;
            jqe.preventDefault();
            jqe.stopPropagation();
        };
        this.init = function() {
            self.isinitializing = true;
            jQuery(window).on('resize', this.resizeWindowHandler);
            jQuery(window).on('popstate', this.windowPopstate);
            var title = self.id+': '+self.data.folder;
            this.initialstate = {'path':self.data.path, 'title':title};
            //window.history.pushState({'path':self.data.path, 'title':title}, title, self.data.url);
            this.buildlist();
        };
        // Init
        this.init();
    };
    jQuery.fn.jimediabrowser = function(options) {
        var element = jQuery(this);
        // Create new class
        var jimediabrowser = new JiMediaBrowser(this, options);
        // Set and return class data
        element.data('jimediabrowser', jimediabrowser);
        return jimediabrowser;
    };
})(jQuery);