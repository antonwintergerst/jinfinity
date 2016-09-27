<?php
/**
 * @version     $Id: jobfinderitemstocontent.php 032 2014-11-11 20:21:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');
require_once(dirname(__FILE__).DS.'content.php');

class JobFinderItemsToContentImporter extends ContentImporter {
    /* >>> PRO >>> */
    public function importTable($dbtable, $primarykey = null)
    {
        if($dbtable=='jobfinder_items') {
            parent::importTable($dbtable, $primarykey);
        } else {
            // ignore other tables
            $this->didCompletePass();
        }
    }
    /**
     * Map jobfinder items to content table
     * @return string
     */
    public function getInputTable()
    {
        $dbtable = 'content';
        return $dbtable;
    }

    public function willImportTableRow(&$item)
    {
        $item->title = htmlspecialchars_decode($item->title);
        $item->introtext = htmlspecialchars_decode($item->introtext);
        $item->fulltext = htmlspecialchars_decode($item->fulltext);
        //$item->introtext = '<p><img src="'.$item->job_image.'" alt="" />'.$item->introtext.'</p>';
        $oldtitle = $item->title;

        parent::willImportTableRow($item);
        if($item->title!=$oldtitle) $this->setStatus(array('msg'=>'Updating `title` from: '.$item->title.', to: '.$oldtitle.', for ID #'.$item->id));
        $item->title = $oldtitle;
    }
    /**
     * Map jobfinder items to joomla content
     * @param $item
     */
    public function didImportTableRow(&$item)
    {
        // create custom field options as required
        $this->setStatus(array('msg'=>'Preparing to create field values for ID #'.$item->id));

        // job logo #image
        if(isset($item->job_image) && $logo = $this->get_jifield('logo')) {
            $this->setStatus(array('msg'=>'Setting '.$logo->get('alias').' value to: '.$item->job_image.' for ID #'.$item->id));

            if(file_exists(JPATH_SITE.'/'.$item->job_image.'_custom.jpg')) {
                $this->store_jifieldvalue($logo->get('id'), $item->id, $item->job_image.'_custom.jpg');
            } else {
                $this->store_jifieldvalue($logo->get('id'), $item->id, $item->job_image.'_original.jpg');
            }
        }

        // job indigenous
        if(strpos(strtolower($item->title), 'indigenous')!==false && $indigenousjob = $this->get_jifield('indigenousjob')) {
            $this->setStatus(array('msg'=>'Setting '.$indigenousjob->get('alias').' value to: indigenous for ID #'.$item->id));

            $this->store_jifieldvalue($indigenousjob->get('id'), $item->id, 'indigenous');
        }

        // job agent #select
        if(isset($item->job_agent) && $agentemail = $this->get_jifield('agentemail')) {
            $this->setStatus(array('msg'=>'Setting '.$agentemail->get('alias').' value to: '.$item->job_agent.' for ID #'.$item->id));

            $this->store_jifieldvalue($agentemail->get('id'), $item->id, $item->job_agent);
        }

        // job company #textfield
        if(isset($item->job_company) && $employer = $this->get_jifield('employer')) {
            $this->setStatus(array('msg'=>'Setting '.$employer->get('alias').' value to: '.$item->job_company.' for ID #'.$item->id));

            $this->store_jifieldvalue($employer->get('id'), $item->id, $item->job_company);
        }

        // job industry #select
        if(isset($item->job_industry) && $legacy_industry = $this->get_jifield('legacy-industry')) {
            $this->setStatus(array('msg'=>'Setting '.$legacy_industry->get('alias').' value to: '.$item->job_industry.' for ID #'.$item->id));

            $this->store_jifieldoption($legacy_industry, $item->job_industry);
            $this->store_jifieldvalue($legacy_industry->get('id'), $item->id, JApplicationHelper::stringURLSafe($item->job_industry));
        }

        // job sector #select
        if(isset($item->job_sector) && $legacy_sector = $this->get_jifield('legacy-sector')) {
            $this->setStatus(array('msg'=>'Setting '.$legacy_sector->get('alias').' value to: '.$item->job_sector.' for ID #'.$item->id));

            $this->store_jifieldoption($legacy_sector, $item->job_sector);
            $this->store_jifieldvalue($legacy_sector->get('id'), $item->id, JApplicationHelper::stringURLSafe($item->job_sector));
        }

        // job state #select
        if(isset($item->job_state) && $legacy_state = $this->get_jifield('legacy-state')) {
            $this->setStatus(array('msg'=>'Setting '.$legacy_state->get('alias').' value to: '.$item->job_state.' for ID #'.$item->id));

            $this->store_jifieldoption($legacy_state, $item->job_state);
            $this->store_jifieldvalue($legacy_state->get('id'), $item->id, JApplicationHelper::stringURLSafe($item->job_state));
        }

        // job city #select
        if(isset($item->job_city) && $legacy_location = $this->get_jifield('legacy-location')) {
            $this->setStatus(array('msg'=>'Setting '.$legacy_location->get('alias').' value to: '.$item->job_city.' for ID #'.$item->id));

            $this->store_jifieldoption($legacy_location, $item->job_city);
            $this->store_jifieldvalue($legacy_location->get('id'), $item->id, JApplicationHelper::stringURLSafe($item->job_city));

            // store to new location fields
            if(isset($item->job_state)) {
                $statemap = array(
                    'VIC'=>'locationvic',
                    'NSW'=>'locationnsw',
                    'ACT'=>'locationact',
                    'NT'=>'locationnt',
                    'QLD'=>'locationqld',
                    'SA'=>'locationsa',
                    'TAS'=>'locationtas',
                    'WA'=>'locationwa'
                );
                if(array_key_exists(strtoupper($item->job_state), $statemap) && $state = $this->get_jifield($statemap[strtoupper($item->job_state)])) {
                    $this->setStatus(array('msg'=>'Setting '.$state->get('alias').' value to: '.$item->job_city.' for ID #'.$item->id));

                    $this->store_jifieldoption($state, $item->job_city);
                    $this->store_jifieldvalue($state->get('id'), $item->id, JApplicationHelper::stringURLSafe($item->job_city));
                }
            }
        }

        // job qualification #select
        if(isset($item->job_qualification) && $legacy_qualification = $this->get_jifield('legacy-qualification')) {
            $this->setStatus(array('msg'=>'Setting '.$legacy_qualification->get('alias').' value to: '.$item->job_qualification.' for ID #'.$item->id));

            $this->store_jifieldoption($legacy_qualification, $item->job_qualification);
            $this->store_jifieldvalue($legacy_qualification->get('id'), $item->id, JApplicationHelper::stringURLSafe($item->job_qualification));
        }

        // job type #select
        if(isset($item->job_type) && $legacy_type = $this->get_jifield('legacy-type')) {
            $this->setStatus(array('msg'=>'Setting '.$legacy_type->get('alias').' value to: '.$item->job_type.' for ID #'.$item->id));

            $this->store_jifieldoption($legacy_type, $item->job_type);
            $this->store_jifieldvalue($legacy_type->get('id'), $item->id, JApplicationHelper::stringURLSafe($item->job_type));
        }

        parent::didImportTableRow($item);
    }
    public function store_jifieldoption($JiField, $option)
    {
        $params = $JiField->get('params');
        $options = $params->get('options');
        if(!in_array($option, $options) && trim(strlen($option))>0) {
            $options[JApplicationHelper::stringURLSafe($option)] = $option;
            $params->set('options', $options);

            $params = $params->toJSON();
            $this->setStatus(array('msg'=>'Setting field options to: '.$params.' for field ID #'.$JiField->get('id')));
            $db = JFactory::getDbo();
            $query = "UPDATE #__jifields SET `attribs`='".$params."' WHERE `id`=".(int) $JiField->get('id');
            $db->setQuery($query);
            $db->query();

            /*$field = new stdClass();
            $field->id = $JiField->get('id');
            $field->attribs = $params->toArray();
            require_once(JPATH_SITE.'/administrator/components/com_jicustomfields/models/fields.php');
            $model = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
            $model->storeField($field);*/
        }
    }
    public function store_jifieldvalue($fid, $cid, $value)
    {
        $db = JFactory::getDbo();
        $query = 'INSERT INTO #__jifields_values (`fid`, `cid`, `value`) VALUES ('.(int)$fid.','.(int)$cid.','.$db->quote($value).')';
        $db->setQuery($query);
        $db->query();
    }
    public function get_jifield($field_alias)
    {
        try {
            // get field data
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('f.*');
            $query->from('#__jifields AS f');
            $query->where('f.`alias` = '.$db->quote($field_alias));
            $db->setQuery($query);
            $field = $db->loadObject();
            if($field==null) return false;

            require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'helpers'.DS.'field.php');
            $JiFieldHelper = new JiCustomFieldHelper();
            $JiField = $JiFieldHelper->loadType($field);
            if($JiField->get('group')!='system') {
                if(isset($field->attribs)) $JiField->setParams($field->attribs);
                return $JiField;
            }
        } catch (Exception $e) {
        }
        return false;
    }
    /* <<< PRO <<< */
}