<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall item attr model class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

class Cmall_item_attr_model extends CB_Model
{

    /**
     * 테이블명
     */
    public $_table = 'cmall_item_attr';

    /**
     * 사용되는 테이블의 프라이머리키
     */
    public $primary_key = 'cia_id'; // 사용되는 테이블의 프라이머리키

    

    function __construct()
    {
        parent::__construct();
    }
}
