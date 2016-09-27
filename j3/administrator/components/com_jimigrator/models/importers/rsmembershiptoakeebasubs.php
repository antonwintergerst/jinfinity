<?php
/**
 * @version     $Id: rsmembershiptoakeebasubs.php 103 2014-02-27 19:17:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.6+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die;

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiimporter.php');

class RSMembershipToAkeebaSubsImporter extends JiImporter {
    /* >>> PRO >>> */
    /**
     * Map RS Membership tables to AkeebaSubs tables
     * @return string
     */
    public function getInputTable() {
        switch($this->dbtable) {
            case 'rsmembership_categories':
                $dbtable = 'akeebasubs_levelgroups';
                break;
            case 'rsmembership_configuration':
            case 'rsmembership_countries':
            case 'rsmembership_coupon_items':
            case 'rsmembership_extras':
            case 'rsmembership_extra_values':
            case 'rsmembership_extra_value_shared':
            case 'rsmembership_files':
            case 'rsmembership_logs':
            case 'rsmembership_membership_attachments':
            case 'rsmembership_membership_extras':
            case 'rsmembership_membership_shared':
            case 'rsmembership_membership_upgrades':
            case 'rsmembership_payments':
            case 'rsmembership_terms':
            case 'rsmembership_transactions':
                $dbtable = 'skip';
                break;
            case 'rsmembership_coupons':
                $dbtable = 'akeebasubs_coupons';
                break;
            case 'rsmembership_fields':
                $dbtable = 'akeebasubs_customfields';
                break;
            case 'rsmembership_memberships':
                $dbtable = 'akeebasubs_levels';
                break;
            case 'rsmembership_membership_users':
                $dbtable = 'akeebasubs_subscriptions';
                break;
            case 'rsmembership_users':
                $dbtable = 'akeebasubs_users';
                break;
            default:
                $dbtable = $this->dbtable;
                break;
        }
        return $dbtable;
    }
    /**
     * Map RS Membership data to AkeebaSubs data
     * @param $item
     */
    public function willImportTableRow(&$item) {
        parent::willImportTableRow($item);
        switch($this->dbtable) {
            case 'rsmembership_categories':
                if(isset($item->id)) $item->akeebasubs_levelgroup_id = $item->id;
                if(isset($item->name)) $item->title = $item->name;
                if(isset($item->published)) $item->enabled = $item->published;
                break;
            case 'rsmembership_coupons':
                if(isset($item->id)) $item->akeebasubs_coupon_id = $item->id;
                if(isset($item->name)) $item->title = $item->name;
                if(isset($item->name)) $item->coupon = strtoupper($item->name);
                if(isset($item->date_added)) $item->created_on = date('Y-m-d H:i:s', $item->date_added);
                if(isset($item->date_start)) $item->publish_up = date('Y-m-d H:i:s', $item->date_start);
                if(isset($item->date_end)) {
                    if($item->date_end==0) {
                        $item->publish_down = '0000-00-00 00:00:00';
                    } else {
                        $item->publish_down = date('Y-m-d H:i:s', $item->date_end);
                    }
                }
                if(isset($item->discount_price)) $item->value = $item->discount_price;
                if(isset($item->max_uses)) $item->hitslimit = $item->max_uses;
                if(isset($item->published)) $item->enabled = $item->published;
                break;
            case 'rsmembership_fields':
                if(isset($item->id)) $item->akeebasubs_customfield_id = $item->id;
                if(isset($item->name)) $item->slug = $item->name;
                if(isset($item->label)) $item->title = $item->label;
                if(isset($item->type)) {
                    switch($item->type) {
                        case 'select':
                            $item->type = 'dropdown';
                            break;
                        case 'textbox':
                        default:
                            $item->type = 'text';
                            break;
                    }
                }
                if(isset($item->values)) $item->options = $item->values;
                if(isset($item->required)) $item->allow_empty = ($item->required==1)? 0:1;
                if(isset($item->published)) $item->enabled = $item->published;
                break;
            case 'rsmembership_memberships':
                if(isset($item->id)) $item->akeebasubs_level_id = $item->id;
                if(isset($item->category_id)) $item->akeebasubs_levelgroup_id = $item->category_id;
                if(isset($item->name)) $item->title = $item->name;
                if(isset($item->period)) $item->duration = $item->period;
                if(isset($item->published)) $item->enabled = $item->published;
                break;
            case 'rsmembership_membership_users':
                if(isset($item->id)) $item->akeebasubs_subscription_id = $item->id;
                if(isset($item->membership_id)) $item->akeebasubs_level_id = $item->membership_id;
                if(isset($item->membership_start)) $item->publish_up = date('Y-m-d H:i:s', $item->membership_start);
                if(isset($item->membership_end)) {
                    if($item->membership_end==0) {
                        $item->publish_down = '0000-00-00 00:00:00';
                    } else {
                        $item->publish_down = date('Y-m-d H:i:s', $item->membership_end);
                    }
                }
                if(isset($item->price)) $item->gross_amount = $item->price;
                if(isset($item->price)) $item->net_amount = $item->price;
                if(isset($item->status)) {
                    switch($item->status) {
                        case '0':
                            $item->state = 'C';
                            break;
                        case '1':
                            $item->state = 'P';
                            break;
                        default:
                            $item->state = 'X';
                            break;
                    }
                }
                $item->processor = 'none';
                $item->processor_key = 'xxxxx';
                break;
            case 'rsmembership_users':
                $customfields = array();
                foreach($item as $key=>$value) {
                    if($key!='user_id') {
                        $newkey = substr($key, 1);
                        $customfields[$newkey] = $value;
                    }
                }
                $item->params = json_encode($customfields);
                break;
            default:
                break;
        }
    }
    /* <<< PRO <<< */
}