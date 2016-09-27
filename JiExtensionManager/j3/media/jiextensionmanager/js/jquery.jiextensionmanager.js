/**
 * @version     $Id: jquery.jiextensionmanager.js 066 2014-12-17 15:44:00Z Anton Wintergerst $
 * @package     JiExtensionManager for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiExtensionManager = function(container, options)
    {
        var self = this;
        // Set Default Options
        this.container = null;
        this.url = 'index.php?option=com_jiextensionmanager';
        this.dlkey = '';
        this.remoteurl = '';
        this.jversion = '*';
        this.strings = {
            'error_noupdates':'Error: No updates available',
            'error_noneselected':'Error: No extensions selected'
        };
        this.extensions = [];

        // Setup Options
        jQuery.each(options, function(index, value) {
            self[index] = value;
        });
        if(this.refreshclienturl==null) this.refreshclienturl = this.url+'&task=update';
        if(this.refreshserverurl==null) this.refreshserverurl = this.url+'&task=extensions.refreshserver';
        if(this.updateallurl==null) this.updateallurl = this.url+'&task=process.update';
        if(this.installurl==null) this.installurl = this.url;

        /* Manager Methods */
        this.controller = function(task, id, e) {
            if(!task) return;
            if(jQuery(e).hasClass('disabled')) return;
            switch (task) {
                case 'refresh':
                    this.refresh();
                    break;
                case 'updateall':
                case 'installselected':
                    this.installMultiple(task);
                    break;
                case 'install':
                case 'update':
                case 'reinstall':
                case 'downgrade':
                case 'uninstall':
                    this.install(task, id);
                    break;
            }
        };
        this.refresh = function() {
            jQuery('.loaded').addClass('hide');
            jQuery('.data').addClass('hide');
            jQuery('.progress').removeClass('hide');

            jQuery(container).find('td.ext_new').removeClass('disabled');

            this.refreshClientData();

        };
        this.refreshClientData = function() {
            jQuery.ajax({
                url:this.refreshclienturl
            }).done(function(response) {
                if(response!=null) {
                    var xml = jQuery.parseXML(response);
                    if(xml==null) xml = response;
                    var extensions = [];
                    var i = 0;
                    jQuery(xml).find('extension').each(function() {
                        el = {};
                        jQuery(this).children().each(function() {
                            var key = String(this.nodeName);
                            el[key] = String(jQuery(this).text()).trim();
                        });
                        if(typeof(el.alias) !== 'undefined') {
                            extensions[i] = el;
                            i++;
                        }
                    });
                    self.updateExtensions(extensions);
                }
            });
        };
        this.updateExtensions = function(extensions) {
            var toolbar = jQuery('div#toolbar');
            jQuery.each(jQuery(extensions), function(index, extension) {
                var row = jQuery('.jiext'+extension.alias);
                if(jQuery(row).length==1) {
                    if(typeof(extension.version)!=='undefined' && extension.version) {
                        jQuery(row).find('span.current_version').text(extension.version);
                        if(extension.missing) {
                            jQuery(row).find('.missing span').attr('data-content', extension.missing);
                            jQuery(row).find('.has_missing').removeClass('hide');
                        }
                        jQuery(row).find('.installed').removeClass('hide');
                    } else {
                        jQuery(row).find('span.current_version').text('');
                        jQuery(row).addClass('not_installed');
                        jQuery(row).removeClass('installed');
                        jQuery(row).find('.not_installed').removeClass('hide');
                    }
                    if(typeof(extension.pro)!=='undefined' && extension.pro!='') {
                        if(extension.pro==1) {
                            // PRO
                            jQuery(row).find('.haspro').removeClass('hide');
                            jQuery(row).find('.hasfree').addClass('hide');
                        } else if(extension.pro==0) {
                            // FREE
                            jQuery(row).find('.haspro').addClass('hide');
                            jQuery(row).find('.hasfree').removeClass('hide');
                        }
                    }
                }
            });
            jQuery('.ext_types').find('.progress').addClass('hide');
            jQuery('.ext_types').find('.loaded').removeClass('hide');
            jQuery('.ext_types').find('.no_external').removeClass('hide');
            this.refreshServerData();
        };
        this.refreshServerData = function() {
            jQuery.ajax({
                url:this.refreshserverurl,
                type:'post',
                data:{
                    'url':this.remoteurl,
                    'dlkey':this.dlkey,
                    'jversion':this.jversion
                }
            }).done(function(response) {
                if(response!=null) {
                    var xml = jQuery.parseXML(response);
                    if(xml==null) xml = response;
                    var extensions = [];
                    var i = 0;
                    jQuery('.jinotices').html(jQuery(xml).find('notice').text());
                    jQuery(xml).find('extension').each(function() {
                        el = {};
                        jQuery(this).children().each(function() {
                            var key = String(this.nodeName);
                            el[key] = String(jQuery(this).text()).trim();
                        });
                        if(typeof(el.alias) !== 'undefined') {
                            extensions[i] = el;
                            i++;
                        }
                    });
                    self.rebuildManager(extensions);
                }
            });
        };
        this.rebuildManager = function(extensions) {
            var toolbar = jQuery('div#toolbar');
            jQuery.each(jQuery(extensions), function(index, extension) {
                var row = jQuery('.jiext'+extension.alias);
                if(extension.alias!='' && jQuery(row).length==1) {
                    var version = String(jQuery(row).find('.current_version').first().text()).trim();

                    jQuery(row).find('.installed').removeClass('hide');
                    if(typeof(extension.version)!=='undefined' && extension.version!='') {
                        jQuery(row).find('.new_version').text(extension.version);
                        jQuery(row).find('.changelog').removeClass('hide');
                        jQuery(row).find('.changelogsummary').attr('data-content', extension.changelog);

                        if(!version || version == '0') {
                            jQuery(toolbar).addClass('has_install');
                            jQuery(row).addClass('selectable not_installed');
                            jQuery(row).removeClass('installed');
                            jQuery(row).find('.install').removeClass('hide');
                            jQuery(row).find('.not_installed').removeClass('hide');
                            jQuery(row).find('.installed').addClass('hide');

                        } else if(jQuery(row).hasClass('has_missing')) {
                            jQuery(toolbar).addClass('has_install');
                            jQuery(row).addClass('selectable');
                            jQuery(row).find('.install').removeClass('hide');
                        } else {
                            var compare = self.compareVersions(version, extension.version);
                            if(compare == '<') {
                                jQuery(toolbar).addClass('has_update');
                                jQuery(row).addClass('selectable update');
                                jQuery(row).find('.update').removeClass('hide');
                            } else if (compare == '>') {
                                jQuery(row).find('.downgrade').removeClass('hide');
                                jQuery(row).find('td.ext_new').addClass('disabled');
                            } else {
                                jQuery(row).removeClass('selectable not_installed update');
                                jQuery(row).find('.changelog, .changelog > span').addClass('disabled');
                                jQuery(row).find('.downgrade').addClass('hide');
                                jQuery(row).find('.uptodate').removeClass('hide');
                                jQuery(row).find('.reinstall').removeClass('hide');
                            }
                        }
                    }

                    jQuery(row).find('.downloadurl').val(extension.downloadurl);
                    // User must be a subscriber

                    jQuery(row).find('.getproupgrade').removeClass('hide');
                    jQuery(row).find('.getfree').removeClass('hide');
                    if(extension.pro==1) {
                        jQuery(row).find('.getproupgrade').addClass('hide');
                        jQuery(row).find('.getfree').addClass('hide');
                        jQuery(row).find('.getpro').removeClass('hide disabled');
                        jQuery(row).find('.renewsubscription').removeClass('hide');
                        jQuery(row).find('.upgradesubscription').addClass('hide');
                    } else if(extension.pro==0) {
                        jQuery(row).find('.upgradesubscription').removeClass('hide');
                        jQuery(row).find('.renewsubscription').addClass('hide');
                        jQuery(row).find('.getfree').removeClass('disabled');
                    }
                    if(extension.haspro==1 || extension.hasfree==1) {
                        jQuery(row).find('.install').removeClass('disabled');
                        jQuery(row).find('.update').removeClass('disabled');
                        jQuery(row).find('.reinstall').removeClass('disabled');
                        jQuery(row).find('.downgrade').removeClass('disabled');
                        if(extension.haspro==0) {
                            jQuery(row).find('.hasfree').addClass('hide');
                            jQuery(row).find('.getproupgrade').addClass('hide');
                            jQuery(row).find('.getfree').addClass('hide');
                            jQuery(row).find('.getpro').addClass('hide');
                            jQuery(row).find('.upgradesubscription').addClass('hide');
                            jQuery(row).find('.renewsubscription').addClass('hide');
                        }
                    } else {
                        jQuery(row).find('.getproupgrade').addClass('hide');
                        jQuery(row).find('.getfree').addClass('hide');
                        jQuery(row).find('.getpro').addClass('hide');
                        jQuery(row).find('.upgradesubscription').addClass('hide');
                        jQuery(row).find('.renewsubscription').addClass('hide');

                        jQuery(row).find('.install').addClass('disabled');
                        jQuery(row).find('.update').addClass('disabled');
                        jQuery(row).find('.reinstall').addClass('disabled');
                        jQuery(row).find('.downgrade').addClass('disabled');
                        jQuery(row).removeClass('selectable');
                    }
                }
                jQuery('.progress').addClass('hide');
                jQuery('.loaded').removeClass('hide');
            });
            this.updateCheckboxes();
        };
        this.updateCheckboxes = function() {
            // hide select boxes
            jQuery('.select').addClass('hide');

            // reset hidden checkboxes
            jQuery(container).find('table tr.not_installed').each(function(i, row) {
                if(jQuery(row).hasClass('xselectable')) {
                    jQuery(row).addClass('selectable').removeClass('xselectable');
                }
            });

            // make hidden rows unselectable
            jQuery(container).find('table.hide_not_installed tr.not_installed').each(function(i, row) {
                if(jQuery(row).hasClass('selectable')) {
                    jQuery(row).addClass('xselectable').removeClass('selectable');
                }
            });

            // show select boxes of selectable rows
            jQuery('.selectable').find('.select').removeClass('hide');
        };
        this.installMultiple = function(task) {
            var ids = [];
            var urls = [];

            switch (task) {
                case 'updateall':
                    var type = 'update';
                    var errormsg = this.strings.error_noupdates;
                    break;
                default:
                    var type = 'install';
                    var errormsg = this.strings.error_noneselected;
                    break;
            }

            jQuery(container).find('tr.selectable').each(function(index, row) {
                var el = jQuery(row).find('td.ext_checkbox input');
                var id = jQuery(el).val();
                if(id) {
                    var pass = 0;
                    switch (task) {
                        case 'updateall':
                            pass = jQuery(row).hasClass('update');
                            break;
                        default:
                            pass = jQuery(el).is(':checked');
                            break;
                    }

                    if (pass) {
                        var url = jQuery('#url_' + id).val();
                        ids.push(id);
                        urls.push(url);
                    }
                }
            });

            if (!ids.length) {
                alert(errormsg);
            } else {
                this.openModal(type, ids, urls);
            }
        };
        this.install = function(task, id) {
            var url = jQuery('#url_' + id).val();
            this.openModal(task, [id], [url]);
        };
        this.openModal = function(task, ids, urls) {
            a = [];
            for (var i = 0; i < ids.length; i++) {
                a.push('ids[]=' + escape(ids[i]))
            }

            width = 480;
            height = 58 + (a.length * 37);
            min = 140;
            max = window.getSize().y - 60;
            if (height > max) {
                height = max;
                width += 16;
            }
            if (height < min) {
                height = min;
            }

            a = a.join('&');

            b = [];
            for (var j = 0; j < urls.length; j++) {
                url = urls[j].replace('http://', '');
                b.push('urls[]=' + escape(url));
            }
            b = b.join('&');

            url = this.url+'&view=process&tmpl=component&task=' + task + '&' + a + '&' + b;
            SqueezeBox.open(url, {handler: 'iframe', size: {x: width, y: height}, classWindow: 'jiextensionmanager_modal'});
        };
        this.compareVersions = function(num1, num2) {
            num1 = num1.split('.');
            num2 = num2.split('.');

            var let1 = '';
            var let2 = '';

            var max = Math.max(num1.length, num2.length);
            for (var i = 0; i < max; i++) {
                if (typeof(num1[i]) == 'undefined') {
                    num1[i] = '0';
                }
                if (typeof(num2[i]) == 'undefined') {
                    num2[i] = '0';
                }

                let1 = num1[i].replace(/^[0-9]*(.*)/, '$1');
                num1[i] = num1[i].toInt();
                let2 = num2[i].replace(/^[0-9]*(.*)/, '$1');
                num2[i] = num2[i].toInt();

                if (num1[i] < num2[i]) {
                    return '<';
                } else if (num1[i] > num2[i]) {
                    return '>';
                }
            }

            // numbers are same, so compare trailing letters
            if (let2 && (!let1 || let1 > let2)) {

                return '>';
            } else if (let1 && (!let2 || let1 < let2 )) {
                return '<';
            } else {
                return '=';
            }
        };
        /* Processing Methods */
        this.failedids = [];
        this.process = function(task) {
            jQuery(container).find('.title').addClass('hide');
            jQuery(container).find('.titles').find('.processing').removeClass('hide');

            this.task = task;
            this.isinstalltask = (task != 'uninstall');

            var sb = window.parent.SqueezeBox;
            sb.overlay['removeEvent']('click', sb.bound.close);
            if (this.ids[0] == 'jiextensionmanager') {
                this.isextensionmanager = 1;
                sb.setOptions({onClose: function() { window.parent.location.href = window.parent.location; }});
            } else {
                sb.setOptions({onClose: function() { window.parent.jimanager.refresh(1); }});
            }

            this.processNextStep(0);
        };
        this.processNextStep = function(step) {
            var id = this.ids[step];

            if (!id) {
                var sb = window.parent.SqueezeBox;
                jQuery('.title').addClass('hide');
                if(jQuery(this.failedids).length) {
                    jQuery('.titles').find('.failed').removeClass('hide');
                    this.ids = this.failedids;
                    this.failedids = [];
                } else {
                    jQuery('.processlist').addClass('hide');
                    jQuery('.titles').find('.done').removeClass('hide');
                    if(!this.isextensionmanager) {
                        window.parent.jimanager.refresh();
                        sb.removeEvents();
                    }
                }
                sb.overlay['addEvent']('click', sb.bound.close);
            } else {
                this.processInstall(step);
            }
        };
        this.processInstall = function(step) {
            var id = this.ids[step];
            var row = jQuery('tr#row_' + id);

            jQuery(row).find('.status').addClass('hide');
            jQuery('.processing_' + id).removeClass('hide');

            var url = 'index.php?option=com_jiextensionmanager&view=process&tmpl=component&id=' + id;
            if(this.isinstalltask) {
                url += '&action=install';
                ext_url = jQuery('#url_'+id).val()+'?&action='+this.task
                url += '&url=' + escape(ext_url);
            } else {
                url += '&action=uninstall';
            }
            jQuery.ajax({
                url:url,
                type:'post',
                data:{}
            }).done(function(response) {
                if(response!=null) {
                    self.processResult(response.trim(), step);
                } else {
                    self.processResult(0, step);
                }
            });
        };
        this.processResult = function(data, step) {
            var id = this.ids[step];
            var row = jQuery('tr#row_' + id);

            jQuery(row).find('.status').addClass('hide');
            if (!data || ( data !== '1' && data.indexOf('<div class="alert alert-success"') == -1 )) {
                this.failedids.push(id);
                jQuery('.failed_'+id).removeClass('hide');
            } else {
                jQuery('.success_'+id).removeClass('hide');
            }
            this.processNextStep(++step);
        };
    };
    jQuery.fn.jiextensionmanager = function(options) {
        var element = jQuery(this);
        if(element.data('jiextensionmanager')) return element.data('jiextensionmanager');
        var jiextensionmanager = new JiExtensionManager(this, options);
        element.data('jiextensionmanager', jiextensionmanager);
        return jiextensionmanager;
    };
})(jQuery);