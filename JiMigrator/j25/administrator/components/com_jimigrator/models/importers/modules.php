<?php 
/**
 * @version     $Id: modules.php 096 2014-07-22 22:26:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class ModulesImporter extends JiImporter {
    public function process($bypass=false) {
        if(!isset($this->moduleidmap)) {
            $this->moduleidmap = array();
        }
        parent::process($bypass);
    }

    /**
     * Override - only delete frontend modules
     * @param $dbtable
     */
    public function truncateTable($dbtable) {
        if($dbtable=='modules') {
            if($this->shouldTruncateTable($dbtable)) {
                $db = JFactory::getDBO();
                $query = 'DELETE FROM `#__'.$dbtable.'` WHERE `client_id`=0';
                $db->setQuery($query);
                if(!$this->debug) $db->query();
                $this->setStatus(array('msg'=>'Cleared '.$dbtable));

                $this->didTruncateTable($dbtable);
            }
        } elseif($dbtable=='modules_menu') {
            if($this->shouldTruncateTable($dbtable)) {
                $db = JFactory::getDBO();
                $query = 'DELETE mm.* FROM `#__'.$dbtable.'` AS mm';
                $query.= ' LEFT JOIN `#__menu` AS m ON (m.`id`=mm.`moduleid`)';
                $query.= ' WHERE m.`client_id`=0';
                $db->setQuery($query);
                if(!$this->debug) $db->query();
                $this->setStatus(array('msg'=>'Cleared '.$dbtable));

                $this->didTruncateTable($dbtable);
            }
        } else {
            parent::truncateTable($dbtable);
        }
    }

    /**
     * Override - modules mapping
     * @param $item
     */
    public function willImportTableRow(&$item) {
        switch($this->dbtable) {
            case 'modules':
        // 3.0 Mappings
        if(!isset($item->language) || empty($item->language)) $item->language = '*';
        if(!isset($item->access) || empty($item->access)) $item->access = 1;
        if(!isset($item->client_id)) $item->client_id = 0;

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

        if($this->params->get('checkin', 1)==1) {
            if($this->debug && $this->debuglevel==1) $this->setStatus(array('msg'=>'Updating `checked_out` from: '.$item->checked_out.', to: 0, for ID #'.$item->id));
            $item->checked_out = 0;
        }

                // custom module links
                if(isset($item->module) && $item->module=='mod_custom' && $this->params->get('rebuildlinks', 0)==1) {
                    libxml_use_internal_errors(true);

                    $dom = new DOMDocument();
                    $text = $item->content;
                    $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
                    $dom->loadHTML('<div>'.$text.'</div>');

                    // find all link attributes
                    $update = false;
                    foreach($dom->getElementsByTagName('a') as $tag) {
                        $href = $tag->getAttribute('href');
                        if($this->debuglevel==1) $this->setStatus(array('msg'=>'Found internal link: '.$href.' for ID #'.$item->id));
                        $url = parse_url($href);

                        // must have a query element
                        if(!isset($url['query'])) continue;

                        parse_str($url['query'], $query);

                        // must belong to the com_content component
                        if(!isset($query['option']) || $query['option']!='com_content') continue;

                        $replace = false;

                        // remap catids
                        if(isset($query['catid']) && isset($this->catidmap[$query['catid']])) {
                            $query['catid'] = $this->catidmap[$query['catid']];
                            $replace = true;
    }

                        // remap menu itemids
                        if(isset($query['Itemid']) && isset($this->menuidmap[$query['Itemid']])) {
                            $query['Itemid'] = $this->menuidmap[$query['Itemid']];
                            $replace = true;
                        }

                        if($replace) {
                            // rebuild url
                            $newquery = '';
                            foreach($query as $key=>$value) {
                                if($newquery!='') $newquery.= '&';
                                $newquery.= $key.'='.$value;
                            }
                            $url['query'] = $newquery;

                            $newhref = $this->unparse_url($url);
                            if($this->debuglevel==1) $this->setStatus(array('msg'=>'Updating internal link to: '.$newhref.' for ID #'.$item->id));
                            $tag->setAttribute('href', $newhref);
                            $update = true;
                        }
                    }
                    if($update) {
                        $text = preg_replace(array("/^\<\!DOCTYPE.*?<html><body>/si", "!</body></html>$!si"), "", $dom->saveHTML());
                        $text = mb_convert_encoding($text, 'UTF-8', 'HTML-ENTITIES');
                        $text = html_entity_decode($text);
                        $item->content = $text;
                    }
                }
                break;
            case 'modules_menu':
                if(isset($item->moduleid) && isset($this->moduleidmap[$item->moduleid])) {
                    $newmoduleid = $this->moduleidmap[$item->moduleid];
                    $this->setStatus(array('msg'=>'Updating `moduleid` from: '.$item->moduleid.', to: '.$newmoduleid.', for #modules_menu'));
                    $item->moduleid = $newmoduleid;
                }
                if(isset($item->menuid) && isset($this->menuidmap[$item->menuid])) {
                    $newmenuid = $this->menuidmap[$item->menuid];
                    $this->setStatus(array('msg'=>'Updating `menuid` from: '.$item->menuid.', to: '.$newmenuid.', for #modules_menu'));
                    $item->menuid = $newmenuid;
                }
                break;
            default:
                break;
        }
    }

    public function didAppendItem(&$item, $newid)
    {
        if(isset($item->id)) $this->moduleidmap[$item->id] = $newid;
    }

    /**
     * Override - add to modules_menu
     * @param $item
     */
    public function didImportTableRow(&$item) {
        if($this->dbtable=='modules') {
        $db = JFactory::getDBO();
        // Add to modules menu
        $query = 'INSERT INTO #__modules_menu (`moduleid`, `menuid`) VALUES ('.$item->id.', 0)';
        $query.= ' ON DUPLICATE KEY UPDATE `moduleid`='.$item->id.', `menuid`=0';
        $db->setQuery($query);
        if(!$this->debug) $db->query();
        $this->setStatus(array('msg'=>'Creating #__modules_menu entry for ID #'.$item->id));
        // Check there are no errors
        if($db->getErrorMsg()) $this->setStatus(array('msg'=>$db->getErrorMsg()));
    }
}}