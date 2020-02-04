<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Board model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Board_crawl_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'board_crawl';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'bdc_id'; // 사용되는 테이블의 프라이머리키

    public $cache_prefix = 'board/board-crawl-model-get-'; // 캐시 사용시 프리픽스

    public $cache_time = 86400; // 캐시 저장시간

    function __construct()
    {
        parent::__construct();

        check_cache_dir('board');
    }


    public function get_board_list($where = '')
    {
        $result = $this->get('', '', $where, '', 0);
        return $result;
    }


    public function get_one($primary_value = '', $select = '', $where = '')
    {
        $use_cache = false;
        if ($primary_value && empty($select) && empty($where)) {
            $use_cache = true;
        }

        if ($use_cache) {
            $cachename = $this->cache_prefix . $primary_value;
            if ( ! $result = $this->cache->get($cachename)) {
                $result = parent::get_one($primary_value);
                $this->cache->save($cachename, $result, $this->cache_time);
            }
        } else {
            $result = parent::get_one($primary_value, $select, $where);
        }
        return $result;
    }


    public function delete($primary_value = '', $where = '')
    {
        if (empty($primary_value)) {
            return false;
        }
        $result = parent::delete($primary_value);
        $this->cache->delete($this->cache_prefix . $primary_value);
        return $result;
    }


    public function update($primary_value = '', $updatedata = '', $where = '')
    {
        if (empty($primary_value)) {
            return false;
        }

        $result = parent::update($primary_value, $updatedata);
        if ($result) {
            $this->cache->delete($this->cache_prefix . $primary_value);
        }
        return $result;
    }


    
}
