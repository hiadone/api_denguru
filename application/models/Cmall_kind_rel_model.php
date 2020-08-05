<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall kind rel model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Cmall_kind_rel_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'cmall_kind_rel';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'ckr_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function save_kind($cit_id = 0, $kind = '')
    {
        $cit_id = (int) $cit_id;
        if (empty($cit_id) OR $cit_id < 1) {
            return;
        }
        $deletewhere = array(
            'cit_id' => $cit_id,
        );
        $this->delete_where($deletewhere);

        if ($kind) {
            foreach ($attkindr as $cval) {
                $insertdata = array(
                    'cit_id' => $cit_id,
                    'ced_id' => $cval,
                );
                $this->insert($insertdata);
            }
        }
    }
}
