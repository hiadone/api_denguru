<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Event class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 관리자>페이지설정>팝업관리 controller 입니다.
 */
class Event extends CB_Controller
{

    /**
     * 관리자 페이지 상의 현재 디렉토리입니다
     * 페이지 이동시 필요한 정보입니다
     */
    

    /**
     * 모델을 로딩합니다
     */
    protected $models = array('Event','Event_group','Event_rel','Event_register_list');

    /**
     * 이 컨트롤러의 메인 모델 이름입니다
     */
    protected $modelname = 'Event_group_model';

    /**
     * 헬퍼를 로딩합니다
     */
    protected $helpers = array('form', 'array', 'dhtml_editor','cmall');

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
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        

        $findex = 'egr_order,egr_id';
        $forder = 'desc';
        $sfield = $this->input->get('sfield', null, '');
        $skeyword = $this->input->get('skeyword', null, '');

        $per_page = '';
        $offset = '';



        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $this->{$this->modelname}->allow_search_field = array('egr_id', 'egr_title', 'egr_content'); // 검색이 가능한 필드
        $this->{$this->modelname}->search_field_equal = array('egr_id'); // 검색중 like 가 아닌 = 검색을 하는 필드
        $this->{$this->modelname}->allow_order_field = array('egr_title', 'egr_id', 'egr_start_date', 'egr_end_date', 'egr_activated','egr_order,egr_id'); // 정렬이 가능한 필드

        $where = array();
        
        $where['egr_activated'] = '1';
        
        $field = array(
            'event_group' => array('egr_id','egr_start_date','egr_end_date','egr_title','egr_datetime','egr_image','egr_content','egr_type'),
            
        );
        
        $select = get_selected($field);
        
        $this->{$this->modelname}->_select = $select;

        // $result = $this->{$this->modelname}
        //     ->get_list($per_page, $offset, $where, '', $findex, $forder, $sfield, $skeyword);
        $result = $this->{$this->modelname}
            ->get_today_list();

        // $list_num = $result['total_rows'];
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                $result['list'][$key]['post_url'] = base_url('postact/event_link/'.element('egr_id', $val));

                $result['list'][$key]['display_datetime'] = display_datetime(
                    element('egr_datetime', $val),'full'
                );

                $result['list'][$key]['display_content'] = display_html_content(
                    element('egr_content', $val),
                    
                );
                
                $result['list'][$key]['egr_image_url'] = '';
                
                if (element('egr_image', $val)) {
                    
                    $result['list'][$key]['egr_image_url'] = cdn_url('eventgroup', element('egr_image', $val));
                    
                } 
                // else {
                //     $thumb_url = get_post_image_url(element('egr_content', $val));
                //     $result['list'][$key]['egr_image_url'] = $thumb_url
                //         ? $thumb_url
                //         : thumb_url('', '');
                // }
              

