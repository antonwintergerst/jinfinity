<?php
/**
 * @version     $Id: form.php 012 2013-09-03 10:17:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal form picker.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.6
 */
class JFormFieldModal_Form extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Modal_Form';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		JFactory::getDocument()->addScriptDeclaration('function jSelectForm_'.$this->id.'(id, title, object) {
            document.id("'.$this->id.'_id").value = id;
            document.id("'.$this->id.'_name").value = title;
            SqueezeBox.close();
        }');

		// Setup variables for display.
		$html	= array();
		$link	= 'index.php?option=com_jiforms&amp;view=forms&amp;layout=modal&amp;tmpl=component&amp;function=jSelectForm_'.$this->id;

		$db	= JFactory::getDBO();
		$db->setQuery(
			'SELECT title' .
			' FROM #__jiforms' .
			' WHERE id = '.(int) $this->value
		);

		try
		{
			$title = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		if (empty($title)) {
			$title = JText::_('COM_CONTENT_SELECT_AN_ARTICLE');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current user display field.
		$html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" /><a class="modal btn" title="'.JText::_('COM_CONTENT_CHANGE_ARTICLE').'"  href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> '.JText::_('JSELECT').'</a>';
		$html[] = '</span>';

		// The active article id field.
		if (0 == (int) $this->value) {
			$value = '';
		} else {
			$value = (int) $this->value;
		}

		// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		return implode("\n", $html);
	}
}
