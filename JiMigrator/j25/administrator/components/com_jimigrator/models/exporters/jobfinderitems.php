<?php
/**
 * @version     $Id: exportitems.php 015 2014-11-11 20:28:00Z Anton Wintergerst $
 * @package     Jinfinity Migrator for Joomla 1.5+
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.'/administrator/components/com_jimigrator/helpers/jiexporter.php');

class JobFinderItemsExporter extends JiExporter {
    public function getColumns() {
        // columns to match Joomla 3.x structure with added job data
        $columns = array('id', 'title','introtext', 'fulltext', 'state', 'featured', 'catid', 'created', 'modified', 'publish_up', 'publish_down',
            'job_company', 'job_image', 'job_agent', 'job_state', 'job_city', 'job_industry', 'job_sector', 'job_type', 'job_qualification');
        return $columns;
    }

    public function buildExportQuery() {
        $query = 'SELECT SQL_CALC_FOUND_ROWS j.id, j.title, j.introtext, j.fulltext, j.published AS state, j.featured, "0" AS catid, j.created, j.updated AS modified, j.publishup AS publish_up, j.publishdown AS publish_down,
        com.name AS job_company,
        com.image AS job_image,
        age.email AS job_agent,
        sta.alias AS job_state,
        cit.name AS job_city,
        ind.name AS job_industry,
        sec.name AS job_sector,
        typ.name AS job_type,
        qua.name AS job_qualification
         FROM #__jobfinder_jobs AS j';
        $query.= ' LEFT JOIN #__jobfinder_companies AS com ON (com.id=j.`cid`)';
        $query.= ' LEFT JOIN #__jobfinder_agents AS age ON (age.id=j.`uid`)';
        $query.= ' LEFT JOIN #__jobfinder_states AS sta ON (sta.id=j.`state`)';
        $query.= ' LEFT JOIN #__jobfinder_cities AS cit ON (cit.id=j.`city`)';
        $query.= ' LEFT JOIN #__jobfinder_industries AS ind ON (ind.id=j.`industry`)';
        $query.= ' LEFT JOIN #__jobfinder_sectors AS sec ON (sec.id=j.`sector`)';
        $query.= ' LEFT JOIN #__jobfinder_types AS typ ON (typ.id=j.`type`)';
        $query.= ' LEFT JOIN #__jobfinder_qualifications AS qua ON (qua.id=j.`qualification`)';
        return $query;
    }
    public function willExportTableRow(&$item) {
        $this->totalprocessed++;

        // map state to catid
        $catmap = array(
            'VIC'=>17,
            'NSW'=>18,
            'QLD'=>19,
            'SA'=>20,
            'NT'=>21,
            'WA'=>22,
            'ACT'=>23,
            'TAS'=>24
        );
        if(isset($item->job_state) && array_key_exists(strtoupper($item->job_state), $catmap)) {
            $item->catid = $catmap[strtoupper($item->job_state)];
        } else {
            $item->catid = 11;
        }
        $this->setStatus(array('msg'=>'Setting `catid` to: '.$item->catid.' for ID #'.$item->id));
    }
    public function getOutputFilename() {
        return 'jobfinder_items.csv';
    }
}