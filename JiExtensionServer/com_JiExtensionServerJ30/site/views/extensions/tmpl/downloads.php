<?php
/**
 * @version     $Id: default.php 029 2014-12-10 09:26:00Z Anton Wintergerst $
 * @package     JiExtensionServer for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::addIncludePath(JPATH_SITE.'/media/jinfinity/html');
// Load Scripts
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('jquery.framework');
    JHTML::stylesheet('components/com_jiextensionserver/assets/css/jiextensions.css');
} else {
    JHtml::_('stylesheet', 'icomoon.css', 'media/jinfinity/css/');
    JHTML::_('stylesheet', 'jiextensions.css', 'components/com_jiextensionserver/assets/css/');
}

require_once(JPATH_SITE.'/components/com_jiextensionserver/helpers/jitime.php');
$JiTimeHelper = new JiTimeHelper();

// Sort items into groups
/*$groups = array();
if(is_array($this->items)) {
    foreach($this->items as $item) {
        if(!isset($groups[$item->alias])) {
            $group = $item;
            $group->children = array();
            $group->access = $item->access;
            $groups[$item->alias] = $group;
        }
        $groups[$item->alias]->children[] = $item;
    }
}*/
?>
<script type="text/javascript">
    if(typeof jQuery!='undefined') {
        jQuery(document).ready(function() {
            jQuery('.changelogbtn').on('click', function(e) {
                var target = e.target != null ? e.target : e.srcElement;
                var changelog = jQuery(target).closest('.subversion').find('.changelog');
                if(jQuery(changelog).hasClass('hide')) {
                    jQuery(changelog).removeClass('hide').show();
                    jQuery(target).text('Changelog [-]');
                } else {
                    jQuery(changelog).addClass('hide').hide();
                    jQuery(target).text('Changelog [+]');
                }
                e.preventDefault();
                e.stopPropagation();
            });
        });
    }
</script>
<div class="list jiextensions">
    <div class="jinotice">
        <?php if($this->user->id==43): ?>
            <h3>Hi Norm! Peter says hi.</h3>
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
        <?php elseif($this->user->id==225): ?>
            <h3>Luke Armistead A.K.A. Quality control superstar!</h3>
        <?php elseif($this->user->id==54): ?>
            <h3>Hi John, remember to uninstall safari (I hear Google Chrome is quite good)</h3>
        <?php endif; ?>
        <?php if($this->subscriptions!=null && count($this->subscriptions)>0): ?>
            <div class="alert alert-success">
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
        <?php else: ?>
            <div class="alert alert-error">
                <h3>Download Token Missing or Subscription Expired</h3>
                <p>Jinfinity subscribers receive access to the latest premium extensions for Joomla.<br>
                    Already subscribed? <a href="<?php echo JURI::root().'login'; ?>">Login here</a><br><br>
                    New to Jinfinity? <a href="<?php echo JURI::root().'subscribe'; ?>">Subscribe Today!</a></p>
                </p>
            </div>
        <?php endif; ?>
        <?php /*<div class="alert alert-danger">
            <h3>Breaking Changes</h3>
            <ul>
                <li>
                    <p>Breaking Changes for JiMigrator (200+)<span class="label label-important" style="margin-left:4px;"><i class="icon-flag"></i>New</span>
                        <br>There are a few critical issues that the team is working on for JiMigrator.
                        <br>Thank you for your patience as we work to resolve these issues.</p>
                </li>
                <li>
                    <p>Breaking Changes for JiBlogTools (175+)
                    <br>When updating, replace any old DRTs to the new format (e.g. %aid becomes {id} and %c becomes {count}).<br>See plugin description for the complete list of DRTs</p>
                </li>
                <li>
                    <p>Breaking Changes for JiFramework (131+), JiBlogTools (170+), JiFileGallery (162+), JiImageSlider (152+), JiImageGallery (138+), & JiMigrator (190+)<br>Please update these extensions to the latest version and cross check any template overrides should your layout be breaking.</p>
                </li>
                <li>
                    <p>Breaking Changes for JiImageGallery (140+)<br>When updating, either reset the Source Directory or re-enable the Advanced Root directory setting.</p>
                </li>
            </ul>
        </div>*/?>
    </div>
    <?php if($this->state->get('filter.jversion') && $this->state->get('filter.jversion')!='*' && !$this->state->get('filter.alias')): ?>
        <div class="alert alert-info">
            <p>Currently displaying downloads for Joomla <?php echo $this->state->get('filter.jversion'); ?> <a class="btn btn-small btn-info" href="http://www.jinfinity.com/downloads?alias=&jversion=" style="margin-bottom:4px;">View all downloads</a></p>
        </div>
    <?php endif; ?>
    <div class="items extensions">
        <?php foreach($this->items as $alias=>$item): ?>
            <?php if($this->state->get('filter.alias')): ?>
                <div class="alert alert-info">
                    <p>Currently displaying downloads for <?php echo $item->title; ?>. <a class="btn btn-small btn-info" href="http://www.jinfinity.com/downloads?alias=&jversion=" style="margin-bottom:4px;">View all downloads</a></p>
                </div>
            <?php endif; ?>
                <div class="item jiextension ext-<?php echo $alias; ?>">
                    <?php if(file_exists(JPATH_SITE.DS.'images'.DS.str_replace('pro', '', $alias).'-icon.png')): ?>
                        <div class="item-image">
                            <img src="<?php echo JURI::root().'images/'.str_replace('pro', '', $alias).'-icon.png'; ?>" />
                        </div>
                    <?php endif; ?>
                    <div class="item-text">
                    <h2><?php echo $item->title; ?><?php if($item->access==5): echo ' <span class="label label-important">[BETA]</span>'; elseif($item->access!=1): echo ' <span class="label label-important">[ALPHA]</span>'; endif; ?></h2>
                    <?php if($item->hasfree): ?>
                            <a class="btn btn-success" href="<?php echo $item->downloadurl; ?>">Download FREE</a>
                    <?php endif; ?>
                    <?php if($item->haspro): ?>
                        <?php if($item->pro): ?>
                            <a class="btn btn-primary" href="<?php echo $item->downloadurl; ?>">Download PRO</a>
                        <?php else: ?>
                                <a class="btn btn-primary" href="http://www.jinfinity.com/subscribe?ext=<?php echo $alias; ?>">Get PRO+</a>
                        <?php endif; ?>
                    <?php endif; ?>
                        </div>
                    <?php if($item->changelog!=null): ?>
                        <div class="subversion">
                            <a href="#" class="changelogbtn">Changelog [+]</a>
                            <div class="changelog hide">
                                <?php echo $item->changelog; ?>
                            </div>
                        </div>
                    <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>