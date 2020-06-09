<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall item model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Cmall_brand_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'cmall_brand';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'cbr_id'; // 사용되는 테이블의 프라이머리키

    

    function __construct()
    {
        parent::__construct();
    }

    public function get_all_brand()
    {
        $cachename = 'cmall-brand-all';
        if ( ! $result = $this->cache->get($cachename)) {
            $return = $this->get($primary_value = '', $select = '', $where = '', $limit = '', $offset = 0, $findex = 'cbr_id', $forder = 'asc');
            if ($return) {
                foreach ($return as $key => $value) {
                    $result[] = $value;
                }
                $this->cache->save($cachename, $result);
            }
        }
        return $result;
    }


    public function get_brand_info($cbr_id = 0)
    {
        $cbr_id = (int) $cbr_id;
        if (empty($cbr_id) OR $cbr_id < 1) {
            return;
        }
        $cachename = 'cmall-brand-detail';
        if ( ! $result = $this->cache->get($cachename)) {
            $return = $this->get($primary_value = '', $select = '', $where = '', $limit = '', $offset = 0, $findex = 'cbr_id', $forder = 'asc');
            if ($return) {
                foreach ($return as $key => $value) {
                    $result[$value['cbr_id']] = $value;
                }
                $this->cache->save($cachename, $result);
            }
        }
        return isset($result[$cbr_id]) ? $result[$cbr_id] : '';
    }


    public function get_brand($cit_id = 0)
    {
        $cit_id = (int) $cit_id;
        if (empty($cit_id) OR $cit_id < 1) {
            return;
        }

        $this->db->select('cmall_brand.*');
        $this->db->join('cmall_item', 'cmall_item.cbr_id = cmall_brand.cbr_id', 'inner');
        $this->db->where(array('cmall_item.cit_id' => $cit_id));
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
