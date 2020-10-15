<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Event group Click Log model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Event_group_click_log_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'event_group_click_log';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'ecl_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'event_group_click_log.*, event_group.egr_title, event_group.egr_datetime, event_group.egr_hit, event_group.egr_image';
        $join[] = array('table' => 'event_group', 'on' => 'event_group_click_log.egr_id = event_group.egr_id', 'type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
        return $result;
    }


    public function get_event_group_click_count($type = 'd', $start_date = '', $end_date = '', $orderby = 'asc')
    {
        if (empty($start_date) OR empty($end_date)) {
            return false;
        }
        $left = ($type === 'y') ? 4 : ($type === 'm' ? 7 : 10);
        if (strtolower($orderby) !== 'desc') $orderby = 'asc';

        $this->db->select('count(*) as cnt, left(ecl_datetime, ' . $left . ') as day ', false);
        $this->db->where('left(ecl_datetime, 10) >=', $start_date);
        $this->db->where('left(ecl_datetime, 10) <=', $end_date);
        $this->db->group_by('day');
        $this->db->order_by('ecl_datetime', $orderby);
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
