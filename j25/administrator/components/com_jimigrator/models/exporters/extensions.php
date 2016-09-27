<?php 
/**
 * @version     $Id: extensions.php 070 2013-10-29 12:10:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die;
/* >>> PRO >>> */

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class ExtensionsExporter extends JiExporter {
    public function process($bypass=true) {
        parent::process($bypass);

        if(isset($this->tables[0])) {
            $this->dbtable = $this->tables[0]['name'];

            $this->setStatus(array('msg'=>'Starting '.$this->dbtable.' Export'));

            // Add Columns
            $columns = $this->getColumns();
            $this->setStatus(array('msg'=>'Adding '.$this->dbtable.' columns to CSV'));
            $this->writeToCSV($columns);

            // Add Rows
            if(!isset($this->extensions)) $this->extensions = array();
            $rows = $this->extensions;
            if(count($rows)>0) {
                $this->setStatus(array('msg'=>'Total extensions found and included: '.count($rows)));
                foreach($rows as $row) {
                    $this->writeToCSV($row);
                }
            } else {
                $this->setStatus(array('msg'=>'No extensions found!'));
            }

            $data = $this->buildEndProcessorData();
            call_user_func_array($this->complete, array($data));
        } else {
            call_user_func_array($this->complete, array(null));
        }
    }
}
/* <<< PRO <<< */