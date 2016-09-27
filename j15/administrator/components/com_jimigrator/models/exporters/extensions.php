<?php 
/**
 * @version     $Id: extensions.php 078 2013-10-29 11:25:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 Only
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class ExtensionsExporter extends JiExporter {
    public function process() {
        parent::process(true);
        $this->setStatus(array('msg'=>'Exporting Extensions'));

        if(isset($this->tables[$this->currenttable])) {
            $table = $this->tables[$this->currenttable];
            $dbtable = $table['name'];
            $this->dbtable = $dbtable;
            $primarykey = (isset($table['pkey']))? $table['pkey'] : null;

            $this->setStatus(array('msg'=>'Starting '.$dbtable.' Export'));

            // Add Columns
            $columns = $this->getColumns();
            $this->setStatus(array('msg'=>'Adding '.$this->dbtable.' columns to CSV'));
            $this->writeToCSV($columns, true);

            if(!isset($this->extensions)) $this->extensions = array();
            // Add Rows
            if(count($this->extensions)>0) {
                $this->setStatus(array('msg'=>'Total extensions found and included: '.count($this->extensions)));
                foreach($this->extensions as $row) {
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

    /**
     * Override - static columns from Joomla 3.0
     * @return array
     */
    public function getColumns() {
        $columns = array('extension_id', 'name', 'type', 'element', 'folder', 'client_id', 'enabled', 'access', 'protected', 'manifest_cache', 'params', 'custom_data', 'system_data', 'checked_out', 'checked_out_time', 'ordering', 'state');
        return $columns;
    }
}