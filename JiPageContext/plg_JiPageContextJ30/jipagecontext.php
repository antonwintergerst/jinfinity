<?php 
/**
 * @version     $Id: jipagecontext.php 048 2014-03-04 16:41:00Z Anton Wintergerst $
 * @package     JiPageContext System Plugin for Joomla 3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

class plgSystemJiPageContext extends JPlugin
{
    public function getDRT($text) {
        $regex = "#{(.*?)}#s";
        $text = preg_replace_callback($regex, array(&$this,'replaceAttribute'), $text);
        return $text;
    }
    public function replaceAttribute($matches) {
        $result = '';
        if(isset($matches[1])) {
            $attr = $matches[1];
            if(isset($this->item->{$attr})) $result = $this->item->{$attr};
        }
        return $result;
    }
    public function onAfterDispatch() {
        $app = JFactory::getApplication();
        if($app->isSite()) {
            $view = JRequest::getCmd('view');
            $id = JRequest::getVar('id');
            // Category Item
            if($view=='category') {
                $db = JFactory::getDBO();
                $query = 'SELECT * FROM #__categories WHERE `id`='.$db->quote($id);
                $db->setQuery($query);
                $item = $db->loadObject();
                if($item!=null) $this->processItem($item, 'cat');
            }
            // Menu Item
            $menu = JFactory::getApplication()->getMenu();
            $active = $menu->getActive();
            if($active!=null) {
                $temp = clone $active;
                $temp->home = ($active==$menu->getDefault())? 'ishome' : 'nothome';
                $this->processItem($temp, 'men');
                // Parent Menu Item
                if(isset($active->parent_id)) {
                    $pid = $active->parent_id;
                    $parent = $menu->getItem($pid);
                    if($parent!=null) {
                        $temp2 = clone $parent;
                        $temp2->home = ($parent==$menu->getDefault())? 'ishome' : 'nothome';
                        $this->processItem($temp2, 'parmen');
                        // Grand Parent Menu Item
                        if(isset($parent->parent_id)) {
                            $pid2 = $parent->parent_id;
                            $parent2 = $menu->getItem($pid2);
                            if($parent2!=null) {
                                $temp3 = clone $parent2;
                                $temp3->home = ($parent2==$menu->getDefault())? 'ishome' : 'nothome';
                                $this->processItem($temp3, 'par2men');
                            }
                        }
                    }
                }
            }
        }
    }
	public function onContentBeforeDisplay($context, &$item, &$params, $limitstart = 0)
    {
        $app = JFactory::getApplication();
        if($app->isSite()) {
            // Article Item
            $view = JRequest::getCmd('view');
            $id = JRequest::getVar('id');
            if($context=='com_content.article' && $view=='article' && $id==$item->id) {
                $this->processItem($item, 'art');
            }
        }
    }
    function processItem($item=null, $type='') {
        // Load Plugin Params
        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin('system', 'jipagecontext');
            $params = new JRegistry();
            $params->loadString($plugin->params);
        } else {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin('system', 'jipagecontext');
            $params = new JParameter($plugin->params);
        }
        $newclassnames = $params->get($type.'clientclass');

        if($newclassnames!=null) {
            $this->item = &$item;
            $newclassnames = $this->getDRT($newclassnames);

            $element = $params->get($type.'clientelement', 'body');
            $jsparams = array(
                'newclassnames'=>$newclassnames
            );
            JHtml::addIncludePath(JPATH_SITE.'/media/jinfinity/html');
            JHtml::_('jquery.framework');
            $document = JFactory::getDocument();
            $document->addScript(JURI::root().'plugins/system/jipagecontext/assets/jquery.jipagecontext.js');
            $js = "if(typeof jQuery!='undefined') {
                    jQuery(document).ready(function() {
                        jQuery('".$element."').jipagecontext(".json_encode($jsparams).");
                    });
                }";
            $document->addScriptDeclaration($js);
        }
    }
}