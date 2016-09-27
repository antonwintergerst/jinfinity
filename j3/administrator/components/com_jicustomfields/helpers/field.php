<?php
/**
 * @version     $Id: field.php 107 2014-12-24 10:17:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldHelper {
    /*
     * Returns a field instance
     */
    function loadType($data, $type=null) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'object.php');
        if($data instanceof JiCustomField) {
            // Load field with data from JiCustomField
            if(!isset($type)) $type = $data->get('type');
            $JiField = $this->loadField($type);
            $JiField->setName($data->get('name'));
            $JiField->setTitle($data->get('title'));
            $JiField->setAlias($data->get('alias'));
            $JiField->setId($data->get('id'));
            $JiField->setAssignmode($data->get('assignmode'));
            $JiField->setAssignment($data->get('assignment'));
            $JiField->setType($data->get('type'));
            $JiField->setPrefix($data->get('prefix'));
            $JiField->setSuffix($data->get('suffix'));
            $JiField->setParams($data->get('params'));
            $JiField->setLabel($data->get('label'));
            $JiField->setDescription($data->get('description'));
            $JiField->setState($data->get('state'));
            $JiField->setValue($data->get('value'));
        } elseif(is_object($data)) {
            // Load field with data from std Object
            if(!isset($type)) $type = $data->type;
            $JiField = $this->loadField($type);
            if(isset($data->name)) $JiField->setName($data->name);
            if(isset($data->title)) $JiField->setTitle($data->title);
            if(isset($data->alias)) $JiField->setAlias($data->alias);
            if(isset($data->id)) $JiField->setId($data->id);
            if(isset($data->assignmode)) $JiField->setAssignmode($data->assignmode);
            if(isset($data->assignment)) $JiField->setAssignment($data->assignment);
            if(isset($data->type)) $JiField->setType($data->type);
            if(isset($data->prefix)) $JiField->setPrefix($data->prefix);
            if(isset($data->suffix)) $JiField->setSuffix($data->suffix);
            if(isset($data->params)) $JiField->setParams($data->params);
            if(isset($data->label)) $JiField->setLabel($data->label);
            if(isset($data->description)) $JiField->setDescription($data->description);
            if(isset($data->state)) $JiField->setState($data->state);
            if(isset($data->value)) $JiField->setValue($data->value);
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
                    $classname = 'JiCustomField'.$type;
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
                        //$JiField->setParams($params);
                    }
                }
                if(isset($JiField)) break;
            }
        }
        if(!isset($JiField)) $JiField = new JiCustomField();
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
            $app = JFactory::getApplication();
            $template = $app->getTemplate();
            $this->paths[] = JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.'com_jicustomfields'.DS.'fields';
            $this->paths[] = JPATH_SITE.DS.'components'.DS.'com_jicustomfields'.DS.'views'.DS.'fields'.DS.'tmpl';
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
                                    $fieldtype = new JiCustomField();
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
class JiCustomFieldType {
    protected $type;
    protected $name;
    protected $description;
    protected $label;
}
class JiCustomField {
    protected $type;
    protected $id;
    protected $title;
    protected $alias;
    // TODO: Remove depreciated $name
    protected $name;
    protected $value;
    protected $description;
    protected $label;
    protected $data;
    protected $prefix;
    protected $suffix;
    public $params;
    protected $group;
    protected $assignmode;
    protected $assignment;

