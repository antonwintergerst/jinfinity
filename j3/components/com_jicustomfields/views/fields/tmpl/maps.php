<?php
/**
 * @version     $Id: maps.php 082 2014-10-28 10:16:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldMaps extends JiCustomField {
    public function renderInputScript() {
        ob_start(); ?>
        <script type="text/javascript">
            (function(jQuery){
                var JiFieldMaps = function(container, data)
                {
                    var self = this;
                    // Class Functions
                    this.updateAddress = function(e) {
                        var sender = e.target != null ? e.target : e.srcElement;
                        if(sender!=null) {
                            self.updateLocation(jQuery(sender).val());
                        }
                    };
                    this.updateLocation = function(address) {
                        // Perform JSON request
                        jQuery.ajax({dataType:'json', url:'http://maps.googleapis.com/maps/api/geocode/json',
                            type:'get',
                            data:{
                                'address': address,
                                'sensor': 'true'
                            }
                        }).done(function(response) {
                                if(response.results!=null && response.status=='OK') {
                                    if(response.results[0]!=null) {
                                        var result = response.results[0];
                                        if(result.geometry!=null) {
                                            if(result.geometry.location!=null) {
                                                var location = result.geometry.location;
                                                if(location.lat!=null) jQuery('#fieldmapslat<?php echo $this->get('id'); ?>').val(location.lat);
                                                if(location.lng!=null) jQuery('#fieldmapslon<?php echo $this->get('id'); ?>').val(location.lng);
                                            }
                                        }
                                    }
                                }
                            });
                    };
                    // Setup Handlers
                    this.updateAddressHandler = function(e) {self.updateAddress(e);};
                    this.prepareInput = function() {
                        jQuery('#fieldmapsaddress<?php echo $this->get('id'); ?>').on('change', this.updateAddressHandler);
                    }
                }
                jQuery.fn.jifieldmaps = function(data) {
                    var element = jQuery(this);
                    if(element.data('jifieldmaps')) return element.data('jifieldmaps');
                    var jifieldmaps = new JiFieldMaps(this, data);
                    element.data('jifieldmaps', jifieldmaps);
                    return jifieldmaps;
                };
            })(jQuery);
        </script>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function renderInput() {
        $value = $this->get('value', 'jiobject');
        ob_start(); ?>
        <div class="jifieldgroup row-fluid <?php echo $this->type; ?>">
            <ul class="jitrow span12 nodrop">
                <li class="jitd span12 mapsaddress-lbl">
                    <label for="fieldmapsaddress<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_MAPSADDRESS'); ?></label>
                </li>
                <li class="jitd span12 mapsaddress">
                    <div class="text input">
                        <input class="inputbox" type="text" id="fieldmapsaddress<?php echo $this->get('id'); ?>" name="<?php echo $this->get('inputname'); ?>[value][address]" value="<?php echo $value->get('address'); ?>" />
                    </div>
                </li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd mapslat-lbl">
                    <label for="fieldmapslat<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_MAPSLAT'); ?></label>
                </li>
                <li class="jitd mapslat">
                    <div class="text input">
                        <input class="inputbox" type="text" id="fieldmapslat<?php echo $this->get('id'); ?>" name="<?php echo $this->get('inputname'); ?>[value][lat]" value="<?php echo $value->get('lat'); ?>" />
                    </div>
                </li>
            </ul>
            <ul class="jitrow span6 nodrop">
                <li class="jitd mapslon-lbl">
                    <label for="fieldmapslon<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_MAPSLON'); ?></label>
                </li>
                <li class="jitd mapslon">
                    <div class="text input">
                        <input class="inputbox" type="text" id="fieldmapslon<?php echo $this->get('id'); ?>" name="<?php echo $this->get('inputname'); ?>[value][lon]" value="<?php echo $value->get('lon'); ?>" />
                    </div>
                </li>
            </ul>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function renderInputParams() {
        $params = $this->get('params');
        ob_start(); ?>
        <div class="jitable optionstable">
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow row-fluid nodrop">
                    <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_MAPS').' '.JText::_('JICUSTOMFIELDS_FIELDPARAMS'); ?></li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd mapswidth-lbl">
                        <label for="fieldmapswidth<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_MAPSWIDTH'); ?></label>
                    </li>
                    <li class="jitd mapswidth">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldmapswidth<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][width]" value="<?php echo $params->get('width', '100%'); ?>" />
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd mapsheight-lbl">
                        <label for="fieldmapsheight<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_MAPSHEIGHT'); ?></label>
                    </li>
                    <li class="jitd mapsheight">
                        <div class="text input">
                            <input class="inputbox" type="text" id="fieldmapsheight<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->id; ?>][params][height]" value="<?php echo $params->get('height', '300px'); ?>" />
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop">
                    <li class="jitd mapsstyle-lbl">
                        <label for="fieldmapsstyle<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_MAPSSTYLE'); ?></label>
                    </li>
                    <li class="jitd mapsstyle">
                        <div class="select input">
                            <?php $choices = array(
                                'hybrid'=>JText::_('JICUSTOMFIELDS_MAPSHYBRID'),
                                'roadmap'=>JText::_('JICUSTOMFIELDS_MAPSROADMAP'),
                                'satellite'=>JText::_('JICUSTOMFIELDS_MAPSSATELLITE'),
                                'terrain'=>JText::_('JICUSTOMFIELDS_TERRAIN')
                            ); ?>
                            <select id="fieldmapsstyle<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][style]" data-placeholder="<?php echo $params->get('style'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('style'))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <?php $html = ob_get_clean();
        return $html;
    }
    public function renderOutput() {
        $params = $this->get('params');
        $value = $this->get('value', 'jiobject');

        // Start building HTML string
        $html = '';

        // Skip/hide empty
        if(($value->get('lat')==null || $value->get('lon')==null) && $params->get('hideempty', '0')==1) return $html;

        $width = $params->get('width', '100%');
        $height = $params->get('height', '300px');

        // Load Google Maps script
        $document = JFactory::getDocument();
        $document->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false');

        // Continue building HTML string
        $html.= $this->get('prefix', '');
        if($params->get('showlabel', 1)==1) $html.= $this->renderLabel();
        ob_start(); ?>
        <script type="text/javascript">
            if(typeof jQuery!='undefined') {
                jQuery(document).ready(function() {
                    if(typeof google!='undefined') {
                        google.maps.visualRefresh = true;
                        var map;
                        function initialize() {
                            var coords = new google.maps.LatLng(<?php echo $value->get('lat', 0); ?>, <?php echo $value->get('lon', 0); ?>);
                            var mapOptions = {
                                zoom: 16,
                                center: coords,
                                mapTypeId: google.maps.MapTypeId.<?php echo strtoupper($params->get('style', 'hybrid')); ?>
                            };
                            map = new google.maps.Map(document.getElementById('<?php echo $this->get('inputid'); ?>'), mapOptions);
                            var marker = new google.maps.Marker({
                                position: coords,
                                map: map,
                                title: '<?php echo $value->get('address', ''); ?>'
                            });

                        }
                        google.maps.event.addDomListener(window, 'load', initialize);
                    }
                });
            }
        </script>
        <div id="<?php echo $this->get('inputid'); ?>" class="jimaps" style="width:<?php echo $width; ?>; height:<?php echo $height; ?>;"></div>
        <?php $html.= ob_get_clean();
        $html.= $this->get('suffix', '');
        return $html;
    }
}