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
    protected $models = array('Cmall_item', 'Cmall_review','Review_file');

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
        $this->load->library(array('pagination', 'querystring', 'accesslevel', 'cmalllib','denguruapi'));

        if ( ! $this->cbconfig->item('use_cmall')) {
            alert('이 웹사이트는 ' . html_escape($this->cbconfig->item('cmall_name')) . ' 기능을 사용하지 않습니다');
            return;
        }
    }


    

   
    protected function _reviewlist($cit_id = 0)
    {
        
        
        

        $view = array();
        $view['view'] = array();

        $view['view']['reviewwrite_url'] = base_url('cmall_review/reviewwrite');

        

        if($cit_id){
            $item = $this->Cmall_item_model->get_one($cit_id);
            if ( ! element('cit_id', $item)) {
                alert('이 상품은 현재 존재하지 않습니다',"",406);
            }
        }
        $mem_id = (int) $this->member->item('mem_id');
        // $review_flag = 0; //모든 리뷰를 볼수 있는 권한이 있는가

        // $per_page = 1;
        // if($mem_id){
        //     $reviewwhere = array('mem_id' => $mem_id);
            
        //     if($this->Cmall_review_model->count_by($reviewwhere)) {
        //         $review_flag = 1;
        //         $per_page = 5;
        //     }
            
        // }

        // $view['view']['review_flag'] = $review_flag;
        // $field = array(
        //     'cmall_review' => array('cre_id','cit_id','cre_good','cre_bad','cre_tip','cre_file_1','cre_file_2','cre_file_3','cre_file_4','cre_file_5','cre_file_6','cre_file_7','cre_file_8','cre_file_9','cre_file_10','mem_id','cre_score','cre_datetime','cre_like','cre_update_datetime'),
        // );
        
        // $select = get_selected($field);
        
        // $this->Cmall_review_model->_select = $select;

        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->input->get('findex', null, 'cre_like');
        $findex = 'cre_type1, cre_like,cre_id';
        $forder = $this->input->get('forder', null, 'desc');
        $sfield = '';
        $skeyword = '';
        // $per_page = 5;
        $per_page = get_listnum(5);
        $offset = ($page - 1) * $per_page;

        $is_admin = $this->member->is_admin();

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        $where['cre_status'] = 1;
        if($cit_id) $where['cb_cmall_review.cit_id'] = $cit_id;

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
            ->get_admin_list($per_page, $offset, $where, '', $findex, $forder);


        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {
                
                // $result['list'][$key]['display_content'] = display_html_content(
                //     element('cre_content', $val),
                //     element('cre_content_html_type', $val),
                //     $thumb_width,
                //     $autolink,
                //     $popup
                // );
                

                
                $result['list'][$key]['display_datetime'] = display_datetime(
                    element('cre_datetime', $val),
                    'sns',
                );


                $result['list'][$key]['can_update'] = false;
                $result['list'][$key]['can_delete'] = false;
                if ($is_admin !== false
                    OR (element('mem_id', $val) && $mem_id === (int) element('mem_id', $val))) {
                    $result['list'][$key]['can_update'] = true;
                    $result['list'][$key]['can_delete'] = true;
                }
                $result['list'][$key] = $this->denguruapi->get_cit_info(element('cit_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->denguruapi->get_mem_info(element('mem_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->denguruapi->convert_review_info($result['list'][$key]);    

                

                $result['list'][$key]['num'] = $list_num--;

                // if(!$review_flag){
                //     break;
                // }
                

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
        // $this->load->event($eventname);
        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $view['view'] = $this->_reviewlist($cit_id);
        $this->data = $view['view'];
        

        return $this->response($this->data, parent::HTTP_OK);

        
        
    }

    
    protected function _itemreviewpost($cit_id = 0,$cre_id = 0,$config)
    {
        
    
        
        $sattr = element('sattr', $config) ? element('sattr', $config) : array();
        $skind = element('skind', $config) ? element('skind', $config) : 0;
        $sform = element('sform', $config) ? element('sform', $config) : array();
        $sscore = element('sscore', $config) ? element('sscore', $config) : array();
        $sage = element('sage', $config) ? element('sage', $config) : array();
        $sort = element('sort', $config) ? element('sort', $config) : 'cre_like';
        $is_mypet_match = element('is_mypet_match', $config) ? element('is_mypet_match', $config) : 0;

        $view = $data = array();
        $view['view'] = array();

        $view['view']['reviewmodify_url'] = base_url('cmall_review/reviewwrite/'.$cit_id);


    
        $this->load->model(array('Cmall_item_model', 'Cmall_review_model', 'Cmall_attr_model','Pet_attr_model','Cmall_kind_model'));

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
        $data['item'] = $this->denguruapi->get_cit_info(element('cit_id', $item));
        $data['item']['item_attr'] = $this->Cmall_attr_model->get_attr(element('cit_id', $item));
        

        $data['item']['popularreview'] = $this->denguruapi->get_popular_item_review(element('cit_id',$item));

        $review_flag = 0; //모든 리뷰를 볼수 있는 권한이 있는가

        $per_page = 2;
        if($mem_id){
            $reviewwhere = array('mem_id' => $mem_id);
            
            if($this->Cmall_review_model->count_by($reviewwhere)) {
                $review_flag = 1;
                $per_page = get_listnum(10000);
            }
            
        }

        $view['view']['review_flag'] = $review_flag;
        
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $sort ? $sort : 'cre_like';
        $forder = $this->input->get('forder', null, 'desc');
        $sfield = '';
        $skeyword = '';

        // $per_page = 5;
        $offset = ($page - 1) * $per_page;

        $is_admin = $this->member->is_admin();

        $all_kind = $this->Cmall_kind_model->get_all_kind();
        

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        

        // if($skind){            
        //     $where['ckd_id'] = $skind;
            
                
        // }
        if($sform){    
            $this->Cmall_review_model->set_where_in('pat_id',$sform);        
                
        }

        if($sage && is_array($sage)){

            $set_where = array();
            foreach($sage as $val){
                if($val === '17')
                    $set_where[] = 'pet_birthday > '.cdate('Y-m-d',strtotime("-1 years"));

                if($val === '18'){
                    $set_where[]= '(pet_birthday >= '.cdate('Y-m-d',strtotime("-1 years")).' and pet_birthday <= '.cdate('Y-m-d',strtotime("-6 years")).')';
                }
                if($val === '19')
                    $set_where[] = 'pet_birthday < '.cdate('Y-m-d',strtotime("-7 years"));                    
            }

            if(!empty($set_where))
                $this->Cmall_review_model->set_where('('.implode(' or ',$set_where).')',false);
        }

        
        if($sattr){            
                $this->Cmall_review_model->set_where_in('pat_id',$sattr);
        }
        if($sscore){            
            $this->Cmall_review_model->set_where_in('cre_score',$sscore);        
                
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
            $this->Cmall_review_model->order_by('cre_id='.$cre_id,'desc',false);   
        }

        // $field = array(
        //     'cmall_review' => array('cre_id','cit_id','cre_title','cre_content','cre_content_html_type','mem_id','cre_score','cre_datetime','cre_like','cre_update_datetime'),
        // );
        
        // $select = get_selected($field);
        
        // $this->Cmall_review_model->_select = $select;

        $result = $this->Cmall_review_model
            ->get_admin_list($per_page, $offset, $where, '', $findex, $forder);

        // $result = $this->Cmall_attr_model->get_review_list($per_page, $offset, $where, '', $findex, $forder);
        // 
        // echo $result['total_rows'];
        $view['view']['data'] = $data;

        
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
                $result['list'][$key] = $this->denguruapi->get_cit_info(element('cit_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->denguruapi->get_mem_info(element('mem_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->denguruapi->convert_review_info($result['list'][$key]);  
                                   
                $result['list'][$key]['num'] = $list_num--;
                
            }
        }
        
        $view['view']['data']['list'] = $result['list'];
        $view['view']['data']['total_rows'] = $result['total_rows'];

        $score = array();
        $score[1] = $score[2] = $score[3] = $score[4] = $score[5] = 0;
        foreach($view['view']['data']['list'] as $val){            
                $score[element('cre_score',$val)]++;
        }
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
        
        
        
        $pet_attr = $this->Pet_attr_model->get_all_attr();

        $view['view']['config']['pet_age'] = element(3,$pet_attr);;
        $view['view']['config']['pet_form'] = element(2,$pet_attr);
        $view['view']['config']['pet_kind'] = element(0,$this->Cmall_kind_model->get_all_kind());
        $view['view']['config']['pet_attr'] = element(1,$pet_attr);
        $view['view']['config']['score'] = $score;
        

        

        return $view['view'];

        
        
    }

    public function itemreviewpost_get($cit_id = 0,$cre_id = 0)
    {   

         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_reviewlist';
        // $this->load->event($eventname);
        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $mem_id = (int) $this->member->item('mem_id');

        if($mem_id)
            $view['view']['data']['member'] = $this->denguruapi->get_mem_info($mem_id);


        $sattr = $sform = $sage = $sscore = array();
        $skind =  '';
        if($this->input->get('sattr') && is_array($this->input->get('sattr'))){
            foreach($this->input->get('sattr') as $val){
                
                if($val === '14') array_push($sform,$val);
                elseif($val === '15') array_push($sform,$val);
                elseif($val === '16') array_push($sform,$val);
                elseif($val === '17') array_push($sage,$val);
                elseif($val === '18') array_push($sage,$val);
                elseif($val === '19') array_push($sage,$val);
                else array_push($sattr,$val);
                // if($val === '4') array_push($sattr,79);
                // if($val === '5') array_push($sattr,80);
                // if($val === '6') array_push($sattr,81);

                // if($val === '7') array_push($sattr,82);
                // if($val === '8') array_push($sattr,83);
                // if($val === '9') array_push($sattr,84);

                // if($val === '10') array_push($sattr,85);
                // if($val === '11') array_push($sattr,86);
                // if($val === '12') array_push($sattr,87);

                // if($val === '13') array_push($sattr,88);
            }
        }

        $skind = $this->input->get('skind');
        $sscore = $this->input->get('sscore');
        

        if($mem_id && $this->input->get('is_mypet_match')){

            
            $sattr = $sform = $sage = array();
            $skind =  '';
            if((int) $view['view']['data']['member']['pet_age'] < 1) array_push($sage,'17');
            elseif((int) $view['view']['data']['member']['pet_age'] < 7) array_push($sage,'18');
            elseif((int) $view['view']['data']['member']['pet_age'] > 7) array_push($sage,'19');

            
            array_push($sform,$view['view']['data']['member']['pat_id']);            
            $skind = $view['view']['data']['member']['ckd_id'];


            if($view['view']['data']['member']['pet_attr']){
                foreach($view['view']['data']['member']['pet_attr'] as $val){

                    if(element('pat_id',$val) === '14') array_push($sform,element('pat_id',$val));
                    elseif(element('pat_id',$val) === '15') array_push($sform,element('pat_id',$val));
                    elseif(element('pat_id',$val) === '16') array_push($sform,element('pat_id',$val));
                    elseif(element('pat_id',$val) === '17') array_push($sage,element('pat_id',$val));
                    elseif(element('pat_id',$val) === '18') array_push($sage,element('pat_id',$val));
                    elseif(element('pat_id',$val) === '19') array_push($sage,element('pat_id',$val));
                    else array_push($sattr,element('pat_id',$val));
                    // if(element('pat_id',$val) === '7') array_push($sattr,82);
                    // if(element('pat_id',$val) === '8') array_push($sattr,83);
                    // if(element('pat_id',$val) === '9') array_push($sattr,84);

                    // if(element('pat_id',$val) === '10') array_push($sattr,85);
                    // if(element('pat_id',$val) === '11') array_push($sattr,86);
                    // if(element('pat_id',$val) === '12') array_push($sattr,87);

                    // if(element('pat_id',$val) === '13') array_push($sattr,88);
                }
            }
        }


        $config = array(
            'sattr' => $sattr,
            'skind' => $skind,
            'sform' => $sform,
            'sage' => $sage,
            'is_mypet_match' => $this->input->get('is_mypet_match'),
            'sort' => $this->input->get('sort'),
            'sscore' => $sscore,

        );

        
        $view['view'] = $this->_itemreviewpost($cit_id,$cre_id,$config);

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


    protected function _userreviewpost($_mem_id='')
    {
        
    

        

        $view = $data = array();
        $view['view'] = array();

    
        

        $this->load->model(array('Cmall_item_model', 'Cmall_review_model', 'Reviewer_model','Member_pet_model'));
        if(empty($_mem_id)){
            show_404();
        }

       $data = $this->denguruapi->get_mem_info($_mem_id);
        
       $view['view']['mem_info'] = $data;   
       $is_admin = $this->member->is_admin();
        $mem_id = (int) $this->member->item('mem_id');
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->input->get('findex', null, 'cre_like');
        $forder = $this->input->get('forder', null, 'desc');
        $sfield = '';
        $skeyword = '';

        $review_flag = 0; //모든 리뷰를 볼수 있는 권한이 있는가

        $per_page = 2;
        if($mem_id){
            $reviewwhere = array('mem_id' => $mem_id);
            
            if($this->Cmall_review_model->count_by($reviewwhere)) {
                $review_flag = 1;
                $per_page = get_listnum(10000);
            }
            
        }

        $view['view']['review_flag'] = $review_flag;

        // $per_page = 5;
        $offset = ($page - 1) * $per_page;

        
        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        $where['cre_status'] = 1;
        $where['mem_id'] = $_mem_id;
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

        

        // $field = array(
        //     'cmall_review' => array('cre_id','cit_id','cre_title','cre_content','cre_content_html_type','mem_id','cre_score','cre_datetime','cre_like','cre_update_datetime'),
        // );
        
        // $select = get_selected($field);
        
        // $this->Cmall_review_model->_select = $select;


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
                

                
                $result['list'][$key]['display_datetime'] = display_datetime(
                    element('cre_datetime', $val),
                    'sns',
                );


                $result['list'][$key]['can_update'] = false;
                $result['list'][$key]['can_delete'] = false;
                if ($is_admin !== false
                    OR (element('mem_id', $val) && $mem_id === (int) element('mem_id', $val))) {
                    $result['list'][$key]['can_update'] = true;
                    $result['list'][$key]['can_delete'] = true;
                }
                $result['list'][$key] = $this->denguruapi->get_cit_info(element('cit_id', $val),$result['list'][$key]);                   
                // $result['list'][$key] = $this->member->get_default_info(element('mem_id', $val),$result['list'][$key]);                   
                $result['list'][$key] = $this->denguruapi->convert_review_info($result['list'][$key]);    
                
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
        // $this->load->event($eventname);

        

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
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
        $view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());

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
        // $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');



        $this->load->model(array('Cmall_item_model', 'Cmall_review_model', 'Review_file_model'));
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
        
        $item = array();
        if ($cit_id) {
            $item = $this->Cmall_item_model->get_one($cit_id,'cit_id,cit_status,cit_is_del');
            
            
            if ( ! element('cit_id', $item) )
                alert('이 상품은 현재 존재하지 않습니다',"",406);

            if(! element('cit_status', $item)) 
                alert('이 상품은 현재 판매하지 않습니다',"",406);
            
            if (!empty(element('cit_is_del', $item))) {
                alert('이 상품은 현재 판매하지 않습니다',"",406);
            }

            $item = $this->denguruapi->get_cit_info($cit_id);
        }
        /**
         * 수정 페이지일 경우 기존 데이터를 가져옵니다
         */
        $review = false;
        if ($cre_id) {
            $review = $this->Cmall_review_model->get_one($cre_id);
            if ( ! element('cre_id', $review)) {
                alert('이 리뷰는 현재 존재하지 않습니다',"",406);
            }
            $is_admin = $this->member->is_admin();
            if ($is_admin === false
                && (int) element('mem_id', $review) !== $mem_id) {
                alert_close('본인의 글 외에는 접근하실 수 없습니다');
            }

            $review = $this->denguruapi->convert_review_info($review);
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
                'field' => 'cre_good',
                'label' => '좋았던 점 ',
                'rules' => 'trim|required|min_length[20]',
            ),
            array(
                'field' => 'cre_bad',
                'label' => '아쉬운 점',
                'rules' => 'trim|required|min_length[20]',
            ),
            array(
                'field' => 'cre_tip',
                'label' => '나만의 팁',
                'rules' => 'trim',
            ),
            array(
                'field' => 'cre_score',
                'label' => '평점',
                'rules' => 'trim|required|numeric|greater_than_equal_to[1]|less_than_equal_to[5]',
            ),
            array(
                'field' => 'cre_type2',
                'label' => '상품추천',
                'rules' => 'trim|numeric',
            ),            
        );
        $this->form_validation->set_rules($config);
        $form_validation = $this->form_validation->run();
        $file_error = '';

        $uploadfiledata = array();
        $uploadfiledata2 = array();
        if ($form_validation) {
            $this->load->library('upload');
            $this->load->library('aws_s3');
            $file = json_encode($_FILES);
            $post = json_encode($_POST);
            // log_message('error', $file);
            // log_message('error', $post);
            if (isset($_FILES) && isset($_FILES['cre_file']) && isset($_FILES['cre_file']['name']) && is_array($_FILES['cre_file']['name'])) {
                $filecount = count($_FILES['cre_file']['name']);
                $upload_path = config_item('uploads_dir') . '/cmall_review/';
                if (is_dir($upload_path) === false) {
                    mkdir($upload_path, 0707);
                    $file = $upload_path . 'index.php';
                    $f = @fopen($file, 'w');
                    @fwrite($f, '');
                    @fclose($f);
                    @chmod($file, 0644);
                }
                $upload_path .= cdate('Y') . '/';
                if (is_dir($upload_path) === false) {
                    mkdir($upload_path, 0707);
                    $file = $upload_path . 'index.php';
                    $f = @fopen($file, 'w');
                    @fwrite($f, '');
                    @fclose($f);
                    @chmod($file, 0644);
                }
                $upload_path .= cdate('m') . '/';
                if (is_dir($upload_path) === false) {
                    mkdir($upload_path, 0707);
                    $file = $upload_path . 'index.php';
                    $f = @fopen($file, 'w');
                    @fwrite($f, '');
                    @fclose($f);
                    @chmod($file, 0644);
                }

                foreach ($_FILES['cre_file']['name'] as $i => $value) {
                    if ($value) {
                        $uploadconfig = array();
                        $uploadconfig['upload_path'] = $upload_path;
                        $uploadconfig['allowed_types'] = 'jpg|jpeg|png|gif|acc|flv|f4a|f4v|mov|mp3|mp4|m4a|m4v|oga|ogg|rss|webm';
                        $uploadconfig['max_size'] = 200 * 1024;
                        $uploadconfig['encrypt_name'] = true;

                        $this->upload->initialize($uploadconfig);
                        $_FILES['userfile']['name'] = $_FILES['cre_file']['name'][$i];
                        $_FILES['userfile']['type'] = $_FILES['cre_file']['type'][$i];
                        $_FILES['userfile']['tmp_name'] = $_FILES['cre_file']['tmp_name'][$i];
                        $_FILES['userfile']['error'] = $_FILES['cre_file']['error'][$i];
                        $_FILES['userfile']['size'] = $_FILES['cre_file']['size'][$i];
                        if ($this->upload->do_upload()) {
                            $filedata = $this->upload->data();

                            $uploadfiledata[$i] = array();
                            $uploadfiledata[$i]['rfi_filename'] = cdate('Y') . '/' . cdate('m') . '/' . element('file_name', $filedata);
                            $uploadfiledata[$i]['rfi_originname'] = element('orig_name', $filedata);
                            $uploadfiledata[$i]['rfi_filesize'] = intval(element('file_size', $filedata) * 1024);
                            $uploadfiledata[$i]['rfi_width'] = element('image_width', $filedata) ? element('image_width', $filedata) : 0;
                            $uploadfiledata[$i]['rfi_height'] = element('image_height', $filedata) ? element('image_height', $filedata) : 0;
                            $uploadfiledata[$i]['rfi_type'] = str_replace('.', '', element('file_ext', $filedata));
                            $uploadfiledata[$i]['is_image'] = element('is_image', $filedata) ? element('is_image', $filedata) : 0;

                            $play_extension = array('acc', 'flv', 'f4a', 'f4v', 'mov', 'mp3', 'mp4', 'm4a', 'm4v', 'oga', 'ogg', 'rss', 'webm');

                            
                            if ( in_array(element('rfi_type', $uploadfiledata[$i]), $play_extension)) {
                                $retval = 1;
                                $cmd ="/usr/bin/ffmpeg  -i ".$this->upload->upload_path.$this->upload->file_name."  -r 0.0033 -vf scale=-1:480 -vcodec png ".$this->upload->upload_path.$this->upload->file_name."%002d.png";
                                @exec($cmd, $output, $retval);
                            }

                            $upload = $this->aws_s3->upload_file($this->upload->upload_path,$this->upload->file_name,$upload_path);
                        } else {
                            $file_error = $this->upload->display_errors();
                            break;
                        }
                    }
                }
            }

            if (isset($_FILES) && isset($_FILES['cre_file_update'])
                && isset($_FILES['cre_file_update']['name'])
                && is_array($_FILES['cre_file_update']['name'])
                && $file_error === '') {
                $filecount = count($_FILES['cre_file_update']['name']);
                $upload_path = config_item('uploads_dir') . '/cmall_review/';
                if (is_dir($upload_path) === false) {
                    mkdir($upload_path, 0707);
                    $file = $upload_path . 'index.php';
                    $f = @fopen($file, 'w');
                    @fwrite($f, '');
                    @fclose($f);
                    @chmod($file, 0644);
                }
                $upload_path .= cdate('Y') . '/';
                if (is_dir($upload_path) === false) {
                    mkdir($upload_path, 0707);
                    $file = $upload_path . 'index.php';
                    $f = @fopen($file, 'w');
                    @fwrite($f, '');
                    @fclose($f);
                    @chmod($file, 0644);
                }
                $upload_path .= cdate('m') . '/';
                if (is_dir($upload_path) === false) {
                    mkdir($upload_path, 0707);
                    $file = $upload_path . 'index.php';
                    $f = @fopen($file, 'w');
                    @fwrite($f, '');
                    @fclose($f);
                    @chmod($file, 0644);
                }

                foreach ($_FILES['cre_file_update']['name'] as $i => $value) {
                    if ($value) {
                        $uploadconfig = array();
                        $uploadconfig['upload_path'] = $upload_path;
                        $uploadconfig['allowed_types'] = 'jpg|jpeg|png|gif|acc|flv|f4a|f4v|mov|mp3|mp4|m4a|m4v|oga|ogg|rss|webm';
                        $uploadconfig['max_size'] = 200 * 1024;
                        $uploadconfig['encrypt_name'] = true;
                        $this->upload->initialize($uploadconfig);
                        $_FILES['userfile']['name'] = $_FILES['cre_file_update']['name'][$i];
                        $_FILES['userfile']['type'] = $_FILES['cre_file_update']['type'][$i];
                        $_FILES['userfile']['tmp_name'] = $_FILES['cre_file_update']['tmp_name'][$i];
                        $_FILES['userfile']['error'] = $_FILES['cre_file_update']['error'][$i];
                        $_FILES['userfile']['size'] = $_FILES['cre_file_update']['size'][$i];
                        if ($this->upload->do_upload()) {
                            $filedata = $this->upload->data();

                            $oldcrefile = $this->Review_file_model->get_one($i);
                            if ((int) element('cre_id', $oldcrefile) !== (int) element('cre_id', $review)) {
                                alert('잘못된 접근입니다');
                            }
                            @unlink(config_item('uploads_dir') . '/cmall_review/' . element('rfi_filename', $oldcrefile));

                            $deleted = $this->aws_s3->delete_file(config_item('s3_folder_name') . '/cmall_review/' . element('rfi_filename', $oldcrefile));

                            $uploadfiledata2[$i] = array();
                            $uploadfiledata2[$i]['rfi_id'] = $i;
                            $uploadfiledata2[$i]['rfi_filename'] = cdate('Y') . '/' . cdate('m') . '/' . element('file_name', $filedata);
                            $uploadfiledata2[$i]['rfi_originname'] = element('orig_name', $filedata);
                            $uploadfiledata2[$i]['rfi_filesize'] = intval(element('file_size', $filedata) * 1024);
                            $uploadfiledata2[$i]['rfi_width'] = element('image_width', $filedata)
                                ? element('image_width', $filedata) : 0;
                            $uploadfiledata2[$i]['rfi_height'] = element('image_height', $filedata)
                                ? element('image_height', $filedata) : 0;
                            $uploadfiledata2[$i]['rfi_type'] = str_replace('.', '', element('file_ext', $filedata));
                            $uploadfiledata2[$i]['is_image'] = element('is_image', $filedata)
                                ? element('is_image', $filedata) : 0;

                            $play_extension = array('acc', 'flv', 'f4a', 'f4v', 'mov', 'mp3', 'mp4', 'm4a', 'm4v', 'oga', 'ogg', 'rss', 'webm');

                            
                            if ( in_array(element('rfi_type', $uploadfiledata2[$i]), $play_extension)) {
                                $retval = 1;
                                $cmd ="/usr/bin/ffmpeg  -i ".$this->upload->upload_path.$this->upload->file_name."  -r 0.0033 -vf scale=-1:480 -vcodec png ".$this->upload->upload_path.$this->upload->file_name."%002d.png";
                                @exec($cmd, $output, $retval);
                            }
                            
                            $upload = $this->aws_s3->upload_file($this->upload->upload_path,$this->upload->file_name,$upload_path);
                        } else {
                            $file_error = $this->upload->display_errors();
                            break;
                        }
                    }
                }
            }
            
        }

        /**
         * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
         * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
         */
        if ($form_validation === false OR $file_error !== '') {

            /**
             * primary key 정보를 저장합니다
             */
            // $view['view']['primary_key'] = $primary_key;
            

            if(validation_errors().$file_error){
                $view['msg'] = validation_errors().$file_error;
                log_message('error', 'msg:'.$file_error .validation_errors() .' pointer:'.current_url());
                // $view['view']['wishlist_url'] = base_url('cmall/wishlist');
                // $view['view']['itemlists_url'] = base_url('cmall/itemlists');
                // $view['view']['data']['review'] = $review;
                // $view['view']['data']['item'] = $item;

                /**
                 * primary key 정보를 저장합니다
                 */
                // $view['view']['primary_key'] = $primary_key;

                $view['http_status_codes'] = 400;
            }   else {
                // $view['msg'] = validation_errors().$file_error;

                $view['wishlist_url'] = base_url('cmall/wishlist');
                $view['itemlists_url'] = base_url('cmall/itemlists');
                $view['data']['review'] = $review;
                $view['data']['item'] = $item;

                /**
                 * primary key 정보를 저장합니다
                 */
                // $view['primary_key'] = $primary_key;

                $view['http_status_codes'] = parent::HTTP_OK;
            }
            
            return $view;
            // return $this->response($view['view'], parent::HTTP_OK);

            

        } else {
            
            /**
             * 유효성 검사를 통과한 경우입니다.
             * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
             */

            // // 이벤트가 존재하면 실행합니다
            // Events::trigger('formruntrue', $eventname);

            
            $updatedata = array(
                'cit_id' => $cit_id,
                'brd_id' => element('brd_id',$item),
                'cre_good' => $this->input->post_put('cre_good', null, ''),
                'cre_bad' => $this->input->post_put('cre_bad', null, ''),
                'cre_tip' => $this->input->post_put('cre_tip', null, ''),
                'cre_score' => $this->input->post_put('cre_score', null, 0),
                'cre_type2' => $this->input->post_put('cre_type2', null, 0),
            );

            

            /**
             * 게시물을 수정하는 경우입니다
             */
            $param =& $this->querystring;
            $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;


            $this->Cmall_review_model->reconnect();
            if ($cre_id) {

                // 이벤트가 존재하면 실행합니다
                // Events::trigger('before_update', $eventname);
                $updatedata['cre_update_datetime'] = cdate('Y-m-d H:i:s');
                $this->Cmall_review_model->update($cre_id, $updatedata);
                $cntresult = $this->cmalllib->update_review_count($cit_id);
                // $jresult = json_decode($cntresult, true);
                // $cnt = element('cit_review_count', $jresult);
                // echo '<script type="text/javascript">window.opener.view_cmall_review("viewitemreview", ' . $cit_id . ', ' . $page . ');window.opener.cmall_review_count_update(' . $cnt . ');</script>';
                

                $view = array('msg' => '정상적으로 수정되었습니다.');
                
                

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

                $cre_id = $this->Cmall_review_model->insert($updatedata);

                // $this->cmalllib->review_alarm($_cre_id);

                $cntresult = $this->cmalllib->update_review_count($cit_id);
                // $jresult = json_decode($cntresult, true);
                // $cnt = element('cit_review_count', $jresult);
                if ($this->cbconfig->item('use_cmall_product_review_approve')) {
                    // echo '<script type="text/javascript">window.opener.view_cmall_review("viewitemreview", ' . $cit_id . ', ' . $page . ');window.opener.cmall_review_count_update(' . $cnt . ');</script>';
                    

                    $view = array('msg' => '정상적으로 입력되었습니다. 관리자의 승인 후 출력됩니다.');
                
                

                } else {
                    // echo '<script type="text/javascript">window.opener.view_cmall_review("viewitemreview", ' . $cit_id . ', ' . $page . ');window.opener.cmall_review_count_update(' . $cnt . ');</script>';
                    

                    $view = array('msg' => '정상적으로 입력되었습니다.');
                

                }
            }

            $file_updated = false;
            $file_changed = false;
            if ($uploadfiledata
                && is_array($uploadfiledata)
                && count($uploadfiledata) > 0) {
                foreach ($uploadfiledata as $pkey => $pval) {
                    if ($pval) {
                        $fileupdate = array(
                            'cre_id' => $cre_id,
                            'brd_id' => element('brd_id',$item),
                            'mem_id' => $mem_id,
                            'rfi_originname' => element('rfi_originname', $pval),
                            'rfi_filename' => element('rfi_filename', $pval),
                            'rfi_filesize' => element('rfi_filesize', $pval),
                            'rfi_width' => element('rfi_width', $pval),
                            'rfi_height' => element('rfi_height', $pval),
                            'rfi_type' => element('rfi_type', $pval),
                            'rfi_is_image' => element('is_image', $pval),
                            'rfi_datetime' => cdate('Y-m-d H:i:s'),
                            'rfi_ip' => $this->input->ip_address(),
                        );
                        $file_id = $this->Review_file_model->insert($fileupdate);
                        // if ( ! element('is_image', $pval)) {
                        //     if (element('use_point', $board)) {
                        //         $point = $this->point->insert_point(
                        //             $mem_id,
                        //             element('point_fileupload', $board),
                        //             element('board_name', $board) . ' ' . $post_id . ' 파일 업로드',
                        //             'fileupload',
                        //             $file_id,
                        //             '파일 업로드'
                        //         );
                        //     }
                        // }
                        $file_updated = true;
                    }
                }
                $file_changed = true;
            }
            if ($uploadfiledata2
                && is_array($uploadfiledata2)
                && count($uploadfiledata2) > 0) {
                foreach ($uploadfiledata2 as $pkey => $pval) {
                    if ($pval) {
                        $fileupdate = array(
                            'mem_id' => $mem_id,
                            'rfi_originname' => element('rfi_originname', $pval),
                            'rfi_filename' => element('rfi_filename', $pval),
                            'rfi_filesize' => element('rfi_filesize', $pval),
                            'rfi_width' => element('rfi_width', $pval),
                            'rfi_height' => element('rfi_height', $pval),
                            'rfi_type' => element('rfi_type', $pval),
                            'rfi_is_image' => element('is_image', $pval),
                            'rfi_datetime' => cdate('Y-m-d H:i:s'),
                            'rfi_ip' => $this->input->ip_address(),
                        );
                        $this->Review_file_model->update($pkey, $fileupdate);
                        // if ( ! element('is_image', $pval)) {
                        //     if (element('use_point', $board)) {
                        //         $point = $this->point->insert_point(
                        //             $mem_id,
                        //             element('point_fileupload', $board),
                        //             element('board_name', $board) . ' ' . $post_id . ' 파일 업로드',
                        //             'fileupload',
                        //             $pkey,
                        //             '파일 업로드'
                        //         );
                        //     }
                        // } else {
                        //     $this->point->delete_point(
                        //         $mem_id,
                        //         'fileupload',
                        //         $pkey,
                        //         '파일 업로드'
                        //     );
                        // }
                        $file_changed = true;
                    }
                }
            }
            if ($this->input->post('cre_file_del')) {
                foreach ($this->input->post('cre_file_del') as $key => $val) {
                    if ($val && ! isset($uploadfiledata2[$val])) {
                        $oldcrefile = $this->Review_file_model->get_one($val);
                        if ( ! element('cre_id', $oldcrefile) OR (int) element('cre_id', $oldcrefile) !== (int) element('cre_id', $review)) {
                            alert('잘못된 접근입니다.');
                        }
                        @unlink(config_item('uploads_dir') . '/cmall_review/' . element('rfi_filename', $oldcrefile));

                        $deleted = $this->aws_s3->delete_file(config_item('s3_folder_name') . '/cmall_review/' . element('rfi_filename', $oldcrefile));
                        $this->Review_file_model->delete($val);
                        // $this->point->delete_point(
                        //     $mem_id,
                        //     'fileupload',
                        //     $key,
                        //     '파일 업로드'
                        // );
                        $file_changed = true;
                    }
                }
            }

            $updatedata['cre_image'] = 0;
            $updatedata['cre_file'] = 0;
            $result = $this->Review_file_model->get_review_file_count($cre_id);
            if ($result && is_array($result)) {
                $total_cnt = 0;
                foreach ($result as $value) {
                    if (element('rfi_is_image', $value)) {
                        $updatedata['cre_image'] = element('cnt', $value);
                        $total_cnt += element('cnt', $value);
                    } else {
                        $updatedata['cre_file'] = element('cnt', $value);
                        $total_cnt += element('cnt', $value);
                    }
                }
            }

            $this->Cmall_review_model->update($cre_id, $updatedata);

            $view['http_status_codes'] = 201;

            return $view;
        }
        
    }

    public function reviewwrite_get($cit_id = 0, $cre_id = 0)
    {

         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_review_write';
        // $this->load->event($eventname);

        

        $view = array();
        
        $view = $this->_reviewwrite($cit_id, $cre_id);

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
        // $view['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        $this->data = $view;
        
        
        return $this->response($view, $view['http_status_codes']);
    }

    public function reviewwrite_post($cit_id = 0, $cre_id = 0)
    {

         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_review_write';
        // $this->load->event($eventname);

        $cit_id = (int) $cit_id;
        

        if (empty($cit_id) OR $cit_id < 1) {
            alert('상품이 현재 존재하지 않습니다',"",400);
        }

        $view = array();
        

        // 이벤트가 존재하면 실행합니다

        $view = $this->_reviewwrite($cit_id, $cre_id);


        return $this->response($view, $view['http_status_codes']);
       
    }

    public function reviewwrite_put($cit_id = 0, $cre_id = 0)
    {   
         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_review_write';
        // $this->load->event($eventname);
        


        print_r($_FILES);

        exit;
        $cit_id = (int) $cit_id;        
        if (empty($cit_id) OR $cit_id < 1) {
            show_404();
        }

        $cre_id = (int) $cre_id;        
        if (empty($cre_id) OR $cre_id < 1) {
            show_404();
        }

        $view = array();
        

        // 이벤트가 존재하면 실행합니다
        
        $view = $this->_reviewwrite($cit_id, $cre_id);


        return $this->response(array('msg' => $view['msg']), $view['http_status_codes']);
        
    }

    public function review_delete($cre_id = 0)
    {   
         // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_review_write';
        // $this->load->event($eventname);



        $cre_id = (int) $cre_id;        
        if (empty($cre_id) OR $cre_id < 1) {
            show_404();
        }

        required_user_login();

        $mem_id = (int) $this->member->item('mem_id');
        
        $this->load->model(array('Cmall_review_model'));
        

        $review = $this->Cmall_review_model->get_one($cre_id);

        if ( ! element('cre_id', $review)) {
            alert('이 리뷰는 현재 존재하지 않습니다',"",406);
        }

        $is_admin = $this->member->is_admin();
        if ($is_admin === false && (int) element('mem_id', $review) !== $mem_id) {
            alert_close('본인의 글 외에는 접근하실 수 없습니다');
        }

        

        $this->cmalllib->_review_delete($cre_id);
        $result = array(
            'msg' => '상품리뷰가 삭제되었습니다'
        );

        $this->data = $result;
        
        return $this->response($this->data, 201);
    }

    public function _reviewwrite_required($str)
    {
        if (!$this->input->post_put('cre_good') && ! $this->input->post_put('cre_bad') && ! $this->input->post_put('cre_tip')) {
            $this->form_validation->set_message(
                '_reviewwrite_required',
                '리뷰 작성시 좋았던점 또는 아쉬운 점 또는 나만의 팁 중 하나는 입력하셔야 합니다'
            );
            return false;
        }
        return true;
    }
}

