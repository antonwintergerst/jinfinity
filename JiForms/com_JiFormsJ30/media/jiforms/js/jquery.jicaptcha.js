/*
 * @version     $Id: jquery.jicaptcha.js 061 2014-11-05 10:21:00Z Anton Wintergerst $
 * @package     JiCaptcha for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiCaptcha = function(container, options)
    {
        var self = this;

        this.setDefaultOptions = function() {
            this.captchaurl = '';
            this.recaptchaurl = '';
        };
        this.setDefaultOptions();

        // User Options
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }

        this.setPrivateOptions = function() {
            this.container = container;
        };
        this.setPrivateOptions();

        // class functions
        this.updateLayout = function(html) {
            jQuery(this.container).find('.jicaptchaimage').remove();
            jQuery(this.container).find('.outerimage').append(html);
            jQuery(this.container).find('.captcha').attr('label', 'Verification code ');
        }
        this.init = function() {
            var img = jQuery(document.createElement('img')).attr({
                'src':this.captchaurl,
                'class':'jicaptchaimage'
            }).load(function() {
                self.updateLayout(img);
            }).error(function() {
                jQuery.ajax({
                    url:self.recaptchaurl
                }).done(function(response) {
                    var html = jQuery(document.createElement('span')).attr({
                        'class':'jicaptchaimage recaptcha'
                    }).html(response);
                    self.updateLayout(html);
                }).error(function(){
                    var html = jQuery(document.createElement('span')).attr({
                        'class':'jicaptchaimage recaptcha'
                    }).html('u97sa2');
                    self.updateLayout(html);
                });
            });
        };
        this.init();
    };
    jQuery.fn.jicaptcha = function(options) {
        var element = jQuery(this);
        if(element.data('jicaptcha')) return element.data('jicaptcha');
        var jicaptcha = new JiCaptcha(this, options);
        element.data('jicaptcha', jicaptcha);
        return jicaptcha;
    };
})(jQuery);