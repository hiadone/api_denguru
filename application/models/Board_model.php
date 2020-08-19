<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Board model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Board_model extends CB_Model
{

	/**
	 * 테이블명
	 */
	public $_table = 'board';

	/**
	 * 사용되는 테이블의 프라이머리키
	 */
	public $primary_key = 'brd_id'; // 사용되는 테이블의 프라이머리키

	public $_select = 'board.brd_id,board.brd_name,board.brd_image,board.brd_blind,cmall_item.cit_id,cmall_item.cit_name,cmall_item.cit_file_1,cmall_item.cit_review_average,cmall_item.cit_price,cmall_item.cit_price_sale,cmall_brand.cbr_id,cmall_brand.cbr_value_kr,cmall_brand.cbr_value_en';

	public $allow_order = array('cit_order asc', 'cit_datetime desc', 'cit_datetime asc', 'cit_hit desc', 'cit_hit asc', 'cit_review_count desc',
		'cit_review_count asc', 'cit_review_average desc', 'cit_review_average asc', 'cit_price desc', 'cit_price asc', 'cit_sell_count desc');

	public $cache_prefix = 'board/board-model-get-'; // 캐시 사용시 프리픽스

	public $cache_time = 43200; // 캐시 저장시간

	function __construct()
	{
		parent::__construct();

		check_cache_dir('board');
	}


	public function get_board_list($where = '',$select = '',$limit = '')
	{
		$result = $this->get($limit, $select, $where, '', 0, 'brd_order', 'ASC');
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


	public function get_group_select($bgr_id = 0)
	{
		$bgr_id = (int) $bgr_id;

		$option = '<option value="0">그룹선택</option>';
		$this->db->order_by('bgr_order', 'ASC');
		$this->db->select('bgr_id, bgr_name');
		$qry = $this->db->get('board_group');
		foreach ($qry->result_array() as $row) {
			$option .= '<option value="' . $row['bgr_id'] . '"';
			if ((int) $row['bgr_id'] === $bgr_id) {
				$option .= ' selected="selected" ';
			}
			$option .= '>' . $row['bgr_name'] . '</option>';
		}
		return $option;
	}

	public function get_item_list($limit = '', $offset = '', $where = '', $category_id = 0, $orderby = '', $sfield = '', $skeyword = '', $sop = 'OR')
	{

		if ( !$orderby) {
			$orderby = 'brd_order asc';
		}
		$sop = (strtoupper($sop) === 'AND') ? 'AND' : 'OR';
		if (empty($sfield)) {
			$sfield = array('cit_name', 'cit_content');
		}

		$search_where = array();
		$search_like = array();
		$search_or_like = array();
		if ($sfield && is_array($sfield)) {
			foreach ($sfield as $skey => $sval) {
				$ssf = $sval;
				if ($skeyword && $ssf && in_array($ssf, $this->allow_search_field)) {
					if (in_array($ssf, $this->search_field_equal)) {
						$search_where[$ssf] = $skeyword;
					} else {
						$swordarray = explode(' ', $skeyword);
						foreach ($swordarray as $str) {
							if (empty($ssf)) {
								continue;
							}
							if ($sop === 'AND') {
								$search_like[] = array($ssf => $str);
							} else {
								$search_or_like[] = array($ssf => $str);
							}
						}
					}
				}
			}
		} else {
			$ssf = $sfield;
			if ($skeyword && $ssf && in_array($ssf, $this->allow_search_field)) {
				if (in_array($ssf, $this->search_field_equal)) {
					$search_where[$ssf] = $skeyword;
				} else {
					$swordarray = explode(' ', $skeyword);
					foreach ($swordarray as $str) {
						if (empty($ssf)) {
							continue;
						}
						if ($sop === 'AND') {
							$search_like[] = array($ssf => $str);
						} else {
							$search_or_like[] = array($ssf => $str);
						}
					}
				}
			}
		}


		$this->db->select($this->_select);
		$this->db->from($this->_table);
		$this->db->join('cmall_item', 'board.brd_id = cmall_item.brd_id', 'inner');
		$this->db->join('cmall_brand', 'cmall_item.cbr_id = cmall_brand.cbr_id', 'left');
		if ($where) {
			$this->db->where($where);
		}
		if ($search_where) {
			$this->db->where($search_where);
		}
		if ($this->where_in) {
			$this->db->where_in(key($this->where_in),$this->where_in[key($this->where_in)]);

			if(key($this->where_in) === 'cca_id' && empty($category_id)){
				$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
			}
		}
		$category_id = (int) $category_id;
		if ($category_id) {
			$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
			$this->db->where('cca_id', $category_id);
		}
		if ($search_like) {
			foreach ($search_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->like($skey, $sval);
				}
			}
		}
		if ($search_or_like) {
			$this->db->group_start();
			foreach ($search_or_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->or_like($skey, $sval);
				}
			}
			$this->db->group_end();
		}

		$this->db->order_by($orderby);
		if ($limit) {
			$this->db->limit($limit, $offset);
		}
		$qry = $this->db->get();
		$result['list'] = $qry->result_array();

		$this->db->select('count(*) as rownum');
		$this->db->from($this->_table);
		$this->db->join('cmall_item', 'board.brd_id = cmall_item.brd_id', 'inner');
		$this->db->join('cmall_brand', 'cmall_item.cbr_id = cmall_brand.cbr_id', 'left');
		if ($where) {
			$this->db->where($where);
		}
		if ($search_where) {
			$this->db->where($search_where);
		}
		if ($this->where_in) {
			$this->db->where_in(key($this->where_in),$this->where_in[key($this->where_in)]);

			if(key($this->where_in) === 'cca_id' && empty($category_id)){
				$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
			}
		}
		if ($category_id) {
			$this->db->join('cmall_category_rel', 'cmall_item.cbr_id = cmall_category_rel.cit_id', 'inner');
			$this->db->where('cca_id', $category_id);
		}
		if ($search_like) {
			foreach ($search_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->like($skey, $sval);
				}
			}
		}
		if ($search_or_like) {
			$this->db->group_start();
			foreach ($search_or_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->or_like($skey, $sval);
				}
			}
			$this->db->group_end();
		}
		$qry = $this->db->get();
		$rows = $qry->row_array();
		$result['total_rows'] = $rows['rownum'];

		return $result;
	}

	public function get_cit_one($primary_value = '', $select = '', $where = '')
	{
		$use_cache = false;
		if ($primary_value && empty($select) && empty($where)) {
			$use_cache = true;
		}

		if ($use_cache) {
			$cachename = $this->cache_prefix .'cit-'. $primary_value;
			// if ( ! $result = $this->cache->get($cachename)) {

				$this->db->select($this->_select);
				$this->db->from($this->_table);
				$this->db->join('cmall_item', 'board.brd_id = cmall_item.brd_id', 'inner');
				$this->db->join('cmall_brand', 'cmall_item.cbr_id = cmall_brand.cbr_id', 'left');
				$this->db->where('cmall_item.cit_id', $primary_value);

				$res = $this->db->get();
				$result = $res->row_array();
				$this->cache->save($cachename, $result, $this->cache_time);
			// }
		} else {
			$this->db->select($this->_select);
			$this->db->from($this->_table);
			$this->db->join('cmall_item', 'board.brd_id = cmall_item.brd_id', 'inner');
			$this->db->join('cmall_brand', 'cmall_item.cbr_id = cmall_brand.cbr_id', 'left');
			$this->db->where($where);
			
			$res = $this->db->get();
			$result = $res->row_array();
		}

		return $result;
	}

	public function get_attr_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = $this->_select;
        $join[] = array('table' => 'cmall_item', 'on' => 'board.brd_id = cmall_item.brd_id','type' => 'inner');
        $join[] = array('table' => 'cmall_attr_rel', 'on' => 'cmall_item.cit_id = cmall_attr_rel.cit_id','type' => 'inner');
        $join[] = array('table' => 'cmall_attr', 'on' => 'cmall_attr_rel.cat_id = cmall_attr.cat_id','type' => 'inner');
        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
        return $result;
    }


    /**
	 * List 페이지 커스테마이징 함수
	 */
	public function get_search_list($limit = '', $offset = '', $where = '', $like = '', $category_id = 0, $orderby = '', $sfield = '', $skeyword = '', $sop = 'OR')
	{
		if ( ! in_array(strtolower($orderby), $this->allow_order)) {
			$orderby = 'cit_order asc';
		}

		$sop = (strtoupper($sop) === 'AND') ? 'AND' : 'OR';
		if (empty($sfield)) {
			$sfield = array('cit_name', 'cta_tag', 'cca_value','cbr_value_kr','cbr_value_en');
		}

		$search_where = array();
		$search_like = array();
		$search_or_like = array();
		if ($sfield && is_array($sfield)) {
			foreach ($sfield as $skey => $sval) {
				$ssf = $sval;
				if ($skeyword && $ssf && in_array($ssf, $this->allow_search_field)) {
					if (in_array($ssf, $this->search_field_equal)) {
						$search_where[$ssf] = $skeyword;
					} else {
						$swordarray = explode(' ', $skeyword);
						foreach ($swordarray as $str) {
							if (empty($ssf)) {
								continue;
							}
							if ($sop === 'AND') {
								$search_like[] = array($ssf => $str);
							} else {
								$search_or_like[] = array($ssf => $str);
							}
						}
					}
				}
			}
		} else {
			$ssf = $sfield;
			if ($skeyword && $ssf && in_array($ssf, $this->allow_search_field)) {
				if (in_array($ssf, $this->search_field_equal)) {
					$search_where[$ssf] = $skeyword;
				} else {
					$swordarray = explode(' ', $skeyword);
					foreach ($swordarray as $str) {
						if (empty($ssf)) {
							continue;
						}
						if ($sop === 'AND') {
							$search_like[] = array($ssf => $str);
						} else {
							$search_or_like[] = array($ssf => $str);
						}
					}
				}
			}
		}

		$this->db->select('cmall_item.cit_id,cmall_item.cit_name,cmall_item.cit_file_1,cmall_item.cit_review_average,cmall_item.cit_price,cmall_item.cit_price_sale, board.brd_key, board.brd_name, board.brd_order, board.brd_search, cmall_attr.cat_id, cmall_attr.cat_value, cmall_attr.cat_parent, cmall_category.cca_id, cmall_category.cca_value, cmall_category.cca_parent ');
		$this->db->from('board');
		$this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');
		$this->db->join('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');
		$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
		$this->db->join('cmall_brand', 'cmall_brand.cbr_id = cmall_item.cbr_id', 'inner');


		// if($skeyword){
			$this->db->join('crawl_tag', 'crawl_tag.brd_id = board.brd_id', 'inner');
			// if (empty($category_id)) {
				$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
			// }
			$this->db->join('cmall_category', 'cmall_category.cca_id = cmall_category_rel.cca_id', 'inner');
			

		// }

		if ($where) {
			$this->db->where($where);
		}
		if ($search_where) {
			$this->db->where($search_where);
		}

		// $category_id = (int) $category_id;
		// if ($category_id) {
		// 	$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
		// 	$this->db->where('cca_id', $category_id);
		// }

		if ($like) {
			$this->db->like($like);
		}
		if ($search_like) {
			foreach ($search_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->like($skey, $sval);
				}
			}
		}
		if ($search_or_like) {
			$this->db->group_start();
			foreach ($search_or_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->or_like($skey, $sval);
				}
			}
			$this->db->group_end();
		}
		$this->db->where( array('brd_search' => 1));
		// $board_id = (int) $board_id;
		// if ($board_id)	{
		// 	$this->db->where( array('b.brd_id' => $board_id));
		// }

		$this->db->order_by($orderby);
		if ($limit) {
			$this->db->limit($limit, $offset);
		}
		$qry = $this->db->get();
		$result['list'] = $qry->result_array();

		$this->db->select('count(*) cnt, board.brd_id');
		$this->db->from('board');
		$this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');
		$this->db->join('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');
		$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
		$this->db->join('cmall_brand', 'cmall_brand.cbr_id = cmall_item.cbr_id', 'inner');
		
		// if($skeyword){
			$this->db->join('crawl_tag', 'crawl_tag.brd_id = board.brd_id', 'inner');
			// if (empty($category_id)) {
				$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
			// }
			$this->db->join('cmall_category', 'cmall_category.cca_id = cmall_category_rel.cca_id', 'inner');
			

		// }

		if ($where) {
			$this->db->where($where);
		}
		if ($search_where) {
			$this->db->where($search_where);
		}

		// if ($category_id) {
		// 	$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
		// 	$this->db->where('cca_id', $category_id);
		// }
		
		if ($like) {
			$this->db->like($like);
		}
		if ($search_like) {
			foreach ($search_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->like($skey, $sval);
				}
			}
		}
		if ($search_or_like) {
			$this->db->group_start();
			foreach ($search_or_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->or_like($skey, $sval);
				}
			}
			$this->db->group_end();
		}
		$this->db->where( array('brd_search' => 1));
		$this->db->group_by('board.brd_id');
		$qry = $this->db->get();
		$cnt = $qry->result_array();
		$result['total_rows'] = 0;
		if ($cnt) {
			foreach ($cnt as $key => $value) {
				if (element('brd_id', $value)) {
					$result['board_rows'][$value['brd_id']] = element('cnt', $value);
				}
			}
			if ($board_id) {
				$result['total_rows'] = $result['board_rows'][$board_id];
			} else {
				$result['total_rows'] = array_sum($result['board_rows']);
			}
		}

		return $result;
	}

	/**
	 * List 페이지 커스테마이징 함수
	 */
	public function get_search_count_by($limit = '', $offset = '', $where = '', $like = '', $category_id = 0, $orderby = '', $sfield = '', $skeyword = '', $sop = 'OR')
	{
		if ( ! in_array(strtolower($orderby), $this->allow_order)) {
			$orderby = 'cit_order asc';
		}

		$sop = (strtoupper($sop) === 'AND') ? 'AND' : 'OR';
		if (empty($sfield)) {
			$sfield = array('post_title', 'post_content');
		}

		$search_where = array();
		$search_like = array();
		$search_or_like = array();
		if ($sfield && is_array($sfield)) {
			foreach ($sfield as $skey => $sval) {
				$ssf = $sval;
				if ($skeyword && $ssf && in_array($ssf, $this->allow_search_field)) {
					if (in_array($ssf, $this->search_field_equal)) {
						$search_where[$ssf] = $skeyword;
					} else {
						$swordarray = explode(' ', $skeyword);
						foreach ($swordarray as $str) {
							if (empty($ssf)) {
								continue;
							}
							if ($sop === 'AND') {
								$search_like[] = array($ssf => $str);
							} else {
								$search_or_like[] = array($ssf => $str);
							}
						}
					}
				}
			}
		} else {
			$ssf = $sfield;
			if ($skeyword && $ssf && in_array($ssf, $this->allow_search_field)) {
				if (in_array($ssf, $this->search_field_equal)) {
					$search_where[$ssf] = $skeyword;
				} else {
					$swordarray = explode(' ', $skeyword);
					foreach ($swordarray as $str) {
						if (empty($ssf)) {
							continue;
						}
						if ($sop === 'AND') {
							$search_like[] = array($ssf => $str);
						} else {
							$search_or_like[] = array($ssf => $str);
						}
					}
				}
			}
		}
		$this->db->select('count(*) cnt, board.brd_id');
		$this->db->from('board');
		$this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');
		$this->db->join('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');
		$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
		$this->db->join('cmall_brand', 'cmall_brand.cbr_id = cmall_item.cbr_id', 'inner');
		if($skeyword){
			$this->db->join('crawl_tag', 'crawl_tag.brd_id = board.brd_id', 'inner');
			if (empty($category_id)) {
				$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
			}
			$this->db->join('cmall_category', 'cmall_category.cca_id = cmall_category_rel.cca_id', 'inner');
			

		}

		if ($where) {
			$this->db->where($where);
		}
		if ($search_where) {
			$this->db->where($search_where);
		}

		if ($category_id) {
			$this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
			$this->db->where('cca_id', $category_id);
		}
		
		if ($like) {
			$this->db->like($like);
		}
		if ($search_like) {
			foreach ($search_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->like($skey, $sval);
				}
			}
		}
		if ($search_or_like) {
			$this->db->group_start();
			foreach ($search_or_like as $item) {
				foreach ($item as $skey => $sval) {
					$this->db->or_like($skey, $sval);
				}
			}
			$this->db->group_end();
		}
		$this->db->where( array('brd_search' => 1));
		$this->db->group_by('board.brd_id');
		$qry = $this->db->get();
		$cnt = $qry->result_array();
		$result['total_rows'] = 0;
		if ($cnt) {
			foreach ($cnt as $key => $value) {
				if (element('brd_id', $value)) {
					$result['board_rows'][$value['brd_id']] = element('cnt', $value);
				}
			}
			if ($board_id) {
				$result['total_rows'] = $result['board_rows'][$board_id];
			} else {
				$result['total_rows'] = array_sum($result['board_rows']);
			}
		}

		return $result;
	}
}



