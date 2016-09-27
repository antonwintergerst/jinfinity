<?php
/**
 * @version     $Id: object.php 012 2013-10-22 13:56:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiFormsObject {

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
            if(!isset($this->data)) $this->data = new stdClass();
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

    public function setProperties($properties)
    {
        if (is_array($properties) || is_object($properties))
        {
            foreach ((array) $properties as $k => $v)
            {
                // Use the set function which might be overridden.
                $this->set($k, $v);
            }
            return true;
        }

        return false;
    }

    /**
     * Method to set an object property
     * @param string $key
     * @param mixed $data
     */
    public function set($key, $data) {
        if(!isset($this->data)) $this->data = new stdClass();
        $this->{$key} = $data;
        $this->data->{$key} = $data;
    }

    /**
     * Method to retrieve an object property
     * @param string $var
     * @param string|null $default
     * @return mixed
     */
    public function get($var, $default=null) {
        $result = (isset($this->data->$var) && $this->data->$var!=null)? $this->data->$var : $default;
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
        $json = json_encode($array);
        return $json;
    }
}