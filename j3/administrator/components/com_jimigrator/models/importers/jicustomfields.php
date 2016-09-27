<?php
/**
 * @version     $Id: jicustomfields.php 012 2013-10-08 13:23:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 3.0+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class JiCustomFieldsImporter extends JiImporter {
    /* >>> PRO >>> */
    public function getInputTable() {
        if($this->dbtable=='jinfinity_fields') {
            $dbtable = 'jifields';
        } elseif($this->dbtable=='jinfinity_fields_index') {
            $dbtable = 'jifields_index';
        } elseif($this->dbtable=='jinfinity_fields_values') {
            $dbtable = 'jifields_values';
        }
        return $dbtable;
    }
    /* <<< PRO <<< */
}