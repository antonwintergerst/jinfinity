/*
 * @version     $Id: jquery.jifields.js 060 2014-12-19 11:37:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
(function(jQuery){
    var JiCustomFields = function(containers)
    {
        var self = this;
        
        if(!jQuery.isArray(containers)) {
            this.containers = [];
            this.containers[0] = containers;
        } else {
            this.containers = containers;
        }
        
        this.dragObject = {status:'initialized'};
        this.mousePos = {x:0, y:0};
        this.newfieldcount = 0;
        this.btn = '.jibtn';
        this.fields = [];
        
        // Class Functions
        this.willreload = function(e)
        {
            // safely destroy inputs
            jQuery.each(this.fields, function(index, jifield) {
                var JiField = jQuery(jifield.e).jifield(jifield, jifield.type);
                if(JiField!=null && JiField.hasOwnProperty('destroyInput')) JiField.destroyInput();
            });
        }
        this.reload = function(e)
        {
            jQuery.each(this.fields, function(index, jifield) {
                var JiField = jQuery(jifield.e).jifield(jifield, jifield.type);
                if(JiField!=null) JiField.prepareInput(true);
            });
            // add chosen select to new html
            if(jQuery().chosen) jQuery('.chzn-select').chosen();
        }
        this.newField = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            
            var id = 'new'+this.newfieldcount;
            var title = jQuery('#fieldtitle').val();
            var type = jQuery('#fieldtype').val();
            if(type=="") {
                alert('Please select a field type');
            } else if(title=="") {
                alert('Please enter a field title');
            } else {
                this.addField(id, title, type);
                // Increment new field
                this.newfieldcount++;
                jQuery('#fieldtitle').val('');
            }
        };
        /*
         * Inserts new field html into container and initializes field types
         */
        this.addField = function(id, title, type) {
            jQuery.ajax({url:'index.php?option=com_jicustomfields&task=fields.renderinput&format=ajax',
                type:'post',
                data:{
                    'type':type,
                    'title':title,
                    'id':id
                }
            }).done(function(response) {
                if(response!=null) {
                    jQuery('#fieldscontainer').append(response);
                    // add chosen select to new html
                    if(jQuery().chosen) jQuery('.chzn-select').chosen();

                    jQuery('.fieldscontainer').jitoggler({btn:'.jitogglerbtn', tab:'.jitogglertab'});
                    jQuery('.fieldscontainer').jisortable({btn:'.jisortbtn', tab:'.jifield'});
                    // Prepare common params
                    var JiField = jQuery('.customfields').jifield({'id':id, 'title':title, 'type':type}, 'commonparams');
                    if(JiField!=null) JiField.prepareInput();
                    // Prepare field type
                    var JiField = jQuery('.customfields').jifield({'id':id, 'title':title, 'type':type}, type);
                    if(JiField!=null) JiField.prepareInput();
                    // Field actions
                    jQuery('.jid'+id).find('.fieldactions .jiremovebtn').on('click', self.removeFieldHandler);
                    jQuery('.jid'+id).find('.fieldactions .jideletebtn').on('click', self.deleteFieldHandler);
                }
            });
        };
        this.removeField = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            var btn = jQuery(sender).closest(this.btn);

            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();

            if(!jQuery(btn).hasClass('deleting')) jQuery(btn).addClass('deleting');
            if(confirm("Are you sure you want to unassign this field from the parent category?")) {
                // Unassign field
                var fid = jQuery(btn).attr('rel').replace('jifields_', '');
                var catid = jQuery(btn).closest('.fieldscontainer').find('.jicatid').val();
                jQuery.ajax({url:'index.php?option=com_jicustomfields&task=fields.unassign&format=json',
                    type:'post',
                    data:{
                        'fid':fid,
                        'catid':catid
                    }
                }).done(function(response) {
                    // Remove field from interface
                    var row = jQuery(btn).closest('.jitable').closest('.jitrow');
                    jQuery(row).remove();
                });
            } else {
                jQuery(btn).removeClass('deleting');
            }
        }
        this.deleteField = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            var btn = jQuery(sender).closest(this.btn);

            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();

            if(!jQuery(btn).hasClass('deleting')) jQuery(btn).addClass('deleting');
            if(confirm("Are you sure you want to delete this field from all items?")) {
                // Delete field
                var fid = jQuery(btn).attr('rel').replace('jifields_', '');
                jQuery.ajax({url:'index.php?option=com_jicustomfields&task=fields.remove&format=json',
                    type:'post',
                    data:{
                        'fid':fid
                    }
                }).done(function(response) {
                    // Remove field from interface
                    var row = jQuery(btn).closest('.jitable').closest('.jitrow');
                    jQuery(row).remove();
                });
            } else {
                jQuery(btn).removeClass('deleting');
            }
        }
        this.getSize = function(e) {
            var width = Math.max(e.scrollWidth, e.offsetWidth, e.clientWidth);
            var height = Math.max(e.scrollHeight, e.offsetHeight, e.clientHeight);
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
        this.getElementsByClass = function (node, searchClass,tag) {
            if(tag==null) tag = '*';
            if(node==null) node = document;
            if(node instanceof jQuery) {
                node = node.get(0);
            }
            
            var classElements = [];
            if(node!=null) {
                var els = node.getElementsByTagName(tag);
                var elsLen = els.length;
                var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
                for(i = 0, j = 0; i < elsLen; i++) {
                    if (pattern.test(els[i].className) ) {
                        classElements[j] = els[i];
                        j++;
                    }
                }
            }
            return classElements;
        };
        this.getElementsByTag = function (node, tag) {
            if(node instanceof jQuery) {
                var i = node.length, arr=[];
                for (i;i--;) {
                    jQuery.merge(arr, node[i].getElementsByTagName(tag));
                }
                return jQuery(arr);
            } else {
                var elements = [];
                if(node!=null) {
                    elements = node.getElementsByTagName(tag);
                }
                return elements;
            }
        };
        
        // Setup Handlers
        this.willReloadHandler = function(e) {self.willreload(e);};
        this.reloadHandler = function(e) {self.reload(e);};
        this.newFieldHandler = function(e) {self.newField(e);};
        this.updateFieldHandler = function(e) {self.updateField(e);};
        this.updateNameHandler = function(e) {self.updateName(e);};
        this.changeFieldHandler = function(e) {self.changeField(e);};
        this.removeFieldHandler = function(e) {self.removeField(e);};
        this.deleteFieldHandler = function(e) {self.deleteField(e);};
        this.addChoiceHandler = function(e) {self.addChoice(e);};
        this.removeChoiceHandler = function(e) {self.removeChoice(e);};
        this.toggleParamsHandler = function(e) {self.toggleParams(e);};
        this.getMouseHandler = function(e) {self.getMouse(e);};
        this.dragFieldHandler = function(e) {self.dragField(e);};
        this.dropFieldHandler = function(e) {self.dropField(e);};
        this.nothingHandler = function(e) {
            if(this.dragObject.status!='dragging') {
            // Prevent Default Actions
            e.preventDefault();
            e.stopPropagation();
            return false;
            }
        };
        
        // Check if we're in IE (That awful relic)
        this.ie = document.all?true:false;
        if(this.ie) {
            // Listen for focus changes
            var inputs = jQuery('input');
            if(inputs!=null) {
                for (var i = 0; i < inputs.length; i++) {
                    jQuery(inputs[i]).on('focus', function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        jQuery(sender).addClass('focused');
                    });
                    jQuery(inputs[i]).on('blur', function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        jQuery(sender).removeClass('focused');
                    });
                }
            }
            var textareas = jQuery('textarea');
            if(textareas!=null) {
                for (var a = 0; a < textareas.length; a++) {
                    jQuery(textareas[a]).on('focus', function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        jQuery(sender).addClass('focused');
                    });
                    jQuery(textareas[a]).on('blur', function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        jQuery(sender).removeClass('focused');
                    });
                }
            }
        } else {
            // Listen for mouse changes
            //document.captureEvents(Event.MOUSEMOVE);
        }
        //jQuery(document).on('mousemove', this.getMouseHandler);
        
        // Scan Dom
        if(this.containers.length>0) {
            // Container exists - Loop through containers
            for(var c=0; c<this.containers.length; c++) {
                jQuery(this.containers[c]).on('willreload', this.willReloadHandler);
                jQuery(this.containers[c]).on('reload', this.reloadHandler);
                jQuery(this.containers[c]).find('.fieldactions .addfieldbtn').on('click', this.newFieldHandler);
                jQuery(this.containers[c]).find('.fieldactions .jiremovebtn').on('click', this.removeFieldHandler);
                jQuery(this.containers[c]).find('.fieldactions .jideletebtn').on('click', this.deleteFieldHandler);
            }
        }
    };
    jQuery.fn.jicustomfields = function(options) {
        var element = jQuery(this);
        if(element.data('jicustomfields')) return element.data('jicustomfields');
        var jicustomfields = new JiCustomFields(this, options);
        element.data('jicustomfields', jicustomfields);
        return jicustomfields;
    };
})(jQuery);
(function(jQuery){
    var JiField = function(container, data, type)
    {
        var self = this;
        // Set Default Data
        this.data = {
            'id':'new',
            'title':'Default',
            'type':'text'
        }
        // Setup Options
        jQuery.each(data, function(index, value) {
            self.data[index] = value;
        });
        
        // Class Functions
        this.prepareInput = function() {
            //console.log('preparing input');
            // prepare common params
            var JiField = jQuery('.customfields').jifield(this.data, 'commonparams');
            // prepare field
            if(JiField!=null) JiField.prepareInput();
        };
    };
    jQuery.fn.jifield = function(data, type) {
        var element = jQuery(this);
        // Create new JiField
        var jifield = new JiField(this, data, type);
        // Extend by field type
        if(jQuery(this)['jifield'+type]!=null) {
            var fieldtype = jQuery(this)['jifield'+type](data);
            jQuery.extend(jifield, fieldtype);
        }
        // Set and return class data
        element.data('jifield', jifield);
        return jifield;
    };
})(jQuery);
(function(jQuery){
    var JiFieldCommonParams = function(container, data)
    {
        var self = this;
        // Class Functions
        this.updateTitle = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            if(sender!=null) {
                var fieldid = sender.id;
                var newtitle = jQuery(sender).val();
                fieldid = 'jifields_'+fieldid.replace('fieldtitle', '')+'-lbl';
                var fieldlabel = jQuery(sender).closest('.jifield').find('#'+fieldid);
                jQuery(fieldlabel).html(newtitle);
            }
        };
        this.updateType = function(e) {
            var sender = e.target != null ? e.target : e.srcElement;
            if(sender!=null) {
                var fieldid = sender.id;
                var newtype = jQuery(sender).val();
                var typedesc = jQuery(sender).closest('.jifield').find('.typedesc');
                jQuery(typedesc).html(Joomla.JText._('JICUSTOMFIELDS_'+newtype+'_DESC'));
            }
        }
        // Setup Handlers
        this.changeTitleHandler = function(e) {self.updateTitle(e);};
        this.changeTypeHandler = function(e) {self.updateType(e);};
        this.prepareInput = function() {
            jQuery(container).find('.inputbox.fieldtitle').on({
                'change':this.changeTitleHandler,
                'keyup':this.changeTitleHandler
            });
            jQuery(container).find('select.typeselect').on('change', this.changeTypeHandler);
        }
    }
    jQuery.fn.jifieldcommonparams = function(data) {
        var element = jQuery(this);
        if(element.data('jifieldcommonparams')) return element.data('jifieldcommonparams');
        var jifieldcommonparams = new JiFieldCommonParams(this, data);
        element.data('jifieldcommonparams', jifieldcommonparams);
        return jifieldcommonparams;
    };
})(jQuery);