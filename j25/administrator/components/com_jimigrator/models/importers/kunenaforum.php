<?php 
/**
 * @version     $Id: kunenaforum.php 092 2013-10-29 10:16:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class KunenaForumImporter extends JiImporter {
    /* >>> PRO >>> */
    /**
     * Kunena Forum Override - Executes before storing in database
     * @param $item
     */
    public function willImportTableRow(&$item) {
        switch($this->dbtable) {
            case 'kunena_attachments':
                if(isset($item->filelocation)) {
                    $pathinfo = pathinfo($item->filelocation);
                    $item->folder = $pathinfo['dirname'];
                    $item->filename = $pathinfo['filename'];
                    $item->filetype = $pathinfo['extension'];
                    //unset($item->filelocation);
                }
                break;
            case 'kunena_categories':
                $item->parent_id = $item->parent;
                //unset($item->parent);

                break;
            default:
                break;
        }
    }
    /* <<< PRO <<< */
}