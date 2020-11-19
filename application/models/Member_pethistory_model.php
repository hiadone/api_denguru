<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Member_pethistory model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Member_pethistory_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'member_pethistory';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'pet_id'; // 사용되는 테이블의 프라이머리키

    public $search_sfield = '';

    function __construct()
    {
        parent::__construct();
    }



    public function get_admin_list($limit = '', $offset = '', $where = '', $like = '', $findex = '', $forder = '', $sfield = '', $skeyword = '', $sop = 'OR',$where_in = '')
    {

        $join = array();
        
        $select = 'member_pethistory.*,member.mem_nickname,member.mem_userid';
        $join[] = array('table' => 'member', 'on' => 'member_pethistory.mem_id = member.mem_id', 'type' => 'left');
        
        $result = $this->_get_list_common($select = '', $join, $limit, $offset, $where, $like, $findex, $forder, $sfield, $skeyword, $sop,$where_in);

        return $result;
    }
}
