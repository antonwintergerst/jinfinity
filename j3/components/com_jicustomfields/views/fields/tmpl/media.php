<?php
/**
 * @version     $Id: media.php 087 2014-11-20 13:27:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldMedia extends JiCustomField {
    public function getModel($name) {
        if(version_compare(JVERSION, '3', 'ge')) {
            $model = JModelLegacy::getInstance($name, 'JiCustomFieldsModel');
        } else {
            $model = JModel::getInstance($name, 'JiCustomFieldsModel');
        }
        return $model;
    }
    public function renderInputScript() {
        $params = $this->get('params');
        $filetypes = explode(',', $params->get('types', 'bmp, gif, jpeg, jpg, folder'));
        $typedata = array();
        foreach($filetypes as $type) {
            $typedata[] = trim($type);
        }
        // Load language strings for javascript
        JText::script('JICUSTOMFIELDS_UPLOAD');
        JText::script('JICUSTOMFIELDS_CANCEL');
        JText::script('JICUSTOMFIELDS_IMAGENOIMAGETIP');

        $model = $this->getModel('mediamanager');
        $data = $model->open();
        ob_start(); ?>
        <script type="text/javascript">
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    jQuery('#<?php echo $this->get('inputid'); ?>').jilistfilter({
                        id:'<?php echo $this->get('id'); ?>',
                        name:'<?php echo $this->get('name'); ?>',
                        type:'media',
                        mediatype:'images',
                        fileinput:'#<?php echo $this->get('inputid'); ?>',
                        container:jQuery('#<?php echo $this->get('inputid'); ?>mediamanager'),
                        url:'<?php echo JURI::root().'index.php?option=com_jicustomfields&format=json&task=mediamanager&filtertype=file'; ?>',
                        uploadurl:'<?php echo JURI::root().'index.php?option=com_jicustomfields&format=ajax&task=upload.display'; ?>',
                        resizeurl:'<?php echo JURI::root().'index.php?option=com_jicustomfields&format=ajax&task=upload.resize'; ?>',
                        data: '<?php echo json_encode($data); ?>',
                        filetypes: '<?php echo json_encode($typedata); ?>'
                    });
                });
            }
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function renderInput() {
        ob_start(); ?>
        <div id="<?php echo $this->get('inputid'); ?>mediamanager" class="jimediamanager"></div>
        <?php $html = ob_get_clean();
        return $html;
    }
    function renderInputLabel() {
        $html = $this->getLabel();
        return $html;
    }
}