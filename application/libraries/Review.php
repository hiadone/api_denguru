<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Review class
 *
 * Copyright (c) CIReview <www.cireview.co.kr>
 *
 * @author CIReview (develop@cireview.co.kr)
 */

/**
 * review table 을 주로 관리하는 class 입니다.
 */
class Review extends CI_Controller
{

    private $CI;
    private $review_id;

    function __construct()
    {
        $this->CI = & get_instance();
    }


    /**
     * review table 의 정보를 얻습니다
     */
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
   


    
    public function convert_default_info($review = array())
    {
        
        $cre_id = (int) element('cre_id', $review);
        if (empty($cre_id) OR $cre_id < 1) {
            return false;
        }
        

        $review['cre_title'] = html_escape(element('cre_title',$review));

        // $review['itemreviewpost_url'] = base_url('cmall_review/itemreviewpost/'.element('cit_id',$review));

        $review['reviewupdate_url'] = base_url('cmall_review/reviewwrite/'.element('cit_id',$review).'/'.element('cre_id',$review));

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

        for ($k = 1; $k <= 10; $k++) {
            
            
        }

        return $review;
    }


    public function get_default_info($cre_id = 0,$arr = array())
    {
        
        
        if (empty($cre_id) OR $cre_id < 1) {
            return $arr;
        }
        
        $review = array();

        $review['cre_title'] = html_escape($this->item_id('cre_title',$cre_id));
        
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
                        $file = $this->CI->Review_file_model->get_one('', '', $imagewhere, '', '', 'rfi_id', 'ASC');
                        if (element('rfi_filename', $file)) {
                            $view['view']['list'][$key]['review_image'] = cdn_url('cmall_review', element('rfi_filename', $file));
                        }
                    } 

                    if (element('cre_file', $value)) {
                        $imagewhere = array(
                            'cre_id' => element('cre_id', $value),
                            'rfi_is_image' => 0,
                        );
                        $file = $this->CI->Review_file_model->get_one('', '', $imagewhere, '', '', 'rfi_id', 'ASC');
                        if (element('rfi_filename', $file)) {
                            $view['view']['list'][$key]['review_file'] = cdn_url('cmall_review', element('rfi_filename', $file));
                        }
                    } 
                
            }
        }

        
        return $view['view'];
        
    }

    public function delete_review($cre_id = 0)
    {
        $cre_id = (int) $cre_id;
        if (empty($cre_id) OR $cre_id < 1) {
            return;
        }

        $view['view'] = array();
        $this->CI->load->model(array('Cmall_review_model','Review_file_model'));
        $this->CI->load->library(array('cmalllib','aws_s3'));
        
        $review = $this->CI->Cmall_review_model->get_one($cre_id);
        
        
        $this->CI->Cmall_review_model->delete($cre_id);
        $cntresult = $this->CI->cmalllib->update_review_count(element('cit_id', $review));
        
        $deletewhere = array(
           'cre_id' => $cre_id,
        );

        // 첨부 파일 삭제
        $crefiles = $this->CI->Review_file_model->get('', '', $deletewhere);
        if ($crefiles) {
           foreach ($crefiles as $crefiles) {
               @unlink(config_item('uploads_dir') . '/cmall_review/' . element('rfi_filename', $crefiles));

               $deleted = $this->CI->aws_s3->delete_file(config_item('s3_folder_name') . '/cmall_review/' . element('rfi_filename', $crefiles));

               $this->CI->Review_file_model->delete(element('rfi_id', $crefiles));
           }
        }

        
        return true;
        
    }
}



