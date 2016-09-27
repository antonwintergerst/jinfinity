<?php
/**
 * @version     $Id: fieldsform.php 021 2014-11-20 15:48:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormFieldFieldsForm extends JFormField
{
    protected $type = 'fieldsform';

    protected function getLabel()
    {
        return null;
    }
    protected function getInput()
    {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $jiparams = JComponentHelper::getParams('com_jicustomfields');
        // Load Fields Model
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'models'.DS.'fields.php');
        if(version_compare(JVERSION, '3', 'ge')) {
            $model = JModelLegacy::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
        } else {
            $model = JModel::getInstance('Fields', 'JiCustomFieldsModel', array('ignore_request'=>true));
        }
        $model->setState('filter.published', 1);

        $item = new stdClass();

        if(is_array($this->value)) {
            $value = $this->value;
            $item->id = (isset($value['id']))? (int) $value['id'] : 0;
            $item->catid = (isset($value['catid']))? (int) $value['catid'] : 0;
        } else {
            $item->id = (int) $jinput->get('id');
            $item->catid = (int) $jinput->get('catid');
        }
        if($item->id!=0 && $item->catid==0) {
            // find catid
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('catid');
            $query->from('#__content');
            $query->where('id='.(int)$item->id);
            $db->setQuery($query);
            $item->catid = (int)$db->loadResult();
        }
        /*if($jiparams->get('debug',0)==1) {
            echo '<pre>';
            print_r($item);
            echo '</pre>';
        }*/

        // Get Field Values for article
        $values = $model->getValues($item, 'item');
        // Load fieldlist
        $jifields = $model->getJiFields($item, $values);

        ob_start(); ?>
        <script type="text/javascript">
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    // joomla only has one category option
                    if(jQuery('#jform_catid option').length==1) {
                        jQuery('.jicustomfields').trigger('willreload');

                        jQuery('.jicustomfields').html('Reload the page to see custom fields assigned to this category.');
                        // attempt to reload with ajax
                        jQuery.ajax({url:'index.php?option=com_jicustomfields&task=fields.renderinputs&format=ajax',
                            type:'post',
                            data:{
                                'id':<?php echo $item->id; ?>,
                                'catid':jQuery('#jform_catid option:first').val()
                            }
                        }).done(function(response) {
                            if(response!=null) {
                                jQuery('.jicustomfields').replaceWith(response);

                                // reinit custom fields
                                jQuery('.jicustomfields').trigger('reload');
                            }
                        });
                    }
                    // joomla category changes
                    jQuery('#jform_catid').change(function() {
                        jQuery('.jicustomfields').trigger('willreload');

                        jQuery('.jicustomfields').html('Reload the page to see custom fields assigned to this category.');
                        // attempt to reload with ajax
                        jQuery.ajax({url:'index.php?option=com_jicustomfields&task=fields.renderinputs&format=ajax',
                            type:'post',
                            data:{
                                'id':<?php echo $item->id; ?>,
                                'catid':jQuery('#jform_catid').val()
                            }
                        }).done(function(response) {
                            if(response!=null) {
                                jQuery('.jicustomfields').replaceWith(response);

                                // reinit custom fields
                                jQuery('.jicustomfields').trigger('reload');
                            }
                        });
                    });
                });
            }
        </script>
        <?php
        $model->renderInputLayout($jifields, $item);
        require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jicustomfields'.DS.'views'.DS.'fields'.DS.'tmpl'.DS.'form.php');
        $html = ob_get_clean();

        return $html;
    }
}