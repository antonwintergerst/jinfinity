<?php 
/**
 * @version     $Id: modules.php 078 2013-10-29 11:25:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 Only
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class ModulesExporter extends JiExporter {
    public function process() {
        if(!isset($this->extensions)) $this->extensions = array();

        if($this->currentpass==0) {
            $this->setStatus(array('msg'=>'Processing modules in Filesystem'));
            $rows = $this->moduleExtProcessor();
            if(count($rows)>0) {
                $this->setStatus(array('msg'=>'Total modules found and included: '.count($rows)));
                foreach($rows as $row) $this->extensions[] = $row;
            } else {
                $this->setStatus(array('msg'=>'No modules found!'));
            }
        }

        parent::process(false);
    }
    public function buildExportQuery() {
        $query = 'SELECT SQL_CALC_FOUND_ROWS * FROM #__'.$this->dbtable.' WHERE `client_id`=0';
        return $query;
    }
    function moduleExtProcessor($rows=null) {
        $items = array();
        jimport('joomla.filesystem.folder');
        $dirs = JFolder::folders( JPATH_ROOT.'/modules' );
        $lang = JFactory::getLanguage();

        foreach ($dirs as $dir)
        {
            if (substr( $dir, 0, 4 ) == 'mod_')
            {
                $files              = JFolder::files( JPATH_ROOT.'/modules/'.$dir, '^([_A-Za-z0-9]*)\.xml$' );
                $module             = new stdClass;
                $module->file       = $files[0];
                $module->module     = str_replace( '.xml', '', $files[0] );
                $module->path       = JPATH_ROOT.'/modules/'.$dir;
                $items[]          = $module;

                $lang->load( $module->module, JPATH_ROOT );
            }
        }
        foreach($items as $i => $row) {
            if ($row->module == '') {
                $items[$i]->name     = 'custom';
                $items[$i]->module   = 'custom';
                $items[$i]->descrip  = 'Custom created module, using Module Manager `New` function';
            } else {
                $data = JApplicationHelper::parseXMLInstallFile( $row->path.DS.$row->file);
                if ( $data['type'] == 'module' ) {
                    $items[$i]->name     = $data['name'];
                    $items[$i]->descrip  = $data['description'];
                }
            }
        }
        $this->processed = 0;
        $this->start = 0;
        $this->total = count($items);
        if($rows==null) $rows = array();
        if($items!=null) {
            foreach($items as $item) {
                // Add to extensions table
                $row = array();
                $row[0] = ''; //extension_id
                $row[1] = $item->module; //name
                $row[2] = 'module'; //type
                $row[3] = $item->module; //element
                $row[4] = ''; //folder
                $row[5] = 1; //client_id
                $row[6] = 1; //enabled
                $row[7] = 1; //access
                $row[8] = '0'; //protected
                $row[9] = ''; //manifest_cache
                $row[10] = ''; //params
                $row[11] = ''; //custom_data
                $row[12] = ''; //system_data
                $row[13] = 0; //checked_out
                $row[14] = '0000-00-00 00:00:00'; //checked_out_time
                $row[15] = 0; //ordering
                $row[16] = 0; //state
                $rows[] = $row;
                $this->processed++;
                $this->setStatus(array('msg'=>'Processed module '.$this->processed.' / '.$this->total));
            }
        }
        return $rows;
    }
}