<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Crawl File model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Crawl_file_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'crawl_file';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'cfi_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'crawl_file.*, crawl.*';
        $join[] = array('table' => 'crawl', 'on' => 'crawl_file.crawl_id = crawl.crawl_id', 'type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);

        return $result;
    }


    public function get_crawl_file_by_date($type = 'd', $start_date = '', $end_date = '', $brd_id = 0, $orderby = 'asc')
    {
        if (empty($start_date) OR empty($end_date)) {
            return false;
        }
        $left = ($type === 'y') ? 4 : ($type === 'm' ? 7 : 10);
        if (strtolower($orderby) !== 'desc') $orderby = 'asc';

        $this->db->select('count(*) as cnt, left(cfi_datetime, ' . $left . ') as day ', false);
        $this->db->where('left(cfi_datetime, 10) >=', $start_date);
        $this->db->where('left(cfi_datetime, 10) <=', $end_date);
        $brd_id = (int) $brd_id;
        if ($brd_id) {
            $this->db->where('brd_id', $brd_id);
        }
        $this->db->group_by('day');
        $this->db->order_by('cfi_datetime', $orderby);
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }


    public function get_crawl_file_count($crawl_id = 0)
    {
        $post_id = (int) $post_id;
        if (empty($post_id) OR $post_id < 1) {
            return false;
        }

        $this->db->select('count(*) as cnt, cfi_is_image ', false);
        $this->db->where('crawl_id', $crawl_id);
        $this->db->group_by('cfi_is_image');
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
