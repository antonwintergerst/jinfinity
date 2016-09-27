<?php 
/**
 * @version     $Id: advancedmodules.php 087 2014-07-21 21:34:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 2.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class AdvancedModulesImporter extends JiImporter {
    /* >>> PRO >>> */
    /**
     * Users Override
     * @param $item
     */
    public function willImportTableRow(&$item) {
        // convert old params to json serialised params
        $oldparams = json_decode($item->params);
        if($oldparams==null && $item->params!=null) {
            $newparams = array();
            // Read params
            $params = explode("\n", $item->params);
            if(count($params>0)) {
                foreach($params as $param) {
                    if(strlen(trim($param))>0) {
                        $parts = explode('=', $param);
                        // -1 is an incompatible Joomla 1.5 variable for menu_image
                        if($parts[0]=='menu_image' && isset($parts[1]) && $parts[1]=='-1') $parts[1] = "";
                        // Add parameter to newparams
                        $newparams[$parts[0]] = isset($parts[1])? $parts[1] : '';
                    }
                }
            }
            $newparams = json_encode($newparams);
            if($this->debug && $this->debuglevel==1) {
                $this->setStatus(array('msg'=>'Converting params from: '.$item->params.' to: '.$newparams));
            }
            $item->params = $newparams;
        }

        if(isset($item->moduleid) && isset($this->moduleidmap[$item->moduleid])) {
            $newmoduleid = $this->moduleidmap[$item->moduleid];
            $this->setStatus(array('msg'=>'Updating `moduleid` from: '.$item->moduleid.', to: '.$newmoduleid.', for #modules_menu'));
        }

        // remap ids
        $params = json_decode($item->params, true);
        if($params!=null) {
            if(isset($params['assignto_menuitems_selection'])) {
                $newids = array();
                foreach($params['assignto_menuitems_selection'] as $id) {
                    $newids[] = (isset($this->menuidmap[$id]))? $this->menuidmap[$id] : $id;
                }
                $this->setStatus(array('msg'=>'Remapping menu item ids from: '.implode(', ', $params['assignto_menuitems_selection']).' to: '.implode(', ', $newids)));
                $params['assignto_menuitems_selection'] = $newids;
            }
            if(isset($params['assignto_cats_selection'])) {
                $newids = array();
                foreach($params['assignto_cats_selection'] as $id) {
                    $newids[] = (isset($this->catidmap[$id]))? $this->catidmap[$id] : $id;
                }
                $this->setStatus(array('msg'=>'Remapping category ids from: '.implode(', ', $params['assignto_cats_selection']).' to: '.implode(', ', $newids)));
                $params['assignto_cats_selection'] = $newids;
            }
            $item->params = json_encode($params);
        }
    }
    /* <<< PRO <<< */
}