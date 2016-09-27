<?php
/**
 * @version     $Id: subversions.php 022 2014-12-15 13:54:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
 
// No direct access 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modellist');
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class JiExtensionServerModelSubversions extends JModelList
{
    /**
     * Constructor.
     *
     * @param	array	An optional associative array of configuration settings.
     * @see		JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 's.id',
                'title', 'e.title',
                'alias', 'e.alias',
                'publisher', 'e.publisher',
                'jversion', 's.jversion',
                'subversion', 's.subversion',
                'downloadhits', 's.downloadhits',
                'updatehits', 's.updatehits',
                'state', 's.state',
                'publish_up', 's.publish_up',
                'publish_down', 's.publish_down',
                'ordering', 's.ordering'
            );
        }

        parent::__construct($config);
    }
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return	void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout'))
        {
            $this->context .= '.'.$layout;
        }

        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $publisher = $this->getUserStateFromRequest($this->context.'.filter.publisher', 'filter_publisher', '');
        $this->setState('filter.publisher', $publisher);

        // List state information.
        parent::populateState('s.subversion', 'desc');
    }
    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db		= $this->getDbo();
        $query	= $db->getQuery(true);
        $user	= JFactory::getUser();
        $app	= JFactory::getApplication();

        // Select the required fields from the table.
        $query->select('s.*');
        $query->from('#__jiextensions_subversions AS s');

        $query->select('b.alias AS jversion');
        $query->join('LEFT', '#__jiextensions_branches AS b ON (b.id = s.bid)');

        $query->select('e.title, e.alias, e.publisher');
        $query->join('LEFT', '#__jiextensions AS e ON (e.id = s.eid)');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('s.state = ' . (int) $published);
            $query->where('e.state = ' . (int) $published);
        } elseif ($published === '') {
            $query->where('(s.state = 0 OR s.state = 1)');
            $query->where('(e.state = 0 OR e.state = 1)');
        }
        // Filter by publisher
        if($publisher = $this->getState('filter.publisher')) {
            $query->where('e.publisher = ' .$db->quote($publisher));
        }
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 's.id:') === 0) {
                $query->where('s.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(e.title LIKE '.$search.')');
            }
        }
        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 's.subversion');
        $orderDirn	= $this->state->get('list.direction', 'desc');

        if($orderCol=='s.subversion') {
            $query->order($db->escape('e.title asc'));
            $query->order($db->escape('b.alias '.$orderDirn));
            $query->order($db->escape($orderCol.' '.$orderDirn));
        } else {
            $query->order($db->escape($orderCol.' '.$orderDirn));
        }

        return $query;
    }
    /**
     * Method to get a list of jiextensions.
     *
     * @return	mixed	An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $items	= parent::getItems();
        return $items;
    }
    public function scan() {
        $return = new stdClass();

        $params =JComponentHelper::getParams('com_jiextensionserver');
        $scandir = $params->get('scandir');
        $allowedExtensions = array('zip');
        $currentfolder = 'root';

        if($scandir!=null) {
            $iterator =  new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($scandir), RecursiveIteratorIterator::SELF_FIRST
            );
            // Loop through recursively
            $extensions = array();
            foreach($iterator as $filename => $file) {
                if(is_dir($file)) {
                    $filetype = 'folder';
                } else {
                    $fparts = explode(".", $file);
                    $filetype = end($fparts);
                }
                if($filetype=='folder') {
                    $folder = basename($iterator->getSubPathName());

                    // ignore system files
                    if(in_array($folder, array('.', '..'))) continue;

                    // exclude premium/free directories
                    if(in_array($folder, array('premium', 'free', '2015'))) {
                        $currentfolder = $iterator->getSubPathName();
                        continue;
                    }

                    $item = new stdClass();
                    $item->filetype = $filetype;
                    $item->filename = $folder;
                    $item->filepath = $file->getPath().DS.$file->getFileName();
                    $item->alias = str_replace(' ', '', strtolower($item->filename));

                } elseif(in_array(strtolower($filetype), $allowedExtensions)) {
                    $item = new stdClass();
                    $item->filetype = $filetype;
                    $item->filename = basename($file->getFileName(), '.'.$item->filetype);
                    $item->filepath = $file->getPath().DS.$file->getFileName();

                    // ignore system files
                    if(in_array($item->filename, array('.', '..'))) continue;

                    // ignore packages
                    if(strstr($item->filename, 'UNZIPFIRST')!==false) continue;

                    // ignore old style extensions
                    if(in_array($currentfolder, array('premium', 'free'))) continue;

                    // new style extensions
                    $extension = basename(dirname($iterator->getSubPathName()));
                    if(empty($extension)) continue;

                    if(strstr($item->filename, 'PRO')!==false) {
                        $item->jversion = 'pro';
                        $item->premium = 1;
                    } else {
                        $item->jversion = 'free';
                        $item->premium = 0;
                    }
                    $nameparts = explode('-', $item->filename);
                    if(!$nameparts || count($nameparts)<=1) continue;

                    foreach($nameparts as $namepart) {
                        if(strpos($namepart, 'v')===0) {
                            $item->subversion = substr($namepart, 1);
                        }
                    }
                    $item->type = '';
                    $item->title = str_replace('Ji ', '', $extension);
                    $item->alias = strtolower(str_replace(' ', '', $nameparts[0]));

                    $item->downloadurl = 'index.php?option=com_jiextensionserver&task=extension.download&id='.$item->alias.'&v='.$item->subversion.'-'.$item->jversion;
                    $item->updateurl = 'index.php?option=com_jiextensionserver&task=extension.update&id='.$item->alias.'&v='.$item->subversion.'-'.$item->jversion;

                    $changelogfile = $file->getPath().DS.str_replace(array('-FREE', '-PRO'), '', $item->filename).'-changelog.txt';
                    if(file_exists($changelogfile)) {
                        $changelog = file($changelogfile);
                        $changelog = implode('<br>', $changelog);
                        $item->changelog = $changelog;
                    } else {
                        $item->changelog = '';
                    }

                    if(!isset($extensions[$item->alias])) $extensions[$item->alias] = array();
                    $extensions[$item->alias]['files'][] = $item;
                    $extensions[$item->alias]['ordering1'][] = $item->alias;
                    $extensions[$item->alias]['ordering2'][] = str_replace('.', '', $item->jversion.$item->subversion);
                }
            }
            //echo '<pre>';
            //print_r($extensions); die;

            // Save new extensions
            // Prepare models
            $extensionModel = JModelLegacy::getInstance('Extension', 'JiExtensionServerModel', array('ignore_request'=>true));
            $branchModel = JModelLegacy::getInstance('Branch', 'JiExtensionServerModel', array('ignore_request'=>true));
            $subversionModel = JModelLegacy::getInstance('Subversion', 'JiExtensionServerModel', array('ignore_request'=>true));
            $extensionTable = $extensionModel->getTable();
            $branchTable = $branchModel->getTable();
            $subversionTable = $subversionModel->getTable();
            $successes = array();
            $failures = array();
            $db = JFactory::getDBO();
            foreach($extensions as $alias=>$product) {
                if(isset($product['files'])) {
                    $tempArray = $product['files'];
                    array_multisort($product['ordering1'], SORT_ASC, $product['ordering2'], SORT_ASC, $tempArray);
                    foreach($tempArray as $extension) {
                        // Reset models
                        $extensionTable->reset();
                        $extensionModel->setState('extension.id', 0);
                        $branchTable->reset();
                        $branchModel->setState('branch.id', 0);
                        $subversionTable->reset();
                        $subversionModel->setState('subversion.id', 0);

                        // Check if extension exists
                        $query = 'SELECT `id` FROM #__jiextensions WHERE `alias`='.$db->quote($extension->alias);
                        $db->setQuery($query);
                        $response = $db->loadResult();
                        if($response==null) {
                            $data = array(
                                'title'=>$extension->title,
                                'alias'=>$extension->alias,
                                'publisher'=>1,
                                'state'=>1,
                                'publish_up'=>date('Y:m:d H:i:s'),
                                'access'=>1
                            );
                            $extensionModel->save($data);
                            $eid = $extensionModel->getState('extension.id');
                        } else {
                            $eid = $response;
                        }
                        // Check if branch exists
                        $query = 'SELECT `id` FROM #__jiextensions_branches WHERE `eid`='.$db->quote($eid).' AND `alias`='.$db->quote($extension->jversion);
                        $db->setQuery($query);
                        $response = $db->loadResult();
                        if($response==null) {
                            $data = array(
                                'eid'=>$eid,
                                'title'=>$extension->jversion,
                                'alias'=>$extension->jversion,
                                'state'=>1,
                                'publish_up'=>date('Y:m:d H:i:s')
                            );
                            $branchModel->save($data);
                            $bid = $branchModel->getState('branch.id');
                        } else {
                            $bid = $response;
                        }

                        // Check if subversion exists
                        /*$query = 'SELECT `id` FROM #__jiextensions_subversions WHERE `eid`='.$db->quote($eid).' AND `bid`='.$db->quote($bid).' AND `subversion`='.$db->quote($extension->subversion);
                        $db->setQuery($query);
                        $response = $db->loadResult();
                        if($response==null) {*/
                            // Save subversion
                            $data = array(
                                'eid'=>$eid,
                                'bid'=>$bid,
                                'changelog'=>$extension->changelog,
                                'premium'=>$extension->premium,
                                'filepath'=>$extension->filepath,
                                'downloadurl'=>$extension->downloadurl,
                                'updateurl'=>$extension->updateurl,
                                'state'=>1,
                                'created'=>date('Y:m:d H:i:s'),
                                'jversion'=>$extension->jversion,
                                'subversion'=>$extension->subversion,
                                'access'=>1
                            );
                            if($subversionModel->save($data)) {
                                $successes[] = $extension->title.' '.strtoupper($extension->jversion).' #'.$extension->subversion;
                                // Update branch
                                $query = 'UPDATE #__jiextensions_branches SET `latest`='.$db->quote($subversionModel->getState('subversion.id')).' WHERE `id`='.$db->quote($bid);
                                $db->setQuery($query);
                                $db->query();
                            } else {
                                $failures[] = $extension->title.' '.strtoupper($extension->jversion).' #'.$extension->subversion;
                            }
                        //}
                    }
                }
            }
            if(count($successes)>0 || count($failures)>0) {
                $msg = '';
                if(count($successes)>0) {
                    $msg.= JText::_('COM_JIEXTENSIONSERVER_SCAN_SUCCESSES').implode(', ', $successes).' ';
                    $return->valid = true;
                }
                if(count($failures)>0) {
                    $msg.= JText::_('COM_JIEXTENSIONSERVER_SCAN_FAILURES').implode(', ', $failures);
                    $return->valid = false;
                }
            }
        }

        if(!isset($msg)) {
            $msg = JText::_('COM_JIEXTENSIONSERVER_SCAN_FAILED');
            $return->valid = false;
        }
        $return->msg = $msg;
        return $return;
    }
}