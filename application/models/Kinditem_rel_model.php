<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall kind rel model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Kinditem_rel_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'kinditem_rel';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'kir_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }

    public function save_kinditem($kig_id = 0, $kinditem = '')
    {
        $kig_id = (int) $kig_id;
        if (empty($kig_id) OR $kig_id < 1) {
            return;
        }
        // $deletewhere = array(
        //     'kig_id' => $kig_id,
        // );
        // $this->delete_where($deletewhere);

        if ($kinditem) {
            foreach ($kinditem as $cval) {

                $where = array(
                        'kig_id' => $kig_id,
                        'cit_id' => $cval,
                    );

                if(!$this->count_by($where)){

                    $insertdata = array(
                        'kig_id' => $kig_id,
                        'cit_id' => $cval,
                    );
                    $this->insert($insertdata);
                }
            }
        }
    }

    public function delete_kinditem($kig_id = 0, $kinditem = '')
    {
        $kig_id = (int) $kig_id;
        if (empty($kig_id) OR $kig_id < 1) {
            return;
        }
        // $deletewhere = array(
        //     'kig_id' => $kig_id,
        // );
        

        if ($kinditem) {
            foreach ($kinditem as $cval) {
                $deletewhere = array(
                    'kig_id' => $kig_id,
                    'cit_id' => $cval,
                );
                $this->delete_where($deletewhere);
            }
        }
    }
}
