/*
 * @version     $Id: jquery.jiuploader.js 132 2014-10-24 17:48:00Z Anton Wintergerst $
 * @package     JiUploader for jQuery
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
(function($){
    var JiUploader = function(container, options)
    {
        var self = this;
        this.strings = {
            'resizethumbnail':Joomla.JText._('COM_JICUSTOMFIELDS_RESIZETHUMBNAIL', 'Resize Thumbnail'),
            'browse':Joomla.JText._('COM_JICUSTOMFIELDS_BROWSE', 'Browse'),
            'upload':Joomla.JText._('COM_JICUSTOMFIELDS_UPLOAD', 'Start Upload'),
            'cancel':Joomla.JText._('COM_JICUSTOMFIELDS_CANCEL', 'Cancel'),
            'remove':Joomla.JText._('COM_JICUSTOMFIELDS_REMOVE', 'Remove')
        };
        // Set Default Options
        this.fileinput = null;
        this.receiver = null;
        this.uploadedfile = '';
        this.url = null;
        this.resizeURL = null;
        this.aspect = null;
        this.id = null;
        this.row = null;
        this.maxtime = 5;
        this.debug = false;
        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        this.curtime = 1;
        this.cloneinput = null;
        this.preview = null;
        this.image = null;
        this.box = null;
        this.dragTarget = null;
        this.offset = {x:0, y:0};
        this.response = null;
        this.container = jQuery(this.fileinput).closest('.jiuploader');
        this.upcontainer = null;
        this.p1coords = {x:0, y:0};
        this.p2coords = {x:0, y:0};
        // Actions
        this.listen = function() {
            // Check for New file added to uploader
            jQuery(this.fileinput).on('change', this.fileChangedHandler);
            jQuery(this.fileinput).on('click', function(e) {
                var target = e.target != null ? e.target : e.srcElement;

            });
        };
        this.ielisten = function() {
            jQuery(this.fileinput).on('change', this.IEfileChangedHandler);
        };
        this.reset = function() {
            this.uploadedfile = '';
            jQuery(this.container).find('div.preview').html('');
            jQuery(this.container).find('div.subactions').html('');
        }
        this.IEfileChanged = function() {
            // Remove Old Previews
            var previews = jQuery(this.fileinput).parent().find('div.preview');
            jQuery.each(previews, function(index, preview) {
                jQuery(preview).html('');
            });
            // Clear receiver
            this.uploadedfile = "";
        };
        this.getFile = function(e) {
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            // Remove Get File Handler
            jQuery(this.fileinput).parent().off('mouseup', this.getFileHandler);
            // Tell browser we want to browse for files
            jQuery(this.fileinput).click();
        };
        this.fileChanged = function() {
            //this.uploadFile();
            // Read File
            this.readFile();
            // Clear receiver
            this.uploadedfile = "";
        };
        this.readFile = function() {
            // clear old previews
            var previews = jQuery(this.container).find('div.preview');
            jQuery.each(previews, function(index, preview) {
                jQuery(preview).html('');
            });
            
            // Check we're not in IE
            var isMSIE = /*@cc_on!@*/0;
            if(!isMSIE) {
                this.preview = jQuery('.preview');
                if(!this.preview.length) {
                    // create new preview
                    this.preview = jQuery(document.createElement('div')).attr('class', 'preview');
                    jQuery(this.fileinput).parent().append(this.preview);
                }
                this.image = jQuery(document.createElement('img'));
                jQuery(this.preview).append(this.image);
                
                /*jQuery(this.image).load(function(){
                    self.image.src = e.target.result;
                    console.log(self.image);
                });*/
                for(i=0; i<jQuery(this.fileinput)[0].files.length; i++){
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        jQuery(self.image).attr({
                            'src': e.target.result,
                            'id': 'imagepreview'
                        }).load(function(){
                            //self.drawBoxes();
                            // show resize editor
                            //clearTimeout(self.checkTimer);
                            //self.checkTimer = setTimeout(drawBoxes, 200);
                        });
                    };
                    reader.readAsDataURL(jQuery(this.fileinput)[0].files[i]);
                }
                // Wait for image to load
                /*var that = this;
                new Asset.image(that.image.src, {
                    id: 'imagepreview',
                    onLoad : that.drawBoxes.delay(200, that)
                    
                });
                reader.readAsDataURL(this.files[0]);*/
            }
            var actions = jQuery(this.container).find('div.subactions');
            if(actions.length) {
                // clear old actions
                jQuery.each(actions, function(index, action) {
                    jQuery(action).html('');
                });
            } else {
                // create new actions container
                actions = jQuery(document.createElement('div')).attr('class', 'subactions');
                jQuery(this.fileinput).parent().append(actions);
            }
            /*var browsebtn = jQuery(document.createElement('a')).attr('href', '#').html(self.strings.browse).on('click', this.getFileHandler);
            jQuery(actions).append(browsebtn);*/
            // Check we're not in IE
            var isMSIE = /*@cc_on!@*/0;
            if(!isMSIE) {
                var uploadbtn = jQuery(document.createElement('a')).attr({
                    href:'#',
                    class:'uploadbtn btn btn-primary'
                }).html('<i class="icon-upload icon-white"></i> '+self.strings.upload).on('click', this.uploadFileHandler);
                jQuery(actions).append(uploadbtn);
            }
            var cancelbtn = jQuery(document.createElement('a')).attr({
                href: '#',
                class:'btn'
            }).html(this.strings.cancel).on('click', this.cancelUploadHandler);
            jQuery(actions).append(cancelbtn);

        };
        this.readExisting = function(e) {
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            
            // Find existing preview
            var previews = jQuery(this.container).find('div.preview');
            this.flowImage();
            jQuery.each(previews, function(index, preview) {
                self.preview = preview;
                var images = jQuery(preview).find('img');
                if(images[0]!=null) {
                    var image = jQuery(images).get(0);
                    self.resize = true;
                    self.src = jQuery(image).attr('src').replace('_custom150.jpg', '_original.jpg');
                    self.uploadedfile = self.src;
                    jQuery(image).attr('src', self.src).load(function() {
                        var imagesize = self.getSize(image);
                        self.image = image;
                        clearTimeout(self.checkTimer);
                        self.checkTimer = setTimeout(drawBoxes, 200);
                    });
                    
                    
                }
            });
            var actions = jQuery(this.container).find('div.subactions');
            if(actions.length) {
                // clear old actions
                jQuery.each(actions, function(index, action) {
                    jQuery(action).html('');
                });
            } else {
                // create new actions container
                actions = jQuery(document.createElement('div')).attr('class', 'subactions');
                jQuery(this.fileinput).parent().append(actions);
            }
            /*var browsebtn = jQuery(document.createElement('a')).attr('href', '#').html(self.strings.browse).on('click', this.getFileHandler);
            jQuery(actions).append(browsebtn);*/
            // Check we're not in IE
            var isMSIE = /*@cc_on!@*/0;
            if(!isMSIE) {
                var uploadbtn = jQuery(document.createElement('a')).attr({
                    href:'#',
                    class:'uploadbtn btn btn-primary'
                }).html('<i class="icon-upload icon-white"></i> '+self.strings.upload).on('click', this.uploadFileHandler);
                jQuery(actions).append(uploadbtn);
            }
            var cancelbtn = jQuery(document.createElement('a')).attr({
                href: '#',
                class:'btn'
            }).html(this.strings.cancel).on('click', this.cancelResizeHandler);
            jQuery(actions).append(cancelbtn);
        };
        this.flowImage = function() {
            jQuery(this.image).css({
                'width': 'auto',
                'height': 'auto'
            });
            jQuery(this.image).parent().css({
                'width': 'auto',
                'height': 'auto'
            });
        };
        this.fixImage = function() {
            var imagesize = this.getSize(this.image);
            jQuery(this.image).css({
                'width': imagesize.x+'px',
                'height': imagesize.y+'px'
            });
            // Scale preview container to match image size
            jQuery(this.image).parent().css({
                'width': imagesize.x+'px',
                'height': imagesize.y+'px'
            });
        };
        this.drawBoxes = function() {
            var imagesize = this.getSize(this.image);
            this.fixImage();
            if(this.aspect != null) {
                var ratio = imagesize.x/imagesize.y;
                if(ratio==1) {
                    // This is a square image
                    var width = imagesize.x/1.25;
                    var height = imagesize.y/1.25;
                } else if (ratio>1) {
                    // This is a wide image
                    var height = imagesize.y/1.25;
                    var width = height/this.aspect;
                } else {
                    // This is a tall image
                    var width = imagesize.x/1.25;
                    var height = width/this.aspect;
                }
                var p1coords = {x: (imagesize.x - width)/2, y:(imagesize.y - height)/2};
                var p2coords = {x: imagesize.x-((imagesize.x-height)/2), y:imagesize.y-((imagesize.y-height)/2)};
            } else {
                var p1coords = {x: (imagesize.x/5), y:(imagesize.y/5)};
                var p2coords = {x: imagesize.x-(imagesize.x/5), y:imagesize.y-(imagesize.y/5)};
            }
            //this.updateBoxes(p1coords, p2coords, imagesize);
            var topfill = jQuery(document.createElement('div')).attr('class', 'fill top').css({
                width: '100%',
                height: Math.round(p1coords.y)+'px',
                left: '0px',
                top: '0px'
            });
            var bottomfill = jQuery(document.createElement('div')).attr('class', 'fill bottom').css({
                width: '100%',
                height: Math.round(imagesize.y-p2coords.y)+'px',
                left: '0px',
                top: p2coords.y+'px'
            });
            var leftfill = jQuery(document.createElement('div')).attr('class', 'fill left').css({
                width: Math.round(p1coords.x)+'px',
                height: Math.round(p2coords.y-p1coords.y)+'px',
                left: '0px',
                top: Math.round(p1coords.y)+'px'
            });
            var rightfill = jQuery(document.createElement('div')).attr('class', 'fill right').css({
                width: Math.round(imagesize.x-p2coords.x)+'px',
                height: Math.round(p2coords.y-p1coords.y)+'px',
                left: Math.round(p2coords.x)+'px',
                top: Math.round(p1coords.y)+'px'
            });
            var p1 = jQuery(document.createElement('span')).attr('class', 'point p1').css({
                left: Math.round(p1coords.x-2)+'px',
                top: Math.round(p1coords.y-2)+'px'
            }).on('mousedown', this.startDragHandler);
            var p2 = jQuery(document.createElement('span')).attr('class', 'point p2').css({
                left: Math.round(p2coords.x-2)+'px',
                top: Math.round(p2coords.y-2)+'px'
            }).on('mousedown', this.startDragHandler);
            var handle = jQuery(document.createElement('span')).attr('class', 'handle').css({
                width: Math.round(p2coords.x-p1coords.x)+'px',
                height: Math.round(p2coords.y-p1coords.y)+'px',
                left: Math.round(p1coords.x)+'px',
                top: Math.round(p1coords.y)+'px'
            }).on('mousedown', this.startDragHandler);
            
            jQuery(this.preview).append(topfill);
            jQuery(this.preview).append(bottomfill);
            jQuery(this.preview).append(leftfill);
            jQuery(this.preview).append(rightfill);
            jQuery(this.preview).append(p1);
            jQuery(this.preview).append(p2);
            jQuery(this.preview).append(handle);
            this.box = {p1: p1, p2: p2, topfill: topfill, bottomfill: bottomfill, leftfill: leftfill, rightfill: rightfill, handle: handle};
        };
        this.updateBoxes = function(p1coords, p2coords, imagesize, imagecoords) {
            var update = 1;
            if(this.aspect!=null) {
                // Lock Aspect Ratio
                var width = p2coords.x-p1coords.x;
                var height = p2coords.y-p2coords.y;
                var ratio = width/height;
                if(this.dragTarget==this.box.p1) {
                    if(ratio>this.aspect) {
                        // Current size is too wide
                        p1coords.y = p2coords.y - width;
                    } else {
                        // Current size is too tall
                        p1coords.x = p2coords.x - height;
                    }
                    // Lock Boundary Constraints
                    if(p1coords.x<=0) update = 0;
                    if(p1coords.y<=0) update = 0;
                } else if(this.dragTarget==this.box.p2) {
                    if(ratio>this.aspect) {
                        // Current size is too wide
                        p2coords.y = p1coords.y + width;
                    } else {
                        // Current size is too tall
                        p2coords.x = p1coords.x + height;
                    }
                    // Lock Boundary Constraints
                    if(p2coords.x>=imagesize.x) update = 0;
                    if(p2coords.y>=imagesize.y) update = 0;
                }
            } else {
                // Lock Boundary Constraints
                if(p1coords.x<0) p1coords.x = 0;
                if(p1coords.y<0) p1coords.y = 0;
                if(p2coords.x>imagesize.x) p2coords.x = imagesize.x;
                if(p2coords.y>imagesize.y) p2coords.y = imagesize.y;
            }
            
            if(update==1) {
                // Update Box Elements
                jQuery(this.box.topfill).css('height', Math.round(p1coords.y)+'px');
                jQuery(this.box.bottomfill).css({
                    'height': Math.round(imagesize.y-p2coords.y)+'px',
                    'top': Math.round(p2coords.y)+'px'
                });
                jQuery(this.box.leftfill).css({
                    'width': Math.round(p1coords.x)+'px',
                    'height': Math.round(p2coords.y-p1coords.y)+'px',
                    'top': Math.round(p1coords.y)+'px'
                });
                jQuery(this.box.rightfill).css({
                    'width': Math.round(imagesize.x-p2coords.x)+'px',
                    'height': Math.round(p2coords.y-p1coords.y)+'px',
                    'left': Math.round(p2coords.x)+'px',
                    'top': Math.round(p1coords.y)+'px'
                });
                jQuery(this.box.p1).css({
                    'left': Math.round(p1coords.x-2)+'px',
                    'top': Math.round(p1coords.y-2)+'px'
                });
                jQuery(this.box.p2).css({
                    'left': Math.round(p2coords.x-2)+'px',
                    'top': Math.round(p2coords.y-2)+'px'
                });
                jQuery(this.box.handle).css({
                    'width': Math.round(p2coords.x-p1coords.x)+'px',
                    'height': Math.round(p2coords.y-p1coords.y)+'px',
                    'left': Math.round(p1coords.x)+'px',
                    'top': Math.round(p1coords.y)+'px'
                });
            }
        };
        this.startDrag = function(e) {
            //console.log('starting drag');
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            // Find Target
            var target = e.target != null ? e.target : e.srcElement;
            this.dragTarget = target;
            // Set current point coords
            this.p1coords = {x: jQuery(this.box.p1).position().left, y: jQuery(this.box.p1).position().top};
            this.p2coords = {x: jQuery(this.box.p2).position().left, y: jQuery(this.box.p1).position().top};
            // Set start positions
            this.cursorStart = {x:e.pageX, y:e.pageY};
            this.p1Start = {x: parseInt(jQuery(this.box.p1).position().left), y: parseInt(jQuery(this.box.p1).position().top)};
            this.p2Start = {x: jQuery(this.box.p2).position().left, y: jQuery(this.box.p2).position().top};
            // Add Drag Handlers
            jQuery(document).on('mousemove', this.doDragHandler);
            jQuery(document).on('mouseup', this.stopDragHandler);
        };
        this.doDrag = function(e) {
            //console.log('doing drag');
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            var boxsize = this.getSize(this.image);
            var boxcoords = this.getCoords(this.image);
            var coords = {x:e.pageX, y:e.pageY};
            var update = 0;
            var cursorDelta = {x: coords.x - this.cursorStart.x, y: coords.y - this.cursorStart.y};
            var p1coords = {x: this.p1Start.x, y: this.p1Start.y};
            var p2coords = {x: this.p2Start.x, y: this.p2Start.y};
            if(jQuery(this.dragTarget).is(this.box.handle)) {
                // Move resize box
                var newp1 = {x: this.p1Start.x + cursorDelta.x, y: this.p1Start.y + cursorDelta.y};
                var newp2 = {x: this.p2Start.x + cursorDelta.x, y: this.p2Start.y + cursorDelta.y};
                if(0<newp1.x && newp2.x<boxsize.x) {
                    p1coords.x = newp1.x;
                    p2coords.x = newp2.x;
                    update = 1;
                }
                if(0<newp1.y && newp2.y<boxsize.y) {
                    p1coords.y = newp1.y;
                    p2coords.y = newp2.y;
                    update = 1;
                }
            } else if(jQuery(this.dragTarget).is(this.box.p1)) {
                // Move point 1
                var newp1 = {x: this.p1Start.x + cursorDelta.x, y: this.p1Start.y + cursorDelta.y};
                var safex = (0<newp1.x && newp1.x<boxsize.x && (newp1.x+16)<p2coords.x)? 1:0;
                if(this.aspect == null && safex==1) {
                    p1coords.x = newp1.x;
                    update = 1;
                }
                var safey = (0<newp1.y && newp1.y<boxsize.y && (newp1.y+16)<p2coords.y)? 1:0;
                if(this.aspect == null && safey==1) {
                    p1coords.y = newp1.y;
                    update = 1;
                }
                if(this.aspect != null) {
                    p1coords.x = newp1.x;
                    p1coords.y = newp1.y;
                    if(safex==1 && safey==1) update = 1;
                }
            } else if(jQuery(this.dragTarget).is(this.box.p2)) {
                // Move point 2
                var newp2 = {x: this.p2Start.x + cursorDelta.x, y: this.p2Start.y + cursorDelta.y};
                var safex = (0<newp2.x && newp2.x<boxsize.x && (newp2.x-16)>p1coords.x)? 1:0;
                if(this.aspect == null && safex==1) {
                    p2coords.x = newp2.x;
                    update = 1;
                }
                var safey = (0<newp2.y && newp2.y<boxsize.y && (newp2.y-16)>p1coords.y)? 1:0;
                if(this.aspect == null && safey==1) {
                    p2coords.y = newp2.y;
                    update = 1;
                }
                if(this.aspect != null) {
                    p2coords.x = newp2.x;
                    p2coords.y = newp2.y;
                    if(safex==1 && safey==1) update = 1;
                }
            }
            if(update==1) {
                this.updateBoxes(p1coords, p2coords, boxsize, boxcoords);
            }
        };
        this.stopDrag = function() {
            //console.log('stopped drag');
            // Remove Drag Handlers
            jQuery(document).off('mousemove', this.doDragHandler);
            jQuery(document).off('mouseup', this.stopDragHandler);
        };
        this.uploadFile = function(e) {
            console.log('uploading');
            jQuery(this.container).find('.uploadbtn').html('Uploading...');
            if(e) {
                // Prevent Default Actions
                e.preventDefault();
                e.stopPropagation();
            }
            if(this.resize==null) {
                // Upload New Image
                // Create new iframe container
                this.upcontainer = jQuery(document.createElement('iframe')).attr({
                    'src': "javascript:false",
                    'name': "upcontainer"+this.id,
                    'id': "upcontainer"+this.id
                });
                // Hide container
                jQuery(this.upcontainer).css("display", "none");
                // Append container to document
                jQuery(document.body).append(this.upcontainer);
                // Create new form container
                var hiddenform = jQuery(document.createElement('form')).attr({
                    'target': "upcontainer"+this.id,
                    'name': "uploader",
                    'method': "post",
                    'enctype': "multipart/form-data",
                    'action': this.url
                });
                // add destination
                var ffpath = jQuery(document.createElement('input')).attr({
                    'name': "ffpath",
                    'type': "hidden"
                }).val(jQuery(this.fileinput).attr('rel'));
                jQuery(hiddenform).append(ffpath);

                // Add Point Values
                if(this.box) {
                    var imagesize = this.getSize(this.image);
                    var p1 = jQuery(this.box.p1).position();
                    var p2 = jQuery(this.box.p2).position();
                    var p1input = jQuery(document.createElement('input')).attr({
                        'name': "p1",
                        'type': "hidden"
                    }).val((((p1.left+2)*100)/imagesize.x)+','+(((p1.top+2)*100)/imagesize.y));
                    jQuery(hiddenform).append(p1input);
                    var p2input = jQuery(document.createElement('input')).attr({
                        'name': "p2",
                        'type': "hidden"
                    }).val((((p2.left+2)*100)/imagesize.x)+','+(((p2.top+2)*100)/imagesize.y));
                    jQuery(hiddenform).append(p2input);
                }
                // Add FID
                var fidinput = jQuery(document.createElement('input')).attr({
                    'name': "fid",
                    'type': "hidden"
                }).val(this.id);
                jQuery(hiddenform).append(fidinput);
                // Add Row
                var rowinput = jQuery(document.createElement('input')).attr({
                    'name': "row",
                    'type': "hidden"
                }).val(this.row);
                jQuery(hiddenform).append(rowinput);
                // Clone file input
                this.cloneinput = jQuery(this.fileinput).clone();
                // Attach file changed event
                this.cloneinput.on('change', this.fileChangedHandler);    
                // Replace original with clone
                jQuery(this.cloneinput).insertAfter(this.fileinput);    
                // Move file input
                jQuery(hiddenform).append(this.fileinput);
                // Append hiddenform to iframe
                jQuery(this.upcontainer).append(hiddenform);
                // Submit form
                hiddenform.submit();
                // Start testing to see if file is processed
                //this.checkTimer = this.checkUpload.delay(200, this);
                this.curtime = 1;
                clearTimeout(self.checkTimer);
                self.checkTimer = setTimeout(checkUpload, 200);
            } else {
                // Resize Existing
                var imagesize = this.getSize(this.image);
                var p1 = jQuery(this.box.p1).position();
                var p2 = jQuery(this.box.p2).position();
                // Perform AJAX request
                jQuery.ajax({url:this.resizeURL,
                    type:'post',
                    data:{
                        'image':this.src,
                        'p1':(((p1.left+2)*100)/imagesize.x)+','+(((p1.top+2)*100)/imagesize.y),
                        'p2':(((p2.left+2)*100)/imagesize.x)+','+(((p2.top+2)*100)/imagesize.y)
                    }
                }).done(function(response) {
                    if(response!=null) {
                        if(response!='false') {
                            self.src = response;
                            self.uploadedfile = response;
                            // Remove Old Preview
                            jQuery(self.preview).html('');

                            self.preview = jQuery('.preview');
                            if(!self.preview.length) {
                                // create new preview
                                self.preview = jQuery(document.createElement('div')).attr('class', 'preview');
                                jQuery(self.fileinput).parent().append(self.preview);
                            }
                            // Add random number to prevent image caching
                            var randomN = Math.floor((Math.random()*100000)+1);
                            self.image = jQuery(document.createElement('img')).attr('src', self.src+'?'+randomN).load(function() {
                                self.fixImage();
                            });
                            jQuery(self.preview).append(self.image);

                            var actions = jQuery(this.fileinput).closest('controls').parent().find('div.subactions');
                            if(actions.length) {
                                // clear old actions
                                jQuery.each(actions, function(index, action) {
                                    jQuery(action).html('');
                                });
                            } else {
                                // create new actions container
                                actions = jQuery(document.createElement('div')).attr('class', 'subactions');
                                jQuery(this.fileinput).closest('controls').parent().append(actions);
                            }
                            /*var browsebtn = jQuery(document.createElement('a')).attr('href', '#').html(self.strings.browse).on('click', self.getFileHandler);
                            jQuery(actions).append(browsebtn);*/
                            var resizebtn = jQuery(document.createElement('a')).attr({
                                'href':'#',
                                'rel':'resize'
                            }).html(self.strings.resizethumbnail).on('click', self.readExistingHandler);
                            jQuery(actions).append(resizebtn);
                            var cancelbtn = jQuery(document.createElement('a')).attr({
                                href: '#',
                                class:'btn'
                            }).html(self.strings.cancel).on('click', this.cancelUploadHandler);
                            jQuery(actions).append(cancelbtn);
                        }
                    }
                });
                
            }
        };
        this.checkUpload = function() {
            //console.log('checking upload');
            clearTimeout(this.checkTimer);
            // Check for response in iframe body
            this.response = jQuery(window[jQuery(this.upcontainer).attr('name')].document.getElementsByTagName("body")[0]).html();
            if(this.curtime>=this.maxtime) {
                // Exceeded max number of upload checks
                alert('Error: Upload is taking too long.\nPlease try again later');
            } else {
                this.curtime++;
                if(this.response==null) {
                    // No response found, try again
                    this.checkTimer = setTimeout(checkUpload, 1000);
                } else {
                    if(this.response=='') {
                        // No response found, try again
                        this.checkTimer = setTimeout(checkUpload, 1000);
                    } else {
                        if(this.response=='false'||this.response.indexOf('error')!=-1||this.response.length>255) {
                            // reset uploadbtn
                            jQuery(this.container).find('.uploadbtn').html('<i class="icon-upload icon-white"></i> '+this.strings.upload);

                            // Something went wrong. Let the user know
                            alert('Unable to upload the file.\nPlease try again later');
                            // DEBUG
                            if(this.debug) console.log(this.response);
                        } else {
                            // Delay updating of preview so the server has time to process the image
                            this.checkTimer = setTimeout(uploadSuccess, 1000);
                        }
                        // Remove upcontainer
                        jQuery(this.upcontainer).remove();
                        // Make cloneinput the new fileinput
                        this.fileinput = this.cloneinput;
                        // Add Get File Handler Back
                        //jQuery(this.fileinput).parent().on('mouseup', this.getFileHandler);
                    }
                }
            }
        };
        this.uploadSuccess = function() {
            // callback
            jQuery(this.container).trigger('uploaded', this.response);
            // DEBUG
            if(this.debug) console.log(this.response);
        };
        this.cancelUpload = function(e) {
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            
            // Remove Old Previews
            var previews = jQuery(this.container).find('div.preview');
            jQuery.each(previews, function(index, preview) {
                jQuery(preview).html('').removeAttr('style');
            });
            var actions = jQuery(this.container).find('div.subactions');
            if(actions.length) {
                // clear old actions
                jQuery.each(actions, function(index, action) {
                    jQuery(action).html('');
                });
            }
            // Add Get File Handler Back
            //jQuery(this.fileinput).parent().on('mouseup', this.getFileHandler);
            this.fileinput.value = "";
            this.uploadedfile = "";
            //this.initialize();
        };
        this.cancelResize = function(e) {
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            
            // Remove Resize boxes
            if(this.box!=null) {
                if(this.box.topfill!=null) jQuery(this.box.topfill).remove();
                if(this.box.bottomfill!=null) jQuery(this.box.bottomfill).remove();
                if(this.box.leftfill!=null) jQuery(this.box.leftfill).remove();
                if(this.box.rightfill!=null) jQuery(this.box.rightfill).remove();
                if(this.box.p1!=null) jQuery(this.box.p1).remove();
                if(this.box.p2!=null) jQuery(this.box.p2).remove();
                if(this.box.handle!=null) jQuery(this.box.handle).remove();
            }
            var actions = jQuery(this.container).find('div.subactions');
            if(actions.length) {
                // clear old actions
                jQuery.each(actions, function(index, action) {
                    jQuery(action).html('');
                });
            } else {
                // create new actions container
                actions = jQuery(document.createElement('div')).attr('class', 'subactions');
                jQuery(this.fileinput).parent().append(actions);
            }
            /*var browsebtn = jQuery(document.createElement('a')).attr('href', '#').html(self.strings.browse).on('click', this.getFileHandler);
            jQuery(actions).append(browsebtn);*/
            var resizebtn = jQuery(document.createElement('a')).attr({
                'href':'#',
                'rel':'resize'
            }).html(self.strings.resizethumbnail).on('click', this.readExistingHandler);
            jQuery(actions).append(resizebtn);
            var cancelbtn = jQuery(document.createElement('a')).attr({
                href: '#',
                class:'btn'
            }).html(this.strings.cancel).on('click', this.cancelUploadHandler);
            jQuery(actions).append(cancelbtn);
        };
        this.getSize = function (e) {
            var width = jQuery(e).width();
            var height = jQuery(e).height();
            return {x:width, y:height};
        };
        this.getCoords = function (e) {
            var pos = jQuery(e).offset();
            var x = pos.left;
            var y = pos.top;
            return {x:x, y:y};
        };
        this.getMouse = function(e) {
            x = (window.Event) ? e.pageX : event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
            y = (window.Event) ? e.pageY : event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
            return {x:x, y:y};
        }
        
        // Setup Handlers
        this.toggleHandler = function(e) {self.toggle(e);};
        this.getFileHandler = function(e) {self.getFile(e);};
        this.fileChangedHandler = function(e) {self.fileChanged();};
        this.readExistingHandler = function(e) {self.readExisting(e);};
        this.drawBoxesHandler = function(e) {self.drawBoxes();};
        this.startDragHandler = function(e) {self.startDrag(e);};
        this.doDragHandler = function(e) {self.doDrag(e);};
        this.stopDragHandler = function(e) {self.stopDrag();};
        this.uploadFileHandler = function(e) {self.uploadFile(e);};
        this.cancelUploadHandler = function(e) {self.cancelUpload(e);};
        this.cancelResizeHandler = function(e) {self.cancelResize(e);};
        this.nothingHandler = function(e) {
            // Prevent Default Actions
            e.preventDefault();
            //e.stopPropagation();
        };
        function drawBoxes() {self.drawBoxes();}
        function checkUpload() {self.checkUpload();}
        function uploadSuccess() {self.uploadSuccess();}
        
        // Init
        this.init = function() {

            this.fileinput = jQuery(this.fileinput);
            // Check we're not in IE
            var isMSIE = /*@cc_on!@*/0;
            if(!isMSIE) {
                //jQuery(this.fileinput).css('display', 'none');
                // Setup browsers
                var browsers = jQuery(this.fileinput).parent().find('a');
                jQuery.each(browsers, function(index, browser) {
                    if(typeof jQuery(browser).attr('rel')!= 'undefined') {
                        if(jQuery(browser).attr('rel').indexOf("browse")!=-1) jQuery(browser).on('click', self.getFileHandler);
                        if(self.uploadedfile!='' && jQuery(browser).attr('rel').indexOf("resize")!=-1) {
                            // Add resize button
                            var resizebtn = jQuery(document.createElement('a')).attr({
                                'href':'#',
                                'rel':'resize'
                            }).html(self.strings.resizethumbnail).on('click', self.readExistingHandler);
                            jQuery(resizebtn).insertAfter(browser);
                        }
                    }
                });
                // Everything is set so start listening
                this.listen();
            } else {
                console.log('JiUploader running in compatibility mode.');
                // IE Support
                // Remove browsers
                var browsers = jQuery(this.fileinput).closest('a');
                jQuery.each(browsers, function(index, browser) {
                    if(self.uploadedfile!='' && jQuery(browser).attr('rel').indexOf("resize")!=-1) {
                        // Add resize button
                        var resizebtn = jQuery(document.createElement('a')).attr({
                            'href':'#',
                            'rel':'resize'
                        }).html(self.strings.resizethumbnail).on('click', self.readExistingHandler);
                        jQuery(resizebtn).insertAfter(browser);
                    }
                    if(jQuery(browsers).attr('rel').indexOf("browse")!=-1 && jQuery(browser).attr('rel').indexOf("noie")!=-1) jQuery(browser).remove();
                });
                this.IEfileChangedHandler = function(e) {self.IEfileChanged;};
                this.ielisten();
            }
        };
        this.init();
    };
    jQuery.fn.jiuploader = function(options) {
        var element = jQuery(this);
        if(element.data('jiuploader')) {
            // Load existing class
            var jiuploader = element.data('jiuploader').init();
            return element.data('jiuploader');
        } else {
            // Create new class
            var jiuploader = new JiUploader(this, options);
        }
        // Set and return class data
        element.data('jiuploader', jiuploader);
        return jiuploader;
    };
})(jQuery);