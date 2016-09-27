<?php
/**
 * @version     $Id: layouttools.php 025 2014-12-23 17:43:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 3.x
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

abstract class JiLayoutTools {
    public static $class = null;
    public static function __callStatic($method, $args) {
        if(!static::$class) static::$class = new JiGridLayoutTools();
        return call_user_func_array(array(static::$class, $method), $args);
    }
}

class JiGridLayoutTools {
    public function truncate($text, $length, $mode='chars', $suffix = '...', $allowScripts = true, $isHTML = true, $insert=1, $force=0, $break='/(<\/p>)|i\s/')
    {
        if(!$allowScripts) {
            $text = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $text);
            $text = preg_replace('#<stylesheet(.*?)>(.*?)</stylesheet>#is', '', $text);
        }
        if($mode=='words') {
            return $this->truncateWords($text, $length, $suffix, $isHTML, $insert, $force);
        } elseif($mode=='paragraphs') {
            return $this->truncateParagraphs($text, $length, $suffix, $isHTML, $insert, $force, $break);
        } else {
            return $this->truncateChars($text, $length, $suffix, $isHTML, $insert, $force);
        }
    }

    public function truncateChars($text, $length, $suffix = '...', $isHTML = true, $insert=0, $force=0)
    {
        $text = $text.' ';
        $i = 0;
        $simpleTags=array('br'=>true,'hr'=>true,'input'=>true,'img'=>true,'link'=>true,'meta'=>true);
        $tags = array();
        if($isHTML){
            preg_match_all('/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

            $writingscript = false;
            foreach($m as $o){
                if($o[0][1] - $i >= $length && !$writingscript) break;

                // ignore scripts
                preg_match('#<script(.*?)>#is', $o[0][0], $sm);
                if($sm) {
                    $writingscript = $o[0][1];
                    continue;
                }
                if($writingscript!==false) {
                    preg_match('#(.*?)</script>#is', $o[0][0], $esm);
                    if($esm) {
                        $i += $o[1][1] - $writingscript;
                        $writingscript = false;
                        continue;
                    }
                }

                $t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);

                // exclude unpaired tags
                if($t[0] != '/' && !isset($simpleTags[$t])) {
                    $tags[] = $t;
                } elseif(end($tags) == substr($t, 1)) {
                    array_pop($tags);
                }
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
        if((strlen($text) > $length || $force==1) && $insert==1) { $one .= $suffix; }
        // Re-attach tags
        $output = $one . implode($tags[0]);
        // Add suffix outside last tag if needed
        if((strlen($text) > $length || $force==1)&& $insert==0) { $output .= $suffix; }

        //added to remove  unnecessary closure
        $output = str_replace('</!-->','',$output);

        return $output;
    }

    public function truncateWords($text, $length, $suffix = '...', $isHTML = true, $insert=0, $force=0)
    {
        $tok = strtok($text, " \n");
        $output = $tok;
        $i = 1;
        while($tok!==false && $i<$length) {
            $i++;
            $tok = strtok(" \n");
            $output.= ' '.$tok;
        }
        if($i>=$length || $force==1) {
            if($isHTML) {
                if($insert==1) $output.= $suffix;
                $output = $this->restoreTags($output);
            }
            if($insert==0 || !$isHTML) $output.= $suffix;
        }
        return $output;
    }

    public function truncateParagraphs($text, $length, $suffix = '...', $isHTML = true, $insert=0, $force=0, $break='/(<\/p>)|i\s/')
    {
        // Find bits
        $bits = preg_split($break, $text, -1, PREG_SPLIT_NO_EMPTY);
        // Find delimiters (endtags)
        preg_match_all($break, $text, $endtags, PREG_SET_ORDER);

        if($bits==null) return $text;

        if(count($bits)>$length || $force==1) {
            $output = '';
            $key = 0;
            // Rebuild text up to the length limit
            while($key<$length && isset($bits[$key])) {
                $output.= $bits[$key];
                if($key==($length-1) && $insert==1) $output.= $suffix;
                $output.= $endtags[$key][0];
                $key++;
            }
            if($isHTML) $output = $this->restoreTags($output);
            if($insert==0 || !$isHTML) $output.= $suffix;
        } else {
            $output = $text;
        }
        return $output;
    }

    /**
     * Finds and removes images in $textvar. Images are then available as an array under $item->jiimages
     * @param $item
     * @param string $textvar
     * @param mixed $params
     */
    public function getImages(&$item, $textvar='text', $params=null)
    {
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jigrid'.DS.'helpers'.DS.'object.php');
        if(!($params instanceof JiGridObject)) $params = new JiGridObject($params);

        $regex = '#<\img(.*?)src\="(.*?)"(.*?)\>#s';
        $oldtext = $item->{$textvar};
        $text = $item->{$textvar};
        $item->jiimages = array();

        // Find images
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
        $imgtag = '';
        $i = 0;
        if($matches!=null) {
            foreach($matches as $match) {
                // break early if not removing other images
                if($params->get('limit') && $i>=$params->get('limit') && $params->get('removeimages', 1)==0) break;

                $imgtag = $match[0];

                $image = new stdClass();
                $source = $match[2];
                $image->src = $source;

                // find all IMG attributes
                if($params->get('preservelinkattribs', 1)==1) {
                    $imageattribs = array();
                    preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $match[0], $iattribmatches, PREG_SET_ORDER);
                    if($iattribmatches!=null) {
                        foreach($iattribmatches as $iattribmatch) {
                            if(isset($iattribmatch[1])) $imageattribs[$iattribmatch[1]] = str_replace('" /', '', $iattribmatch[2]);
                        }
                    }
                    $image->imageattribs = $imageattribs;
                }
                if(isset($image->imageattribs['class']) && $params->get('maskclass')) {
                    $maskclasses = $params->get('maskclass');

                    if(!is_array($maskclasses)) $maskclasses = explode(' ', $maskclasses);
                    $iclasses = explode(' ', $imageattribs['class']);
                    $skip = false;
                    foreach($maskclasses as $maskclass) {
                        if($params->get('maskmode', 'include')=='include') {
                            $skip = !in_array($maskclass, $iclasses);
                            if(!$skip) break;
                        } elseif($params->get('maskmode', 'include')=='exclude') {
                            $skip = in_array($maskclass, $iclasses);
                            if($skip) break;
                        }
                    }
                    if($skip) continue;
                }

                if($params->get('resize', 0)==1) {
                    $image->path = $this->createThumbnail($source, $params);
                } else {
                    $image->path = $source;
                }

                if($params->get('limit') && $i>=$params->get('limit')) {
                    // remove other images
                    if($params->get('removeimages', 1)==1) $text = preg_replace('#'.$match[0].'#s', '', $text);
                    continue;
                }

                // remove original
                $text = preg_replace('#'.$match[0].'#s', '', $text);

                if($params->get('preserveimgattribs', 1)!=1 || !isset($image->imageattribs)) $image->imageattribs = array();
                $image->imageattribs['src'] = $image->path;
                if(isset($image->imageattribs['width'])) unset($image->imageattribs['width']);
                if(isset($image->imageattribs['height'])) unset($image->imageattribs['height']);
                if(!isset($image->imageattribs['alt']) || empty($image->imageattribs['alt'])) $image->imageattribs['alt'] = $item->title;
                $image->imageattribs['class'] = (isset($image->imageattribs['class']))? 'jiimg '.$image->imageattribs['class'] : 'jiimg';

                $image->attribs = new JiGridObject($image->imageattribs);
                $item->jiimages[] = $image;

                // setup links
                if($params->get('preservelinkattribs', 1)==1) {
                    // find surrounding link attribute
                    $linkattribs = array();
                    preg_match_all('#<a\s(.*)>(.*)'.$imgtag.'(.*)<\/a>#siU', $oldtext, $linkmatches, PREG_SET_ORDER);
                    if(isset($linkmatches[0][0])) {
                        preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $linkmatches[0][1], $lattribmatches, PREG_SET_ORDER);
                        if($lattribmatches!=null) {
                            foreach($lattribmatches as $lattribmatch) {
                                if(isset($lattribmatch[1])) $linkattribs[$lattribmatch[1]] = $lattribmatch[2];
                            }
                        }
                        // strip link from HTML
                        $text = preg_replace('#<a\s(.*)>(.*)'.$imgtag.'(.*)<\/a>#siU', '', $text);
                    }

                    $image->linkattribs = $linkattribs;
                }
                $i++;
            }
        }
        $text = str_replace('<p></p>', '', $text);
        $item->{$textvar} = $text;
    }

    public function createThumbnail($filepath, $params)
    {
        if(is_object($filepath)) {
            $thumbnail = $filepath;
            $filepath = $thumbnail->path;
        } else {
            $thumbnail = new stdClass();
        }

        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jigrid'.DS.'helpers'.DS.'object.php');
        if(!($params instanceof JiGridObject)) $params = new JiGridObject($params);

        $thumbspath = JURI::root().'images/jithumbs';
        $thumbsdir = JPATH_SITE.DS.'images'.DS.'jithumbs';

        $pcontext = $params->get('context', 'img');

        $pathparts = explode('/', $filepath);
        $file = end($pathparts);
        $nameparts = explode('.', $file);
        $name = $nameparts[0];
        $type = end($nameparts);
        $imageset = false;

        if($params->get('resize', 1)==1) {
            if(JFile::exists($thumbsdir.'/'.$name.'_'.$pcontext.'.jpg') && $params->get('cache', 1)==1) {
                $imageset = true;
                $imageInfo = getimagesize($thumbsdir.'/'.$name.'_'.$pcontext.'.jpg');
                $thumbnail->width = $imageInfo[0];
                $thumbnail->height = $imageInfo[1];
            } else {
                require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jigrid'.DS.'helpers'.DS.'imageprocessor.php');
                $JiImageProcessor = new JiGridImageProcessor();
                $imageset = $JiImageProcessor->resizeImage($filepath, $thumbsdir.'/'.$name.'_'.$pcontext.'.jpg', $params, $type);
                if($imageset) {
                    $thumbnail->width = $JiImageProcessor->width;
                    $thumbnail->height = $JiImageProcessor->height;
                }
            }
        }


        if($imageset) {
            $thumbnail->path = $thumbspath.'/'.$name.'_'.$pcontext.'.jpg';
            return $thumbnail;
        } else {
            $imageInfo = getimagesize($filepath);
            $thumbnail->width = $imageInfo[0];
            $thumbnail->height = $imageInfo[1];
            $thumbnail->path = str_replace(JPATH_SITE.DS, '', $filepath);
            return $thumbnail;
        }
    }

    public function getHTMLAttribs($attribs, $filter=array())
    {
        $html = '';
        $emptyfilter = count($filter)==0;
        foreach($attribs as $key=>$val) {
            if($emptyfilter || in_array($key, $filter)) $html.= ' '.$key.'="'.$val.'"';
        }
        return $html;
    }

    public function getDateSpans($source, $format='d/m/Y')
    {
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