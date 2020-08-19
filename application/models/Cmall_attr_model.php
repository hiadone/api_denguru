<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall attr model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Cmall_attr_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'cmall_attr';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'cat_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_all_attr()
    {
        $cachename = 'cmall-attr-all';
        if ( ! $result = $this->cache->get($cachename)) {
            $return = $this->get($primary_value = '', $select = '', $where = '', $limit = '', $offset = 0, $findex = 'cat_order', $forder = 'asc');
            if ($return) {
                foreach ($return as $key => $value) {
                    $result[$value['cat_parent']][] = $value;
                }
                $this->cache->save($cachename, $result);
            }
        }
        return $result;
    }


    public function get_attr_info($cat_id = 0)
    {
        $cat_id = (int) $cat_id;
        if (empty($cat_id) OR $cat_id < 1) {
            return;
        }
        $cachename = 'cmall-attr-detail';
        if ( ! $result = $this->cache->get($cachename)) {
            $return = $this->get($primary_value = '', $select = '', $where = '', $limit = '', $offset = 0, $findex = 'cat_order', $forder = 'asc');
            if ($return) {
                foreach ($return as $key => $value) {
                    $result[$value['cat_id']] = $value;
                }
                $this->cache->save($cachename, $result);
            }
        }
        return isset($result[$cat_id]) ? $result[$cat_id] : '';
    }


    public function get_attr($cit_id = 0)
    {
        $cit_id = (int) $cit_id;
        if (empty($cit_id) OR $cit_id < 1) {
            return;
        }

        $this->db->select('cmall_attr.*');
        $this->db->join('cmall_attr_rel', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
        $this->db->where(array('cmall_attr_rel.cit_id' => $cit_id));
        $this->db->order_by('cat_order', 'asc');
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }

    public function get_review_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'cmall_review.*';
        $join[] = array('table' => 'cmall_attr_rel', 'on' => 'cmall_attr.cat_id = cmall_attr_rel.cat_id','type' => 'inner');
        $join[] = array('table' => 'cmall_review', 'on' => 'cmall_review.cit_id = cmall_attr_rel.cit_id', 'type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
        return $result;
    }

    public function get_popular_attr($brd_id = 0, $limit = '')
    {
        $this->db->select('count(*) as cnt, cat_value ', false);
        $this->db->from('cmall_attr');
        $this->db->join('cmall_attr_rel', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
        $this->db->join('cmall_item', 'cmall_item.cit_id = cmall_attr_rel.cit_id', 'inner');

        // $this->db->where('left(crawl_datetime, 10) >=', $start_date);
        if($brd_id)
            $this->db->where('cmall_item.brd_id', $brd_id);
        $this->db->where('cit_status', 1);
        $this->db->group_by('cat_value');
        $this->db->order_by('cnt', 'desc');
        if ($limit) {
            $this->db->limit($limit);
        }
        $qry = $this->db->get();
        $result = $qry->result_array();

        return $result;
    }
}

