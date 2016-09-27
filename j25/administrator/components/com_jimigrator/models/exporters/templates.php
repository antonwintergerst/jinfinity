<?php
/**
 * @version     $Id: templates.php 070 2013-10-29 12:06:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
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
    public function buildExportQuery() {
        if($this->dbtable=='extensions') {
            $query = 'SELECT SQL_CALC_FOUND_ROWS * FROM #__'.$this->dbtable;
            $query.= ' WHERE `type`="template"';
            if($this->params->get('siteonly', 1)==1) $query.= ' AND `client_id`=0';
        } else {
            $query = parent::buildExportQuery();
        }
        return $query;
    }
    public function writeColumnsToCSV() {
        if($this->dbtable!='extensions') parent::writeColumnsToCSV();
    }
    public function shouldExportTableRow(&$item) {
        if($this->dbtable=='extensions') {
            if(!isset($this->extensions)) $this->extensions = array();
            $this->extensions[] = $item;
            return false;
        } else {
            return parent::shouldExportTableRow($item);
        }
    }
    /* <<< PRO <<< */
}