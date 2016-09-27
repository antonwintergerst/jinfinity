<?php
/**
 * @version     $Id: field.php 088 2013-10-25 12:24:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiMigratorFieldHelper {
    /*
     * Returns a field instance
     */
    function loadType($data, $type=null) {
        if($data instanceof JiMigratorField) {
            // Load field with data from JiMigratorField
            if(!isset($type)) $type = $data->get('type');
            $JiField = $this->loadField($type);
            $JiField->setName($data->get('name'));
            $JiField->setTitle($data->get('title'));
            $JiField->setId($data->get('id'));
            $JiField->setAssignmode($data->get('assignmode'));
            $JiField->setAssignment($data->get('assignment'));
            $JiField->setType($data->get('type'));
            $JiField->setParams($data->get('params'));
            $JiField->setLabel($data->get('label'));
            $JiField->setDescription($data->get('description'));
        } elseif(is_object($data)) {
            // Load field with data from std Object
            if(!isset($type)) $type = $data->type;
            $JiField = $this->loadField($type);
            if(isset($data->name)) $JiField->setName($data->name);
            if(isset($data->title)) $JiField->setTitle($data->title);
            if(isset($data->id)) $JiField->setId($data->id);
            if(isset($data->assignmode)) $JiField->setAssignmode($data->assignmode);
            if(isset($data->assignment)) $JiField->setAssignment($data->assignment);
            if(isset($data->type)) $JiField->setType($data->type);
            if(isset($data->params)) {
                $JiField->setParams($data->params);
            } else {
                $assigned = array('name', 'title', 'id', 'assignmode', 'assignment', 'type', 'params', 'label', 'description');
                $params = array();
                foreach($data as $key=>$value) {
                    if(!in_array($key, $assigned)) $params[$key] = $value;
                }
                $JiField->setParams($params);
            }
            if(isset($data->label)) $JiField->setLabel($data->label);
            if(isset($data->description)) $JiField->setDescription($data->description);

            $JiField->setData($data);
        } else {
            $JiField = $this->loadField($type);
        }
        return $JiField;
    }
    function loadField($type) {
        if(isset($type)) {
            // Load Type
            if(!isset($this->paths)) $this->setPaths();
            foreach($this->paths as $path) {
                // Load Class
                if(file_exists($path.'/'.$type.'.php')) {
                    require_once($path.'/'.$type.'.php');
                    $classname = 'JiMigratorField'.$type;
                    if(class_exists($classname)) $JiField = new $classname;
                }
                // Load XML
                if(isset($JiField) && file_exists($path.'/'.$type.'.xml')) {
                    $xml = simplexml_load_file($path.'/'.$type.'.xml');
                    $xmljversion = $xml['version'];
                    $xmlname = $xml->getName();
                    if($xmlname=='fieldtype' && version_compare(JVERSION, $xmljversion, 'ge')) {
                        if(isset($xml->group[0])) $JiField->setGroup((string) $xml->group[0]);
                        // Get FieldType Params
                        $params = array();
                        if(isset($xml->fields)) {
                            foreach($xml->fields->field as $xmlfield) {
                                $param = new stdClass();
                                // Get Attributes
                                foreach($xmlfield->attributes() as $attrkey=>$attrvalue) {
                                    $param->{$attrkey} = (string) $attrvalue;
                                }
                                $params[$param->name] = $param;
                            }
                        }

                        $JiField->setParams($params);
                    }
                }
                if(isset($JiField)) break;
            }
        }

        if(!isset($JiField)) $JiField = new JiMigratorField();
        return $JiField;
    }
    /*
     * Paths to source field types from
     */
    public function setPaths($paths=null) {
        if(is_array($paths)) {
            foreach($paths as $path) {
                $this->paths[] = $path;
            }
        } elseif($paths!=null) {
            $this->paths[] = $paths;
        } else {
            // Default paths
            $this->paths[] = JPATH_SITE.'/administrator/components/com_jimigrator/jifields';
        }
    }
    /*
     * Builds list of field types from XML files
     */
    public function getFieldTypes($rebuild=false) {
        if(!isset($this->fieldtypes) || $this->fieldtypes==null || $rebuild) {
            // Set Paths
            if(!isset($this->paths)) $this->setPaths();
            // Build fieldtypes
            $fieldtypes = array();
            foreach($this->paths as $dir) {
                if(is_dir($dir)) {
                    if($dh = opendir($dir)) {
                        while(($file=readdir($dh))!==false) {
                            $fileparts = explode('.', $file);
                            $filetype = end($fileparts);
                            if($filetype=='xml') {
                                $xml = simplexml_load_file($dir.'/'.$file);
                                $xmljversion = $xml['version'];
                                $xmlname = $xml->getName();
                                if($xmlname=='fieldtype' && version_compare(JVERSION, $xmljversion, 'ge')) {
                                    // Create fieldtype Object
                                    if(isset($xml->name[0])) {
                                        $name = (string) $xml->name[0];
                                        $name = strtolower($name);
                                    } else {
                                        $name = strtolower($fileparts[0]);
                                    }
                                    $fieldtype = new JiMigratorField();
                                    $fieldtype->setName($name);
                                    if(isset($xml->group[0])) $fieldtype->setGroup((string) $xml->group[0]);
                                    if(isset($xml->label[0])) $fieldtype->setLabel((string) $xml->label[0]);
                                    if(isset($xml->description[0])) $fieldtype->setDescription((string) $xml->description[0]);
                                    // Get FieldType Params
                                    $params = array();
                                    if(isset($xml->fields)) {
                                        foreach($xml->fields->field as $xmlfield) {
                                            $param = new stdClass();
                                            // Get Attributes
                                            foreach($xmlfield->attributes() as $attrkey=>$attrvalue) {
                                                $param->{$attrkey} = (string) $attrvalue;
                                            }
                                            // Get Options
                                            $options = array();
                                            foreach($xmlfield->option as $xmloption) {
                                                $value = (string) $xmloption->attributes()->value;
                                                $options[$value] = (string) $xmloption[0];
                                            }
                                            $param->options = $options;
                                            $params[] = $param;
                                        }
                                    }
                                    //$fieldtype->setParams($params);
                                    $fieldtypes[] = $fieldtype;
                                }
                            }
                        }
                    }
                }
            }

            $this->fieldtypes = $fieldtypes;
            $this->setOrder();
            return $this->fieldtypes;
        } else {
            // Return current fieldtypes instance
            return $this->fieldtypes;
        }
    }
    /*
     * Sets order of fieldtypes
     */
    function setOrder($order_pri='label') {
        if($this->fieldtypes!=null) {
            // Set fieldtype Order
            $order1 = array();
            foreach($this->fieldtypes as $fieldtype) {
                $order1[] = $fieldtype->get($order_pri);
            }
            array_multisort($order1, SORT_ASC, $this->fieldtypes);
        }
    }
}
class JiMigratorFieldType {
    protected $type;
    protected $name;
    protected $description;
    protected $label;
}
class JiMigratorField {
    protected $type;
    protected $id;
    protected $title;
    protected $name;
    protected $value;
    protected $description;
    protected $label;
    protected $data;
    protected $params;
    protected $group;
    protected $assignmode;
    protected $assignment;

