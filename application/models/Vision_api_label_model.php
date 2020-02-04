<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vision api label class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Vision_api_label_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'vision_api_label';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'val_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_vision_api_label_count($type = 'd', $start_date = '', $end_date = '', $brd_id = 0)
    {
        if (empty($start_date) OR empty($end_date)) {
            return false;
        }
        $left = ($type === 'y') ? 4 : ($type === 'm' ? 7 : 10);

        $this->db->select('count(*) as cnt, val_tag ', false);
        $this->db->from('vision_api_label');
        $this->db->join('crawl', 'crawl.crawl_id = vision_api_label.crawl_id', 'left');
        $this->db->where('left(crawl_datetime, 10) >=', $start_date);
        $this->db->where('left(crawl_datetime, 10) <=', $end_date);
        $this->db->where('crawl_del', 0);
        $brd_id = (int) $brd_id;
        if ($brd_id) {
            $this->db->where('crawl.brd_id', $brd_id);
        }
        $this->db->group_by('val_tag');
        $this->db->order_by('cnt', 'desc');
        $qry = $this->db->get();
        $result = $qry->result_array();

        return $result;
    }


    public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'vision_api_label.*, crawl.mem_id as crawl_mem_id, crawl.crawl_userid, crawl.crawl_nickname, crawl.brd_id, crawl.crawl_datetime, crawl.crawl_hit, crawl.crawl_secret, crawl.crawl_title';
        $join[] = array('table' => 'crawl', 'on' => 'vision_api_label.crawl_id = crawl.crawl_id', 'type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);

        return $result;
    }


    /**
     * List 페이지 커스테마이징 함수
     */
    public function get_tag_list ($limit = '', $offset = '', $tag = '')
    {
        if (empty($tag)) {
            return false;
        }

        $this->db->select('crawl.*, board.brd_key, board.brd_name, board.brd_mobile_name, board.brd_order, board.brd_search ');
        $this->db->from('crawl ');
        $this->db->join('board', 'crawl.brd_id = board.brd_id', 'inner');
        $this->db->join('vision_api_label', 'crawl.crawl_id = vision_api_label.crawl_id', 'inner');

        $where = array(
            'board.brd_search' => 1,
            'crawl.crawl_secret' => 0,
            'vision_api_label.val_tag' => $tag,
            'crawl.crawl_del' => 0,
        );
        $this->db->where($where);
        $this->db->order_by('crawl.crawl_num, crawl.crawl_reply');
        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $qry = $this->db->get();

        $result['list'] = $qry->result_array();

        $this->db->select('count(*) cnt');
        $this->db->from('crawl');
        $this->db->join('board', 'crawl.brd_id = board.brd_id', 'inner');
        $this->db->join('vision_api_label', 'crawl.crawl_id = vision_api_label.crawl_id', 'inner');
        $this->db->where($where);
        $qry = $this->db->get();
        $cnt = $qry->row_array();
        $result['total_rows'] = element('cnt', $cnt);

        return $result;
    }


    public function get_popular_tags($start_date = '', $limit = '')
    {
        $this->db->select('count(*) as cnt, val_tag ', false);
        $this->db->from('vision_api_label');
        $this->db->join('crawl', 'crawl.crawl_id = vision_api_label.crawl_id', 'left');
        $this->db->where('left(crawl_datetime, 10) >=', $start_date);
        $this->db->where('crawl_del', 0);
        $this->db->group_by('val_tag');
        $this->db->order_by('cnt', 'desc');
        if ($limit) {
            $this->db->limit($limit);
        }
        $qry = $this->db->get();
        $result = $qry->result_array();

        return $result;
    }
}
