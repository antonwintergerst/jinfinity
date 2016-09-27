<?php
/**
 * @version     $Id: object.php 016 2014-03-23 19:36:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiCustomFieldsObject {

    /**
     * Optionally bind data on object creation
     * @param mixed $data
     */
    public function __construct($data=null) {
        if($data!=null) {
            $this->data = new stdClass();
            $this->bindData($this->data, $data);
        }
    }

    /**
     * @param null $parent
     * @param $data
     */
    public function bindData($parent=null, $data) {
        if($parent==null) {
            if($this->data==null) $this->data = new stdClass();
            $parent = $this->data;
        }
        if(is_string($data) && (substr($data, 0, 1)=='{' || substr($data, 0, 1)=='[')) {
            $data = json_decode($data, true);
            if($data!=null) {
                foreach($data as $var=>$value) {
                    $parent->$var = $value;
                }
            }
        } elseif(is_array($data) || is_object($data)) {
            foreach($data as $var=>$value) {
                $parent->$var = $value;
            }
        }
    }

    /**
     * Method to set an object property
     * @param string $key
     * @param mixed $data
     */
    public function set($key, $data) {
        if(!isset($this->data)) $this->data = new stdClass();
        $this->data->{$key} = $data;
    }

    /**
     * Method to retrieve an object property
     * @param string $var
     * @param string|null $default
     * @return mixed
     */
    public function get($var, $default=null) {
        $result = (isset($this->data->$var))? $this->data->$var : $default;
        return $result;
    }

    /**
     * Method to convert the current data to an array
     * @return array
     */
    public function toArray() {
        $array = isset($this->data)? (array) $this->data : array();
        return $array;
    }

    /**
     * Method to convert the current data to a JSON string
     * @return string
     */
    public function toJSON() {
        $array = $this->toArray();
        $json = $this->raw_json_encode($array);
        return $json;
    }
    public function raw_json_encode($input) {
        return preg_replace_callback(
            '/\\\\u([0-9a-zA-Z]{4})/',
            function ($matches) {
                return mb_convert_encoding(pack('H*',$matches[1]),'UTF-8','UTF-16');
            },
            json_encode($input)
        );
    }
}