    protected $inputid;
    protected $inputname;
    protected $item;
    protected $paramskey = 'params';
    protected $state;
    /*
     * Provides a single method to access all protected properties
     */
    public function get($property, $arg1=null, $arg2=null) {
        switch($property) {
            case 'id':
                return $this->getId();
                break;
            case 'title':
                return $this->getTitle();
                break;
            case 'name':
                return $this->getName();
                break;
            case 'value':
                if($arg1==null) {
                    return $this->getValue();
                } else {
                    return $this->getValue($arg1);
                }
                break;
            case 'label':
                return $this->getLabel();
                break;
            case 'params':
                return $this->getParams();
                break;
            case 'options':
                if(method_exists($this, 'getOptions')) {
                    return $this->getOptions($arg2);
                } else {
                    return $arg1;
                }
            default:
                if(isset($this->{$property}) && !empty($this->{$property})) {
                    return $this->{$property};
                } else {
                    return $arg1;
                }

                break;
        }
    }
    public function set($property, $value) {
        $this->{$property} = $value;
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
    public function setAlias($alias) {
        $this->alias = $alias;
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
                $this->label = $this->title;
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
    public function setState($state) {
        $this->state = $state;
    }
    public function getId() {
        if($this->id==null) {
            return 0;
        } else {
            return $this->id;
        }
    }
    public function setId($id) {
        if($id==null) $id = 0;
        $this->id = $id;
        $this->inputid = 'jifields_'.$id;
        $this->inputname = 'jifields['.$id.']';
    }
    public function setValue($value) {
        $this->value = $value;
    }
    public function getValue($type='string') {
        if($this->value==null) {
            $app = JFactory::getApplication();
            $jinput = $app->input;
            $params = $jinput->get($this->paramskey, array(), 'raw');
            if(isset($params[$this->title])) {
                $this->value = $params[$this->title];
            } elseif(isset($params[$this->name])) {
                $this->value = $params[$this->name];
            }
        }

        $params = $this->get('params');
        // set default value
        if($params->get('showdefault') && empty($this->value)) $this->value = $params->get('default');

        if($type=='string') {
            if($this->value instanceof JiCustomFieldsObject) {
                $this->value = $this->value->toJSON();
            } elseif(is_array($this->value) || is_object($this->value)) {
                $this->value = $this->raw_json_encode($this->value);
            }
        } elseif($type=='jiobject') {
            if(!($this->value instanceof JiCustomFieldsObject)) {
                $this->value = new JiCustomFieldsObject($this->value);
            }
        } elseif($type=='array') {
            if($this->value instanceof JiCustomFieldsObject) {
                $this->value = $this->value->toArray();
            } elseif(is_object($this->value)) {
                $this->value = (array) $this->value;
            } elseif(!is_array($this->value)) {
                if($jsonarray = json_decode($this->value, true)) {
                    $this->value = $jsonarray;
                } elseif(strpos($this->value, ',')!==false) {
                    $this->value = explode(',', $this->value);
                } else {
                    $this->value = array($this->value);
                }
            }
        }
        /*if($decode) {
            return $this->decodeData($this->value);
        } else {
            return $this->value;
        }*/
        return $this->value;
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
    /*
     * Sets data object that was used to create the JiCustomField
     */
    public function setData($data) {
        $this->data = $data;
    }
    public function setParamsKey($key) {
        $this->paramskey = $key;
    }
    public function setParams($data) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'object.php');
        if(is_string($data)) {
            $data = json_decode($data, true);
            /*echo '<pre>';
            print_r($data);
            echo '</pre>';*/
            if(is_array($data)) {
                $this->getParams();
                foreach($data as $key=>$value) {
                    $this->params->set($key, $value);
                }
            }
        } elseif($data instanceof JiCustomFieldsObject) {
            $this->params = $data;
        } elseif(is_array($data) || is_object($data)) {
            $this->getParams();
            foreach($data as $key=>$value) {
                $this->params->set($key, $value);
            }
        }
    }
    public function getParams() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'object.php');
        if(!isset($this->params)) $this->params = new JiCustomFieldsObject();
        return $this->params;
    }
    public function getOptions($reset=false) {
        if(isset($this->options) && !$reset) {
            return $this->options;
        } else {
            $params = $this->get('params');
            $this->options = array();
            foreach((array)$params->get('options') as $value=>$label) {
                // @legacy complex array compatibility
                if(is_object($label) || is_array($label)) {
                    $option = (array) $label;
                    $value = isset($option['value'])? $option['value'] : '';
                    $label = isset($option['label'])? $option['label'] : '';
                }
                $this->options[$value] = $label;
            }
        }
        return $this->options;
    }
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }
    public function setSuffix($suffix) {
        $this->suffix = $suffix;
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
        if($this->params instanceof JiCustomFieldsObject) $this->params = $this->encodeData($this->params);
    }
    public function prepareInput() {
        //$this->value = $this->get('value', true);
        //$this->value = $this->decodeData($this->value);
        if($this->params instanceof JiCustomFieldsObject) $this->params = $this->decodeData($this->params);
    }
    public function prepareOutput() {
        $this->value = $this->decodeData($this->value);
        $this->params = $this->decodeData($this->params);
    }
    /*
     * Mixed: Encodes string, array or object ready for database storing
     */
    public function encodeData($data) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'object.php');
        if(is_string($data) && $data!=null) {
            // Remove line breaks
            $data = str_replace(array("\r\n", "\r"), "", $data);
            //$data = htmlentities($data, ENT_QUOTES, 'UTF-8');
        } elseif($data instanceof JiCustomFieldsObject) {
            $data = $data->toArray();
            foreach($data as $key=>$datablock) {
                $data[$key] = $this->encodeData($datablock);
            }
            $data = new JiCustomFieldsObject($data);
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
        /*require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'object.php');
        if(is_string($data)) {
            //$data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
        } elseif($data instanceof JiCustomFieldsObject) {
            $data = $data->toArray();
            foreach($data as $key=>$datablock) {
                $data[$key] = $this->decodeData($datablock);
            }
            $data = new JiCustomFieldsObject($data);
        } elseif(is_array($data)) {
            foreach($data as $key=>$datablock) {
                $data[$key] = $this->decodeData($datablock);
            }
        } elseif(is_object($data)) {
            foreach($data as $key=>$datablock) {
                $data->$key = $this->decodeData($datablock);
            }
        }*/
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
    public function renderLabel() {
        $html = '<span class="jifieldlabel">'.$this->get('label').'</span>';
        return $html;
    }
    public function renderInput() {
        $html = '';
        return $html;
    }
    public function renderInputScript() {
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

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if(empty($value) && $params->get('hideempty', '0')==1) return $html;

        // Continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();

        $valuehtml = '<span class="jifieldvalue">'.$value.'</span>';
        // TODO
        /*if($params->get('linkedvalues', 1)==1) {
            require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/route.php');
            $item = $this->get('item');
            $catid = isset($item->catid)? $item->catid : null;
            $link = JiCustomFieldsHelperRoute::getSearchRoute($catid);

            $html.= '<a class="jifieldlink" href="'.JRoute::_($link.'&fs['.$this->id.']='.htmlspecialchars($value)).'" title="View more articles like '.$value.'">'.$valuehtml.'</a>';
        } else {*/
            $html.= $valuehtml;
        //}

        $html.= $this->get('suffix', '');

        return $html;
    }
}