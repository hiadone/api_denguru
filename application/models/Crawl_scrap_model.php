<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Crawl scrap model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Crawl_scrap_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'crawl_scrap';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'csr_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'crawl_scrap.*, crawl_scrap.mem_id as crawl_scrap_mem_id,  crawl.post_id, crawl.brd_id,
            crawl.crawl_datetime, crawl.crawl_hit,  crawl.crawl_title, crawl.crawl_is_image';
        $join[] = array('table' => 'crawl', 'on' => 'crawl_scrap.crawl_id = crawl.crawl_id', 'type' => 'inner');

        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
        return $result;
    }
}
