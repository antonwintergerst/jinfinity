<?php
/**
 * @version     $Id: validator.php 010 2013-08-29 14:50:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiFormsValidator
{
    public function validateField($value, $checks=null, $label='')
    {
        // Clear Return Object
        $return = new stdClass();

        // Clear Variables
        $msg = null;
        $errors = 0;
        ($label!='')? $labelString = $label.' ': $labelString = '';

        if(is_string($checks)) $checks = explode(' ', $checks);

        if(in_array('password', $checks)) {
            // Validate as password
            if(strlen($value) < 6) {
                $msg = $labelString.'length is too short';
                $errors ++;
            }
        }
        if(in_array('alpha', $checks)) {
            // Validate as alphabet characters
            if(strlen($value)>0 && ctype_alpha(str_replace(' ', '', $value))==false) {
                $msg = $labelString.'contains invalid characters';
                $errors ++;
            }
        }
        if(in_array('alphaplus', $checks)) {
            // Validate as alphabet plus some special characters
            if(strlen($value)>0 && preg_match("/[\^<,\"@\/\{\}\(\)\*\$%\?=>:\|;#]+/i", $value)) {
                $msg = $labelString.'contains invalid characters';
                $errors ++;
            }
        }
        if(in_array('alphanum', $checks)) {
            // Validate as alphanumeric characters
            if(strlen($value)>0 && ctype_alpha(str_replace(' ', '', $value))==false) {
                $msg = $labelString.'contains invalid characters';
                $errors ++;
            }
        }
        if(in_array('numeric', $checks)) {
            // Validate as numeric characters
            if(strlen($value)>0 && ctype_digit(str_replace(' ', '', $value))==false) {
                $msg = $labelString.'contains invalid characters';
                $errors ++;
            }
        }
        if(in_array('email', $checks)) {
            // Validate as email address
            $emailvalidity = $this->validateEmail($value);
            if($emailvalidity==false) {
                $msg = '"'.$value.'" is not a valid email';
                $errors ++;
            }
        }
        if(in_array('filename', $checks)) {
            // Validate as filename characters
            if(strlen($value)>0 && preg_match("/[\^<,\"@\/\{\}\(\)\*\$%\?=>:\|;#]+/i", $value)) {
                $msg = $labelString.'upload failed. Try again';
                $errors ++;
            }
        }
        if(in_array('required', $checks)) {
            // Validate as required
            if(strlen(str_replace(' ', '', $value))==0) {
                $msg = $labelString.'is required';
                $errors ++;
            }
        }
        if(!isset($msg) && $errors==0) {
            // Show valid message
            $msg = $labelString.'looks good!';
        }
        $return->valid = ($errors==0);
        $return->msg = $msg;

        return $return;
    }
    private function validateEmail($email, $skipDNS=false) {
        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex) {
            $isValid = false;
        } else {
            $domain = substr($email, $atIndex+1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64) {
                // local part length exceeded
                $isValid = false;
            } elseif ($domainLen < 1 || $domainLen > 255) {
                // domain part length exceeded
                $isValid = false;
            } elseif ($local[0] == '.' || $local[$localLen-1] == '.') {
                // local part starts or ends with '.'
                $isValid = false;
            } elseif (preg_match('/\\.\\./', $local)) {
                // local part has two consecutive dots
                $isValid = false;
            } elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                // character not valid in domain part
                $isValid = false;
            } elseif (preg_match('/\\.\\./', $domain)) {
                // domain part has two consecutive dots
                $isValid = false;
            } elseif (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
                // character not valid in local part unless 
                // local part is quoted
                if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
                    $isValid = false;
                }
            }
            if(!$skipDNS) {
                if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
                    // domain not found in DNS
                    $isValid = false;
                }
            }
        }
        return $isValid;
    }
}
?>