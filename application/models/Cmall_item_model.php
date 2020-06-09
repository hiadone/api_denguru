<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall item model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Cmall_item_model extends CB_Model
{

	/**
	 * 테이블명
	 */
	public $_table = 'cmall_item';

	/**
	 * 사용되는 테이블의 프라이머리키
	 */
	public $primary_key = 'cit_id'; // 사용되는 테이블의 프라이머리키



	public $cache_time = 86400; // 캐시 저장시간
	
	public $allow_order = array('cit_order asc', 'cit_datetime desc', 'cit_datetime asc', 'cit_hit desc', 'cit_hit asc', 'cit_review_count desc',
		'cit_review_count asc', 'cit_review_average desc', 'cit_review_average asc', 'cit_price desc', 'cit_price asc', 'cit_sell_count desc');

	
	function __construct()
	{
		parent::__construct();
	}


	// public function get_latest($config)
	// {
	// 	$where['cit_status'] = 1;
	// 	if (element('cit_type1', $config)) {
	// 		$where['cit_type1'] = 1;
	// 	}
	// 	if (element('cit_type2', $config)) {
	// 		$where['cit_type2'] = 1;
	// 	}
	// 	if (element('cit_type3', $config)) {
	// 		$where['cit_type3'] = 1;
	// 	}
	// 	if (element('cit_type4', $config)) {
	// 		$where['cit_type4'] = 1;
	// 	}
	// 	$limit = element('limit', $config) ? element('limit', $config) : 4;

	// 	$cachename = 'cmall/main-' . element('cit_type1', $config) . '-' . $limit . '-' . cdate('Y-m-d');

	// 	if ( ! $result = $this->cache->get($cachename)) {
	// 		$this->db->select('cmall_item.*,board.*,cmall_brand.*');
	// 		$this->db->join('board', 'cmall_item.brd_id = board.brd_id', 'inner');
	// 		$this->db->join('cmall_brand', 'cmall_item.cit_brand = cmall_brand.cbr_id', 'left');
	// 		$this->db->where($where);
	// 		$this->db->limit($limit);
	// 		$this->db->order_by('cit_order', 'asc');
	// 		$qry = $this->db->get($this->_table);
	// 		$result = $qry->result_array();
	// 		$this->cache->save($cachename, $result, $this->cache_time);
	// 	}
	// 	return $result;
	// }


	/**
	 * List 페이지 커스테마이징 함수
	 */
	


	public function update_hit($primary_value = '')
	{
		if (empty($primary_value)) {
			return false;
		}

		$this->db->where($this->primary_key, $primary_value);
		$this->db->set('cit_hit', 'cit_hit+1', false);
		$result = $this->db->update($this->_table);
		return $result;
	}

	
}
