<?php
/**
 * @version     $Id: griditem.php 020 2013-06-24 10:30:00Z Anton Wintergerst $
 * @package     JiGrid Template Framework for Joomla 2.5-3.0
 * @copyright   Copyright (C) 2013 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.form.helper');

class JFormFieldGridTypeChanger extends JFormField
{
    protected $type = 'GridTypeChanger';

    public function getInput()
    {
        JHtml::_('jquery.framework');
        ob_start(); ?>
        <script type="text/javascript">
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    function updateTypeOptions(type) {
                        (type!='grid')? jQuery('.hide-grid').removeClass('hide') : jQuery('.hide-grid').addClass('hide');
                        (type!='row')? jQuery('.hide-row').removeClass('hide') : jQuery('.hide-row').addClass('hide');
                        (type!='cell')? jQuery('.hide-cell').removeClass('hide') : jQuery('.hide-cell').addClass('hide');
                        if(type=='grid') {
                            jQuery('.grid-only').removeClass('hide');
                            jQuery('.row-only').addClass('hide');
                            jQuery('.cell-only').addClass('hide');
                        } else if(type=='row') {
                            jQuery('.grid-only').addClass('hide');
                            jQuery('.row-only').removeClass('hide');
                            jQuery('.cell-only').addClass('hide');
                        } else if(type=='cell') {
                            jQuery('.grid-only').addClass('hide');
                            jQuery('.row-only').addClass('hide');
                            jQuery('.cell-only').removeClass('hide');
                        }
                    }
                    jQuery('#jform_type').on('change', function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        updateTypeOptions(jQuery(sender).val());
                    });
                    updateTypeOptions(jQuery('#jform_type').val());
                });
            }
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }
}