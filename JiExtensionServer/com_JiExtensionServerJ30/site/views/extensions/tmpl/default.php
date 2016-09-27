<?php
/**
 * @version     $Id: default.php 027 2014-02-27 14:25:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.'/components/com_jiextensionserver/helpers/jitime.php');
$JiTimeHelper = new JiTimeHelper();

header("Expires: Wed, 26 Jun 1988 09:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Content-type: text/xml; charset=utf-8");
echo '<?xml version="1.0" encoding="utf-8"?>';
?>

<extensions xmlns="http://www.jinfinity.com">
    <notice><?php echo '<![CDATA[' ?>
        <?php if($this->user->id==43 || $this->user->id==53): ?>
            <h3>Norm, Peter says hi!</h3>
            <pre>
                __________                  __,___/  ",",
         ___.--"          "\'.         ____/  l   \    ",'-,
  ------f"               // \\\        \  (l\ \    \     \ \",
        |                    |||       /   u       |      \ \ \
        |                    |||     _ )          /       | |  \
    ----L_-XXX-.             .|'    / U          &lt;        | |  |
                "\   -&lt;_____///     \           6 )       | |  |
                  \___)     -"       '.       -.&lt;"       / /   |
                                      |'.___  |       _._."   /
                                      |     ./     _."."   _."
                                     /      |"----"     _."
                                  jjs       \
            </pre>
            <?php require_once(JPATH_SITE.'/components/com_jiextensionserver/helpers/pong.php'); ?>
        <?php elseif($this->user->id==225): ?>
            <h3>Luke Armistead A.K.A. Quality control superstar!</h3>
            <?php require_once(JPATH_SITE.'/components/com_jiextensionserver/helpers/pong.php'); ?>
        <?php elseif($this->user->id==54): ?>
            <h3>Hi John, remember to uninstall safari (I hear Google Chrome is quite good)</h3>
        <?php elseif($this->user->id==50): ?>
            <h3>-_- Coding Wizard -_-</h3>
            <?php require_once(JPATH_SITE.'/components/com_jiextensionserver/helpers/pong.php'); ?>
        <?php endif; ?>
        <?php if($this->subscriptions!=null && count($this->subscriptions)>0): ?>
            <blockquote>
                <div class="alert-success" style="padding: 10px; width: 60%; border: 1px solid #CCC;">
                    <h3>Active Subscriptions</h3>
                    <ul>
                        <?php foreach ($this->subscriptions as $subscription): ?>
                            <li>
                                <?php
                                $start = time();
                                $end = date('U', strtotime($subscription->end_date));
                                $timeleft = $end-$start;
                                if($timeleft>31536000):
                                    echo $subscription->title.', Lifetime';
                                else:
                                    if($expiry<0) {
                                        $expiry = 'Expired '.$JiTimeHelper->xAgo(abs($end - $start).' ago');
                                    } else {
                                        $expiry = 'Expires in '.$JiTimeHelper->xAgo(abs($end - $start));
                                    }
                                    echo $subscription->title.', '.$expiry; ?>
                                    <a class="btn btn-small btn-info" href="http://www.jinfinity.com/my-account" style="margin-bottom:4px;">
                                        <i class="icon-basket"></i>Renew
                                    </a>
                                <?php endif ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </blockquote>
        <?php else: ?>
            <blockquote>
                <div class="alert-error" style="padding: 10px; width: 60%; border: 1px solid #CCC;">
                    <h3>Download Token Missing or Subscription Expired</h3>
                    <p>Jinfinity subscribers receive access to the latest premium extensions for Joomla.<br>
                        Already subscribed? <a target="_blank" href="http://www.jinfinity.com/my-account">Get your JiDownloadToken here</a><br><br>
                        New to Jinfinity? <a target="_blank" href="http://www.jinfinity.com/subscribe">Subscribe Today!</a></p>
                    </p>
                </div>
            </blockquote>
        <?php endif; ?>
        <?php echo ']]>'; ?></notice>
    <?php if(is_array($this->items)): ?>
        <?php foreach($this->items as $item): ?>
            <extension>
                <title><![CDATA[<?php echo $item->title; ?><?php if($group->access==5): echo ' <span class="label label-important">[BETA]</span>'; elseif($group->access!=1): echo ' <span class="label label-important">[ALPHA]</span>'; endif; ?>]]></title>
                <alias><?php echo $item->alias; ?></alias>
                <changelog><?php echo ($item->changelog!=null)? '<![CDATA['.$item->changelog.']]>' : ''; ?></changelog>
                <publisher>Jinfinity</publisher>
                <hasfree><?php echo $item->hasfree; ?></hasfree>
                <haspro><?php echo $item->haspro; ?></haspro>
                <pro><?php echo $item->pro; ?></pro>
                <downloadurl><?php echo ($item->downloadurl!=null)? '<![CDATA['.JURI::root().$item->downloadurl.']]>' : ''; ?></downloadurl>
                <updateurl><?php echo ($item->updateurl!=null)? '<![CDATA['.JURI::root().$item->updateurl.']]>' : ''; ?></updateurl>
                <jversion><?php echo $item->jversion; ?></jversion>
                <version><?php echo $item->subversion; ?></version>
            </extension>
        <?php endforeach; ?>
    <?php endif; ?>
</extensions>