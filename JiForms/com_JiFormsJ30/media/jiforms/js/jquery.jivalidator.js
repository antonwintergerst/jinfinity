/*
 * @version     $Id: jquery.jivalidator.css 019 2014-11-19 11:32:00Z Anton Wintergerst $
 * @package     JiValidator for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiValidator = function(container, options)
    {
        var self = this;
        // Set Default Options
        this.setDefaultOptions = function() {
            this.form = null;
            this.inputs = null;
            this.url = '';
        };
        this.setDefaultOptions();

        // Set User Options
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }
        // Set Private Options
        this.setPrivateOptions = function() {
            if(this.validateurl==null) this.validateurl = this.url+'&task=validator.validate';
            this.isClientValidated = false;
            this.isServerValidated = false;
            this.validform = 0;
            this.submitted = false;
        };
        this.setPrivateOptions();

        // Actions
        this.willSubmit = function(e) {
            // prevent duplicate submissions
            if(this.submitted===true) e.preventDefault();

            // Check if form is valid
            if(this.validform==0) {
                // Prevent Default Actions (don't submit form yet)
                e.preventDefault();
                e.stopPropagation();
                // Validate Form
                this.validate(true, true);
            } else {
                // Else form will be submitted

                // prevent duplicate submissions
                this.submitted = true;
            }

        };
        this.validate = function(client, server) {
            if(client==null) client = false;
            if(server==null) server = false;
            // Set Client Validator Queue
            this.validatorQueue = jQuery(this.form).find('input.validate,textarea.validate,select.validate').not(':disabled');
            // Perform Client Validation
            this.isClientValidated = (client==true)? this.clientValidation() : true;
            if(this.isClientValidated==true) {
                // Perform Server Validation
                this.isServerValidated = (server==true)? this.serverValidation(this.validatorQueue) : false;
            }
        };
        this.clientValidation = function() {
            // Clear Variables
            var totalerrors = 0;
            var msgs = {};
            // Loop Through Inputs
            jQuery.each(this.validatorQueue, function(index, input) {
                var errors = 0;
                // Check if element has the validate class
                if(jQuery(input).hasClass('validate')) {
                    var response = {};
                    response.id = jQuery(input).attr('id');
                    response.valid = true;
                    if(jQuery(input).attr('label')!=null) {
                        response.label = jQuery(input).attr('label');
                    } else {
                        var label = jQuery('label[for="'+response.id+'"]');
                        if(jQuery(label).length==0) {
                            response.label = response.id;
                        } else {
                            var labeltext = jQuery('<div>'+jQuery(label).html()+'</div>');
                            jQuery(labeltext).find("span.required").remove();
                            response.label = jQuery(labeltext).html();
                        }
                    }
                    var value = jQuery(input).val();
                    if(value==null) value = '';
                    value = value.replace(/ /g, '');
                    if(jQuery(input).hasClass('password')) {
                        // Validate as password
                        if(jQuery(input).val().length<6) {
                            response.msg = 'Password must be at least 6 characters long';
                            response.valid = false;
                            errors++;
                        }
                    }
                    if(jQuery(input).hasClass('password2')) {
                        // Validate as confirmation password
                        if(jQuery(container).find('input.password').length!=0 && jQuery(input).val()!=jQuery(container).find('input.password').val()) {
                            response.msg = 'Passwords do not match';
                            response.valid = false;
                            errors++;
                        }
                    }
                    if(value.length>0) {
                        if(jQuery(input).hasClass('alpha')) {
                            // Validate as alphabet characters
                            if(!value.match(/^[a-z]+$/i)) {
                                response.msg = response.label+' contains invalid characters';
                                response.valid = false;
                                errors ++;
                            }
                        }
                        if(jQuery(input).hasClass('alphaplus')) {
                            // Validate as alphabet characters
                            if(value.match(/[\~\`\^\<\"\@\{\}\[\]\*\$%\?=>:\|;#]+/i)) {
                                response.msg = response.label+' contains invalid characters';
                                response.valid = false;
                                errors++;
                            }
                        }
                        if(jQuery(input).hasClass('alphanum')) {
                            // Validate as alphanumeric characters
                            if(!value.match(/^[a-z0-9]+$/i)) {
                                response.msg = response.label+' contains invalid characters';
                                response.valid = false;
                                errors++;
                            }
                        }
                        if(jQuery(input).hasClass('numeric')) {
                            // Validate as numeric characters
                            if(!value.match(/^[0-9]+$/i)) {
                                response.msg = response.label+' contains invalid characters';
                                response.valid = false;
                                errors++;
                            }
                        }
                        if(jQuery(input).hasClass('email')) {
                            // Validate as email address
                            if(!value.match(/.+\@.+\..+/)) {
                                response.msg = 'this doesn\'t look like a valid email';
                                response.valid = false;
                                errors++;
                            }
                        }
                    }
                    if(jQuery(input).hasClass('required')) {
                        // Validate as required
                        if(value.length==0) {
                            response.msg = response.label+' is required';
                            response.valid = false;
                            errors++;
                        }
                    }
                    if(errors==0) {
                        // Show valid message
                        response.msg = response.label+' looks good!';
                    }
                    var key = response.id;
                    msgs[key] = response;
                    totalerrors+= errors;
                }
            });
            this.updateElements(msgs);
            return (totalerrors==0)? true : false;
        }
        this.serverValidation = function(inputs) {
            // Clear Variables
            var i = 0;
            var objects = [];
            // Loop Through Inputs
            jQuery.each(inputs, function(index, input) {
                // Add to objects array for server validation
                var field = {
                    'id':jQuery(input).attr('id'),
                    'name':jQuery(input).attr('name'),
                    'class':jQuery(input).attr('class'),
                    'value':jQuery(input).val()
                };
                var label = jQuery('label[for="'+field.id+'"]');
                if(jQuery(label).length!=0) field.label = jQuery(label).html();
                objects[i] = field;
                i++;
            });
            // Check if there are any objects for validating
            if(objects!=null) {
                // Convert 'objects' array to JSON object
                var jsonobject = JSON.stringify(objects);
                // Perform JSON request
                jQuery.ajax({
                    url:this.validateurl, type:'post', dataType:'json', data:{'objects':jsonobject}
                }).done(function(response) {
                        self.didValidateServer(response);
                    });
            }
        };
        this.didValidateServer = function(response) {
            if(response.valid == true) {
                // Set form valid state
                self.validform = 1;
                // Submit Form
                jQuery(self.form).trigger('submit');
            } else {
                self.updateElements(response.msgs);
                // Set form valid state
                self.validform = 0;
            }
        };
        this.updateElements = function(msgs) {
            // Loop through msgs
            jQuery.each(msgs, function(i, msg) {
                // Set class
                var classname;
                if(msg.valid==false) {
                    classname = 'validityhint failmsg';
                    jQuery('#'+msg.id).closest('.jifield').removeClass('isvalid');
                    jQuery('#'+msg.id).closest('.jifield').addClass('haserrors');
                } else {
                    classname = 'validityhint successmsg';
                    jQuery('#'+msg.id).closest('.jifield').removeClass('haserrors');
                    jQuery('#'+msg.id).closest('.jifield').addClass('isvalid');
                }
                // Check if error element already exists
                var existing = jQuery('#'+msg.id+'-validityhint');
                if(jQuery(existing).length==0) {
                    // Create error element
                    var element = jQuery(document.createElement('span')).attr({
                        'id':msg.id+'-validityhint',
                        'class':classname
                    }).html(msg.msg);
                    jQuery('#'+msg.id).parent().append(element);
                } else {
                    // Update error element
                    jQuery(existing).attr('class', classname).html(msg.msg);
                }
            });
        };

        // Setup Handlers
        this.submitHandler = function(e) {self.willSubmit(e)};
        this.validateHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            // Set Client Validator Queue
            self.validatorQueue = [];
            self.validatorQueue[0] = target;
            // Validate Input
            self.clientValidation();
        };
        this.delayedValidateHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            // Set Client Validator Queue
            self.validatorQueue = [];
            self.validatorQueue[0] = target;
            if(self.validatorTimer!=null) clearTimeout(self.validatorTimer);
            self.validatorTimer = setTimeout(function() {self.clientValidation()}, 2000);
        };
        this.validateInputOnKeypressHandler = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            // Set Client Validator Queue
            self.validatorQueue = [];
            self.validatorQueue[0] = target;
            // Instantly validate when deleting characters
            if(e.code==8 || e.code==46) {
                self.clientValidation();
            } else {
                // Clear Validator Timers
                if(self.validatorTimer!=null) clearTimeout(self.validatorTimer);
                // Start Validator Timer
                self.validatorTimer = setTimeout(function() {self.clientValidation()}, 2000);
            }
        };
        this.init = function() {
            if(jQuery(container).prop("tagName")=='FORM') {
                // Container is the form
                this.form = container;
                if(this.inputs==null) this.inputs = jQuery(this.form).find('input.validate,textarea.validate,select.validate').not(':disabled');
                // Override Submit Event
                jQuery(this.form).on('submit', this.submitHandler);
            } else {
                // Container is the input element
                if(this.inputs==null) this.inputs = [container];
            }
            // Loop Through Inputs
            jQuery.each(this.inputs, function(index, input) {
                jQuery(input).on({
                    focus: self.delayedValidateHandler,
                    keypress: self.validateInputOnKeypressHandler,
                    change: self.delayedValidateHandler,
                    blur: self.validateHandler
                });
            });
        };
        this.init();
    };
    jQuery.fn.jivalidator = function(options) {
        var element = jQuery(this);
        if(element.data('jivalidator')) return element.data('jivalidator');
        var jivalidator = new JiValidator(this, options);
        element.data('jivalidator', jivalidator);
        return jivalidator;
    };
})(jQuery);