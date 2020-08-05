<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall attr model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Pet_attr_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'pet_attr';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'pat_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_all_attr()
    {
        $cachename = 'pet-attr-all';
        if ( ! $result = $this->cache->get($cachename)) {
            $return = $this->get($primary_value = '', $select = 'pat_id,pat_value,pat_parent', $where = '', $limit = '', $offset = 0, $findex = 'pat_order', $forder = 'asc');
            if ($return) {
                foreach ($return as $key => $value) {
                    $result[$value['pat_parent']][] = $value;
                }
                $this->cache->save($cachename, $result);
            }
        }
        return $result;
    }


    public function get_attr_info($pat_id = 0)
    {
        $pat_id = (int) $pat_id;
        if (empty($pat_id) OR $pat_id < 1) {
            return;
        }
        $cachename = 'pet-attr-detail-'.$pat_id;
        if ( ! $result = $this->cache->get($cachename)) {
            $return = $this->get($primary_value = '', $select = '', $where = '', $limit = '', $offset = 0, $findex = 'pat_order', $forder = 'asc');
            if ($return) {
                foreach ($return as $key => $value) {
                    $result[$value['pat_id']] = $value;
                }
                $this->cache->save($cachename, $result);
            }
        }
        return isset($result[$pat_id]) ? $result[$pat_id] : '';
    }


    public function get_attr($pet_id = 0)
    {
        $pet_id = (int) $pet_id;
        if (empty($pet_id) OR $pet_id < 1) {
            return;
        }

        $this->db->select('pet_attr.*');
        $this->db->join('pet_attr_rel', 'pet_attr.pat_id = pet_attr_rel.pat_id', 'inner');
        $this->db->where(array('pet_attr_rel.pet_id' => $pet_id));
        $this->db->order_by('pat_order', 'asc');
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
