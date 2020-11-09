<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Denguruapi class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * cmall table 을 관리하는 class 입니다.
 */
class Denguruapi extends CI_Controller
{

    private $CI;

    function __construct()
    {
        $this->CI = & get_instance();
    }


    public function get_child_category($cca_parent_id = 0)
    {
        
        $this->CI->load->model('Cmall_category_model');

        $my_category = $cca_parent_id;

        $result = array();
        
        $result = $this->CI->Cmall_category_model->get_category_child($my_category);        

        return $result;
    }

    public function get_wish_info($data = array())
    {
        
        $this->CI->load->model(array( 'Cmall_storewishlist_model','Cmall_wishlist_model'));

        $data['addstorewish_url'] = site_url('cmall/storewish/'.element('brd_id',$data));
        $data['storewishstatus'] = 0;
        if(!empty($this->CI->member->is_member())){
            $where = array(
                'mem_id' => $this->CI->member->is_member(),
                'brd_id' => element('brd_id',$data),
            );
            $data['storewishstatus'] = $this->CI->Cmall_storewishlist_model->count_by($where);  
        }
        
        
        $data['additemwish_url'] = site_url('cmall/itemwish/'.element('cit_id',$data));     
        $data['itemwishstatus'] = 0;

        if(!empty($this->CI->member->is_member())){
            $where = array(
                'mem_id' => $this->CI->member->is_member(),
                'cit_id' => element('cit_id',$data),
            );
            $data['itemwishstatus'] = $this->CI->Cmall_wishlist_model->count_by($where);    
        }

        return $data;
    }

    public function convert_cit_info($cmall_item = array())
    {
        
        

        
        $cit_id = (int) element('cit_id',$cmall_item);
        if (empty($cit_id) OR $cit_id < 1) {
            return false;
        }

        $cmall_item['cit_image'] = cdn_url('cmallitem',element('cit_file_1',$cmall_item));
        $cmall_item['cit_outlink_url'] = base_url('postact/cit_link/'.$cit_id);
        $cmall_item['cit_inlink_url'] = cmall_item_url($cit_id);
        if(empty(element('cit_price_sale',$cmall_item)))
            $cmall_item['cit_price_sale_percent'] = 0;
        else $cmall_item['cit_price_sale_percent'] = number_format((element('cit_price',$cmall_item,0) - element('cit_price_sale',$cmall_item,0)) / element('cit_price',$cmall_item,0) * 100);
        $cmall_item['cit_brand'] = element('cbr_value_kr',$cmall_item,element('cbr_value_en',$cmall_item,''));

        return $cmall_item;
    }

    public function get_cit_info($cit_id = 0,$arr = array())
    {
        
        

        
        
        if (empty($cit_id) OR $cit_id < 1) {
            return $arr;
        }
        
        $cmall_item = array();
        $this->CI->load->model('Board_model');
        $cit_info = $this->CI->Board_model->get_cit_one($cit_id);

        $cmall_item = $this->convert_cit_info($cit_info);

        if(empty($cmall_item)) {
            $cmall_item['cit_id'] = 0;
            $cmall_item['cit_name'] = '없는 상품입니다';
            $cmall_item['cit_review_average'] = 0;
            $cmall_item['cit_price'] = 0;
            $cmall_item['cit_price_sale'] = 0;            

            $cmall_item['cit_image'] = cdn_url('cmallitem',element('cit_file_1',$cmall_item));
            $cmall_item['cit_outlink_url'] = base_url('postact/cit_link/'.$cit_id);
            $cmall_item['cit_inlink_url'] = cmall_item_url($cit_id);
            if(empty(element('cit_price_sale',$cmall_item)))
             $cmall_item['cit_price_sale_percent'] = 0;
            else $cmall_item['cit_price_sale_percent'] = number_format((element('cit_price',$cmall_item) - element('cit_price_sale',$cmall_item)) / element('cit_price',$cmall_item) * 100);

            $cmall_item['cit_brand'] = element('cbr_value_kr',$cmall_item) ? element('cbr_value_kr',$cmall_item) : element('cbr_value_en',$cmall_item);
        }
        $cmall_item = array_merge($arr, $cmall_item);

        return $cmall_item;
    }

    // public function get_cmall_item($cit_id = 0, $cit_key = '')
    // {
    //     if (empty($cit_id) && empty($cit_key)) {
    //         return false;
    //     }

