/**
 * @version     $Id: jquery.jimediamanager.js 086 2014-11-20 13:27:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiMediaManager = function(container, options)
    {
        var self = this;
        this.strings = {
            'upload':Joomla.JText._('COM_JICUSTOMFIELDS_UPLOAD', 'Upload'),
            'cancel':Joomla.JText._('COM_JICUSTOMFIELDS_CANCEL', 'Cancel'),
            'noimagetip':Joomla.JText._('COM_JICUSTOMFIELDS_IMAGENOIMAGETIP', '<p>No image selected.</p>')
        };
        // Set Default Options
        this.id = null;
        this.name = 'Media Manager';
        this.type = 'media';
        this.label = '';
        this.fileinput = null;
        this.url = null;
        this.rootpath = '';
        this.mediatype = 'files';
        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        // Actions
        this.openManager = function(e) {
            if(this.manager==null) {
                if(jQuery('#jimediamanager').length>0) {
                    // Load existing Media Manager
                    this.managerinner = jQuery('#jimediamanager').find('.jimminner');
                } else {
                    // Initialize Media Manager
                    var manageroverlay = jQuery(document.createElement('div')).attr({'id':'jimmoverlay', 'tabindex':'-1'});
                    jQuery(document.body).append(manageroverlay);
                    this.manager = jQuery(document.createElement('div')).attr({id:'jimediamanager', class:'jimediamanager'});
                    var managerouter = jQuery(document.createElement('div')).attr({'class':'jimmouter'});
                    jQuery(this.manager).append(managerouter);
                    this.managerinner = jQuery(document.createElement('div')).attr({'class':'jimminner'});
                    jQuery(this.managerinner).on('click', function(e) {
                        if(!jQuery(e.target).closest('.jimminner').length) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    });
                    jQuery(managerouter).append(this.managerinner);
                    var closebtn = jQuery(document.createElement('a')).attr({'class':'jimmclosebtn', 'href':'#', 'rel':'close'}).html(self.strings.cancel);
                    jQuery(closebtn).on('click', self.hideManagerHandler);
                    jQuery(managerouter).append(closebtn);
                    jQuery(document.body).append(this.manager);
                    jQuery('html').on('click', self.hideManagerHandler);
                }
            }
            this.getManagerHTML();
            this.showManager();
            e.preventDefault();
            e.stopPropagation();
        };
        this.showManager = function() {
            this.updateManagerOverlay();
            jQuery('#jimmoverlay').css({
                'opacity': '0',
                'display': 'block'
            });
            jQuery('#jimmoverlay').animate({opacity: '0.7'}, 250, function() {
                jQuery('#jimediamanager').css('display', 'block');
            });
        };
        this.hideManager = function(e) {
            if(self.manager!=null) {
                if(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.updateManagerOverlay();
                jQuery('#jimmoverlay').animate({opacity: '0'}, 250, function() {
                    jQuery('#jimmoverlay').css('display', 'none');
                    jQuery('#jimediamanager').css('display', 'none');
                    jQuery('#jimmoverlay').remove();
                    jQuery('#jimediamanager').remove();
                    self.manager = null;
                });
            }
        };
        this.updateManagerOverlay = function() {
            jQuery('#jimmoverlay').css({
                'width':jQuery(document).width()+'px',
                'height':jQuery(document).height()+'px'
            });
        };
        this.getManagerHTML = function() {
            // show loading indicator
            jQuery(this.managerinner).append('<div class="loadingicon"><div class="f_circleG frotateG_01"></div><div class="f_circleG frotateG_02"></div><div class="f_circleG frotateG_03"></div><div class="f_circleG frotateG_04"></div><div class="f_circleG frotateG_05"></div><div class="f_circleG frotateG_06"></div><div class="f_circleG frotateG_07"></div><div class="f_circleG frotateG_08"></div></div>');
            self.centerVertically();

            var type = 'media';
            var name = self.title;
            jQuery.ajax({url:self.url,
                type:'post',
                data:{
                    'type':type,
                    'name':self.name,
                    'label':self.label,
                    'id':self.id,
                    'mediatype':self.mediatype
                }
            }).done(function(response) {
                if(response!=null) {
                    jQuery(self.managerinner).children().remove();
                    jQuery(self.managerinner).append(response);

                    self.centerVertically();

                    // Prepare field type
                    var JiField = jQuery('.customfields').jifield({
                        'id':self.id,
                        'name':self.name,
                        'label':self.label,
                        'type':type,
                        'mediatype':self.mediatype
                    }, type);
                    if(JiField!=null) JiField.prepareInput();
                }
            });
        };
        this.selectedFile = function(e) {
            if(e.file!=null) {
                // Update value
                jQuery(this.fileinput).val(e.file);
                this.updatePreview();
                this.hideManager();
            }
        };
        this.clearField = function(e) {
            jQuery(this.fileinput).val('');
            this.updatePreview();
            e.preventDefault();
            e.stopPropagation();
        };
        this.updatePreview = function() {
            var value = jQuery(this.fileinput).val();
            var tip = '';
            if(value!=null && value!='') {
                tip = '<img src="'+self.rootpath+jQuery(this.fileinput).val()+'" />'
            } else {
                tip = self.strings.noimagetip;
            }
            // Update preview
            var preview = jQuery(this.fileinput).parent().find('.media-preview');
            if(typeof(jQuery.tooltip)==="function") jQuery(preview).attr('title', tip).tooltip('fixTitle');
        };
        this.centerVertically = function()
        {
            // center modal vertically
            var top = ((jQuery(window).height() - jQuery(this.managerinner).outerHeight())/2) + jQuery(window).scrollTop();
            if(top<0) top = 0;
            jQuery('#jimediamanager').css('top', top+'px');
        }
        this.updateLayout = function()
        {
            this.centerVertically();
        }
        // Setup Handlers
        this.openManagerHandler = function(e) {self.openManager(e);};
        this.selectedFileHandler = function(e) {self.selectedFile(e);};
        this.clearFieldHandler = function(e) {self.clearField(e);};
        this.hideManagerHandler = function(e) {
            // only close if clicking outside of modal window
            if(!jQuery(e.target).closest('.jimminner').length) {
                self.hideManager(e);
            }
        };
        this.resizeWindowHandler = function(e) {self.updateLayout;};

        // Init
        this.init = function() {
            jQuery(window).on('resize', this.resizeWindowHandler);
            this.fileinput = jQuery(this.fileinput);
            this.updatePreview();
            jQuery(this.fileinput).on('fileselected', self.selectedFileHandler);
            var btns = jQuery(this.fileinput).parent().find('a');
            jQuery.each(btns, function(index, btn) {
                if(typeof jQuery(btn).attr('rel')!= 'undefined') {
                    if(jQuery(btn).attr('rel').indexOf("picker")!=-1) {
                        jQuery(btn).on('click', self.openManagerHandler);
                    } else if(jQuery(btn).attr('rel').indexOf("clear")!=-1) {
                        jQuery(btn).on('click', self.clearFieldHandler);
                    }
                }
            });
        };
        this.init();
    };
    jQuery.fn.jimediamanager = function(options) {
        var element = jQuery(this);
        // create new class
        var jimediamanager = new JiMediaManager(this, options);
        // set and return class data
        element.data('jimediamanager', jimediamanager);
        return jimediamanager;
    };
})(jQuery);
/*
 * @version     $Id: jquery.jilistfilter.js 103 2014-10-28 11:16:00Z Anton Wintergerst $
 * @package     ListFilter for jQuery
 * @copyright   Copyright (C) 2014 3L Coding. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.3lcoding.com
 * @email       support@3lcoding.com
 */
