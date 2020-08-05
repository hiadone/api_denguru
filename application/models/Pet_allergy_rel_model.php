<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall attr rel model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Pet_allergy_rel_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'pet_allergy_rel';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'par_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function save_attr($pet_id = 0, $attr = '')
    {
        $pet_id = (int) $pet_id;
        if (empty($pet_id) OR $pet_id < 1) {
            return;
        }
        $deletewhere = array(
            'pet_id' => $pet_id,
        );
        $this->delete_where($deletewhere);

        if ($attr) {
            foreach ($attr as $cval) {
                $insertdata = array(
                    'pet_id' => $pet_id,
                    'pag_id' => $cval,
                );
                $this->insert($insertdata);
            }
        }
    }
}
