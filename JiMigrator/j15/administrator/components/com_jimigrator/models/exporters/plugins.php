<?php 
/**
 * @version     $Id: plugins.php 078 2013-10-29 11:25:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 Only
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class PluginsExporter extends JiExporter {
    /* >>> PRO >>> */
    public function process() {
        if(!isset($this->extensions)) $this->extensions = array();

        if($this->currentpass==0) {
            $this->setStatus(array('msg'=>'Processing plugins in Filesystem'));
            $rows = $this->pluginExtProcessor();
            if(count($rows)>0) {
                $this->setStatus(array('msg'=>'Total plugins found and included: '.count($rows)));
                foreach($rows as $row) $this->extensions[] = $row;
            } else {
                $this->setStatus(array('msg'=>'No plugins found!'));
            }
        }

        parent::process(false);
    }
    function pluginExtProcessor($rows=null) {
        if($rows==null) $rows = array();
        $db = JFactory::getDBO();
        $query = 'SELECT SQL_CALC_FOUND_ROWS * FROM #__plugins';
        // Set Query
        $db->setQuery($query, $this->start, $this->limit);
        $items = $db->loadObjectList();
        // Check there are no errors
        if($db->getErrorMsg()) {
            // Diagnostics
            $this->setStatus(array('msg'=>$db->getErrorMsg()));
        }
        if($items!=null) {
            if($this->total==null) {
                // Calculate total items
                $db->setQuery('SELECT FOUND_ROWS();');
                $this->total = $db->loadResult();
            }
            foreach($items as $item) {
                $row = array();
                $row[0] = ''; //extension_id
                $row[1] = $item->name; //name
                $row[2] = 'plugin'; //type
                $row[3] = $item->element; //element
                $row[4] = $item->folder; //folder
                $row[5] = $item->client_id; //client_id
                $row[6] = $item->published; //enabled
                if(!isset($item->access) || (isset($item->access) && $item->access==0)) $item->access = 1;
                $row[7] = $item->access; //access
                $row[8] = '0'; //protected
                $row[9] = ''; //manifest_cache
                $row[10] = $item->params; //params
                $row[11] = ''; //custom_data
                $row[12] = ''; //system_data
                $row[13] = $item->checked_out; //checked_out
                $row[14] = $item->checked_out_time; //checked_out_time
                $row[15] = $item->ordering; //ordering
                $row[16] = 0; //state
                $rows[] = $row;
            }
            $this->processed = $this->processed + count($items);
            $this->setStatus(array('msg'=>'Processed plugins '.$this->start.' - '.($this->start+count($items)).' / '.$this->total));
            if($this->processed<$this->total) {
                // Process the next batch
                $this->start = $this->start + $this->limit;
                // Wait a bit before starting the next batch
                usleep(250000);
                $rows = $this->pluginExtProcessor($rows);
            }
        }
        return $rows;
    }
    /* <<< PRO <<< */
}