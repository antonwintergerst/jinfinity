<?php 
/**
 * @version     $Id: articles.php 020 2013-07-23 12:33:00Z Anton Wintergerst $
 * @package     Jinfinity Blog Tools Content Plugin for Joomla 1.5+
 * @copyright   Copyright (C) 2012 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(version_compare( JVERSION, '1.6.0', 'ge' )) {
    // Joomla 1.7+ Compatibility
    jimport('joomla.form.helper');
    JFormHelper::loadFieldClass('list');
    
    class JFormFieldArticles extends JFormFieldList 
    {
    
        protected $type = 'Articles';
     
        public function getOptions()
        {
            // Load Database Object
            $db = JFactory::getDBO();
            // Query Statement
            $query = 'SELECT `id`, `title` FROM #__content WHERE `state`=1';
            $query.= ' ORDER BY `title` ASC';
            // Set Query
            $db->setQuery( $query );
            // Load Data
            $articles = $db->loadObjectList();
            if($articles!=null) {
                foreach($articles as $article) {
                    $options[] = JHTML::_('select.option',  $article->id, $article->title );
                }
            }
            return $options;
        }
    }
} else {
    // Joomla 1.5 Compatibility
    class JElementArticles extends JElement {
    
        var $_name = 'Articles';
    
        function fetchElement($name, $value, &$node, $control_name)
        {
            $fieldName = $control_name.'['.$name.']';
            // Load Database Object
            $db =& JFactory::getDBO();
            // Query Statement
            $query = 'SELECT `id`, `title` FROM #__content WHERE `state`=1';
            $query.= ' ORDER BY `title` ASC';
            // Set Query
            $db->setQuery( $query );
            // Load Data
            $articles = $db->loadObjectList();
            if($articles!=null) {
                foreach($articles as $article) {
                    $options[] = JHTML::_('select.option',  $article->id, $article->title );
                }
            }
            
            $attribs = ' ';
            if ($v = $node->attributes( 'size' )) {
                $attribs.= 'size="'.$v.'"';
            }
            if ($v = $node->attributes( 'class' )) {
                $attribs.= 'class="'.$v.'"';
            } else {
                $attribs.= 'class="inputbox"';
            }
            if ($m = $node->attributes( 'multiple' ))
            {
                $attribs.= ' multiple="multiple"';
                $fieldName.= '[]';
            }
            
            $output= JHTML::_('select.genericlist', $options, $fieldName, $attribs, 'value', 'text', $value );
            return $output;
        }
    }
}