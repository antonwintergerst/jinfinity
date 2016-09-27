<?php 
/**
 * @version     $Id: helper.php 146 2014-12-23 09:25:00Z Anton Wintergerst $
 * @package     Jinfinity Blog Tools Content Plugin for Joomla 1.7+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Load Joomla File & Folder libraries if not already loaded
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class plgJiBlogToolsHelper {
    private $excludedrts = array('total');

    public function getDRT($text)
    {
        $regex = "#{(.*?)}#s";
        $text = preg_replace_callback($regex, array(&$this,'replaceAttribute'), $text);
        return $text;
    }

    public function replaceAttribute($matches)
    {
        $result = '';
        if(isset($matches[1])) {
            $attr = $matches[1];
            if(in_array($attr, $this->excludedrts)) {
                $result = $matches[0];
            } else {
                if(isset($this->item->{$attr})) $result = $this->item->{$attr};
            }
        }
        return $result;
    }

    private function getParams($pluginType='content', $pluginName='jiblogtools')
    {
        if(isset($this->params[$pluginType.$pluginName])) {
            return $this->params[$pluginType.$pluginName];
        } else {
            if(version_compare( JVERSION, '1.6.0', 'ge' )) {
                // Get plugin params
                $plugin = JPluginHelper::getPlugin($pluginType, $pluginName);
                $params = new JRegistry($plugin->params);
            } else {
                // Get plugin params
                $plugin = &JPluginHelper::getPlugin($pluginType, $pluginName);
                $params = new JParameter($plugin->params);
            }
            if(!isset($this->params) || !is_array($this->params)) $this->params = array();
            $this->params[$pluginType.$pluginName] = $params;
            return $params;
        }
    }

    public function createDirectory($dirs)
    {
        if(!is_array($dirs)) $dirs = array($dirs);
        $indexfile = JPATH_SITE.DS.'images'.DS.'index.html';
        $indexfileexists = file_exists($indexfile);
        foreach($dirs as $dir) {
            if(!file_exists($dir) || !is_dir($dir)) {
                mkdir($dir);
                chmod($dir, 0755);
            }
            if($indexfileexists && !file_exists($dir.DS.'index.html')) copy($indexfile, $dir.DS.'index.html');
        }
    }

    public function downloadImage($sources=array())
    {
        $params = $this->getParams();
        $result = false;
        $allowedExtensions = explode(',', $params->get('images_types', "gif,jpg,jpeg,png"));
        if($allowedExtensions==false) $allowedExtensions = array('gif','jpg','jpeg','png');
        foreach($sources as $input) {
            if(strpos($input, '.')==false) continue;
            $sparts = explode(".", $input);
            $type = end($sparts);
            if(!in_array(strtolower($type), $allowedExtensions)) continue;

            $filename = basename($input, $type);
            $source = 'images'.DS.'jiexternal'.DS.$filename.$type;
            $output = JPATH_SITE.DS.$source;

            //if($params->get('external_cache', 1)==0 || !file_exists($output) || (file_exists($output) && getimagesize($output)==false)) {
                $this->createDirectory(array(JPATH_SITE.DS.'images'.DS.'jiexternal'));
                $ch = curl_init($input);
                $fp = fopen($output, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                // Check if image exists
                if(getimagesize($output)!==false) {
                    $result = $source;
                    break;
                }
            //}
        }
        return $result;
    }

    public function &getHTML($data=array(), $link, $prefix, $article, $icount)
    {
        $params = $this->getParams();
        // Get Vars
        if(version_compare(JVERSION, '2.5.0', 'ge' )) {
            $jinput = JFactory::getApplication()->input;
            $Itemid = $jinput->get('Itemid', 0, 'int');
        } else {
            $Itemid = JRequest::getVar('Itemid');
        }
        $debug = $params->get('debug', 0);
        $source = (isset($data['source']))? $data['source'] : null;
        $imageattribs = (isset($data['imageattribs']))? $data['imageattribs'] : array();
        $linkattribs = (isset($data['linkattribs']))? $data['linkattribs'] : array();
        
        // Process source filenames
        $source = str_replace('%20', ' ', $source);

        $rootdir = JPATH_SITE.DS.'images';
        $thumbsdir = $rootdir.DS.'jithumbs';
        $thumbspath = JURI::root().'images/jithumbs';

        // Check file belongs to this domain
        $baseurl = str_replace(array('http://', 'https://'), '', JURI::base());
        if((strstr($source, 'http://')!=false || strstr($source, 'https://')!=false) && strstr($source, $baseurl)==false) {
            if($params->get('ignore_external', 1)==1) {
                // Error: This image belongs to an external domain
                $html = ($debug==1)? '<div>Error: This image belongs to an external domain</div>' : false;
                return $html;
            } else {
                $sources = array($source);
                if(isset($linkattribs['href'])) $sources[] = $linkattribs['href'];
                $result = $this->downloadImage($sources);
                if($result!=false) $source = $result;
            }
        }

        $source = trim(str_replace(JURI::root(), '', $source), '/');
        $imagefile = JPATH_SITE.DS.$source;
        
        // Check if file exists
        if(!JFile::exists($imagefile)) {
            // Error: Source file does not exist!
            $html = ($debug==1)? '<div>Error: Source file does not exist! ('.$source.')</div>' : false;
            return $html;
        }
        // Ignore small images
        if($params->get('ignore_size', 1)==1) {
            $minwidth = (int) $params->get('ignore_width', 100);
            $minheight = (int) $params->get('ignore_height', 100);
            $imageInfo = getimagesize($imagefile);
            $imgwidth = $imageInfo[0];
            $imgheight = $imageInfo[1];
            if($imgwidth<=$minwidth) {
                // Warning: Image width is too small to process!
                $html = ($debug==1)? '<div>Warning: Image width is too small to process! ('.$source.')</div>' : false;
                return $html;
            }
            if($imgheight<=$minheight) {
                // Warning: Image height is too small to process!
                $html = ($debug==1)? '<div>Warning: Image height is too small to process! ('.$source.')</div>' : false;
                return $html;
            }
        }
        // Create Thumbs Directory
        $this->createDirectory(array($thumbsdir));
            
        // Get source file type
        $sparts = explode(".", $source);
        $type = end($sparts);
        // check if this is an allowed file type
        $allowedExtensions = explode(',', $params->get('images_types', "gif,jpg,jpeg,png"));
        if($allowedExtensions==false) $allowedExtensions = array('gif','jpg','jpeg','png');
        if(!in_array(strtolower($type), $allowedExtensions)) {
            $html = ($debug==1)? '<div>Error: Extension type of source file is not supported!</div>' : false;
            return $html;
        }

        // get filename
        $nparts = explode(".", $source);
        $nfirst = current($nparts);
        $nparts = explode("/", $nfirst);
        $name = end($nparts);

        $outputfile = $thumbsdir.DS.$name.'_'.$article->id.'_'.$prefix.'.jpg';
        $outputpath = $thumbspath.'/'.$name.'_'.$article->id.'_'.$prefix.'.jpg';

        if($params->get($prefix.'_thumbs_modify', 1)==1) {
            // Create Thumbnail if it doesn't already exist or if cache is disabled
            if($params->get('thumbs_cache', 1)==0 || !JFile::exists($outputfile)) {
                // JiFramework Check
                if(!function_exists('jiimport')) {
                    $html = JText::_('JIBLOGTOOLS_WARNING_JIFRAMEWORK');
                    return $html;
                }
                jiimport('jiimageprocessor');

                $JiImageProcessor = new JiImageProcessor();
                $imageMade = $JiImageProcessor->resizeImage($imagefile, $outputfile, $params, $type, $prefix, $debug);
                if($imageMade!=true) {
                    // Error: Could not process image!
                    $html = ($debug==1)? '<div>Error: Could not process image!</div><div>'.$JiImageProcessor->errormsg.'</div>' : false;
                    return $html;
                }
            }
        } else {
            // Just copy the original to the cache
            if($params->get('thumbs_cache', 1) == 0 || !JFile::exists($outputfile)) JFile::copy($imagefile, $outputfile);
        }

        if($params->get($prefix.'_thumbs_preserveattribs', 1)!=1) $imageattribs = array();
        if(isset($imageattribs['src'])) unset($imageattribs['src']);
        if(isset($imageattribs['width'])) unset($imageattribs['width']);
        if(isset($imageattribs['height'])) unset($imageattribs['height']);

        // Build IMG attributes
        if(!isset($imageattribs['alt'])) $imageattribs['alt'] = $name;
        $imageattribs['class'] = 'jiimg';
        if(isset($imageattribs['class'])) $imageattribs['class'].' '.$imageattribs['class'];

            // Build html
        $html = '<img src="'.$outputpath.'" ';
        foreach($imageattribs as $attrib=>$value) {
            $html.= $attrib.'="'.$value.'" ';
        }
        $html.= '/>';
        $linktype = $params->get($prefix.'_thumbs_link', (($prefix=='art')?'image':'article'));
        $title = isset($article->title)? $article->title : '';
        if($linktype=='article' || $linktype=='category') {
            // article link
            $html = '<a class="thumblink '.$prefix.'thumb'.$icount.'" href="'.$link.'" title="'.$title.'">'.$html.'</a>';
        } elseif($linktype=='image') {
            // original image link
            $attrsdata = $params->get($prefix.'_thumbs_linkattr', 'rel="slimbox-images"');
            if($attrsdata!=null) {
                $attrsdata = $this->getDRT($attrsdata);
            }

            // map new attributes
            preg_match_all('#(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?#si', $attrsdata, $newattrs, PREG_SET_ORDER);
            $linkattrs = array();
            if($newattrs!=null) {
                foreach($newattrs as $newattr) {
                    if(isset($newattr[1])) $linkattrs[$newattr[1]] = $newattr[2];
                }
            }
            // add system attributes
            $class = 'thumblink '.$prefix.'thumb'.$icount;
            if(isset($linkattrs['class'])) {
                $linkattrs['class'] = $class.' '.$linkattrs['class'];
            } else {
                $linkattrs['class'] = $class;
            }
            if(!isset($linkattrs['target'])) $linkattrs['target'] = $params->get($prefix.'_thumbs_linktarget', '_blank');
            if(!isset($linkattrs['href'])) $linkattrs['href'] = $source;
            if(!isset($linkattrs['title'])) $linkattrs['title'] = $title;

            $linkattrstext = '';
            foreach($linkattrs as $attrib=>$value) {
                $linkattrstext.= $attrib.'="'.$value.'" ';
            }
            $html = '<a '.$linkattrstext.'>'.$html.'</a>';
        } else {
            // no link
            $html = '<span class="'.$prefix.'thumb'.$icount.'">'.$html.'</span>';
        }
        $imageprefix = $params->get($prefix.'_thumbs_prefix', '');
        $imagesuffix = $params->get($prefix.'_thumbs_suffix', '');
        $html = $imageprefix.$html.$imagesuffix;
        $html = $this->getDRT($html);
        
        return $html;
    }
}
?>