<?php
/**
 * @version     $Id: catmap.php 005 2014-10-27 11:55:00Z Anton Wintergerst $
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

class JiCustomFieldsModelCatMap extends JModelAdmin
{
    protected $text_prefix = 'COM_JICUSTOMFIELDS';

    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            $user = JFactory::getUser();
            return $user->authorise('core.delete', 'com_content.article.'.(int) $record->id);
        }
    }

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

    protected function prepareTable($table)
    {
    }

    public function getTable($type = 'CatMaps', $prefix = 'JiCustomFieldsTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            if(isset($item->attribs)) {
                // Convert the params field to an array.
                $registry = new JRegistry;
                $registry->loadString($item->attribs);
                $item->attribs = $registry->toArray();
            }
        }

        return $item;
    }

    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jicustomfields.catmap', 'catmap', array('control' => 'jform', 'load_data' => $loadData));
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
        if ($this->getState('catmap.id'))
        {
            $id = $this->getState('catmap.id');
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

    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_jicustomfields.edit.catmap.data', array());

        if (empty($data)) {
            $data = $this->getItem();
            if(isset($data->catid) && $data->catid==0) $data->allcats = 1;
        }

        return $data;
    }

    public function save($data)
    {
        if(isset($data['attribs'])) $data['attribs'] = json_encode($data['attribs']);
        if(isset($data['allcats']) && $data['allcats']==1) $data['catid'] = 0;

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
    }

    protected function getReorderConditions($table)
    {
        $condition = array();
        return $condition;
    }

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

        if (!empty($commands['catid']))
        {
            $cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

            if ($cmd == 'c')
            {
                $result = $this->batchCopy($commands['catid'], $pks, $contexts);
                if (is_array($result))
                {
                    $pks = $result;
                }
                else
                {
                    return false;
                }
            }
            elseif ($cmd == 'm' && !$this->batchMove($commands['catid'], $pks, $contexts))
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

    protected function batchCopy($catid, $pks, $contexts)
    {
        if(empty($catid)) {
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

            // New catid
            $table->catid = $catid;

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
    protected function batchMove($catid, $pks, $contexts)
    {
        if(empty($catid)) {
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

            // New catid
            $table->catid = $catid;

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