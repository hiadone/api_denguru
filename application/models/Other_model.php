<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Other model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Other_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'other';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'oth_id'; // 사용되는 테이블의 프라이머리키

    public $cache_time = 86400; // 캐시 저장시간

    function __construct()
    {
        parent::__construct();

        check_cache_dir('other');
    }


    public function get_other($type = '', $limit = '')
    {
        
        if (strtolower($type) !== 'order') {
            $type = 'random';
        }

        $cachename = 'other/other-' . $type . '-' . cdate('Y-m-d');

        if ( ! $result = $this->cache->get($cachename)) {
            $this->db->select('oth_start_date,oth_end_date,oth_title,oth_url,oth_image');
            $this->db->from($this->_table);
            $this->db->where('oth_activated', 1);
            $this->db->group_start();
            $this->db->where(array('oth_start_date <=' => cdate('Y-m-d')));
            $this->db->or_where(array('oth_start_date' => null));
            $this->db->group_end();
            $this->db->group_start();
            $this->db->where('oth_end_date >=', cdate('Y-m-d'));
            $this->db->or_where('oth_end_date', '0000-00-00');
            $this->db->or_where(array('oth_end_date' => ''));
            $this->db->or_where(array('oth_end_date' => null));
            $this->db->group_end();
            $this->db->order_by('oth_order', 'DESC');
            $res = $this->db->get();
            $result = $res->result_array();

            $this->cache->save($cachename, $result, $this->cache_time);
        }

        if ($type === 'random') {
            shuffle($result);
        }
        if ($limit) {
            $result = array_slice($result, 0, $limit);
        }
        return $result;
    }
}
