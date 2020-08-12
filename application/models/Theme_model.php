<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Theme model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Theme_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'theme';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'the_id'; // 사용되는 테이블의 프라이머리키

    public $cache_time = 86400; // 캐시 저장시간

    function __construct()
    {
        parent::__construct();

        check_cache_dir('theme');
    }


    public function get_theme($type = '', $limit = '')
    {
        
        if (strtolower($type) !== 'order') {
            $type = 'random';
        }

        $cachename = 'theme/theme-' . $type . '-' . cdate('Y-m-d');

        // if ( ! $result = $this->cache->get($cachename)) {
            $this->db->select('theme.the_id,the_title,brd_id');
            $this->db->from($this->_table);
            $this->db->join('theme_rel', 'theme.the_id = theme_rel.the_id', 'inner');
            // $this->db->where(array('theme_rel.the_id' => $the_id));
            $this->db->where('the_activated', 1);
            $this->db->group_start();
            $this->db->where(array('the_start_date <=' => cdate('Y-m-d')));
            $this->db->or_where(array('the_start_date' => null));
            $this->db->group_end();
            $this->db->group_start();
            $this->db->where('the_end_date >=', cdate('Y-m-d'));
            $this->db->or_where('the_end_date', '0000-00-00');
            $this->db->or_where(array('the_end_date' => ''));
            $this->db->or_where(array('the_end_date' => null));
            $this->db->group_end();
            $this->db->order_by('the_order', 'DESC');
            $res = $this->db->get();
            $result = $res->result_array();

            $this->cache->save($cachename, $result, $this->cache_time);
        // }

        if ($type === 'random') {
            shuffle($result);
        }
        if ($limit) {
            $result = array_slice($result, 0, $limit);
        }
        return $result;
    }

    public function get_theme_rel($the_id = 0)
    {
        $the_id = (int) $the_id;
        if (empty($the_id) OR $the_id < 1) {
            return;
        }

        $this->db->select('theme_rel.*');
        $this->db->join('theme_rel', 'theme.the_id = theme_rel.the_id', 'inner');
        $this->db->where(array('theme_rel.the_id' => $the_id));
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}
