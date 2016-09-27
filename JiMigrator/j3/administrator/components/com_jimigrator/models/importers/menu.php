<?php 
/**
 * @version     $Id: menu.php 146 2014-07-24 15:08:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class MenuImporter extends JiImporter {
    /**
     * Override - nested split processor
     */
    public function process($bypass=false) {
        if(!isset($this->menuidmap)) {
            $this->menuidmap = array();
        }
        // This is a memory heavy processer - force a low process limit to avoid reaching the memory limit
        if($this->limit>25) $this->limit = 25;
        // Force processor to iterate over menu three times
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
     * Override - only delete frontend menu items
     * @param $dbtable
     */
    public function truncateTable($dbtable) {
        if($this->shouldTruncateTable($dbtable)) {
            $db = JFactory::getDBO();
            // delete client facing menu items
            $query = 'DELETE FROM `#__'.$dbtable.'` WHERE `client_id`=0 AND NOT (`alias`="root") AND NOT (`title`="Menu_Item_Root") AND `id`!=1';
            $db->setQuery($query);
            if(!$this->debug) $db->query();
            $this->setStatus(array('msg'=>'Cleared '.$dbtable));

            // update auto increment value
            if(!$this->debug) {
                $query = 'SELECT `id` FROM `#__'.$dbtable.'` ORDER BY `id` DESC LIMIT 0, 1';
                $db->setQuery($query);
                $lastid = (int) $db->loadResult();

                $query = 'ALTER TABLE `#__'.$dbtable.'` AUTO_INCREMENT = '.($lastid+1);
                $db->setQuery($query);
                $db->query();
            }

            $this->didTruncateTable($dbtable);
        }
    }

    /**
     * Override - menu mapping
     * @param $item
     */
    public function willImportTableRow(&$item) {
        if(isset($item->name)) {
            if(!isset($item->title)) $item->title = $item->name;
            unset($item->name);
        }
        if(!isset($item->language) || empty($item->language)) $item->language = '*';
        if(!isset($item->access) || empty($item->access)) $item->access = 1;
        if(!isset($item->client_id)) $item->client_id = 0;
        if(isset($item->alias) && $item->alias=='home') $item->default = 1;
        if(isset($item->default) && $item->default==1) $item->language = 'All';
        if(isset($item->parent)) $item->parent_id = $item->parent;

        // only do this processing on the first store
        if($this->currenttable==0) {
            if($this->params->get('clearparams', 0)==1) {
                if(isset($item->params) && json_decode($item->params)==null) {
                    $item->params = '';
                    $this->setStatus(array('msg'=>'Clearing `params` for ID #'.$item->id));
                }
            } elseif($this->params->get('rebuildparams', 1)==1) {
                if(!isset($item->params)) $item->params = null;
                $oldparams = $item->params;
                $item->params = $this->rebuildParams($item->params);
                $this->setStatus(array('msg'=>'Updating `params` (rebuilt using JSON serialization) for ID #'.$item->id));
                if($this->debuglevel>=1) {
                    $this->setStatus(array('msg'=>'Old `params`: '.$oldparams));
                    $this->setStatus(array('msg'=>'New `params`: '.$item->params));
                }
            }

            // override checked out
            if($this->params->get('checkin', 1)==1) {
                if($this->debug && $this->debuglevel==1) $this->setStatus(array('msg'=>'Updating `checked_out` from: '.$item->checked_out.', to: 0, for ID #'.$item->id));
                $item->checked_out = 0;
            }

            if(isset($item->link) && strstr($item->link, 'view=category')!=false) {
                preg_match_all('/([^?&=#]+)=([^&#]*)/', $item->link, $urlvarmatches);
                $urlvars = array_combine($urlvarmatches[1], $urlvarmatches[2]);
                if(isset($urlvars['id']) && isset($this->catidmap[$urlvars['id']])) {
                    $newcatid = $this->catidmap[$urlvars['id']];
                    $oldlink = $item->link;
                    $item->link = str_replace('id='.$urlvars['id'], 'id='.$newcatid, $item->link);
                    $this->setStatus(array('msg'=>'Updating `link` from: '.$oldlink.', to: '.$item->link.', for ID #'.$item->id));
                }
            }

            $item->component_id = $this->resolveComponentID($item);
        }
    }

    /**
     * Override - menu nested split processor
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

                    // exclude root menu item
                    if((isset($item->alias) && $item->alias=='root') || (isset($item->title) && $item->title=='Menu_Item_Root')) {
                        if(isset($item->id) && $item->id!=1) {
                            $this->menuidmap[$item->id] = 1;
                        }
                        $this->setStatus(array('msg'=>'Skipping root menu item found with ID #'.$item->id));
                        continue;
                    }

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
                    if($this->debuglevel>=1) $this->setStatus(array('msg'=>'Temporary alias: '.$alias.' for item ID #'.$item->id));
                    $data['alias'] = $alias;

                    if(!isset($data['menutype'])) $data['menutype'] = 'mainmenu';

                    $oldParentID = (isset($data['parent_id']))? $data['parent_id'] : 0;
                    $newParentID = 1;
                    $data['parent_id'] = $newParentID;
                    //$this->setStatus(array('msg'=>'Updating `parent_id` from: '.$oldParentID.', to: '.$newParentID.', for ID #'.$oldID));

                    // Load Table Instance
                    $menuTable = JTable::getInstance('Menu', 'JTable');
                    $menuTable->reset();

                    // Bind Data
                    if(!$menuTable->bind($data)) {
                        $this->setStatus(array('msg'=>'Warning: failed to bind item for ID #'.$oldID));
                        $this->setStatus(array('msg'=>$menuTable->getError()));
                        continue;
                    }
                    $menuTable->setLocation($menuTable->parent_id, 'last-child');

                    // Store Data
                    if(!$menuTable->store()) {
                        $this->setStatus(array('msg'=>'Warning: failed to save item for ID #'.$oldID));
                        $this->setStatus(array('msg'=>$menuTable->getError()));
                    } else {
                        // Get insert ID
                        $newID = $menuTable->id;
                        $this->setStatus(array('msg'=>'Updating `id` from: '.$oldID.', to: '.$newID.', for ID #'.$oldID));
                        // Update ID
                        $item->id = $newID;
                        $this->menuidmap[$oldID] = $newID;
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

                    // exclude root menu item
                    if((isset($item->alias) && $item->alias=='root') || (isset($item->title) && $item->title=='Menu_Item_Root')) {
                        $this->setStatus(array('msg'=>'Skipping root menu item found with ID #'.$item->id));
                        continue;
                    }

                    if(isset($this->menuidmap[$item->parent_id]) && $item->parent_id!=$this->menuidmap[$item->parent_id]) {
                        $this->setStatus(array('msg'=>'Updating `parent_id` from: '.$item->parent_id.', to: '.$this->menuidmap[$item->parent_id].', for ID #'.$item->id));

                        // use transposed ids
                        $item->id = isset($this->menuidmap[$item->id])? $this->menuidmap[$item->id] : $item->id;
                        $item->parent_id = isset($this->menuidmap[$item->parent_id])? $this->menuidmap[$item->parent_id] : $item->parent_id;

                        // Update parent_id (taking note that the ID of this item may have also shifted)
                        $data = array(
                            'parent_id'=>$item->parent_id,
                            'level'=>null,
                            'path'=>null
                        );

                        // Load Table Instance
                        $menuTable = JTable::getInstance('Menu', 'JTable');
                        $menuTable->reset();
                        if(!$menuTable->load(array('id'=>$item->id))) continue;
                        // Bind Data
                        $menuTable->bind($data);
                        // Store Data
                        $menuTable->store();
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
                    $item->id = isset($this->menuidmap[$item->id])? $this->menuidmap[$item->id] : $item->id;

                    // exclude root menu item
                    if((isset($item->alias) && $item->alias=='root') || (isset($item->title) && $item->title=='Menu_Item_Root')) {
                        $this->setStatus(array('msg'=>'Skipping root menu item found with ID #'.$item->id));
                        continue;
                    }

                    // Load Table Instance
                    $menuTable = JTable::getInstance('Menu', 'JTable');
                    $menuTable->reset();
                    if(!$menuTable->load(array('id'=>$item->id))) {
                        $this->setStatus(array('msg'=>'Warning: unable to load item ID #'.$item->id));
                        continue;
                    }

                    if($this->params->get('resetalias', 1)==1) {
                        // set alias and title
                        $conditions = array();
                        $conditions[] = '`id`!='.(int)$item->id;
                        //if(isset($menuTable->menutype)) $conditions[] = '`menutype`="'.$menuTable->menutype.'"';
                        if(isset($menuTable->parent_id)) $conditions[] = '`parent_id`='.(int)$menuTable->parent_id;
                        if(isset($menuTable->level)) $conditions[] = '`level`='.(int)$menuTable->level;
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
                    if(!$menuTable->bind($data)) {
                        $this->setStatus(array('msg'=>'Warning: failed to bind item for ID #'.$item->id));
                        $this->setStatus(array('msg'=>$menuTable->getError()));
                        continue;
                    }
                    // Store Data
                    if(!$menuTable->store()) {
                        $this->setStatus(array('msg'=>'Warning: failed to save item for ID #'.$item->id));
                        $this->setStatus(array('msg'=>$menuTable->getError()));
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
     * Override - menu nested split processor
     * @return array
     */
    public function buildEndProcessorData() {
        $this->setStatus(array('msg'=>'Rebuilding menu nest...'));
        // Load Table Instance
        $menuTable = JTable::getInstance('Menu', 'JTable');
        $menuTable->reset();
        // Rebuild Nest
        $menuTable->rebuild(1);

        $data = parent::buildEndProcessorData();
        return $data;
    }

    /**
     * Override - remove invalid menu_image param
     * @param $params
     * @return string
     */
    public function rebuildParams($params) {
        if($params!=null && json_decode($params)==null) {
            $newparams = array();
            // Read params
            $params = explode("\n", $params);
            if(count($params>0)) {
                foreach($params as $param) {
                    if(strlen(trim($param))>0) {
                        $parts = explode('=', $param, 2);
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        if(strlen($key)>0 && strlen($value)>0) {
                            // -1 is an incompatible Joomla 1.5 variable for menu_image
                            if($key=='menu_image' && $value=='-1') $value = "";
                            // Add parameter to newparams
                            $newparams[$key] = $value;
                        }
                    }
                }
            }
            $params = (count($newparams)>0)? json_encode($newparams) : '';
        }
        return $params;
    }

    /**
     * Method to find component ID from menu item
     * @param $item
     * @return string
     */
    function resolveComponentID($item) {
        $args = array();
        parse_str(parse_url($item->link, PHP_URL_QUERY), $args);

        if (isset($args['option'])) {
            // Load the language file for the component.
            $lang = JFactory::getLanguage();
            $lang->load($args['option'], JPATH_ADMINISTRATOR, null, false, false)
            ||  $lang->load($args['option'], JPATH_ADMINISTRATOR.'/components/'.$args['option'], null, false, false)
            ||  $lang->load($args['option'], JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
            ||  $lang->load($args['option'], JPATH_ADMINISTRATOR.'/components/'.$args['option'], $lang->getDefault(), false, false);

            // Determine the component id.
            $component = JComponentHelper::getComponent($args['option']);
        }
        if(isset($component->id)) {
            $component_id = $component->id;
            $this->setStatus(array('msg'=>'Resolved component ID to: '.$component_id.' for item ID #'.$item->id));
        } else {
            $component_id = '';
            $this->setStatus(array('msg'=>'Warning! Unable to resolve component ID for item ID #'.$item->id));
        }
        return $component_id;
    }
}