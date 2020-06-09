<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Search class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 게시물 전체 검색시 필요한 controller 입니다.
 */
class Search extends CB_Controller
{

	/**
	 * 모델을 로딩합니다
	 */
	protected $models = array('Board', 'Search_keyword');

	/**
	 * 헬퍼를 로딩합니다
	 */
	protected $helpers = array('form', 'array');

	function __construct()
	{
		parent::__construct();

		/**
		 * 라이브러리를 로딩합니다
		 */
		$this->load->library(array('pagination', 'querystring','cmalllib'));
	}


	/**
	 * 검색 페이지 함수입니다
	 */
	protected function _index($config)
	{
		

		$view = array();
		$view['view'] = array();

		$oth_id = element('oth_id', $config) ? element('oth_id', $config) : '0';
		$stype = element('stype', $config) ? element('stype', $config) : '0';
		$scategory_id = element('scategory_id', $config) ? element('scategory_id', $config) : '0';
		$skeyword = element('skeyword', $config) ? element('skeyword', $config) : '';
		$sage = element('sage', $config) ? element('sage', $config) : '0';
		$sattr = element('sattr', $config) ? element('sattr', $config) : '0';
		$sstart_price = element('sstart_price', $config) ? element('sstart_price', $config) : '0';
		$send_price = element('send_price', $config) ? element('send_price', $config) : '0';

		$page = element('page', $config) ? element('page', $config) : 1;
		$sop = element('sop', $config) ? element('sop', $config) : '';
		$findex = element('findex', $config) ? element('findex', $config) : 'cit_order';
		$forder = element('forder', $config) ? element('forder', $config) : 'DESC';
		$limit = element('limit', $config) ? element('limit', $config) : '';
		$period_second = element('period_second', $config);
		$cache_minute = element('cache_minute', $config) ? element('cache_minute', $config) : '1';

		if($scategory_id){
			$view['view']['category_nav'] = $this->cmalllib->get_nav_category($scategory_id);
			// $view['view']['category_all'] = $this->cmalllib->get_all_category();
			// $view['view']['category_id'] = $category_id;
		}

		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		
		// if ($sfield === 'post_both') {
		$sfield = array('cit_name', 'cta_tag', 'cca_value','cbr_value_kr','cbr_value_en');
		// }

		$mem_id = (int) $this->member->item('mem_id');
		// if (empty($skeyword)) {

			

		// 	/**
		// 	 * 레이아웃을 정의합니다
		// 	 */
		// 	$page_title = $this->cbconfig->item('site_meta_title_search');
		// 	$meta_description = $this->cbconfig->item('site_meta_description_search');
		// 	$meta_keywords = $this->cbconfig->item('site_meta_keywords_search');
		// 	$meta_author = $this->cbconfig->item('site_meta_author_search');
		// 	$page_name = $this->cbconfig->item('site_page_name_search');

		// 	$layoutconfig = array(
		// 		'path' => 'search',
		// 		'layout' => 'layout',
		// 		'skin' => 'search',
		// 		'layout_dir' => $this->cbconfig->item('layout_search'),
		// 		'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_search'),
		// 		'use_sidebar' => $this->cbconfig->item('sidebar_search'),
		// 		'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_search'),
		// 		'skin_dir' => $this->cbconfig->item('skin_search'),
		// 		'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_search'),
		// 		'page_title' => $page_title,
		// 		'meta_description' => $meta_description,
		// 		'meta_keywords' => $meta_keywords,
		// 		'meta_author' => $meta_author,
		// 		'page_name' => $page_name,
		// 	);
		// 	$view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
		// 	return $view['view'];
			
		// }


		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		

		$this->Board_model->allow_search_field = array('cit_id', 'cit_name', 'cta_tag', 'cca_value','cbr_value_kr','cbr_value_en'); // 검색이 가능한 필드
		$this->Board_model->search_field_equal = array('cit_id'); // 검색중 like 가 아닌 = 검색을 하는 필드


		

		$per_page = 15;
		$offset = ($page - 1) * $per_page;

		$where = array();
		
		if($sage){
            if($sage === 1)
                $where['pet_birthday > '] = cdate('Y-m-d',strtotime("-1 years"));
            if($sage === 2){
                $where['pet_birthday >= '] = cdate('Y-m-d',strtotime("-1 years"));
                $where['pet_birthday <= '] = cdate('Y-m-d',strtotime("-6 years"));
            }
            if($sage === 3)
                $where['pet_birthday < '] = cdate('Y-m-d',strtotime("-7 years"));
        }

        
        if($sattr){         
        	if(is_array($sattr))
        		$this->Board_model->group_where_in('pet_attr',impode(',',$sattr));
        	else 
        		$this->Board_model->group_where_in('pet_attr',$sattr);
        }

        if($sstart_price){            
                $where['cit_price >= '] = $sstart_price;
        }

        if($send_price){            
                $where['cit_price <='] = $send_price;
        }

        if($scategory_id){            

        	
        	
                if(is_array($scategory_id))
                	$this->Board_model->group_where_in('cca_id',impode(',',$scategory_id));
                else 
                	$this->Board_model->group_where_in('cca_id',$scategory_id);

        }
        


		$where = array(
			'brd_search' => 1,
			'brd_blind' => 0,
			'cit_status' => 1,
		);
		$like = '';
		
		$result = $this->Board_model
			->get_search_list($per_page, $offset, $where, $like, $scategory_id, $findex, $sfield, $skeyword, $sop);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;
		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$result['list'][$key]['cit_info'] = $this->cmalllib->get_default_info(element('cit_id', $val),$val);
				$result['list'][$key]['num'] = $list_num--;
			}
		}

			
		
		
			$view['view']['data'] = $result;

		if ( ! $this->cb_jwt->userdata('skeyword_'.$oth_id.'_'. urlencode($skeyword))) {
			$sfieldarray = array('post_title', 'post_content', 'post_both');
			// if (in_array($sfield2, $sfieldarray)) {
			if ($mem_id) {
				$searchinsert = array(
					'sek_keyword' => $skeyword,
					'sek_datetime' => cdate('Y-m-d H:i:s'),
					'sek_ip' => $this->input->ip_address(),
					'mem_id' => $mem_id,
					'oth_id' => $oth_id,
				);
				$this->Search_keyword_model->insert($searchinsert);
				$this->cb_jwt->set_userdata(
					'skeyword_' . urlencode($skeyword),
					1
				);
			}
			if ($oth_id) {
				$this->load->model(array('Other_model','Other_keyword_model'));

				if ($mem_id) {
					$otherinsert = array(
						'okw_keyword' => $skeyword,
						'okw_datetime' => cdate('Y-m-d H:i:s'),
						'okw_ip' => $this->input->ip_address(),
						'mem_id' => $mem_id,
						'oth_id' => $oth_id,
					);
					$this->Other_keyword_model->insert($otherinsert);
					$this->cb_jwt->set_userdata(
						'skeyword_' . urlencode($skeyword),
						1
					);
				}
				
				$this->Other_model->update_plus($oth_id, 'oth_hit', 1);
			}
		}
		// $highlight_keyword = '';
		// if ($skeyword) {
		// 	$key_explode = explode(' ', $skeyword);
		// 	if ($key_explode) {
		// 		foreach ($key_explode as $seval) {
		// 			if ($highlight_keyword) {
		// 				$highlight_keyword .= ',';
		// 			}
		// 			$highlight_keyword .= '\'' . html_escape($seval) . '\'';
		// 		}
		// 	}
		// }
		// $view['view']['highlight_keyword'] = $highlight_keyword;

		/**
		 * primary key 정보를 저장합니다
		 */
		

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('search?' . $param->replace('page'));
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
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

	public function index_get($oth_id = 0,$category_id = 0)
	{

		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_search_index';
		$this->load->event($eventname);

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);
		
		

		$config = array(
			'oth_id' => $oth_id,
			'stype' => $this->input->get('stype'),
			'category_id' => $category_id,
			'scategory_id' => $this->input->get('scategory_id'),
			'skeyword' => $this->input->get('skeyword'),
			'sage' => $this->input->get('sage'),
			'sattr' => $this->input->get('sattr'),
			'sstart_price' => $this->input->get('sstart_price'),
			'send_price' => $this->input->get('send_price'),
			'page' => $this->input->get('page'),
			'cache_minute' => 1,
		);

		$view['view'] = $this->_index($config);
		
		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_search');
		$meta_description = $this->cbconfig->item('site_meta_description_search');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_search');
		$meta_author = $this->cbconfig->item('site_meta_author_search');
		$page_name = $this->cbconfig->item('site_page_name_search');

		

		$layoutconfig = array(
			'path' => 'search',
			'layout' => 'layout',
			'skin' => 'search',
			'layout_dir' => $this->cbconfig->item('layout_search'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_search'),
			'use_sidebar' => $this->cbconfig->item('sidebar_search'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_search'),
			'skin_dir' => $this->cbconfig->item('skin_search'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_search'),
			'page_title' => $page_title,
			'meta_description' => $meta_description,
			'meta_keywords' => $meta_keywords,
			'meta_author' => $meta_author,
			'page_name' => $page_name,
		);
		// $view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
		// 
		$this->data = $view['view'];
		
		return $this->response($this->data, parent::HTTP_OK);	
	}

	

	public function _searchcountby($config)
	{

				

		$view = array();
		$view['view'] = array();

		$stype = element('stype', $config) ? element('stype', $config) : '0';
		$scategory_id = element('scategory_id', $config) ? element('scategory_id', $config) : '0';
		$skeyword = element('skeyword', $config) ? element('skeyword', $config) : '';
		$sage = element('sage', $config) ? element('sage', $config) : '0';
		$sattr = element('sattr', $config) ? element('sattr', $config) : '0';
		$sstart_price = element('sstart_price', $config) ? element('sstart_price', $config) : '0';
		$send_price = element('send_price', $config) ? element('send_price', $config) : '0';

		$page = element('page', $config) ? element('page', $config) : 1;
		$sop = element('sop', $config) ? element('sop', $config) : '';
		$findex = element('findex', $config) ? element('findex', $config) : 'cit_order';
		$forder = element('forder', $config) ? element('forder', $config) : 'DESC';
		$limit = element('limit', $config) ? element('limit', $config) : '';
		$period_second = element('period_second', $config);
		$cache_minute = element('cache_minute', $config) ? element('cache_minute', $config) : '1';

		$view['view']['child_category'] = $this->cmalllib->get_child_category($scategory_id);
			// $view['view']['category_all'] = $this->cmalllib->get_all_category();
			// $view['view']['category_id'] = $category_id;
		

		
		
		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$findex = 'cit_order asc';
		$sfield = $sfield2 = $this->input->get('sfield', null, '');
		$sop = $this->input->get('sop', null, '');
		// if ($sfield === 'post_both') {
			$sfield = array('cit_name', 'cta_tag', 'cca_value','cbr_value_kr','cbr_value_en');
		// }

		$mem_id = (int) $this->member->item('mem_id');

		$skeyword = $this->input->get('skeyword', null, '');
		// if (empty($skeyword)) {

			

		// 	/**
		// 	 * 레이아웃을 정의합니다
		// 	 */
		// 	$page_title = $this->cbconfig->item('site_meta_title_search');
		// 	$meta_description = $this->cbconfig->item('site_meta_description_search');
		// 	$meta_keywords = $this->cbconfig->item('site_meta_keywords_search');
		// 	$meta_author = $this->cbconfig->item('site_meta_author_search');
		// 	$page_name = $this->cbconfig->item('site_page_name_search');

		// 	$layoutconfig = array(
		// 		'path' => 'search',
		// 		'layout' => 'layout',
		// 		'skin' => 'search',
		// 		'layout_dir' => $this->cbconfig->item('layout_search'),
		// 		'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_search'),
		// 		'use_sidebar' => $this->cbconfig->item('sidebar_search'),
		// 		'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_search'),
		// 		'skin_dir' => $this->cbconfig->item('skin_search'),
		// 		'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_search'),
		// 		'page_title' => $page_title,
		// 		'meta_description' => $meta_description,
		// 		'meta_keywords' => $meta_keywords,
		// 		'meta_author' => $meta_author,
		// 		'page_name' => $page_name,
		// 	);
		// 	$view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
		// 	return $view['view'];
			
		// }


		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		

		$this->Board_model->allow_search_field = array('cit_id', 'cit_name', 'cta_tag', 'cca_value','cbr_value_kr','cbr_value_en'); // 검색이 가능한 필드
		$this->Board_model->search_field_equal = array('cit_id'); // 검색중 like 가 아닌 = 검색을 하는 필드

		$per_page = 15;
		$offset = ($page - 1) * $per_page;

		$where = array();
		
		if($sage){
            if($sage === 1)
                $where['pet_birthday > '] = cdate('Y-m-d',strtotime("-1 years"));
            if($sage === 2){
                $where['pet_birthday >= '] = cdate('Y-m-d',strtotime("-1 years"));
                $where['pet_birthday <= '] = cdate('Y-m-d',strtotime("-6 years"));
            }
            if($sage === 3)
                $where['pet_birthday < '] = cdate('Y-m-d',strtotime("-7 years"));
        }

        
        if($sattr){         
        	if(is_array($sattr))
        		$this->Board_model->group_where_in('pet_attr',impode(',',$sattr));
        	else 
        		$this->Board_model->group_where_in('pet_attr',$sattr);
        }

        if($sstart_price){            
                $where['cit_price >= '] = $sstart_price;
        }

        if($send_price){            
                $where['cit_price <='] = $send_price;
        }

        
        // $child_category = array();
        // foreach($view['view']['child_category'] as $value){       	
        // 	array_push($child_category,element('cca_id',$value));
        // }

        // if(!empty($child_category))
        // 	$this->Board_model->group_where_in('cca_id',$child_category);
        
        if($scategory_id){            
            if(is_array($scategory_id))
            	$this->Board_model->group_where_in('cca_id',impode(',',$scategory_id));
            else 
            	$this->Board_model->group_where_in('cca_id',$scategory_id);
        }


		$where = array(
			'brd_search' => 1,
			'brd_blind' => 0,
			'cit_status' => 1,
		);
		$like = '';
		
		$result = $this->Board_model
			->get_search_list('','', $where, $like, $category_id, $findex, $sfield, $skeyword, $sop);
		$data = array();
		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {

				$data[$val['cca_parent']][$val['cca_id']] = $val;

				$data[$val['cat_parent']][$val['cat_id']] = $val;
				 
			}
		}

			
		print_r2($data);
		
			$view['view']['data'] = $result;

		if ( ! $this->cb_jwt->userdata('skeyword_'.$oth_id.'_'. urlencode($skeyword))) {
			$sfieldarray = array('post_title', 'post_content', 'post_both');
			// if (in_array($sfield2, $sfieldarray)) {
			if ($mem_id) {
				$searchinsert = array(
					'sek_keyword' => $skeyword,
					'sek_datetime' => cdate('Y-m-d H:i:s'),
					'sek_ip' => $this->input->ip_address(),
					'mem_id' => $mem_id,
					'oth_id' => $oth_id,
				);
				$this->Search_keyword_model->insert($searchinsert);
				$this->cb_jwt->set_userdata(
					'skeyword_' . urlencode($skeyword),
					1
				);
			}
			if ($oth_id) {
				$this->load->model(array('Other_model'));
				$this->Other_model->update_plus(element('oth_id', $data), 'oth_hit', 1);
			}
		}
		// $highlight_keyword = '';
		// if ($skeyword) {
		// 	$key_explode = explode(' ', $skeyword);
		// 	if ($key_explode) {
		// 		foreach ($key_explode as $seval) {
		// 			if ($highlight_keyword) {
		// 				$highlight_keyword .= ',';
		// 			}
		// 			$highlight_keyword .= '\'' . html_escape($seval) . '\'';
		// 		}
		// 	}
		// }
		// $view['view']['highlight_keyword'] = $highlight_keyword;

		/**
		 * primary key 정보를 저장합니다
		 */
		$view['view']['primary_key'] = $this->Post_model->primary_key;

		/**
		 * 페이지네이션을 생성합니다
		 */
		

		

		
		return $view['view'];
			
	}


	public function searchcountby_get()
	{

		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_search_index';
		$this->load->event($eventname);

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);
		
		$config = array(
			'oth_id' => $oth_id,
			'stype' => $this->input->get('stype'),
			'category_id' => $category_id,
			'scategory_id' => $this->input->get('scategory_id'),
			'skeyword' => $this->input->get('skeyword'),
			'sage' => $this->input->get('sage'),
			'sattr' => $this->input->get('sattr'),
			'sstart_price' => $this->input->get('sstart_price'),
			'send_price' => $this->input->get('send_price'),
			'page' => $this->input->get('page'),
			'cache_minute' => 1,
		);

		$view['view'] = $this->_searchcountby($config);
		

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_search');
		$meta_description = $this->cbconfig->item('site_meta_description_search');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_search');
		$meta_author = $this->cbconfig->item('site_meta_author_search');
		$page_name = $this->cbconfig->item('site_page_name_search');

		

		$layoutconfig = array(
			'path' => 'search',
			'layout' => 'layout',
			'skin' => 'search',
			'layout_dir' => $this->cbconfig->item('layout_search'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_search'),
			'use_sidebar' => $this->cbconfig->item('sidebar_search'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_search'),
			'skin_dir' => $this->cbconfig->item('skin_search'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_search'),
			'page_title' => $page_title,
			'meta_description' => $meta_description,
			'meta_keywords' => $meta_keywords,
			'meta_author' => $meta_author,
			'page_name' => $page_name,
		);
		// $view['view']['layout'] = $this->managelayout->front($layoutconfig, $this->cbconfig->get_device_view_type());
		// 
		$this->data = $view['view'];
		
		print_r2($this->data);
		return $this->response($this->data, parent::HTTP_OK);	
	}

}
