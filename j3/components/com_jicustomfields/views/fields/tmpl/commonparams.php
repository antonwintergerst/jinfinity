<?php
/**
 * @version     $Id: commonparams.php 096 2014-12-24 10:17:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class JiCustomFieldCommonParams extends JiCustomField {
    public function renderInput() {
        $app = JFactory::getApplication();
        $jinput = $app->input;
    	$isNew = (strstr($this->get('id'), 'new')!==false);
    	$params = $this->get('params');
        ob_start(); ?>
        <div class="jitable">
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow row-fluid nodrop common">
                    <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_GENERALPARAMS'); ?></li>
                </ul>
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd fieldtitle-lbl">
                        <label for="fieldtitle<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_FIELDTITLE'); ?></label>
                    </li>
                    <li class="jitd fieldtitle">
                        <div class="text input">
                            <input class="inputbox fieldtitle" type="text" id="fieldtitle<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][title]" value="<?php echo $this->get('title'); ?>" />
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd fieldalias-lbl">
                        <label for="fieldalias<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_FIELDALIAS'); ?></label>
                    </li>
                    <li class="jitd fieldalias">
                        <div class="text input">
                            <input class="inputbox fieldalias" type="text" id="fieldalias<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][alias]" value="<?php echo $this->get('alias'); ?>" />
                        </div>
                    </li>
                </ul>
            </div>
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd fieldtype-lbl">
                        <label for="fieldtype<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_FIELDTYPE'); ?></label>
                    </li>
                    <li class="jitd fieldtype">
                        <div class="select input">
                            <select id="fieldtypeselect<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][type]" data-placeholder="<?php echo JText::_('JICUSTOMFIELDS_FIELDTYPE'); ?>" class="chzn-select typeselect">
                                <?php foreach($this->fieldtypes as $JiFieldType): ?>
                                    <?php JText::script('JICUSTOMFIELDS_'.$JiFieldType->get('name').'_DESC'); ?>
                                    <?php if($JiFieldType->get('group')!='system'): ?>
                                        <?php $selected = ($JiFieldType->get('name')==$this->get('type', 'textfield'))? ' selected="selected"':''; ?>
                                        <option value="<?php echo $JiFieldType->get('name'); ?>"<?php echo $selected; ?>><?php echo JText::_($JiFieldType->get('label')); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="typedesc"><?php echo JText::_('JICUSTOMFIELDS_'.$this->get('type', 'textfield').'_DESC'); ?></div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd fieldstate-lbl">
                        <label for="fieldstate<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_STATE'); ?></label>
                    </li>
                    <li class="jitd fieldstate">
                        <div class="select input">
                            <?php $choices = array(
                                '1'=>JText::_('JPUBLISHED'),
                                '0'=>JText::_('JUNPUBLISHED')
                            ); ?>
                            <select id="fieldstate<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][state]" data-placeholder="<?php echo $this->get('state'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$this->get('state', 1) && $this->get('id')!=0)? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd showlabel-lbl">
                        <label for="fieldshowlabel<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_OUTPUTLABEL'); ?></label>
                    </li>
                    <li class="jitd showlabel">
                        <div class="select input">
                            <?php $choices = array('1'=>JText::_('JICUSTOMFIELDS_YES'),'0'=>JText::_('JICUSTOMFIELDS_NO')); ?>
                            <select id="fieldshowlabel<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][showlabel]" data-placeholder="<?php echo JText::_('JICUSTOMFIELDS_YES'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('showlabel', 1))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd position-lbl">
                        <label for="fieldposition<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_OUTPUTPOSITION'); ?></label>
                    </li>
                    <li class="jitd position">
                        <div class="select input">
                            <?php $choices = array(
                                'above'=>JText::_('JICUSTOMFIELDS_ABOVEPOSITION'),
                                'below'=>JText::_('JICUSTOMFIELDS_BELOWPOSITION'),
                                'head'=>JText::_('JICUSTOMFIELDS_HEADPOSITION'),
                                'custom'=>JText::_('JICUSTOMFIELDS_CUSTOMPOSITION')
                            ); ?>
                            <select id="fieldposition<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][position]" data-placeholder="<?php echo $params->get('position'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('position', 'below'))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd prefix-lbl">
                        <label for="fieldprefix<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_OUTPUTPREFIX'); ?></label>
                    </li>
                    <li class="jitd prefix">
                        <div class="textarea input">
                            <textarea id="fieldprefix<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][prefix]"><?php echo htmlspecialchars($this->get('prefix', '<div class="jifield">')); ?></textarea>
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd suffix-lbl">
                        <label for="fieldsuffix<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_OUTPUTSUFFIX'); ?></label>
                    </li>
                    <li class="jitd suffix">
                        <div class="textarea input">
                            <textarea id="fieldsuffix<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][suffix]"><?php echo htmlspecialchars($this->get('suffix', '</div>')); ?></textarea>
                        </div>
                    </li>
                </ul>
                <?php if($isNew): ?>
                    <input type="hidden" id="newfield<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][new]" value="1" />
                <?php endif; ?>
            </div>
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd linkedvalues-lbl">
                        <label for="fieldlinkedvalues<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_LINKED_VALUES'); ?></label>
                    </li>
                    <li class="jitd linkedvalues">
                        <div class="select input">
                            <?php $choices = array(
                                1=>JText::_('JYES'),
                                0=>JText::_('JNO')
                            ); ?>
                            <select id="fieldlinkedvalues<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][linkedvalues]" data-placeholder="<?php echo $params->get('linkedvalues'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('linkedvalues', 1))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd showdefault-lbl">
                        <label for="fieldshowdefault<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_SHOW_DEFAULT'); ?></label>
                    </li>
                    <li class="jitd showdefault">
                        <div class="select input">
                            <?php $choices = array(
                                1=>JText::_('JYES'),
                                0=>JText::_('JNO')
                            ); ?>
                            <select id="fieldshowdefault<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][showdefault]" data-placeholder="<?php echo $params->get('showdefault'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('showdefault', 0))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd fielddefault-lbl">
                        <label for="fielddefault<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_FIELD_DEFAULT'); ?></label>
                    </li>
                    <li class="jitd fielddefault">
                        <div class="textarea input">
                            <textarea id="fielddefault<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][default]"><?php echo $params->get('default', ''); ?></textarea>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd showin-lbl">
                        <label for="fieldshowin<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_SHOW_IN'); ?></label>
                    </li>
                    <li class="jitd showin">
                        <div class="select input">
                            <?php $choices = array(
                                0=>JText::_('JICUSTOMFIELDS_SHOW_IN_ALL_VIEWS'),
                                1=>JText::_('JICUSTOMFIELDS_SHOW_IN_CATEGORIES'),
                                2=>JText::_('JICUSTOMFIELDS_SHOW_IN_ARTICLES')
                            ); ?>
                            <select id="fielddisplaycontext<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][showin]" data-placeholder="<?php echo $params->get('showin'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('showin', 0))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd hideempty-lbl">
                        <label for="fieldhideempty<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_HIDEEMPTY'); ?></label>
                    </li>
                    <li class="jitd hideempty">
                        <div class="select input">
                            <?php $choices = array(
                                1=>JText::_('JYES'),
                                0=>JText::_('JNO')
                            ); ?>
                            <select id="fieldhideempty<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][hideempty]" data-placeholder="<?php echo $params->get('hideempty'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('hideempty', 1))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="jifieldgroup row-fluid">
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd showhint-lbl">
                        <label for="fieldshowhint<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_SHOW_HINT'); ?></label>
                    </li>
                    <li class="jitd showhint">
                        <div class="select input">
                            <?php $choices = array(
                                1=>JText::_('JYES'),
                                0=>JText::_('JNO')
                            ); ?>
                            <select id="fieldshowhint<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][showhint]" data-placeholder="<?php echo $params->get('showhint'); ?>" class="chzn-select">
                                <?php foreach($choices as $value=>$label): ?>
                                    <?php $selected = ($value==$params->get('showhint', 0))? ' selected="selected"':''; ?>
                                    <option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
                <ul class="jitrow span6 nodrop common">
                    <li class="jitd fieldhint-lbl">
                        <label for="fieldhint<?php echo $this->get('id'); ?>"><?php echo JText::_('JICUSTOMFIELDS_FIELD_HINT'); ?></label>
                    </li>
                    <li class="jitd fieldhint">
                        <div class="textarea input">
                            <textarea id="fieldhint<?php echo $this->get('id'); ?>" name="jifields[<?php echo $this->get('id'); ?>][params][hint]"><?php echo $params->get('hint', ''); ?></textarea>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <?php // new field with unspecified field type
        if($this->get('id')==0 && $jinput->get('option')=='com_jicustomfields' && $jinput->get('view')=='field'): ?>
            <div class="jitable">
                <div class="jifieldgroup row-fluid">
                    <ul class="jitrow row-fluid nodrop common">
                        <li class="jitd span12 header"><?php echo JText::_('JICUSTOMFIELDS_TYPEPARAMS'); ?></li>
                    </ul>
                    <div class="jifieldgroup row-fluid">
                        <ul class="jitrow span12 nodrop common">
                            <li class="jitd">
                                <p>More type specific options will become available after field is first saved.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php $html = ob_get_clean();
        return $html;
    }
}