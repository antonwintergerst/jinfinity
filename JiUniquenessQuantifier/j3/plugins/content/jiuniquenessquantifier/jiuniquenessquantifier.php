<?php
/**
 * @version     $Id: jiuniquenessquantifier.php 020 2014-12-08 11:25:00Z Anton Wintergerst $
 * @package     JiUniquenessQuantifier Content Plugin
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

class plgContentJiUniquenessQuantifier extends JPlugin
{
    public function onContentBeforeSave($context, $article, $isNew)
    {
        $id = (isset($article->id))? (int) $article->id : 0;

        /* >>> PRO >>> */
        // check if alias will be force generated from title
        $forceAlias = false;
        jiimport('JiAccess');
        $params = $this->getParams('content', 'jiuniquenessquantifier');
        $accessData = $params->get('rules', array(), 'array');

        if(count($accessData)>0) {
            // prime access class
            JiAccess::getAssetRules($accessData);
            $user = JFactory::getUser();
            $userExtended = new JiAccessUser($user->id);

            // authorise extended user
            if(!$userExtended->authorise('content.edit.alias')) {
                // force alias generation from title
                $forceAlias = true;
                $article->alias = null;
            }
        }
        /* <<< PRO <<< */

        $aliasEmpty = (!isset($article->alias) || empty($article->alias));

        $titleChanged = false;
        $aliasChanged = false;
        if(!$isNew) {
            // load existing article
            $item = JTable::getInstance('Content', 'JTable');
            $item->load($article->id);
            if($item->title!=$article->title) $titleChanged = true;
            if($aliasEmpty || $item->alias!=$article->alias) {
                // attach existing alias for front-end form
                if($aliasEmpty/* >>> PRO >>> */ && !$forceAlias/* <<< PRO <<< */) {
                    $article->alias = $item->alias;
                    $aliasEmpty = false;
                }
                $aliasChanged = true;
            }
        }

        // find a unique alias for new articles
        // or when changing the article title
        if($isNew || $aliasEmpty || $titleChanged || $aliasChanged) {
            if(!$aliasEmpty) {
                // convert the supplied alias to a safe format
                $alias = JApplicationHelper::stringURLSafe($article->alias);
            } else {
                // create a safe alias from the article title
                $alias = JApplicationHelper::stringURLSafe($article->title);
                $article->alias = $alias;
            }
            // check if the alias already exists
            $existingTitle = $this->aliasExists($alias, $id, $article->catid);
            if($existingTitle!==false) {
                // alias is already in use
                while($existingTitle!==false) {
                    // continue searching for the next available alias
                    $alias = JString::increment($alias, 'dash');
                    $existingTitle = $this->aliasExists($alias, $id, $article->catid);
                }
                // update the article with the new alias
                $article->alias = $alias;
            }
        }
        return true;
    }

    public function aliasExists($alias, $id=0, $catid)
    {
        $db = JFactory::getDBO();

        //$query = 'SELECT `title` FROM #__content WHERE `alias`='.$db->quote($alias).' AND `catid`='.(int)$catid;
        $query = $db->getQuery(true);
        $query->select('`title`');
        $query->from('#__content');
        $query->where('`alias`='.$db->quote($alias).' AND `catid`='.(int)$catid);
        if((int)$id!=0) $query->where('`id`!='.(int)$id);

        $db->setQuery($query);
        $result = $db->loadObject();
        if($result==null) return false;

        return $result;
    }
    private function getParams($pluginType, $pluginName) {
        // Load Plugin Params
        if(version_compare( JVERSION, '1.6.0', 'ge' )) {
            // Get plugin params
            $plugin = JPluginHelper::getPlugin($pluginType, $pluginName);
            $params = new JRegistry($plugin->params);
        } else {
            // Get plugin params
            $plugin = &JPluginHelper::getPlugin($pluginType, $pluginName);
            $params = new JParameter($plugin->params);
        }
        return $params;
    }
}