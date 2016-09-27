<?php
/**
 * @version     $Id: griditem.php 020 2013-06-24 10:30:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modeladmin');

class JiGridModelGridItem extends JModelAdmin
{
    /**
     * @var		string	The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_JIGRID';

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
        elseif (!empty($record->parent_id)) {
            return $user->authorise('core.edit.state', 'com_content.category.'.(int) $record->parent_id);
        }
        // Default to component settings if neither article nor category known.
        else {
            return parent::canEditState('com_content');
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
    public function getTable($type = 'JiGrid', $prefix = 'JiGridTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    protected function populateState()
    {
        $app = JFactory::getApplication('administrator');

        $parentId = $app->input->getInt('parent_id');
        $this->setState('griditem.parent_id', $parentId);

        // Load the User state.
        $pk = $app->input->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_jigrid');
        $this->setState('params', $params);
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
        $form = $this->loadForm('com_jigrid.griditem', 'griditem', array('control' => 'jform', 'load_data' => $loadData));
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
        if ($this->getState('griditem.id'))
        {
            $id = $this->getState('griditem.id');
        }
        else
        {
            // New record. Can only create in selected categories.
            $form->setFieldAttribute('parent_id', 'action', 'core.create');
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
        $data = $app->getUserState('com_jigrid.edit.griditem.data', array());

        if (empty($data)) {
            $data = $this->getItem();

            // Prime some default values.
            if($this->getState('griditem.id') == 0) {
                $parentid = $app->input->get('parent_id', $app->getUserState('com_jigrid.griditems.filter.parent_id'));
                $data->set('parent_id', $parentid);
                $data->set('parent_grid', $parentid);
                $data->set('parent_row', $parentid);
                $data->set('parent_cell', $parentid);
            } else {
                $parentid = $data->get('parent_id');
                $data->set('parent_grid', $parentid);
                $data->set('parent_row', $parentid);
                $data->set('parent_cell', $parentid);
            }
        }

        return $data;
    }

    public function updateParams($params) {
        // Get a new database query instance
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        // Build the query
        $query->update('#__extensions AS a');
        $query->set('a.params = ' . $db->quote((string)$params));
        $query->where('a.element = "com_jigrid"');

        // Execute the query
        $db->setQuery($query);
        $db->query();
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

            // Attach different data depending on mode
            if(isset($data['mode'])) {
                // Update global mode
                $jiparams = JComponentHelper::getParams('com_jigrid');
                $jiparams->set('mode', $data['mode']);
                $this->updateParams($jiparams);

                if($data['mode']=='easy') {
                    $type = $data['type'];
                    if(isset($data['parent_'.$type])) $data['parent_id'] = $data['parent_'.$type];
                } else {
                    // HTML Class
                    $class = $data['attribs']['class'];
                    foreach(array('tv', 'desktop', 'tablet', 'phone') as $screentype) {
                        $class = str_replace('hide-'.$screentype, '', $class);
                        if(trim($class)!='') $class.= ' ';
                        if(isset($data['attribs']['hide_'.$screentype]) && $data['attribs']['hide_'.$screentype]==1) $class.= 'hide-'.$screentype;
                    }
                    $class = str_replace(array('tv-only', 'desktop-only', 'tablet-only', 'phone-only'), '', $class);
                    if(trim($class)!='') $class.= ' ';
                    if(isset($data['attribs']['only_type']) && $data['attribs']['only_type']!='') $class.= $data['attribs']['only_type'].'-only';
                    $data['attribs']['class'] = trim($class);
                }
            }
            // Encode attribs
            $data['attribs'] = json_encode($data['attribs']);

            // Set the new parent id if parent id not matched OR while New/Save as Copy .
            if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
            {
                $table->setLocation($data['parent_id'], 'last-child');
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

        // Rebuild the path for the node:
        if(!$table->rebuildPath($table->id))
        {
            $this->setError($table->getError());
            return false;
        }

        // Rebuild the paths of the node's children:
        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path))
        {
            $this->setError($table->getError());
            return false;
        }

        return true;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks    A list of the primary keys to change.
     * @param   integer  $value   The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    public function publish(&$pks, $value = 1)
    {
        if (parent::publish($pks, $value))
        {
            return true;
        }
    }

    /**
     * Method rebuild the entire nested set tree.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   1.6
     */
    public function rebuild()
    {
        // Get an instance of the table object.
        $table = $this->getTable();

        if (!$table->rebuild())
        {
            $this->setError($table->getError());
            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }
    /**
     * Method to save the reordered nested set tree.
     * First we save the new order values in the lft values of the changed ids.
     * Then we invoke the table rebuild to implement the new ordering.
     *
     * @param   array    $idArray    An array of primary key ids.
     * @param   integer  $lft_array  The lft value
     *
     * @return  boolean  False on failure or error, True otherwise
     *
     * @since   1.6
     */
    public function saveorder($idArray = null, $lft_array = null)
    {
        // Get an instance of the table object.
        $table = $this->getTable();

        if (!$table->saveorder($idArray, $lft_array))
        {
            $this->setError($table->getError());
            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Batch copy categories to a new category.
     *
     * @param   integer  $value     The new category.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     * @since   1.6
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        // $value comes as {parent_id}.{extension}
        $parts = explode('.', $value);
        $parentId = (int) JArrayHelper::getValue($parts, 0, 1);

        $table = $this->getTable();
        $db = $this->getDbo();
        $i = 0;

        // Check that the parent exists
        if ($parentId)
        {
            if (!$table->load($parentId))
            {
                if ($error = $table->getError())
                {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else
                {
                    // Non-fatal error
                    $this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $parentId = 0;
                }
            }
            // Check that user has create permission for parent category
            /*$canCreate = ($parentId == $table->getRootId()) ? $user->authorise('core.create', $extension) : $user->authorise('core.create', $extension . '.category.' . $parentId);
            if (!$canCreate)
            {
                // Error since user cannot create in parent category
                $this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));
                return false;
            }*/
        }

        // If the parent is 0, set it to the ID of the root item in the tree
        if (empty($parentId))
        {
            if (!$parentId = $table->getRootId())
            {
                $this->setError($db->getErrorMsg());
                return false;
            }
            // Make sure we can create in root
            /*elseif (!$user->authorise('core.create', $extension))
            {
                $this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));
                return false;
            }*/
        }

        // We need to log the parent ID
        $parents = array();

        // Calculate the emergency stop count as a precaution against a runaway loop bug
        $query = $db->getQuery(true);
        $query->select('COUNT(id)');
        $query->from($db->quoteName('#__jigrid'));
        $db->setQuery($query);

        try
        {
            $count = $db->loadResult();
        }
        catch (RuntimeException $e)
        {
            $this->setError($e->getMessage());
            return false;
        }

        // Parent exists so we let's proceed
        while (!empty($pks) && $count > 0)
        {
            // Pop the first id off the stack
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
                    $this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Copy is a bit tricky, because we also need to copy the children
            $query->clear();
            $query->select('id');
            $query->from($db->quoteName('#__jigrid'));
            $query->where('lft > ' . (int) $table->lft);
            $query->where('rgt < ' . (int) $table->rgt);
            $db->setQuery($query);
            $childIds = $db->loadColumn();

            // Add child ID's to the array only if they aren't already there.
            foreach ($childIds as $childId)
            {
                if (!in_array($childId, $pks))
                {
                    array_push($pks, $childId);
                }
            }

            // Make a copy of the old ID and Parent ID
            $oldId = $table->id;
            $oldParentId = $table->parent_id;

            // Reset the id because we are making a copy.
            $table->id = 0;

            // If we a copying children, the Old ID will turn up in the parents list
            // otherwise it's a new top level item
            $table->parent_id = isset($parents[$oldParentId]) ? $parents[$oldParentId] : $parentId;

            // Set the new location in the tree for the node.
            $table->setLocation($table->parent_id, 'last-child');

            // TODO: Deal with ordering?
            // $table->ordering	= 1;
            $table->level = null;
            //$table->asset_id = null;
            $table->lft = null;
            $table->rgt = null;

            // Alter the title & alias
            list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
            $table->title = $title;
            $table->alias = $alias;

            // Store the row.
            if (!$table->store())
            {
                $this->setError($table->getError());
                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$i] = $newId;
            $i++;

            // Now we log the old 'parent' to the new 'parent'
            $parents[$oldId] = $table->id;
            $count--;
        }

        // Rebuild the hierarchy.
        if (!$table->rebuild())
        {
            $this->setError($table->getError());
            return false;
        }

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id))
        {
            $this->setError($table->getError());
            return false;
        }

        return $newIds;
    }

    /**
     * Batch move categories to a new category.
     *
     * @param   integer  $value     The new category ID.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    protected function batchMove($value, $pks, $contexts)
    {
        $parentId = (int) $value;

        $table = $this->getTable();
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Check that the parent exists.
        if ($parentId)
        {
            if (!$table->load($parentId))
            {
                if ($error = $table->getError())
                {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                else
                {
                    // Non-fatal error
                    $this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $parentId = 0;
                }
            }
            // Check that user has create permission for parent category
            /*$canCreate = ($parentId == $table->getRootId()) ? $user->authorise('core.create', $extension) : $user->authorise('core.create', $extension . '.category.' . $parentId);
            if (!$canCreate)
            {
                // Error since user cannot create in parent category
                $this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_CREATE'));
                return false;
            }*/

            // Check that user has edit permission for every category being moved
            // Note that the entire batch operation fails if any category lacks edit permission
            /*foreach ($pks as $pk)
            {
                if (!$user->authorise('core.edit', $extension . '.category.' . $pk))
                {
                    // Error since user cannot edit this category
                    $this->setError(JText::_('COM_CATEGORIES_BATCH_CANNOT_EDIT'));
                    return false;
                }
            }*/
        }

        // We are going to store all the children and just move the category
        $children = array();

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
                    $this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Set the new location in the tree for the node.
            $table->setLocation($parentId, 'last-child');

            // Check if we are moving to a different parent
            if ($parentId != $table->parent_id)
            {
                // Add the child node ids to the children array.
                $query->clear();
                $query->select('id');
                $query->from($db->quoteName('#__jigrid'));
                $query->where($db->quoteName('lft') . ' BETWEEN ' . (int) $table->lft . ' AND ' . (int) $table->rgt);
                $db->setQuery($query);

                try
                {
                    $children = array_merge($children, (array) $db->loadColumn());
                }
                catch (RuntimeException $e)
                {
                    $this->setError($e->getMessage());
                    return false;
                }
            }

            // Store the row.
            if (!$table->store())
            {
                $this->setError($table->getError());
                return false;
            }

            // Rebuild the tree path.
            if (!$table->rebuildPath())
            {
                $this->setError($table->getError());
                return false;
            }
        }

        // Process the child rows
        if (!empty($children))
        {
            // Remove any duplicates and sanitize ids.
            $children = array_unique($children);
            JArrayHelper::toInteger($children);
        }

        return true;
    }

    /**
     * Custom clean the cache of com_content and content modules
     *
     * @since	1.6
     */
    protected function cleanCache($group = null, $client_id = 0)
    {
        $extension = JFactory::getApplication()->input->get('extension');
        switch ($extension)
        {
            case 'com_jigrid':
                parent::cleanCache('com_jigrid');
                parent::cleanCache('mod_jigrid');
                break;
            default:
                parent::cleanCache($extension);
                break;
        }
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $parent_id  The id of the parent.
     * @param   string   $alias      The alias.
     * @param   string   $title      The title.
     *
     * @return  array  Contains the modified title and alias.
     *
     * @since	1.7
     */
    protected function generateNewTitle($category_id, $alias, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();
        while ($table->load(array('alias' => $alias, 'parent_id' => $category_id)))
        {
            $title = JString::increment($title);
            $alias = JString::increment($alias, 'dash');
        }

        return array($title, $alias);
    }
}