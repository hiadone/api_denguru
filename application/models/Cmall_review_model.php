<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall review model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Cmall_review_model extends CB_Model
{

	/**
	 * 테이블명
	 */
	public $_table = 'cmall_review';

	public $_select = 'cre_id,cit_id,cre_good,cre_bad,cre_tip,mem_id,cre_score,cre_datetime,cre_like,cre_update_datetime,cre_image,cre_file';
	/**
	 * 사용되는 테이블의 프라이머리키
	 */
	public $primary_key = 'cre_id'; // 사용되는 테이블의 프라이머리키

	function __construct()
	{
		parent::__construct();
	}


	public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
	{
		// $select = 'cmall_review.*, member.mem_id, member.mem_userid, member.mem_nickname, member.mem_is_admin, member.mem_icon,member_pet.*';

		$select = 'cmall_review.*, member.mem_id, member.mem_userid, member.mem_nickname, member.mem_is_admin, member.mem_icon';

		$join[] = array('table' => 'member', 'on' => 'cmall_review.mem_id = member.mem_id', 'type' => 'inner');
		$join[] = array('table' => 'member_pet', 'on' => 'member.mem_id = member_pet.mem_id and member_pet.pet_main = 1', 'type' => 'inner');		
		$join[] = array('table' => 'cmall_item', 'on' => 'cmall_review.cit_id = cmall_item.cit_id ', 'type' => 'inner');
		$result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
		return $result;
	}


	public function get_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
	{
		$select = $this->_select;
		$result = $this->_get_list_common($select, $join='', $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
		return $result;
	}


	public function get_review_count($cit_id = 0)
	{
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}

		$this->db->select_sum('cre_score');
		$this->db->select('count(*) as cnt, cit_id', false);
		$this->db->where('cre_status', 1);
		$this->db->where('cit_id', $cit_id);
		$this->db->group_by(array('cit_id'));
		$qry = $this->db->get($this->_table);
		$result = $qry->row_array();

		return $result;
	}

	public function get_popular($cit_id = 0,$limit=0)
	{
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}

		
		// $this->db->select('cre_content');
		$this->db->where('cre_status', 1);
		$this->db->where('cit_id', $cit_id);
		$this->db->group_start();
		$this->db->or_where('cre_image >',0);
		$this->db->or_where('cre_file >',0);
		$this->db->group_end();
		$this->db->order_by('cre_like', 'desc');
		$qry = $this->db->get($this->_table);
		if($limit) $this->db->limit($limit);
		$result = $qry->result_array();

		return $result;
	}
}
