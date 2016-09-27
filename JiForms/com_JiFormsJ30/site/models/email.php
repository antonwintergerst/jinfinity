<?php
/**
 * @version     $Id: email.php 020 2013-09-04 18:10:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.modelitem');

class JiFormsModelEmail extends JModelItem
{
    protected $_context = 'com_jiforms.email';

    private function getDRT($text) {
        $regex = "#{([^:}]*):?([^}]*)}#s";
        $text = preg_replace_callback($regex, array(&$this,'replaceAttribute'), $text);
        return $text;
    }
    private function replaceAttribute($matches) {
        $result = '';
        if(isset($matches[1])) {
            $attr = $matches[1];
            $val = $this->form->data->get($attr);
            if($val!=null) $result = $val;
        }
        return $result;
    }
    public function sendEmail($alias) {
        $email = $this->getEmail($alias);

        // Prepare Email
        $messagebody = $this->getDRT(htmlspecialchars_decode($email->message));

        // To
        $to = $this->getDRT($email->headers->get('to', null, 'raw'));
        $from = $this->getDRT($email->headers->get('from', null, 'raw'));
        $haserror = false;
        if(!empty($to) && !empty($from)) {
            // Subject
            $subject = $this->getDRT($email->subject);

            // Message
            $message = '<html><head><title>'.$subject.'</title></head><body>'.$messagebody.'</body></html>';

            // Headers
            $headers = 'MIME-Version: 1.0'."\r\n";
            $headers.= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
            $headers.= 'To: '.$to."\r\n";
            $headers.= 'From: '.$from."\r\n";
            $replyto = $this->getDRT($email->headers->get('replyto', null, 'raw'));
            if(!empty($replyto)) {
                $headers.= 'Reply-To: '.$replyto."\r\n";
            }
            $cc = $this->getDRT($email->headers->get('cc', null, 'raw'));
            if(!empty($cc)) {
                $headers.= 'Cc: '.$cc."\r\n";
            }
            $bcc = $this->getDRT($email->headers->get('bcc', null, 'raw'));
            if(!empty($bcc)) {
                $headers.= 'Bcc: '.$bcc."\r\n";
            }
            // Send Email
            if(!mail($to, $subject, $message, $headers)) $haserror = true;
        }
        //if($haserror) echo 'Error sending mail';
    }

    protected function populateState()
    {
        $app = JFactory::getApplication('site');

        // Load state from the request.
        $pk = $app->input->getInt('alias');
        $this->setState('email.alias', $pk);

        $offset = $app->input->getUInt('limitstart');
        $this->setState('list.offset', $offset);
    }
    public function &getEmail($pk=null)
    {
        $pk = (!empty($pk)) ? $pk : $this->getState('email.alias');

        if ($this->_item === null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$pk])) {

            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true);

                $query->select('e.*');
                $query->from('#__jiforms_emails AS e');

                $query->where('e.alias = '.$db->Quote($pk));

                // Filter by start and end dates.
                $nullDate = $db->Quote($db->getNullDate());
                $date = JFactory::getDate();

                $nowDate = $db->Quote($date->toSql());

                $query->where('(e.publish_up = ' . $nullDate . ' OR e.publish_up <= ' . $nowDate . ')');
                $query->where('(e.publish_down = ' . $nullDate . ' OR e.publish_down >= ' . $nowDate . ')');

                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived = $this->getState('filter.archived');

                if (is_numeric($published)) {
                    $query->where('(e.state = ' . (int) $published . ' OR e.state =' . (int) $archived . ')');
                }

                $db->setQuery($query);

                $data = $db->loadObject();

                if (empty($data)) {
                    return JError::raiseError(404, JText::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND'));
                }

                // Check for published state if filter set.
                if (((is_numeric($published)) || (is_numeric($archived))) && (($data->state != $published) && ($data->state != $archived))) {
                    return JError::raiseError(404, JText::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND'));
                }

                // Convert parameter fields to objects.
                $registry = new JRegistry;
                $registry->loadString($data->headers);
                $data->headers = $registry;

                $this->_item[$pk] = $data;
            }
            catch (Exception $e)
            {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    JError::raiseError(404, $e->getMessage());
                }
                else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }

        return $this->_item[$pk];
    }
}