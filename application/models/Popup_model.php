<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Popup model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Popup_model extends CB_Model
{

	/**
	 * 테이블명
	 */
	public $_table = 'popup';

	/**
	 * 사용되는 테이블의 프라이머리키
	 */
	public $primary_key = 'pop_id'; // 사용되는 테이블의 프라이머리키

	public $cache_time = 86400; // 캐시 저장시간

	function __construct()
	{
		parent::__construct();

		check_cache_dir('popup');
	}


	public function get_today_list()
	{
		$cachename = 'popup/popup-info-' . cdate('Y-m-d');
		$data = array();
		if ( ! $data = $this->cache->get($cachename)) {
			$this->db->select('pop_id,pop_start_date,pop_end_date,pop_title,pop_content,pop_disable_hours,pop_activated,pop_image,pop_deep_link_info');
			$this->db->from($this->_table);
			$this->db->where('pop_activated', 1);
			$this->db->group_start();
			$this->db->where(array('pop_start_date <=' => cdate('Y-m-d')));
			$this->db->or_where(array('pop_start_date' => null));
			$this->db->group_end();
			$this->db->group_start();
			$this->db->where('pop_end_date >=', cdate('Y-m-d'));
			$this->db->or_where('pop_end_date', '0000-00-00');
			$this->db->or_where(array('pop_end_date' => ''));
			$this->db->or_where(array('pop_end_date' => null));
			$this->db->group_end();
			$res = $this->db->get();
			$result['list'] = $res->result_array();

			if($result['list']){
				foreach($result['list'] as $key => $val){
					if($val['pop_deep_link_info'])
						$result['list'][$key]['pop_deep_link_info'] = json_decode($val['pop_deep_link_info']);	
				}
				
			}

			$data['result'] = $result;
			$data['cached'] = '1';

			$this->cache->save($cachename, $data, $this->cache_time);
		}
		return isset($data['result']) ? $data['result'] : false;
	}


	public function delete($primary_value = '', $where = '')
	{
		$result = parent::delete($primary_value, $where);
		$this->cache->delete('popup/popup-info-' . cdate('Y-m-d'));

		return $result;
	}


	public function update($primary_value = '', $updatedata = '', $where = '')
	{
		$result = parent::update($primary_value, $updatedata);
		$this->cache->delete('popup/popup-info-' . cdate('Y-m-d'));

		return $result;
	}
}