    //     if ($cit_id) {
    //         $this->CI->load->model('Cmall_item_model');
    //         $cmall_item = $this->CI->Cmall_item_model->get_one($cit_id);
    //     } elseif ($cit_key) {
    //         $where = array(
    //             'cit_key' => $cit_key,
    //         );
    //         $this->CI->load->model('Cmall_item_model');
    //         $cmall_item = $this->CI->Cmall_item_model->get_one('', '', $where);
    //     } else {
    //         return false;
    //     }

    //     if (element('cit_id', $cmall_item)) {
    //         $this->cmall_item_id[element('cit_id', $cmall_item)] = $cmall_item;
    //     }
    //     if (element('cit_key', $cmall_item)) {
    //         $this->cmall_item_key[element('cit_key', $cmall_item)] = $cmall_item;
    //     }
    // }

    // public function item_id($column = '', $cit_id = 0)
    // {
    //     if (empty($column)) {
    //         return false;
    //     }
    //     $cit_id = (int) $cit_id;
    //     if (empty($cit_id) OR $cit_id < 1) {
    //         return false;
    //     }
    //     if ( ! isset($this->cmall_item_id[$cit_id])) {
    //         $this->get_cmall_item($cit_id, '');
    //     }
        
    //     if ( ! isset($this->cmall_item_id[$cit_id])) {
    //         return false;
    //     }
    //     $cmall_item = $this->cmall_item_id[$cit_id];

    //     return isset($cmall_item[$column]) ? $cmall_item[$column] : false;
    // }

    public function cit_latest($config)
    {   

        $this->CI->load->model('Board_model');
            
        $cache_minute = element('cache_minute', $config);
        $where['cit_status'] = 1;
        $where['cit_is_del'] = 0;
        if (element('cit_type1', $config)) {
            $where['cit_type1'] = 1;
        }
        if (element('cit_type2', $config)) {
            $where['cit_type2'] = 1;
        }
        if (element('cit_type3', $config)) {
            $where['cit_type3'] = 1;
        }
        if (element('cit_type4', $config)) {
            $where['cit_type4'] = 1;
        }
        $limit = element('limit', $config) ? element('limit', $config) : 4;
        

        $cachename = 'latest/cit-' . element('cit_type1', $config).element('cit_type2', $config).element('cit_type3', $config).element('cit_type4', $config) . '-' . $limit . '-' . cdate('Y-m-d');

        if ( ! $result = $this->CI->cache->get($cachename)) {
            
            $this->CI->db->join('cmall_item', 'board.brd_id = cmall_item.brd_id', 'inner');
            $this->CI->db->join('cmall_brand', 'cmall_item.cbr_id = cmall_brand.cbr_id', 'inner');
            $this->CI->db->where($where);
            $this->CI->db->limit($limit);
            $this->CI->db->order_by('cit_order', 'asc');
            $qry = $this->CI->db->get('board');
            $result = $qry->result_array();
            check_cache_dir('latest');
            $this->CI->cache->save($cachename, $result, $cache_minute);
        }
        return $result;
    }

    public function convert_brd_info($board = array())
    {

        // if (element('brd_id', $board)) {
        //  $board_meta = $this->get_all_meta(element('brd_id', $board));
        //  if (is_array($board_meta)) {
        //      $board = array_merge($board, $board_meta);
        //  }
        // }

        // if (element('brd_id', $board) && $brd_id === element('brd_id', $board)) {
        //  $this->board_id[element('brd_id', $board)] = $board;
        // }
        // if (element('brd_key', $board)) {
        //  $this->board_key[element('brd_key', $board)] = $board;
        // }
        
        $brd_id = (int) element('brd_id',$board);
        if (empty($brd_id) OR $brd_id < 1) {
            return false;
        }
        
        
        $board['brd_image'] = cdn_url('board',element('brd_image',$board,''));
        $board['brd_outlink_url'] = base_url('postact/brd_link/'.$brd_id);
        $board['brd_inlink_url'] = base_url('cmall/store/'.$brd_id);

        

        return $board;
    }

