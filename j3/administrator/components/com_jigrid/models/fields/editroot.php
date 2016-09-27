<?php
/**
 * @version     $Id: editroot.php 036 2013-07-17 15:35:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('text');

class JFormFieldEditRoot extends JFormField
{
    protected $type = 'EditRoot';

    public function getInput() {
        ob_start(); ?>
        <a href="index.php?option=com_jigrid&task=griditem.edit&id=1"><?php echo JText::_('COM_JIGRID_EDITROOT_LINK'); ?></a>
        <?php $html = ob_get_clean();
        return $html;
    }
}