<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Faq model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Faq_model extends CB_Model
{

	/**
	 * 테이블명
	 */
	public $_table = 'faq';

	public $_select = 'faq_id,fgr_id,faq_title,faq_content,faq_content_html_type,faq_datetime';

	/**
	 * 사용되는 테이블의 프라이머리키
	 */
	public $primary_key = 'faq_id'; // 사용되는 테이블의 프라이머리키

    public $cache_time = 86400; // 캐시 저장시간

	function __construct()
	{
		parent::__construct();

        
	}


	public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
	{
		$select = 'faq.*, member.mem_id, member.mem_userid, member.mem_nickname, member.mem_is_admin, member.mem_icon';
		$join[] = array('table' => 'member', 'on' => 'faq.mem_id = member.mem_id', 'type' => 'left');
		$result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
		return $result;
	}

	public function get_today_list($where = array())
    {
        $cachename = 'faq/faq-list-' . cdate('Y-m-d');
        $data = array();
        if ( ! $data = $this->cache->get($cachename)) {
            $this->db->select($this->_select);
            $this->db->from($this->_table);
            $this->db->where($where);
            $res = $this->db->get();
            $result['list'] = $res->result_array();

            $data['result'] = $result;
            $data['cached'] = '1';
            check_cache_dir('faq');
            $this->cache->save($cachename, $data, $this->cache_time);
        }
        return isset($data['result']) ? $data['result'] : false;
    }
}
