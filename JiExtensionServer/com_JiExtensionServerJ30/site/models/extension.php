<?php
/**
 * @version     $Id: extension.php 038 2014-12-15 13:54:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.modelitem');

class JiExtensionServerModelExtension extends JModelItem
{
    /**
     * @var		string	The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_JIEXTENSIONSERVER';

    function download() {
        $params =JComponentHelper::getParams('com_jiextensionserver');

        $jinput = JFactory::getApplication()->input;
        $alias = $jinput->get('id');
        $branch = $jinput->get('jv', 'free', 'raw');
        $subversion = $jinput->get('v');
        $subparts = explode('-', $subversion);

        if($subparts && count($subparts)>1) {
            $subversion = $subparts[0];
            $branch = strtolower($subparts[1]);
        }
        if($branch==null) $branch = 'free';
        if($alias==null) return '0';

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('s.id, s.filepath, s.premium, e.alias, e.attribs, b.alias AS branch, b.attribs AS battribs');
        $query->from('#__jiextensions AS e');
        $query->join('LEFT', '#__jiextensions_branches AS b ON (b.eid = e.id)');
        $query->join('LEFT', '#__jiextensions_subversions AS s ON (s.bid = b.id)');
        $query->where('e.alias = '.$db->quote($alias));
        $query->where('b.alias = '.$db->quote($branch));
        $query->where('s.subversion = '.$db->quote($subversion));
        $db->setQuery($query);
        $extension = $db->loadObject();

        if($extension!=null) {
            $extension->params = new JRegistry($extension->attribs);
            $extension->params->merge(new JRegistry($extension->battribs));

            // get current user
            $user = JFactory::getUser();
            $uid = 0;
            if($user->id==0) {
                // Check token is valid
                $token = $jinput->get('dlkey', null, 'raw');
                $token = htmlspecialchars_decode($token);
                $model = JModelLegacy::getInstance('Token', 'JiExtensionServerModel');
                $isValidToken = $model->checkToken($token);
                if($isValidToken) {
                    $uid = $model->uid;
                    $user = JFactory::getUser($uid);
                }
            } else {
                $uid = $user->id;
            }

            if($extension->branch=='pro') {
                if($uid==0) die;
                // check if user has a bundle subscription
                $allowedGroups = $params->get('premium_usergroups', array(0));
                $bundleLicence = false;
                if(in_array(0, $allowedGroups)!=false) {
                    $bundleLicence = true;
                } elseif(isset($user->groups)) {
                    foreach($user->groups as $group) {
                        if(in_array($group, $allowedGroups)) {
                            $bundleLicence = true;
                            break;
                        }
                    }
                }
                if(!$bundleLicence) {
                    // check if user has a single extension subscription
                    $allowedGroups = $extension->params->get('access_usergroups', null);
                    $singleLicence = false;
                    if(is_array($allowedGroups) && isset($user->groups)) {
                        foreach($user->groups as $group) {
                            if(in_array($group, $allowedGroups)) {
                                $singleLicence = true;
                                break;
                            }
                        }
                    }
                    if(!$singleLicence) return '0';
                }
            }

            // record user activity
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__jiextensions_activity'));
            $query->columns($db->quoteName(array('uid', 'sid', 'site', 'activity', 'date')));
            $query->values(implode(',', array(
                $uid,
                $db->quote($extension->id),
                $db->quote($_SERVER['HTTP_REFERER']),
                $db->quote('download'),
                $db->quote(date('Y-m-d H:i:s'))
            )));
            $db->setQuery($query);
            try {
                $db->query();
            } catch (Exception $e) {
            }

            // increment hits
            $query = 'UPDATE #__jiextensions_subversions SET `downloadhits`=(`downloadhits`+1) WHERE `id`='.$db->quote($extension->id);
            $db->setQuery($query);
            try {
                $db->query();
            } catch (Exception $e) {
            }

            $this->downloadFile($extension->filepath);
        }
        return '0';
    }
    protected function downloadFile($filepath) {
        $params =JComponentHelper::getParams('com_jiextensionserver');

        // Clean data
        $filepath = htmlspecialchars_decode($filepath);
        $filepath = mb_convert_encoding($filepath, "UTF-8", "auto");
        $deniedurl = $params->get('denied_url', JURI::root());

        // Check if file exists
        if(!file_exists($filepath)) $this->downloadFileFailed($deniedurl, $filepath);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');

        // Suggest better filename for browser to use when saving file:
        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
        header('Content-Transfer-Encoding: binary');
        // Caching headers:
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        // Send a formatted size of file
        header('Content-Length: '.sprintf('%d', filesize($filepath)));

        $this->readfile_chunked($filepath);
        // Exit before anything is added to the binary
        exit;
    }
    protected function downloadFileFailed($deniedurl, $filepath=null) {
        echo '0';
        exit;
        /*header("Expires: Mon, 26 Jul 12012 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Location: ".$deniedurl);
        // Close the script
        exit;*/
    }
    /*
	 * Standard read file (only good for small files) [Depreciated]
	 */
    protected function readfile_plain($filepath) {
        $handle = fopen($filepath, 'rb');
        fpassthru($handle);
        fclose($handle);
    }
    /*
	 * Splits up file reading for large files
	 */
    protected function readfile_chunked($filepath, $retbytes=true) {
        $chunksize = 1*(1024*1024); // how many bytes per chunk
        $buffer = '';
        $cnt =0;
        $handle = fopen($filepath, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            echo $buffer;
            ob_flush();
            flush();
            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;
    }
}