    protected $inputid;
    protected $inputname;
    protected $item;
    protected $paramskey = 'params';
    /*
     * Provides a single method to access all protected properties
     */
    public function get($property, $arg1=null) {
        switch($property) {
            case 'title':
                return $this->getTitle();
                break;
            case 'name':
                return $this->getName();
                break;
            case 'value':
                return $this->getValue($arg1);
                break;
            case 'label':
                return $this->getLabel();
                break;
            case 'params':
                return $this->getParams();
                break;
            default:
                return $this->{$property};
                break;
        }
    }
    /*
     * Type: Field subclass type
     */

    /*
     * Title: Unique identifier
     */
    public function setTitle($title) {
        $this->title = $title;
    }
    public function getTitle() {
        return $this->title;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function getName() {
        return $this->name;
    }
    /*
     * Label: Human readable identifier
     */
    public function setLabel($label) {
        $this->label = $label;
    }
    public function getLabel() {
        if(!isset($this->label)) {
            if(isset($this->title)) {
                $this->label = $this->title;
            } else {
                $this->label = $this->name;
            }
        }
        return $this->label;
    }
    /*
     * Description: Displayed in forms for additional information
     */
    public function setDescription($description) {
        $this->description = $description;
    }
    public function setId($id) {
        $this->id = $id;
        $this->inputid = 'jifields_'.$id;
        $this->inputname = 'jifields['.$id.']';
    }
    public function setValue($value) {
        $this->value = $value;
    }
    public function getValue($decode=false) {
        if($this->value==null) {
            $params = JRequest::getVar($this->paramskey);
            if(isset($params[$this->title])) {
                $this->value = $params[$this->title];
            } elseif(isset($params[$this->name])) {
                $this->value = $params[$this->name];
            }
        }
        if($decode) {
            return $this->decodeData($this->value);
        } else {
            return $this->value;
        }
    }
    /*
     * Sets data object that was used to create the JiMigratorField
     */
    public function setData($data) {
        $this->data = $data;
    }
    public function setParamsKey($key) {
        $this->paramskey = $key;
    }
    public function setParams($data) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        if(is_string($data)) {
            $data = json_decode($data, true);
            if(is_array($data) || is_object($data)) {
                $this->getParams();
                foreach($data as $key=>$value) {
                    $this->params->set($key, $value);
                }
            }
        } elseif($data instanceof JiMigratorObject) {
            $this->params = $data;
        } elseif(is_array($data) || is_object($data)) {
            $this->getParams();
            foreach($data as $key=>$value) {
                $this->params->set($key, $value);
            }
        }
    }
    public function getParams() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        if(!isset($this->params)) $this->params = new JiMigratorObject();
        return $this->params;
    }
    public function setType($type) {
        $this->type = $type;
    }
    public function setGroup($group) {
        $this->group = $group;
    }
    public function setAssignmode($assignmode) {
        $this->assignmode = $assignmode;
    }
    public function setAssignment($assignment) {
        $this->assignment = $assignment;
    }
    public function setItem($item) {
        $this->item = $item;
    }
    public function prepareStore() {
        // Decode data first to prevent double encoding
        $this->value = $this->decodeData($this->getValue());
        $this->params = $this->decodeData($this->getParams());
        // Encode data
        $this->value = $this->encodeData($this->value);
        $this->params = $this->encodeData($this->params);
    }
    public function prepareInput() {
        $this->value = $this->get('value', true);
        $this->value = $this->decodeData($this->value);
        $this->params = $this->decodeData($this->params);
    }
    public function prepareOutput() {
        $this->value = $this->decodeData($this->value);
        $this->params = $this->decodeData($this->params);
    }
    /*
     * Mixed: Encodes string, array or object ready for database storing
     */
    public function encodeData($data) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        if(is_string($data) && $data!=null) {
            $data = str_replace(array("\r\n", "\r"), "", $data);
            $data = htmlentities($data, ENT_QUOTES, 'UTF-8');
            $data = mb_convert_encoding($data, "UTF-8", "auto");
        } elseif($data instanceof JiMigratorObject) {
            $data = $data->toArray();
            foreach($data as $key=>$datablock) {
                $data[$key] = $this->encodeData($datablock);
            }
            $data = new JiMigratorObject($data);
        } elseif(is_array($data)) {
            foreach($data as $key=>$datablock) {
                $data[$key] = $this->encodeData($datablock);
            }
        } elseif(is_object($data)) {
            foreach($data as $key=>$datablock) {
                $data->$key = $this->encodeData($datablock);
            }
        }
        return $data;
    }
    /*
     * Mixed: Decodes string, array or object back to normal
     */
    public function decodeData($data) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jimigrator'.DS.'helpers'.DS.'object.php');
        if(is_string($data)) {
            $data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
        } elseif($data instanceof JiMigratorObject) {
            $data = $data->toArray();
            foreach($data as $key=>$datablock) {
                $data[$key] = $this->decodeData($datablock);
            }
            $data = new JiMigratorObject($data);
        } elseif(is_array($data)) {
            foreach($data as $key=>$datablock) {
                $data[$key] = $this->decodeData($datablock);
            }
        } elseif(is_object($data)) {
            foreach($data as $key=>$datablock) {
                $data->$key = $this->decodeData($datablock);
            }
        }
        return $data;
    }
    /*
     * Input Properties
     */
    public function setInputId($inputid) {
        $this->inputid = $inputid;
    }
    public function setInputName($inputname) {
        $this->inputname = $inputname;
    }
    /*
     * Renderable HTML properties
     */
    function renderLabel() {
        $html = '<span class="jifieldlabel">'.$this->get('label').':</span>';
        return $html;
    }
    public function renderInput() {
        $html = '';
        return $html;
    }
    public function renderInputParams() {
        $html = '';
        return $html;
    }
    public function renderInputLabel() {
        $html = '<label id="'.$this->inputid.'-lbl" for="'.$this->get('inputid').'"';
        if($this->get('description')!=null) $html.='class="hasTip" title="'.$this->get('description').'"';
        $html.='>'.$this->get('label').'</label>';
        return $html;
    }
    public function renderOutput() {
        $params = $this->get('params');
        $value = $this->get('value');
        $html = '';
        if(!empty($value) || $params->get('hideempty', '1')==1) {
            $html.= $params->get('prefix', '');
            if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();
            $html.= '<span class="jifieldvalue">'.$this->get('value').'</span>';
            $html.= $params->get('suffix', '');
        }
        return $html;
    }
}