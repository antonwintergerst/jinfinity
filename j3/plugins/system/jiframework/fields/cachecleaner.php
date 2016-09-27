<?php
/**
 * @version     $Id: cachecleaner.php 010 2014-12-18 18:33:00Z Anton Wintergerst $
 * @package     JiCacheCleaner for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JFormFieldCacheCleaner extends JFormField
{
    protected $type = 'cachecleaner';

    protected function getLabel()
    {
        return '';
    }

    protected function getInput()
    {
        $this->params = $this->element->attributes();

        $document = JFactory::getDocument();
        $document->addScript(JURI::root().'media/jiframework/js/jquery.jicachecleaner.js');

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $result = $jinput->get('cleancache');

        if($result!==null) {
            $app->enqueueMessage('Ji image cache successfully cleared', 'message');
            $dirs = array(
                JPATH_SITE.'/images/jipreviews',
                JPATH_SITE.'/images/jithumbs'
            );
            foreach($dirs as $dir) {
                $this->jidelete($dir);
            }
            $document->addScriptDeclaration("console.log('JiCache cleared!');");
        }

        $uri = JFactory::getURI();
        $pageURL = $uri->toString();

        $document->addScriptDeclaration("jQuery(document).ready(function() {
            jQuery(document).jicachecleaner({'url':'".$this->get('url', $pageURL)."&cleancache'});
        });");
        return '';
    }

    private function jidelete($dir)
    {
        if(in_array($dir, array('.', '..'))) return false;
        if(is_file($dir)) {
            return unlink($dir);
        } elseif(!is_dir($dir)) {
            return false;
        }
        $files = scandir($dir);
        foreach($files as $file) {
            if(in_array($file, array('.', '..'))) continue;
            $file = $dir.'/'.$file;
            if(is_dir($file)) {
                $this->jidelete($file);
            } else {
                unlink($file);
            }
        }
        return rmdir($dir);
    }

    private function get($var, $default = '')
    {
        return (isset($this->params[$var]) && (string) $this->params[$var] != '') ? (string) $this->params[$var] : $default;
    }
}