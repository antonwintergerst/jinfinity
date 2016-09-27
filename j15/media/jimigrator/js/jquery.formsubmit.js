/**
 * @version     $Id: jquery.formsubmit.js 116 2015-01-04 12:17:00Z Anton Wintergerst $
 * @package     JiFormSubmit for jQuery
 * @copyright   Copyright (C) 2015 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 * */
(function(jQuery){
    var JiFormSubmit = function(container, options)
    {
        var self = this;

        // Set default options
        this.setDefaultOptions = function() {
            this.btn = null;
            this.form = container;
            this.formtype = null;
            this.status = '.jistatus';
            this.statustext = '.jistatus .jistatustext';
            this.totalprogressbar = '.jistatus .totalprogress';
            this.passprogressbar = '.jistatus .passprogress';
            this.totalprogress = 0;
            this.passprogress = 0;
            this.baseurl = '';
            this.maxcalls = 60;
            this.updatefrequency = 2000;
            this.debug = true;
            this.enableasync = true;
            this.maxfilesize = 8388608; // 8MB
            this.checkstatus = false;
        };
        this.setDefaultOptions();
        
        // Set user options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });

        // Set private options
        this.setPrivateOptions = function() {
            if(this.statusurl==null) this.statusurl = this.baseurl+'&task=status';
            if(this.cleanupurl==null) this.cleanupurl = this.baseurl+'&task=cleanup';
            this.statusTimer = null;
            this.currentcall = 0;
            this.currentmsg = '';
        };
        this.setPrivateOptions();

        // Actions

        /**
         * Console log handler
         * @param data
         */
        this.log = function(data) {
            if(this.debug && typeof console!='undefined') {
                console.log(data);
            }
        }

        /**
         * Method to submit forms without status (automatically updates progress for file uploads)
         */
        this.formSubmit = function() {
            this.checkstatus = false;

            this.cleanup();
            jQuery(this.statustext).html('<h2>Processing request...</h2>');

            var form = jQuery(this.form);

            var filedatasize = 0;
            var fileinputs = jQuery(form).find('[type=file]');
            jQuery.each(fileinputs, function(i, fileinput) {
                var files = jQuery(fileinput).get(0).files;
                if(files!=null) {
                    if(files[0]!=null) filedatasize+=files[0].size;
                }
            });
            if(filedatasize>0) {
                jQuery(form).submit(function(e){
                    if(e.isDefaultPrevented()) return false;
                    // Check for large files

                    if(filedatasize>self.maxfilesize) {
                        self.log('limit: '+self.maxfilesize);
                        self.log(filedatasize);
                        self.requestFailed('<h2>Upload failed: file size exceeds maximum limit</h2>');
                        return false;
                    }
                    //if(jQuery(form).find('[type=file]').filter(function(){ return jQuery(this).val()!=''}).length==0) return true;
                    var formData = new FormData(jQuery(form).get(0));
                    jQuery.ajax({
                        url: jQuery(form).attr('action'),
                        type: 'POST',
                        xhr: function() {
                            myXhr = jQuery.ajaxSettings.xhr();
                            if(myXhr.upload){
                                myXhr.upload.addEventListener('progress',function(e) {
                                    var progress = Math.round(e.loaded * 10000.0 / e.total)/100.0;
                                    if(progress<0) progress = 1;
                                    if(progress>=100) progress = 99;
                                    self.requestProgress(e, progress, 100);
                                }, false);
                            }
                            return myXhr;
                        },
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false
                    }).done(self.uploadSuccess).fail(self.uploadFailed);
                    e.preventDefault();
                });
                jQuery(form).submit();
            } else {
                this.formAsyncSubmit();
            }

        };

        this.requestFailed = function(errorMsg) {
            self.log(errorMsg);
            jQuery(self.statustext).html(errorMsg);
            self.setTotalProgress(100, true, 0);
            self.setPassProgress(100, true, 0);
        };

        /**
         * Called when this.formSubmit() file uploads succeed
         */
        this.uploadSuccess = function() {
            jQuery(self.form).trigger('success');
            //var url = jQuery(self.form).attr('action').split('#')[0];
            //window.location.replace(url);
        };

        /**
         * Called when this.formSubmit() file uploads fail
         * @param contents
         */
        this.uploadFailed = function(contents) {
            if(contents.responseText) contents = contents.responseText;
            try {
                jQuery('body').html(jQuery('<body>').html(contents));
            } catch(e) {
                var action = jQuery(self.form).attr('action').split('#')[0];
                window.location_= action;
            }
        };
        /**
         * Updates this.formSubmit() file upload progress
         * @param e
         * @param progress
         * @param speed
         */
        this.requestProgress = function(e, progress, speed) {
            jQuery(self.statustext).html('Upload '+progress+'% complete');
            if(speed==null) speed = 0;
            self.setTotalProgress(progress, false, speed);
            self.setPassProgress(progress, false, speed);
        };

        /**
         * Method to submit form with status (Asynchonously if possible)
         */
        this.formAsyncSubmit =function() {
            this.checkstatus = true;

            this.cleanup();
            this.log('Posting form');

            // Get Form
            var postvars = jQuery(this.form).serialize();
            var formurl = jQuery(this.form).attr('action');

            // Submit Form using AJAX
            jQuery.ajax({
                url:formurl,
                type:'post',
                data:postvars
            });
            this.getStatus();
        };

        /**
         * Method to request current status
         */
        this.getStatus = function() {
            self.log('Getting status');
            self.currentcall++;
            if(self.currentcall<self.maxcalls) {
                // Call for response
                jQuery.ajax({dataType:'json', async:self.enableasync, url:self.statusurl}).done(self.didReceiveStatus).fail(self.statusFailed);
            } else {
                self.log('Exceeded call limit');
                jQuery(self.statustext).html('Processing Failed! Response timed out');
                window.clearTimeout(self.statusTimer);
            }
        };

        /**
         * Called if this->getStatus() request fails
         * @param jqXHR
         * @param textStatus
         * @param errorThrown
         */
        this.statusFailed = function(jqXHR, textStatus, errorThrown) {
            self.log('Request failed: '+textStatus+' '+errorThrown);
            self.enableasync = false;
            window.clearTimeout(self.statusTimer);
            self.statusTimer = window.setTimeout(self.getStatus, self.updatefrequency);
        };

        /**
         * Called if this->getStatus() request succeeds
         * @param response
         */
        this.didReceiveStatus = function(response) {
            self.log(response);
            var checkAgain = true;
            var newmsg = '';
            if(response!=null) {
                if(response.msg!=null) {
                    newmsg = response.msg;

                    // Update Progress
                    if(response.totalprogress!=null) self.setTotalProgress(response.totalprogress);
                    if(response.passprogress!=null) self.setPassProgress(response.passprogress);

                    // Update Status
                    if(response.html!=null) {
                        jQuery(self.statustext).html(response.html);
                    } else {
                        jQuery(self.statustext).html(response.msg);
                    }
                }
                if(response.status!=null) {
                    if(response.status=='pass') {
                        self.passurl = response.url
                        window.setTimeout(function() {
                            self.log('running next pass');
                            jQuery.ajax({url:self.passurl});
                        }, self.updatefrequency);
                    } else if(response.status=='failed') {
                        self.setTotalProgress(100, true, 0);
                        self.setPassProgress(100, true, 0);

                        window.clearTimeout(self.statusTimer);
                        checkAgain = false;
                        jQuery.ajax({url:self.cleanupurl});
                    } else if(response.status=='complete') {
                        self.setTotalProgress(100);
                        self.setPassProgress(100);

                        // Status indicates the process has finished
                        window.clearTimeout(self.statusTimer);
                        checkAgain = false;
                        jQuery.ajax({url:self.cleanupurl});

                        jQuery(self.form).trigger('success');
                    }
                }
            }
            if(newmsg!=self.currentmsg) {
                // This is a different message to the last call
                self.currentmsg = newmsg;
                self.currentcall = 0;
            }
            if(checkAgain) self.statusTimer = window.setTimeout(self.getStatus, self.updatefrequency);
        };

        /**
         * Resets class state
         */
        this.cleanup = function() {
            jQuery(self.totalprogressbar).addClass('animate blue').removeClass('red');
            jQuery(self.passprogressbar).addClass('animate').removeClass('red');
            jQuery(self.totalprogressbar+' .bar').css('width', '0%');
            jQuery(self.passprogressbar+' .bar').css('width', '0%');
            this.totalprogress = 0;
            this.passprogress = 0;
            this.currentcall = 0;
            this.currentmsg = '';
            jQuery(this.statustext).html('<h2>Warming up...</h2>');
        };

        /**
         * Method to update total progress UI
         * @param progress
         * @param failed
         * @param speed
         */
        this.setTotalProgress = function(progress, failed, speed) {
            if(speed==null) speed = self.updatefrequency;

            if(progress==100) {
                jQuery(self.totalprogressbar).removeClass('animate');
                jQuery(self.passprogressbar).removeClass('animate');
            }
            if(progress<self.totalprogress || speed==0) {
                jQuery(self.totalprogressbar+' .bar').stop(true, true).css('width', progress+'%');
            } else {
                jQuery(self.totalprogressbar+' .bar').animate({
                    'width':progress+'%'
                }, speed);
            }
            self.totalprogress = progress;
            if(failed) {
                // Status indicates the process has failed
                jQuery(self.totalprogressbar).removeClass('blue').addClass('red');
                jQuery(self.totalprogressbar+' .bar').stop(true, true).css('width', '100%');
            }
        };

        /**
         * Method to update pass progress UI
         * @param progress
         * @param failed
         * @param speed
         */
        this.setPassProgress = function(progress, failed, speed) {
            if(speed==null) speed = self.updatefrequency;

            if(progress==0 || progress<self.passprogress || speed==0) {
                jQuery(self.passprogressbar+' .bar').stop(true, true).css('width', progress+'%');
            } else {
                jQuery(self.passprogressbar+' .bar').animate({
                    'width':progress+'%'
                }, speed);
            }
            self.passprogress = progress;
            if(failed) {
                // Status indicates the process has failed
                jQuery(self.passprogressbar).removeClass('blue').addClass('red');
                jQuery(self.passprogressbar+' .bar').stop(true, true).css('width', '100%');
            }
        };
        
        // Setup Handlers
        this.submitHandler = function (e){
            self.formSubmit();
            e.preventDefault();
            e.stopPropagation();
        };
        this.asyncSubmitHandler = function (e){
            self.formAsyncSubmit();
            e.preventDefault();
            e.stopPropagation();
        };

        // Init
        if(this.formtype=='async') {
            jQuery(this.btn).on('click', this.asyncSubmitHandler);
        } else {
            jQuery(this.btn).on('click', this.submitHandler); 
        }
    };
    jQuery.fn.jiformsubmit = function(options) {
        var element = jQuery(this);
        // Create new class
        var jiformsubmit = new JiFormSubmit(this, options);
        // Set and return class data
        element.data('jiformsubmit', jiformsubmit);
        return jiformsubmit;
    };
})(jQuery);