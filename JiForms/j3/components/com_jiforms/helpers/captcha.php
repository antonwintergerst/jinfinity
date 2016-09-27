<?php
/**
 * @version     $Id: jicaptcha.php 040 2014-11-05 11:47:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiFormsCaptchaHandler
{
    private $functions = array('html', 'image', 'code', 'check');
    private $vars = array();

    function __set($name,$data)
    {
        if(is_callable($data))
            $this->functions[$name] = $data;
        else
            $this->vars[$name] = $data;
    }

    function __get($name)
    {
        if(isset($this->vars[$name]))
            return $this->vars[$name];
    }

    function __call($method,$args)
    {
        if(isset($this->functions[$method])) {
            call_user_func_array($this->functions[$method],$args);
        } else {
        }
    }
    function html() {
        $captcha = new JiFormsCaptcha();
        $result = $captcha->getHtml();
        return $result;
    }
    function image() {
        $captcha = new JiFormsCaptcha();
        $result = $captcha->getCaptcha();
        return $result;
    }
    function code() {
        $captcha = new JiFormsCaptcha();
        $result = $captcha->getCode();
        return $result;
    }
    function check($input) {
        $captcha = new JiFormsCaptcha();
        $result = $captcha->checkCode($input);
        return $result;
    }
}
class JiFormsCaptcha {
    /**
     * @param int $characters
     * @return string
     */
    public function getCode($characters=6) {
        /* list all possible characters, similar looking characters and vowels have been removed */
        $possible = '23456789bcdfghjkmnpqrstvwxyz';
        $code = '';
        $i = 0;
        while ($i < $characters) {
            $code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
            $i++;
        }
        $app = JFactory::getApplication();
        $app->setUserState('com_jiforms.form.captcha', $code);
        return $code;
    }

    /**
     * @param string $width
     * @param string $height
     * @param string $characters
     * @return bool
     */
    public function getCaptcha($width='120', $height='40', $characters='6') {
        $code = $this->getCode($characters);
        // font size will be 75% of the image height

        $font_size = $height * 0.75;
        $image = @imagecreate($width, $height) or die('Cannot initialize new GD image stream');

        // set the colours
        $background_color = imagecolorallocate($image, 255, 255, 255);
        $text_color = imagecolorallocate($image, 20, 40, 100);
        $noise_color = imagecolorallocate($image, 100, 120, 180);

        // generate random dots in background
        for( $i=0; $i<($width*$height)/3; $i++ ) {
            imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
        }

        // generate random lines in background
        for( $i=0; $i<($width*$height)/150; $i++ ) {
            imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
        }

        if(function_exists('imagettfbbox')) {
            // create textbox and add text
            $fontfile = JPATH_SITE.DS.'media'.DS.'jiforms'.DS.'fonts'.DS.'monofont.ttf';
            $textbox = imagettfbbox($font_size, 0, $fontfile, $code) or die('Error in imagettfbbox function');
            $x = ($width - $textbox[4])/2;
            $y = ($height - $textbox[5])/2;
            imagettftext($image, $font_size, 0, $x, $y, $text_color, $fontfile, $code) or die('Error in imagettftext function');
        } else {
            die('TTF Freetype extension not enabled');
        }

        // set headers
        header('Content-Type: image/jpeg');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Wed, 1 Jun 1998 00:00:00 GMT");

        // output captcha image to browser
        imagejpeg($image);
        imagedestroy($image);

        return true;
    }

    public function getHtml() {
        $app = JFactory::getApplication();
        $document = $app->getDocument();
        $document->addScript('media/jiforms/js/jquery.jicaptcha.js');

        $html = '<script type="text/javascript">
            if(typeof jQuery!=undefined) {
                jQuery(document).ready(function() {
                    jQuery(\'.jicaptcha\').jicaptcha({
                        \'captchaurl\':\'index.php?option=com_jiforms&task=captcha.image&t=\' + new Date().getTime(),
                        \'recaptchaurl\':\'index.php?option=com_jiforms&task=captcha.code&t=\' + new Date().getTime()
                    });
                });
            }
        </script>
        <div class="jifield textfield captcha">
        <label for="jicaptcha">Please enter the verification code in the image below.</label>
        <div class="jicaptcha fieldinner"><div class="outerimage"></div><div class="outerinput"><input class="inputbox required validate captcha" name="jicaptcha" id="jicaptcha" type="text" /></div></div>
        </div>';
        return $html;
    }

    /**
     * @param $input
     * @return bool
     */
    public function checkCode($input) {
        $app = JFactory::getApplication();
        $code = $app->getUserState('com_jiforms.form.captcha', 'u97sa2');
        $result = ($input==$code);
        return $result;
    }
}