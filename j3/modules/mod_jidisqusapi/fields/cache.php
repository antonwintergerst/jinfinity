<?php
/*
 * @version     $Id: cache.php 100 2013-06-04 18:36:00Z Anton Wintergerst $
 * @package     Jinfinity Cache Field Type for Joomla! 1.5+
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
*/
defined('_JEXEC') or die;

class JFormFieldCache extends JFormField {
    public $type = 'Cache';

    protected function getLabel() {
        return '';
    }

    protected function getInput() {
        $this->params = $this->element->attributes();

        $title = $this->get('label');
        $description = $this->get('description');

        if($title) $title = JText::_($title);
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            JHtml::_('jquery.framework');
        }
        ob_start(); ?>
        <script type="text/javascript">
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    var target = null;
                    function installCache(e) {
                        if(e!=null) {
                            target = e.target != null ? e.target : e.srcElement;
                            e.preventDefault();
                            e.stopPropagation();
                        }
                        processTask('install');
                    }
                    function clearCache(e) {
                        if(e!=null) {
                            target = e.target != null ? e.target : e.srcElement;
                            e.preventDefault();
                            e.stopPropagation();
                        }
                        processTask('clear');
                    }
                    function uninstallCache(e) {
                        if(e!=null) {
                            target = e.target != null ? e.target : e.srcElement;
                            e.preventDefault();
                            e.stopPropagation();
                        }
                        processTask('uninstall');
                    }
                    function processTask(task) {
                        jQuery.getJSON('<?php echo JURI::root().'modules/mod_jidisqusapi/admin/json.php'; ?>', {
                            'modtask':task
                        }, function(response) {
                            if(response!=null) {
                                if(response.valid!=null) {
                                    if(response.valid==true) {
                                        jQuery(target).html(response.msg);
                                        if(task=='install') {
                                            jQuery(target).off(installCacheHandler);
                                        } else if(task=='clear') {
                                            jQuery(target).off(clearCacheHandler);
                                        } else if(task=='uninstall') {
                                            jQuery(target).off(uninstallCacheHandler);
                                        }
                                    }
                                }
                            }
                        });
                    }
                    var installCacheHandler = function(e) {installCache(e);};
                    var clearCacheHandler = function(e) {clearCache(e);};
                    var uninstallCacheHandler = function(e) {uninstallCache(e);};

                    jQuery('.jiaction_install').on('click', installCacheHandler);
                    jQuery('.jiaction_clear').on('click', clearCacheHandler);
                    jQuery('.jiaction_uninstall').on('click', uninstallCacheHandler);
                });
            }
        </script>
        <a class="jiaction_install" href="#" title="Install <?php echo $title; ?>">Install <?php echo $title; ?></a>
        <a class="jiaction_clear" href="#" title="Clear <?php echo $title; ?>">Clear <?php echo $title; ?></a>
        <a class="jiaction_uninstall" href="#" title="Uninstall <?php echo $title; ?>">Uninstall <?php echo $title; ?></a>
        <?php return ob_get_clean();
    }
    private function get($var, $default = '')
    {
        return (isset($this->params[$var]) && (string) $this->params[$var] != '') ? (string) $this->params[$var] : $default;
    }
}