    public function get_brd_info($brd_id = 0,$arr = array())
    {   
        if (empty($brd_id) OR $brd_id < 1) {
            return false;
        }

        $this->CI->load->library(array('board'));

        $board = array();
        if($this->CI->board->item_id('brd_id',$brd_id)){
            $board['brd_id'] = $brd_id;
            $board['brd_name'] = $this->CI->board->item_id('brd_name',$brd_id);
            $board['brd_image'] = cdn_url('board',$this->CI->board->item_id('brd_image',$brd_id));
            $board['brd_outlink_url'] = base_url('postact/brd_link/'.$brd_id);
            $board['brd_inlink_url'] = base_url('cmall/store/'.$brd_id);
        } else {
            $board['brd_id'] = 0;
            $board['brd_name'] = '없는 스토어 입니다.';
            $board['brd_image'] = thumb_url('board');
            $board['brd_outlink_url'] = '';
            $board['brd_inlink_url'] = '';
        }

        $board = array_merge($board, $arr);

        return $board;
    }

    public function get_popular_brd_tags($brd_id = 0, $limit = 10)
    {
        $cachename = 'latest/get_popular_brd_tags' . $brd_id . '_' . $limit;
        $data = array();

        if ( ! $data = $this->CI->cache->get($cachename)) {

            $this->CI->load->model( array('Cmall_item_model'));
            $result = $this->CI->Cmall_item_model->get_popular_tags($brd_id, $limit);

            $data['result'] = $result;
            $data['cached'] = '1';
            check_cache_dir('latest');
            $this->CI->cache->save($cachename, $data, 86400);

        }
        return isset($data['result']) ? $data['result'] : array();
    }

    public function get_popular_brd_attr($brd_id = 0, $limit = 10)
    {
        $cachename = 'latest/get_popular_brd_attr' . $brd_id . '_' . $limit;
        $data = array();

        if ( ! $data = $this->CI->cache->get($cachename)) {

            $this->CI->load->model( array('Cmall_item_model'));
            $result = $this->CI->Cmall_item_model->get_popular_attr($brd_id, $limit);

            $data['result'] = $result;
            $data['cached'] = '1';
            check_cache_dir('latest');
            $this->CI->cache->save($cachename, $data, 86400);

        }
        return isset($data['result']) ? $data['result'] : array();
    }

    public function get_popular_cit_tags($cit_id = 0, $limit = 10)
    {
        $cachename = 'latest/get_popular_cit_tags' . $cit_id . '_' . $limit;
        $data = $view = $return = $return2 = $arr1 = $arr2 = array();

        if ( ! $data = $this->CI->cache->get($cachename)) {

            $this->CI->load->model( array('Cmall_item_model'));
            $result = $this->CI->Cmall_item_model->get_popular_cit_tags($cit_id);

            $data['result'] = $result;
            $data['cached'] = '1';
            check_cache_dir('latest');
            $this->CI->cache->save($cachename, $data, 86400);

        }

        foreach($data['result'] as $val){
            if(element(element('ckd_value_kr',$val),element('ckd_value_kr',$view)))
                $view['ckd_value_kr'][element('ckd_value_kr',$val)]++;
            else
                $view['ckd_value_kr'][element('ckd_value_kr',$val)]=1;

            if(element(element('pag_value',$val),element('pag_value',$view)))
                $view['pag_value'][element('pag_value',$val)]++;
            else
                $view['pag_value'][element('pag_value',$val)]=1;

            if(element(element('cat_value',$val),element('cat_value',$view)))
                $view['cat_value'][element('cat_value',$val)]++;
            else
                $view['cat_value'][element('cat_value',$val)]=1;
        }
        
        
        
        $i=0;
        foreach($view as  $value){
            asort($value);
            $lastValue = end($value);
            $lastKey = key($value); 
            $arr2[$lastKey] = $lastValue;
            array_pop($value);
            $arr1 = array_merge($arr1,$value);
            

        }
        arsort($arr1);
        arsort($arr2);
        
        $return = array_merge($arr2,$arr1);
        $return = array_slice($return,0,10);
        foreach($return as $key => $val){
            array_push($return2,$key);
        }
        
        
        return isset($return2) ? $return2 : array();
    }

