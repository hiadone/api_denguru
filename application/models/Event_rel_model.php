<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall kind rel model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Event_rel_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'event_rel';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'egr_id'; // 사용되는 테이블의 프라이머리키

    function __construct()
    {
        parent::__construct();
    }


    public function save_event($eve_id = 0, $event = '')
    {
        $eve_id = (int) $eve_id;
        if (empty($eve_id) OR $eve_id < 1) {
            return;
        }
        // $deletewhere = array(
        //     'eve_id' => $eve_id,
        // );
        // $this->delete_where($deletewhere);

        if ($event) {
            foreach ($event as $cval) {
                $insertdata = array(
                    'eve_id' => $eve_id,
                    'cit_id' => $cval,
                );
                $this->insert($insertdata);
            }
        }
    }

    public function delete_event($eve_id = 0, $event = '')
    {
        $eve_id = (int) $eve_id;
        if (empty($eve_id) OR $eve_id < 1) {
            return;
        }
        // $deletewhere = array(
        //     'eve_id' => $eve_id,
        // );
        

        if ($event) {
            foreach ($event as $cval) {
                $deletewhere = array(
                    'eve_id' => $eve_id,
                    'cit_id' => $cval,
                );
                $this->delete_where($deletewhere);
            }
        }
    }
}
