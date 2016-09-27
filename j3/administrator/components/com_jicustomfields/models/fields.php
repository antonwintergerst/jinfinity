<?php
/**
 * @version     $Id: fields.php 182 2014-12-31 10:10:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.application.component.view');

class JiCustomFieldsModelFields extends JModelList
{
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id',
                'title',
                'state',
                'ordering',
                'published'
            );
        }

        // include global shortcuts
        require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/jifieldhelper.php');

        parent::__construct($config);
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();

        // Load state from the request.
        $pk = $app->input->getInt('id');
        $this->setState('filter.catid', $pk);

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout'))
        {
            $this->context .= '.'.$layout;
        }

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        // List state information.
        parent::populateState('title', 'asc');
    }

    protected function getListQuery()
    {
        // Create a new query object.
        $db		= $this->getDbo();
        $query	= $db->getQuery(true);
        $user	= JFactory::getUser();
        $app	= JFactory::getApplication();

        // Select the required fields from the table.
        $query->select('f.*');
        $query->from('#__jifields AS f');

        if($catid = $this->getState('filter.catid')) {
            $query->join('left', '#__jifields_map AS map ON map.fid = f.id');
            $query->where('map.catid='.(int)$catid.' OR map.catid=0');
        }
        if($cid = $this->getState('filter.cid')) {
            $query->select('v.value');
            $query->join('left', '#__jifields_values AS v ON (v.fid = f.id AND v.cid='.(int)$cid.')');
        }

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('f.state = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(f.state = 0 OR f.state = 1)');
        }
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('f.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(f.`title` LIKE '.$search.')');
            }
        }
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'title');
        $orderDirn	= $this->state->get('list.direction', 'asc');
        $query->order($db->escape('`'.$orderCol.'` '.$orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items	= parent::getItems();
        return $items;
    }

    public function getFieldTypes()
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        if(!isset($JiFieldHelper)) $JiFieldHelper = new JiCustomFieldHelper();
        $fieldtypes = $JiFieldHelper->getFieldTypes();
        return $fieldtypes;
    }

    /*
     * Renders fields input layout
     */
    public function renderInputLayout($jifields, $item)
    {
        jimport('joomla.filesystem.file');

        // shortcut variables
        $this->jifields = $jifields;
        $this->item = $item;

        $app = JFactory::getApplication();
        $template = $app->getTemplate();
        $stylepath1 = JPATH_THEMES.'/'.$template.'/html/com_jicustomfields/fields/form.php';
        $stylepath2 = JPATH_THEMES.'/'.$template.'/html/com_content/fields/form.php';
        if(JFile::exists($stylepath1)) {
            require_once($stylepath1);
        } elseif(JFile::exists($stylepath2)) {
            require_once($stylepath2);
        } else {
            require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'views'.DS.'fields'.DS.'tmpl'.DS.'form.php');
        }
    }

    /*
     * Returns a single new field HTML input
     */
    public function renderInput()
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        $JiFieldHelper = new JiCustomFieldHelper();
        $jinput = JFactory::getApplication()->input;
        // Build Field Data Object
        $data = new stdClass();
        $data->id = $jinput->get('id', null, 'string');
        $data->type = $jinput->get('type', null, 'string');
        $data->title = $jinput->get('title', 'new', 'string');
        $data->state = 1;
        // Load Field
        $JiField = $JiFieldHelper->loadType($data);
        $JiField->prepareInput();
        ob_start();
        require_once(JPATH_SITE.'/administrator/components/com_jicustomfields/views/fields/tmpl/form.field.php');
        $html = ob_get_clean();
        return $html;
    }

    public function renderInputOption()
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        $JiFieldHelper = new JiCustomFieldHelper();
        $jinput = JFactory::getApplication()->input;
        // Build Field Data Object
        $data = new stdClass();
        $data->id = $jinput->get('id', null, 'string');
        $data->type = $jinput->get('type', null, 'string');
        $data->title = $jinput->get('title', 'new', 'string');
        // Load Field
        $JiField = $JiFieldHelper->loadType($data);
        ob_start();
        // render html for new option
        echo $JiField->renderInputOption($jinput->get('value', null, 'raw'), $jinput->get('label', null, 'raw'), $jinput->get('optioncount', 'new0', 'string'));
        $html = ob_get_clean();
        return $html;
    }

    public function renderOutput(&$item, $context='', $pagecontext='')
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;

        $jiparams = JComponentHelper::getParams('com_jicustomfields');
        $document = JFactory::getDocument();

        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        $JiFieldHelper = new JiCustomFieldHelper();

        require_once(JPATH_SITE.'/components/com_content/helpers/route.php');
        if(isset($item->id) && isset($item->catid)) $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->id, $item->catid));

        // set display context
        $displaycontext = 0;
        if($context=='com_content.article') {
            $displaycontext = 2;
        } else if($context=='com_content.category' || $context=='com_content.featured') {
            $displaycontext = 1;
        }
        $fieldfilter = (isset($item->params))? $item->params->get('show_fields') : 0;
        $itemparams = (isset($item->params))? $item->params : new JRegistry;

        // attach fields to certain contexts
        if(strpos($context, 'com_content')!==false || strpos($context, 'com_jicustomfields')!==false) {
            // get all field values
            $values = $this->getValues($item);

            // attach fields to item
            $item->fields = $this->getFields($item);
            $item->fieldsabove = '';
            $item->fieldsbelow = '';

            // Map fields to item object
            if($item->fields!=null) {
                foreach($item->fields as $fid=>$field) {
                    $JiField = $JiFieldHelper->loadType($field);
                    $JiField->setItem($item);

                    // attach value
                    if(isset($values[$fid])) $JiField->setValue($values[$fid]);

                    $JiField->setParams($field->attribs);
                    $CommonParamsField = $JiFieldHelper->loadType($JiField, 'commonparams');
                    $CommonParamsField->prepareOutput();
                    $JiField->setParams($CommonParamsField->get('params'));
                    $JiField->prepareOutput();
                    $fieldparams = $JiField->get('params');
                    if($itemparams->get('showlabel')!='') $JiField->params->set('showlabel', $itemparams->get('showlabel'));
                    $position = $fieldparams->get('position', 'above');

                    // only render if in relevant display context
                    $showin = (int)$fieldparams->get('showin', 0);
                    if(($fieldfilter==0 || in_array($fid, $fieldfilter)) && ($showin==0 || $showin==$displaycontext)) {
                        if($position=='above') {
                            $item->fieldsabove.= $JiField->renderOutput();
                        } elseif($position=='below') {
                            $item->fieldsbelow.= $JiField->renderOutput();
                        } elseif($position=='head') {
                            $document->addCustomTag($JiField->renderOutput());
                        }
                    }
                    $item->{$JiField->get('alias')} = $JiField;
                }
            }
        }

        // find source text
        if($context=='com_content.article' || isset($item->text)) {
            $text = $item->text;
        } else {
            if(isset($item->introtext)) {
                $text = $item->introtext;
            } else if(isset($item->text)) {
                $text = $item->text;
            } else {
                $text = $item->fulltext;
            }
        }
        $updatetext = false;

        // ignore warnings
        libxml_use_internal_errors(true);

        // convert to dom
        $dom = new DOMDocument;
        $dom->loadHTML('<div>'.$text.'</div>');
        // Perform Xpath Query
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//*[text()[contains(.,"{/fields}")]]');
        $regex = "#{fields(.*?)}(.*?){/fields}#s";
        $inlinetags = array('a','b','em','big','i','p','strong','span','tt','s');
        if($nodes!=null && $nodes->length>0) {
            $savetext = false;
            foreach($nodes as $node) {
                //$nodetext = $node->wholeText;
                $tempdoc = new DOMDocument();
                $cloned = $node->cloneNode(TRUE);
                $tempdoc->appendChild($tempdoc->importNode($cloned,TRUE));
                $nodetext = $tempdoc->saveHTML();
                // find curly code matches
                preg_match_all($regex, $nodetext, $matches, PREG_SET_ORDER);
                if(count($matches)>0) {
                    $savetext = true;
                    $updatetext = true;
                    // Repair invalid html
                    $tag = $node->tagName;
                    $prefix = '';
                    $suffix = '';
                    if(in_array($tag, $inlinetags)) {
                        $prefix = '</'.$node->tagName.'>';
                        $suffix = '<'.$node->tagName.'>';
                    }
                    foreach($matches as $match) {
                        $target_item = isset($match[1])? trim($match[1]) : null;
                        if(strlen($target_item)==0) $target_item = false;
                        $field_alias = isset($match[2])? $match[2] : null;
                        $replacement = '';

                        if(!$target_item && !isset($item->{$field_alias})) {
                            // find current article
                            if($pagecontext=='com_content.article') {
                                $target_item = (int) $jinput->get('id');
                            }
                        }

                        if($field_alias) {
                            if($target_item) {
                                // another article
                                $JiField = get_jifield($field_alias, $target_item);
                                if($JiField) {
                                    $fieldparams = $JiField->get('params');

                                    // only render if in relevant display context
                                    $showin = (int)$fieldparams->get('showin', 0);
                                    if(($showin==0 || $showin==$displaycontext) && $JiField) {
                                        $fieldoutput = $JiField->renderOutput();
                                        // fast test for block elements
                                        if(!in_array(substr($fieldoutput, 1, 1), $inlinetags)) {
                                            // replacement will contain block elements
                                            // close and re-open existing inline tags
                                            $replacement = $prefix.$JiField->renderOutput().$suffix;
                                        } else {
                                            // replacement appears to be inline
                                            $replacement = $JiField->renderOutput();
                                        }
                                    }
                                }
                            } else if(isset($item->{$field_alias})) {
                                // this article
                                $JiField = $item->{$field_alias};
                                $fieldparams = $JiField->get('params');

                                // only render if in relevant display context
                                $showin = (int)$fieldparams->get('showin', 0);
                                if($showin==0 || $showin==$displaycontext) {
                                    $fieldoutput = $JiField->renderOutput();
                                    // fast test for block elements
                                    if(!in_array(substr($fieldoutput, 1, 1), $inlinetags)) {
                                        // replacement will contain block elements
                                        // close and re-open existing inline tags
                                        $replacement = $prefix.$JiField->renderOutput().$suffix;
                                    } else {
                                        // replacement appears to be inline
                                        $replacement = $JiField->renderOutput();
                                    }
                                }
                            }
                        }
                        $nodetext = preg_replace('#'.$match[0].'#s', $replacement, $nodetext);
                    }
                    // update node
                    $newNode = $dom->createDocumentFragment();
                    $newNode->appendXML($nodetext);
                    $node->parentNode->replaceChild($newNode, $node);
                }
            }
            // only save text if it has changed
            if($savetext) {
                $empty = true;
                foreach($dom->childNodes as $node) {
                    $nodevalue = trim(str_replace(array("\r\n", "\r"), "", $node->nodeValue));
                    if(!empty($nodevalue)) {
                        $empty = false;
                        break;
                    }
                }
                if($empty) {
                    $text = '';
                } else {
                    $text = mb_substr($dom->saveXML($xpath->query('//body')->item(0)), 6, -7, "UTF-8");
                }
            }
        }

        // only add fieldsabove and fieldsbelow if they contain content
        if(!empty($item->fieldsabove)) {
            $updatetext = true;
            $text = $jiparams->get('above_prefix', '').$item->fieldsabove.$jiparams->get('above_suffix', '').$text;
        }
        if(!empty($item->fieldsbelow)) {
            $updatetext = true;
            $text.= $jiparams->get('below_prefix', '').$item->fieldsbelow.$jiparams->get('below_suffix', '');
        }

        // only update text if it has changed
        if($updatetext) {
            // update source text
            if($context=='com_content.article' || isset($item->text)) {
                $item->text = $text;
            } else {
                if(isset($item->introtext)) {
                    $item->introtext = $text;
                } else if(isset($item->text)) {
                    $item->text = $text;
                } else {
                    $item->fulltext = $text;
                }
            }
        }
    }

    /*
     * Returns an array of JiFields
     */
    public function getJiFields($item=null, $values=array())
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        $JiFieldHelper = new JiCustomFieldHelper();
        $jifields = array();
        $response = $this->getFields($item);
        if($response!=null) {
            foreach($response as $field) {
                $JiField = $JiFieldHelper->loadType($field);
                if($JiField->get('group')!='system') {
                    if(isset($values[$field->id])) {
                        $JiField->setValue($values[$field->id]);
                    }
                    if(isset($field->attribs)) $JiField->setParams($field->attribs);
                    $jifields[$field->id] = $JiField;
                }
            }
        }
        return $jifields;
    }

    public function getJiField($fid=0, $item=null, $values=array(), $article=0)
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        $JiFieldHelper = new JiCustomFieldHelper();
        if($fid==0) {
            $JiField = $JiFieldHelper->loadType(null, 'textfield');
            return $JiField;
        }

        // Load Database Object
        $db = JFactory::getDBO();
        // Get Fieldlist
        $query = $db->getQuery(true);
        $query->select('f.*');
        $query->from('#__jifields AS f');
        $query->join('left', '#__jifields_map AS map ON map.fid = f.id');
        if(isset($item) && (int)$item->catid!=0) {
            $query->where('map.catid = '.(int) $item->catid.' OR map.catid=0');
        }
        if(isset($fid)) {
            $query->where('f.id = '.(int) $fid);
        }
        $query->order('map.`ordering` ASC');
        $db->setQuery($query);
        $field = $db->loadObject();

        $JiField = null;
        if($field!=null) {
            $JiField = $JiFieldHelper->loadType($field);
            if($JiField->get('group')!='system') {
                if(isset($values[$field->id])) $JiField->setValue($values[$field->id]);
                if(isset($field->attribs)) $JiField->setParams($field->attribs);
                $jifields[$field->id] = $JiField;
            }
        }
        return $JiField;
    }

    /*
     * Returns a single field data object from the database
     */
    public function getField($fid)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT * FROM #__jifields WHERE `id`='.$db->quote($fid);
        $db->setQuery($query);
        $result = $db->loadObject();
        return $result;
    }

    /*
     * Returns an array of field data objects
     */
    public function getFields($item=null, $order_by=null, $order_dir=null, $searchword=null)
    {
        $jiparams = JComponentHelper::getParams('com_jicustomfields');
        if($jiparams->get('debug',0)==1) {
            echo '<pre>';
            print_r($item);
            echo '</pre>';
        }
        // Load Database Object
        $db = JFactory::getDBO();
        // Get Fieldlist
        $query = $db->getQuery(true);
        $query->select('f.*');
        $query->from('#__jifields AS f');
        $query->join('left', '#__jifields_map AS map ON (map.`fid`=f.`id`)');

        // Filter by published state
        $published = $this->getState('filter.published');

        if(is_numeric($published)) {
            $query->where('f.state = ' . (int) $published);
        } elseif($published === '') {
            $query->where('(f.state = 0 OR f.state = 1)');
        }

        if(isset($item)) {
            // assume uncategorised if no catid is present
            if(!isset($item->catid) || empty($item->catid)) {
                if(!isset($item)) $item = new stdClass();
                $item->catid = 2;
                if($jiparams->get('debug',0)==1) {
                    echo '<pre>';
                    print_r($item);
                    print_r('WARNING: Missing Category ID. Assuming it to be 2:(Uncategorised)');
                    echo '</pre>';
                }
            }

            // consider cat<=>0 relationship to include all fields
            // or 0<=>field relationship to include all categories
            $query->join('left', '#__jifields_map AS map2 ON (map2.`catid`=0 AND map2.`fid`=0) OR (map2.`catid`='.(int) $item->catid.' AND map2.`fid`=0) OR (map.`fid`=map2.`fid` AND map2.`catid`=0)');
            $query->where('CASE WHEN map2.`fid`=0 THEN 1 ELSE (map.`catid`='.(int) $item->catid.' OR map2.`catid`=0) END');
        }

        $query->order('f.`ordering` ASC');
        $db->setQuery($query, 0, 100);
        $response = $db->loadObjectList('id');
        if(!$response && $jiparams->get('debug',0)==1){
            echo '<pre>';
            print_r($db->getErrorMsg());
            echo '</pre>';
        }
        return $response;
    }

    public function getFieldNames()
    {
        // Get JiCustomFields Parameters
        $jiparams = JComponentHelper::getParams('com_jicustomfields');
        $idprefix = $jiparams->get('fields_idprefix', 0);
        // Load Database Object
        $db = JFactory::getDBO();
        // Get Fieldlist
        $query = 'SELECT `id`, `title` FROM #__jifields ORDER BY `ordering` ASC';
        $db->setQuery($query, 0, 100);
        $fields = $db->loadObjectList();

        $return = array();
        if($fields!=null) {
            foreach($fields as $field) {
                $return[] = ($idprefix==1)? $field->id.'-'.$field->title : $field->title;
            }
        } else {
            $return = false;
        }
        return $return;
    }

    /*
     * Returns array of values for a particular item
     */
    public function getValues($item, $contenttype=null)
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'object.php');

        $values = array();
        $id = $this->getItemID($item);
        if($id!=null && $id!=false) {
            // Load Database Object
            $db = JFactory::getDBO();
            // Get Article Fields
            $query = $db->getQuery(true);
            $query->select('DISTINCT fv.`fid`, fv.`value`');
            $query->from('#__jifields_values AS fv');
            $query->join('left', '#__jifields_map AS map ON (map.`fid`=fv.`fid`)');
            $query->join('left', '#__jifields AS f ON (f.`id`=fv.`fid`)');
            $query->where('fv.`cid`='.$id);
            $query->order('f.ordering ASC');

            //$query = 'SELECT `fid`, `value` FROM #__jifields_values WHERE `cid`='.$id;
            $db->setQuery($query, 0, 100);

            $results = $db->loadObjectList();

            if($results!=null) {
                foreach($results as $result) {
                    if(isset($values[$result->fid]) && ((is_array($values[$result->fid]) && !in_array($result->value, $values[$result->fid])) || $result->value!=$values[$result->fid])) {
                        // multiple unique values for this field
                        if(!is_array($values[$result->fid])) $values[$result->fid] = array($values[$result->fid]);
                        $values[$result->fid][] = $result->value;
                    } else {
                        // single unique value for this field
                        $values[$result->fid] = $result->value;
                    }
                }
            }
        }
        return $values;
    }

    /*
     * Returns item ID if exists, else returns false
     */
    public function getItemID($item)
    {
        if(is_object($item)) {
            if(isset($item->id)) $id = (int) $item->id;
        } else {
            $id = (int) $item;
        }
        return (isset($id) && $id!=0)? $id : false;
    }

    /*
     * Stores all fields, values and indices for an item
     */
    public function store($item=null, $context=null)
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
        $jinput = JFactory::getApplication()->input;
        $JiFieldHelper = new JiCustomFieldHelper();
        $return = new stdClass();
        // Save Custom Fields
        if(isset($item) && isset($item->jifields)) {
            $fields = $item->jifields;
        } else {
            $fields = $jinput->get('jifields', null, 'raw');
        }

        //echo '<pre>';print_r($fields);echo '</pre>'; die;
        if(isset($fields)) {
            $ordering = 1;
            $errors = array();
            foreach($fields as $fid=>$data) {
                // Alter the title for save as copy
                if($context=='com_jicustomfields.field' && $jinput->get('task')=='save2copy') {
                    list($title, $alias) = $this->generateNewTitle($data['alias'], $data['title']);
                    $data['title'] = $title;
                    $data['alias'] = $alias;
                    $data['state'] = 0;
                    $data['new'] = 1;
                }

                // Convert field data array to object
                $field = new stdClass();
                foreach($data as $dkey=>$dvalue) {
                    $field->{$dkey} = $dvalue;
                }
                // Set order
                $field->ordering = $ordering;

                //$isNew = (isset($field->new) || (!isset($field->new) && $this->fieldExists($fid)));
                // Get JiField
                if(!isset($field->new)) {
                    // existing field
                    $field->id = $fid;
                    $JiField = $this->getJiField($fid);
                } else {
                    // new field
                    $field = $this->storeField($field);
                    if(!$field) {
                        $errors[] = 'Unable to save/create field';
                        continue;
                    }
                    $JiField = $JiFieldHelper->loadType($field);
                    $fid = $JiField->get('id');
                }

                $ordering++;
                if(isset($field->params)) $JiField->setParams($field->params);
                if(isset($field->value)) $JiField->setValue($field->value);
                // Perform system prepare store
                $CommonParamsField = $JiFieldHelper->loadType($JiField->get('data'), 'commonparams');
                $CommonParamsField->setParams($JiField->get('params'));
                $CommonParamsField->prepareStore();
                $JiField->setParams($CommonParamsField->get('params'));
                // Perform custom prepare store
                $JiField->prepareStore();
                $field->attribs = $JiField->get('params')->toArray();

                // Store field
                if(isset($field->title) && isset($field->alias)) $this->storeField($field);
                if(isset($item->catid)) $this->storeMap($fid, $item->catid, $ordering);

                if(isset($item->id)) {
                    // delete old values
                    $this->deleteValues($fid, $item->id);

                    if(in_array($JiField->get('type'), array('multiselect', 'tags'))) {
                        // these fields may have more than one value
                        $datavalues = $JiField->getValue('array');
                        foreach($datavalues as $datavalue) {
                            $this->storeValue($fid, $item->id, $datavalue);
                        }
                    } else {
                        $this->storeValue($fid, $item->id, $JiField->getValue('string'));
                    }
                }
            }
            // Store item values
            if(count($errors)==0) {
                $return->valid = "true";
                $return->msg = "Custom Fields saved successfully!";
            } else {
                $return->valid = "false";
                if(count($fields)==1) {
                    $return->msg = "Unable to save the custom field. Possibly missing some parameters.";
                } else {
                    $return->msg = "Unable to save some custom fields";
                }
            }
        } else {
            $return->valid = "false";
            $return->msg = "Unable to save fields. Reason: No fields were submitted.";
        }
        return $return;
    }

    public function raw_json_encode($input)
    {
        return preg_replace_callback(
            '/\\\\u([0-9a-zA-Z]{4})/',
            function ($matches) {
                return mb_convert_encoding(pack('H*',$matches[1]),'UTF-8','UTF-16');
            },
            json_encode($input)
        );
    }

    public function fieldExists($id)
    {
        $id = (int) $id;
        $db = JFactory::getDBO();
        if($id!=0) {
            // Check if exists
            $query = 'SELECT `id` FROM #__jifields WHERE `id`='.(int)$id;
            $db->setQuery($query);
            $exists = (int) $db->loadResult();
            if($exists>0) return true;
        }
        return false;
    }

    /*
     * Stores a field and returns the data attached
     */
    public function storeField($field)
    {
        // Prepare Data
        if(isset($field->id)) {
            $id = (int) $field->id;
        } else {
            $id = 0;
        }

        // Load Database Object
        $db = JFactory::getDBO();
        $exists = 0;
        if($id!=0) {
            // Check if exists
            $query = 'SELECT `id` FROM #__jifields WHERE `id`='.(int)$id;
            $db->setQuery($query);
            $exists = (int) $db->loadResult();
        }

        // validate
        if($exists==0) {
            if(!isset($field->title) || empty($field->title)) return false;
            if(!isset($field->type) || empty($field->type)) return false;
        }

        if(isset($field->assignment)) $field->assignment = implode(',', $field->assignment);
        if(isset($field->attribs) && is_array($field->attribs)) $field->attribs = $this->raw_json_encode($field->attribs);

        if(trim($field->alias)=='') {
            $field->alias = $field->title;
            $field->alias = JApplication::stringURLSafe($field->alias);
            if(trim(str_replace('-', '', $field->alias))=='') $field->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
        }

        // Ensure alias is unique
        $query = 'SELECT `id` FROM #__jifields WHERE `alias`='.$db->quote($field->alias).' AND `id`!='.(int)$id;
        $db->setQuery($query);
        if((int) $db->loadResult()!=0) {
            list($title, $alias) = $this->generateNewTitle($field->alias, $field->title);
            $field->alias = $alias;
            $field->title = $title;
        }

        // Only save these variables
        $varstosave = array('title', 'alias', 'type', 'prefix', 'suffix', 'attribs', 'state');

        // save using JTable
        $table = $this->getTable();
        if($table) {
        $table->load($field->id);
        $table->bind($field);

        if (!$table->check())
        {
            $this->setError($table->getError());

            return false;
        }
        if (!$table->store())
        {
            $this->setError($table->getError());

            return false;
        }

        $field->id = $table->id;
        } else {

        if($exists==0) {
            $data = new stdClass();
            foreach($varstosave as $key) {
                if(isset($field->{$key}) && $field->{$key}!=null) $data->{$key} = $field->{$key};
            }
            // Save New
            $db->insertObject('#__jifields', $data, 'id');
            $field->id = $db->insertid();
        } else {
            $data = new stdClass();
            foreach($varstosave as $key) {
                if(isset($field->{$key}) && $field->{$key}!=null) $data->{$key} = $field->{$key};
            }
            $update = $this->objectToUpdate($data);
            // Update Existing
            $query = "UPDATE #__jifields SET ".$update." WHERE `id`=".(int) $field->id;
            $db->setQuery($query);
            $db->query();
        }
        }
        $this->setState('field.id', $field->id);
        return $field;
    }

    public function generateNewTitle($alias, $title)
    {
        JTable::addIncludePath(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'tables');
        // Alter the title & alias
        $table = $this->getTable();
        if($table) {
            while ($table->load(array('alias'=>$alias)))
            {
                $title = JString::increment($title);
                $alias = JString::increment($alias, 'dash');
            }
        }

        return array($title, $alias);
    }

    public function getTable($type = 'Fields', $prefix = 'JiCustomFieldsTable', $config = array())
    {
        JTable::addIncludePath(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'tables');
        return JTable::getInstance($type, $prefix, $config);
    }

    public function storeMap($fid, $catid, $ordering=0)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT `id` FROM #__jifields_map WHERE `fid`='.(int)$fid.' AND `catid`='.(int)$catid;
        $db->setQuery($query);
        $exists = $db->loadResult();
        if($exists==null) {
            $query = 'INSERT INTO #__jifields_map (`fid`, `catid`, `ordering`) VALUES ('.(int)$fid.','.(int)$catid.','.(int)$ordering.')';
        } else {
            $query = 'UPDATE #__jifields_map SET `ordering`='.(int)$ordering.' WHERE `id`='.(int)$exists;
        }
        $db->setQuery($query);
        $db->query();
    }

    public function storeValue($fid=0, $cid=0, $value)
    {
        // must have a valid fid and cid
        if((int)$fid==0 || (int)$cid==0) return;

        if(is_array($value) || is_object($value)) {

            $value = $this->raw_json_encode($value);
        }
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('fv.`id`');
        $query->from('#__jifields_values AS fv');
        $query->join('left', '#__jifields AS f ON f.`id`=fv.`fid`');

        // allow multi-select and tag fields to have more than one value
        $query->where('f.`type` NOT IN ("multiselect", "tags") AND fv.`fid`='.(int)$fid.' AND fv.`cid`='.(int)$cid);

        $db->setQuery($query);
        $exists = $db->loadResult();

        if($exists) {
            // maintain a single value
            $query = 'DELETE fv1 FROM #__jifields_values AS fv1, #__jifields_values AS fv2 WHERE fv1.`id`>fv2.`id`
             AND fv1.`fid`='.(int)$fid.' AND fv1.`cid`='.(int)$cid.'
             AND fv2.`fid`='.(int)$fid.' AND fv2.`cid`='.(int)$cid;
            $db->setQuery($query);
            $db->query();
        } else {
            // remove possible duplicates
            $query = 'DELETE fv1 FROM #__jifields_values AS fv1, #__jifields_values AS fv2 WHERE fv1.`id`>fv2.`id` AND fv1.`value`=fv2.`value`
             AND fv1.`fid`='.(int)$fid.' AND fv1.`cid`='.(int)$cid.'
             AND fv2.`fid`='.(int)$fid.' AND fv2.`cid`='.(int)$cid;
            $db->setQuery($query);
            $db->query();
        }

        // check there is still a value for this singular field
        if($exists) {
            $query = $db->getQuery(true);
            $query->select('`id`');
            $query->from('#__jifields_values');
            $query->where('`fid`='.(int)$fid.' AND `cid`='.(int)$cid);

            $db->setQuery($query);
            $exists = $db->loadResult();
        }

        if($exists) {
            // update existing
            $query = 'UPDATE #__jifields_values SET `value`='.$db->quote($value).' WHERE `id`='.(int)$exists;
            $db->setQuery($query);
            $db->query();
        } elseif($value!='') {
            // insert new value
            $query = 'INSERT INTO #__jifields_values (`fid`, `cid`, `value`) VALUES ('.(int)$fid.','.(int)$cid.','.$db->quote($value).')';
            $db->setQuery($query);
            $db->query();
        }
    }

    public function deleteValues($fid=0, $cid=0)
    {
        // must have a valid fid and cid
        if((int)$fid==0 || (int)$cid==0) return;

        $db = JFactory::getDBO();
        $query = 'DELETE FROM #__jifields_values WHERE `fid`='.(int)$fid.' AND `cid`='.(int)$cid;
        $db->setQuery($query);
        $db->query();
    }

    public function unassignField()
    {
        $return = new stdClass();
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $fid = (int) $jinput->get('fid');
        $catid = (int) $jinput->get('catid');
        if($fid==0) {
            $return->valid = "false";
            $return->msg = "Unable to alter field assignment. Reason: No field selected.";
            return $return;
        }
        if($catid==0) {
            $return->valid = "false";
            $return->msg = "Unable to alter field assignment. Reason: Category not defined.";
            return $return;
        }
        $db = JFactory::getDBO();
        $query = 'DELETE FROM #__jifields_map WHERE `fid`='.$fid.' AND `catid`='.$catid;
        $db->setQuery($query);
        $db->query();

        $return->valid = "true";
        $return->msg = "Field unassigned from category successfully!";

        return $return;
    }

    /*
     * Removes a field and all its indices from the database
     */
    public function delete()
    {
        $return = new stdClass();
        // Delete from Database
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $fid = (int) $jinput->get('fid');
        if($fid!=0) {
            // Get from Database
            $db = JFactory::getDBO();
            $query = 'SELECT * FROM #__jifields WHERE `id`="'.$fid.'"';
            $db->setQuery( $query );
            $field = $db->loadObject();

            // Delete field
            $query = 'DELETE FROM #__jifields WHERE `id`="'.$fid.'"';
            $db->setQuery($query);
            if(!$db->query()) {
                $this->setError($this->getErrorMsg());
                $return->valid = "false";
                $return->msg = "Unable to delete this field. Reason: Database error.";
            } else {
                // Delete field maps
                $query = 'DELETE FROM #__jifields_map WHERE `fid`="'.$fid.'"';
                $db->setQuery($query);
                $db->query();
                $return->valid = "true";
                $return->msg = $field->title." deleted successfully!";
            }
        } else {
            $return->valid = "false";
            $return->msg = "Unable to delete this field. Reason: No field selected.";
        }
        return $return;
    }

    public function objectToUpdate($data)
    {
        $variables = get_object_vars($data);
        $keys = array_keys($variables);
        $cols = count($variables);
        $row = "";
        for ($i=0; $i<$cols; $i++){
            if ($i<($cols-1)){
                $col = "`".$keys[$i]."`='".$variables[$keys[$i]]."', ";
            } else {
                $col = "`".$keys[$i]."`='".$variables[$keys[$i]]."'";
            };
            $row = $row.$col;
        }
        return $row;
    }

    public function arrayToUpdate($variables)
    {
        $keys = array_keys($variables);
        $cols = count($variables);
        $row = "";
        for($i=0; $i<$cols; $i++){
            if ($i<($cols-1)){
                $col = "`".$keys[$i]."`='".$variables[$keys[$i]]."', ";
            } else {
                $col = "`".$keys[$i]."`='".$variables[$keys[$i]]."'";
            }
            $row = $row.$col;
        }
        return $row;
    }
}