<?php
/**
 * @version     $Id: jicustomfields.php 104 2014-12-12 11:58:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 System Plugin
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// include global shortcuts
require_once(JPATH_SITE.'/components/com_jicustomfields/helpers/jifieldhelper.php');

class plgSystemJiCustomFields extends JPlugin
{
    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $lang = JFactory::getLanguage();
        $lang->load('com_jicustomfields');
    }

    public function getFieldsModel() {
        // Load Fields Model
        if(isset($this->FieldsModel)) {
            return $this->FieldsModel;
        } else {
            require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
            if(version_compare(JVERSION, '3', 'ge')) {
                $this->FieldsModel = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
            } else {
                $this->FieldsModel = JModel::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
            }
            $this->FieldsModel->setState('filter.published', 1);
            return $this->FieldsModel;
        }
    }

    public function onAfterRoute()
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;

        // Get JiCustomFields Parameters
        $jiparams = JComponentHelper::getParams('com_jicustomfields');

        $view = $jinput->get('view');
        $task = $jinput->get('task', '');
        if($view=='form' && $task=='') {
            // Get the current component
            $option = $jinput->get('option');
            $format = $jinput->get('format');

            // Get the Application
            $app = JFactory::getApplication();
            // Check that this is not the backend and the page is of the content component
            if($app->getName()=='site' && $option=='com_content' && $format==null) {
                if($view=='form' && $task=='') {
                    // Article Form
                }
                // Article Assignment
                elseif($view=='article') {
                    $assignto = $jiparams->get('assigntoarticles');
                    // Assign To: 'none'. Break if no articles are to use JiCustomFields
                    if($assignto=='none') return;
                    // Assign To: 'all'. Ignore other logic as all articles are to use JiCustomFields
                    if($assignto!='all') {
                        $article = (int) $jinput->get('id');
                        $selected = explode(',', $jiparams->get('selectedarticles'));
                        // Assign To: 'include'. Break if article is not included in the selection
                        if($assignto=='include' && !in_array($article, $selected)) return;
                        // Assign To: 'exclude'. Break if article is included in the selection
                        if($assignto=='exclude' && in_array($article, $selected)) return;
                    }
                }
                // Category Assignment
                elseif($view=='category') {
                    $assignto = $jiparams->get('assigntocategories');
                    // Assign To: 'none'. Break if no categories are to use JiCustomFields
                    if($assignto=='none') return;
                    // Assign To: 'all'. Ignore other logic as all categories are to use JiCustomFields
                    if($assignto!='all') {
                        $category = (int) $jinput->get('id');
                        $selected = explode(',', $jiparams->get('selectedcategories'));
                        // Assign To: 'include'. Break if category is not included in the selection
                        if($assignto=='include' && !in_array($category, $selected)) return;
                        // Assign To: 'exclude'. Break if category is included in the selection
                        if($assignto=='exclude' && in_array($category, $selected)) return;
                    }
                }

                // Change active component to com_jicustomfields
                $jinput->set('option', 'com_jicustomfields');
            }
        }
        return;
    }

    public function onAfterDispatch()
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;

        $view = $jinput->get('view');
        $task = $jinput->get('task', '');
        if($view=='form' && $task=='') {
            $option = $jinput->get('option');
            // Tell JiCustomFields we're cloaking
            if($option!='com_jicustomfields') $jinput->set('cloaked_option', 'com_jicustomfields');
            // Cloak com_jicustomfields with com_content
            if($option=='com_jicustomfields') $jinput->set('option', 'com_content');
        }
    }

    public function onContentBeforeDisplay($context, &$article, &$params, $limitstart = 0)
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;

        // Get JiCustomFields Component Parameters
        $jiparams = JComponentHelper::getParams('com_jicustomfields');
        $cloaking = $jinput->get('cloaking');

        $app = JFactory::getApplication();
        if($cloaking!=true && $app->isSite() && $jiparams->get('renderEvent', 'beforeDisplay')=='beforeDisplay') {
            $this->jiCustomFields($context, $article, $params, $limitstart);
        }
    }

    public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        // Get the Application
        $jiparams = JComponentHelper::getParams('com_jicustomfields');
        $app = JFactory::getApplication();
        if($app->isSite() && $context!='com_content.article'){// && $jiparams->get('renderEvent', 'beforeDisplay')=='prepare') {
            $this->jiCustomFields($context, $article, $params, $limitstart);
        }
    }

    public function jiCustomFields($context, &$article, &$params, $limitstart = 0)
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->get('option');
        $view = $jinput->get('view');

        $pagecontext = '';
        if($option=='com_content' && ($view=='category' || $view=='featured') || ($option=='com_jicustomfields' && $view=='search') || ($option=='com_jicustomfields' && $view=='category')) {
            $pagecontext = 'com_content.category';
        } elseif($option=='com_content' && $view=='article') {
            $pagecontext = 'com_content.article';
        }
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
        if(version_compare(JVERSION, '3', 'ge')) {
            $model = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
        } else {
            $model = JModel::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
        }
        $model->setState('filter.published', 1);
        $model->renderOutput($article, $context, $pagecontext);
    }

    public function onContentPrepareForm($form, $data)
    {
        if (!($form instanceof JForm)) {
            $this->_subject->setError('JERROR_NOT_A_FORM');
            return false;
        }
        $jiparams = JComponentHelper::getParams('com_jicustomfields');
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $appname = $app->getName();

        // set editmode
        $option = $jinput->get('option');
        if($appname=='administrator' && $option=='com_jicustomfields') {
            // com_jicustomfields management
            $editmode = 'admin';
        } else {
            if($appname=='administrator' && $jiparams->get('fields_admin_manager', 1)==1) {
                // admin management
                $editmode = 'admin';
            } elseif($jiparams->get('fields_site_manager', 1)==1) {
                // front-end management
                $editmode = 'admin';
            } else {
                $editmode = 'site';
            }
        }
        if($form->getName()=='com_content.article') {
            // Load XML Fields
            if($editmode=='admin') {
                $form->loadFile(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields'.DS.'fieldsformadmin.xml', false);
            } else {
                $form->loadFile(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields'.DS.'fieldsform.xml', false);
            }
            if(is_object($data)) {
                // Get Field Values for article
                //$values = $this->getFieldsModel()->getValues($data, 'article');
                // Update Item Field Data
                $attribs = $data->attribs;
                if(is_string($attribs)) $attribs = json_decode($attribs, true);
                if(!is_array($attribs)) $attribs = array();
                $attribs['jifields'] = array('id'=>$data->get('id'),'catid'=>$data->get('catid'));
                $data->set('attribs', $attribs);
            }
        } elseif($form->getName()=='com_categories.categorycom_content') {
            // Load XML Fields
            /*$form->loadFile(JPATH_SITE.'/administrator/components/com_jicustomfields/fields/customfields.category.xml', true);

            // Update Item Field Data
            $values = $this->getFieldsModel()->getValues($data, 'article');
            if($values!=null) {
                $attribs = $data->attribs;
                $attribs['customfields'] = json_decode($values, true);
                $data->set('attribs', $attribs);
            }*/
        }

        return true;
    }
    public function onContentBeforeSave($context, $article, $isNew) {
    }
    public function onContentAfterSave($context, $article, $isNew)
    {
        $aid = $article->id;
        // Save Custom Fields
        $this->getFieldsModel()->store($article, 'com_content.article');
        return true;
    }
}