<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Crawl model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Crawl_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'crawl';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'crawl_id'; // 사용되는 테이블의 프라이머리키

    public $allow_order = array('crawl_datetime desc', 'crawl_datetime asc','crawl_id desc', 'crawl_id asc', 'crawl_id desc', 'crawl_id asc');

    function __construct()
    {
        parent::__construct();
    }


    /**
     * List 페이지 커스테마이징 함수
     */
    public function get_crawl_list($limit = '', $offset = '', $where = '', $category_id = '', $orderby = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        if ( ! in_array(strtolower($orderby), $this->allow_order)) {
            $orderby = 'crawl_id';
        }

        $sop = (strtoupper($sop) === 'AND') ? 'AND' : 'OR';
        if (empty($sfield)) {
            $sfield = array('crawl_title');
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

        $this->db->select('crawl.*');
        $this->db->from($this->_table);

        if ($where) {
            $this->db->where($where);
        }
        if ($search_where) {
            $this->db->where($search_where);
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
        if ($where) {
            $this->db->where($where);
        }
        if ($search_where) {
            $this->db->where($search_where);
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


    


    


    /**
     * List 페이지 커스테마이징 함수
     */
    public function get_search_list($limit = '', $offset = '', $where = '', $like = '', $board_id = 0, $orderby = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        if ( ! in_array(strtolower($orderby), $this->allow_order)) {
            $orderby = 'crawl_id,';
        }

        $sop = (strtoupper($sop) === 'AND') ? 'AND' : 'OR';
        if (empty($sfield)) {
            $sfield = array('crawl_title');
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

        $this->db->select('crawl.*, board.brd_key, board.brd_name, board.brd_mobile_name, board.brd_order, board.brd_search');
        $this->db->from('crawl');
        $this->db->join('board', 'crawl.brd_id = board.brd_id', 'inner');
        

        if ($where) {
            $this->db->where($where);
        }
        if ($search_where) {
            $this->db->where($search_where);
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
        $board_id = (int) $board_id;
        if ($board_id)  {
            $this->db->where( array('b.brd_id' => $board_id));
        }

        $this->db->order_by($orderby);
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        $qry = $this->db->get();
        $result['list'] = $qry->result_array();

        $this->db->select('count(*) cnt, board.brd_id');
        $this->db->from('crawl');
        $this->db->join('board', 'crawl.brd_id = board.brd_id', 'inner');

        if ($where) {
            $this->db->where($where);
        }
        if ($search_where) {
            $this->db->where($search_where);
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
