<?php
/**
 * @version     $Id: form.php 041 2014-11-19 11:08:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modelitem');

class JiFormsEmailHandler
{
    private $functions = array('send');
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
    function send($alias,$form=null) {
        if(!isset($form)) {
            require_once(JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'models'.DS.'form.php');
            if(version_compare(JVERSION, '3.0.0', 'ge')) {
                $model = JModelLegacy::getInstance('Form', 'JiFormsModel');
            } else {
                $model = JModel::getInstance('Form', 'JiFormsModel');
            }
            $form = $model->getFormInstance($this->id);
        }
        require_once(JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'models'.DS.'email.php');
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            $model = JModelLegacy::getInstance('Email', 'JiFormsModel', array('ignore_request'=>true));
        } else {
            $model = JModel::getInstance('Email', 'JiFormsModel', array('ignore_request'=>true));
        }
        $model->form = $form;
        $model->sendEmail($alias);
    }
}
class JiFormsModelForm extends JModelItem
{
    protected $_context = 'com_jiforms.form';

    public function setFormState($data, $fields) {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jiforms'.DS.'helpers'.DS.'object.php');
        if($data instanceof JiFormsObject) {
            $data = $data->toArray();
        }
        $app = JFactory::getApplication();
        $app->setUserState('com_jiforms.form.data', $data);

        // merge fields
        $newfields = $app->getUserState('com_jiforms.form.fields', array());
        foreach($fields as $field) {
            if(!in_array($field, $newfields)) $newfields[] = $field;
        }

        $app->setUserState('com_jiforms.form.fields', $newfields);
    }
    public function getFormState() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jiforms'.DS.'helpers'.DS.'object.php');
        $app = JFactory::getApplication();
        $data = $app->getUserState('com_jiforms.form.data');
        $result = array(
            'data'=>new JiFormsObject($data),
            'fields'=>$app->getUserState('com_jiforms.form.fields')
        );
        return $result;
    }
    public function eventHandler($event, $form=null) {
        $app = JFactory::getApplication();
        if(!isset($form)) $form = new stdClass();
        $form->show = true;
        $runaction = true;
        switch($event) {
            case 'fail':
            case 'beforeload':
                $form->show = true;
                $runaction = false;
                if($event=='fail') {
                    $this->beforeLoad(true);
                } else {
                    $this->beforeLoad(false);
                }
                break;
            case 'onload':
                $form->show = false;
                break;
            case 'submit':
            case 'onsubmit':
                $event = 'onsubmit';
                $this->submit();
                $form->show = false;
                $runaction = false;
                $this->actionHandler('aftersubmit');
                break;
            case 'validsuccess':
                $this->actionHandler($event);
                $runaction = false;
                $jinput = JFactory::getApplication()->input;
                if($this->getState('component')) {
                    $url = $this->getURL();
                    $url = JRoute::_('index.php?'.$url.'&option=com_jiforms&view=form&id='.(int)$this->getState('form.id').'&event=thankyou');
                } else {
                    $url = $this->getURL(array('path'), array('event'));
                    $url = JRoute::_($url.'&event=thankyou');
                }
                $app = JFactory::getApplication();
                $app->redirect($url);
                break;
            case 'validfail':
                $this->actionHandler($event);
                $runaction = false;
                $jinput = JFactory::getApplication()->input;
                if($this->getState('component')) {
                    $url = $this->getURL();
                    $url = JRoute::_('index.php?'.$url.'&option=com_jiforms&view=form&id='.(int)$this->getState('form.id').'&event=fail');
                } else {
                    $url = $this->getURL(array('path'), array('event'));
                    $url = JRoute::_($url.'&event=fail');
                }
                $app = JFactory::getApplication();
                $app->redirect($url);
                break;
            case 'thankyou':
                // Clear user state
                $form->show = false;
                $app->setUserState('com_jiforms.form.data', array());

                $hasthankyou = $this->actionHandler($event);
                $runaction = false;
                if(!$hasthankyou) {
                    echo '<div class="jiforms thankyou"><p>Thank you for your enquiry.</p></div>';
                }
                break;
            default:
                $form->show = false;
                break;
        }
        if($runaction) $this->actionHandler($event);
        return $form->show;
    }
    private function actionHandler($event, $form=null) {
        $form = $this->getFormInstance();

        if(!isset($form->id) || (int)$form->id==0) return false;

        require_once(JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'models'.DS.'actions.php');
        $model = JModelLegacy::getInstance('Actions', 'JiFormsModel', array('ignore_request'=>true));
        $model->setState('filter.event', $event);
        $model->setState('filter.fid', $form->id);
        $actions = $model->getItems();
        if(is_array($actions) && count($actions)>0) {
            foreach($actions as $action) {
                // Get content from filesystem
                if(isset($action->alias)) {
                    $app = JFactory::getApplication();
                    $path = JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'views'.DS.'action'.DS.'tmpl'.DS.$action->alias.'.php';
                    // First check for template overrides
                    $stylepath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'com_jiforms'.DS.'action'.DS.$action->alias.'.php';
                    if(file_exists($stylepath)) {
                        $action->content = file_get_contents($stylepath);
                    } elseif(file_exists($path)) {
                        $action->content = file_get_contents($path);
                    }
                }
                eval('?>'.$action->content.'<?php ');
            }
            $this->setFormState($form->data, $form->fields);
            return true;
        } else {
            return false;
        }
    }
    public function getValues($fields) {
        $jinput = JFactory::getApplication()->input;
        $values = array();
        foreach($fields as $field) {
            $values[$field] = $jinput->get($field, '', 'raw');
        }
        return $values;
    }
    public function getFields($form, $values=false) {
        $jinput = JFactory::getApplication()->input;

        $fields = array();
        if(isset($form->content)) {
            $content = $form->content;
            // Strip non-HTML tags
            $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
            $content = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);
            $content = preg_replace('#<\?php(.*?)\?>#is', '', $content);
            $doc = new DOMDocument;
            if(!@$doc->loadhtml($content)) {
                // Unable to process content
            } else {
                $xpath = new DOMXpath($doc);
                foreach($xpath->query('//form//input | //form//textarea | //form//select') as $eInput) {
                    $name = $eInput->getAttribute('name');
                    if(isset($name)) $fields[$name] = $jinput->get($name, '', 'raw');
                }
            }
        }
        return $fields;
    }
    private function getFieldAttribs($form) {
        $fields = array();
        $content = $form->content;

        if(strpos($content, '$form->captcha->html()')!==false) {
            $field = new stdClass();
            $field->name = 'jicaptcha';
            $field->class = 'inputbox required validate captcha';
            $fields[] = $field;
        }

        // Strip non-HTML tags
        $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
        $content = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);
        $content = preg_replace('#<\?php(.*?)\?>#is', '', $content);

        $doc = new DOMDocument;
        if(!@$doc->loadhtml($content)) {
            // Unable to process content
        } else {
            $xpath = new DOMXpath($doc);
            foreach($xpath->query('//form//input | //form//textarea | //form//select') as $eInput) {
                $field = new stdClass();
                $field->name = $eInput->getAttribute('name');
                $field->class = $eInput->getAttribute('class');
                $fields[] = $field;
            }
        }
        return $fields;
    }
    public function beforeLoad($persist=false) {
        $form = $this->getForm();
        if($form) {
            $form->fields = $this->getFields($form, true);
            $form->data = $this->getValues($form->fields);

            $form->data = new JiFormsObject($form->data);
            if($persist) {

            } else {
                $this->setFormState($form->data, $form->fields);
            }
            $this->actionHandler('beforeload');
        }
    }
    public function getFormOnload($pk=null) {
        $form = $this->getForm($pk);

        if($form) {
            $result = $this->getFormState();
            $form->data = $result['data'];
            $form->fields = $result['fields'];

            $this->eventHandler('onload');
        }
        return $form;
    }
    private function submit() {
        $form = $this->getFormInstance(null, false);

        $this->setFormState($form->data, $form->fields);

        $this->actionHandler('onsubmit');

        $jinput = JFactory::getApplication()->input;
        require_once(JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'helpers'.DS.'validator.php');
        $validator = new JiFormsValidator();
        $fieldattribs = $this->getFieldAttribs($form);
        $hascaptcha = false;
        $result = new stdClass();
        $result->valid = true;
        foreach($fieldattribs as $field) {
            if($field->name=='jicaptcha') $hascaptcha = true;
            if(in_array($field->name, $form->fields)) {
                $value = $jinput->get($field->name, null, 'raw');
                if(strstr($field->class, 'validate')!==false) {
                    $check = $validator->validateField($value, $field->class, $field->name);
                    if($check->valid!=true) {
                        $result = $check;
                    }
                }
            }
        }
        if($result->valid && $hascaptcha) {
            $result->valid = $form->captcha->check($jinput->get('jicaptcha', null));
            $result->msg = 'Verification code does not match';
        }

        if($result->valid) {
            $this->eventHandler('validsuccess');
        } else {
            $app = JFactory::getApplication();
            $app->setUserState('com_jiforms.submission.form.data', $form->data);
            JError::raiseWarning(100, $result->msg);
            $this->eventHandler('validfail');
        }
    }

    protected function populateState()
    {
        $app = JFactory::getApplication('site');

        // Load state from the request.
        $pk = $app->input->getInt('id');
        $this->setState('form.id', $pk);
        $this->setState('component', true);

        $offset = $app->input->getUInt('limitstart');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        // TODO: Tune these values based on other permissions.
        $user		= JFactory::getUser();
        if ((!$user->authorise('core.edit.state', 'com_content')) &&  (!$user->authorise('core.edit', 'com_content'))){
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
    public function &getFormInstance($pk=null, $retrieve=true) {
        if(!isset($this->form)) {
            $form = $this->getForm($pk, $retrieve);
            if($retrieve) {
                $result = $this->getFormState();
                $form->data = $result['data'];
                $form->fields = $result['fields'];
            }
            $this->form = $form;
        }
        return $this->form;
    }
    public function &getForm($pk=null, $retrieve=true)
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jiforms'.DS.'helpers'.DS.'object.php');

        $pk = (!empty($pk)) ? $pk : (int) $this->getState('form.id');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {

            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select('f.*');
                $query->from('#__jiforms AS f');

                $query->where('f.id = ' . (int) $pk);

                // Filter by start and end dates.
                $nullDate = $db->Quote($db->getNullDate());
                $date = JFactory::getDate();

                $nowDate = $db->Quote($date->toSql());

                $query->where('(f.publish_up = ' . $nullDate . ' OR f.publish_up <= ' . $nowDate . ')');
                $query->where('(f.publish_down = ' . $nullDate . ' OR f.publish_down >= ' . $nowDate . ')');

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(f.state = ' . (int) $published . ' OR f.state =' . (int) $archived . ')');
                }

                $db->setQuery($query);

                $form = $db->loadObject();

                if (empty($form)) {
                    return;
                    return JError::raiseError(404, JText::_('COM_JIFORMS_ERROR_FORM_NOT_FOUND'));
                }

                // Check for published state if filter set.
                if (((is_numeric($published)) || (is_numeric($archived))) && (($form->state != $published) && ($form->state != $archived))) {
                    return JError::raiseError(404, JText::_('COM_JIFORMS_ERROR_FORM_NOT_FOUND'));
                }

                // Convert parameter fields to objects.
                $registry = new JRegistry;
                $registry->loadString($form->attribs);

                // Get content from filesystem
                if(isset($form->alias)) {
                    $app = JFactory::getApplication();
                    $path = JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'views'.DS.'form'.DS.'tmpl'.DS.$form->alias.'.php';
                    // First check for template overrides
                    $stylepath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'com_jiforms'.DS.'form'.DS.$form->alias.'.php';
                    if(file_exists($stylepath)) {
                        $form->content = file_get_contents($stylepath);
                    } elseif(file_exists($path)) {
                        $form->content = file_get_contents($path);
                    }
                }

                $this->prepareForm($form);

                // Attach email handler
                $handler = new JiFormsEmailHandler();
                $handler->id = $pk;
                $form->email = $handler;

                // Attach captcha handler
                require_once(JPATH_SITE.DS.'components'.DS.'com_jiforms'.DS.'helpers'.DS.'captcha.php');
                $captchaHandler = new JiFormsCaptchaHandler();
                $form->captcha = $captchaHandler;

                $result = $this->getFormState();
                $form->fields = $result['fields'];
                $form->data = $result['data'];
                if($retrieve) {



                }

                if(!isset($form->data)) {
                    $form->data = $this->getFields($form, true);
                    $form->data = new JiFormsObject($form->data);
                }
                if(!isset($form->fields) && $form->data instanceof JiFormsObject) {
                    $form->fields = array_keys($form->data->toArray());
                }

                $this->_item[$pk] = $form;
            }
            catch (Exception $e)
            {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    JError::raiseError(404, $e->getMessage());
                }
                else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }
        return $this->_item[$pk];
    }
    public function prepareForm(&$form) {
        /*if(isset($form->content)) {
            $content = $form->content;
            // Find all form tags
            $content = preg_replace_callback('@<form\s.*>.*<\/form>@siU', array(&$this,'replaceForm'), $content);
            $form->content = $content;
        }*/
        $jinput = JFactory::getApplication()->input;
        if($this->getState('component')) {
            $url = $this->getURL();
            $form->url = 'index.php?'.$url.'&option=com_jiforms&view=form&id='.(int)$this->getState('form.id');
        } else {
            $url = $this->getURL(array('path'), array('event'));
            $form->url = $url.'&fid='.$this->getState('form.id');
        }
    }
    private function getURL($parts=array('query','fragment'), $skipvars=array('option','view','id','event')) {
        // Create new action URL
        $uri = JURI::getInstance();
        foreach($skipvars as $skipvar) {
            $uri->delVar($skipvar);
        }

        $url = $uri->toString($parts);
        //if(strstr($url, '?')==false) $url.= '?';
        //$url = str_replace('?', '', $url);
        return $url;
    }
    public function replaceForm($formmatch) {
        $jinput = JFactory::getApplication()->input;
        if($this->getState('component')) {
            $url = $this->getURL();
            $url = JRoute::_('index.php?'.$url.'&option=com_jiforms&view=form&id='.(int)$this->getState('form.id').'&event=submit');
        } else {
            $url = $this->getURL(array('path'), array('event'));
            $url = JRoute::_($url.'&event=submit&fid='.$this->getState('form.id'));
        }

        // Replace action URL
        $form = $formmatch[0];
        $form = preg_replace('@\s(action)="[^"]+"@', ' action="'.$url.'"', $form);
        return $form;
    }
    public function getId($alias) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('f.id');
        $query->from('#__jiforms AS f');

        $query->where('f.alias = '.$db->quote($alias));
        $db->setQuery($query);

        $id = $db->loadResult();
        return $id;
    }
}