    public function get_all_crawl($brd_id = 0)
    {
        $brd_id = (int) $brd_id;
        if (empty($brd_id) OR $brd_id < 1) {
            return false;
        }
        $this->CI->load->model('Board_crawl_model');
        $result = $this->CI->Board_crawl_model->get_one('','brd_id,brd_url,brd_order_url,brd_register_url,brd_orderstatus_url,brd_phone,brd_order_key,brd_url_key,brd_register_id,brd_register_name,brd_register_zipcode,brd_register_addr1,brd_register_addr2,brd_register_hidden,brd_register_phone,brd_register_handphone,brd_register_email,brd_register_birthday,brd_register_quest,brd_register_answer,brd_nomember_order_url',array('brd_id' => $brd_id));

        return $result;
    }

    public function get_review($cre_id = 0, $brd_key = '')
    {
        if (empty($cre_id)) {
            return false;
        }

        if ($cre_id) {
            $this->CI->load->model('Cmall_review_model');
            $review = $this->CI->Cmall_review_model->get_one($cre_id);
        }  else {
            return false;
        }
        
        

        if (element('cre_id', $review)) {
            $this->review_id[element('cre_id', $review)] = $review;
        }
        
    }


    



    /**
     * item 을 cre_id 에 기반하여 얻습니다
     */
    public function item_id($column = '', $cre_id = 0)
    {
        if (empty($column)) {
            return false;
        }
        $cre_id = (int) $cre_id;
        if (empty($cre_id) OR $cre_id < 1) {
            return false;
        }
        if ( ! isset($this->review_id[$cre_id])) {
            $this->get_review($cre_id, '');
        }
        
        $review = $this->review_id[$cre_id];

        return isset($review[$column]) ? $review[$column] : false;
    }


   


    /**
     * 모든 item 을 cre_id 에 기반하여 얻습니다
     */
    public function item_all($cre_id = 0)
    {
        $cre_id = (int) $cre_id;
        if (empty($cre_id) OR $cre_id < 1) {
            return false;
        }
        if ( ! isset($this->review_id[$cre_id])) {
            $this->get_review($cre_id, '');
        }
        if ( ! isset($this->review_id[$cre_id])) {
            return false;
        }

        return $this->review_id[$cre_id];
    }


    


    


    /**
     * 인기태그를 가져옵니다
     */
    public function get_popular_tags($start_date = '', $limit = '')
    {
        $cachename = 'latest/get_popular_tags_' . $start_date . '_' . $limit;
        $data = array();

        if ( ! $data = $this->CI->cache->get($cachename)) {

            $this->CI->load->model( array('Post_tag_model'));
            $result = $this->CI->Post_tag_model->get_popular_tags($start_date, $limit);

            $data['result'] = $result;
            $data['cached'] = '1';
            check_cache_dir('latest');
            $this->CI->cache->save($cachename, $data, 60);

        }
        return isset($data['result']) ? $data['result'] : false;
    }
   


    
    public function convert_review_info($review = array())
    {
        
        $cre_id = (int) element('cre_id', $review);
        if (empty($cre_id) OR $cre_id < 1) {
            return false;
        }
        

        $review['cre_good'] = html_escape(element('cre_good',$review));
        $review['cre_bad'] = html_escape(element('cre_bad',$review));
        $review['cre_tip'] = html_escape(element('cre_tip',$review));


        // $review['itemreviewpost_url'] = base_url('cmall_review/itemreviewpost/'.element('cit_id',$review));

        $review['reviewmodify_url'] = base_url('cmall_review/reviewwrite/'.element('cit_id',$review).'/'.element('cre_id',$review));

        $review['reviewdelete_url'] = base_url('cmall_review/review/'.element('cre_id',$review));

        $review['userreviewpost_url'] = base_url('cmall_review/userreviewpost/'.element('mem_id',$review));
        

        $review['reviewlike_url'] = base_url('postact/reviewlike/'.$cre_id.'/1');
        $review['reviewlikestatus'] = 0;
        if($this->CI->member->is_member()){
            
            $this->CI->load->model(array('Blame_model','Like_model'));
            $where = array(
                'target_id' => $cre_id,
                'target_type' => 3,
                'mem_id' => $this->CI->member->is_member(),
            );

            $review['reviewlikestatus'] = $this->CI->Like_model->count_by($where);  
        }

        $review['reviewblame_url'] = base_url('postact/review_blame/'.$cre_id);
        $review['reviewblamestatus'] = 0;
        if($this->CI->member->is_member()){
            

            $where = array(
                'target_id' => $cre_id,
                'target_type' => 3,
                'mem_id' => $this->CI->member->is_member(),
            );

            $review['reviewblamestatus'] = $this->CI->Like_model->count_by($where);  
        }

        if (element('cre_image', $review) || element('cre_image', $review))
            $this->CI->load->model('Review_file_model');

        $review['review_image'] = array();
        if (element('cre_image', $review)) {
            $imagewhere = array(
                'cre_id' => element('cre_id', $review),
                'rfi_is_image' => 1,
            );
            $file = $this->CI->Review_file_model->get('', '', $imagewhere, '', '', 'rfi_id', 'ASC');

            if ($file && is_array($file)) {
                foreach ($file as $fkey => $fvalue) {

                    

                    $review['review_image'][] = array('rfi_id' =>element('rfi_id', $fvalue),'uri' =>cdn_url('cmall_review', element('rfi_filename', $fvalue)));
                }
            }
            
        } 

        $review['review_file'] = array();
        if (element('cre_file', $review)) {
            $imagewhere = array(
                'cre_id' => element('cre_id', $review),
                'rfi_is_image' => 0,
            );
            $file = $this->CI->Review_file_model->get('', '', $imagewhere, '', '', 'rfi_id', 'ASC');
            if ($file && is_array($file)) {
                foreach ($file as $fkey => $fvalue) {
                    $review['review_file'][] = array('rfi_id' =>element('rfi_id', $fvalue),'uri' =>cdn_url('cmall_review', element('rfi_filename', $fvalue)));
                }
            }
        } 

        return $review;
    }


