<?php
/**
 * @version     $Id: field.php 019 2014-12-31 10:10:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.application.component.view');

class JiCustomFieldsModelField extends JModelAdmin
{
    /**
     * @var		string	The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_JICUSTOMFIELDS';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param	object	$record	A record object.
     *
     * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->state != -2) {
                return;
            }
            $user = JFactory::getUser();
            return $user->authorise('core.delete', 'com_content.article.'.(int) $record->id);
        }
    }

    protected function canEditState($record)
    {
        return parent::canEditState($record);
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param	JTable	A JTable object.
     *
     * @return	void
     */
    protected function prepareTable($table)
    {
        if (empty($table->id))
        {
            // Set ordering to the last item if not set
            if (empty($table->ordering))
            {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true)
                    ->select('MAX(ordering)')
                    ->from('#__jifields');

                $db->setQuery($query);
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }
    }

    /**
     * Returns a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     *
     * @return	JTable	A database object
     */
    public function getTable($type = 'Fields', $prefix = 'JiCustomFieldsTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get a single record.
     *
     * @param	integer	The id of the primary key.
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->attribs);
            $item->attribs = $registry->toArray();
        }

        return $item;
    }

    /**
     * Method to get the record form.
     *
     * @param	array	$data		Data for the form.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     *
     * @return	mixed	A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jicustomfields.field', 'field', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        $jinput = JFactory::getApplication()->input;

        // The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
        if ($jinput->get('a_id'))
        {
            $id = $jinput->get('a_id', 0);
        }
        // The back end uses id so we use that the rest of the time and set it to 0 by default.
        else
        {
            $id = $jinput->get('id', 0);
        }
        // Determine correct permissions to check.
        if ($this->getState('field.id'))
        {
            $id = $this->getState('field.id');
        }
        else
        {
            // New record. Can only create in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.create');
        }

        $user = JFactory::getUser();

        // Check for existing article.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_content.article.'.(int) $id))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_content'))
        )
        {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');

        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_jicustomfields.edit.field.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param	array	The form data.
     *
     * @return	boolean	True on success.
     */
    public function save($data)
    {
        $jinput = JFactory::getApplication()->input;

        $fields = $jinput->get('jifields', null, 'raw');
        if(isset($data['state'])) {
            foreach($fields as &$field) {
                $field['state'] = $data['state'];
            }
            $jinput->set('jifields', $fields);
        }

        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
        if(version_compare(JVERSION, '3', 'ge')) {
            $model = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel');
        } else {
            $model = JModel::getInstance('Fields', 'JiCustomFieldsModel');
        }

        $result = $model->store(null, 'com_jicustomfields.field');

        // transfer field id state to this model
        $this->setState($this->getName() . '.id', $model->getState('field.id'));

        if($result->valid=="true") {
            return true;
        } else {
            $this->setError($result->msg);
            return false;
        }
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param	object	A record object.
     *
     * @return	array	An array of conditions to add to add to ordering queries.
     */
    protected function getReorderConditions($table)
    {
        $condition = array();
        $condition[] = 'state >= 0';
        return $condition;
    }

    /**
     * Custom clean the cache of com_jicustomfields and jicustomfields modules
     *
     */
    protected function cleanCache($group = null, $client_id = 0)
    {
        parent::cleanCache('com_jicustomfields');
    }
    /* Batch Functions */
    public function batch($commands, $pks, $contexts)
    {
        // Sanitize user ids.
        $pks = array_unique($pks);
        JArrayHelper::toInteger($pks);

        // Remove any values of zero.
        if (array_search(0, $pks, true))
        {
            unset($pks[array_search(0, $pks, true)]);
        }

        if (empty($pks))
        {
            $this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
            return false;
        }

        $done = false;

        if (!empty($commands['context']))
        {
            $cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

            if ($cmd == 'c')
            {
                $result = $this->batchCopy($commands['context'], $pks, $contexts);
                if (is_array($result))
                {
                    $pks = $result;
                }
                else
                {
                    return false;
                }
            }
            elseif ($cmd == 'm' && !$this->batchMove($commands['context'], $pks, $contexts))
            {
                return false;
            }
            $done = true;
        }

        if (!$done)
        {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }
    /**
     * Batch copy items to a new context or current.
     *
     * @param   integer  $value     The new context.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     */
    protected function batchCopy($context, $pks, $contexts)
    {
        if(empty($context)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
            return false;
        }

        $table = $this->getTable();
        $i = 0;

        // Parent exists so we let's proceed
        while (!empty($pks))
        {
            // Pop the first ID off the stack
            $pk = array_shift($pks);

            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk))
            {
                if ($error = $table->getError())
                {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else
                {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Reset the ID because we are making a copy
            $table->id = 0;

            // TODO: Deal with ordering?
            //$table->ordering	= 1;

            // Check the row.
            if (!$table->check())
            {
                $this->setError($table->getError());
                return false;
            }

            // Store the row.
            if (!$table->store())
            {
                $this->setError($table->getError());
                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$i]	= $newId;
            $i++;
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
    }
    protected function batchMove($context, $pks, $contexts)
    {
        if(empty($context)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
            return false;
        }
        $table = $this->getTable();

        // Parent exists so we let's proceed
        foreach ($pks as $pk)
        {
            // Check that the row actually exists
            if (!$table->load($pk))
            {
                if ($error = $table->getError())
                {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else
                {
                    // Not fatal error
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // New context
            $table->context = $context;

            // Check the row.
            if (!$table->check())
            {
                $this->setError($table->getError());
                return false;
            }

            // Store the row.
            if (!$table->store())
            {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }
}