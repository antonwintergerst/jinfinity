<?php
/**
 * @version     $Id: email.php 013 2013-09-03 12:07:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.application.component.view');

class JiFormsModelEmail extends JModelAdmin
{
    /**
     * @var		string	The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_JIFORMS';

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

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param	object	$record	A record object.
     *
     * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
     */
    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        // Check for existing article.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_content.article.'.(int) $record->id);
        }
        // New article, so check against the category.
        elseif (!empty($record->catid)) {
            return $user->authorise('core.edit.state', 'com_content.category.'.(int) $record->catid);
        }
        // Default to component settings if neither article nor category known.
        else {
            return parent::canEditState('com_content');
        }
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
        // Set the publish date to now
        $db = $this->getDbo();
        if($table->state == 1 && (int) $table->publish_up == 0) {
            $table->publish_up = JFactory::getDate()->toSql();
        }

        if($table->state == 1 && intval($table->publish_down) == 0)
        {
            $table->publish_down = $db->getNullDate();
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
    public function getTable($type = 'Emails', $prefix = 'JiFormsTable', $config = array())
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
            if(isset($item->attribs)) $registry->loadString($item->attribs);
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
        $form = $this->loadForm('com_jiforms.email', 'email', array('control' => 'jform', 'load_data' => $loadData));
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
        if ($this->getState('email.id'))
        {
            $id = $this->getState('email.id');
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
            $form->setFieldAttribute('featured', 'disabled', 'true');
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('featured', 'filter', 'unset');
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
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
        $data = $app->getUserState('com_jiforms.edit.email.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            $data->message = htmlspecialchars_decode($data->message);
            // Move attribs back into data
            $headerdata = json_decode($data->headers, true);
            $headers = array(
                'to'=>'',
                'cc'=>'',
                'bcc'=>'',
                'from'=>'',
                'replyto'=>''
            );
            foreach($headers as $header=>$value) {
                $data->{$header} = (isset($headerdata[$header]))? $headerdata[$header] : '';
            }

            // Prime some default values.
            if($this->getState('email.id') == 0) {
                $data->set('context', $app->input->get('context', $app->getUserState('com_jiforms.emails.filter.context')));
            }
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
        $data['message'] = htmlspecialchars($data['message']);
        // Move data into headers
        $headers = array(
            'to'=>'',
            'cc'=>'',
            'bcc'=>'',
            'from'=>'',
            'replyto'=>''
        );
        foreach($headers as $header=>$value) {
            if(isset($data[$header])) {
                $headers[$header] = $data[$header];
                unset($data[$header]);
            }
        }

        $data['headers'] = json_encode($headers);

        $table = $this->getTable();
        $key = $table->getKeyName();
        $pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $isNew = true;

        // Allow an exception to be thrown.
        try
        {
            // Load the row if saving an existing record.
            if ($pk > 0)
            {
                $table->load($pk);
                $isNew = false;
            }

            // Bind the data.
            if (!$table->bind($data))
            {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check())
            {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store())
            {
                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());

            return false;
        }

        $pkName = $table->getKeyName();

        if (isset($table->$pkName))
        {
            $this->setState($this->getName() . '.id', $table->$pkName);
        }
        $this->setState($this->getName() . '.new', $isNew);
        return true;

        /*if (parent::save($data)) {
            return true;
        }

        return false;*/
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
        return $condition;
    }

    /**
     * Custom clean the cache of com_jiforms and jiforms modules
     *
     */
    protected function cleanCache($group = null, $client_id = 0)
    {
        parent::cleanCache('com_jiforms');
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

            // New context
            $table->context = $context;

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