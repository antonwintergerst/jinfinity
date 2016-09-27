<?php
/**
 * @version     $Id: token.php 033 2014-03-04 14:26:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modeladmin');

class JiExtensionServerModelToken extends JModelAdmin
{
    private $secret = 'not telling';
    private $method = 'AES-256-CBC';
    public $uid = 0;

    public function getItem($pk = null)
    {
        $item = new stdClass();
        $jinput = JFactory::getApplication()->input;
        $item->dlkey = $jinput->get('jitoken.dlkey', '');
        $item->id = $jinput->get('jitoken.uid', 0);
        $item->valid = $jinput->get('jitoken.valid', 0);

        return $item;
    }
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jiextensionserver.token', 'token', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_jiextensionserver.edit.token.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getToken($uid=null, $password=null) {
        if($uid==null || $password==null) {
            $user = JFactory::getUser($uid);
            $uid = $user->id;
            $password = $user->password;
        }
        $this->uid = $uid;
        $iv = $this->createIV();

        $token_part1 = openssl_encrypt($uid, $this->method, $this->secret, false, $iv);
        $token_part2 = openssl_encrypt($password, $this->method, $this->secret, false, $iv);
        $token = $token_part1.':'.$token_part2.':'.bin2hex($iv);

        return $token;
    }
    public function checkToken($token) {
        $token = str_replace(' ', '+', $token);

        $tokenparts = explode(':', $token);
        if(count($tokenparts)==3) {
            if(strlen($tokenparts[0])!=24) return false;
            if(strlen($tokenparts[1])!=64) return false;
            if(strlen($tokenparts[2])!=32) return false;

            $iv = pack("H*", $tokenparts[2]);

            $uid = (int) openssl_decrypt($tokenparts[0], $this->method, $this->secret, false, $iv);
            $password = openssl_decrypt($tokenparts[1], $this->method, $this->secret, false, $iv);
            $this->uid = $uid;
            if($uid==0) return false;

            $db = JFactory::getDBO();
            $query = 'SELECT `id` FROM #__users WHERE `id`='.$db->quote($uid).' AND `password`='.$db->quote($password);
            $db->setQuery($query);
            $result = $db->loadResult();
            if($result!=null && (int) $result>0) return true;
        }
        return false;
    }
    private function createIV() {
        $iv_size = openssl_cipher_iv_length($this->method);
        $iv = openssl_random_pseudo_bytes($iv_size);
        return $iv;
    }
}