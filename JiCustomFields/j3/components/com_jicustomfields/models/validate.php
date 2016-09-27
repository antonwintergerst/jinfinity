<?php 
/*
 * @version     $Id: validate.php 010 2013-01-29 23:21:00Z Anton Wintergerst $
 * @package     Jinfinity Framework for Joomla 3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
class JinfinityModelValidate extends JModelLegacy
{
    function validate() {
        // Clear Variables
        $msgs = array();
        $errors = 0;
        // Get Objects Array
        $objects = json_decode(JRequest::getVar('objects'), true);
        // Loop through objects and validate
        foreach($objects as $object) {
            $msgs[$object['id']]['id'] = $object['id'];
            $msgs[$object['id']]['error'] = false;
            if(strpos($object['class'], 'password')!==false) {
                // Validate as password
                if(strlen($object['value']) < 6) {
                    $msgs[$object['id']]['msg'] = $object['id'].' length is too short';
                    $msgs[$object['id']]['error'] = true;
                    $errors ++;
                }
            }
            if(strpos($object['class'], 'alphabet')!==false) {
                // Validate as alphabet characters
                if(ctype_alpha($object['value'])==false) {
                    $msgs[$object['id']]['msg'] = $object['id'].' contains invalid characters';
                    $msgs[$object['id']]['error'] = true;
                    $errors ++;
                }
            }
            if(strpos($object['class'], 'alphanum')!==false) {
                // Validate as alphanumeric characters
                if(ctype_alpha($object['value'])==false) {
                    $msgs[$object['id']]['msg'] = $object['id'].' contains invalid characters';
                    $msgs[$object['id']]['error'] = true;
                    $errors ++;
                }
            }
            if(strpos($object['class'], 'numeric')!==false) {
                // Validate as numeric characters
                if(ctype_digit($object['value'])==false) {
                    $msgs[$object['id']]['msg'] = $object['id'].' contains invalid characters';
                    $msgs[$object['id']]['error'] = true;
                    $errors ++;
                }
            }
            if(strpos($object['class'], 'email')!==false) {
                // Validate as email address
                //if(strpos($object['value'], '@')===false || strpos($object['value'], '.')===false) {
                $emailvalidity = $this->validEmail($object['value']);
                if($emailvalidity == false) {
                    $msgs[$object['id']]['msg'] = $object['value'].' is not a valid email';
                    $msgs[$object['id']]['error'] = true;
                    $errors ++;
                }
            }
            if(strpos($object['class'], 'uid')!==false) {
                // Get Database Object
                $db =& JFactory::getDBO();
                // Query for user
                $query = 'SELECT id FROM #__users WHERE email="'.$object['value'].'"'; 
                $db->setQuery($query);
                $exists = $db->loadResult();
                // Validate as email address
                if($exists!=null) {
                    $msgs[$object['id']]['msg'] = 'This email is already registered';
                    $msgs[$object['id']]['error'] = true;
                    $errors ++;
                }
            }
            if(strpos($object['class'], 'required')!==false) {
                // Validate as required
                if(strlen($object['value'])==0) {
                    $msgs[$object['id']]['msg'] = $object['id'].' is required';
                    $msgs[$object['id']]['error'] = true;
                    $errors ++;
                }
            }
            if($msgs[$object['id']]['error']==false) {
                // Show valid message
                $msgs[$object['id']]['msg'] = $object['id'].' is valid';
            }
        }
        $valid = ($errors==0)? true : false;
        
        return array('msgs'=>$msgs, 'valid'=>$valid);
    }
    function validEmail($email, $skipDNS = false) {
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