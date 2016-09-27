/**
 * @version     $Id: jquery.jitypechanger.js 056 2013-07-17 16:21:00Z Anton Wintergerst $
 * @package     JiTypeChanger for jQuery
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiTypeChanger = function(container, options)
    {
        var self = this;

        // Set Private Options
        this.setPrivateOptions = function() {
            this.instances = [];
            this.jihidemap = {};
            this.jidcount = 0;
        };
        this.setPrivateOptions();
        this.getChanger = function() {
            return self;
        }
        this.createInstance = function(e, ioptions) {
            if(ioptions==null) ioptions = {};
            ioptions.getChangerHandler = this.getChanger;
            var element = jQuery(e);
            if(element.data('jitypechanger')) {
                return element.data('jitypechanger');
            } else {
                var instance = new JiTypeChangerInstance(e, ioptions);
                element.data('jitypechanger', instance);
                this.instances.push(instance);
                return instance;
            }
        };
        /**
         * Updates HTML elements with results from JiTypeChanger Instances
         */
        this.updateTypeOptions = function() {
            this.jihidemap = {};
            jQuery.each(this.instances, function(index, instance) {
                instance.updateTypeOptions();
            });
            jQuery.each(this.jihidemap, function(jid, hidecount) {
                if(hidecount<=-1) {
                    jQuery('.jid'+jid).css({background:'#CCEFBB', 'opacity':0, 'transition': 'background-color 3s linear'}).slideDown(250).animate(
                        {opacity: 1},
                        {queue: false, duration: 250,
                        complete:function() {
                            jQuery('.jid'+jid).css({'background' : ''}).removeClass('hide');
                        }
                    });
                } else {
                    jQuery('.jid'+jid).slideUp(function() {
                        jQuery('.jid'+jid).addClass('hide');
                    });
                }
            });
        };
        this.readyHandler = function() {self.updateTypeOptions();};
        this.init = function() {
            jQuery(document).ready(this.readyHandler);
        };
        this.init();
    }
    var JiTypeChangerInstance = function(container, options)
    {
        var self = this;
        // Set Default Options
        this.setDefaultOptions = function() {
            this.selector = container;
            this.types = null;
            this.currenttype = null;
            this.lasttype = null;
        };
        this.setDefaultOptions();

        // Set User Options
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }
        this.getChanger = function() {
            if(this.getChangerHandler!=null) {
                return this.getChangerHandler.call(undefined);
            } else {
                return;
            }
        }
        this.updateTypeOptions = function() {
            if(this.currenttype==null) this.currenttype = jQuery(this.selector).val();

            // Map elements to a temporary map so all types can be passed without duplicated hide modifiers
            var tmphidemap = {};
            jQuery.each(this.types, function(index, type) {
                // Assume elements to be hidden while processing
                // Hide from selected types
                jQuery('.hide-'+type).addClass('hide').each(function(i, e) {
                    var jid = self.getJID(e);
                    if(self.currenttype!=type) {
                        //tmphidemap[jid] = -1;
                        self.setHideCount(jid, -1);
                    } else {
                        //tmphidemap[jid] = 1;
                        self.setHideCount(jid, 1);
                    }
                });
                // Show only on the current type
                jQuery('.'+type+'-only').addClass('hide').each(function(i, e) {
                    var jid = self.getJID(e);
                    if(self.currenttype==type) {
                        tmphidemap[jid] = -1;
                    } else {
                        tmphidemap[jid] = 1;
                    }
                });
            });
            // Apply temporary hide map and respect other type changer instances
            jQuery.each(tmphidemap, function(jid, increment) {
                self.setHideCount(jid, increment);
            });
            this.lasttype = this.currenttype;
        };
        this.getJID = function(e) {
            var jid = null;
            var allclasses = jQuery(e).attr('class');
            if(allclasses!=null) {
                var classparts = allclasses.split(' ');
                jQuery.each(classparts, function(index, classname) {
                    if(classname.indexOf('jid')!=-1) {
                        jid = parseInt(classname.replace('jid', ''));
                    }
                });
            }
            if(jid==null) {
                this.getChanger().jidcount++;
                jQuery(e).addClass('jid'+this.getChanger().jidcount);
                jid = this.getChanger().jidcount;
            }
            return jid;
        };
        this.getHideCount = function(jid) {
            var hidecount = 0;
            if(this.getChanger().jihidemap[jid]!=null) {
                hidecount = parseInt(this.getChanger().jihidemap[jid]);
            } else {
                this.getChanger().jihidemap[jid] = hidecount;
            }
            return hidecount;
        };
        this.setHideCount = function(jid, i) {
            var hidecount = self.getHideCount(jid);
            hidecount+=i;
            if(hidecount<=-1) {
                self.getChanger().jihidemap[jid] = -1;
            } else {
                self.getChanger().jihidemap[jid] = hidecount;
            }
        };
        this.init = function() {
            jQuery(this.selector).on('change', function(e) {
                var sender = e.target != null ? e.target : e.srcElement;
                self.currenttype = jQuery(sender).val();
                self.getChanger().updateTypeOptions();
            });
            this.currenttype = jQuery(this.selector).val();
        };
        this.init();
    };
    jQuery.fn.jitypechanger = function(options) {
        var mainchanger;
        var mainelement = jQuery(window);
        if(mainelement.data('jitypechanger')) {
            mainchanger = mainelement.data('jitypechanger');
        } else {
            mainchanger = new JiTypeChanger(window, null);
            jQuery(mainelement).data('jitypechanger', mainchanger);
        }
        var element = jQuery(this);
        if(element.data('jitypechanger')) return element.data('jitypechanger');
        var jitypechanger = mainchanger.createInstance(this, options);
        return jitypechanger;
    };
})(jQuery);