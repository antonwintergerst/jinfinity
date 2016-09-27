<?php
/**
 * @version     $Id: categories.php 107 2014-07-24 15:48:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 3.0+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class CategoriesImporter extends JiImporter {
    /**
     * Override - nested split processor
     */
    public function process($bypass=false) {
        if(!isset($this->catidmap)) {
            $this->catidmap = array();
        }
        // This is a memory heavy processer - force a low process limit to avoid reaching the memory limit
        if($this->limit>25) $this->limit = 25;
        // Force processor to iterate over categories three times
        $table = $this->tables[0];
        $this->tables = array(
            $table,
            $table,
            $table
        );
        parent::process($bypass);
    }

    /**
     * Override - nested split processor
     * @param $dbtable
     * @return bool
     */
    public function shouldTruncateTable($dbtable) {
        if($this->currenttable==0) {
            return parent::shouldTruncateTable($dbtable);
        } else {
            return false;
        }
    }

    /**
     * Override - add default categories
     * @param $dbtable
     */
    public function didTruncateTable($dbtable) {
        // Add default categories
        $db = JFactory::getDBO();
        $query = "INSERT INTO `#__categories` (`id`, `asset_id`, `parent_id`, `lft`, `rgt`, `level`, `path`, `extension`, `title`, `alias`, `note`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`, `metadesc`, `metakey`, `metadata`, `created_user_id`, `created_time`, `modified_user_id`, `modified_time`, `hits`, `language`, `version`) VALUES
(1, 0, 0, 0, 13, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\",\"foobar\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(6, 31, 1, 9, 10, 1, 'uncategorised', 'com_weblinks', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1),
(7, 32, 1, 11, 12, 1, 'uncategorised', 'com_users', 'Uncategorised', 'uncategorised', '', '', 1, 0, '0000-00-00 00:00:00', 1, '{\"target\":\"\",\"image\":\"\"}', '', '', '{\"page_title\":\"\",\"author\":\"\",\"robots\":\"\"}', 42, '2011-01-01 00:00:01', 0, '0000-00-00 00:00:00', 0, '*', 1);";
        $db->setQuery($query);
        if(!$this->debug) $db->query();
        if($db->getErrorMsg()) $this->setStatus(array('msg'=>$db->getErrorMsg()));
        $this->setStatus(array('msg'=>'Created default items for '.$dbtable));
    }

    /**
     * Override - category mapping
     * @param $item
     */
    public function willImportTableRow(&$item) {
        if(!isset($item->language) || empty($item->language)) $item->language = '*';
        if(!isset($item->access) || empty($item->access)) $item->access = 1;

        // only do this processing on the first store
        if($this->currenttable==0) {
            if($this->params->get('clearparams', 0)==1) {
                if(isset($item->params) && json_decode($item->params)==null) {
                    $item->params = '';
                    $this->setStatus(array('msg'=>'Clearing `params` for ID #'.$item->id));
                }
            } elseif($this->params->get('rebuildparams', 1)==1) {
                if(!isset($item->params)) $item->params = null;
                $item->params = $this->rebuildParams($item->params);
                $this->setStatus(array('msg'=>'Updating `params` (rebuilt using JSON serialization) for ID #'.$item->id));
            }

            // override checked out
            if($this->params->get('checkin', 1)==1) {
                if($this->debug && $this->debuglevel==1) $this->setStatus(array('msg'=>'Updating `checked_out` from: '.$item->checked_out.', to: 0, for ID #'.$item->id));
                $item->checked_out = 0;
            }

            // override created by
            if($this->params->get('overridecreated_by', 0)==1) {
                $newcreated_by = $this->params->get('created_by', 0);
                if($newcreated_by!=0) {
                    $this->setStatus(array('msg'=>'Setting `created_user_id` from: '.$item->created_user_id.' to: '.$newcreated_by.' for ID #'.$item->id));
                    $item->created_user_id = $newcreated_by;
                }
            }
        }
    }

    /**
     * Override - category nested split processor
     * @var array $items
     * @var $item database row item
     * @var $db
     * @var int $passtotal
     * @var int $i
     */
    public function importTableProcessor() {
        $params = $this->get('params');
        $this->willBeginPass();

        $items = $this->getItems($this->sourcedir.'/'.$this->dbtable.'.csv', $this->start, ($this->start==0)? $this->limit : $this->limit-1);
        $passtotal = count($items);

        $db = JFactory::getDBO();
        $headers = $this->getColumns();

        if($this->currenttable==0) {
            $this->setStatus(array('msg'=>'Starting new import pass'));
            // First Pass: save data into database
            for($i=0; $i<$passtotal; $i++) {
                if(isset($items[$i])) {
                    // DEBUG LVL1
                    if($this->debuglevel>=1) $this->setStatus(array('msg'=>'Processing '.$this->dbtable.' item '.($i+$this->start).' / '.$this->total));

                    $item = $items[$i];

                    $this->willImportTableRow($item);

                    // Remember old ID for later
                    $oldID = $item->id;

                    $this->setStatus(array('msg'=>'Saving item ID #'.$oldID));
                    $data = (array) $item;

                    // Let Joomla rebuild the hierarchy
                    $data['asset_id'] = null;
                    $data['id'] = null;
                    $data['lft'] = null;
                    $data['level'] = null;
                    $data['rgt'] = null;
                    // make a temporary unique alias
                    list($title, $alias) = $this->resetAlias(date('Y-m-d h:i:s').'_'.$i.rand(), date('Y-m-d h:i:s').'_'.$i, $item->language);
                    $data['alias'] = $alias;

                    if(!isset($data['extension'])) $data['extension'] = 'com_content';

                    $oldParentID = (isset($data['parent_id']))? $data['parent_id'] : 0;
                    $newParentID = 1;
                    $data['parent_id'] = $newParentID;
                    //$this->setStatus(array('msg'=>'Updating `parent_id` from: '.$oldParentID.', to: '.$newParentID.', for ID #'.$oldID));

                    // Load Table Instance
                    $categoryTable = JTable::getInstance('Category', 'JTable');
                    $categoryTable->reset();

                    // Bind Data
                    if(!$categoryTable->bind($data)) {
                        $this->setStatus(array('msg'=>'Warning: failed to bind item for ID #'.$oldID));
                        $this->setStatus(array('msg'=>$categoryTable->getError()));
                        continue;
                    }
                    $categoryTable->setLocation($categoryTable->parent_id, 'last-child');

                    // Store Data
                    if(!$categoryTable->store()) {
                        $this->setStatus(array('msg'=>'Warning: failed to save item for ID #'.$oldID));
                        $this->setStatus(array('msg'=>$categoryTable->getError()));
                    } else {
                        // Get insert ID
                        $newID = $categoryTable->id;
                        $this->setStatus(array('msg'=>'Updating `id` from: '.$oldID.', to: '.$newID.', for ID #'.$oldID));
                        // Update ID
                        $item->id = $newID;
                        $this->catidmap[$oldID] = $newID;
                    }
                }
            }
        } elseif($this->currenttable==1) {
            $this->setStatus(array('msg'=>'Starting new hierarchy pass'));
            // Second Pass: Update Parents
            for($i=0; $i<$passtotal; $i++) {
                if(isset($items[$i])) {
                    // DEBUG LVL1
                    if($this->debuglevel>=1) $this->setStatus(array('msg'=>'Processing '.$this->dbtable.' item '.($i+$this->start).' / '.$this->total));

                    $item = $items[$i];
                    $this->willImportTableRow($item);

                    if(isset($item->parent_id) && isset($this->catidmap[$item->parent_id]) && $item->parent_id!=$this->catidmap[$item->parent_id]) {
                        $this->setStatus(array('msg'=>'Updating `parent_id` from: '.$item->parent_id.', to: '.$this->catidmap[$item->parent_id].', for ID #'.$item->id));

                        // use transposed ids
                        $item->id = isset($this->catidmap[$item->id])? $this->catidmap[$item->id] : $item->id;
                        $item->parent_id = isset($this->catidmap[$item->parent_id])? $this->catidmap[$item->parent_id] : $item->parent_id;

                        // Update parent_id (taking note that the ID of this item may have also shifted)
                        $data = array(
                            'parent_id'=>$item->parent_id,
                            'level'=>null,
                            'path'=>null
                        );

                        // Load Table Instance
                        $categoryTable = JTable::getInstance('Category', 'JTable');
                        $categoryTable->reset();
                        if(!$categoryTable->load(array('id'=>$item->id))) continue;
                        // Bind Data
                        $categoryTable->bind($data);
                        // Store Data
                        $categoryTable->store();
                    }
                }
            }
        } elseif($this->currenttable==2) {
            $this->setStatus(array('msg'=>'Starting new alias pass'));
            // third pass: set title/alias
            for($i=0; $i<$passtotal; $i++) {
                if(isset($items[$i])) {
                    // DEBUG LVL1
                    if($this->debuglevel>=1) $this->setStatus(array('msg'=>'Processing '.$this->dbtable.' item '.($i+$this->start).' / '.$this->total));

                    $item = $items[$i];
                    $this->willImportTableRow($item);

                    // use transposed id
                    $item->id = isset($this->catidmap[$item->id])? $this->catidmap[$item->id] : $item->id;

                    // Load Table Instance
                    $categoryTable = JTable::getInstance('Category', 'JTable');
                    $categoryTable->reset();
                    if(!$categoryTable->load(array('id'=>$item->id))) continue;

                    if($this->params->get('resetalias', 1)==1) {
                        // set alias and title
                        $conditions = array();
                        $conditions[] = '`id`!='.(int)$item->id;
                        if(isset($categoryTable->parent_id)) $conditions[] = '`parent_id`='.(int)$categoryTable->parent_id;
                        if(isset($categoryTable->level)) $conditions[] = '`level`='.(int)$categoryTable->level;
                        list($title, $alias) = $this->resetAlias($item->alias, $item->title, $item->language, $conditions);
                        if($item->title!=$title) $this->setStatus(array('msg'=>'Updating `title` from: '.$item->title.', to: '.$title.', for ID #'.$item->id));
                        if($item->alias!=$alias) $this->setStatus(array('msg'=>'Updating `alias` from: '.$item->alias.', to: '.$alias.', for ID #'.$item->id));
                        $item->title = $title;
                        $item->alias = $alias;
                    }
                    $data = array(
                        'title'=>$item->title,
                        'alias'=>$item->alias
                    );

                    // Bind Data
                    if(!$categoryTable->bind($data)) {
                        $this->setStatus(array('msg'=>'Warning: failed to bind item for ID #'.$item->id));
                        $this->setStatus(array('msg'=>$categoryTable->getError()));
                        continue;
                    }
                    // Store Data
                    if(!$categoryTable->store()) {
                        $this->setStatus(array('msg'=>'Warning: failed to save item for ID #'.$item->id));
                        $this->setStatus(array('msg'=>$categoryTable->getError()));
                    }
                }
            }
        }
        // Free up memory
        unset($items, $db, $i, $columns, $insert, $item, $query, $newid, $columndata, $headers, $key, $value);
        if(function_exists('gc_collect_cycles')) gc_collect_cycles();

        $this->didCompletePass();
    }

    /**
     * Override - category nested split processor
     * @return array
     */
    public function buildEndProcessorData() {
        $this->setStatus(array('msg'=>'Rebuilding category nest...'));
        // Load Table Instance
        $categoryTable = JTable::getInstance('Category', 'JTable');
        $categoryTable->reset();
        // Rebuild Nest
        $categoryTable->rebuild(1);

        $data = parent::buildEndProcessorData();
        return $data;
    }
}