                if (empty($val['egr_start_date']) OR $val['egr_start_date'] === '0000-00-00') {
                    $result['list'][$key]['egr_start_date'] = '0000-00-00';


                }
                if (empty($val['egr_end_date']) OR $val['egr_end_date'] === '0000-00-00') {
                    $result['list'][$key]['egr_end_date'] = '0000-00-00';
                }

                
                // $result['list'][$key]['num'] = $list_num--;
            }
        }

        $view['view']['data'] = $result;

        /**
         * primary key 정보를 저장합니다
         */
        // $view['view']['primary_key'] = $this->{$this->modelname}->primary_key;

        /**
         * 페이지네이션을 생성합니다
         */
        
        // $config['total_rows'] = $result['total_rows'];
        // $config['per_page'] = $per_page;
        // $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        // $view['view']['page'] = $page;
        
        return $view['view'];
        
    }

    public function lists_get()
    {
        // 이벤트 라이브러리를 로딩합니다
        // $eventname = 'event_admin_page_event_lists';
        // $this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
        $view['view'] = $this->_lists();

        /**
         * 레이아웃을 정의합니다
         */
        $page_title = $this->cbconfig->item('site_meta_title_cmall');
        $meta_description = $this->cbconfig->item('site_meta_description_cmall');
        $meta_keywords = $this->cbconfig->item('site_meta_keywords_cmall');
        $meta_author = $this->cbconfig->item('site_meta_author_cmall');
        $page_name = $this->cbconfig->item('site_page_name_cmall');

        $searchconfig = array(
            '{컨텐츠몰명}',
        );
        $replaceconfig = array(
            $this->cbconfig->item('cmall_name'),
        );

        $page_title = str_replace($searchconfig, $replaceconfig, $page_title);
        $meta_description = str_replace($searchconfig, $replaceconfig, $meta_description);
        $meta_keywords = str_replace($searchconfig, $replaceconfig, $meta_keywords);
        $meta_author = str_replace($searchconfig, $replaceconfig, $meta_author);
        $page_name = str_replace($searchconfig, $replaceconfig, $page_name);

        $layoutconfig = array(
            'path' => 'cmall',
            'layout' => 'layout',
            'skin' => 'cmall',
            'layout_dir' => $this->cbconfig->item('layout_cmall'),
            'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_cmall'),
            'use_sidebar' => $this->cbconfig->item('sidebar_cmall'),
            'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_cmall'),
            'skin_dir' => $this->cbconfig->item('skin_cmall'),
            'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_cmall'),
            'page_title' => $page_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'meta_author' => $meta_author,
            'page_name' => $page_name,
        );
        // $view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        
        $this->data = $view['view'];    


        return $this->response($this->data, parent::HTTP_OK);
    }

    /**
     * 게시판 글쓰기 또는 수정 페이지를 가져오는 메소드입니다
     */
    public function _post($pid)
    {
        // 이벤트 라이브러리를 로딩합니다
        // $eventname = 'event_event_post';
        // $this->load->event($eventname);
        
        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        /**
         * 프라이머리키에 숫자형이 입력되지 않으면 에러처리합니다
         */
        // if ($pid) {
            $pid = (int) $pid;
            if (empty($pid) OR $pid < 1) {
                show_404();
            }
        // }
        // $primary_key = $this->{$this->modelname}->primary_key;

        /**
         * 수정 페이지일 경우 기존 데이터를 가져옵니다
         */
        // $getdata = array();
        // if ($pid) {
            $getdata = $this->{$this->modelname}->get_one($pid);
            
            $mem_id = (int) $this->member->item('mem_id');

            $getdata2 = $this->Event_register_list_model->get_one('','',array('egr_id' => $pid,'erl_status' => 1,'mem_id' => $mem_id));

            $getdata['erl_status'] = element('erl_status',$getdata2,0);
            $getdata['event_registr_url'] = site_url('postact/event_registr/' .$pid.'/'.$mem_id );

            $getdata['display_datetime'] = display_datetime(
                element('egr_datetime', $getdata),'full'
            );

            $getdata['display_content'] = display_html_content(
                element('egr_content', $getdata),
                
            );
            
            $getdata['egr_image_url'] = '';
            
            if (element('egr_image', $getdata)) {
                
                $getdata['egr_image_url'] = cdn_url('eventgroup', element('egr_image', $getdata));
                
            } 

            $getdata['egr_detail_image_url'] = '';
            
            if (element('egr_detail_image', $getdata)) {
                
                $getdata['egr_detail_image_url'] = cdn_url('eventgroup', element('egr_detail_image', $getdata));
                
            } 
            // else {
            //     $thumb_url = get_post_image_url(element('egr_content', $val));
            //     $result['list'][$key]['egr_image_url'] = $thumb_url
            //         ? $thumb_url
            //         : thumb_url('', '');
            // }
            

            if (empty($getdata['egr_start_date']) OR $getdata['egr_start_date'] === '0000-00-00') {
                $getdata['egr_start_date'] = '0000-00-00';


            }
            if (empty($getdata['egr_end_date']) OR $getdata['egr_end_date'] === '0000-00-00') {
                $getdata['egr_end_date'] = '0000-00-00';
            }
        // }


        /**
         * Validation 라이브러리를 가져옵니다
         */
        


        /**
         * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
         * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
         */
    

            

            /**
             * primary key 정보를 저장합니다
             */
            // $view['view']['primary_key'] = $primary_key;

            // 이벤트가 존재하면 실행합니다
            // $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

            $view['view']['list_url'] = base_url('/event/lists');
                        
            
            $where = array(
                'egr_id' => $pid,
            );

            $result = $this->Event_model->get_today_list($pid);


            if (element('list', $result)) {
                foreach (element('list', $result) as $key => $val) {

                    $result['list'][$key]['display_datetime'] = display_datetime(
                        element('eve_datetime', $val),'full'
                    );

                    $result['list'][$key]['display_content'] = display_html_content(
                        element('eve_content', $val),
                        
                    );
                    
                    $result['list'][$key]['eve_image_url'] = '';
                    
                    if (element('eve_image', $val)) {
                        
                        $result['list'][$key]['eve_image_url'] = cdn_url('event', element('eve_image', $val));
                        
                    } 
                    // else {
                    //     $thumb_url = get_post_image_url(element('egr_content', $val));
                    //     $result['list'][$key]['egr_image_url'] = $thumb_url
                    //         ? $thumb_url
                    //         : thumb_url('', '');
                    // }
                  

                    if (empty($val['eve_start_date']) OR $val['eve_start_date'] === '0000-00-00') {
                        $result['list'][$key]['eve_start_date'] = '0000-00-00';


                    }
                    if (empty($val['eve_end_date']) OR $val['eve_end_date'] === '0000-00-00') {
                        $result['list'][$key]['eve_end_date'] = '0000-00-00';
                    }

                    // $event_rel = $this->Event_model->get_event(element('eve_id',$val));
                    

                    if(element('eve_id',$val)){

                        // $eveval_id =array();
                        // foreach($event_rel as $eveval){
                        //     array_push($eveval_id,element('cit_id',$eveval));
                        // }

                        // if(!empty($eveval_id)){

                            $config = array(
                                'per_page' => 99999,
                                'findex' => '(0.1/evr_order) DESC,evr_id DESC',
                                'set_join' => array('event_rel','cmall_item.cit_id = event_rel.cit_id','inner'),
                                );
                            
                            $this->load->library('cmalllib');
                            $_itemlists = $this->cmalllib->_itemlists('','',array('eve_id' =>element('eve_id',$val)),$config);
                            $result['list'][$key]['itemlists'] = element('list',$_itemlists);
                            
                        // }
                    }


                    // $result['list'][$key]['num'] = $list_num--;
                }
            }

            
            
            $view['view']['data'] = $getdata;            
            $view['view']['data']['secionlist'] = $result['list'];

            
            $view['view']['next_post'] = '';
            $view['view']['prev_post'] = '';
           
            
            $use_prev_next = true;
            $param =& $this->querystring;
           

            

            
            if ($use_prev_next) {

                $where = array();
                $where['egr_activated'] =1;

                $view['view']['next_post'] = $next_post
                    = $this->{$this->modelname}
                    ->get_prev_next_post(
                        element('egr_id', $getdata),
                        '',
                        'next',
                        $where
                    );

                if (element('egr_id', $next_post)) {
                    $view['view']['next_post']['url'] = base_url('postact/event_link/'. element('egr_id', $next_post)) . '?' . $param->output();
                }

                $view['view']['prev_post'] = $prev_post
                    = $this->{$this->modelname}
                    ->get_prev_next_post(
                        element('egr_id', $getdata),
                        '',
                        'prev',
                        $where                        
                    );
                if (element('egr_id', $prev_post)) {
                    $view['view']['prev_post']['url'] = base_url('postact/event_link/'. element('egr_id', $prev_post)) . '?' . $param->output();
                }
            }
            
            
            
            
            return $view['view'];
            

        
    }

    /**
     * 게시판 글쓰기 또는 수정 페이지를 가져오는 메소드입니다
     */
    public function post_get($pid = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        // $eventname = 'event_event_post';
        // $this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $view['view'] = $this->_post($pid);
        /**
         * 레이아웃을 정의합니다
         */
        $page_title = $this->cbconfig->item('site_meta_title_cmall');
        $meta_description = $this->cbconfig->item('site_meta_description_cmall');
        $meta_keywords = $this->cbconfig->item('site_meta_keywords_cmall');
        $meta_author = $this->cbconfig->item('site_meta_author_cmall');
        $page_name = $this->cbconfig->item('site_page_name_cmall');

        $searchconfig = array(
            '{컨텐츠몰명}',
        );
        $replaceconfig = array(
            $this->cbconfig->item('cmall_name'),
        );

        $page_title = str_replace($searchconfig, $replaceconfig, $page_title);
        $meta_description = str_replace($searchconfig, $replaceconfig, $meta_description);
        $meta_keywords = str_replace($searchconfig, $replaceconfig, $meta_keywords);
        $meta_author = str_replace($searchconfig, $replaceconfig, $meta_author);
        $page_name = str_replace($searchconfig, $replaceconfig, $page_name);

        $layoutconfig = array(
            'path' => 'cmall',
            'layout' => 'layout',
            'skin' => 'cmall',
            'layout_dir' => $this->cbconfig->item('layout_cmall'),
            'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_cmall'),
            'use_sidebar' => $this->cbconfig->item('sidebar_cmall'),
            'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_cmall'),
            'skin_dir' => $this->cbconfig->item('skin_cmall'),
            'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_cmall'),
            'page_title' => $page_title,
            'meta_description' => $meta_description,
            'meta_keywords' => $meta_keywords,
            'meta_author' => $meta_author,
            'page_name' => $page_name,
        );
        $view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        
        $this->data = $view['view'];    


        return $this->response($this->data, parent::HTTP_OK);
        
    }

    
}
