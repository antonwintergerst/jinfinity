<?php
/**
 * @version     $Id: helper.php 047 2014-12-23 08:04:00Z Anton Wintergerst $
 * @package     JiContentInjector System Plugin for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgJiContentInjectorHelper {
    /*
     * @return: string Updated text on success, false when text is unchanged
     */
    public function inject($source, $context='content') {
        $changes = 0;
        if(isset($source)) {
            // Get Injections
            require_once(JPATH_SITE.'/administrator/components/com_jicontentinjector/models/injections.php');
            if(version_compare(JVERSION, '3', 'ge')) {
                $model = JModelLegacy::getInstance('Injections', 'JiContentInjectorModel', array('ignore_request'=>true));
            } else {
                $model = JModel::getInstance('Injections', 'JiContentInjectorModel', array('ignore_request'=>true));
            }
            $model->setState('filter.published', 1);
            $model->setState('filter.context', $context);
            $injections = $model->getItems();

            $jiparams = JComponentHelper::getParams('com_jicontentinjector');

            if(isset($injections) && is_array($injections) && count($injections)>0) {
                $html = new JiHTMLParser();
                $html->debug = ($jiparams->get('debug', 0)==1);
                $html->loadHTML($source->text);
                foreach($injections as $item) {
                    // Prepare attribs
                    $item->attribs = new JRegistry($item->attribs);
                    $proceed = true;
                    $checks = array();
                    // Check if this injector has assignment for this context
                    if(isset($source->artid)) {
                        // Check category assignment
                        $checks[] = $this->checkArticleAssignment($source->artid, $item->attribs);
                    }
                    if(isset($source->catid)) {
                        // Check category assignment
                        $checks[] = $this->checkCategoryAssignment($source->catid, $item->attribs);
                    }
                    if(count($checks)>0) {
                        $method = $item->attribs->get('assignmethod', 'all');
                        if($method=='all') {
                            $proceed = !in_array(0, $checks);
                        } else {
                            $proceed = in_array(1, $checks);
                        }
                    }

                    if($proceed) {
                        list($query, $append) = $html->selectorToQuery($item->selector);
                        $elements = $html->find($query);
                        if($elements!=false) {
                            $i = 1;
                            foreach($elements as $element) {
                                if(($item->attribs->get('selectfrom', 0)==0 || $i>=$item->attribs->get('selectfrom', 0)) && ($item->attribs->get('selectto', 0)==0 || $i<=$item->attribs->get('selectto', 0))) {
                                    $dispatcher	= JEventDispatcher::getInstance();
                                    $params = new JRegistry();
                                    $results = $dispatcher->trigger('onContentPrepare', array('com_jicontentinjector.item', &$item, &$params, null));
                                    $newelement = $html->getTextElement($item->text);
                                    if($append) {
                                        $element->appendChild($newelement);
                                    } else {
                                        $element->insertBefore($newelement, $element->firstChild);
                                    }
                                    $changes++;
                                }
                                $i++;
                            }
                        }
                    }
                }
            }
        }
        if($changes==0) return false;
        return $html->getText();
    }
    private function checkArticleAssignment($sourceartid, $params) {
        $mode = $params->get('artmode', 'all');

        if($mode=='all') {
            return 1;
        } elseif($mode=='include') {
            $assignedarts = $params->get('arts', array(0));
            if(in_array(0, $assignedarts)) return 1;

            if(in_array($sourceartid, $assignedarts)) return 1;
        } elseif($mode=='exclude') {
            $assignedarts = $params->get('arts', array(0));
            if(in_array(0, $assignedarts)) return 0;
            if(!in_array($sourceartid, $assignedarts)) return 1;
        }
        // Catch any false cases
        return 0;
    }
    private function checkCategoryAssignment($sourcecatid, $params) {
        $mode = $params->get('catmode', 'all');
        if($mode=='all') {
            return 1;
        } elseif($mode=='include') {
            $assignedcats = $params->get('cats', array(0));
            if(in_array(0, $assignedcats)) return 1;

            if($assignedcats) {
                if($params->get('catchildren', 1) && (int) $params->get('catlevels', 3) > 0) {
                    // Include sub category articles
                    $assignedcats = $this->getCategoryChildren($assignedcats, (int)$params->get('catlevels', 3));
                }
                if(in_array($sourcecatid, $assignedcats)) return 1;
            }
        } elseif($mode=='exclude') {
            $assignedcats = $params->get('cats', array(0));
            if(in_array(0, $assignedcats)) return 0;

            if($assignedcats) {
                if($params->get('catchildren', 1) && (int) $params->get('catlevels', 3) > 0) {
                    // Include sub category articles
                    $assignedcats = $this->getCategoryChildren($assignedcats, (int)$params->get('catlevels', 3));
                }
                if(!in_array($sourcecatid, $assignedcats)) return 1;
            }
        }
        // Catch any false cases
        return 0;
    }
    private function getCategoryChildren($assignedcats, $levels=9999) {
        // Get an instance of the generic categories model
        $categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
        $categories->setState('filter.get_children', $levels);
        $categories->setState('filter.published', 1);
        $additional_catids = array();

        foreach ($assignedcats as $catid) {
            $categories->setState('filter.parentId', $catid);
            $recursive = true;
            $categoryObjects = $categories->getItems($recursive);

            if($categoryObjects) {
                foreach ($categoryObjects as $category) {
                    $condition = (($category->level - $categories->getParent()->level) <= $levels);
                    if ($condition) {
                        $additional_catids[] = $category->id;
                    }
                }
            }
        }
        $assignedcats = array_unique(array_merge($assignedcats, $additional_catids));
        return $assignedcats;
    }
}
class JiHTMLParser {
    protected $html;
    protected $xpath;
    public $debug = false;
    public function loadHTML($source) {
        if(!$this->debug) libxml_use_internal_errors(true);
        $this->html = new DOMDocument;
        $this->html->strictErrorChecking = false;

        $this->html->loadHTML($source);
    }
    public function find($query) {
        // Perform query
        $this->xpath = new DOMXPath($this->html);
        $nodes = $this->xpath->query($query);
        if($nodes->length==0) return false;
        return $nodes;
    }
    public function getText() {
        $text = $this->html->saveHTML();
        $text = preg_replace(array('/^\<\!DOCTYPE.*?<html><body>/si', "!</body></html>$!si"), "", $text);
        $text = $this->xmlToHTML($text);
        return $text;
    }
    public function getTextElement($html)
    {
        $html = str_replace(array("\r\n", "\r"), "", $html);
        $element = $this->html->createDocumentFragment();
        $element->appendXML($html);
        return $element;
    }
    public function appendChild($oldElement, $newhtml)
    {
        $newhtml = str_replace(array("\r\n", "\r"), "", $newhtml);
        $newElement = $this->html->createDocumentFragment();
        $newElement->appendXML($newhtml);
        $oldElement->appendChild($newElement);
    }
    /*
     * Correction for non-closing element tags
     * @xml: string XML formatted string
     * @return: string HTML formatted string
     */
    private function xmlToHTML($xml) {
        $html = preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', create_function('$m', '
            $xhtml_tags = array("br", "hr", "input", "frame", "img", "area", "link", "col", "base", "basefont", "param");
            return in_array($m[1], $xhtml_tags) ? "<$m[1]$m[2] />" : "<$m[1]$m[2]></$m[1]>";
        '), $xml);
        return $html;
    }
    /*
     * @selector: string CSS style selector
     * @return: string XPath query
     */
    public function selectorToQuery($selector, $query='//') {
        $append = true;
        // Convert CSS selector to XPath query
        $parts = explode(' ', $selector);
        $i = 0;
        foreach($parts as $part) {
            if($i>0) $query.= '/';
            $element = '*';
            $item = trim(str_replace(array(':first', ':last', ':before', ':after'), '', $part));
            //TODO
            if(strstr($item, '+')!=false) {
                $subparts = explode('+', $item);
            }
            if(strstr($item, '.')!=false) {
                // Class selector
                if(strpos($item, '.')!=1) {
                    $subparts = explode('.', $item);
                    $element = $subparts[0];
                    $class = $subparts[1];
                } else {
                    $class = str_replace('.', '', $item);
                }
                $query.= $element.'[@class="'.$class.'"]';
            } elseif(strstr($item, '#')!=false) {
                // ID selector
                if(strpos($item, '.')!=1) {
                    $subparts = explode('#', $item);
                    $element = $subparts[0];
                    $id = $subparts[1];
                } else {
                    $id = str_replace('#', '', $item);
                }
                $query.= $element.'[@id="'.$id.'"]';
            } else {
                // Element selector
                $element = $item;
                $query.= $element;
            }
            //TODO
            if(strstr($part, '+')!=false) {
                $query.= '/following-sibling::*[1]/self::';
            }
            if(strstr($part, ':first')!=false) {
                $query.= '[1]';
            } elseif(strstr($part, ':last')!=false) {
                $query.= '[last()]';
            }
            if(strstr($part, ':before')!=false) {
                $append = false;
            } elseif(strstr($part, ':after')!=false) {
                $append = true;
            }
            $i++;
        }
        $query = "//div";
        return array($query, $append);
    }
}