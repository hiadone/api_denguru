<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall attr rel model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Cmall_attr_rel_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'cmall_attr_rel';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'car_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function save_attr($cit_id = 0, $attr = '')
    {
        $cit_id = (int) $cit_id;
        if (empty($cit_id) OR $cit_id < 1) {
            return;
        }
        $deletewhere = array(
            'cit_id' => $cit_id,
        );
        $this->delete_where($deletewhere);

        if ($attr) {
            foreach ($attr as $cval) {
                $insertdata = array(
                    'cit_id' => $cit_id,
                    'cat_id' => $cval,
                );
                $this->insert($insertdata);
            }
        }
    }
}
