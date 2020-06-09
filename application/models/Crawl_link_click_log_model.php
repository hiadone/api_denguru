<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Crawl Link Click Log model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Crawl_link_click_log_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'crawl_link_click_log';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'clc_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'crawl_link_click_log.*, crawl.*, crawl_link.*';
        $join[] = array('table' => 'crawl_link', 'on' => 'crawl_link_click_log.cln_id = crawl_link.cln_id', 'type' => 'inner');
        $join[] = array('table' => 'crawl', 'on' => 'crawl_link.crawl_id = crawl.crawl_id', 'type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);

        return $result;
    }


    public function get_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'crawl_link_click_log.*, crawl.*, crawl_link.*';
        $join[] = array('table' => 'crawl_link', 'on' => 'crawl_link_click_log.cln_id = crawl_link.cln_id', 'type' => 'inner');
        $join[] = array('table' => 'crawl', 'on' => 'crawl_link.crawl_id = crawl.crawl_id', 'type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);

        return $result;
    }


    public function get_link_click_count($type = 'd', $start_date = '', $end_date = '', $brd_id = 0, $orderby = 'asc')
    {
        if (empty($start_date) OR empty($end_date)) {
            return false;
        }
        $left = ($type === 'y') ? 4 : ($type === 'm' ? 7 : 10);
        if (strtolower($orderby) !== 'desc') $orderby = 'asc';

        $this->db->select('count(*) as cnt, left(clc_datetime, ' . $left . ') as day ', false);
        $this->db->where('left(clc_datetime, 10) >=', $start_date);
        $this->db->where('left(clc_datetime, 10) <=', $end_date);
        $brd_id = (int) $brd_id;
        if ($brd_id) {
            $this->db->where('brd_id', $brd_id);
        }
        $this->db->group_by('day');
        $this->db->order_by('clc_datetime', $orderby);
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