    public function get_review_info($cre_id = 0,$arr = array())
    {
        
        
        if (empty($cre_id) OR $cre_id < 1) {
            return $arr;
        }
        
        $review = array();
        
        $review['reviewlike_url'] = base_url('postact/reviewlike/'.$cre_id.'/1');
        $review['reviewlikestatus'] = 0;
        if($this->CI->member->is_member()){
            
            $this->CI->load->model(array('Blame_model','Like_model'));
            $where = array(
                'target_id' => $cre_id,
                'target_type' => 3,
                'mem_id' => $this->CI->member->is_member(),
            );

            $review['reviewlikestatus'] = $this->CI->Like_model->count_by($where);  
        }

        $review['reviewblame_url'] = base_url('postact/review_blame/'.$cre_id);
        $review['reviewblamestatus'] = 0;
        if($this->CI->member->is_member()){
            

            $where = array(
                'target_id' => $cre_id,
                'target_type' => 3,
                'mem_id' => $this->CI->member->is_member(),
            );

            $review['reviewblamestatus'] = $this->CI->Like_model->count_by($where);  
        }

        return $review;
    }

    public function get_popular_item_review($cit_id = 0)
    {
        $cit_id = (int) $cit_id;
        if (empty($cit_id) OR $cit_id < 1) {
            return;
        }

        $view['view'] = array();
        $this->CI->load->model(array('Cmall_review_model','Review_file_model'));

        $popular = $this->CI->Cmall_review_model->get_popular($cit_id,6);
        
        
        
        if ($popular && is_array($popular)) {
            foreach ($popular as $key => $value) {              
                
                    if (element('cre_image', $value)) {
                        $imagewhere = array(
                            'cre_id' => element('cre_id', $value),
                            'rfi_is_image' => 1,
                        );
                        $file = $this->CI->Review_file_model->get('', '', $imagewhere, '', '', 'rfi_id', 'ASC');

                        foreach($file as $fval){
                            if (element('rfi_filename', $fval)) {
                                $view['view']['list']['review_image'][] =
                                    array('rfi_id' =>element('rfi_id', $fval),'uri' =>cdn_url('cmall_review', element('rfi_filename', $fval)));
                                
                                // $view['view']['list']['review_image'][element('rfi_id', $fval)] = cdn_url('cmall_review', element('rfi_filename', $fval));
                            }
                        }
                    } 

                    if (element('cre_file', $value)) {
                        $imagewhere = array(
                            'cre_id' => element('cre_id', $value),
                            'rfi_is_image' => 0,
                        );
                        $file = $this->CI->Review_file_model->get('', '', $imagewhere, '', '', 'rfi_id', 'ASC');

                        foreach($file as $fval){
                            if (element('rfi_filename', $fval)) {
                                $view['view']['list']['review_file'][] = 
                                    array('rfi_id' =>element('rfi_id', $fval),'uri' =>cdn_url('cmall_review', element('rfi_filename', $fval)));

                                // [element('rfi_id', $fval)] = cdn_url('cmall_review', element('rfi_filename', $fval));
                                // $view['view']['list']['review_file'][element('rfi_id', $fval)] = cdn_url('cmall_review', element('rfi_filename', $fval));
                            }
                        }
                    } 
                
            }
        }

        
        return $view['view'];
        
    }

