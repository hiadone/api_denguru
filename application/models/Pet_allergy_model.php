<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall attr model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Pet_allergy_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'pet_allergy';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'pag_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_all_allergy()
    {
        $cachename = 'pet-allergy-all';
        if ( ! $result = $this->cache->get($cachename)) {
            $return = $this->get($primary_value = '', $select = '', $where = '', $limit = '', $offset = 0, $findex = 'pag_order', $forder = 'asc');
            if ($return) {
                foreach ($return as $key => $value) {
                    $result[$value['pag_parent']][] = $value;
                }
                $this->cache->save($cachename, $result);
            }
        }
        return $result;
    }


    public function get_allergy_info($pag_id = 0)
    {
        $pag_id = (int) $pag_id;
        if (empty($pag_id) OR $pag_id < 1) {
            return;
        }
        $cachename = 'pag-allergy-detail-'.$pag_id;
        if ( ! $result = $this->cache->get($cachename)) {
            $return = $this->get($primary_value = '', $select = '', $where = '', $limit = '', $offset = 0, $findex = 'pag_order', $forder = 'asc');
            if ($return) {
                foreach ($return as $key => $value) {
                    $result[$value['pag_id']] = $value;
                }
                $this->cache->save($cachename, $result);
            }
        }
        return isset($result[$pag_id]) ? $result[$pag_id] : '';
    }


    public function get_allergy($pet_id = 0)
    {
        $pet_id = (int) $pet_id;
        if (empty($pet_id) OR $pet_id < 1) {
            return;
        }

        $this->db->select('pet_allergy.*');
        $this->db->join('pet_allergy_rel', 'pet_allergy.pag_id = pet_allergy_rel.pag_id', 'inner');
        $this->db->where(array('pet_allergy_rel.pet_id' => $pet_id));
        $this->db->order_by('pag_order', 'asc');
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
