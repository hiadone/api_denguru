<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notice class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 관리자>페이지설정>팝업관리 controller 입니다.
 */
class Notice extends CB_Controller
{

    /**
     * 관리자 페이지 상의 현재 디렉토리입니다
     * 페이지 이동시 필요한 정보입니다
     */
    

    /**
     * 모델을 로딩합니다
     */
    protected $models = array('Notice');

    /**
     * 이 컨트롤러의 메인 모델 이름입니다
     */
    protected $modelname = 'Notice_model';

    /**
     * 헬퍼를 로딩합니다
     */
    protected $helpers = array('form', 'array', 'dhtml_editor');

    function __construct()
    {
        parent::__construct();

        /**
         * 라이브러리를 로딩합니다
         */
        $this->load->library(array('pagination', 'querystring'));
    }

    /**
     * 목록을 가져오는 메소드입니다
     */
    public function _lists()
    {
        // 이벤트 라이브러리를 로딩합니다
        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        

        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        
        $findex = '(CASE WHEN noti_order=0 THEN -999 ELSE noti_order END),noti_id';
        $forder = 'desc';
        $sfield = $this->input->get('sfield', null, '');
        $skeyword = $this->input->get('skeyword', null, '');

        $per_page = 1;
        $offset = ($page - 1) * $per_page;

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $this->{$this->modelname}->allow_search_field = array('noti_id', 'noti_title', 'noti_content'); // 검색이 가능한 필드
        $this->{$this->modelname}->search_field_equal = array('noti_id'); // 검색중 like 가 아닌 = 검색을 하는 필드
        $this->{$this->modelname}->allow_order_field = array('noti_title', 'noti_id', 'noti_start_date', 'noti_end_date', 'noti_activated','(CASE WHEN noti_order=0 THEN -999 ELSE noti_order END),noti_id'); // 정렬이 가능한 필드

        $where = array();
        
        $where['noti_activated'] = '1';
        
        // $field = array(
        //     'event' => array('noti_id','eve_start_date','eve_end_date','eve_title','eve_datetime','eve_image','eve_content','eve_content_html_type','eve_width'),
            
        // );
        
        // $select = get_selected($field);
        
        // $this->{$this->modelname}->select = $select;

        $result = $this->{$this->modelname}
            ->get_today_list();
        // $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                $result['list'][$key]['post_url'] = base_url('notice/post/'.element('noti_id', $val)); 

                $result['list'][$key]['display_content'] = display_html_content(
                    element('noti_content', $val),
                    element('noti_content_html_type', $val),
                    
                );

                $result['list'][$key]['display_datetime'] = display_datetime(
                    element('noti_datetime', $val),'full'
                );
                if (empty($val['noti_start_date']) OR $val['noti_start_date'] === '0000-00-00') {
                    $result['list'][$key]['noti_start_date'] = '미지정';
                }
                if (empty($val['noti_end_date']) OR $val['noti_end_date'] === '0000-00-00') {
                    $result['list'][$key]['noti_end_date'] = '미지정';
                }
                // $result['list'][$key]['num'] = $list_num--;
            }
        }

        $view['view']['data'] = $result;

        /**
         * primary key 정보를 저장합니다
         */
        $view['view']['primary_key'] = $this->{$this->modelname}->primary_key;

        /**
         * 페이지네이션을 생성합니다
         */
        // $config['base_url'] = site_url('/notice/lists') . '?' . $param->replace('page');
        // $config['total_rows'] = $result['total_rows'];
        // $config['per_page'] = $per_page;
        // $this->pagination->initialize($config);
        // // $view['view']['paging'] = $this->pagination->create_links();
        // $view['view']['next_link'] = $this->pagination->get_next_link();
        // $view['view']['page'] = $page;

        return $view['view'];

        
    }


    public function lists_get()
    {
        // 이벤트 라이브러리를 로딩합니다
        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        
        $view['view'] = $this->_lists();




        /**
         * 어드민 레이아웃을 정의합니다
         */
        $layout_dir = 'mobile';
        $mobile_layout_dir = 'mobile';
        $use_sidebar = $this->cbconfig->item('sidebar_board');
        $use_mobile_sidebar = $this->cbconfig->item('mobile_sidebar_board');
        $skin_dir = $this->cbconfig->item('skin_board');
        $mobile_skin_dir = $this->cbconfig->item('mobile_skin_board');


        $layoutconfig = array(
            'path' => 'notice',
            'layout' => 'layout',
            'skin' => 'list',
            'layout_dir' => $layout_dir,
            'mobile_layout_dir' => $mobile_layout_dir,
            'use_sidebar' => $use_sidebar,
            'use_mobile_sidebar' => $use_mobile_sidebar,
            'skin_dir' => $skin_dir,
            'mobile_skin_dir' => $mobile_skin_dir,
            'page_title' => '공지사항',
            'page_name' => '공지사항',
            'page_url' => '/event/lists',
        );

        // $view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        $this->data = $view['view'];    

        
        return $this->response($this->data, parent::HTTP_OK);
    }
    /**
     * 게시판 글쓰기 또는 수정 페이지를 가져오는 메소드입니다
     */
    protected function _post($pid = 0)
    {
        

        $view = array();
        $view['view'] = array();

        

        /**
         * 프라이머리키에 숫자형이 입력되지 않으면 에러처리합니다
         */
        if ($pid) {
            $pid = (int) $pid;
            if (empty($pid) OR $pid < 1) {
                show_404();
            }
        }
        $primary_key = $this->{$this->modelname}->primary_key;

        /**
         * 수정 페이지일 경우 기존 데이터를 가져옵니다
         */
        $getdata = array();
        if ($pid) {
            $getdata = $this->{$this->modelname}->get_one($pid);
        }

        /**
         * Validation 라이브러리를 가져옵니다
         */
        


        /**
         * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
         * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
         */
        

            

            if ($pid) {
                if (empty($getdata['noti_start_date']) OR $getdata['noti_start_date'] === '0000-00-00') {
                    $getdata['noti_start_date'] = '';
                }
                if (empty($getdata['noti_end_date']) OR $getdata['noti_end_date'] === '0000-00-00') {
                    $getdata['noti_end_date'] = '';
                }
                $view['view']['data'] = $getdata;
            }

            /**
             * primary key 정보를 저장합니다
             */
            $view['view']['primary_key'] = $primary_key;

            

            $view['view']['list_url'] = base_url('/notice/lists');
            


            

            $view['view']['data']['display_content'] = display_html_content(
                        element('noti_content', $getdata),
                        element('noti_content_html_type', $getdata)
                    );

            
            
            $view['view']['next_post'] = '';
            $view['view']['prev_post'] = '';
           
            
            $use_prev_next = true;
            $param =& $this->querystring;
           
            
            

            
            if ($use_prev_next) {
                $where = array();
                $where['noti_activated'] =1;

                $view['view']['next_post'] = $next_post
                    = $this->{$this->modelname}
                    ->get_prev_next_post(
                        element('noti_id', $getdata),
                        '',
                        'next',
                        $where
                    );

                if (element('noti_id', $next_post)) {
                    $view['view']['next_post']['url'] = base_url('notice/post/'.element('noti_id', $next_post)) . '?' . $param->output();
                }

                $view['view']['prev_post'] = $prev_post
                    = $this->{$this->modelname}
                    ->get_prev_next_post(
                        element('noti_id', $getdata),
                        '',
                        'prev',
                        $where                        
                    );
                if (element('noti_id', $prev_post)) {
                    $view['view']['prev_post']['url'] = base_url('notice/post/'.element('noti_id', $prev_post)) . '?' . $param->output();
                }
            }
            

        return $view['view'];
        
    }

    public function post_get($pid = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        
        $view['view'] = $this->_post($pid);
        
            
        $layout_dir = 'mobile';
        $mobile_layout_dir = 'mobile';
        $use_sidebar = $this->cbconfig->item('sidebar_board');
        $use_mobile_sidebar = $this->cbconfig->item('mobile_sidebar_board');
        $skin_dir = $this->cbconfig->item('skin_board');
        $mobile_skin_dir = $this->cbconfig->item('mobile_skin_board');


        $layoutconfig = array(
            'path' => 'notice',
            'layout' => 'layout',
            'skin' => 'post',
            'layout_dir' => $layout_dir,
            'mobile_layout_dir' => $mobile_layout_dir,
            'use_sidebar' => $use_sidebar,
            'use_mobile_sidebar' => $use_mobile_sidebar,
            'skin_dir' => $skin_dir,
            'mobile_skin_dir' => $mobile_skin_dir,
            'page_title' => '공지사항',
            'page_name' => '공지사항',
            'page_url' => '/event/lists',
        );

        
        $view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        
        $this->data = $view['view'];    


        return $this->response($this->data, parent::HTTP_OK);
        
    }
}
