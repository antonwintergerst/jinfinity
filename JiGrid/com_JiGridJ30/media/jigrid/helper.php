<?php
/**
 * @version     $Id: helper.php 055 2014-11-27 19:51:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jigrid'.DS.'helpers'.DS.'object.php');
require_once(JPATH_SITE.DS.'media'.DS.'jigrid'.DS.'layouttools.php');

class JiGridLayoutHelper extends JiGridObject {
    public function __construct() {
        // Open up some of this class methods to other JiGridObject subclasses
        JiGridFactory::getData()->set('getRandomColor', array($this, 'getRandomColor'));
    }
    public function getGrid() {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jigrid'.DS.'models'.DS.'griditems.php');
        $model = JModelLegacy::getInstance('GridItems', 'JiGridModel', $config = array('ignore_request' => true));
        $model->setState('filter.published', 1);
        $model->setState('filter.root', false);
        $items = $model->getItems();

        if($items!=null) {
            // Create child array
            $this->children = array();
            foreach($items as $item) {
                $this->children[$item->parent_id][] = $item;
            }
            // Set root
            $grid = new JiGrid((array)$items[0]);
            $grid->bindData(null, $items[0]->attribs);

            // Build tree
            $grid = $this->buildGrid($grid, 1);
        } else {
            $grid = new JiGrid();
        }

        return $grid;
    }
    public function buildGrid($root, $rid) {
        $gridparams = JComponentHelper::getParams('com_jigrid');
        if(isset($this->children[$rid])) {
            $children = $root->get('children', array());
            foreach($this->children[$rid] as $child) {
                if($child->type=='grid') {
                    $griditem = new JiGrid((array) $child);
                    $griditem = $this->buildGrid($griditem, $child->id);
                } elseif($child->type=='row') {
                    $griditem = new JiRow((array) $child);
                    $griditem = $this->buildGrid($griditem, $child->id);
                } else {
                    $griditem = new JiCell((array) $child);
                    $griditem = $this->buildGrid($griditem, $child->id);
                }
                if($griditem!=null) {
                    $griditem->bindData(null, $child->attribs);
                    // Exclude empty nodes
                    $doc = JFactory::getDocument();
                    $proceed = true;
                    if($gridparams->get('hideempty', 0)==1 && (is_a($griditem, 'JiGrid') || is_a($griditem, 'JiCell'))) {
                        $position = $griditem->get('position');
                        if($position!=null) {
                            if(!$doc->countModules($position)) {
                                $proceed = false;
                            } else {
                                $content = $doc->getBuffer('modules', $position, array('style'=>'jigrid'));
                                if(empty($content)) $proceed = false;
                            }
                        }
                        if($griditem->get('component')!=null) {
                            if(trim($doc->getBuffer('component'))=='') $proceed = false;
                        }
                    }
                    if($proceed) $children[] = $griditem;
                }
            }
            // Exclude empty nodes
            if($gridparams->get('hideempty', 0)==1 && count($children)==0) return;

            $root->set('children', $children);
        }

        return $root;
    }
    public function setState($state, $total=0) {
        $this->set('laststate', $this->get('state'));
        $this->set('state', $state);
        $gridlevel = JiGridFactory::getData()->get('gridlevel', 0);
        $rowcount = JiGridFactory::getData()->get('rowcount', 0);
        $cellcount = JiGridFactory::getData()->get('cellcount', 0);
        switch($state) {
            case 'grid.start':
                $gridlevel++;
                $this->set('gridlevel', $gridlevel);
                JiGridFactory::getData()->set('gridlevel', $gridlevel);

                $this->set('lastrowcount', $rowcount);
                $rowcount = 0;
                $this->set('rowcount', $rowcount);
                JiGridFactory::getData()->set('rowcount', $rowcount);

                $this->set('totalrows', $total);
                JiGridFactory::getData()->set('totalrows', $total);
                break;
            case 'grid.end':
                $gridlevel--;
                $this->set('gridlevel', $gridlevel);
                JiGridFactory::getData()->set('gridlevel', $gridlevel);

                $this->set('rowcount', $this->get('lastrowcount'));
                JiGridFactory::getData()->set('rowcount', $this->get('lastrowcount'));
                break;
            case 'row.start':
                $rowcount++;
                $this->set('rowcount', $rowcount);
                JiGridFactory::getData()->set('rowcount', $rowcount);

                $cellcount = 0;
                $this->set('cellcount', $cellcount);
                JiGridFactory::getData()->set('cellcount', $cellcount);

                $this->set('totalcells', $total);
                JiGridFactory::getData()->set('totalcells', $total);
                break;
            case 'cell.start':
                $cellcount++;
                $this->set('cellcount', $cellcount);
                JiGridFactory::getData()->set('cellcount', $cellcount);
                break;
            default:
                break;
        }
    }
    public function loadLayout($primary, $secondary=null) {
        $primary = $primary.'.php';
        if(file_exists($this->get('styledir').DS.$primary)) {
            return $this->get('styledir').DS.$primary;
        } elseif(file_exists($this->get('layoutdir').DS.$primary)) {
            return $this->get('layoutdir').DS.$primary;
        } elseif($secondary!=null) {
            return $this->loadLayout($secondary);
        } else {
            return $this->get('layoutdir').DS.'index.html';
        }
    }
    public function getHead() {
        JHtml::addIncludePath(JPATH_SITE.DS.'media'.DS.'jinfinity'.DS.'html');
        $gridparams = JComponentHelper::getParams('com_jigrid');
        $setminheight = ($gridparams->get('setminheight', 1)==1)? 'true' : 'false';
        $equalizeheights = ($gridparams->get('equalizeheights', 1)==1)? 'true' : 'false';
        $hidesmall = ($gridparams->get('hidesmall', 1)==1)? 'true' : 'false';

        JHtml::stylesheet('media'.DS.'jigrid'.DS.'css'.DS.'jigrid.css');
        JHtml::_('jquery.framework');
        JHtml::script('media'.DS.'jigrid'.DS.'js'.DS.'jquery.jigrid.js');
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration('
            var jigrid = null;
            if(typeof jQuery!=\'undefined\') {
                jQuery(document).ready(function() {
                    jigrid = jQuery(\'.jigrid.level1\').jigrid({
                        \'tvwidth\':'.$gridparams->get('tvwidth', 1920).',
                        \'desktopwidth\':'.$gridparams->get('desktopwidth', 980).',
                        \'tabletwidth\':'.$gridparams->get('tabletwidth', 768).',
                        \'phonewidth\':'.$gridparams->get('phonewidth', 480).',
                        \'setminheight\':'.$setminheight.',
                        \'equalizeheights\':'.$equalizeheights.',
                        \'hidesmall\':'.$hidesmall.'
                    });
                });
            }
        ');
        $jinput = JFactory::getApplication()->input;
        if($gridparams->get('setscreencontext', 1)==1) {
            $screencontext = $jinput->get('screencontext');
            if($screencontext == null) {
                $agent = $_SERVER['HTTP_USER_AGENT'];
                if (strstr($agent, 'iPhone') ||
                    strstr($agent, 'Android') ||
                    strstr($agent, 'Blackberry') ||
                    strstr($agent, 'BlackBerry') ||
                    strstr($agent, 'OperaMobi') ||
                    strstr($agent, 'Opera Mini') ||
                    strstr($agent, 'IEMobile') ||
                    strstr($agent, 'Jasmine') ||
                    strstr($agent, 'Fennec') ||
                    strstr($agent, 'Blazer') ||
                    strstr($agent, 'Minimo') ||
                    strstr($agent, 'MOT-') ||
                    strstr($agent, 'Nokia') ||
                    strstr($agent, 'SAMSUNG') ||
                    strstr($agent, 'Polaris') ||
                    strstr($agent, 'LG-') ||
                    strstr($agent, 'SonyEricsson') ||
                    strstr($agent, 'hiptop') ||
                    strstr($agent, 'avantgo') ||
                    strstr($agent, 'plucker') ||
                    strstr($agent, 'SIE-') ||
                    strstr($agent, 'xiino') ||
                    strstr($agent, 'elaine') ||
                    strstr($agent, 'AUDIOVOX') ||
                    strstr($agent, 'mobile') ||
                    strstr($agent, 'webOS')) {

                    $screencontext = 'phone';
                } else {
                    $screencontext = 'desktop';
                }
                $jinput->set('screencontext', $screencontext);
            }
        }
        if($gridparams->get('setbrowsercontext', 1)==1) {
            $browsercontext = $jinput->get('browsercontext');
            if($browsercontext==null) {
                $agent = $_SERVER['HTTP_USER_AGENT'];
                preg_match('/MSIE (.*?);/', $agent, $matches);
                if(count($matches)>1) {
                    $version = $matches[1];
                    if($version<=7) {
                        $browsercontext = 'ie ie7 oldie';
                    } elseif ($version<=8) {
                        $browsercontext = 'ie ie8 oldie';
                    } elseif ($version<=9) {
                        $browsercontext = 'ie ie9 oldie';
                    } else {
                        $browsercontext = 'ie iex';
                    }
                }
                if($browsercontext==null) {
                    if(strpos($agent, 'Chrome')!==false) {
                        $browsercontext = 'chrome notie';
                    } elseif(strpos($agent, 'CriOS')!==false) {
                        $browsercontext = 'chrome chromeios notie';
                    } elseif(strpos($agent, 'Firefox')!==false) {
                        $browsercontext = 'firefox notie';
                    } elseif(strpos($agent, 'Safari')!==false) {
                        $browsercontext = 'safari notie';
                    } elseif(strpos($agent, 'Trident')!==false) {
                        $browsercontext = 'ie iex trident';
                    } else {
                        $browsercontext = 'other notie';
                    }
                }
                $jinput->set('browsercontext', $browsercontext);
            }
        }
    }
    public function getRandomColor() {
        // Random Colors
        $spread = 25;
        $colors = $this->get('randomcolors', array());
        for($c=0;$c<3;++$c) {
            $colors[$c] = rand(0+$spread,255-$spread);
        }
        $this->set('randomcolors', $colors);
        $r = rand($colors[0]-$spread, $colors[0]+$spread);
        $g = rand($colors[1]-$spread, $colors[1]+$spread);
        $b = rand($colors[2]-$spread, $colors[2]+$spread);
        $color = array($r, $g, $b);
        return $color;
    }
    public function truncate($text, $length, $suffix = '...', $isHTML = true, $insert=0, $force=0)
    {
        $text = $text.' ';
        $i = 0;
        $simpleTags=array('br'=>true,'hr'=>true,'input'=>true,'image'=>true,'link'=>true,'meta'=>true);
        $tags = array();
        if($isHTML){
            preg_match_all('/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            foreach($m as $o){
                if($o[0][1] - $i >= $length)
                    break;
                $t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);
                // test if the tag is unpaired, then we mustn't save them
                if($t[0] != '/' && (!isset($simpleTags[$t])))
                    $tags[] = $t;
                elseif(end($tags) == substr($t, 1))
                    array_pop($tags);
                $i += $o[1][1] - $o[0][1];
            }
        }

        // output without closing tags
        $output = substr($text, 0, $length = min(strlen($text),  $length + $i));
        // closing tags
        $output2 = (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '');

        // Find last space or HTML tag (solving problem with last space in HTML tag eg. <span class="new">)
        $osplit = preg_split('/<.*>| /', $output, -1, PREG_SPLIT_OFFSET_CAPTURE);
        $oend1 = end($osplit);
        $pos = (int)end($oend1);
        // Append closing tags to output
        $output.=$output2;

        // Get everything until last space
        $one = substr($output, 0, $pos);
        // Get the rest
        $two = substr($output, $pos, (strlen($output) - $pos));
        // Extract all tags from the last bit
        preg_match_all('/<(.*?)>/s', $two, $tags);
        // Add suffix inside last tag if needed
        if((strlen($text) > $length || $force==1) && $insert==0) { $one .= $suffix; }
        // Re-attach tags
        $output = $one . implode($tags[0]);
        // Add suffix outside last tag if needed
        if((strlen($text) > $length || $force==1)&& $insert==1) { $output .= $suffix; }

        //added to remove  unnecessary closure
        $output = str_replace('</!-->','',$output);

        return $output;
    }
    public function getDateSpans($source, $format='d/m/Y') {
        $html = '';
        for($fm=0; $fm<strlen($format); $fm++):
            $subformat = substr($format, $fm, 1);
            $value = date($subformat, strtotime($source));
            $class = preg_replace('/[^a-zA-Z0-9\']/', 'separator', $subformat);
            $html.= '<span class="fm'.$class.'">'.$value.'</span>';
        endfor;

        return $html;
    }
}
abstract class JiGridFactory {
    public static $data = null;
    public static function getData() {
        if(!self::$data){
            self::$data = new JiGridObject();
        }
        return self::$data;
    }
}
class JiGrid extends JiGridObject {
    public function getClass() {
        $class = 'jigrid '.$this->get('alias');
        if(JiGridFactory::getData()->get('gridlevel', 1)==1) {
            $class.= ' level1';
        } else {
            $class.= ' level'.JiGridFactory::getData()->get('gridlevel');
            $class.= ' jicell span-'.$this->get('span', 1);
        }
        if($this->get('class')!=null) $class.= ' '.$this->get('class');
        if($this->get('autospan')==1) $class.= ' autospan';
        if(JiGridFactory::getData()->get('totalcells')==1) $class.= ' singlechild';
        if($this->get('minwidth')!=null) $class.= ' minw-'.$this->get('minwidth');
        return $class;
    }
    public function getStyle() {
        $gridparams = JComponentHelper::getParams('com_jigrid');
        $styles = array();
        if($gridparams->get('rainbow', 1)==1) {
            $color = call_user_func(JiGridFactory::getData()->get('getRandomColor'));
            $styles[] = 'background-color: rgb('.$color[0].','.$color[1].','.$color[2].');';
        }
        $style = (count($styles)>0)? ' style="'.implode('', $styles).'"' : '';
        return $style;
    }
}
class JiRow extends JiGridObject {
    public function getClass() {
        $class = 'jirow '.$this->get('alias');
        $class.= ' row'.JiGridFactory::getData()->get('rowcount');
        $class.= ' cols-'.$this->get('cols', 12);
        if($this->get('cols-tv')!=null) $class.= ' colstv-'.$this->get('cols-tv');
        if($this->get('cols-tablet')!=null) $class.= ' colstablet-'.$this->get('cols-tablet');
        if($this->get('cols-phone')!=null) $class.= ' colsphone-'.$this->get('cols-phone');
        if($this->get('ypercent')!=null) $class.= ' ypercent-'.$this->get('ypercent');
        if($this->get('ypercent-phone')!=null) $class.= ' ypercentphone-'.$this->get('ypercent-phone');
        if($this->get('class')!=null) $class.= ' '.$this->get('class');
        if(JiGridFactory::getData()->get('totalrows')==1) $class.= ' singlechild';
        return $class;
    }
    public function getStyle() {
        $gridparams = JComponentHelper::getParams('com_jigrid');
        $style = '';
        if($gridparams->get('rainbow', 1)==1) {
            $color = call_user_func(JiGridFactory::getData()->get('getRandomColor'));
            $style = ' style="background-color: rgb('.$color[0].','.$color[1].','.$color[2].');"';
        }
        return $style;
    }
    public function getOuterClass() {
        $class = 'outer';
        if(JiGridFactory::getData()->get('gridlevel', 1)==1) {
            $class.= ' outerwrap';
        }
        return $class;
    }
    public function getOuterStyle() {
        $gridparams = JComponentHelper::getParams('com_jigrid');
        $styles = array();
        if(JiGridFactory::getData()->get('gridlevel', 1)==1) {
            $styles[] = 'max-width:'.$gridparams->get('sitewidth', '960px').';';
            $alignment = $gridparams->get('sitealignment', 'center');
            if($alignment=='center') {
                $styles[] = 'margin:0 auto;';
            } elseif($alignment=='left') {
                $styles[] = 'margin-right: auto;';
            } elseif($alignment=='right') {
                $styles[] = 'margin-left: auto;';
            }
        }
        $style = (count($styles)>0)? ' style="'.implode('', $styles).'"' : '';
        return $style;
    }
}
class JiCell extends JiGridObject {
    public function getClass() {
        $class = 'jicell '.$this->get('alias');
        $class.= ' span-'.$this->get('span', 1);
        if($this->get('message')!=null) $class.= ' message';
        if($this->get('component')!=null) $class.= ' component';
        if($this->get('position')!=null) $class.= ' module '.$this->get('position');
        if($this->get('class')!=null) $class.= ' '.$this->get('class');
        if($this->get('autospan')==1) $class.= ' autospan';
        if(JiGridFactory::getData()->get('totalcells')==1) $class.= ' singlechild';
        if($this->get('minwidth')!=null) $class.= ' minw-'.$this->get('minwidth');
        if(JiGridFactory::getData()->get('cellcount')==1) $class.= ' first';
        return $class;
    }
    public function getStyle() {
        $gridparams = JComponentHelper::getParams('com_jigrid');
        $style = '';
        if($gridparams->get('rainbow', 1)==1) {
            $color = call_user_func(JiGridFactory::getData()->get('getRandomColor'));
            $style = ' style="background-color: rgb('.$color[0].','.$color[1].','.$color[2].');"';
        }
        return $style;
    }
}