<?php
/**
 * @version     $Id: jigrid.php 025 2013-07-18 10:41:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.database.tablenested');
class JiGridTableJiGrid extends JTableNested
{
    var $id = null;

    var $title = null;

    function __construct(&$db)
    {
        parent::__construct('#__jigrid', 'id', $db);
    }
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state = (int) $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks))
        {
            if ($this->$k)
            {
                $pks = array($this->$k);
            }
            // Nothing to set publishing state on, return false.
            else
            {
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time'))
        {
            $checkin = '';
        }
        else
        {
            $checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
        }

        // Get the JDatabaseQuery object
        $query = $this->_db->getQuery(true);

        // Update the publishing state for rows with the given primary keys.
        $query->update($this->_db->quoteName($this->_tbl));
        $query->set($this->_db->quoteName('state') . ' = ' . (int) $state);
        $query->where('(' . $where . ')' . $checkin);
        $this->_db->setQuery($query);

        try
        {
            $this->_db->execute();
        }
        catch (RuntimeException $e)
        {
            $this->setError($e->getMessage());
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows()))
        {
            // Checkin the rows.
            foreach ($pks as $pk)
            {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks))
        {
            $this->state = $state;
        }

        $this->setError('');

        return true;
    }
    /**
     * Method to get the parent asset id for the record
     *
     * @param   JTable   $table  A JTable object (optional) for the asset parent
     * @param   integer  $id     The id (optional) of the content.
     *
     * @return  integer
     *
     * @since   11.1
     */
    protected function _getAssetParentId($table = null, $id = null)
    {
        $assetId = null;

        // This is a article under a category.
        if ($this->catid)
        {
            // Build the query to get the asset id for the parent category.
            $query = $this->_db->getQuery(true);
            $query->select($this->_db->quoteName('asset_id'));
            $query->from($this->_db->quoteName('#__categories'));
            $query->where($this->_db->quoteName('id') . ' = ' . (int) $this->catid);

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            if ($result = $this->_db->loadResult())
            {
                $assetId = (int) $result;
            }
        }

        // Return the asset id.
        if ($assetId)
        {
            return $assetId;
        }
        else
        {
            return parent::_getAssetParentId($table, $id);
        }
    }
    /**
     * Method to update order of table rows
     *
     * @param   array  $idArray    id numbers of rows to be reordered.
     * @param   array  $lft_array  lft values of rows to be reordered.
     *
     * @return  integer  1 + value of root rgt on success, false on failure.
     *
     * @since   11.1
     * @throws  RuntimeException on database error.
     */
    public function saveorder($idArray = null, $lft_array = null)
    {
        try
        {
            $query = $this->_db->getQuery(true);

            // Validate arguments
            if (is_array($idArray) && is_array($lft_array) && count($idArray) == count($lft_array))
            {
                for ($i = 0, $count = count($idArray); $i < $count; $i++)
                {
                    // Do an update to change the lft values in the table for each id
                    $query->clear()
                        ->update($this->_tbl)
                        ->where($this->_tbl_key . ' = ' . (int) $idArray[$i])
                        ->set('lft = ' . (int) $lft_array[$i].', ordering = 0');

                    $this->_db->setQuery($query)->execute();

                    // @codeCoverageIgnoreStart
                    if ($this->_debug)
                    {
                        $this->_logtable();
                    }
                    // @codeCoverageIgnoreEnd
                }

                return $this->rebuild();
            }
            else
            {
                return false;
            }
        }
        catch (Exception $e)
        {
            $this->_unlock();
            throw $e;
        }
    }
    /**
     * Checks that the object is valid and able to be stored.
     *
     * This method checks that the parent_id is non-zero and exists in the database.
     * Note that the root node (parent_id = 0) cannot be manipulated with this class.
     *
     * @return  boolean  True if all checks pass.
     *
     * @since   11.1
     * @throws  RuntimeException on database error.
     */
    public function check()
    {
        $this->parent_id = (int) $this->parent_id;
        if($this->alias!='root') {

        // Set up a mini exception handler.
        try
        {
            // Check that the parent_id field is valid.
            if ($this->parent_id == 0)
            {
                throw new UnexpectedValueException(sprintf('Invalid `parent_id` [%d] in %s', $this->parent_id, get_class($this)));
            }

            $query = $this->_db->getQuery(true);
            $query->select('COUNT(' . $this->_tbl_key . ')')
                ->from($this->_tbl)
                ->where($this->_tbl_key . ' = ' . $this->parent_id);

            if (!$this->_db->setQuery($query)->loadResult())
            {
                throw new UnexpectedValueException(sprintf('Invalid `parent_id` [%d] in %s', $this->parent_id, get_class($this)));
            }
        }
        catch (UnexpectedValueException $e)
        {
            // Validation error - record it and return false.
            $this->setError($e);

            return false;
        }
            // @codeCoverageIgnoreStart
        catch (Exception $e)
        {
            // Database error - rethrow.
            throw $e;
        }
        // @codeCoverageIgnoreEnd
        }

        return true;
    }
}