    public function get_mem_info($_mem_id = 0,$arr = array())
    {
        
        
        
        if (empty($_mem_id) OR $_mem_id < 1) {
            return false;
        }




        
        

        $this->CI->load->model(
            array(
                'Member_pet_model','Reviewer_model'
            )
        );

        $data = array();

        if($this->CI->member->is_member() !== $_mem_id){
            $data['member_reviewer_url']= base_url('/profile/reviewer/'.$_mem_id);

            
            $data['reviewerstatus'] = 0; //리뷰어로 선정했는지 여부 

            if(!empty($this->CI->member->is_member())){
                $countwhere = array(
                'mem_id' => $this->CI->member->is_member(),
                'target_mem_id' => $_mem_id,
                );
                $data['reviewerstatus'] = $this->CI->Reviewer_model
                ->count_by($countwhere);  
            }
        }
        $member = $this->CI->Member_model->get_by_memid($_mem_id);
        
        
        

        
        
        $pet = $this->CI->Member_pet_model->get_one('','pet_id',array('mem_id' => element('mem_id', $member),'pet_main' => 1));
        
        $pet_info = $this->get_pet_info(element('mem_id', $member),element('pet_id', $pet));

        $data['mem_id'] = element('mem_id',$member);
        $data['mem_userid'] = element('mem_userid',$member);
        $data['mem_email'] = element('mem_email',$member);
        $data['mem_username'] = element('mem_username',$member);
        $data['mem_nickname'] = element('mem_nickname',$member);
        $data['petwrite_url'] = base_url('mypage/petwrite');

        if (is_array($pet_info)) {
            $data = array_merge($data, $pet_info);
        }

        // $pet_form = $this->CI->Pet_attr_model->get_attr_info(element('pat_id',$member));

        // $pet_kind = $this->CI->Cmall_kind_model->get_kind_info(element('ckd_id',$member));
        

        

        // $data['pet_id'] = element('pet_id',$member);
        // $data['pet_name'] = element('pet_name',$member);
        // $data['pet_birthday'] = element('pet_birthday',$member);
        // $data['pet_age'] = date('Y') - cdate('Y',strtotime($data['pet_birthday']));
        // $data['pet_sex'] = element('pet_sex',$member);
        // $data['pet_photo_url'] = cdn_url('member_photo',element('pet_photo',$member));
        // $data['pet_neutral'] = element('pet_neutral',$member);
        // $data['pet_weight'] = element('pet_weight',$member);

        // $data['pat_id'] = element('pat_id',$member);
        // $data['pet_form_str'] = element('pat_value',$pet_form);

        // $data['pet_kind'] = element('ckd_value_kr',$pet_kind,element('ckd_value_en',$pet_kind));

        // // $data['ckd_size'] = element('ckd_size',$pet_kind);
        // $data['ckd_size_str'] = element('ckd_size',$pet_kind) == '4' ? '소형견' : element('ckd_size',$pet_kind) == '5' ? '중형견' : '대형견';


        

        // $data['ckd_id'] = element('ckd_id',$member);
        
        // $data['pet_attr'] = $this->CI->Pet_attr_model->get_attr(element('pet_id',$member));
        
        
        // $data['pet_is_allergy'] = element('pet_is_allergy',$member);

        // $data['pet_allergy_rel'] = $this->CI->Pet_allergy_model->get_allergy(element('pet_id',$member));


            

        $data = array_merge($arr, $data);

        return $data;
    }

