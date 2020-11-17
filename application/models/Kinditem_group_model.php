<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Faq group model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Kinditem_group_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'kinditem_group';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'kig_id'; // 사용되는 테이블의 프라이머리키

    public $cache_prefix = 'kinditem_group/kinditem-group-model-get-'; // 캐시 사용시 프리픽스

    public $cache_time = 86400; // 캐시 저장시간

    function __construct()
    {
        parent::__construct();

        check_cache_dir('kinditem_group');
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

    public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {

        $select = 'kinditem_group.*,cmall_kind.*,sum(IF(cb_kinditem_rel.kir_id, 1, 0)) as kinditem_count';
        $join[] = array('table' => 'cmall_kind', 'on' => 'cmall_kind.ckd_id = kinditem_group.ckd_id', 'type' => 'inner');
        $join[] = array('table' => 'cb_kinditem_rel', 'on' => 'cb_kinditem_rel.kig_id = kinditem_group.kig_id', 'type' => 'left');

        

        $forder = (strtoupper($forder) === 'ASC') ? 'ASC' : 'DESC';
        $sop = (strtoupper($sop) === 'AND') ? 'AND' : 'OR';

        $count_by_where = array();
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
                        $swordarray = explode('abcdef', $skeyword);
                        
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
                    $swordarray = explode('abcdef', $skeyword);
                    
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

        if ($select) {
            $this->db->select($select);
        }
        $this->db->from($this->_table);
        if ( ! empty($join['table']) && ! empty($join['on'])) {
            if (empty($join['type'])) {
                $join['type'] = 'left';
            }
            $this->db->join($join['table'], $join['on'], $join['type']);
        } elseif (is_array($join)) {
            foreach ($join as $jkey => $jval) {
                if ( ! empty($jval['table']) && ! empty($jval['on'])) {
                    if (empty($jval['type'])) {
                        $jval['type'] = 'left';
                    }
                    $this->db->join($jval['table'], $jval['on'], $jval['type']);
                }
            }
        }

        if ($where) {
            $this->db->where($where);
        }
        
        if($this->or_where){
            $this->db->group_start();
                    
            foreach ($this->or_where as $skey => $sval) {
                $this->db->or_where($skey, $sval);
            }
            
            $this->db->group_end();
        }

        if ($this->where_in) {
            foreach($this->where_in as $wval){
                $this->db->where_in(key($wval),$wval[key($wval)]);  
            }
            
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
        if ($count_by_where) {
            $this->db->where($count_by_where);
        }

        $this->db->group_by('kinditem_group.ckd_id');

        $this->db->order_by($findex, $forder);
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        $qry = $this->db->get();
        $result['list'] = $qry->result_array();


        

        return $result;

       
    }

    public function get_item_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {

        $select = 'kinditem_group.*,cmall_item.*,kinditem_rel.*';
        $join[] = array('table' => 'kinditem_rel', 'on' => 'kinditem_rel.kig_id = kinditem_group.kig_id', 'type' => 'inner');
        $join[] = array('table' => 'cmall_item', 'on' => 'cmall_item.cit_id = kinditem_rel.cit_id', 'type' => 'inner');
        

        

        $forder = (strtoupper($forder) === 'ASC') ? 'ASC' : 'DESC';
        $sop = (strtoupper($sop) === 'AND') ? 'AND' : 'OR';

        $count_by_where = array();
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
                        $swordarray = explode('abcdef', $skeyword);
                        
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
                    $swordarray = explode('abcdef', $skeyword);
                    
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

        if ($select) {
            $this->db->select($select);
        }
        $this->db->from($this->_table);
        if ( ! empty($join['table']) && ! empty($join['on'])) {
            if (empty($join['type'])) {
                $join['type'] = 'left';
            }
            $this->db->join($join['table'], $join['on'], $join['type']);
        } elseif (is_array($join)) {
            foreach ($join as $jkey => $jval) {
                if ( ! empty($jval['table']) && ! empty($jval['on'])) {
                    if (empty($jval['type'])) {
                        $jval['type'] = 'left';
                    }
                    $this->db->join($jval['table'], $jval['on'], $jval['type']);
                }
            }
        }

        if ($where) {
            $this->db->where($where);
        }
        
        if($this->or_where){
            $this->db->group_start();
                    
            foreach ($this->or_where as $skey => $sval) {
                $this->db->or_where($skey, $sval);
            }
            
            $this->db->group_end();
        }

        if ($this->where_in) {
            foreach($this->where_in as $wval){
                $this->db->where_in(key($wval),$wval[key($wval)]);  
            }
            
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
        if ($count_by_where) {
            $this->db->where($count_by_where);
        }

        

        $this->db->order_by($findex, $forder);
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        $qry = $this->db->get();
        $result['list'] = $qry->result_array();


        $this->db->select('count(*) as rownum');

        $this->db->from($this->_table);
        if ( ! empty($join['table']) && ! empty($join['on'])) {
            if (empty($join['type'])) {
                $join['type'] = 'left';
            }
            $this->db->join($join['table'], $join['on'], $join['type']);
        } elseif (is_array($join)) {
            foreach ($join as $jkey => $jval) {
                if ( ! empty($jval['table']) && ! empty($jval['on'])) {
                    if (empty($jval['type'])) {
                        $jval['type'] = 'left';
                    }
                    $this->db->join($jval['table'], $jval['on'], $jval['type']);
                }
            }
        }
        
        
        
        if ($where) {
            $this->db->where($where);
        }
        if ($search_where) {
            $this->db->where($search_where);
        }

        if($this->or_where){
            $this->db->group_start();
                    
            foreach ($this->or_where as $skey => $sval) {
                $this->db->or_where($skey, $sval);
            }
            
            $this->db->group_end();
        }

        if ($this->where_in) {
            foreach($this->where_in as $wval){
                $this->db->where_in(key($wval),$wval[key($wval)]);  
            }
            
        }
        if ($this->set_where) {         
            foreach ($this->set_where as $skey => $sval) {
                $this->db->where($skey, $sval,false);               
            }
        }
        
        // if ($category_id) {
        //  $this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
        //  $this->db->where('cca_id', $category_id);
        // }
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

        return $result;

       
    }

    public function get_kinditme($kig_id = 0)
    {
        $kig_id = (int) $kig_id;
        if (empty($kig_id) OR $kig_id < 1) {
            return;
        }

        
        

        $this->db->select('kinditem_rel.*');
        $this->db->join('kinditem_rel', 'kinditem_group.kig_id = kinditem_rel.kig_id', 'inner');
        $this->db->where(array('kinditem_rel.kig_id' => $kig_id));
        $qry = $this->db->get($this->_table);
        $result = $qry->result_array();

        return $result;
    }
}


