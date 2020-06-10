<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Review File model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Review_file_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'review_file';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'rfi_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'review_file.*, cmall_review.mem_id as review_mem_id,cmall_review.brd_id, cmall_review.cre_datetime, cmall_review.cre_hit';
        $join[] = array('table' => 'cmall_review', 'on' => 'review_file.cre_id = cmall_review.cre_id', 'type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);

        return $result;
    }

    public function get_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'review_file.*';
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);

        return $result;
    }


    public function get_review_file_by_date($type = 'd', $start_date = '', $end_date = '', $brd_id = 0, $orderby = 'asc')
    {
        if (empty($start_date) OR empty($end_date)) {
            return false;
        }
        $left = ($type === 'y') ? 4 : ($type === 'm' ? 7 : 10);
        if (strtolower($orderby) !== 'desc') $orderby = 'asc';

        $this->db->select('count(*) as cnt, left(rfi_datetime, ' . $left . ') as day ', false);
        $this->db->where('left(rfi_datetime, 10) >=', $start_date);
        $this->db->where('left(rfi_datetime, 10) <=', $end_date);
        $brd_id = (int) $brd_id;
        if ($brd_id) {
            $this->db->where('brd_id', $brd_id);
        }
        $this->db->group_by('day');
        $this->db->order_by('rfi_datetime', $orderby);
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }


    public function get_review_file_count($review_id = 0)
    {
        $review_id = (int) $review_id;
        if (empty($review_id) OR $review_id < 1) {
            return false;
        }

        $this->db->select('count(*) as cnt, rfi_is_image ', false);
        $this->db->where('cre_id', $review_id);
        $this->db->group_by('rfi_is_image');
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