(function(jQuery){
    var JiListFilter = function(container, options)
    {
        var self = this;
        // Set Default Options
        this.container = null;
        this.data = null;
        this.baseurl = '';
        this.url = 'index.php?option=com_jicustomfields&format=json';
        this.style = 'large grid';
        this.buffertime = 200;
        this.uploadurl = 'index.php?option=com_jicustomfields&format=ajax&view=upload';
        this.resizeurl = 'index.php?option=com_jicustomfields&format=ajax&view=upload&task=resize';

        // Setup Options
        jQuery.each(options, function(index, value) {
            if(index=='data') {
                self.data = jQuery.parseJSON(value);
            } else {
                self[index] = value;
            }
        });
        this.setPrivateOptions = function() {
            var q = (this.url.indexOf('?')==-1)? '?':'';
            if(this.loadiconurl==null) this.loadiconurl = this.url+q+'&task=mediamanager.loadicon';
            if(this.openurl==null) this.openurl = this.url+q+'&task=mediamanager.open';
            this.styles = ['list', 'small grid', 'medium grid', 'large grid'];
            this.isbuilding = false;
            this.loadcount = 0;
            this.requestqueue = [];
        }
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
        this.open = function(path) {
            jQuery.ajax({dataType:'json', url:this.openurl,
                type:'post',
                data:{
                    id:self.id,
                    name:self.name,
                    type:self.type,
                    ffpath:path,
                    mediatype:self.data.mediatype
                }
            }).done(function(response) {
                    if(response!=null) {
                        if(response.valid==true) {
                            if(response.isdir) {
                                // Open Directory
                                self.data = response;
                                self.buildlist();
                            } else {
                                // Fire Open File Event
                                jQuery(self.fileinput).trigger({type:'fileselected', file:response.file});
                            }
                        }
                    }
                });
        };
        this.search = function(searchword) {
            jQuery.ajax({dataType:'json', url:this.openurl,
                type:'post',
                data:{
                    id:self.id,
                    name:self.name,
                    type:self.type,
                    ffsearchword:searchword,
                    mediatype:self.data.mediatype
                }
            }).done(function(response) {
                    if(response!=null) {
                        if(response.valid==true) {
                            self.data = response;
                            self.buildlist();
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
        }
        // Setup Handlers
        this.openHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var link = jQuery(target).closest('a');
            var path = jQuery(link).attr('rel');
            self.open(path);
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
        this.toggleStyleHandler = function(e) {
            self.toggleStyle();
            e.preventDefault();
            e.stopPropagation();
        };
        this.buildlist = function() {
            if(this.data.items!=null && !this.isbuilding) {
                this.isbuilding = true;
                // Clear old list
                jQuery(this.container).html('');
                // inner container
                var ffbox = jQuery(document.createElement('ul')).attr({'class':'ffbox'});

                // header
                var ffhead = jQuery(document.createElement('li')).attr({'class':'ffhead'});
                this.createTitleBar(ffhead);

                // list headers
                if(self.style=='list') {
                    var ffrow = jQuery(document.createElement('ul')).attr({'class':'ffrow columnnames'});
                    // column 1
                    var ffcol = jQuery(document.createElement('li')).attr({'class': 'col1 span1 mimeicon'});
                    jQuery(ffrow).append(ffcol);
                    // column 2
                    ffcol = jQuery(document.createElement('li')).attr({'class': 'col2 span8 name'}).html('Name');
                    jQuery(ffrow).append(ffcol);
                    // column 3
                    ffcol = jQuery(document.createElement('li')).attr({'class': 'col3 span3 size'}).html('Size');
                    jQuery(ffrow).append(ffcol);

                    jQuery(ffhead).append(ffrow);
                }
                this.createUploadBar(ffhead);
                jQuery(ffbox).append(ffhead);

                // body
                var ffbody = null;
                if(self.style=='list') {
                    ffbody = this.createListBody();
                } else {
                    ffbody = this.createGridBody(self.style);
                }
                jQuery(ffbox).append(ffbody);

                // footer
                /*var fffooter = jQuery(document.createElement('li')).attr({'class':'fffooter'});
                this.createUploadBar(fffooter);
                jQuery(ffbox).append(fffooter);*/

                jQuery(this.container).append(ffbox);

                // init uploader
                var jiuploader = jQuery('.jiuploader').jiuploader({fileinput:'#jiuploadinput', receiver:'#jiuploadreceiver', url:this.uploadurl, resizeurl:this.resizeurl});
                jQuery('.jiuploader').on('uploaded', function(e, filepath) {
                    self.open(self.data.ffpath);
                });

                this.equalizeHeights('.ffbody .ffrow', true);
                this.isbuilding = false;
            }
        };
        this.createUploadBar = function(fffooter) {
            var ffrow = jQuery(document.createElement('ul')).attr({'class':'ffrow jiuploader'});
            var ffcol = jQuery(document.createElement('li')).attr({'class': 'col1 span12'});
            var uploadpreview = jQuery(document.createElement('div')).attr({
                class:'preview'
            });
            jQuery(ffcol).append(uploadpreview);

            var uploadcontrols = jQuery(document.createElement('div')).attr({
                class:'controls'
            });
            var uploadlabel = jQuery(document.createElement('span')).attr({
                class:'uploadlabel'
            }).html('Upload file');
            jQuery(uploadcontrols).append(uploadlabel);
            var uploadinput = jQuery(document.createElement('input')).attr({
                type:'file',
                id:'jiuploadinput',
                name:'jiuploadinput[file]',
                rel:this.data.ffpath
            });
            jQuery(uploadcontrols).append(uploadinput);
            var uploadreceiver = jQuery(document.createElement('input')).attr({
                type:'hidden',
                id:'jiuploadreceiver',
                name:'jiuploadreceiver'
            });
            jQuery(uploadcontrols).append(uploadreceiver);
            var uploadhint = jQuery(document.createElement('span')).attr({
                class:'uploadhint'
            }).html('Upload files (Maximum Size: 10MB)');
            jQuery(uploadcontrols).append(uploadhint);
            jQuery(ffcol).append(uploadcontrols);
            var uploadactions = jQuery(document.createElement('div')).attr({
                class:'subactions'
            });
            jQuery(ffcol).append(uploadactions);
            jQuery(ffrow).append(ffcol);
            jQuery(fffooter).append(ffrow);
        }
        this.createTitleBar = function(ffhead) {
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
            jQuery(link).append('<i class="jiicon refresh fa fa-refresh"></i>');
            jQuery(link).on('click', this.openPathHandler);
            jQuery(icontainer).append(link);

            jQuery(ffcol).append(icontainer);
            jQuery(ffrow).append(ffcol);
            // Column 2
            ffcol = jQuery(document.createElement('li')).attr({'class': 'col2 span3 search'});
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
            jQuery(link).append('<i class="jiicon search fa fa-search"></i>');
            jQuery(link).on('click', this.searchHandler);
            jQuery(icontainer).append(link);
            jQuery(ffcol).append(icontainer);
            jQuery(ffrow).append(ffcol);

            // Column 3
            ffcol = jQuery(document.createElement('li')).attr({'class': 'col3 span1 liststyle'});

            var link = jQuery(document.createElement('a')).attr({
                'class': 'jibtn icon16 ui liststyle',
                'href': '#',
                'title': 'Toggle list style'
            });
            var iconclass = 'fa-th';
            if(this.style=='small grid') {
                iconclass = 'fa-th-list';
            } else if(this.style=='medium grid') {
                iconclass = 'fa-th';
            } else if(this.style=='large grid') {
                iconclass = 'fa-th-large';
            } else if(this.style=='list') {
                iconclass = 'fa-list';
            }
            jQuery(link).append('<i class="jiicon liststyleicon fa '+iconclass+'"></i>');
            jQuery(link).on('click', this.toggleStyleHandler);
            jQuery(ffcol).append(link);
            jQuery(ffrow).append(ffcol);

            jQuery(ffhead).append(ffrow);
        }
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
            var cols;
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
            var ffbody = jQuery(document.createElement('li')).attr({'class':'ffbody '+style+' cols'+cols});
            var k = 0;
            var c = 0;
            var r = 0;

            // create file items
            for(var i=0; i<this.data.items.length; i++) {
                var item = this.data.items[i];
                var classtext = '';
                if(c==0) classtext = ' first';
                if(c==cols-1) classtext = ' last';

                var ffrowouter = jQuery(document.createElement('div')).attr({'class':'ffrowouter k'+k});
                var ffrow = jQuery(document.createElement('ul')).attr({'class':'ffrow k'+k});

                // item
                this.createCell(item, ffrow);

                jQuery(ffrowouter).append(ffrow);
                jQuery(ffbody).append(ffrowouter);
                k++;
                if(k>1) k = 0;
                c++;
                if(c>=cols) {
                    c = 0;
                    r++;
                    k = r%2;
                }
            }
            return ffbody;
        }
        /**
         * get action for item
         * @param item
         */
        this.getAction = function(item) {
            if(item.type=='system') {
                // system actions
                if(item.task=='reset') {
                    return this.resetHandler;
                } else if(item.task=='more') {
                    this.start = parseInt(item.start);
                    if(item.searchmore!=null) {
                        return this.searchMoreHandler;
                    } else {
                        return this.openMoreHandler
                    }
                } else if(item.task=='upload') {
                    return function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    };
                }
            } else {
                // default action
                return this.openHandler;
            }
        }
        this.createListBody = function() {
            var ffbody = jQuery(document.createElement('li')).attr({'class':'ffbody list'});
            var k = 0;
            for(var i=0; i<this.data.items.length; i++) {
                var item = this.data.items[i];

                var ffrow = jQuery(document.createElement('ul')).attr({'class':'ffrow k'+k});

                this.createCell(item, ffrow);

                jQuery(ffbody).append(ffrow);
                k++;
                if(k>1) k = 0;
            }
            return ffbody;
        }
        this.createCell = function(item, ffrow) {
            var itemaction = this.getAction(item);
            // Column 1
            var ffcol = jQuery(document.createElement('li')).attr({'class': 'col1 span1 thumb'});
            var link = jQuery(document.createElement('a')).attr({
                'class': 'jibtn icon16 mime '+(item.children!=null)? 'thumblink haschildren' : 'thumblink',
                'href': '#',
                'rel': item.path,
                'title': 'Open directory'
            });

            var iconid = '';
            if(item.loadicon!=null) {
                // create and load icon
                iconid = 'jiloadimg'+this.loadcount;
                var data = {
                    id:this.id,
                    ffpath:item.path,
                    e:iconid
                };
                data[this.id] = this.data.folder;
                this.request(this.loadiconurl, data, 'json', function(response) {
                    if(response!=null) {
                        if(response.valid==true) {
                            var img = jQuery(self.container).find('#'+response.e);
                            jQuery(img).attr('src', self.baseurl+response.img);
                        }
                        var img = jQuery(self.container).find('#'+response.e);
                        jQuery(img).closest('.thumblink').find('.loadingicon').remove();
                    }
                });
                this.loadcount++;
            }
            if(item.icon!=null) {
                var icon = jQuery(document.createElement('span')).attr({'class': 'thumbicon '+item.type});
                var iconimg = jQuery(document.createElement('img')).attr({'src': this.baseurl+item.icon, 'id':iconid});
                jQuery(icon).append(iconimg);
                jQuery(link).append(icon);
                if(item.loadicon!=null) {
                    // show loading
                    jQuery(link).append('<div class="loadingicon"><div class="f_circleG frotateG_01"></div><div class="f_circleG frotateG_02"></div><div class="f_circleG frotateG_03"></div><div class="f_circleG frotateG_04"></div><div class="f_circleG frotateG_05"></div><div class="f_circleG frotateG_06"></div><div class="f_circleG frotateG_07"></div><div class="f_circleG frotateG_08"></div></div>');
                }
            }

            jQuery(link).on('click', itemaction);
            jQuery(ffcol).append(link);
            jQuery(ffrow).append(ffcol);
            // Column 2
            ffcol = jQuery(document.createElement('li')).attr({'class': 'col2 span8 name'});
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

            jQuery(link).on('click', itemaction);

            /*// create uploader as item
             if(item.type=='system' && item.task=='upload') {
             var uploadinput = jQuery(document.createElement('input')).attr({
             type:'file',
             id:'jiuploadinput',
             name:'jiuploadinput',
             rel:item.path
             });
             jQuery(ffcol).append(uploadinput);
             var uploadreceiver = jQuery(document.createElement('input')).attr({
             type:'hidden',
             id:'jiuploadreceiver',
             name:'jiuploadreceiver'
             });
             jQuery(ffcol).append(uploadreceiver);
             }*/
            jQuery(ffcol).append(link);
            jQuery(ffrow).append(ffcol);
            // Column 3
            ffcol = jQuery(document.createElement('li')).attr({'class': 'col3 span2 size'});
            if(item.size!=null) {
                var span = jQuery(document.createElement('span')).attr({
                    'href': '#'
                }).html(item.size);
                jQuery(ffcol).append(span);
            }
            jQuery(ffrow).append(ffcol);
        }
        this.update = function() {
            this.equalizeHeights('.ffbody .ffrow', true);
        };
        this.resizeWindowHandler = function(e) {self.update;};

        // Init
        this.init = function() {
            self.isinitializing = true;
            jQuery(window).on('resize', this.resizeWindowHandler);
            var title = self.id+': '+self.data.folder;
            this.initialstate = {'path':self.data.path, 'title':title};
            //window.history.pushState({'path':self.data.path, 'title':title}, title, self.data.url);
            this.buildlist();
        };
        this.init();
    };
    jQuery.fn.jilistfilter = function(options) {
        var element = jQuery(this);
        // create new class
        var jilistfilter = new JiListFilter(this, options);
        // set and return class data
        element.data('jilistfilter', jilistfilter);
        return jilistfilter;
    };
})(jQuery);