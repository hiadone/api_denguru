<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall storewishlist model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Cmall_storewishlist_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'cmall_storewishlist';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'csi_id'; // 사용되는 테이블의 프라이머리키

    public $_select = 'cmall_storewishlist.*'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'cmall_storewishlist.*, board.brd_name, board.brd_key, board.brd_image';
        $join[] = array('table' => 'board', 'on' => 'cmall_storewishlist.cit_id = board.cit_id', 'type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
        return $result;
    }


    public function get_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = $this->_select;
        $result = $this->_get_list_common($select, $join='', $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
        return $result;
    }


    public function get_rank($start_date = '', $end_date = '')
    {
        if (empty($start_date) OR empty($end_date)) {
            return false;
        }

        $this->db->where('left(cwi_datetime, 10) >=', $start_date);
        $this->db->where('left(cwi_datetime, 10) <=', $end_date);
        $this->db->select('cmall_storewishlist.cit_id, board.cit_name');
        $this->db->join('board', 'cmall_storewishlist.cit_id = board.cit_id', 'inner');
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
