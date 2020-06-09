<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall_review class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 컨텐츠몰 페이지에 관한 controller 입니다.
 */
class Cmall_review extends CB_Controller
{

    /**
     * 모델을 로딩합니다
     */
    protected $models = array();

    /**
     * 헬퍼를 로딩합니다
     */
    protected $helpers = array('form', 'array', 'cmall', 'dhtml_editor');

    function __construct()
    {
        parent::__construct();

        /**
         * 라이브러리를 로딩합니다
         */
        $this->load->library(array('pagination', 'querystring', 'accesslevel', 'cmalllib','review'));

        if ( ! $this->cbconfig->item('use_cmall')) {
            alert('이 웹사이트는 ' . html_escape($this->cbconfig->item('cmall_name')) . ' 기능을 사용하지 않습니다');
            return;
        }
    }


    

   
    protected function _reviewlist($cit_id = 0)
    {
        
        
        

        $view = array();
        $view['view'] = array();

        $this->load->model(array('Cmall_item_model', 'Cmall_review_model'));

        if($cit_id){
            $item = $this->Cmall_item_model->get_one($cit_id);
            if ( ! element('cit_id', $item)) {
                alert('이 상품은 현재 존재하지 않습니다',"",406);
            }
        }
        $mem_id = (int) $this->member->item('mem_id');

        $field = array(
            'cmall_review' => array('cre_id','cit_id','cre_title','cre_content','cre_content_html_type','mem_id','cre_score','cre_datetime','cre_like','cre_update_datetime'),
        );
        
        $select = get_selected($field);
        
        $this->Cmall_review_model->_select = $select;

        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->input->get('findex', null, 'cre_like');
        $forder = $this->input->get('forder', null, 'desc');
        $sfield = '';
        $skeyword = '';

        $per_page = 5;
        $offset = ($page - 1) * $per_page;

        $is_admin = $this->member->is_admin();

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        $where['cre_status'] = 1;
        if($cit_id) $where['cit_id'] = $cit_id;

        $thumb_width = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_review_mobile_thumb_width')
            : $this->cbconfig->item('cmall_product_review_thumb_width');
        $autolink = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('use_cmall_product_review_mobile_auto_url')
            : $this->cbconfig->item('use_cmall_product_review_auto_url');
        $popup = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_review_mobile_content_target_blank')
            : $this->cbconfig->item('cmall_product_review_content_target_blank');

        $result = $this->Cmall_review_model
            ->get_list($per_page, $offset, $where, '', $findex, $forder);
        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {
                
                $result['list'][$key]['display_content'] = display_html_content(
                    element('cre_content', $val),
                    element('cre_content_html_type', $val),
                    $thumb_width,
                    $autolink,
                    $popup
                );
                

                
                


                $result['list'][$key]['can_update'] = false;
                $result['list'][$key]['can_delete'] = false;
                if ($is_admin !== false
                    OR (element('mem_id', $val) && $mem_id === (int) element('mem_id', $val))) {
                    $result['list'][$key]['can_update'] = true;
                    $result['list'][$key]['can_delete'] = true;
                }
                $result['list'][$key] = $this->cmalllib->get_default_info(element('cit_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->member->get_default_info(element('mem_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->review->convert_default_info($result['list'][$key]);                   
                $result['list'][$key]['num'] = $list_num--;
                
            }
        }
        $view['view']['data'] = $result;
        

        /**
         * 페이지네이션을 생성합니다
         */
        $config['base_url'] = site_url('cmall_review/reviewlist/' . $cit_id) . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;

        if ( ! $this->input->get('page')) {
            $_GET['page'] = (string) $page;
        }

        $config['_attributes'] = 'onClick="cmall_review_page(\'' . $cit_id . '\', $(this).attr(\'data-ci-pagination-page\'));return false;"';
        if ($this->cbconfig->get_device_view_type() === 'mobile') {
            $config['num_links'] = 3;
        } else {
            $config['num_links'] = 5;
        }
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['next_link'] = $this->pagination->get_next_link();
        $view['view']['page'] = $page;


        

        /**
         * 레이아웃을 정의합니다
         */
        // $skindir = ($this->cbconfig->get_device_view_type() === 'mobile')
        //  ? $this->cbconfig->item('mobile_skin_cmall')
        //  : $this->cbconfig->item('skin_cmall');
        // if (empty($skindir)) {
        //  $skindir = ($this->cbconfig->get_device_view_type() === 'mobile')
        //      ? $this->cbconfig->item('mobile_skin_default')
        //      : $this->cbconfig->item('skin_default');
        // }
        // if (empty($skindir)) {
        //  $skindir = 'basic';
        // }
        // $skin = 'cmall/' . $skindir . '/review_list';

        // $view['view']['view_skin_url'] = site_url(VIEW_DIR . 'cmall/' . $skindir);

        

        
        // $this->layout = element('layout_skin_file', element('layout', $view));
        // $this->view = element('view_skin_file', element('layout', $view));

        
        

        // redirect(site_url('/board/b-a-1'));

        return $view['view'];

        
        
    }

    public function reviewlist_get($cit_id = 0)
    {   


         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_reviewlist';
        $this->load->event($eventname);
        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $view['view'] = $this->_reviewlist($cit_id);
        $this->data = $view['view'];
        

        return $this->response($this->data, parent::HTTP_OK);

        
        
    }

    
    protected function _itemreviewpost($cit_id = 0,$cre_id = 0)
    {
        
    
        

        $view = $data = array();
        $view['view'] = array();

    
        $this->load->model(array('Cmall_item_model', 'Cmall_review_model', 'Cmall_attr_model'));

        if (!$cit_id || $cit_id < 1) {
            show_404();
        }

        // if($cit_id){
            $item = $this->Cmall_item_model->get_one($cit_id,'cit_id');
            if ( ! element('cit_id', $item)) {
                alert('이 상품은 현재 존재하지 않습니다',"",406);
            }
        // }
        $mem_id = (int) $this->member->item('mem_id');
        $data['item'] = $this->cmalllib->get_default_info(element('cit_id', $item));
        $data['item']['item_attr'] = $this->Cmall_attr_model->get_attr(element('cit_id', $item));
        

        $data['item']['popularreview'] = $this->review->get_popular_item_review(element('cit_id',$item));

        $view['view']['data'] = $data;
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->input->get('findex', null, 'cre_like');
        $forder = $this->input->get('forder', null, 'desc');
        $sfield = '';
        $skeyword = '';

        $per_page = 5;
        $offset = ($page - 1) * $per_page;

        $is_admin = $this->member->is_admin();

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();

        

        if($this->input->get('sage')){
            if($this->input->get('sage') === 1)
                $where['pet_birthday > '] = cdate('Y-m-d',strtotime("-1 years"));
            if($this->input->get('sage') === 2){
                $where['pet_birthday >= '] = cdate('Y-m-d',strtotime("-1 years"));
                $where['pet_birthday <= '] = cdate('Y-m-d',strtotime("-6 years"));
            }
            if($this->input->get('sage') === 1)
                $where['pet_birthday < '] = cdate('Y-m-d',strtotime("-7 years"));
        }

        if($this->input->get('sform')){            
                $where['pet_form'] = $this->input->get('sform');
        }
        if($this->input->get('skind')){            
                $where['pet_kind'] = $this->input->get('skind');
        }
        if($this->input->get('sattr')){            
                $where['pet_attr'] = $this->input->get('sattr');
        }
        if($this->input->get('sscore')){            
                $where['cre_score'] = $this->input->get('sscore');
        }
        $where['cre_status'] = 1;
        // $where['cit_status'] = 1;
        if($cit_id) $where['cmall_review.cit_id'] = $cit_id;

        $thumb_width = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_review_mobile_thumb_width')
            : $this->cbconfig->item('cmall_product_review_thumb_width');
        $autolink = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('use_cmall_product_review_mobile_auto_url')
            : $this->cbconfig->item('use_cmall_product_review_auto_url');
        $popup = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_review_mobile_content_target_blank')
            : $this->cbconfig->item('cmall_product_review_content_target_blank');

        if(empty(!$cre_id)){
            $this->Cmall_review_model->order_by('cre_id='.$cre_id,'',false);   
        }

        $field = array(
            'cmall_review' => array('cre_id','cit_id','cre_title','cre_content','cre_content_html_type','mem_id','cre_score','cre_datetime','cre_like','cre_update_datetime'),
        );
        
        $select = get_selected($field);
        
        $this->Cmall_review_model->_select = $select;

        $result = $this->Cmall_review_model
            ->get_list($per_page, $offset, $where, '', $findex, $forder);

        // $result = $this->Cmall_attr_model->get_review_list($per_page, $offset, $where, '', $findex, $forder);

        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {
                

                
                $result['list'][$key]['display_content'] = display_html_content(
                    element('cre_content', $val),
                    element('cre_content_html_type', $val),
                    $thumb_width,
                    $autolink,
                    $popup
                );
                

                
                


                $result['list'][$key]['can_update'] = false;
                $result['list'][$key]['can_delete'] = false;
                if ($is_admin !== false
                    OR (element('mem_id', $val) && $mem_id === (int) element('mem_id', $val))) {
                    $result['list'][$key]['can_update'] = true;
                    $result['list'][$key]['can_delete'] = true;
                }
                $result['list'][$key] = $this->cmalllib->get_default_info(element('cit_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->member->get_default_info(element('mem_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->review->convert_default_info($result['list'][$key]);  
                                   
                $result['list'][$key]['num'] = $list_num--;
                
            }
        }
        $view['view']['data']['list'] = $result['list'];
        

        /**
         * 페이지네이션을 생성합니다
         */
        $config['base_url'] = site_url('cmall_review/reviewlist/' . $cit_id) . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;

        if ( ! $this->input->get('page')) {
            $_GET['page'] = (string) $page;
        }

        $config['_attributes'] = 'onClick="cmall_review_page(\'' . $cit_id . '\', $(this).attr(\'data-ci-pagination-page\'));return false;"';
        if ($this->cbconfig->get_device_view_type() === 'mobile') {
            $config['num_links'] = 3;
        } else {
            $config['num_links'] = 5;
        }
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['next_link'] = $this->pagination->get_next_link();
        $view['view']['page'] = $page;
        
        
        
        $view['view']['config']['pet_age'] = config_item('pet_age');
        $view['view']['config']['pet_form'] = config_item('pet_form');
        $view['view']['config']['pet_kind'] = array();
        $view['view']['config']['pet_attr'] = config_item('pet_attr');
        

        

        return $view['view'];

        
        
    }

    public function itemreviewpost_get($cit_id = 0,$cre_id = 0)
    {   

         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_reviewlist';
        $this->load->event($eventname);
        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $view['view'] = $this->_itemreviewpost($cit_id,$cre_id);

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


    protected function _userreviewpost($_mem_id='')
    {
        
    

        

        $view = $data = array();
        $view['view'] = array();

    
        

        $this->load->model(array('Cmall_item_model', 'Cmall_review_model', 'Reviewer_model','Member_pet_model'));
        if(empty($_mem_id)){
            show_404();
        }

       $data = $this->member->get_default_info($_mem_id);
        
       $view['view']['mem_info'] = $data;
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->input->get('findex', null, 'cre_like');
        $forder = $this->input->get('forder', null, 'desc');
        $sfield = '';
        $skeyword = '';

        $per_page = 5;
        $offset = ($page - 1) * $per_page;

        $is_admin = $this->member->is_admin();

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        $where['cre_status'] = 1;
        // if($cit_id) $where['cit_id'] = $cit_id;

        $thumb_width = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_review_mobile_thumb_width')
            : $this->cbconfig->item('cmall_product_review_thumb_width');
        $autolink = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('use_cmall_product_review_mobile_auto_url')
            : $this->cbconfig->item('use_cmall_product_review_auto_url');
        $popup = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_review_mobile_content_target_blank')
            : $this->cbconfig->item('cmall_product_review_content_target_blank');

        

        $field = array(
            'cmall_review' => array('cre_id','cit_id','cre_title','cre_content','cre_content_html_type','mem_id','cre_score','cre_datetime','cre_like','cre_update_datetime'),
        );
        
        $select = get_selected($field);
        
        $this->Cmall_review_model->_select = $select;


        $result = $this->Cmall_review_model
            ->get_list($per_page, $offset, $where, '', $findex, $forder);
        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                
                $result['list'][$key]['display_content'] = display_html_content(
                    element('cre_content', $val),
                    element('cre_content_html_type', $val),
                    $thumb_width,
                    $autolink,
                    $popup
                );
                

                
                


                $result['list'][$key]['can_update'] = false;
                $result['list'][$key]['can_delete'] = false;
                if ($is_admin !== false
                    OR (element('mem_id', $val) && $mem_id === (int) element('mem_id', $val))) {
                    $result['list'][$key]['can_update'] = true;
                    $result['list'][$key]['can_delete'] = true;
                }
                $result['list'][$key] = $this->cmalllib->get_default_info(element('cit_id', $val),$result['list'][$key]);                   
                // $result['list'][$key] = $this->member->get_default_info(element('mem_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->review->convert_default_info($result['list'][$key]);    
                
                $result['list'][$key]['num'] = $list_num--;
                
            }
        }
        $view['view']['data'] = $result;
        
        
        

        /**
         * 페이지네이션을 생성합니다
         */
        $config['base_url'] = site_url('cmall_review/userreviewpost/' . $_mem_id) . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;

        if ( ! $this->input->get('page')) {
            $_GET['page'] = (string) $page;
        }

        $config['_attributes'] = 'onClick="cmall_review_page(\'' . $_mem_id . '\', $(this).attr(\'data-ci-pagination-page\'));return false;"';
        if ($this->cbconfig->get_device_view_type() === 'mobile') {
            $config['num_links'] = 3;
        } else {
            $config['num_links'] = 5;
        }
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['next_link'] = $this->pagination->get_next_link();
        $view['view']['page'] = $page;
        
   
        return $view['view'];

        
        
    }

    public function userreviewpost_get($_mem_id = '')
    {
     
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_reviewlist';
        $this->load->event($eventname);

        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        $view['view']['event']['before'] = Events::trigger('before', $eventname);
        $view['view'] = $this->_userreviewpost($_mem_id);

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

    protected function _reviewwrite($cit_id = 0, $cre_id = 0)
    {

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();


        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');



        $this->load->model(array('Cmall_item_model', 'Cmall_review_model'));
        $primary_key = $this->Cmall_review_model->primary_key;
        /**
         * 프라이머리키에 숫자형이 입력되지 않으면 에러처리합니다
         */
        if ($cre_id) {
            $cre_id = (int) $cre_id;
            if (empty($cre_id) OR $cre_id < 1) {
                show_404();
            }
        }

        if ($cit_id) {
            $cit_id = (int) $cit_id;
            if (empty($cit_id) OR $cit_id < 1) {
                show_404();
            }
        }
        
        if ($cit_id) {
            $item = $this->Cmall_item_model->get_one($cit_id,'cit_id,cit_status');
            
            
            if ( ! element('cit_id', $item) )
                alert('이 상품은 현재 존재하지 않습니다',"",406);

            if(! element('cit_status', $item)) 
                alert('이 상품은 현재 판매하지 않습니다',"",406);
            

            $item = $this->cmalllib->get_default_info($cit_id);
        }
        /**
         * 수정 페이지일 경우 기존 데이터를 가져옵니다
         */
        $getdata = array();
        if ($cre_id) {
            $getdata = $this->Cmall_review_model->get_one($cre_id);
            if ( ! element('cre_id', $getdata)) {
                alert('이 리뷰는 현재 존재하지 않습니다',"",406);
            }
            $is_admin = $this->member->is_admin();
            if ($is_admin === false
                && (int) element('mem_id', $getdata) !== $mem_id) {
                alert_close('본인의 글 외에는 접근하실 수 없습니다');
            }
        }

        /**
         * 주문완료 후 사용후기 작성 가능한 경우
         **/
        if ( ! $this->cbconfig->item('use_cmall_product_review_anytime')) {
            $ordered = $this->cmalllib->is_ordered_item($mem_id, $cit_id);
            if (empty($ordered)) {
                alert_close('주문을 완료하신 후에 상품후기 작성이 가능합니다');
            }

        }

        /**
         * Validation 라이브러리를 가져옵니다
         */
        $this->load->library('form_validation');

        /**
         * 전송된 데이터의 유효성을 체크합니다
         */
        $config = array(
            array(
                'field' => 'cre_title',
                'label' => '제목',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'cre_content',
                'label' => '내용',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'cre_score',
                'label' => '평점',
                'rules' => 'trim|required|numeric|is_natural_no_zero|greater_than_equal_to[1]|less_than_equal_to[5]',
            ),
        );
        $this->form_validation->set_rules($config);


        /**
         * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
         * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
         */
        if ($this->form_validation->run() === false) {

            /**
             * primary key 정보를 저장합니다
             */
            // $view['view']['primary_key'] = $primary_key;
            

            $view['view']['msg'] = validation_errors();

            $view['view']['wishlist_url'] = base_url('cmall/wishlist');
            $view['view']['itemlists_url'] = base_url('cmall/itemlists');
            $view['view']['data']['review'] = $getdata;
            $view['view']['data']['item'] = $item;

            /**
             * primary key 정보를 저장합니다
             */
            $view['view']['primary_key'] = $primary_key;

            

            return $this->response($view['view'], parent::HTTP_OK);

            

        } else {
            
            /**
             * 유효성 검사를 통과한 경우입니다.
             * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
             */

            // // 이벤트가 존재하면 실행합니다
            // Events::trigger('formruntrue', $eventname);

            $content_type = $this->cbconfig->item('use_cmall_product_review_dhtml') ? 1 : 0;
            $updatedata = array(
                'cit_id' => $cit_id,
                'cre_title' => $this->input->post_put('cre_title', null, ''),
                'cre_content' => $this->input->post_put('cre_content', null, ''),
                'cre_content_html_type' => $content_type,
                'cre_score' => $this->input->post_put('cre_score', null, 0),
            );

            /**
             * 게시물을 수정하는 경우입니다
             */
            $param =& $this->querystring;
            $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;

            if ($cre_id) {

                // 이벤트가 존재하면 실행합니다
                // Events::trigger('before_update', $eventname);
                $updatedata['cre_update_datetime'] = cdate('Y-m-d H:i:s');
                $this->Cmall_review_model->update($cre_id, $updatedata);
                $cntresult = $this->cmalllib->update_review_count($cit_id);
                // $jresult = json_decode($cntresult, true);
                // $cnt = element('cit_review_count', $jresult);
                // echo '<script type="text/javascript">window.opener.view_cmall_review("viewitemreview", ' . $cit_id . ', ' . $page . ');window.opener.cmall_review_count_update(' . $cnt . ');</script>';
                

                $view['view'] = array('msg' => '정상적으로 수정되었습니다.');
                
                return $this->response($view['view'], 201);

            } else {

                // 이벤트가 존재하면 실행합니다
                // Events::trigger('before_insert', $eventname);

                /**
                 * 게시물을 새로 입력하는 경우입니다
                 */
                $updatedata['cre_datetime'] = cdate('Y-m-d H:i:s');
                $updatedata['mem_id'] = $mem_id;
                $updatedata['cre_ip'] = $this->input->ip_address();

                if ( ! $this->cbconfig->item('use_cmall_product_review_approve')) {
                    $updatedata['cre_status'] = 1;
                }

                $_cre_id = $this->Cmall_review_model->insert($updatedata);

                $this->cmalllib->review_alarm($_cre_id);

                $cntresult = $this->cmalllib->update_review_count($cit_id);
                // $jresult = json_decode($cntresult, true);
                // $cnt = element('cit_review_count', $jresult);
                if ($this->cbconfig->item('use_cmall_product_review_approve')) {
                    // echo '<script type="text/javascript">window.opener.view_cmall_review("viewitemreview", ' . $cit_id . ', ' . $page . ');window.opener.cmall_review_count_update(' . $cnt . ');</script>';
                    

                    $view['view'] = array('msg' => '정상적으로 입력되었습니다. 관리자의 승인 후 출력됩니다.');
                
                return $this->response($view['view'], 201);

                } else {
                    // echo '<script type="text/javascript">window.opener.view_cmall_review("viewitemreview", ' . $cit_id . ', ' . $page . ');window.opener.cmall_review_count_update(' . $cnt . ');</script>';
                    

                    $view['view'] = array('msg' => '정상적으로 입력되었습니다.');
                
                    return $this->response($view['view'], 201);
                }
            }
        }
        
    }

    public function reviewwrite_get($cit_id = 0, $cre_id = 0)
    {

         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_review_write';
        $this->load->event($eventname);

        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $view['view'] = $this->_reviewwrite($cit_id, $cre_id);

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
        
        
       
    }

    public function reviewwrite_post($cit_id = 0)
    {

         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_review_write';
        $this->load->event($eventname);

        $cit_id = (int) $cit_id;
        

        if (empty($cit_id) OR $cit_id < 1) {
            show_404();
        }

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        $view['view']['event']['before'] = Events::trigger('before', $eventname);
        $this->_reviewwrite($cit_id);



       
    }

    public function reviewwrite_put($cit_id = 0, $cre_id = 0)
    {   
         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_review_write';
        $this->load->event($eventname);
        $aaa= array();
        

        $cit_id = (int) $cit_id;        
        if (empty($cit_id) OR $cit_id < 1) {
            show_404();
        }

        $cre_id = (int) $cre_id;        
        if (empty($cre_id) OR $cre_id < 1) {
            show_404();
        }

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        $view['view']['event']['before'] = Events::trigger('before', $eventname);
        $this->_reviewwrite($cit_id, $cre_id);
    }

    
}

