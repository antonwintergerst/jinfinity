<?php 
/**
 * @version     $Id: content.php 096 2014-07-22 22:26:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class ContentImporter extends JiImporter {
    /**
     * Override - prepare catidmap
     */
    public function process($bypass=false) {
        if(!isset($this->catidmap)) {
            $this->catidmap = array();
        }
        parent::process($bypass);
    }
    /**
     * Content Override
     * @param $item
     */
    public function willImportTableRow(&$item) {
        // Preprocessing
        if(!isset($item->language) || empty($item->language)) $item->language = '*';
        if(!isset($item->access) || empty($item->access)) $item->access = 1;
        if($this->params->get('resetalias', 1)==1) {
            $conditions = array();
            if(isset($item->catid)) $conditions[] = '`catid`='.(int)$item->catid;
            list($title, $alias) = $this->resetAlias($item->alias, $item->title, $item->language, $conditions);
            $item->title = $title;
            $item->alias = $alias;
            if($item->title!=$title) $this->setStatus(array('msg'=>'Updating `title` from: '.$item->title.', to: '.$title.', for ID #'.$item->id));
            if($item->alias!=$alias) $this->setStatus(array('msg'=>'Updating `alias` from: '.$item->alias.', to: '.$alias.', for ID #'.$item->id));
        }
        if($this->params->get('clearparams', 1)==1 && json_decode($item->attribs)==null) {
            if(isset($item->attribs)) {
                $item->attribs = '';
                $this->setStatus(array('msg'=>'Clearing `attribs` for ID #'.$item->id));
            }
            if(isset($item->metadata) && json_decode($item->metadata)==null) {
                $item->metadata = '';
                $this->setStatus(array('msg'=>'Clearing `metadata` for ID #'.$item->id));
            }
        } elseif($this->params->get('rebuildparams', 1)==1) {
            if(!isset($item->attribs)) $item->attribs = null;
            $item->attribs = $this->rebuildParams($item->attribs);
            $this->setStatus(array('msg'=>'Updating `attribs` (rebuilt using JSON serialization) for ID #'.$item->id));
            if(isset($item->metadata)) {
                $item->metadata = $this->rebuildParams($item->metadata);
                $this->setStatus(array('msg'=>'Updating `metadata` (rebuilt using JSON serialization) for ID #'.$item->id));
            }
        }

        // override checked out
        if($this->params->get('checkin', 1)==1) {
            if($this->debug && $this->debuglevel==1) $this->setStatus(array('msg'=>'Updating `checked_out` from: '.$item->checked_out.', to: 0, for ID #'.$item->id));
            $item->checked_out = 0;
        }

        if($item->catid==0) {
            $newcatid = 2;
            $this->setStatus(array('msg'=>'Updating `catid` from: '.$item->catid.', to: '.$newcatid.', for ID #'.$item->id));
            $item->catid = $newcatid;
        } elseif(isset($this->catidmap[$item->catid]) && $item->catid!=$this->catidmap[$item->catid]) {
            $newcatid = $this->catidmap[$item->catid];
            $this->setStatus(array('msg'=>'Updating `catid` from: '.$item->catid.', to: '.$newcatid.', for ID #'.$item->id));
            $item->catid = $newcatid;
        }
        // Override Category
        if($this->params->get('overridecategory', 0)==1) {
            $newcatid = $this->params->get('category', 0);
            if($newcatid!=0) {
                $this->setStatus(array('msg'=>'Setting `catid` from: '.$item->catid.' to: '.$newcatid.' for ID #'.$item->id));
                $item->catid = $newcatid;
            }
        }
        if($item->catid==0||$item->catid==1) $item->catid = 2;

        // override created by
        if($this->params->get('overridecreated_by', 0)==1) {
            $newcreated_by = $this->params->get('created_by', 0);
            if($newcreated_by!=0) {
                $this->setStatus(array('msg'=>'Setting `created_by` from: '.$item->created_by.' to: '.$newcreated_by.' for ID #'.$item->id));
                $item->created_by = $newcreated_by;
            }
        }

        $images = array();
        if(isset($item->images)) {
            $imagesdata = json_decode($item->images);
            if($imagesdata!=null) {
                $images = $imagesdata;
            }
        }
        // Intro Image
        if(isset($item->introtext) && $this->params->get('introimgfromtext', 0)==1 && (!isset($images['image_intro']) || empty($images['image_intro']))) {
            $text = $item->introtext;
            // Find first img
            $regex = '#<\img(.*?)src\="(.*?)"(.*?)\>#s';
            preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
            if(isset($matches[0])) {
                $match = $matches[0];
                // Find all IMG attributes
                $imageattribs = array();
                preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $match[0], $iattribmatches, PREG_SET_ORDER);
                if($iattribmatches!=null) {
                    foreach($iattribmatches as $iattribmatch) {
                        if(isset($iattribmatch[1])) $imageattribs[$iattribmatch[1]] = $iattribmatch[2];
                    }
                }
                if(isset($imageattribs['src'])) {
                    $this->setStatus(array('msg'=>'Setting `Intro Image` to: '.$imageattribs['src'].' for ID #'.$item->id));
                    $images['image_intro'] = $imageattribs['src'];
                    if(isset($imageattribs['alt'])) $images['image_intro_alt'] = $imageattribs['alt'];
                    if(isset($imageattribs['alt']) && $imageattribs['alt']!=$imageattribs['src']) $images['image_intro_caption'] = $imageattribs['alt'];
                }
            }
            $imagex = preg_quote($match[0]);
            // Remove surrounding paragraphs
            $text = preg_replace('#<p(.*)>+\s*('.$imagex.')\s*</p>+#i', $match[0], $text);
            $text = preg_replace('#'.$match[0].'#s', '', $text);
            $item->introtext = $text;
        }
        // Full Image
        if(isset($item->fulltext) && $this->params->get('fullimgfromtext', 0)==1 && (!isset($images['image_fulltext']) || empty($images['image_fulltext']))) {
            $text = $item->fulltext;
            // Find first img
            $regex = '#<\img(.*?)src\="(.*?)"(.*?)\>#s';
            preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
            if(isset($matches[0])) {
                $match = $matches[0];
                // Find all IMG attributes
                $imageattribs = array();
                preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $match[0], $iattribmatches, PREG_SET_ORDER);
                if($iattribmatches!=null) {
                    foreach($iattribmatches as $iattribmatch) {
                        if(isset($iattribmatch[1])) $imageattribs[$iattribmatch[1]] = $iattribmatch[2];
                    }
                }
                if(isset($imageattribs['src'])) {
                    $this->setStatus(array('msg'=>'Setting `Fulltext Image` to: '.$imageattribs['src'].' for ID #'.$item->id));
                    $images['image_fulltext'] = $imageattribs['src'];
                    if(isset($imageattribs['alt'])) $images['image_fulltext_alt'] = $imageattribs['alt'];
                    if(isset($imageattribs['alt']) && $imageattribs['alt']!=$imageattribs['src']) $images['image_fulltext_caption'] = $imageattribs['alt'];
                }
            }
            $imagex = preg_quote($match[0]);
            // Remove surrounding paragraphs
            $text = preg_replace('#<p(.*)>+\s*('.$imagex.')\s*</p>+#i', $match[0], $text);
            $text = preg_replace('#'.$match[0].'#s', '', $text);
            $item->fulltext = $text;
        }
        if(count($images)>0) $item->images = json_encode($images);

        // intro text links
        if(isset($item->introtext) && $this->params->get('rebuildlinks', 0)==1) {
            libxml_use_internal_errors(true);

            $dom = new DOMDocument();
            $text = $item->introtext;
            $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
            $dom->loadHTML('<div>'.$text.'</div>');

            // find all link attributes
            $update = false;
            foreach($dom->getElementsByTagName('a') as $tag) {
                $href = $tag->getAttribute('href');
                if($this->debug && $this->debuglevel==1) $this->setStatus(array('msg'=>'Found internal link: '.$href.' for ID #'.$item->id));
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
                    if($this->debug && $this->debuglevel==1) $this->setStatus(array('msg'=>'Updating internal link to: '.$newhref.' for ID #'.$item->id));
                    $tag->setAttribute('href', $newhref);
                    $update = true;
                }
            }
            if($update) {
                $text = preg_replace(array("/^\<\!DOCTYPE.*?<html><body>/si", "!</body></html>$!si"), "", $dom->saveHTML());
                $text = mb_convert_encoding($text, 'UTF-8', 'HTML-ENTITIES');
                $text = html_entity_decode($text);
                $item->introtext = $text;
            }
        }
        // full text links
        if(isset($item->fulltext) && $this->params->get('rebuildlinks', 0)==1) {
            libxml_use_internal_errors(true);

            $dom = new DOMDocument();
            $text = $item->fulltext;
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
                $item->fulltext = $text;
            }
        }
    }
}