<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reviewer model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Reviewer_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'reviewer';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'rve_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function get_reviewering_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'reviewer.*, member.mem_id, member.mem_userid, member.mem_level, member.mem_nickname,
            member.mem_is_admin, member.mem_icon, member.mem_lastlogin_datetime';
        $join[] = array('table' => 'member', 'on' => 'reviewer.target_mem_id = member.mem_id', 'type' => 'left');

        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
        return $result;
    }


    public function get_reviewered_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR')
    {
        $select = 'reviewer.*, member.mem_id, member.mem_userid, member.mem_level, member.mem_nickname,
            member.mem_is_admin, member.mem_icon, member.mem_lastlogin_datetime';
        $join[] = array('table' => 'member', 'on' => 'reviewer.mem_id = member.mem_id', 'type' => 'left');

        $result = $this->_get_list_common($select, $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop);
        return $result;
    }
}