    public function get_mem_pet_info($mem_id)
    {
        
        
        $mem_id = (int) $mem_id;
        if (empty($mem_id) OR $mem_id < 1) {
            return false;
        }




        // if($this->is_member() === $_mem_id){
        //  $data = array();
        //  $data['mem_id'] = $this->item('mem_id');
        //  $data['mem_userid'] = $this->item('mem_userid');
        //  $data['mem_email'] = $this->item('mem_email');
        //  $data['mem_username'] = $this->item('mem_username');
        //  $data['mem_nickname'] = $this->item('mem_nickname');
        //  $data['social'] = $this->item('social');
        //  $data['pet_id'] = $this->item('pet_id');
        //  $data['pet_name'] = $this->item('pet_name');
        //  $data['pet_birthday'] = $this->item('pet_birthday');
        //  $data['pet_age'] = date('Y') - cdate('Y',strtotime($data['pet_birthday']));
        //  $data['pet_sex'] = $this->item('pet_sex');
        //  $data['pet_photo_url'] = cdn_url('member_photo',$this->item('pet_photo'));
        //  $data['pet_neutral'] = $this->item('pet_neutral');
        //  $data['pet_weight'] = $this->item('pet_weight');
        //  $data['pet_form'] = element($this->item('pet_form'),config_item('pet_form'),'');
        //  $data['pet_kind'] = $this->item('pet_kind');

        //  if($this->item('pet_attr')){
        //      foreach(explode(",",$this->item('pet_attr')) as $value){
        //          $data['pet_attr'][]= element($value,config_item('pet_attr'),'');
        //      }
        //  }
            
            
        //  $data['pet_allergy'] = $this->item('pet_allergy');
        // }else{

           $this->CI->load->model(
               array(
                   'Member_pet_model'
               )
           );

           $data = array();
           $petlist = $this->CI->Member_pet_model->get('','',array('mem_id' => $mem_id),'','','pet_main','desc');

           
            if($petlist)
                foreach($petlist as $key => $value){

                    $data['list'][$key] = $this->get_pet_info(element('mem_id', $value),element('pet_id', $value));
                    
                }
            
        
            
            return $data;
    }

    public function get_pet_info($mem_id = 0,$pet_id = 0)
    {
        
        
        
        if (empty($mem_id) OR $mem_id < 1) {
            return false;
        }

        if (empty($pet_id) OR $pet_id < 1) {
            return false;
        }




        
        

        $this->CI->load->model(
            array(
                'Member_pet_model','Pet_attr_model','Pet_allergy_model','Cmall_kind_model'
            )
        );

        $data = array();
        
        
        $pet = $this->CI->Member_pet_model->get_one('','',array('mem_id' => $mem_id,'pet_id' => $pet_id));
        
        if (empty($pet)) {
            return false;
        }

        $pet_form = $this->CI->Pet_attr_model->get_attr_info(element('pat_id',$pet));

        $pet_kind = $this->CI->Cmall_kind_model->get_kind_info(element('ckd_id',$pet));
        

        

        $data['pet_id'] = element('pet_id',$pet);
        $data['pet_name'] = element('pet_name',$pet);
        $data['pet_birthday'] = element('pet_birthday',$pet);
        $data['pet_age'] = date('Y') - cdate('Y',strtotime($data['pet_birthday']));
        $data['pet_sex'] = element('pet_sex',$pet);
        $data['pet_photo_url'] = cdn_url('member_photo',element('pet_photo',$pet));
        $data['pet_neutral'] = element('pet_neutral',$pet);
        $data['pet_weight'] = element('pet_weight',$pet);

        $data['pat_id'] = element('pat_id',$pet);
        $data['pet_form_str'] = element('pat_value',$pet_form);

        $data['pet_kind'] = element('ckd_value_kr',$pet_kind,element('ckd_value_en',$pet_kind));

        // $data['ckd_size'] = element('ckd_size',$pet_kind);
        $data['ckd_size_str'] = element('ckd_size',$pet_kind) == '4' ? '소형견' : element('ckd_size',$pet_kind) == '5' ? '중형견' : '대형견';


        

        $data['ckd_id'] = element('ckd_id',$pet);
        
        $data['pet_attr'] = $this->CI->Pet_attr_model->get_attr(element('pet_id',$pet));
        
        
        $data['pet_is_allergy'] = element('pet_is_allergy',$pet);

        $data['pet_allergy_rel'] = $this->CI->Pet_allergy_model->get_allergy(element('pet_id',$pet));


            

        

        return $data;
    }
}
