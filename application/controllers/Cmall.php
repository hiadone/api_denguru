<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmall class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 컨텐츠몰 페이지에 관한 controller 입니다.
 */
class Cmall extends CB_Controller
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
        $this->load->library(array('pagination', 'querystring', 'accesslevel', 'cmalllib','denguruapi'));

        if ( ! $this->cbconfig->item('use_cmall')) {
            alert('이 웹사이트는 ' . html_escape($this->cbconfig->item('cmall_name')) . ' 기능을 사용하지 않습니다',"",406);
            return;
        }


    }


    /**
     * 컨텐츠몰 메인페이지입니다
     */
    protected function _main()
    {
        

        $view = array();
        $view['view'] = array();

        $this->load->model(array('Cmall_item_model','Other_model','Cmall_review_model'));
        
        $mem_id = (int) $this->member->item('mem_id');

        
        

        
        $view['view']['type1_url'] = base_url('cmall/cit_type1_lists');
        
        // if ($this->member->is_member()) {
        
        $config = array(
            'mem_id' => $mem_id,
            'pet_id' => $this->member->item('pet_id'),
        );

        $view['view']['data']['ai_recom'] = $this->_itemairecomlists($config);
            // }
        
        // $field = array(
        //  'board' => array('brd_name'),
        //  'cmall_item' => array('cit_id','cit_name','cit_file_1','cit_review_average','cit_price','cit_price_sale'),
        //  'cmall_brand' => array('cbr_value_kr','cbr_value_en'),
        // );
        
        // $select = get_selected($field);

        $config = array(
            'cit_type1' => '1',
            'limit' => '5',
            'cache_minute' => 86400,
            // 'select' => $select,
        );
        $result_1 = $this->denguruapi->cit_latest($config);
        
        // print_r2($result_1);
        // exit;
        if ($result_1) {
            foreach ($result_1 as $key => $val) {
                // $view['list'][$key]['cit_id'] = element('cit_id',$val);
                // $view['list'][$key]['cit_key'] = element('cit_key',$val);
                // $view['list'][$key]['cit_name'] = element('cit_name',$val);
                // $view['list'][$key]['cit_order'] = element('cit_order',$val);                
                // $view['list'][$key]['cit_price'] = element('cit_price',$val);
                // $view['list'][$key]['cit_file_1'] = cdn_url('cmallitem',element('cit_file_1',$val));
                // $view['list'][$key]['cit_hit'] = element('cit_hit',$val);
                // $view['list'][$key]['cit_datetime'] = element('cit_datetime',$val);
                // $view['list'][$key]['cit_updated_datetime'] = element('cit_updated_datetime',$val);
                // $view['list'][$key]['cit_sell_count'] = element('cit_sell_count',$val);
                // $view['list'][$key]['cit_wish_count'] = element('cit_wish_count',$val);
                // $view['list'][$key]['cit_review_count'] = element('cit_review_count',$val);
                // $view['list'][$key]['cit_review_average'] = element('cit_review_average',$val);
                // $view['list'][$key]['cit_qna_count'] = element('cit_qna_count',$val);
                // $view['list'][$key]['cit_is_soldout'] = element('cit_is_soldout',$val);
                // $view['list'][$key]['post_id'] = element('post_id',$val);
                // $view['list'][$key]['cmall_item_url'] = cmall_item_url(element('cit_id',$val));
                // $view['list'][$key]['board_url'] = board_url(element('brd_id',$val));
                // $view['list'][$key]['post_url'] = post_url(element('post_id',$val));
                // $view['list'][$key]['cit_post_url'] = element('cit_post_url',$val);
                // $view['list'][$key]['cit_attr'] = element('cit_attr',$val);
                $result_1[$key] = $this->denguruapi->convert_cit_info($result_1[$key]);
                $result_1[$key] = $this->denguruapi->convert_brd_info($result_1[$key]);
            }
            
            $view['view']['data']['type1']['list'] = $result_1;
        }
        
        


        $result_2_top = $this->Other_model->get_other();

        if ($result_2_top) {
            foreach ($result_2_top as $key => $val) {
                $result_2_top[$key]['oth_image'] = cdn_url('other',element('oth_image',$val));
                $result_2_top[$key]['search_url'] = base_url('search/show_list/'.element('oth_id',$val).'?skeyword='.element('oth_title',$val));
            }
            $view['view']['data']['type2']['top']['list'] = $result_2_top;
        }


        $config = array(
            'cit_type2' => '2',
            'limit' => '20',
            'cache_minute' => 86400,
            // 'select' => $select,
        );

        $result_2 = $this->denguruapi->cit_latest($config);

        if ($result_2) {
            foreach ($result_2 as $key => $val) {
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_id'] = element('cit_id',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_key'] = element('cit_key',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_name'] = element('cit_name',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_order'] = element('cit_order',$val);
            
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_price'] = element('cit_price',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_file_1'] = element('cit_file_1',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_hit'] = element('cit_hit',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_datetime'] = element('cit_datetime',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_updated_datetime'] = element('cit_updated_datetime',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_sell_count'] = element('cit_sell_count',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_wish_count'] = element('cit_wish_count',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_review_count'] = element('cit_review_count',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_review_average'] = element('cit_review_average',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_qna_count'] = element('cit_qna_count',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_is_soldout'] = element('cit_is_soldout',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['post_id'] = element('post_id',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cmall_item_url'] = cmall_item_url(element('cit_id',$val));
                // $view['view']['data']['type2']['middle']['list'][$key]['board_url'] = board_url(element('brd_id',$val));
                // $view['view']['data']['type2']['middle']['list'][$key]['post_url'] = post_url(element('post_id',$val));
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_post_url'] = element('cit_post_url',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_attr'] = element('cit_attr',$val);
                // $view['view']['data']['type2']['middle']['list'][$key]['cit_brand'] = element('cbr_value_kr',$val,element('cbr_value_en',$val));
                
                $result_2[$key] = $this->denguruapi->convert_cit_info($result_2[$key]);
                $result_2[$key] = $this->denguruapi->convert_brd_info($result_2[$key]);
                
            }

            $view['view']['data']['type2']['middle']['list'] = $result_2;
        }

        $config = array(
            'mem_id' => $mem_id,
            'pet_id' => $this->member->item('pet_id'),
        );
        // if ($this->member->is_member()) {
                $view['view']['data']['denguru_recom'] = $this->_itemdengururecomlists($config);
            // }

        // $param =& $this->querystring;
        // $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        // $findex = $this->input->get('findex', null, 'cre_id');
        // $forder = $this->input->get('forder', null, 'desc');
        // $sfield = '';
        // $skeyword = '';

        // $per_page = 5;
        // $offset = ($page - 1) * $per_page;

        // $is_admin = $this->member->is_admin();
        
        // $where = array();
        // $where['cre_status'] = 1;
        // $thumb_width = ($this->cbconfig->get_device_view_type() === 'mobile')
        //  ? $this->cbconfig->item('cmall_product_review_mobile_thumb_width')
        //  : $this->cbconfig->item('cmall_product_review_thumb_width');
        // $autolink = ($this->cbconfig->get_device_view_type() === 'mobile')
        //  ? $this->cbconfig->item('use_cmall_product_review_mobile_auto_url')
        //  : $this->cbconfig->item('use_cmall_product_review_auto_url');
        // $popup = ($this->cbconfig->get_device_view_type() === 'mobile')
        //  ? $this->cbconfig->item('cmall_product_review_mobile_content_target_blank')
        //  : $this->cbconfig->item('cmall_product_review_content_target_blank');

        // $findex = $this->input->get('findex', null, 'cre_id');
        // $forder = $this->input->get('forder', null, 'desc');

        // $result = $this->Cmall_review_model
        //  ->get_admin_list(5,'', $where, '', $findex, $forder);
        // $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        // if (element('list', $result)) {
        //  foreach (element('list', $result) as $key => $val) {
        //      $view['view']['review'][$key]['cit_name'] = html_escape(element('cit_name', $val));
        //      $view['view']['review'][$key]['cre_title'] = html_escape(element('cre_title', $val));

        //      $view['view']['review'][$key]['mem_userid'] = element('mem_userid', $val);
        //      $view['view']['review'][$key]['mem_nickname'] = element('mem_nickname', $val);
        //      $view['view']['review'][$key]['mem_icon'] = element('mem_icon', $val);
                    
        //      $view['view']['review'][$key]['cre_datetime'] = element('cre_datetime', $val);

        //      $view['view']['review'][$key]['cre_score'] = element('cre_score', $val);

        //      $view['view']['review'][$key]['display_content'] = display_html_content(
        //          element('cre_content', $val),
        //          element('cre_content_html_type', $val),
        //          $thumb_width,
        //          $autolink,
        //          $popup
        //      );
        //      $view['view']['review'][$key]['cre_like'] = element('cre_like', $val);

        //      $view['view']['review'][$key]['can_update'] = false;
        //      $view['view']['review'][$key]['can_delete'] = false;
        //      if ($is_admin !== false
        //          OR (element('mem_id', $val) && $mem_id === (int) element('mem_id', $val))) {
        //          $view['view']['review'][$key]['can_update'] = true;
        //          $view['view']['review'][$key]['can_delete'] = true;
        //      }
        //      $view['view']['review'][$key]['num'] = $list_num--;
        //  }
        // }
        
        
        
        // $this->layout = element('layout_skin_file', element('layout', $view));
        // $this->view = element('view_skin_file', element('layout', $view));

        
        

        // redirect(site_url('/board/b-a-1'));

        return $view['view'];
    }

    public function main_get()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_index';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        
        
        $view['view'] = $this->_main();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
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
            'skin' => 'main',
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
        // $this->layout = element('layout_skin_file', element('layout', $view));
        // $this->view = element('view_skin_file', element('layout', $view));

        
        

        // redirect(site_url('/board/b-a-1'));

        return $this->response($this->data, parent::HTTP_OK);
    }


    protected function itemairecomlists_get($pet_id=0)
    {
        
        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');
        
        if(empty($pet_id)) $pet_id = $this->member->item('pet_id');

        $config = array(
            'mem_id' => $mem_id,
            'pet_id' => $pet_id,            
            'sort' => $this->input->get('sort'),
        );

        $view['view'] = $this->_itemairecomlists($config);  
        

        $this->data = $view['view'];
        
        // print_r2($this->data);
        return $this->response($this->data, parent::HTTP_OK);
        
    }

    protected function _itemairecomlists($config)
    {
        


        $mem_id = element('mem_id', $config) ? element('mem_id', $config) : 0;
        $pet_id = element('pet_id', $config) ? element('pet_id', $config) : 0;
        $sort = element('sort', $config) ? element('sort', $config) : 'cit_type3';
        $sort = $sort.',';

        $view = array();
        $view['view'] = array();

        if(empty($mem_id)) return false;

        if(empty($pet_id)) return false;        



        $this->load->model(array('Board_model','Member_pet_model','Cmall_kind_model','Cmall_attr_model'));


        $pet = $this->Member_pet_model->get_one($pet_id);


        if (empty(element('pet_id', $pet))) {
            return false;
        }

        if (empty(element('mem_id', $pet))) {
            return false;
        }

        if ($mem_id != element('mem_id', $pet)) {
            return false;
        }

        $pet_info = $this->denguruapi->get_pet_info($mem_id,$pet_id);
        
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        

        $findex = ($this->input->get('findex') && in_array($this->input->get('findex'), $allow_order_field)) ? $this->input->get('findex') : '(0.1/cit_order3)';
        

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        

        $sattr =  array();
        $sattr2 =  array();
        $usattr =  array();
        $skind = '';


        

        if(element('ckd_size',$pet_info)) array_push($sattr2,element('ckd_size',$pet_info));

        if((int) element('pet_age',$pet_info) < 1) array_push($sattr,12);
        elseif((int) element('pet_age',$pet_info) < 7) array_push($sattr,13);
        elseif((int) element('pet_age',$pet_info) > 7) array_push($sattr,14);

        $skind = element('ckd_id',$pet_info);

        if(element('pet_attr',$pet_info)){

            foreach(element('pet_attr',$pet_info) as $val){
                
                if(element('pat_id',$val) === '4') array_push($sattr,79);
                if(element('pat_id',$val) === '5') array_push($sattr,80);
                if(element('pat_id',$val) === '6') array_push($sattr,81);

                if(element('pat_id',$val) === '7') array_push($sattr,82);
                if(element('pat_id',$val) === '8') array_push($sattr,83);
                if(element('pat_id',$val) === '9') array_push($sattr,84);

                if(element('pat_id',$val) === '10') array_push($sattr,85);
                if(element('pat_id',$val) === '11') array_push($sattr,86);
                if(element('pat_id',$val) === '12') array_push($sattr,87);

                if(element('pat_id',$val) === '13') array_push($sattr,88);
            }
        }

        if(element('pet_allergy_rel',$pet_info)){
            foreach(element('pet_allergy_rel',$pet_info) as $val){
                
                if(element('pag_id',$val) === '3') array_push($usattr,22);
                if(element('pag_id',$val) === '4') array_push($usattr,53);
                if(element('pag_id',$val) === '5') array_push($usattr,54);
                
                if(element('pag_id',$val) === '6') array_push($usattr,55);
                if(element('pag_id',$val) === '7') array_push($usattr,56);
                if(element('pag_id',$val) === '8') array_push($usattr,57);
                
                if(element('pag_id',$val) === '9') array_push($usattr,58);
                if(element('pag_id',$val) === '10') array_push($usattr,59);
                if(element('pag_id',$val) === '11') array_push($usattr,60);
                
                if(element('pag_id',$val) === '12') array_push($usattr,61);
                if(element('pag_id',$val) === '13') array_push($usattr,62);
                if(element('pag_id',$val) === '14') array_push($usattr,63);

                if(element('pag_id',$val) === '15') array_push($usattr,64);
                if(element('pag_id',$val) === '16') array_push($usattr,65);
                if(element('pag_id',$val) === '17') array_push($usattr,66);

                if(element('pag_id',$val) === '18') array_push($usattr,67);
                if(element('pag_id',$val) === '19') array_push($usattr,68);
                if(element('pag_id',$val) === '20') array_push($usattr,69);

                if(element('pag_id',$val) === '21') array_push($usattr,70);
                if(element('pag_id',$val) === '22') array_push($usattr,89);
                if(element('pag_id',$val) === '23') array_push($usattr,90);

                if(element('pag_id',$val) === '24') array_push($usattr,91);
                if(element('pag_id',$val) === '25') array_push($usattr,92);
                if(element('pag_id',$val) === '26') array_push($usattr,93);

                if(element('pag_id',$val) === '27') array_push($usattr,94);
            }
        }

        $all_kind = $this->Cmall_kind_model->get_all_kind();
        $all_attr = $this->Cmall_attr_model->get_all_attr();
        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array(
                'brd_search' => 1,
                'brd_blind' => 0,               
                );

        

        
        
            $cmallwhere = 'where
                cit_status = 1
                AND cit_is_del = 0
                AND cit_is_soldout = 0
                AND cit_type3 = 1
            ';
            $_join = '';

            $this->Board_model->_select = 'board.brd_id,board.brd_name,board.brd_image,board.brd_blind,cmall_item.cit_id,cmall_item.cit_name,cmall_item.cit_file_1,cmall_item.cit_review_average,cmall_item.cit_price,cmall_item.cit_price_sale';
            

            $_join = "
                select cit_id,brd_id,cit_order,cit_order1,cit_order2,cit_order3,cit_order4,cit_name,cit_file_1,cit_review_average,cit_price,cit_price_sale,".$sort."cbr_id,cit_version from cb_cmall_item ".$cmallwhere;
        
        if($sattr && is_array($sattr)){
                        
            $sattr_id = array();
            $usattr_id = array();
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($sattr as $cval){
                        if($cval == element('cat_id',$aaval)){
                            $sattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($usattr as $cval){
                        if($cval == element('cat_id',$aaval)){

                            $usattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            
            
            
            $sattr_val = array();
            $usattr_val = array();
            foreach($sattr_id as $skey => $sval){
                foreach($sval as $sval_){
                    array_push($sattr_val,$sval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }

            foreach($usattr_id as $uskey => $usval){
                foreach($usval as $usval_){
                    array_push($usattr_val,$usval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }
            
            
            if(!empty($sattr_val))
                $_join .= " and cit_id in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$sattr_val)."))";
            if(!empty($usattr_val))
                $_join .= " and cit_id not in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$usattr_val)."))";
        }

        if($sattr && is_array($sattr)){
                        
            $sattr_id = array();
            
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($sattr as $cval){
                        if($cval == element('cat_id',$aaval)){
                            $sattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            

            
            
            
            $sattr_val = array();
            
            foreach($sattr_id as $skey => $sval){
                foreach($sval as $sval_){
                    array_push($sattr_val,$sval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }

            
            
            
            if(!empty($sattr_val))
                $_join .= " and cit_id in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$sattr_val)."))";
            
        }

        if($usattr && is_array($usattr)){
                        
            
            $usattr_id = array();
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($usattr as $cval){
                        if($cval == element('cat_id',$aaval)){

                            $usattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            
            
            
            
            $usattr_val = array();
            

            foreach($usattr_id as $uskey => $usval){
                foreach($usval as $usval_){
                    array_push($usattr_val,$usval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }
            
            if(!empty($usattr_val))
                $_join .= " and cit_id not in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$usattr_val)."))";
        }

        if($sattr2 && is_array($sattr2)){
                        
            $sattr2_id = array();
            
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($sattr2 as $cval){
                        if($cval == element('cat_id',$aaval)){
                            $sattr2_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            

            
            
            
            $sattr2_val = array();
            
            foreach($sattr2_id as $skey => $sval){
                foreach($sval as $sval_){
                    array_push($sattr2_val,$sval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }

            
            
            
            if(!empty($sattr2_val))
                $_join .= " and cit_id in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$sattr2_val)."))";
            
        }


        if($skind){

            // $this->Board_model->set_where_in('cmal1l_kind_rel.ckd_id',$skind);
            // $this->Board_model->set_where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);
            $_join .=" and cit_id in (select cit_id from cb_cmall_kind_rel where ckd_id = ".$skind." )" ;

    //         if(empty($sattr))
                // $this->Board_model->set_join(array('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner')); 
            
        }
        
        if(!empty($_join))
            $set_join[] = array("
                (".$_join." ORDER BY RAND()) as cb_cmall_item ",'cmall_item.brd_id = board.brd_id','inner');


        

        
        // $field = array(
        //  'board' => array('brd_name'),
        //  'cmall_item' => array('cit_id','cit_name','cit_file_1','cit_review_average','cit_price','cit_price_sale'),
        //  'cmall_brand' => array('cbr_value_kr','cbr_value_en'),
        // );
        
        // $select = get_selected($field);

        // $this->Board_model->select = $select;

        if(!empty($set_join)) {
            $this->Board_model->set_join($set_join);
            // $this->Board_model->set_group_by('cmall_item.cit_id');
        }
        $result = $this->Board_model
            ->get_search_list(20,'' , $where,'','','');
        $list_num = $result['total_rows'];
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                $result['list'][$key] = $this->denguruapi->convert_cit_info($result['list'][$key]);
                $result['list'][$key] = $this->denguruapi->convert_brd_info($result['list'][$key]);
                $result['list'][$key]['attr'] = $this->Cmall_attr_model->get_attr(element('cit_id',$val));

                
                // $result['list'][$key]['num'] = $list_num--;
            }
        }   

        
        $result['pet_info'] = $pet_info;
        $view['view'] = $result;
        
        return $view['view'];
        
    }

    protected function itemdengururecomlists_get($pet_id=0)
    {
        
        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');
        if(empty($pet_id)) $pet_id = $this->member->item('pet_id');

        $config = array(
            'mem_id' => $mem_id,
            'pet_id' => $pet_id,            
            'sort' => $this->input->get('sort'),
            

        );
        $view['view'] = $this->_itemdengururecomlists($config); 
        

        $this->data = $view['view'];
        
        // print_r2($this->data);
        return $this->response($this->data, parent::HTTP_OK);
        
    }

    protected function _itemdengururecomlists($config)
    {
        

        $mem_id = element('mem_id', $config) ? element('mem_id', $config) : 0;
        $pet_id = element('pet_id', $config) ? element('pet_id', $config) : 0;
        $sort = element('sort', $config) ? element('sort', $config) : 'cit_type1';

        $sort=$sort.',';
        $view = array();
        $view['view'] = array();
        if(empty($mem_id)) return false;

        if(empty($pet_id)) return false;        



        $this->load->model(array('Board_model','Member_pet_model','Cmall_kind_model','Cmall_attr_model','Kinditem_rel_model','Kinditem_group_model'));


        $pet = $this->Member_pet_model->get_one($pet_id);

        if (empty(element('pet_id', $pet))) {
            return false;
        }

        if (empty(element('mem_id', $pet))) {
            return false;
        }

        if ($mem_id != element('mem_id', $pet)) {
            return false;
        }

        $pet_info = $this->denguruapi->get_pet_info($mem_id,$pet_id);
        

        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        

        $findex = ($this->input->get('findex') && in_array($this->input->get('findex'), $allow_order_field)) ? $this->input->get('findex') : 'cit_order3 asc';
        

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        

        $sattr =  array();
        $sattr2 =  array();
        $usattr =  array();
        $skind = '';


        if(element('ckd_size',$pet_info)) array_push($sattr2,element('ckd_size',$pet_info));

        if((int) element('pet_age',$pet_info) < 1) array_push($sattr,12);
        elseif((int) element('pet_age',$pet_info) < 7) array_push($sattr,13);
        elseif((int) element('pet_age',$pet_info) > 6) array_push($sattr,14);

        $skind = element('ckd_id',$pet_info);

        
        
        if(element('pet_attr',$pet_info)){

            foreach(element('pet_attr',$pet_info) as $val){
                
                if(element('pat_id',$val) === '4') array_push($sattr,79);
                if(element('pat_id',$val) === '5') array_push($sattr,80);
                if(element('pat_id',$val) === '6') array_push($sattr,81);

                if(element('pat_id',$val) === '7') array_push($sattr,82);
                if(element('pat_id',$val) === '8') array_push($sattr,83);
                if(element('pat_id',$val) === '9') array_push($sattr,84);

                if(element('pat_id',$val) === '10') array_push($sattr,85);
                if(element('pat_id',$val) === '11') array_push($sattr,86);
                if(element('pat_id',$val) === '12') array_push($sattr,87);

                if(element('pat_id',$val) === '13') array_push($sattr,88);
            }
        }

        if(element('pet_allergy_rel',$pet_info)){
            foreach(element('pet_allergy_rel',$pet_info) as $val){
                
                if(element('pag_id',$val) === '3') array_push($usattr,22);
                if(element('pag_id',$val) === '4') array_push($usattr,53);
                if(element('pag_id',$val) === '5') array_push($usattr,54);
                
                if(element('pag_id',$val) === '6') array_push($usattr,55);
                if(element('pag_id',$val) === '7') array_push($usattr,56);
                if(element('pag_id',$val) === '8') array_push($usattr,57);
                
                if(element('pag_id',$val) === '9') array_push($usattr,58);
                if(element('pag_id',$val) === '10') array_push($usattr,59);
                if(element('pag_id',$val) === '11') array_push($usattr,60);
                
                if(element('pag_id',$val) === '12') array_push($usattr,61);
                if(element('pag_id',$val) === '13') array_push($usattr,62);
                if(element('pag_id',$val) === '14') array_push($usattr,63);

                if(element('pag_id',$val) === '15') array_push($usattr,64);
                if(element('pag_id',$val) === '16') array_push($usattr,65);
                if(element('pag_id',$val) === '17') array_push($usattr,66);

                if(element('pag_id',$val) === '18') array_push($usattr,67);
                if(element('pag_id',$val) === '19') array_push($usattr,68);
                if(element('pag_id',$val) === '20') array_push($usattr,69);

                if(element('pag_id',$val) === '21') array_push($usattr,70);
                if(element('pag_id',$val) === '22') array_push($usattr,89);
                if(element('pag_id',$val) === '23') array_push($usattr,90);

                if(element('pag_id',$val) === '24') array_push($usattr,91);
                if(element('pag_id',$val) === '25') array_push($usattr,92);
                if(element('pag_id',$val) === '26') array_push($usattr,93);

                if(element('pag_id',$val) === '27') array_push($usattr,94);
            }
        }

        $all_kind = $this->Cmall_kind_model->get_all_kind();
        $all_attr = $this->Cmall_attr_model->get_all_attr();
        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array(
                'brd_search' => 1,
                'brd_blind' => 0,               
                );

        

        
        
            $cmallwhere = 'where
                cit_status = 1
                AND cit_is_del = 0
                AND cit_is_soldout = 0
                AND cit_type3 = 1
            ';


            $_join = '';

            $this->Board_model->_select = 'board.brd_id,board.brd_name,board.brd_image,board.brd_blind,cmall_item.cit_id,cmall_item.cit_name,cmall_item.cit_file_1,cmall_item.cit_review_average,cmall_item.cit_price,cmall_item.cit_price_sale';
            

            $_join = "
                select cit_id,brd_id,cit_order,cit_order1,cit_order2,cit_order3,cit_order4,cit_name,cit_file_1,cit_review_average,cit_price,cit_price_sale,".$sort."cbr_id,cit_version from cb_cmall_item ".$cmallwhere;
        
        if($sattr && is_array($sattr)){
                        
            $sattr_id = array();
            $usattr_id = array();
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($sattr as $cval){
                        if($cval == element('cat_id',$aaval)){
                            $sattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($usattr as $cval){
                        if($cval == element('cat_id',$aaval)){

                            $usattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            
            
            
            $sattr_val = array();
            $usattr_val = array();
            foreach($sattr_id as $skey => $sval){
                foreach($sval as $sval_){
                    array_push($sattr_val,$sval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }

            foreach($usattr_id as $uskey => $usval){
                foreach($usval as $usval_){
                    array_push($usattr_val,$usval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }
            
            
            if(!empty($sattr_val))
                $_join .= " and cit_id in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$sattr_val)."))";
            if(!empty($usattr_val))
                $_join .= " and cit_id not in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$usattr_val)."))";
        }

        if($sattr && is_array($sattr)){
                        
            $sattr_id = array();
            
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($sattr as $cval){
                        if($cval == element('cat_id',$aaval)){
                            $sattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            

            
            
            
            $sattr_val = array();
            
            foreach($sattr_id as $skey => $sval){
                foreach($sval as $sval_){
                    array_push($sattr_val,$sval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }

            
            
            
            if(!empty($sattr_val))
                $_join .= " and cit_id in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$sattr_val)."))";
            
        }

        if($usattr && is_array($usattr)){
                        
            
            $usattr_id = array();
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($usattr as $cval){
                        if($cval == element('cat_id',$aaval)){

                            $usattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            
            
            
            
            $usattr_val = array();
            

            foreach($usattr_id as $uskey => $usval){
                foreach($usval as $usval_){
                    array_push($usattr_val,$usval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }
            
            if(!empty($usattr_val))
                $_join .= " and cit_id not in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$usattr_val)."))";
        }

        if($sattr2 && is_array($sattr2)){
                        
            $sattr2_id = array();
            
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($sattr2 as $cval){
                        if($cval == element('cat_id',$aaval)){
                            $sattr2_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            

            
            
            
            $sattr2_val = array();
            
            foreach($sattr2_id as $skey => $sval){
                foreach($sval as $sval_){
                    array_push($sattr2_val,$sval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }

            
            
            
            if(!empty($sattr2_val))
                $_join .= " and cit_id in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$sattr2_val)."))";
            
        }


        if($skind){

            // $this->Board_model->set_where_in('cmal1l_kind_rel.ckd_id',$skind);
            // $this->Board_model->set_where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);
            $_join .=" and cit_id in (select cit_id from cb_cmall_kind_rel where ckd_id = ".$skind." )" ;

    //         if(empty($sattr))
                // $this->Board_model->set_join(array('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner')); 
            
        }
        
        if(!empty($_join))
            $set_join[] = array("
                (".$_join." ORDER BY RAND()) as cb_cmall_item ",'cmall_item.brd_id = board.brd_id','inner');
        // $field = array(
        //  'board' => array('brd_name'),
        //  'cmall_item' => array('cit_id','cit_name','cit_file_1','cit_review_average','cit_price','cit_price_sale'),
        //  'cmall_brand' => array('cbr_value_kr','cbr_value_en'),
        // );
        
        // $select = get_selected($field);

        // $this->Board_model->select = $select;
        
        if(!empty($set_join)) {
            $this->Board_model->set_join($set_join);
            // $this->Board_model->set_group_by('cmall_item.cit_id');
        }
        $result = $this->Board_model
            ->get_search_list(6,'' , $where,'','','rand()');
        $list_num = $result['total_rows'];
        

        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {
                $result['list'][$key] = $this->denguruapi->convert_cit_info($result['list'][$key]);
                $result['list'][$key] = $this->denguruapi->convert_brd_info($result['list'][$key]);

                // $result['list'][$key]['category']=$this->Cmall_category_model->get_category(element('cit_id',$val));
                // $result['list'][$key]['attr']=$this->Cmall_attr_model->get_attr(element('cit_id',$val));

                // $result['list'][$key]['num'] = $list_num--;
            }
        }

        $result['pet_info'] = $pet_info;

        if ($skind) {
            

            $where = array(
                    'brd_search' => 1,
                    'brd_blind' => 0,
                    'cit_status' => 1,
                    'cit_is_del' => 0,
                    'cit_is_soldout' => 0,
                    'cit_type3' => 1,
                    );
            $where = array('kinditem_group.ckd_id' => $skind);

            $this->Kinditem_group_model->set_where("(kir_start_date <='".cdate('Y-m-d')."' or kir_start_date IS NULL) and (kir_end_date >='".cdate('Y-m-d')."' or kir_end_date IS NULL)");

            $result2 = $this->Kinditem_group_model
                ->get_item_list('','', $where);


            $list_num = $result2['total_rows'];
            if (element('list', $result2)) {
                foreach (element('list', $result2) as $key => $val) {
                    
                    $result2['list'][$key] = $this->denguruapi->convert_cit_info($result2['list'][$key]);
                    $result2['list'][$key] = $this->denguruapi->convert_brd_info($result2['list'][$key]);                    
                }
            }

            $result['list'] = array_merge($result2['list'],$result['list']);
        }
        $result['list']= array_slice($result['list'],0,6);


        
        

        
        $view['view'] = $result;
        
        
        return $view['view'];
        
    }

    

    protected function _itemlists($category_id = 0,$brd_id = 0,$swhere = array(),$config = array())
    {
        

        // $view = array();
        // $view['view'] = array();

        // $this->load->model(array('Board_model'));
        // /**
        //  * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
        //  */
        // $param =& $this->querystring;
        // $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;

        // $alertmessage = $this->member->is_member()
        //  ? '회원님은 상품 목록을 볼 수 있는 권한이 없습니다'
        //  : '비회원은 상품목록에 접근할 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        // $access_list = $this->cbconfig->item('access_cmall_list');
        // $access_list_level = $this->cbconfig->item('access_cmall_list_level');
        // $access_list_group = $this->cbconfig->item('access_cmall_list_group');
        // $this->accesslevel->check(
        //  $access_list,
        //  $access_list_level,
        //  $access_list_group,
        //  $alertmessage,
        //  ''
        // );

        // $findex = ($this->input->get('findex') && in_array($this->input->get('findex'), $allow_order_field)) ? $this->input->get('findex') : 'cit_order asc';
        // $sfield = $this->input->get('sfield', null, '');
        // if ($sfield === 'cit_both') {
        //  $sfield = array('cit_name', 'cit_content');
        // }
        // $skeyword = $this->input->get('skeyword', null, '');

        // $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;

        


        // $offset = ($page - 1) * $per_page;

        // $this->Board_model->allow_search_field = array('brd_name','cit_id', 'cit_name', 'cit_content', 'cit_both', 'cit_price'); // 검색이 가능한 필드
        // $this->Board_model->search_field_equal = array('cit_id'); // 검색중 like 가 아닌 = 검색을 하는 필드

        // /**
        //  * 게시판 목록에 필요한 정보를 가져옵니다.
        //  */
        // $where = array();
        // $where['cit_status'] = 1;
        // $where['brd_blind'] = 0;
        // // $field = array(
        // //   'board' => array('brd_name'),
        // //   'cmall_item' => array('cit_id','cit_name','cit_file_1','cit_review_average','cit_price','cit_price_sale'),
        // //   'cmall_brand' => array('cbr_value_kr','cbr_value_en'),
        // // );
        
        // // $select = get_selected($field);

        // // $this->Board_model->select = $select;

        // $item_ids = $this->input->get('chk_item_id');
        // if($item_ids && is_array($item_ids)){
        //  $this->Board_model->set_where_in('cit_id',$item_ids);
        //  $per_page = 9999;
        //  $offset = '';
        // }

        // if($brd_id){
        //  $where['board.brd_id'] = $brd_id;
        //  $per_page = 18;
        //  $offset = '';
        // }

        // if(element('per_page', $config)){
        //  $per_page = element('per_page', $config);
        //  $offset = ($page - 1) * $per_page;  
        // }
        
        // if($swhere && is_array($swhere)){
        //  foreach($swhere as $skey => $sval){
        //      if(!empty($sval)){
        //          if(is_array($sval) )
        //              $this->Board_model->set_where_in($skey,$sval);
        //          else
        //              $where[$skey] = $sval;
        //      }
        //  }
        // }
        // $result = $this->Board_model
        //  ->get_item_list($per_page, $offset, $where, $category_id, $findex, $sfield, $skeyword);
        // $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        // if (element('list', $result)) {
        //  foreach (element('list', $result) as $key => $val) {

        //      $result['list'][$key] = $this->denguruapi->convert_cit_info($result['list'][$key]);
        //      $result['list'][$key] = $this->denguruapi->convert_brd_info($result['list'][$key]);
        //      $result['list'][$key]['num'] = $list_num--;
        //  }
        // }
        // $view['view'] = $result;
        // if($category_id){
        //  // $view['view']['category_nav'] = $this->cmalllib->get_nav_category($category_id);
        //  // $view['view']['category_all'] = $this->cmalllib->get_all_category();
        //  $view['view']['category_id'] = $category_id;
        // }
        // /**
        //  * 페이지네이션을 생성합니다
        //  */
        // if(empty($brd_id)){
        //  $config['base_url'] = site_url('cmall/itemlists/' . $category_id.'/' . $brd_id) . '?' . $param->replace('citpage');
        //  $config['total_rows'] = $result['total_rows'];
        //  $config['per_page'] = $per_page;
        //  $this->pagination->initialize($config);
        //  // $view['view']['paging'] = $this->pagination->create_links();
        //  $view['view']['next_link'] = $this->pagination->get_next_link();
        //  $view['view']['citpage'] = $citpage;


        // }
        

        
        
        // return $view['view'];
        
    }

    public function itemlists_get($category_id = 0,$brd_id = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_lists';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        
        // if(!is_array($item_ids)) $item_ids = array($item_ids);
        $swhere = array();
        if($this->input->get('chk_item_id')){
            $item_ids = $this->input->get('chk_item_id');
            $swhere  = array('cit_id' => $item_ids);
        }

        $view['view']['data'] = $this->cmalllib->_itemlists($category_id,$brd_id,$swhere);  
            
        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

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
        
        // print_r2($this->data);
        return $this->response($this->data, parent::HTTP_OK);
    }

    protected function _item($cit_id = 0)
    {

        
        
        $cit_id = (int) $cit_id;
        if (empty($cit_id) OR $cit_id < 1) {
            show_404();
        }

        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');

        
        $this->load->model(array('Board_model','Cmall_item_model','Cmall_review_model','Cmall_storewishlist_model','Cmall_wishlist_model','Cmall_category_model'));

        $field = array(
            'board' => array('brd_id','brd_name','brd_image','brd_blind'),
            'cmall_item' => array('cit_id','post_id','cit_name','cit_file_1','cit_review_average','cit_price','cit_price_sale','cit_status','cit_mobile_content','cit_content','cit_content_html_type'),
            'cmall_brand' => array('cbr_id','cbr_value_kr','cbr_value_en'),
        );
        
        $select = get_selected($field);
        
        $this->Board_model->_select = $select;

        $data = $this->Board_model->get_cit_one($cit_id);

        $data = $this->denguruapi->convert_cit_info($data);
        $data = $this->denguruapi->convert_brd_info($data);

        $board_crawl = $this->denguruapi->get_all_crawl(element('brd_id',$data));

        // $data['brd_register_url'] = element('brd_register_url',$board_crawl);    
        // $data['brd_order_url'] = element('brd_order_url',$board_crawl);
        // $data['brd_orderstatus_url'] = element('brd_orderstatus_url',$board_crawl);

        if ( ! element('cit_id', $data)) {
            alert('이 상품은 현재 존재하지 않습니다',"",406);
        }
        if (element('brd_blind', $data)) {
            alert('이 스토어는 현재 운영하지 않습니다.',"",406);
        }
        if ( ! element('cit_status', $data)) {
            alert('이 상품은 현재 판매하지 않습니다',"",406);
        }
        if (!empty(element('cit_is_del', $data))) {
            alert('이 상품은 현재 판매하지 않습니다',"",406);
        }

        // $data['meta'] = $this->Cmall_item_meta_model->get_all_meta(element('cit_id', $data));
        // $data['detail'] = $this->Cmall_item_detail_model->get_all_detail(element('cit_id', $data));

        $alertmessage = $this->member->is_member()
            ? '회원님은 상품 페이지를 볼 수 있는 권한이 없습니다'
            : '비회원은 상품 페이지를 볼 수 있는 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        $access_read = $this->cbconfig->item('access_cmall_read');
        $access_read_level = $this->cbconfig->item('access_cmall_read_level');
        $access_read_group = $this->cbconfig->item('access_cmall_read_group');
        $this->accesslevel->check(
            $access_read,
            $access_read_level,
            $access_read_group,
            $alertmessage,
            ''
        );

        

        // if ( ! $this->cb_jwt->userdata('cmall_item_id_' . element('cit_id', $data))) {
            // $this->Cmall_item_model->update_hit(element('cit_id', $data));
        //  $this->cb_jwt->set_userdata(
        //      'cmall_item_id_' . element('cit_id', $data),
        //      '1'
        //  );
        // }
        if ( ! $this->cb_jwt->userdata('cit_inlink_click_' . element('cit_id', $data))) {

            $this->cb_jwt->set_userdata(
                'cit_inlink_click_' . element('cit_id', $data),
                '1'
            );

            
            if($mem_id){
                $insertdata = array(
                    // 'pln_id' => element('pln_id', $data),
                    'post_id' => element('post_id', $data),
                    'brd_id' => element('brd_id', $data),
                    'cit_id' => element('cit_id', $data),
                    'clc_datetime' => cdate('Y-m-d H:i:s'),
                    'clc_ip' => $this->input->ip_address(),
                    'clc_useragent' => $this->agent->agent_string(),
                    'mem_id' => $mem_id,
                );

                $this->load->model('Crawl_link_click_log_model');
                $this->Crawl_link_click_log_model->insert($insertdata);
            }   
            
            $this->Cmall_item_model->update_hit(element('cit_id', $data));

            // $this->_stat_count_board(element('brd_id', $data));
        }

        $data['display_content'] = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? (
                    element('cit_mobile_content', $data)
                    ? element('cit_mobile_content', $data)
                    : element('cit_content', $data)
                )
            : element('cit_content', $data);
        $thumb_width = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_mobile_thumb_width')
            : $this->cbconfig->item('cmall_product_thumb_width');
        $autolink = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('use_cmall_product_mobile_auto_url')
            : $this->cbconfig->item('use_cmall_product_auto_url');
        $popup = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_mobile_content_target_blank')
            : $this->cbconfig->item('cmall_product_content_target_blank');
        $data['display_content'] = display_html_content(
            element('display_content', $data),
            element('cit_content_html_type', $data),
            $thumb_width,
            $autolink,
            $popup,
            $writer_is_admin = true
        );

        // $data['header_content'] = ($this->cbconfig->get_device_view_type() === 'mobile')
        //  ? display_html_content(element('mobile_header_content', element('meta', $data)), 1, $thumb_width)
        //  : display_html_content(element('header_content', element('meta', $data)), 1, $thumb_width);

        // $data['footer_content'] = ($this->cbconfig->get_device_view_type() === 'mobile')
        //  ? display_html_content(element('mobile_footer_content', element('meta', $data)), 1, $thumb_width)
        //  : display_html_content(element('footer_content', element('meta', $data)), 1, $thumb_width);

        $data = $this->denguruapi->get_wish_info($data);

        $data['reviewlist_url'] = base_url('cmall_review/reviewlist/'.element('cit_id',$data));
        $data['cit_review_count'] = $this->Cmall_review_model->count_by(array('cit_id' => element('cit_id',$data)));    
        // $data['reviewscore'] = $this->Cmall_review_model->get_review_count(element('cit_id',$data));
        $data['popularreview'] = $this->denguruapi->get_popular_item_review(element('cit_id',$data));

        $data['ai_keyword'] = array();
        $ai_keyword = $this->denguruapi->get_popular_cit_tags(element('cit_id',$data));

        if($ai_keyword && is_array($ai_keyword))
            foreach($ai_keyword as $val){
                $data['ai_keyword'][] = array($val,base_url('search/show_list/0?skeyword='.$val)); 
            }
        $get_category = $this->Cmall_category_model->get_category(element('cit_id', $data));
        $cca_id_arr =array();
        if($get_category){
            foreach($get_category as $gval){
                array_push($cca_id_arr,element('cca_id',$gval));
            }
        }
        

        $data['similaritemlist_similar'] = $this->cmalllib->_itemlists(element(0,$cca_id_arr),element('brd_id',$data),'',array('per_page' => 6));
        $data['similaritemlist_type1'] = $this->cmalllib->_itemlists('',element('brd_id',$data),array('cit_type1' => 1),array('per_page' => 6));
        $data['similaritemlist_type3'] = $this->cmalllib->_itemlists('',element('brd_id',$data),array('cit_type3' => 1),array('per_page' => 6));

        $view['view']['data'] = $data;
        

        
        
        
        return $view['view'];
    }

    public function item_get($cit_id = 0)
    {

        
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_item';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        

        
        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        

        $view['view'] = $this->_item($cit_id);
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

    public function itemwish_post($cit_id = 0,$stype='wish')
    {

        
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_item';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        required_user_login();  
        $mem_id = (int) $this->member->item('mem_id');

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
        
        if (empty($cit_id) || empty($stype)) {
            show_404();
        }
        $this->load->model(array('Cmall_item_model'));

        $where = array(
            'cit_id' => $cit_id,
        );
        $data = $this->Cmall_item_model->get_one($cit_id);
        if ( ! element('cit_id', $data)) {
            alert('이 상품은 현재 존재하지 않습니다',"",406);
        }
        
        if ( ! element('cit_status', $data)) {
            alert('이 상품은 현재 판매하지 않습니다',"",406);
        }
        if (!empty(element('cit_is_del', $data))) {
            alert('이 상품은 현재 판매하지 않습니다',"",406);
        }
        // $data['meta'] = $this->Cmall_item_meta_model->get_all_meta(element('cit_id', $data));
        // $data['detail'] = $this->Cmall_item_detail_model->get_all_detail(element('cit_id', $data));

        $alertmessage = $this->member->is_member()
            ? '회원님은 상품 페이지를 볼 수 있는 권한이 없습니다'
            : '비회원은 상품 페이지를 볼 수 있는 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        $access_read = $this->cbconfig->item('access_cmall_read');
        $access_read_level = $this->cbconfig->item('access_cmall_read_level');
        $access_read_group = $this->cbconfig->item('access_cmall_read_group');
        $this->accesslevel->check(
            $access_read,
            $access_read_level,
            $access_read_group,
            $alertmessage,
            ''
        );

        if ($stype) {
            if ( ! $mem_id) {
                alert(
                    '로그인 후 이용이 가능합니다',
                    '',
                    403
                );
                
            }

            

            if ($stype === 'wish') {
                $return = $this->cmalllib->addwish($mem_id, $cit_id);
                if ($return) {
                    $result = array('msg' => 'success');
                    $view['view']=$result;
                }
            } elseif ($stype === 'cart'
                // && $this->input->post('chk_detail')
                // && is_array($this->input->post('chk_detail'))
                && $this->input->post('detail_qty')) {
                $return = $this->cmalllib->addcart(
                    $mem_id,
                    $cit_id,
                    '',
                    $this->input->post('detail_qty')
                );
                if ($return) {
                    $result = array('msg' => 'success');
                    $view['view']=$result;
                }
            } elseif ($stype === 'order'
                // && $this->input->post('chk_detail')
                // && is_array($this->input->post('chk_detail'))
                && $this->input->post('detail_qty')) {
                $return = $this->cmalllib->addorder(
                    $mem_id,
                    $cit_id,
                    '',
                    $this->input->post('detail_qty')
                );
                if ($return) {
                    $result = array('msg' => 'success');
                    $view['view']=$result;
                }
            }
        }

        

        
        $this->data = $view['view'];
        
        return $this->response($this->data, parent::HTTP_OK);
    }

    public function itemwish_delete($cit_id = 0,$stype ='wish')
    {

        
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_item';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
        
        if (empty($cit_id) || empty($stype)) {
            show_404();
        }
        $this->load->model(array('Cmall_item_model'));

        $where = array(
            'cit_id' => $cit_id,
        );
        $data = $this->Cmall_item_model->get_one($cit_id);
        if ( ! element('cit_id', $data)) {
            alert('이 상품은 현재 존재하지 않습니다',"",406);
        }
        if (!empty(element('cit_is_del', $data))) {
            alert('이 상품은 현재 판매하지 않습니다',"",406);
        }
        if ( ! element('cit_status', $data)) {
            alert('이 상품은 현재 판매하지 않습니다',"",406);
        }

        // $data['meta'] = $this->Cmall_item_meta_model->get_all_meta(element('cit_id', $data));
        // $data['detail'] = $this->Cmall_item_detail_model->get_all_detail(element('cit_id', $data));

        $alertmessage = $this->member->is_member()
            ? '회원님은 상품 페이지를 볼 수 있는 권한이 없습니다'
            : '비회원은 상품 페이지를 볼 수 있는 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        $access_read = $this->cbconfig->item('access_cmall_read');
        $access_read_level = $this->cbconfig->item('access_cmall_read_level');
        $access_read_group = $this->cbconfig->item('access_cmall_read_group');
        $this->accesslevel->check(
            $access_read,
            $access_read_level,
            $access_read_group,
            $alertmessage,
            ''
        );

        if ($stype) {
            if ( ! $mem_id) {
                alert(
                    '로그인 후 이용이 가능합니다',
                    '',
                    403
                );
                
            }

            

            if ($stype === 'wish') {
                $return = $this->cmalllib->delwish($mem_id, $cit_id);
                if ($return) {
                    $result = array('msg' => 'success');
                    $view['view']=$result;
                }
            } elseif ($stype === 'cart'
                // && $this->input->post('chk_detail')
                // && is_array($this->input->post('chk_detail'))
                && $this->input->post('detail_qty')) {
                $return = $this->cmalllib->addcart(
                    $mem_id,
                    $cit_id,
                    '',
                    $this->input->post('detail_qty')
                );
                if ($return) {
                    $result = array('msg' => 'success');
                    $view['view']=$result;
                }
            } elseif ($stype === 'order'
                // && $this->input->post('chk_detail')
                // && is_array($this->input->post('chk_detail'))
                && $this->input->post('detail_qty')) {
                $return = $this->cmalllib->addorder(
                    $mem_id,
                    $cit_id,
                    '',
                    $this->input->post('detail_qty')
                );
                if ($return) {
                    $result = array('msg' => 'success');
                    $view['view']=$result;
                }
            }
        }

        

        
        $this->data = $view['view'];
        
        return $this->response($this->data, 204);
    }

    public function storewish_post($brd_id = 0,$stype='store')
    {

        
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_store';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        required_user_login();
        $mem_id = (int) $this->member->item('mem_id');
        
        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        
        if (empty($brd_id ) || empty($stype)) {
            show_404();
        }

        

        $board = $this->board->item_all($brd_id);

        if (empty(element('brd_id', $board))) {
            alert('없는 스토어입니다.',"",406);
        }

        if (element('brd_blind', $board)) {
            alert('이 스토어는 현재 운영하지 않습니다',"",406);
        }

        // $data['meta'] = $this->Cmall_item_meta_model->get_all_meta(element('cit_id', $data));
        // $data['detail'] = $this->Cmall_item_detail_model->get_all_detail(element('cit_id', $data));

        $alertmessage = $this->member->is_member()
            ? '회원님은 상품 페이지를 볼 수 있는 권한이 없습니다'
            : '비회원은 상품 페이지를 볼 수 있는 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        $check = array(
            'group_id' => element('bgr_id', $board),
            'board_id' => element('brd_id', $board),
        );
        $this->accesslevel->check(
            element('access_write', $board),
            element('access_write_level', $board),
            element('access_write_group', $board),
            $alertmessage,
            $check
        );

        if ($stype) {
            if ( ! $mem_id) {
                alert(
                    '로그인 후 이용이 가능합니다',"",403
                );
                
            }

            

            if ($stype === 'store') {
                $return = $this->cmalllib->addstore($mem_id, $brd_id);  
                if ($return) {
                    $result = array('msg' => 'success');
                    $view['view']=$result;
                }           
            } 
        }

        // if ( ! $this->cb_jwt->userdata('cmall_item_id_' . element('cit_id', $data))) {
            // $this->Cmall_item_model->update_hit(element('cit_id', $data));
        //  $this->cb_jwt->set_userdata(
        //      'cmall_item_id_' . element('cit_id', $data),
        //      '1'
        //  );
        // }

        
        $this->data = $view['view'];
        
        return $this->response($this->data, parent::HTTP_OK);
    }

    public function storewish_delete($brd_id = '',$stype='store')
    {

        
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_store';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        
        if (empty($brd_id ) || empty($stype)) {
            show_404();
        }

        

        $board = $this->board->item_all($brd_id);

        
        if (element('brd_blind', $board)) {
            // alert('이 스토어는 현재 운영하지 않습니다',"",406);
        }

        // $data['meta'] = $this->Cmall_item_meta_model->get_all_meta(element('cit_id', $data));
        // $data['detail'] = $this->Cmall_item_detail_model->get_all_detail(element('cit_id', $data));

        $alertmessage = $this->member->is_member()
            ? '회원님은 페이지를 볼 수 있는 권한이 없습니다'
            : '비회원은 페이지를 볼 수 있는 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        $check = array(
            'group_id' => element('bgr_id', $board),
            'board_id' => element('brd_id', $board),
        );
        $this->accesslevel->check(
            element('access_write', $board),
            element('access_write_level', $board),
            element('access_write_group', $board),
            $alertmessage,
            $check
        );

        if ($stype) {
            if ( ! $mem_id) {
                alert(
                    '로그인 후 이용이 가능합니다',"",403
                );
                
            }

            

            if ($stype === 'store') {
                $return = $this->cmalllib->delstore($mem_id, $brd_id);  
                if ($return) {
                    $result = array('msg' => 'success');
                    $view['view']=$result;
                }           
            } 
        }

        // if ( ! $this->cb_jwt->userdata('cmall_item_id_' . element('cit_id', $data))) {
            // $this->Cmall_item_model->update_hit(element('cit_id', $data));
        //  $this->cb_jwt->set_userdata(
        //      'cmall_item_id_' . element('cit_id', $data),
        //      '1'
        //  );
        // }

        
        $this->data = $view['view'];
        
        return $this->response($this->data, 204);
    }

    public function cartoption_post()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_cartoption';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $cit_id = (int) $this->input->post('cit_id');
        if (empty($cit_id) OR $cit_id < 1) {
            show_404();
        }

        if ($this->member->is_member() === false) {
            show_404();
        }
        $mem_id = (int) $this->member->item('mem_id');

        $this->load->model(array('Cmall_item_model', 'Cmall_item_detail_model', 'Cmall_cart_model'));

        $item = $this->Cmall_item_model->get_one($cit_id);
        if ( ! element('cit_id', $item)) {
            show_404();
        }

        $detail = $this->Cmall_item_detail_model->get_all_cart_detail($cit_id);
        if ($detail) {
            foreach ($detail as $key => $value) {
                $detail[$key]['cart'] = $this->Cmall_cart_model
                    ->get_item_is_cart(element('cde_id', $value), $mem_id);
            }
        }

        if ( ! element('cit_id', $item)) {
            show_404();
        }

        $view['view']['item'] = $item;
        $view['view']['detail'] = $detail;

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

        /**
         * 레이아웃을 정의합니다
         */
        $skindir = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('mobile_skin_cmall')
            : $this->cbconfig->item('skin_cmall');
        if (empty($skindir)) {
            $skindir = ($this->cbconfig->get_device_view_type() === 'mobile')
                ? $this->cbconfig->item('mobile_skin_default')
                : $this->cbconfig->item('skin_default');
        }
        if (empty($skindir)) {
            $skindir = 'basic';
        }
        $skin = 'cmall/' . $skindir . '/cartoption';

        $this->data = $view;
        $this->view = $skin;
    }


    public function itemimage_get($cit_id = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_itemimage';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        if (empty($cit_id)) {
            show_404();
        }
        $this->load->model(array('Cmall_item_model'));

        $where = array(
            'cit_id' => $cit_id,
        );
        $data = $this->Cmall_item_model->get_one('', '', $where);
        if ( ! element('cit_id', $data)) {
            show_404();
        }

        $view['view']['data'] = $data;

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

        /**
         * 레이아웃을 정의합니다
         */
        $page_title = element('cit_name', $data) . ' > ' . $this->cbconfig->item('cmall_name') . ' 이미지 크게보기';
        $layoutconfig = array(
            'path' => 'cmall',
            'layout' => 'layout_popup',
            'skin' => 'itemimage',
            'layout_dir' => $this->cbconfig->item('layout_cmall'),
            'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_cmall'),
            'use_sidebar' => $this->cbconfig->item('sidebar_cmall'),
            'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_cmall'),
            'skin_dir' => $this->cbconfig->item('skin_cmall'),
            'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_cmall'),
            'page_title' => $page_title,
        );
        $view['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        $this->data = $view;
        $this->layout = element('layout_skin_file', element('layout', $view));
        $this->view = element('view_skin_file', element('layout', $view));
    }


    public function cart_post()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_cart';
        //$this->load->event($eventname);

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();

        $mem_id = (int) $this->member->item('mem_id');

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $this->load->model(array('Cmall_cart_model'));

        if ($this->input->post('chk')) {
            $cit_id = $this->input->post('chk');
            $return = $this->cmalllib->cart_to_order(
                $mem_id,
                $cit_id
            );
            if ($return) {
                redirect('cmall/order');
            }
        }

        $cachename = 'delete_old_cart_cache';
        $cachetime = 3600;
        if ( ! $result = $this->cache->get($cachename)) {
            $days = $this->cbconfig->item('cmall_cart_keep_days')
                ? $this->cbconfig->item('cmall_cart_keep_days') : 14;
            $cartdays = cdate('Y-m-d H:i:s', ctimestamp() - $days * 86400);
            $deletewhere = array(
                'cct_datetime <' => $cartdays,
            );
            $this->Cmall_cart_model->delete_where($deletewhere);
            $this->cache->save($cachename, cdate('Y-m-d H:i:s'), $cachetime);
        }


        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $findex = 'cmall_item.cit_id';
        $forder = 'desc';

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        $where['cmall_cart.mem_id'] = $mem_id;
        $result = $this->Cmall_cart_model->get_cart_list($where, $findex, $forder);
        if ($result) {
            foreach ($result as $key => $val) {
                $result[$key]['cit_inlink_url'] = cmall_item_url(element('cit_id', $val));
                $result[$key]['detail'] = $this->Cmall_cart_model
                    ->get_cart_detail($mem_id, element('cit_id', $val));
            }
        }
        $view['view']['data'] = $result;
        $view['view']['list_delete_url'] = site_url('cmallact/cart_delete/?' . $param->output());

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

        /**
         * 레이아웃을 정의합니다
         */
        $page_title = $this->cbconfig->item('site_meta_title_cmall_cart');
        $meta_description = $this->cbconfig->item('site_meta_description_cmall_cart');
        $meta_keywords = $this->cbconfig->item('site_meta_keywords_cmall_cart');
        $meta_author = $this->cbconfig->item('site_meta_author_cmall_cart');
        $page_name = $this->cbconfig->item('site_page_name_cmall_cart');

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
            'skin' => 'cart',
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
        $view['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        $this->data = $view;
        $this->layout = element('layout_skin_file', element('layout', $view));
        $this->view = element('view_skin_file', element('layout', $view));
    }


    /**
     * 주문하기 입니다
     */
    public function order_get()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_order';
        //$this->load->event($eventname);

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();

        $mem_id = (int) $this->member->item('mem_id');

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $alertmessage = $this->member->is_member()
            ? '회원님은 상품을 구매할 수 있는 권한이 없습니다'
            : '비회원은 상품을 구매할 수 있는 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        $access_buy = $this->cbconfig->item('access_cmall_buy');
        $access_buy_level = $this->cbconfig->item('access_cmall_buy_level');
        $access_buy_group = $this->cbconfig->item('access_cmall_buy_group');
        $this->accesslevel->check(
            $access_buy,
            $access_buy_level,
            $access_buy_group,
            $alertmessage,
            ''
        );

        $this->load->model(array('Cmall_cart_model'));

        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $findex = 'cmall_item.cit_id';
        $forder = 'desc';

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        $where['cmall_cart.mem_id'] = $mem_id;
        $result = $this->Cmall_cart_model->get_order_list($where, $findex, $forder);
        $good_name = '';
        $good_count = -1;
        $jwt_cct_id = array();
        if ($result) {
            foreach ($result as $key => $val) {
                $result[$key]['cit_inlink_url'] = cmall_item_url(element('cit_id', $val));
                $result[$key]['detail'] = $this->Cmall_cart_model
                    ->get_order_detail($mem_id, element('cit_id', $val));
                if (empty($good_name)) {
                    $good_name = element('cit_name', $val);
                }
                $good_count ++;
                $jwt_cct_id[] = element('cct_id', $val);
            }
        }
        $view['view']['data'] = $result;

        $this->load->model('Unique_id_model');
        $unique_id = $this->Unique_id_model->get_id($this->input->ip_address());
        $view['view']['unique_id'] = $unique_id;
        $view['view']['good_name'] = $good_name;
        if ($good_count > 0) {
            $view['view']['good_name'] .= ' 외 ' . $good_count . '건';
        }
        $this->cb_jwt->set_userdata(
            'unique_id',
            $unique_id
        );
        $this->cb_jwt->set_userdata(
            'order_cct_id',
            implode('-', $jwt_cct_id)
        );

        $view['view']['use_pg'] = $use_pg = false;
        if ($this->cbconfig->item('use_payment_card')
            OR $this->cbconfig->item('use_payment_realtime')
            OR $this->cbconfig->item('use_payment_vbank')
            OR $this->cbconfig->item('use_payment_phone')
            OR $this->cbconfig->item('use_payment_easy')) {
            $view['view']['use_pg'] = $use_pg = true;
        }


        if ($this->cbconfig->item('use_payment_pg') === 'kcp' && $use_pg) {
            $this->load->library('paymentlib');
            $view['view']['pg'] = $this->paymentlib->kcp_init();
            /*   //삭제예정
            if ($this->cbconfig->get_device_type() !== 'mobile') {
                $view['view']['body_script'] = 'onLoad="CheckPayplusInstall();"';
            }
            */
        }

        if ($this->cbconfig->item('use_payment_pg') === 'lg' && $use_pg) {
            $this->load->library('paymentlib');
            $view['view']['pg'] = $this->paymentlib->lg_init();
            /*   //삭제예정
            if ($this->cbconfig->get_device_type() !== 'mobile') {
                $view['view']['body_script'] = 'onload="isActiveXOK();"';
            }
            */
        }

        if ($this->cbconfig->item('use_payment_pg') === 'inicis' && $use_pg) {
            $this->load->library('paymentlib');
            $view['view']['pg'] = $this->paymentlib->inicis_init();
            /* //삭제예정
            if ($this->cbconfig->get_device_type() !== 'mobile') {
                $view['view']['body_script'] = 'onload="enable_click();"';
            }
            */
        }

        $view['view']['ptype'] = 'cmall';

        $view['view']['form1name'] = ($this->cbconfig->get_device_type() === 'mobile') ? 'mform_1' : 'form_1';
        $view['view']['form2name'] = ($this->cbconfig->get_device_type() === 'mobile') ? 'mform_2' : 'form_2';
        $view['view']['form3name'] = ($this->cbconfig->get_device_type() === 'mobile') ? 'mform_3' : 'form_3';
        $view['view']['form4name'] = ($this->cbconfig->get_device_type() === 'mobile') ? 'mform_4' : 'form_4';

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

        /**
         * 레이아웃을 정의합니다
         */
        $page_title = $this->cbconfig->item('site_meta_title_cmall_order');
        $meta_description = $this->cbconfig->item('site_meta_description_cmall_order');
        $meta_keywords = $this->cbconfig->item('site_meta_keywords_cmall_order');
        $meta_author = $this->cbconfig->item('site_meta_author_cmall_order');
        $page_name = $this->cbconfig->item('site_page_name_cmall_order');

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
            'skin' => 'order',
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
        $view['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
        $this->data = $view;
        $this->layout = element('layout_skin_file', element('layout', $view));
        $this->view = element('view_skin_file', element('layout', $view));
    }


    public function orderresult_get($cor_id = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_orderresult';
        //$this->load->event($eventname);

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();

        $this->load->library(array('paymentlib'));
        $mem_id = (int) $this->member->item('mem_id');

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        if (empty($cor_id) OR $cor_id < 1) {
            show_404();
        }

        $this->load->model(array('Cmall_item_model', 'Cmall_order_model', 'Cmall_order_detail_model'));

        $order = $this->Cmall_order_model->get_one($cor_id);
        if ( ! element('cor_id', $order)) {
            show_404();
        }
        if ($this->member->is_admin() === false
            && (int) element('mem_id', $order) !== $mem_id) {
            alert('잘못된 접근입니다',"",400);
        }

        $board_crawl = $this->denguruapi->get_all_crawl(element('brd_id',$order));

        $result['cor_id'] = element('cor_id',$order);
        $result['brd_info']  = $this->denguruapi->get_brd_info(element('brd_id', $order));
        $result['brd_info']['brd_phone']  = element('brd_phone',$board_crawl);
        $result['cor_memo'] = element('cor_memo',$order);
        $result['cor_total_money'] = element('cor_total_money',$order);
        $result['cor_content'] = element('cor_content',$order);
        $result['cor_pay_type'] = element('cor_pay_type',$order);

        $result['cor_order_history'] = $cor_order_history = array();
        if(element('cor_order_history',$order)){
            $cor_order_history_ = explode("\n",element('cor_order_history',$order));
            
            
 
            
            foreach($cor_order_history_ as  $val){
                
                $new_names ='';
                $new_names = trim(preg_replace('/[^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}a-zA-Z\s]/u', "", $val));
            
            

                $cor_order_history[$new_names] = $val;
            }
            
            foreach($cor_order_history as $val){
                array_push($result['cor_order_history'],$val);
            }

            
            
            



            
        }

        $result['cor_order_delete_url'] = base_url('cmall/orderresult/'.element('cor_id',$order));


        $result['brd_site_type'] = element('brd_site_type',$board_crawl) ;
        $result['brd_nomember_order_url'] = element('brd_nomember_order_url',$board_crawl) ;

        $order_crawl = $this->Cmall_order_model->get_one(element('cor_id',$order), 'cor_id,brd_id,cor_key,cor_pay_type');

        $param =& $this->querystring;
        $brd_url_key_ = parse_url(trim(element('brd_url_key',$board_crawl)));

        

        if(element('brd_order_key',$board_crawl)==='sixshop' || element('brd_order_key',$board_crawl)==='parse'){
            $result['brd_orderstatus_url'] = element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).element('cor_key',$order_crawl);
        } else {
            $result['brd_orderstatus_url'] = element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).'?'.$param->replace(element('brd_order_key',$board_crawl),element('cor_key',$order_crawl),element('query',$brd_url_key_));
        }

        if(element('cor_pay_type',$order_crawl) =='naverpay'){
            $brd_url_key_ = parse_url(trim('https://m.pay.naver.com/o/orderStatus'));
            $result['brd_orderstatus_url'] = element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).element('cor_key',$order_crawl);
        }
        
        $result_=array();
        $orderdetail = $this->Cmall_order_detail_model->get_by_item(element('cor_id',$order));
        if ($orderdetail) {
            foreach ($orderdetail as $value) {
                $result_['item'][] 
                    = $this->denguruapi->get_cit_info(element('cit_id', $value));
                
            }
        }

       
        

        $view['view']['data']['orderresult'] = $result;
        $view['view']['data']['orderdetail'] = $result_;


        $this->data = $view['view'];
        
        return $this->response($this->data, parent::HTTP_OK);
    }


    public function inicisweb()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_payment_inicis_pc_pay';
        //$this->load->event($eventname);

        // 이벤트가 존재하면 실행합니다
        // Events::trigger('before', $eventname);

        $this->load->library(array('paymentlib'));
        $init = $this->paymentlib->inicis_init();

        if( 'inicis' !== $this->cbconfig->item('use_payment_pg') ){
            die(json_encode(array('error'=>'올바른 방법으로 이용해 주십시오.')));
        }

        $request_mid = $this->input->post('mid', null, '');
        $jwt_order_num = $this->cb_jwt->userdata('unique_id');

        if( ($request_mid != element('pg_inicis_mid', $init)) || ! $jwt_order_num ){
            alert("잘못된 요청입니다.","",400);
        }

        $orderNumber = $this->input->post('orderNumber', true, 0);

        if( !$orderNumber ){
            alert("주문번호가 없습니다.","",400);
        }

        $this->load->model('Payment_order_data_model');
        $row = $this->Payment_order_data_model->get_one($orderNumber);
        $params = array();
        $data = cmall_tmp_replace_data($row['pod_data']);

        if( !$data ){
            alert("임시 주문 정보가 저장되지 않았습니다. \\n 다시 실행해 주세요.","",500);
        }

        foreach($data as $key=>$value) {
            if(is_array($value)) {
                foreach($value as $k=>$v) {
                    $_POST[$key][$k] = $params[$key][$k] = $v;
                }
            } else {
                $_POST[$key] = $params[$key] = $value;
            }
        }

        // 이벤트가 존재하면 실행합니다
        // Events::trigger('after', $eventname);

        $this->orderupdate();
    }

    /**
     * 주문 업데이트 함수입니다
     */
    public function orderupdate($agent_type='')
    {
        if( 'mobile' == $agent_type && $this->cbconfig->item('use_payment_pg') === 'inicis' && ($unique_id = $this->cb_jwt->userdata('unique_id')) && $exist_order = get_cmall_order_data($unique_id) ){    //상품주문
            exists_inicis_cmall_order($unique_id, array(), $exist_order['cor_datetime']);
            exit;
        }

        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_orderupdate';
        //$this->load->event($eventname);

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();

        $mem_id = (int) $this->member->item('mem_id');

        // 이벤트가 존재하면 실행합니다
        // Events::trigger('before', $eventname);

        if ('bank' != $this->input->post('pay_type') && $this->cbconfig->item('use_payment_pg') === 'lg'
            && ! $this->input->post('LGD_PAYKEY')) {
            alert('결제등록 요청 후 주문해 주십시오');
        }

        if ( ! $this->cb_jwt->userdata('unique_id') OR ! $this->input->post('unique_id') OR $this->cb_jwt->userdata('unique_id') !== $this->input->post('unique_id')) {
            alert('잘못된 접근입니다');
        }
        if ( ! $this->cb_jwt->userdata('order_cct_id')) {
            alert('잘못된 접근입니다');
        }

        $this->load->model('Cmall_cart_model');
        $where = array();
        $where['cmall_cart.mem_id'] = $mem_id;
        $findex = 'cmall_item.cit_id';
        $forder = 'desc';
        $jwt_cct_id = array();

        $good_mny = $this->input->post('good_mny', null, 0);    //request 값으로 받은 값
        $item_cct_price = 0;        //주문한 상품의 총 금액의 초기화

        $orderlist = $this->Cmall_cart_model->get_order_list($where, $findex, $forder);
        if ($orderlist) {
            foreach ($orderlist as $key => $val) {
                $details = $this->Cmall_cart_model->get_order_detail($mem_id, element('cit_id', $val));

                if( !empty($details) ){
                    foreach((array) $details as $detail ){
                        if( empty($detail) ) continue;

                        $item_cct_price += ((int) element('cit_price', $val) + (int) element('cde_price', $detail)) * element('cct_count', $detail);
                    }
                }

                $jwt_cct_id[] = element('cct_id', $val);
            }
        }

        if ( $item_cct_price != $good_mny ){
        }

        if ($this->cb_jwt->userdata('order_cct_id') !== implode('-', $jwt_cct_id)) {
            alert('결제 내역이 상이합니다, 관리자에게 문의하여주세요');
        }

        if ( ! is_numeric($this->input->post('order_deposit'))) {
            alert(html_escape($this->cbconfig->item('deposit_name')) . ' 의 값은 숫자만 와야 합니다');
        }
        if ( ! is_numeric($this->input->post('total_price_sum'))) {
            alert('총 결제금액의 값은 숫자만 와야 합니다');
        }
        $order_deposit = (int) $this->input->post('order_deposit');
        $total_price_sum = (int) $this->input->post('total_price_sum');
        if ($order_deposit) {
            if ($order_deposit < 0) {
                alert(html_escape($this->cbconfig->item('deposit_name')) . ' 의 값은 0 보다 작을 수 없습니다 ', site_url('cmall/order'));
            }
            if ($order_deposit > $total_price_sum) {
                alert(html_escape($this->cbconfig->item('deposit_name')) . ' 의 값은 총 결제금액보다 클 수 없습니다', site_url('cmall/order'));
            }
            if ($order_deposit > (int) $this->member->item('total_deposit')) {
                alert(html_escape($this->cbconfig->item('deposit_name')) . ' 값이 회원님이 보유하고 계신 값보다 큰 값이 입력되어서 진행할 수 없습니다', site_url('cmall/order'));
            }
        }


        $this->load->library('paymentlib');

        $insertdata = array();
        $result = '';
        $od_status = 'order'; //주문상태

        if ($this->input->post('pay_type') === 'bank') {        //무통장입금
            $insertdata['cor_datetime'] = date('Y-m-d H:i:s');
            $insertdata['mem_realname'] = $this->input->post('mem_realname', null, '');
            $insertdata['cor_total_money'] = $total_price_sum;
            $insertdata['cor_cash_request'] = $this->input->post('good_mny', null, 0);
            $insertdata['cor_deposit'] = $order_deposit;
            $insertdata['cor_cash'] = 0;

            /*   //request 요청값으로 체크하면 안됨
            if ($this->input->post('good_mny')) {
            }
            */

            if ( ((int) $item_cct_price - (int) $order_deposit ) != 0 ) {
                $insertdata['cor_status'] = 0;
                $insertdata['cor_approve_datetime'] = null;
            } else {
                $insertdata['cor_status'] = 1;
                $insertdata['cor_approve_datetime'] = date('Y-m-d H:i:s');
                $od_status = 'deposit'; //주문상태
            }

        } elseif ($this->input->post('pay_type') === 'realtime') {
            if ($this->cbconfig->item('use_payment_pg') === 'kcp') {
                $result = $this->paymentlib->kcp_pp_ax_hub();
            } elseif ($this->cbconfig->item('use_payment_pg') === 'lg') {
                $result = $this->paymentlib->xpay_result();
            } elseif ($this->cbconfig->item('use_payment_pg') === 'inicis') {
                $result = $this->paymentlib->inipay_result($agent_type);
            }

            $insertdata['cor_tno'] = element('tno', $result);
            $insertdata['cor_app_no'] = element('app_no', $result) ? element('app_no', $result) : '';
            $insertdata['cor_datetime'] = date('Y-m-d H:i:s');
            $insertdata['cor_approve_datetime'] = preg_replace(
                "/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/",
                "\\1-\\2-\\3 \\4:\\5:\\6",
                element('app_time', $result)
            );
            $insertdata['cor_total_money'] = $total_price_sum;
            $insertdata['cor_cash_request'] = element('amount', $result);
            $insertdata['cor_deposit'] = $order_deposit;
            $insertdata['cor_cash'] = $cor_cash = element('amount', $result);
            $insertdata['cor_status'] = 1;
            $insertdata['mem_realname'] = $this->input->post('mem_realname', null, '');
            $insertdata['cor_pg'] = $this->cbconfig->item('use_payment_pg');

            if ( ((int) $item_cct_price - (int) $order_deposit - $cor_cash) == 0 ) {
                $od_status = 'deposit'; //주문상태
            }

         } elseif ($this->input->post('pay_type') === 'vbank') {
            if ($this->cbconfig->item('use_payment_pg') === 'kcp') {
                $result = $this->paymentlib->kcp_pp_ax_hub();

                $result['bankname'] = iconv("cp949", "utf-8", $result['bankname']);
                $result['depositor'] = iconv("cp949", "utf-8", $result['depositor']);

            } elseif ($this->cbconfig->item('use_payment_pg') === 'lg') {
                $result = $this->paymentlib->xpay_result();
            } elseif ($this->cbconfig->item('use_payment_pg') === 'inicis') {
                $result = $this->paymentlib->inipay_result($agent_type);
            }

            $insertdata['cor_tno'] = element('tno', $result);
            $insertdata['cor_app_no'] = element('app_no', $result);
            $insertdata['cor_datetime'] = date('Y-m-d H:i:s');
            $insertdata['cor_total_money'] = $total_price_sum;
            $insertdata['cor_cash_request'] = element('amount', $result);
            $insertdata['cor_deposit'] = $order_deposit;
            $insertdata['cor_cash'] = 0;
            $insertdata['cor_status'] = 0;
            $insertdata['mem_realname'] = element('depositor', $result);
            $insertdata['cor_vbank_expire'] = element('cor_vbank_expire', $result) ? date("Y-m-d", strtotime(element('cor_vbank_expire', $result))) : '0000-00-00 00:00:00';
            $insertdata['cor_bank_info'] = element('bankname', $result) . ' ' . element('account', $result);
            $insertdata['cor_pg'] = $this->cbconfig->item('use_payment_pg');
        } elseif ($this->input->post('pay_type') === 'phone') {
            if ($this->cbconfig->item('use_payment_pg') === 'kcp') {
                $result = $this->paymentlib->kcp_pp_ax_hub();
            } elseif ($this->cbconfig->item('use_payment_pg') === 'lg') {
                $result = $this->paymentlib->xpay_result();
            } elseif ($this->cbconfig->item('use_payment_pg') === 'inicis') {
                $result = $this->paymentlib->inipay_result($agent_type);
            }

            $insertdata['cor_tno'] = element('tno', $result);
            $insertdata['cor_app_no'] = element('commid', $result) . ' ' . element('mobile_no', $result);
            $insertdata['cor_datetime'] = date('Y-m-d H:i:s');
            $insertdata['cor_approve_datetime'] = preg_replace(
                "/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/",
                "\\1-\\2-\\3 \\4:\\5:\\6",
                element('app_time', $result)
            );
            $insertdata['cor_total_money'] = $total_price_sum;
            $insertdata['cor_cash_request'] = element('amount', $result);
            $insertdata['cor_deposit'] = $order_deposit;
            $insertdata['cor_cash'] = $cor_cash = element('amount', $result);
            $insertdata['cor_status'] = 1;
            $insertdata['mem_realname'] = $this->input->post('mem_realname', null, '');
            $insertdata['cor_bank_info'] = element('mobile_no', $result);
            $insertdata['cor_pg'] = $this->cbconfig->item('use_payment_pg');

            if ( ((int) $item_cct_price - (int) $order_deposit - $cor_cash) == 0 ) {
                $od_status = 'deposit'; //주문상태
            }

        } elseif ($this->input->post('pay_type') === 'card') {
            if ($this->cbconfig->item('use_payment_pg') === 'kcp') {
                $result = $this->paymentlib->kcp_pp_ax_hub();
            } elseif ($this->cbconfig->item('use_payment_pg') === 'lg') {
                $result = $this->paymentlib->xpay_result();
            } elseif ($this->cbconfig->item('use_payment_pg') === 'inicis') {
                $result = $this->paymentlib->inipay_result($agent_type);
            }

            $insertdata['cor_tno'] = element('tno', $result);
            $insertdata['cor_app_no'] = element('app_no', $result);
            $insertdata['cor_datetime'] = date('Y-m-d H:i:s');
            $insertdata['cor_approve_datetime'] = preg_replace(
                "/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/",
                "\\1-\\2-\\3 \\4:\\5:\\6",
                element('app_time', $result)
            );
            $insertdata['cor_total_money'] = $total_price_sum;
            $insertdata['cor_cash_request'] = element('amount', $result);
            $insertdata['cor_deposit'] = $order_deposit;
            $insertdata['cor_cash'] = $cor_cash = element('amount', $result);
            $insertdata['cor_bank_info'] = element('card_name', $result);
            $insertdata['cor_status'] = 1;
            $insertdata['mem_realname'] = $this->input->post('mem_realname', null, '');
            $insertdata['cor_pg'] = $this->cbconfig->item('use_payment_pg');

            if ( ((int) $item_cct_price - (int) $order_deposit - $cor_cash) == 0 ) {
                $od_status = 'deposit'; //주문상태
            }

        } else {
            alert('결제 수단이 잘못 입력되었습니다');
        }

        // 이벤트가 존재하면 실행합니다
        // Events::trigger('step1', $eventname);

        //실제로 결제된 금액
        $real_total_price = $total_price_sum - $order_deposit;

        // 주문금액과 결제금액이 일치하는지 체크
        if (element('tno', $result) && (int) element('amount', $result) !== $real_total_price) {
            if ($this->cbconfig->item('use_payment_pg') === 'kcp') {
                $this->paymentlib->kcp_pp_ax_hub_cancel($result);
            } elseif ($this->cbconfig->item('use_payment_pg') === 'lg') {
                $this->paymentlib->xpay_cancel($result);
            } elseif ($this->cbconfig->item('use_payment_pg') === 'inicis') {
                $this->paymentlib->inipay_cancel($result, $agent_type);
            }
            alert('결제가 완료되지 않았습니다. 다시 시도해주십시오', site_url('cmall/order'));
        }

        // 이벤트가 존재하면 실행합니다
        // Events::trigger('step2', $eventname);

        // 정보 입력
        $cor_id = $this->cb_jwt->userdata('unique_id');
        $insertdata['cor_id'] = $cor_id;
        $insertdata['mem_id'] = $mem_id;
        $insertdata['mem_nickname'] = $this->member->item('mem_nickname');
        $insertdata['mem_email'] = $this->input->post('mem_email', null, '');
        $insertdata['mem_phone'] = $this->input->post('mem_phone', null, '');
        $insertdata['cor_pay_type'] = $this->input->post('pay_type', null, '');
        $insertdata['cor_content'] = $this->input->post('cor_content', null, '');
        $insertdata['cor_ip'] = $this->input->ip_address();
        $insertdata['cor_useragent'] = $this->agent->agent_string();
        $insertdata['is_test'] = $this->cbconfig->item('use_pg_test');
        $insertdata['status'] = $od_status;

        $this->load->model(array('Cmall_item_model', 'Cmall_order_model', 'Cmall_order_detail_model'));
        $res = $this->Cmall_order_model->insert($insertdata);
        if ($res) {
            $cwhere = array(
                'mem_id' => $mem_id,
                'cct_order' => 1,
            );
            $cartorder = $this->Cmall_cart_model->get('', '', $cwhere);
            if ($cartorder) {
                foreach ($cartorder as $key => $val) {
                    $item = $this->Cmall_item_model
                        ->get_one(element('cit_id', $val), 'cit_download_days');
                    $insertdetail = array(
                        'cor_id' => $cor_id,
                        'mem_id' => $mem_id,
                        'cit_id' => element('cit_id', $val),
                        'cde_id' => element('cde_id', $val),
                        'cod_download_days' => element('cit_download_days', $item),
                        'cod_count' => element('cct_count', $val),
                        'cod_status' => $od_status,
                    );
                    $this->Cmall_order_detail_model->insert($insertdetail);
                    $deletewhere = array(
                        'mem_id' => $mem_id,
                        'cit_id' => element('cit_id', $val),
                        'cde_id' => element('cde_id', $val),
                    );
                    $this->Cmall_cart_model->delete_where($deletewhere);
                }
            }
            if ($order_deposit) {
                $this->load->library('depositlib');
                $this->depositlib->do_deposit_to_contents(
                    $mem_id,
                    $order_deposit,
                    $pay_type = '',
                    $content = '상품구매 주문번호 : ' . $cor_id,
                    $admin_memo = ''
                );
            }
        }

        if (empty($res)) {
            if ($this->input->post('pay_type') !== 'bank') {
                if ($this->cbconfig->item('use_payment_pg') === 'kcp') {
                    $this->paymentlib->kcp_pp_ax_hub_cancel($result);
                } elseif ($this->cbconfig->item('use_payment_pg') === 'lg') {
                    $this->paymentlib->xpay_cancel($result);
                } elseif ($this->cbconfig->item('use_payment_pg') === 'inicis') {
                    $this->paymentlib->inipay_cancel($result, $agent_type);
                }
            }
            alert('결제가 완료되지 않았습니다. 다시 시도해주십시오', site_url('cmall/order'));
        }


        if ($this->input->post('pay_type') === 'bank') {
            $this->cmalllib->orderalarm('bank_to_contents', $cor_id);
        } else {
            $this->cmalllib->orderalarm('cash_to_contents', $cor_id);
        }

        // 이벤트가 존재하면 실행합니다
        // Events::trigger('after', $eventname);

        $this->cb_jwt->set_userdata('unique_id', '');
        $this->cb_jwt->set_userdata('order_cct_id', '');

        redirect('cmall/orderresult/' . $cor_id);
    }


    public function orderlist_get()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_orderlist';
        //$this->load->event($eventname);

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $this->load->model(array('Cmall_order_model','Cmall_order_detail_model','Cmall_item_model'));
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->Cmall_order_model->primary_key;
        $forder = 'desc';

        $per_page = get_listnum();
        $offset = ($page - 1) * $per_page;
        
        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        $where['mem_id'] = $this->member->item('mem_id');
        $where['is_del'] = 0;

        $result_= array();
        $result = $this->Cmall_order_model
            ->get_list($per_page, $offset, $where, '', $findex, $forder);

        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                
                $result_['list'][$key]['cor_id'] = element('cor_id',$val);
                $result_['list'][$key]['brd_info']  = $this->denguruapi->get_brd_info(element('brd_id', $val));

                $orderdetail = $this->Cmall_order_detail_model->get_by_item(element('cor_id',$val));
                $board_crawl = $this->denguruapi->get_all_crawl(element('brd_id',$val));   

                if ($orderdetail) {
                    foreach ($orderdetail as $value) {
                        $result_['list'][$key]['item'][] 
                            = $this->denguruapi->get_cit_info(element('cit_id', $value));
                        
                    }
                }

                $result_['list'][$key]['cor_memo'] = element('cor_memo',$val);
                $result_['list'][$key]['cor_total_money'] = element('cor_total_money',$val);
                $result_['list'][$key]['cor_content'] = element('cor_content',$val);
                $result_['list'][$key]['cor_pay_type'] = element('cor_pay_type',$val);

                $result_['list'][$key]['display_datetime'] = display_datetime(
                    element('cor_datetime', $val),'user','Y-m-d'
                );

                
                
                $result_['list'][$key]['orderresult_url'] = base_url('cmall/orderresult/'.element('cor_id',$val));
                $result_['list'][$key]['brd_site_type'] = element('brd_site_type',$board_crawl) ;
                $result_['list'][$key]['brd_nomember_order_url'] = element('brd_nomember_order_url',$board_crawl) ;

                $order_crawl = $this->Cmall_order_model->get_one(element('cor_id',$val), 'cor_id,brd_id,cor_key,cor_pay_type');

                $param =& $this->querystring;
                $brd_url_key_ = parse_url(trim(element('brd_url_key',$board_crawl)));

                

                if(element('brd_order_key',$board_crawl)==='sixshop' || element('brd_order_key',$board_crawl)==='parse'){
                    $result_['list'][$key]['brd_orderstatus_url'] = element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).element('cor_key',$order_crawl);
                } else {
                    $result_['list'][$key]['brd_orderstatus_url'] = element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).'?'.$param->replace(element('brd_order_key',$board_crawl),element('cor_key',$order_crawl),element('query',$brd_url_key_));
                }

                if(element('cor_pay_type',$order_crawl) =='naverpay'){
                    $brd_url_key_ = parse_url(trim('https://m.pay.naver.com/o/orderStatus'));
                    $result_['list'][$key]['brd_orderstatus_url'] = element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).element('cor_key',$order_crawl);
                }

                
                
                // $result['list'][$key]['num'] = $list_num--;
            }
        }

        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        $view['view']['data'] = $result_;

        /**
         * 페이지네이션을 생성합니다
         */
        $config['base_url'] = site_url('cmall/orderlist') . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['next_link'] = $this->pagination->get_next_link();
        $view['view']['page'] = $page;


        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

        

        $this->data = $view['view'];
        
        return $this->response($this->data, parent::HTTP_OK);
    }


    public function _wishlist($brd_id = 0 ,$type='')
    {
        

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();

        $view = array();
        $view['view'] = array();

        $this->load->model(array('Cmall_wishlist_model'));
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->Cmall_wishlist_model->primary_key;
        $forder = 'asc';


        $per_page = get_listnum();

        $offset = ($page - 1) * $per_page;

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        

        

        $where = array();
        $where['cmall_wishlist.mem_id'] = $this->member->item('mem_id');
        // $where['cit_status'] = 1;
        $result = $this->Cmall_wishlist_model
            ->get_list($per_page, $offset, $where, '', $findex, $forder);
        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {
                $result['list'][$key] = $this->denguruapi->get_cit_info(element('cit_id',$val),$result['list'][$key]);

                $board_crawl = $this->denguruapi->get_all_crawl(element('brd_id',$result['list'][$key]));

                // $result['list'][$key]['brd_register_url'] = element('brd_register_url',$board_crawl);    
                // $result['list'][$key]['brd_order_url'] = element('brd_order_url',$board_crawl);

                $result['list'][$key]['delete_url'] = site_url('cmallact/wishlist/' . element('cwi_id', $val) . '?' . $param->output());
                $result['list'][$key]['num'] = $list_num--;
            }
        }

        $view['view']['data'] = $result;
        if($type==='store')
            $view['view']['storeby_wishlist_url'] = site_url('cmall/wishlist');
        else
            $view['view']['storeby_wishlist_url'] = site_url('cmall/wishlist/0/store');

        if($type==='store'){
            $data=array();
            if (element('list', $result)) {
                foreach (element('list', $result) as $key => $val) {
                    
                    $_data = $this->denguruapi->get_brd_info(element('brd_id', $val));

                    $data['list'][element('brd_id',$val)]['brd_name'] = element('brd_name',$_data);
                    $data['list'][element('brd_id',$val)]['brd_image'] = element('brd_image',$_data);
                    $data['list'][element('brd_id',$val)]['brd_outlink_url'] = element('brd_outlink_url',$_data);
                    $data['list'][element('brd_id',$val)]['brd_inlink_url'] = element('brd_inlink_url',$_data);
                    $data['list'][element('brd_id',$val)]['brd_wishlist_url'] = site_url('cmall/wishlist/'.element('brd_id',$val));

                    $data['list'][element('brd_id',$val)]['brd_id'] = element('brd_id',$val);

                    if(empty($data['list'][element('brd_id',$val)]['cnt']))
                        $data['list'][element('brd_id',$val)]['cnt'] = 1;
                    else
                        $data['list'][element('brd_id',$val)]['cnt']++;
                    
                    $data['list'][element('brd_id',$val)]['brd_attr'] = $this->denguruapi->get_popular_brd_attr(element('brd_id', $val));

                    
                }
            }

            $_data = array();
            if (element('list', $data)) {
                foreach (element('list', $data) as $key => $val) {
                    
                    

                    $_data['list'][]  = $val;
                    

                        
                }
            }
            $_data['total_rows'] = isset($data['list']) ? count($data['list']) : 0;
            $view['view']['data'] = $_data;
        }

        if(!empty($brd_id)){
            $data=array();
            if (element('list', $result)) {
                foreach (element('list', $result) as $key => $val) {
                    
                    if($brd_id !== element('brd_id',$val)) continue;
                    $data['list'][] = $val; 

                        
                }
            }
            
            $data['total_rows'] = isset($data['list']) ? count($data['list']) : 0;
            $view['view']['data'] = $data;
        }

        

        /**
         * 페이지네이션을 생성합니다
         */
        $config['base_url'] = site_url('cmall/wishlist') . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['next_link'] = $this->pagination->get_next_link();
        $view['view']['page'] = $page;

        
        return $view['view'];
        
    }

    public function wishlist_get($brd_id = 0 ,$type='')
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_wishlist';
        //$this->load->event($eventname);

        
        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
        
        $view['view'] = $this->_wishlist($brd_id, $type);
        
        /**
         * 레이아웃을 정의합니다
         */
        $page_title = $this->cbconfig->item('site_meta_title_cmall_wishlist');
        $meta_description = $this->cbconfig->item('site_meta_description_cmall_wishlist');
        $meta_keywords = $this->cbconfig->item('site_meta_keywords_cmall_wishlist');
        $meta_author = $this->cbconfig->item('site_meta_author_cmall_wishlist');
        $page_name = $this->cbconfig->item('site_page_name_cmall_wishlist');

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
            'skin' => 'wishlist',
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
    
    protected function _storewishlist()
    {
        

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();

        $view = array();
        $view['view'] = array();

        $this->load->model(array('Cmall_storewishlist_model','Cmall_item_model','Crawl_tag_model'));
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->Cmall_storewishlist_model->primary_key;
        $forder = 'desc';

        // $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
        $per_page = get_listnum();
        $offset = ($page - 1) * $per_page;

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();

        // if($this->input->get('sform')){            
  //               $where['pet_form'] = $this->input->get('sform');
  //       }
  //       if($this->input->get('skind')){            
  //               $where['pet_kind'] = $this->input->get('skind');
  //       }
  //       if($this->input->get('sattr')){            
  //               $where['pet_attr'] = $this->input->get('sattr');
  //       }
  
        $where['cmall_storewishlist.mem_id'] = $this->member->item('mem_id');
        

        $field = array(
            'cmall_storewishlist' => array('csi_id','csi_datetime','brd_id'),
        );
        
        $select = get_selected($field);
        
        $this->Cmall_storewishlist_model->_select = $select;

        // $result = $this->Cmall_storewishlist_model
        //  ->get_list($per_page, $offset, $where, '', $findex, $forder);
        // $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        $result = $this->Cmall_storewishlist_model
            ->get_list('','', $where);
        
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {
                $result['list'][$key] = $this->denguruapi->get_brd_info(element('brd_id', $val),$result['list'][$key]);
                // $result['list'][$key]['brd_tag'] = $this->denguruapi->get_popular_brd_tags(element('brd_id', $val));
                $result['list'][$key]['brd_attr'] = $this->denguruapi->get_popular_brd_attr(element('brd_id', $val),8);
                

                
                $result['list'][$key]['cit_type3_count'] = $this->Cmall_item_model->count_by(array('cit_type3' => 1,'cit_status' => 1,'cit_is_del' => 0,'cit_is_soldout' => 0,'brd_id' => element('brd_id', $val)));
                $result['list'][$key]['delete_url'] = site_url('cmallact/storewishlist/' . element('csi_id', $val) . '?' . $param->output());
                // $result['list'][$key]['num'] = $list_num--;
            }
        }
        $view['view']['data'] = $result;

        /**
         * 페이지네이션을 생성합니다
         */
        // $config['base_url'] = site_url('cmall/storewishlist') . '?' . $param->replace('page');
        // $config['total_rows'] = $result['total_rows'];
        // $config['per_page'] = $per_page;
        // $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        // $view['view']['page'] = $page;

        
        return $view['view'];
        
    }

    public function storewishlist_get()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_wishlist';
        //$this->load->event($eventname);

        
        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
        
        $view['view'] = $this->_storewishlist();
        
        /**
         * 레이아웃을 정의합니다
         */
        $page_title = $this->cbconfig->item('site_meta_title_cmall_wishlist');
        $meta_description = $this->cbconfig->item('site_meta_description_cmall_wishlist');
        $meta_keywords = $this->cbconfig->item('site_meta_keywords_cmall_wishlist');
        $meta_author = $this->cbconfig->item('site_meta_author_cmall_wishlist');
        $page_name = $this->cbconfig->item('site_page_name_cmall_wishlist');

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
            'skin' => 'wishlist',
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

    
    protected function _storeranklist($config)
    {
        

        

        $view = array();
        $view['view'] = array();
        

        
        $this->load->model(array('Board_model','Cmall_attr_model','Pet_attr_model','Cmall_kind_model','Theme_model'));

        $sattr = element('sattr', $config) ? element('sattr', $config) : false;
        $skind = element('skind', $config) ? element('skind', $config) : false;
        $is_mypet_match = element('is_mypet_match', $config) ? element('is_mypet_match', $config) : false;

        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        
        $mem_id = (int) $this->member->item('mem_id');


        $per_page = get_listnum();        
        $offset = ($page - 1) * $per_page;

        $all_kind = $this->Cmall_kind_model->get_all_kind();
        $all_attr = $this->Cmall_attr_model->get_all_attr();
        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array(
                'brd_search' => 1,
                'brd_blind' => 0,               
                );

        

        $this->Board_model->_select = 'board.*';
        if($sattr || $skind){
            $cmallwhere = 'where
                cit_status = 1
                AND cit_is_del = 0
                AND cit_is_soldout = 0
            ';
            $_join = '';
            

            $_join = "
                select cit_id,brd_id from cb_cmall_item ".$cmallwhere;

            // $set_join[] = array("
            //  (select cit_id,brd_id from cb_cmall_item ".$cmallwhere.") as cb_cmall_item",'cmall_item.brd_id = board.brd_id','inner');
        } 
        if($sattr && is_array($sattr)){
                        
            $sattr_id = array();
            foreach($all_attr as $akey => $aval){
                
                foreach($aval as  $aaval){  
                    foreach($sattr as $cval){
                        if($cval == element('cat_id',$aaval)){
                            $sattr_id[$akey][] = $cval;
                        }
                    }   
                }
            }

            
            $sattr_val = array();
            // $usattr_val = array();
            foreach($sattr_id as $skey => $sval){
                foreach($sval as $sval_){
                    array_push($sattr_val,$sval_);
                }
                    
                // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            }

            // foreach($usattr_id as $uskey => $usval){
            //     foreach($usval as $usval_){
            //         array_push($usattr_val,$usval_);
            //     }
                    
            //     // $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                
            // }
            
            if(!empty($sattr_val))
                $_join .= " and cit_id in (select cit_id from cb_cmall_attr_rel where cat_id in (".implode(',',$sattr_val)."))";


            
            
        }

        if($skind){

            $_join .=" and cit_id in (select cit_id from cb_cmall_kind_rel where ckd_id = ".$skind." )" ;
        }
        // if($this->input->get('sform')){            
  //               $where['pet_form'] = $this->input->get('sform');
  //       }
  //       if($this->input->get('skind')){            
  //               $where['pet_kind'] = $this->input->get('skind');
  //       }
  //       if($this->input->get('sattr')){            
  //               $where['pet_attr'] = $this->input->get('sattr');
  //       }
  //       
        
        
        if(!empty($_join))
            $set_join[] = array("
                (".$_join." ) as cb_cmall_item ",'cmall_item.brd_id = board.brd_id','inner');
        // $result = $this->Board_model->get_attr_list('','',$where);

        
        if(!empty($set_join)) {
            $this->Board_model->set_join($set_join);
            // $this->Board_model->set_group_by('cmall_item.cit_id');
        }
        if($sattr || $skind){           
            $this->Board_model->group_by('brd_id');            
        }
        $result = $this->Board_model
                ->get_rank_list($per_page, $offset, $where, '', '', 'brd_order asc');
        // echo count($result['list']);
        // $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {         
            foreach (element('list', $result) as $key => $val) {                
                $result['list'][$key] = $this->denguruapi->convert_brd_info($val);
                $result['list'][$key]['brd_attr'] = $this->denguruapi->get_popular_brd_attr(element('brd_id', $val),8);

                
                // $result[$key]['cit_type3_count'] = $this->Cmall_item_model->count_by(array('cit_type3' => 1,'brd_id' => element('brd_id', $val)));
                
                // $result['list'][$key]['delete_url'] = site_url('cmallact/storewishlist/' . element('csi_id', $val) . '?' . $param->output());
                
            }
        }


        $view['view']['data']['rank'] = $result;

        $config['base_url'] = site_url('cmall/storeranklist') . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['data']['rank']['next_link'] = $this->pagination->get_next_link();
        $view['view']['data']['rank']['page'] = $page;


        $result = $this->Theme_model->get_theme();
        $result_= array();
        // $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if ($result) {
            foreach ($result as $key => $val) {

                $result_[element('the_id',$val)]['the_title'] = element('the_title',$val);
                $result_[element('the_id',$val)]['brd_list'][] = $this->denguruapi->get_brd_info(element('brd_id', $val) );

                
                // $result[$key]['brd_tag'] = $this->denguruapi->get_popular_brd_tags(element('brd_id', $val),8);

                
                // $result[$key]['cit_type3_count'] = $this->Cmall_item_model->count_by(array('cit_type3' => 1,'brd_id' => element('brd_id', $val)));
                
                // $result[$key]['delete_url'] = site_url('cmallact/storewishlist/' . element('csi_id', $val) . '?' . $param->output());
                
            }

            $view['view']['data']['theme']['list'] = $result_[array_key_first($result_)];
        }
        

        

        $pet_attr = $this->Pet_attr_model->get_all_attr();

        $view['view']['config']['pet_age'] = element(3,$pet_attr);;
        $view['view']['config']['pet_form'] = element(2,$pet_attr);
        $view['view']['config']['pet_kind'] = element(0,$this->Cmall_kind_model->get_all_kind());
        $view['view']['config']['pet_attr'] = element(1,$pet_attr);;


    
            
            

        /**
         * 페이지네이션을 생성합니다
         */
        // $config['base_url'] = site_url('cmall/storeranklist') . '?' . $param->replace('page');
        // $config['total_rows'] = $result['total_rows'];
        // $config['per_page'] = $per_page;
        // $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        // $view['view']['page'] = $page;

        
        return $view['view'];
        
    }

    public function storeranklist_get()
    {
        

        
        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');

        if($mem_id)
            $view['view']['data']['member'] = $this->denguruapi->get_mem_info($mem_id);


        $sattr =  array();
        $skind = '';
        if($this->input->get('sattr') && is_array($this->input->get('sattr'))){
            foreach($this->input->get('sattr') as $val){
                if($val === '17') array_push($sattr,12);
                if($val === '18') array_push($sattr,13);
                if($val === '19') array_push($sattr,14);

                if($val === '4') array_push($sattr,79);
                if($val === '5') array_push($sattr,80);
                if($val === '6') array_push($sattr,81);

                if($val === '7') array_push($sattr,82);
                if($val === '8') array_push($sattr,83);
                if($val === '9') array_push($sattr,84);

                if($val === '10') array_push($sattr,85);
                if($val === '11') array_push($sattr,86);
                if($val === '12') array_push($sattr,87);

                if($val === '13') array_push($sattr,88);
            }
        }
        
        $skind = $this->input->get('skind');

        if($mem_id && $this->input->get('is_mypet_match')){

            $sattr =  array();
            $skind = '';
            if((int) $view['view']['data']['member']['pet_age'] < 1) array_push($sattr,12);
            elseif((int) $view['view']['data']['member']['pet_age'] < 7) array_push($sattr,13);
            elseif((int) $view['view']['data']['member']['pet_age'] > 7) array_push($sattr,14);

            $skind = $view['view']['data']['member']['ckd_id'];

            if($view['view']['data']['member']['pet_attr']){
                foreach($view['view']['data']['member']['pet_attr'] as $val){
                    
                    if(element('pat_id',$val) === '4') array_push($sattr,79);
                    if(element('pat_id',$val) === '5') array_push($sattr,80);
                    if(element('pat_id',$val) === '6') array_push($sattr,81);

                    if(element('pat_id',$val) === '7') array_push($sattr,82);
                    if(element('pat_id',$val) === '8') array_push($sattr,83);
                    if(element('pat_id',$val) === '9') array_push($sattr,84);

                    if(element('pat_id',$val) === '10') array_push($sattr,85);
                    if(element('pat_id',$val) === '11') array_push($sattr,86);
                    if(element('pat_id',$val) === '12') array_push($sattr,87);

                    if(element('pat_id',$val) === '13') array_push($sattr,88);
                }
            }
        }


        $config = array(
            'sattr' => $sattr,
            'skind' => $skind,
            // 'is_mypet_match' => $this->input->get('is_mypet_match'),
        );

        // print_r2($config);
        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
        
        $view['view'] = $this->_storeranklist($config);
        
        /**
         * 레이아웃을 정의합니다
         */
        $page_title = $this->cbconfig->item('site_meta_title_cmall_wishlist');
        $meta_description = $this->cbconfig->item('site_meta_description_cmall_wishlist');
        $meta_keywords = $this->cbconfig->item('site_meta_keywords_cmall_wishlist');
        $meta_author = $this->cbconfig->item('site_meta_author_cmall_wishlist');
        $page_name = $this->cbconfig->item('site_page_name_cmall_wishlist');

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
            'skin' => 'wishlist',
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

    /**
     * Q&A 목록을 ajax 로 가져옵니다
     */
    public function qnalist($cit_id = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_qnalist';
        //$this->load->event($eventname);

        $cit_id = (int) $cit_id;
        if (empty($cit_id) OR $cit_id < 1) {
            show_404();
        }

        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $this->load->model(array('Cmall_item_model', 'Cmall_qna_model'));

        $item = $this->Cmall_item_model->get_one($cit_id);
        if ( ! element('cit_id', $item)) {
            alert('이 상품은 현재 존재하지 않습니다',"",406);
        }

        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->input->get('findex') ? $this->input->get('findex') : 'cre_id';
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
        $where['cit_id'] = $cit_id;

        $thumb_width = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_qna_mobile_thumb_width')
            : $this->cbconfig->item('cmall_product_qna_thumb_width');
        $autolink = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('use_cmall_product_qna_mobile_auto_url')
            : $this->cbconfig->item('use_cmall_product_qna_auto_url');
        $popup = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_qna_mobile_content_target_blank')
            : $this->cbconfig->item('cmall_product_qna_content_target_blank');

        $result = $this->Cmall_qna_model
            ->get_list($per_page, $offset, $where, '', $findex, $forder);
        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {
                $result['list'][$key]['display_name'] = display_username(
                    element('mem_userid', $val),
                    element('mem_nickname', $val),
                    element('mem_icon', $val)
                );
                $result['list'][$key]['display_datetime'] = display_datetime(element('cqa_datetime', $val), 'full');
                $result['list'][$key]['display_content'] = display_html_content(
                    element('cqa_content', $val),
                    element('cqa_content_html_type', $val),
                    $thumb_width,
                    $autolink,
                    $popup
                );
                $result['list'][$key]['reply_content'] = display_html_content(
                    element('cqa_reply_content', $val),
                    element('cqa_reply_html_type', $val),
                    $thumb_width,
                    $autolink,
                    $popup
                );
                if (element('cqa_secret', $val)) {
                    if ($mem_id && ($is_admin !== false OR (int) element('mem_id', $val) === $mem_id)) {
                        $result['list'][$key]['display_content'] = '<div class="label label-warning">비밀글입니다</div> ' . $result['list'][$key]['display_content'];
                        $result['list'][$key]['reply_content'] = '<div class="label label-warning">비밀글입니다</div>' . $result['list'][$key]['reply_content'];
                    } else {
                        $result['list'][$key]['display_content'] = '<div class="label label-warning">비밀글입니다</div>';
                        $result['list'][$key]['reply_content'] = '<div class="label label-warning">비밀글입니다</div>';
                    }
                }
                if ( ! element('cqa_reply_content', $val)) {
                    $result['list'][$key]['reply_content'] = '아직 답변이 완료되지 않았습니다';
                }

                $result['list'][$key]['can_update'] = false;
                $result['list'][$key]['can_delete'] = false;
                if ($is_admin !== false OR (element('mem_id', $val) && $mem_id === (int) element('mem_id', $val))) {
                    $result['list'][$key]['can_update'] = true;
                    $result['list'][$key]['can_delete'] = true;
                }
                $result['list'][$key]['num'] = $list_num--;
            }
        }
        $view['view']['data'] = $result;
        $view['view']['cit_id'] = $cit_id;

        /**
         * 페이지네이션을 생성합니다
         */
        $config['base_url'] = site_url('cmall/qnalist/' . $cit_id) . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;

        if ( ! $this->input->get('page')) {
            $_GET['page'] = (string) $page;
        }

        $config['_attributes'] = 'onClick="cmall_qna_page(\'' . $cit_id . '\', $(this).attr(\'data-ci-pagination-page\'));return false;"';
        if ($this->cbconfig->get_device_view_type() === 'mobile') {
            $config['num_links'] = 3;
        } else {
            $config['num_links'] = 5;
        }
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['next_link'] = $this->pagination->get_next_link();
        $view['view']['page'] = $page;


        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

        /**
         * 레이아웃을 정의합니다
         */
        $skindir = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('mobile_skin_cmall')
            : $this->cbconfig->item('skin_cmall');
        if (empty($skindir)) {
            $skindir = ($this->cbconfig->get_device_view_type() === 'mobile')
                ? $this->cbconfig->item('mobile_skin_default')
                : $this->cbconfig->item('skin_default');
        }
        if (empty($skindir)) {
            $skindir = 'basic';
        }
        $skin = 'cmall/' . $skindir . '/qna_list';

        $this->data = $view;
        $this->view = $skin;
    }


    public function qna_write($cit_id = 0, $cqa_id = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_qna_write';
        //$this->load->event($eventname);

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login('alert');

        $mem_id = (int) $this->member->item('mem_id');

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        $this->load->model(array('Cmall_item_model', 'Cmall_qna_model'));

        /**
         * 프라이머리키에 숫자형이 입력되지 않으면 에러처리합니다
         */
        if ($cqa_id) {
            $cqa_id = (int) $cqa_id;
            if (empty($cqa_id) OR $cqa_id < 1) {
                show_404();
            }
        }
        $cit_id = (int) $cit_id;
        if (empty($cit_id) OR $cit_id < 1) {
            show_404();
        }
        $primary_key = $this->Cmall_qna_model->primary_key;

        $item = $this->Cmall_item_model->get_one($cit_id);

        if ( ! element('cit_id', $item) )
            alert('이 상품은 현재 존재하지 않습니다',"",406);
            
        if(! element('cit_status', $item)) 
            alert('이 상품은 현재 판매하지 않습니다',"",406);

        if (!empty(element('cit_is_del', $data))) {
            alert('이 상품은 현재 판매하지 않습니다',"",406);
        }



        /**
         * 수정 페이지일 경우 기존 데이터를 가져옵니다
         */
        $getdata = array();
        if ($cqa_id) {
            $getdata = $this->Cmall_qna_model->get_one($cqa_id);
            if ( ! element('cqa_id', $getdata)) {
                alert_close('잘못된 접근입니다');
            }
            $is_admin = $this->member->is_admin();
            if ($is_admin === false && (int) element('mem_id', $getdata) !== $mem_id) {
                alert_close('본인의 글 외에는 접근하실 수 없습니다');
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
                'field' => 'cqa_title',
                'label' => '제목',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'cqa_content',
                'label' => '내용',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'cqa_secret',
                'label' => '비밀글여부',
                'rules' => 'trim|numeric',
            ),
            array(
                'field' => 'cqa_receive_email',
                'label' => '답변시 메일받기',
                'rules' => 'trim|numeric',
            ),
            array(
                'field' => 'cqa_receive_sms',
                'label' => '답변시 문자받기',
                'rules' => 'trim|numeric',
            ),
        );
        $this->form_validation->set_rules($config);


        /**
         * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
         * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
         */
        if ($this->form_validation->run() === false) {

            // 이벤트가 존재하면 실행합니다
            // $view['view']['event']['formrunfalse'] = Events::trigger('formrunfalse', $eventname);

            /**
             * primary key 정보를 저장합니다
             */
            $view['view']['primary_key'] = $primary_key;
            $view['view']['data'] = $getdata;
            $view['view']['item'] = $item;

            // 이벤트가 존재하면 실행합니다
            // $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

            /**
             * 레이아웃을 정의합니다
             */
            $page_title = $this->cbconfig->item('site_meta_title_cmall_qna_write');
            $meta_description = $this->cbconfig->item('site_meta_description_cmall_qna_write');
            $meta_keywords = $this->cbconfig->item('site_meta_keywords_cmall_qna_write');
            $meta_author = $this->cbconfig->item('site_meta_author_cmall_qna_write');
            $page_name = $this->cbconfig->item('site_page_name_cmall_qna_write');

            $searchconfig = array(
                '{컨텐츠몰명}',
                '{상품명}',
                '{판매가격}',
                '{기본설명}',
            );
            $replaceconfig = array(
                $this->cbconfig->item('cmall_name'),
                element('cit_name', $item),
                element('cit_price', $item),
                element('cit_summary', $item),
            );

            $page_title = str_replace($searchconfig, $replaceconfig, $page_title);
            $meta_description = str_replace($searchconfig, $replaceconfig, $meta_description);
            $meta_keywords = str_replace($searchconfig, $replaceconfig, $meta_keywords);
            $meta_author = str_replace($searchconfig, $replaceconfig, $meta_author);
            $page_name = str_replace($searchconfig, $replaceconfig, $page_name);

            $layoutconfig = array(
                'path' => 'cmall',
                'layout' => 'layout_popup',
                'skin' => 'qna_write',
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
            $view['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
            $this->data = $view;
            $this->layout = element('layout_skin_file', element('layout', $view));
            $this->view = element('view_skin_file', element('layout', $view));

        } else {
            /**
             * 유효성 검사를 통과한 경우입니다.
             * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
             */

            // 이벤트가 존재하면 실행합니다
            // $view['view']['event']['formruntrue'] = Events::trigger('formruntrue', $eventname);

            $content_type = $this->cbconfig->item('use_cmall_product_qna_dhtml') ? 1 : 0;
            $cqa_secret = $this->input->post('cqa_secret') ? 1 : 0;
            $cqa_receive_email = $this->input->post('cqa_receive_email') ? 1 : 0;
            $cqa_receive_sms = $this->input->post('cqa_receive_sms') ? 1 : 0;

            $updatedata = array(
                'cit_id' => $cit_id,
                'cqa_title' => $this->input->post('cqa_title', null, ''),
                'cqa_content' => $this->input->post('cqa_content', null, ''),
                'cqa_content_html_type' => $content_type,
                'cqa_secret' => $cqa_secret,
                'cqa_receive_email' => $cqa_receive_email,
                'cqa_receive_sms' => $cqa_receive_sms,
            );

            /**
             * 게시물을 수정하는 경우입니다
             */
            $param =& $this->querystring;
            $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;

            if ($cqa_id) {
                $this->Cmall_qna_model->update($cqa_id, $updatedata);
                $cntresult = $this->cmalllib->update_qna_count($cit_id);
                $jresult = json_decode($cntresult, true);
                $cnt = element('cit_qna_count', $jresult);
                echo '<script type="text/javascript">window.opener.view_cmall_qna("viewitemqna", ' . $cit_id . ', ' . $page . ');window.opener.cmall_qna_count_update(' . $cnt . ');</script>';
                alert_close('정상적으로 수정되었습니다.');
            } else {
                /**
                 * 게시물을 새로 입력하는 경우입니다
                 */
                $updatedata['cqa_datetime'] = cdate('Y-m-d H:i:s');
                $updatedata['mem_id'] = $mem_id;
                $updatedata['cqa_ip'] = $this->input->ip_address();

                $_cqa_id = $this->Cmall_qna_model->insert($updatedata);

                $this->cmalllib->qna_alarm($_cqa_id);

                $cntresult = $this->cmalllib->update_qna_count($cit_id);
                $jresult = json_decode($cntresult, true);
                $cnt = element('cit_qna_count', $jresult);
                echo '<script type="text/javascript">window.opener.view_cmall_qna("viewitemqna", ' . $cit_id . ', ' . $page . ');window.opener.cmall_qna_count_update(' . $cnt . ');</script>';
                alert_close('정상적으로 입력되었습니다.');
            }
        }
    }

    


    protected function _cit_type1_lists()
    {
        
        

        $view = array();
        $view['view'] = array();

        $this->load->model('Cmall_item_model');

        $config = array(
            'cit_type1' => '1',
            'limit' => '30',
            'cache_minute' => 86400
        );

        $result_1 = $this->denguruapi->cit_latest($config);

        if ($result_1) {
            foreach ($result_1 as $key => $val) {
                // $view['view']['list'][$key]['cit_id'] = element('cit_id',$val);
                // $view['view']['list'][$key]['cit_key'] = element('cit_key',$val);
                // $view['view']['list'][$key]['cit_name'] = element('cit_name',$val);
                // $view['view']['list'][$key]['cit_order'] = element('cit_order',$val);                
                // $view['view']['list'][$key]['cit_price'] = element('cit_price',$val);
                // $view['view']['list'][$key]['cit_file_1'] = element('cit_file_1',$val);
                // $view['view']['list'][$key]['cit_hit'] = element('cit_hit',$val);
                // $view['view']['list'][$key]['cit_datetime'] = element('cit_datetime',$val);
                // $view['view']['list'][$key]['cit_updated_datetime'] = element('cit_updated_datetime',$val);
                // $view['view']['list'][$key]['cit_sell_count'] = element('cit_sell_count',$val);
                // $view['view']['list'][$key]['cit_wish_count'] = element('cit_wish_count',$val);
                // $view['view']['list'][$key]['cit_review_count'] = element('cit_review_count',$val);
                // $view['view']['list'][$key]['cit_review_average'] = element('cit_review_average',$val);
                // $view['view']['list'][$key]['cit_qna_count'] = element('cit_qna_count',$val);
                // $view['view']['list'][$key]['cit_is_soldout'] = element('cit_is_soldout',$val);
                // $view['view']['list'][$key]['post_id'] = element('post_id',$val);
                // $view['view']['list'][$key]['cmall_item_url'] = cmall_item_url(element('cit_id',$val));
                // $view['view']['list'][$key]['board_url'] = board_url(element('brd_id',$val));
                // $view['view']['list'][$key]['post_url'] = post_url(element('post_id',$val));
                // $view['view']['list'][$key]['cit_post_url'] = element('cit_post_url',$val);
                // $view['view']['list'][$key]['cit_attr'] = element('cit_attr',$val);
                // $view['view']['list'][$key]['cit_brand'] = element('cbr_value_kr',$val,element('cbr_value_en',$val));
                
                $result_1[$key] = $this->denguruapi->convert_cit_info($result_1[$key]);
                
            }
        }

        $view['view']['list'] = $result_1;

        return $view['view'];
    }

    public function cit_type1_lists_get()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_index';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
        $view['view']['data'] = $this->_cit_type1_lists();

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

    //카테고리 리스트
    protected function _categorylists()
    {


        $view = array();
        $key=0;
        $view['view'] = array();
        
        $data['list'][$key]['cca_id'] = 0;
        $data['list'][$key]['cca_value'] = '전체';
        $data['list'][$key]['category_url'] = base_url('search/show_list');
        $data['list'][$key]['category_image_url'] = cdn_url('category','icon-cate-0.svg');
        $category = $this->cmalllib->get_all_category();
        if (element(0, $category)) {
            foreach (element(0, $category) as $value) {
                $key++;
                $data['list'][$key]['cca_id'] = html_escape(element('cca_id', $value));
                $data['list'][$key]['cca_value'] = html_escape(element('cca_value', $value));
                $data['list'][$key]['category_url'] = base_url('search/show_list?scategory[]=' . element('cca_id', $value));
                $data['list'][$key]['category_image_url'] = cdn_url('category','icon-cate-'.element('cca_id', $value).'.svg');

                // if (element(element('cca_id', $value), $category)) {
                //  foreach (element(element('cca_id', $value), $category) as $svalue) {
        
                //  }
                // }
            }
        }

        $view['view']['data'] = $data;

        return $view['view'];
    }

    public function categorylists_get()
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_index';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);
        $view['view'] = $this->_categorylists();

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

    protected function _store($brd_id = 0)
    {

        


        $view = array();
        $view['view'] = array();

        $mem_id = (int) $this->member->item('mem_id');

        
        $this->load->model(array('Cmall_storewishlist_model','Board_model'));

        
        
        $board = $this->Board_model->get_one($brd_id,'brd_id,brd_blind,cit_updated_datetime');
        $board_crawl = $this->denguruapi->get_all_crawl($brd_id);

        // $view['view']['brd_register_url'] = trim(element('brd_register_url',$board_crawl));  
        // $view['view']['brd_order_url'] = trim(element('brd_order_url',$board_crawl));
        $view['view']['brd_updated_datetime'] = element('cit_updated_datetime', $board);
        
        if ( ! element('brd_id', $board)) {
            alert('이 스토어는 현재 존재하지 않습니다',"",406);
        }
        if (element('brd_blind', $board)) {
            alert('이 스토어는 현재 운영하지 않습니다',"",406);
        }

        // $data['meta'] = $this->Cmall_item_meta_model->get_all_meta(element('cit_id', $data));
        // $data['detail'] = $this->Cmall_item_detail_model->get_all_detail(element('cit_id', $data));

        $alertmessage = $this->member->is_member()
            ? '회원님은 상품 페이지를 볼 수 있는 권한이 없습니다'
            : '비회원은 상품 페이지를 볼 수 있는 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        $access_read = $this->cbconfig->item('access_cmall_read');
        $access_read_level = $this->cbconfig->item('access_cmall_read_level');
        $access_read_group = $this->cbconfig->item('access_cmall_read_group');
        $this->accesslevel->check(
            $access_read,
            $access_read_level,
            $access_read_group,
            $alertmessage,
            ''
        );

        

        // if ( ! $this->cb_jwt->userdata('cmall_item_id_' . element('cit_id', $data))) {
            // $this->Cmall_item_model->update_hit(element('cit_id', $data));
        //  $this->cb_jwt->set_userdata(
        //      'cmall_item_id_' . element('cit_id', $data),
        //      '1'
        //  );
        // }
        if ( ! $this->cb_jwt->userdata('brd_inlink_click_' . element('brd_id', $board))) {

            $this->cb_jwt->set_userdata(
                'brd_inlink_click_' . element('brd_id', $board),
                '1'
            );

            
            if($mem_id){
                $insertdata = array(
                    'pln_id' => 0,
                    'post_id' => 0,
                    'brd_id' => element('brd_id', $board),
                    'cit_id' => 0,
                    'clc_datetime' => cdate('Y-m-d H:i:s'),
                    'clc_ip' => $this->input->ip_address(),
                    'clc_useragent' => $this->agent->agent_string(),
                    'mem_id' => $mem_id,
                );
                $this->load->model('Crawl_link_click_log_model');
                $this->Crawl_link_click_log_model->insert($insertdata);
            }
            $this->load->model('Board_model');
            $this->Board_model->update_plus(element('brd_id', $board), 'brd_hit', 1);
            // $this->_stat_count_board(element('brd_id', $board));
        }
        
        

        // $data['header_content'] = ($this->cbconfig->get_device_view_type() === 'mobile')
        //  ? display_html_content(element('mobile_header_content', element('meta', $data)), 1, $thumb_width)
        //  : display_html_content(element('header_content', element('meta', $data)), 1, $thumb_width);

        // $data['footer_content'] = ($this->cbconfig->get_device_view_type() === 'mobile')
        //  ? display_html_content(element('mobile_footer_content', element('meta', $data)), 1, $thumb_width)
        //  : display_html_content(element('footer_content', element('meta', $data)), 1, $thumb_width);

        $where = array(
                'brd_id' => element('brd_id',$board),
            );
        $view['view']['storewishcount'] = $this->Cmall_storewishlist_model->count_by($where);   

        $view['view']['addstorewish_url'] = site_url('cmall/storewish/'.element('brd_id',$board));
        $view['view']['storewishstatus'] = 0;
        if(!empty($mem_id)){
            $where = array(
                'mem_id' => $mem_id,
                'brd_id' => element('brd_id',$board),
            );
            $view['view']['storewishstatus'] = $this->Cmall_storewishlist_model->count_by($where);  
        }
        
        

        $view['view']['data'] = $this->denguruapi->get_brd_info(element('brd_id', $board));
        $view['view']['data']['brd_tag'] = $this->denguruapi->get_popular_brd_tags(element('brd_id', $board));
        $view['view']['data']['brd_attr'] = $this->denguruapi->get_popular_brd_attr(element('brd_id', $board));
        $view['view']['data']['similaritemlist'] = $this->cmalllib->_itemlists('',$brd_id,array('cit_type3' => 1));

        
        

        
        
        
        return $view['view'];
    }

    public function store_get($brd_id = 0)
    {

        
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_item';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        

        
        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        

        $view['view'] = $this->_store($brd_id);
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
     * 방문로그를 남깁니다
     */
    public function _stat_count_board($brd_id = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_board_post_stat_count_board';
        //$this->load->event($eventname);

        if (empty($brd_id)) {
            return false;
        }

        // 이벤트가 존재하면 실행합니다
        // Events::trigger('count_before', $eventname);

        // 방문자 기록
        if ( ! get_cookie('board_id_' . $brd_id.'cit_id_' . $cit_id)) {
            $cookie_name = 'board_id_' . $brd_id;
            $cookie_value = '1';
            $cookie_expire = 86400; // 1일간 저장
            set_cookie($cookie_name, $cookie_value, $cookie_expire);

            $this->load->model('Stat_count_board_model');
            $this->Stat_count_board_model->add_visit_board($brd_id);

        }
    }

    protected function _store_info($brd_id = 0)
    {

        


        $view = array();
        $view['view'] = array();

        if (empty($brd_id )) {
            show_404();
        }
        

        
        $this->load->model(array('Cmall_storewishlist_model','Board_model'));

        
        
        $board = $this->Board_model->get_one($brd_id,'brd_id,brd_blind,brd_name');

        if ( ! element('brd_id', $board)) {
            alert('이 스토어는 현재 존재하지 않습니다',"",406);
        }
        if (element('brd_blind', $board)) {
            alert('이 스토어는 현재 운영하지 않습니다',"",406);
        }

        $board_crawl = $this->denguruapi->get_all_crawl($brd_id);

        $view['view'] = $board_crawl;

        foreach($board_crawl as $key => $val){
            if($key ==='brd_register_zipcode')
                $view['view'][$key] = explode("-",trim($val));  

            if($key ==='brd_register_phone')
                $view['view'][$key] = explode("-",trim($val));  

            if($key ==='brd_register_handphone')
                $view['view'][$key] = explode("-",trim($val));

            if($key ==='brd_register_birthday')
                $view['view'][$key] = explode("-",trim($val));

            
        }
        

        $view['view']['brd_name'] = trim(element('brd_name', $board));  
        

        // $data['meta'] = $this->Cmall_item_meta_model->get_all_meta(element('cit_id', $data));
        // $data['detail'] = $this->Cmall_item_detail_model->get_all_detail(element('cit_id', $data));

        // $alertmessage = $this->member->is_member()
        //  ? '회원님은 상품 페이지를 볼 수 있는 권한이 없습니다'
        //  : '비회원은 상품 페이지를 볼 수 있는 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오';
        // $access_read = $this->cbconfig->item('access_cmall_read');
        // $access_read_level = $this->cbconfig->item('access_cmall_read_level');
        // $access_read_group = $this->cbconfig->item('access_cmall_read_group');
        // $this->accesslevel->check(
        //  $access_read,
        //  $access_read_level,
        //  $access_read_group,
        //  $alertmessage,
        //  ''
        // );

        

        
        

        
        
        
        return $view['view'];
    }

    public function store_info_get($brd_id = 0)
    {

        
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_cmall_item';
        //$this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        

        
        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        

        $view['view'] = $this->_store_info($brd_id);
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

    // public function _get_cit_info($cit_id = 0,$cmall_item = array())
    // {

    //  if (element('cit_id', $cmall_item) && $cit_id !== element('cit_id', $cmall_item)) {
    //      return false;
    //  }

    //  $cit_id = (int) $cit_id;
    //  if (empty($cit_id) OR $cit_id < 1) {
    //      return false;
    //  }
    //  $data = array();
        
    //  $data['cit_image'] = cdn_url('cmallitem',element('cit_file_1',$cmall_item));
    //  $data['cit_outlink_url'] = base_url('postact/cit_link/'.$cit_id);
    //  $data['cit_inlink_url'] = cmall_item_url($cit_id);
        
    //  if(empty(element('cit_price_sale',$cmall_item)))
    //      $data['cit_price_sale_percent'] = 0;
    //  else $data['cit_price_sale_percent'] = number_format((element('cit_price',$cmall_item) - element('cit_price_sale',$cmall_item)) / element('cit_price',$cmall_item) *100);

    //  return $data;
    // }
    // 
    

    public function orderresult_delete($cor_id)
    {
        required_user_login();

        $this->load->library(array('paymentlib'));
        $mem_id = (int) $this->member->item('mem_id');

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        // $view['view']['event']['before'] = Events::trigger('before', $eventname);

        if (empty($cor_id) OR $cor_id < 1) {
            show_404();
        }

        $this->load->model(array( 'Cmall_order_model'));

        $order = $this->Cmall_order_model->get_one($cor_id);
        if ( ! element('cor_id', $order)) {
            show_404();
        }
        if ($this->member->is_admin() === false
            && (int) element('mem_id', $order) !== $mem_id) {
            alert('잘못된 접근입니다',"",400);
        }

        $this->Cmall_order_model->update(element('cor_id', $order),array('is_del' => 1));
        $result = array('msg' => 'success');
        $view['view']= $result;
        
        return $this->response($view['view'], 201);
    }


    function getconfig_get()
    {


        $this->load->model(array('Cmall_attr_model','Pet_attr_model','Cmall_kind_model'));
        $pet_attr = $this->Pet_attr_model->get_all_attr();

        $view['view']['config']['pet_age'] = element(3,$pet_attr);;
        $view['view']['config']['pet_form'] = element(2,$pet_attr);
        $view['view']['config']['pet_kind'] = element(0,$this->Cmall_kind_model->get_all_kind());
        $view['view']['config']['pet_attr'] = element(1,$pet_attr);

        return $this->response($view['view'], 200);
    }

    function getsearchkeywordrank_get()
    {


        $start_date = cdate("Y-m-d", strtotime("-1 month", time()));
        $end_date = cdate('Y-m-d');

        $this->load->model('Search_keyword_model');
        $result = $this->Search_keyword_model->get_rank($start_date, $end_date);

        $sum_count = 0;
        $arr = array();
        $max = 0;

        if ($result && is_array($result)) {
            foreach ($result as $key => $value) {
                $s = element('sek_keyword', $value);
                if ( ! isset($arr[$s])) {
                    $arr[$s] = 0;
                }
                $arr[$s]++;

                if ($arr[$s] > $max) {
                    $max = $arr[$s];
                }
                $sum_count++;

            }
        }

        $view['search']['list'] = array();
        $i = 0;
        $k = 0;
        $save_count = -1;
        $tot_count = 0;

        if (count($arr)) {
            arsort($arr);
            foreach ($arr as $key => $value) {
                $count = (int) $arr[$key];
                $view['search']['list'][$k]['count'] = $count;
                $i++;
                if ($save_count !== $count) {
                    $no = $i;
                    $save_count = $count;
                }
                $view['search']['list'][$k]['no'] = $no;

                $view['search']['list'][$k]['key'] = $key;
                $view['search']['list'][$k]['search_url'] = base_url('search/show_list?skeyword='.$key);
                
                

                
                $k++;
            }

            // $view['view']['max_value'] = $max;
            // $view['view']['sum_count'] = $sum_count;
        }
        $view['search']['list'] = array_slice($view['search']['list'],0,20,true);


        $data['search_keyword_rank'] = $view['search'];

        return $this->response($data, 200);
    }
}

