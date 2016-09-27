<?php
/**
 * @version     $Id: jitime.php 025 2013-06-25 11:40:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JiTimeHelper
{
    public function xAgo($t)
    {
        $yeart = 365.25*86400;
        $years = (int) ($t/$yeart);
        $r = $t - ($years*$yeart);
        $months = (int) ($r/($yeart/12));
        $r = $r - ($months*($yeart/12));
        $weeks = (int) ($r/($yeart/52));
        $r = $r - ($weeks*($yeart/52));
        $days = (int) ($r/86400);
        $r = $r - ($days*86400);
        $hrs = (int) ($r/3600);
        $r = $r - ($hrs*3600);
        $mins = (int) ($r/60);
        $secs = (int) ($r - ($mins*60));

        $parts = array();
        if($years>0) $parts[] = $years.' year'.$this->pl($years);
        if($months>0) $parts[] = $months.' month'.$this->pl($months);
        if($weeks>0 && count($parts)<2) $parts[] = $weeks.' week'.$this->pl($weeks);
        if($days>0 && count($parts)<2) $parts[] = $days.' day'.$this->pl($days);
        if($hrs>0 && count($parts)<2) $parts[] = $hrs.' hour'.$this->pl($hrs);
        if($mins>0 && count($parts)<2) $parts[] = $mins.' minute'.$this->pl($mins);
        if($secs>0 && count($parts)<2) $parts[] = $secs.' second'.$this->pl($secs);

        $result = implode($parts, ' ');
        return $result;
    }
    function pl($v) {
        return ($v<2)? '':'s';
    }
}