<?php 
/**
 * @version     $Id: templates.php 078 2013-10-29 11:25:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5 Only
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class TemplatesExporter extends JiExporter {
    /* >>> PRO >>> */
    public function process() {
        parent::process(true);

        $this->dbtable = null;

        if(!isset($this->extensions)) $this->extensions = array();

        if($this->currentpass==0) {
            $this->setStatus(array('msg'=>'Processing templates in Filesystem'));
            $rows = $this->templateExtProcessor();
            if(count($rows)>0) {
                $this->setStatus(array('msg'=>'Total templates found and included: '.count($rows)));
                foreach($rows as $row) $this->extensions[] = $row;
            } else {
                $this->setStatus(array('msg'=>'No templates found!'));
            }
        }

        $data = $this->buildEndProcessorData();
        call_user_func_array($this->complete, array($data));
    }
    function templateExtProcessor($rows=null) {
        // Use core template helper to find templates
        require_once(JPATH_SITE.'/administrator/components/com_templates/helpers/template.php');
        $templateHelper = new TemplatesHelper();
        $items = $templateHelper->parseXMLTemplateFiles(JPATH_SITE.'/templates');
        $this->processed = 0;
        $this->start = 0;
        $this->total = count($items);
        if($rows==null) $rows = array();
        if($items!=null) {
            foreach($items as $item) {
                $row = array();
                $row[0] = ''; //extension_id
                $row[1] = $item->name; //name
                $row[2] = 'template'; //type
                $row[3] = $item->name; //element
                $row[4] = ''; //folder
                $row[5] = 0; //client_id
                $row[6] = 1; //enabled
                $row[7] = 1; //access
                $row[8] = '0'; //protected
                $row[9] = ''; //manifest_cache
                $row[10] = '{}'; //params
                $row[11] = ''; //custom_data
                $row[12] = ''; //system_data
                $row[13] = $item->checked_out; //checked_out
                $row[14] = '0000-00-00 00:00:00'; //checked_out_time
                $row[15] = 0; //ordering
                $row[16] = 0; //state
                $rows[] = $row;
                
                $this->processed++;
                $this->setStatus(array('msg'=>'Processed template '.$this->processed.' / '.$this->total));
            }
        }
        return $rows;
    }
    /* <<< PRO <<< */
}