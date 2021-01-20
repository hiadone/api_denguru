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
	protected $models = array('Board', 'Search_keyword','Cmall_attr','Cmall_kind','Cmall_category');

	/**
	 * 헬퍼를 로딩합니다
	 */
	protected $helpers = array('form', 'array','cmall');

	function __construct()
	{
		parent::__construct();

		/**
		 * 라이브러리를 로딩합니다
		 */
		$this->load->library(array('pagination', 'querystring','cmalllib','denguruapi'));
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
		$ssort = element('ssort', $config) ? element('ssort', $config) : 'cit_type2';
		$option = element('option', $config) ? element('option', $config) : 'show_list';
		$skeyword = element('skeyword', $config) ? element('skeyword', $config) : '';

		$scategory = element('scategory', $config) ? element('scategory', $config) : false;
		if(is_array($scategory)) $scategory = array_filter($scategory);		
		$sage = element('sage', $config) ? element('sage', $config) : false;
		if(is_array($sage)) $sage = array_filter($sage);
		$sattr = element('sattr', $config) ? element('sattr', $config) : false;
		if(is_array($sattr)) $sattr = array_filter($sattr);
		$skind = element('skind', $config) ? element('skind', $config) : false;
		if(is_array($skind)) $skind = array_filter($skind);

		$sstart_price = element('sstart_price', $config) ? element('sstart_price', $config) : '0';
		$send_price = element('send_price', $config) ? element('send_price', $config) : '0';

		$page = element('page', $config) ? element('page', $config) : 1;
		$sop = element('sop', $config) ? element('sop', $config) : '';
		$findex = element('findex', $config) ? element('findex', $config) : '(0.1/cit_order)';
		$forder = element('forder', $config) ? element('forder', $config) : 'DESC';
		$limit = element('limit', $config) ? element('limit', $config) : '';
		$period_second = element('period_second', $config);
		$cache_minute = element('cache_minute', $config) ? element('cache_minute', $config) : '1';

		
		if($ssort){
			$findex = $ssort;
		}
		
		if($ssort === 'high_price'){
			$findex = 'cit_price_sale desc,cit_price desc';
		}

		if($ssort === 'low_price'){
			$findex = 'cit_price_sale asc,cit_price asc';
		}

		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		
		// if ($sfield === 'post_both') {
		// $sfield = array('cit_name', 'cta_tag', 'cca_value','cbr_value_kr','cbr_value_en');
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
		
		$cmall_price = $cmall_kind = $cmall_color = $cmall_age = $cmall_size =  $cmall_category = array();
		$all_kind = $this->Cmall_kind_model->get_all_kind();
		$all_attr = $this->Cmall_attr_model->get_all_attr();
		$all_category = $this->Cmall_category_model->get_all_category();
		// $this->Board_model->allow_search_field = array('cit_id', 'cit_name', 'cta_tag', 'cca_value','cbr_value_kr','cbr_value_en'); // 검색이 가능한 필드
		// $this->Board_model->search_field_equal = array('cit_id'); // 검색중 like 가 아닌 = 검색을 하는 필드
		$category_child_id=array();


		

		if($scategory && is_array($scategory)){
			foreach($all_category as $key => $val){

				if($key === 0 ) continue;
				foreach($val as $key_ => $val_){
					
					if(in_array(element('cca_id',$val_),$scategory)) {
						$b = array_search($key,$scategory); 
						if($b!==FALSE) unset($scategory[$b]); 
					}
					
				}
			}	
			foreach($scategory as $val){				
				array_push($category_child_id,$val);
				
				$category_child = $this->Cmall_category_model->get_category_child($val);	

				if(!empty($category_child)){

					foreach($category_child as $cval){

						array_push($category_child_id,element('cca_id',$cval));
						
					}
				}
				
			}
		}


		$where = array(
				'brd_search' => 1,
				'brd_blind' => 0,				
			);

		$cmallwhere = 'where
				cit_status = 1
				AND cit_is_del = 0
				AND cit_is_soldout = 0
				AND (cit_price > 0 or cit_price_sale > 0)
			';
		
		$is_color=false;

		if($scategory && is_array($scategory)){
			foreach($scategory as $val){
				if(in_array($val,array(6,14,15,16,17,18,21,22,23)))
					$is_color = true;
			}
		}

			// $per_page = 20;
			$per_page = get_listnum();
			$offset = ($page - 1) * $per_page;

			if($sstart_price){            
	            $cmallwhere .=' AND (case when cit_price_sale > 0 then cit_price_sale >='.$sstart_price.' else cit_price >='.$sstart_price.' end)';
	                
	        }

	        if($send_price){            
	        	$cmallwhere .=' AND (case when cit_price_sale > 0 then cit_price_sale <='.$send_price.' else cit_price <='.$send_price.' end)';
	        }

	        
	        if($skeyword){
	        	// $cmallwhere .=' AND cit_name like "%'.$skeyword.'%"';
	        }

	        $this->Board_model->_select = 'board.brd_id,board.brd_name,board.brd_image,board.brd_blind,cmall_item.cit_id,cmall_item.cit_name,cmall_item.cit_file_1,cmall_item.cit_review_average,cmall_item.cit_price,cmall_item.cit_price_sale';
        	$set_join[] = array("
				(select cit_id,brd_id,cit_order,cit_name,cit_file_1,cit_review_average,cit_price,cit_price_sale,cbr_id,cit_version,cit_type1,cit_type2,cit_type3,cit_type4 from cb_cmall_item ".$cmallwhere.") as cb_cmall_item",'cmall_item.brd_id = board.brd_id','inner');
	       


			if($skeyword){
	        	
	        	// $this->Board_model->set_where("(cit_name like '%".$skeyword."%' OR cta_tag = '".$skeyword."' OR cca_value = '".$skeyword."' OR cat_value = '".$skeyword."' OR cbr_value_kr = '".$skeyword."' )",'',false);

	        	// $this->Board_model->set_where("(  cbr_value_kr = '".$skeyword."' )",'',false);
	            
	            $set_join[] = array("
					(select cit_id from cb_cmall_item where cit_name like '%".$skeyword."%'
					UNION
					select cit_id from cb_crawl_tag where cta_tag = '".$skeyword."' 
					UNION
					select cit_id from cb_cmall_attr_rel INNER JOIN cb_cmall_attr ON cb_cmall_attr_rel.cat_id = cb_cmall_attr.cat_id  where cat_value = '".$skeyword."'
					UNION
					select cit_id from cb_board INNER JOIN cb_cmall_item ON cb_board.brd_id = cb_cmall_item.brd_id  where brd_name like '%".$skeyword."%'
					UNION
					select cit_id from cb_cmall_brand INNER JOIN cb_cmall_item ON cb_cmall_brand.cbr_id = cb_cmall_item.cbr_id  where cbr_value_kr like '%".$skeyword."%' or cbr_value_en like '%".$skeyword."%'
					) as AAA",'cmall_item.cit_id = AAA.cit_id','inner');
	           

	            // $this->Board_model->set_join(array('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner'));
	            // $this->Board_model->set_join(array("(select cca_id,cca_value from cb_cmall_category where cca_value like '%".$skeyword."%') as cb_cmall_category",'cmall_category_rel.cca_id = cmall_category.cca_id','outter'));

	            // $this->Board_model->set_join(array("(select cit_id,cta_id,cta_tag from cb_crawl_tag where cta_tag like '%".$skeyword."%') as cb_crawl_tag",'cmall_item.cit_id = crawl_tag.cit_id','inner'));

	            // $this->Board_model->set_join(array('cmall_attr_rel','cmall_attr_rel.cit_id = cmall_item.cit_id','inner'));
	            // $this->Board_model->set_join(array("(select cat_id,cat_value from cb_cmall_attr where cat_value like '%".$skeyword."%') as cb_cmall_attr",'cmall_attr.cat_id = cmall_attr_rel.cat_id','outter'));

	            // $this->Board_model->set_join(array('cmall_brand','cmall_brand.cbr_id = cmall_item.cbr_id','inner'));

	        }
			
	        
	        if($sattr && is_array($sattr)){
    			
    			$sattr_id = array();
    			foreach($all_attr as $akey => $aval){
    				
    				foreach($aval as  $aaval){	
    					foreach($sattr as $cval){
    						if($cval === element('cat_id',$aaval)){
    							$sattr_id[$akey][] = $cval;
    						}
    					}	
    	        	}
            	}

            	$_join = '';
            	foreach($sattr_id as $skey => $sval){
            	
            		if(empty($_join))
            			$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).')  ) AS A ';
            		else 
            			$_join .= 'INNER JOIN (select cit_id,cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') ) AS B'.$skey.') AS cb_cmall_attr_rel'.$skey.' ON `A`.`cit_id` = `cb_cmall_attr_rel'.$skey.'`.`cit_id`';
            			
            		// $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
            		
            	}
            	
            	if($_join)
            		$set_join[] = array('(select cit_id,cat_id from ('.$_join.') AS c) AS cb_cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel'.'.cit_id','inner');


            	
            	
            }

	        if($skind && is_array($skind)){

	            // $this->Board_model->set_where_in('cmal1l_kind_rel.ckd_id',$skind);
	            // $this->Board_model->set_where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);
	            $set_join[] = array('(select cit_id,ckd_id from cb_cmall_kind_rel where ckd_id in ('.implode(",",$skind).') ) AS cb_cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');

	            if(empty($sattr))
					$set_join[] = array('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');	
	            $set_join[] = array('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
	        }
	        

	        if(!empty($category_child_id) && is_array($category_child_id)){
	        	
	            // $this->Board_model->set_where_in('cmall_category_rel.cca_id',$category_child_id);
	            $set_join[] = array('(select cit_id,cca_id from cb_cmall_category_rel where cca_id in ('.implode(",",$category_child_id).') ) as cb_cmall_category_rel ','cmall_item.cit_id = cmall_category_rel.cit_id','inner');

	        }

	        
	        


			
			$like = '';
		if($option ==='show_list'){
			$this->Board_model->set_group_by('cmall_item.cit_id');
			if(!empty($set_join)) $this->Board_model->set_join($set_join);
			$result = $this->Board_model
				->get_search_list($per_page, $offset, $where, $like, '', $findex);
			$list_num = $result['total_rows'] - ($page - 1) * $per_page;
			
			if (element('list', $result)) {
				foreach (element('list', $result) as $key => $val) {
					$result['list'][$key] = $this->denguruapi->convert_cit_info($result['list'][$key]);
					$result['list'][$key] = $this->denguruapi->convert_brd_info($result['list'][$key]);

					// $result['list'][$key]['category']=$this->Cmall_category_model->get_category(element('cit_id',$val));
					// $result['list'][$key]['attr']=$this->Cmall_attr_model->get_attr(element('cit_id',$val));

					$result['list'][$key]['num'] = $list_num--;
				}
			}

			$config['base_url'] = site_url('search/show_list?' . $param->replace('page'));
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
			
			// print_r2($result);
			$view['view']['data'] = $result;
			$view['view']['data']['member'] =false;
			if($mem_id)
				$view['view']['data']['member'] = $this->denguruapi->get_mem_info($this->member->item('mem_id'));					

			if ( ! $this->cb_jwt->userdata('skeyword_'. urlencode($skeyword))) {
				$sfieldarray = array('post_title', 'post_content', 'post_both');
				// if (in_array($sfield2, $sfieldarray)) {
				if ($skeyword) {
					if (empty($oth_id)) {
						$searchinsert = array(
							'sek_keyword' => $skeyword,
							'sek_datetime' => cdate('Y-m-d H:i:s'),
							'sek_ip' => $this->input->ip_address(),
							'mem_id' => $mem_id,
							'oth_id' => $oth_id,
						);
						$this->Search_keyword_model->insert($searchinsert);
						$this->cb_jwt->set_userdata(
							'skeyword_'. urlencode($skeyword),
							1
						);
					}
				}
			}
			if ( ! $this->cb_jwt->userdata('skeyword_'.$oth_id.'_'. urlencode($skeyword))) {
				if ($oth_id && $mem_id) {
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
							'skeyword_'.$oth_id.'_'. urlencode($skeyword),
							1
						);
					}
					
					$this->Other_model->update_plus($oth_id, 'oth_hit', 1);
				}
			}
		} else {

			$this->Board_model->set_group_by('cmall_item.cit_id');
			// $this->Board_model->get_today_price_count()
			if(!empty($set_join)) $this->Board_model->set_join($set_join);
			$result = $this->Board_model
				->get_search_count($per_page, $offset, $where, $like, '', $findex);
			$view['view']['data']['total_rows'] = $result;
			// echo $result;	
			// echo "<br>";


			$total_rows =0;
			

			

			if($option === 'price'){


				$group_by='';

				// if($sstart_price){            
		  //               $where['cit_price >= '] = $sstart_price;
		  //       }

		  //       if($send_price){            
		  //               $where['cit_price <='] = $send_price;
		  //       }

				if ($where) {			
					$this->db->where($where);			
				} 
				
				// if($sattr){


		  //           $this->db->where_in('cmall_attr_rel.cat_id',$sattr);
		  //           $this->db->join('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');		
		  //           // $this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
		  //           // $this->db->join('cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel.cit_id','inner');
		  //       }
		        

		        
				
                
    	        
		        

		        

		       

				// $this->db->group_by($group_by);
				$this->db->select('sum(case when cit_price_sale > 0 then (case when cit_price_sale > 0 and cit_price_sale <= 10000 then 1 else 0 end) else (case when cit_price > 0 and cit_price <= 10000 then 1 else 0 end) end) 10000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 10000 and cit_price_sale <= 20000 then 1 else 0 end) else (case when cit_price >= 10000 and cit_price <= 20000 then 1 else 0 end) end) 20000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 20000 and cit_price_sale <= 30000 then 1 else 0 end) else (case when cit_price >= 20000 and cit_price <= 30000 then 1 else 0 end) end) 30000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 30000 and cit_price_sale <= 40000 then 1 else 0 end) else (case when cit_price >= 30000 and cit_price <= 40000 then 1 else 0 end) end) 40000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 40000 and cit_price_sale <= 50000 then 1 else 0 end) else (case when cit_price >= 40000 and cit_price <= 50000 then 1 else 0 end) end) 50000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 50000 and cit_price_sale <= 60000 then 1 else 0 end) else (case when cit_price >= 50000 and cit_price <= 60000 then 1 else 0 end) end) 60000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 60000 and cit_price_sale <= 70000 then 1 else 0 end) else (case when cit_price >= 60000 and cit_price <= 70000 then 1 else 0 end) end) 70000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 70000 and cit_price_sale <= 80000 then 1 else 0 end) else (case when cit_price >= 70000 and cit_price <= 80000 then 1 else 0 end) end) 80000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 80000 and cit_price_sale <= 90000 then 1 else 0 end) else (case when cit_price >= 80000 and cit_price <= 90000 then 1 else 0 end) end) 90000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 90000 and cit_price_sale <= 100000 then 1 else 0 end) else (case when cit_price >= 90000 and cit_price <= 100000 then 1 else 0 end) end) 100000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 100000 and cit_price_sale <= 110000 then 1 else 0 end) else (case when cit_price >= 100000 and cit_price <= 110000 then 1 else 0 end) end) 110000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 110000 and cit_price_sale <= 120000 then 1 else 0 end) else (case when cit_price >= 110000 and cit_price <= 120000 then 1 else 0 end) end) 120000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 120000 and cit_price_sale <= 130000 then 1 else 0 end) else (case when cit_price >= 120000 and cit_price <= 130000 then 1 else 0 end) end) 130000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 130000 and cit_price_sale <= 140000 then 1 else 0 end) else (case when cit_price >= 130000 and cit_price <= 140000 then 1 else 0 end) end) 140000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 140000 and cit_price_sale <= 150000 then 1 else 0 end) else (case when cit_price >= 140000 and cit_price <= 150000 then 1 else 0 end) end) 150000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 150000 and cit_price_sale <= 160000 then 1 else 0 end) else (case when cit_price >= 150000 and cit_price <= 160000 then 1 else 0 end) end) 160000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 160000 and cit_price_sale <= 170000 then 1 else 0 end) else (case when cit_price >= 160000 and cit_price <= 170000 then 1 else 0 end) end) 170000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 170000 and cit_price_sale <= 180000 then 1 else 0 end) else (case when cit_price >= 170000 and cit_price <= 180000 then 1 else 0 end) end) 180000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 180000 and cit_price_sale <= 190000 then 1 else 0 end) else (case when cit_price >= 180000 and cit_price <= 190000 then 1 else 0 end) end) 190000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 190000 and cit_price_sale <= 200000 then 1 else 0 end) else (case when cit_price >= 190000 and cit_price <= 200000 then 1 else 0 end) end) 200000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 200000 and cit_price_sale <= 210000 then 1 else 0 end) else (case when cit_price >= 200000 and cit_price <= 210000 then 1 else 0 end) end) 210000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 210000 and cit_price_sale <= 220000 then 1 else 0 end) else (case when cit_price >= 210000 and cit_price <= 220000 then 1 else 0 end) end) 220000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 220000 and cit_price_sale <= 230000 then 1 else 0 end) else (case when cit_price >= 220000 and cit_price <= 230000 then 1 else 0 end) end) 230000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 230000 and cit_price_sale <= 240000 then 1 else 0 end) else (case when cit_price >= 230000 and cit_price <= 240000 then 1 else 0 end) end) 240000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 240000 and cit_price_sale <= 250000 then 1 else 0 end) else (case when cit_price >= 240000 and cit_price <= 250000 then 1 else 0 end) end) 250000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 250000 and cit_price_sale <= 260000 then 1 else 0 end) else (case when cit_price >= 250000 and cit_price <= 260000 then 1 else 0 end) end) 260000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 260000 and cit_price_sale <= 270000 then 1 else 0 end) else (case when cit_price >= 260000 and cit_price <= 270000 then 1 else 0 end) end) 270000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 270000 and cit_price_sale <= 280000 then 1 else 0 end) else (case when cit_price >= 270000 and cit_price <= 280000 then 1 else 0 end) end) 280000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 280000 and cit_price_sale <= 290000 then 1 else 0 end) else (case when cit_price >= 280000 and cit_price <= 290000 then 1 else 0 end) end) 290000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 290000 and cit_price_sale <= 300000 then 1 else 0 end) else (case when cit_price >= 290000 and cit_price <= 300000 then 1 else 0 end) end) 300000under,sum(case when cit_price_sale > 0 then (case when cit_price_sale >= 300000 then 1 else 0 end) else (case when cit_price >= 300000 then 1 else 0 end) end) 300000over');
				// $this->db->where(array('cmall_item.brd_id' =>2));				
				// $this->db->where(array('cmall_category_rel.cca_id' =>7));
				// $this->db->where(' (`cb_cmall_attr`.`cat_parent` = 9
				// )','',false);
				

					
						// $this->db->where($set_where, '',false);			
				
				$this->db->from('board');
				$this->db->join("
					(select cit_id,brd_id,cit_price,cit_price_sale from cb_cmall_item where
				cit_status = 1 AND cit_is_del = 0 AND cit_is_soldout = 0) as cb_cmall_item",'cmall_item.brd_id = board.brd_id','inner');
				// $this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');

				if($skeyword){
		        	
		        	// $this->Board_model->set_where("(cit_name like '%".$skeyword."%' OR cta_tag = '".$skeyword."' OR cca_value = '".$skeyword."' OR cat_value = '".$skeyword."' OR cbr_value_kr = '".$skeyword."' )",'',false);

		        	// $this->Board_model->set_where("(  cbr_value_kr = '".$skeyword."' )",'',false);
		            
		            $this->db->join("
						(select cit_id from cb_cmall_item where cit_name like '%".$skeyword."%'
						UNION
						select cit_id from cb_crawl_tag where cta_tag = '".$skeyword."' 
						UNION
						select cit_id from cb_cmall_attr_rel INNER JOIN cb_cmall_attr ON cb_cmall_attr_rel.cat_id = cb_cmall_attr.cat_id  where cat_value = '".$skeyword."'
						UNION
						select cit_id from cb_board INNER JOIN cb_cmall_item ON cb_board.brd_id = cb_cmall_item.brd_id  where brd_name like '%".$skeyword."%'
						UNION
						select cit_id from cb_cmall_brand INNER JOIN cb_cmall_item ON cb_cmall_brand.cbr_id = cb_cmall_item.cbr_id  where cbr_value_kr like '%".$skeyword."%' or cbr_value_en like '%".$skeyword."%'
						) as AAA",'cmall_item.cit_id = AAA.cit_id','inner');
				           

				            // $this->Board_model->set_join(array('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner'));
				            // $this->Board_model->set_join(array("(select cca_id,cca_value from cb_cmall_category where cca_value like '%".$skeyword."%') as cb_cmall_category",'cmall_category_rel.cca_id = cmall_category.cca_id','outter'));

				            // $this->Board_model->set_join(array("(select cit_id,cta_id,cta_tag from cb_crawl_tag where cta_tag like '%".$skeyword."%') as cb_crawl_tag",'cmall_item.cit_id = crawl_tag.cit_id','inner'));

				            // $this->Board_model->set_join(array('cmall_attr_rel','cmall_attr_rel.cit_id = cmall_item.cit_id','inner'));
				            // $this->Board_model->set_join(array("(select cat_id,cat_value from cb_cmall_attr where cat_value like '%".$skeyword."%') as cb_cmall_attr",'cmall_attr.cat_id = cmall_attr_rel.cat_id','outter'));

				            // $this->Board_model->set_join(array('cmall_brand','cmall_brand.cbr_id = cmall_item.cbr_id','inner'));

				        }
				if(!empty($category_child_id) && is_array($category_child_id)){
					

		            // $this->db->where_in('cmall_category_rel.cca_id',$category_child_id);
		            $this->db->join('(select cit_id,cca_id from cb_cmall_category_rel where cca_id in ('.implode(",",$category_child_id).') group by cit_id) as cb_cmall_category_rel ','cmall_item.cit_id = cmall_category_rel.cit_id','inner');

		            
		        }

		        if($sattr && is_array($sattr)){
        			$_join = '';	
        			$sattr_id = array();
        			foreach($all_attr as $akey => $aval){
        				
        				foreach($aval as  $aaval){	
        					foreach($sattr as $cval){
        						if($cval === element('cat_id',$aaval)){
        							$sattr_id[$akey][] = $cval;
        						}
        					}	
        	        	}
                	}



                	
                	foreach($sattr_id as $skey => $sval){
                	
                		if(empty($_join))
                			$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') ) AS A ';
                		else 
                			$_join .= 'INNER JOIN (select cit_id,cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') ) AS B'.$skey.') AS cb_cmall_attr_rel'.$skey.' ON `A`.`cit_id` = `cb_cmall_attr_rel'.$skey.'`.`cit_id`';
                			
                		// $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                		
                	}
  	

                	if($_join)
                		$this->db->join('(select cit_id,cat_id from ('.$_join.') AS c group by cit_id) AS cb_cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel'.'.cit_id','inner');	
                }
		        if($skind && is_array($skind)){

	        		// $this->db->where_in('cmall_kind_rel.ckd_id',$skind);
	        		// $this->db->where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);
	        	 //    $this->db->join('cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');
	        	    $this->db->join('(select cit_id,ckd_id from cb_cmall_kind_rel where ckd_id in ('.implode(",",$skind).') group by cit_id) AS cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');

	        	    if(empty($_join))
				        $this->db->join('(select cit_id,cat_id from cb_cmall_attr_rel group by cit_id) as cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');	
	        		
					$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
		            
		        }
				
				// $this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
				

				
				$qry = $this->db->get();
				$result = $qry->result_array();
				
				foreach($result as $val){
					foreach($val as $key_ => $val_){
		        		$cmall_price[] = array(		        			
		        			'pri_value' => $key_,
		        			'rownum' => $val_,
		        			);


		        	}
				}
				
			}

			if($is_color && $option ==='color'){
				$color_code = array(
								'블랙'=>'#000000',
								'화이트'=>'#ffffff',
								'베이지'=>'#F5F5DC',
								'그레이'=>'#2F4F4F',
								'레드'=>'#FF0000',
								'핑크'=>'#FFC0CB',
								'오렌지'=>'#FFA500',
								'옐로우'=>'#FFFF00',
								'민트'=>'#F5FFFA',
								'그린'=>'#008000',
								'카키'=>'#F0E68C',
								'블루'=>'#0000FF',
								'네이비'=>'#000080',
								'퍼플'=>'#800080',
								'버건디'=>'#760c0c',
								'브라운'=>'#A52A2A',
								'데님'=>'#79BAEC',
							);

				$color_url = array(
								'스트라이프'  => 'color-stripe.png',
								'도트' => 'color-dotted.png',
								'체크/헤링본' => 'color-check.png',
								'플라워' => 'color-flower.png',
								'기타패턴' => 'color-etc.png',
							);
				$check_code = array(
								'블랙'=>'icon-check-white.svg',
								'화이트'=>'icon-check-pink.svg',
								'베이지'=>'icon-check-pink.svg',
								'그레이'=>'icon-check-pink.svg',
								'레드'=>'icon-check-white.svg',
								'핑크'=>'icon-check-white.svg',
								'오렌지'=>'icon-check-white.svg',
								'옐로우'=>'icon-check-white.svg',
								'민트'=>'icon-check-white.svg',
								'그린'=>'icon-check-white.svg',
								'카키'=>'icon-check-white.svg',
								'블루'=>'icon-check-white.svg',
								'네이비'=>'icon-check-white.svg',
								'퍼플'=>'icon-check-white.svg',
								'버건디'=>'icon-check-white.svg',
								'브라운'=>'icon-check-white.svg',
								'데님'=>'icon-check-white.svg',
								'스트라이프'  => 'icon-check-pink.svg',
								'도트' => 'icon-check-pink.svg',
								'체크/헤링본' => 'icon-check-pink.svg',
								'플라워' => 'icon-check-pink.svg',
								'기타패턴' => 'icon-check-pink.svg',
							);

				
				$group_by='cmall_attr.cat_id';

				// if($sstart_price){            
		  //               $where['cit_price >= '] = $sstart_price;
		  //       }

		  //       if($send_price){            
		  //               $where['cit_price <='] = $send_price;
		  //       }

				if ($where) {			
					$this->db->where($where);			
				} 
				


				
		        

		        

		       

				$this->db->group_by($group_by);
				$this->db->select($group_by.',cat_value,count( cb_cmall_item.cit_id) as rownum');
				// $this->db->where(array('cmall_item.brd_id' =>2));				
				// $this->db->where(array('cmall_category_rel.cca_id' =>7));
				// $this->db->where(' (`cb_cmall_attr`.`cat_parent` = 9
				// )','',false);
				

					
						// $this->db->where($set_where, '',false);			
				
				$this->db->from('board');
				$this->db->join("
					(select cit_id,brd_id from cb_cmall_item ".$cmallwhere.") as cb_cmall_item",'cmall_item.brd_id = board.brd_id','inner');
				// $this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');

				if($skeyword){
		        	
		        	// $this->Board_model->set_where("(cit_name like '%".$skeyword."%' OR cta_tag = '".$skeyword."' OR cca_value = '".$skeyword."' OR cat_value = '".$skeyword."' OR cbr_value_kr = '".$skeyword."' )",'',false);

		        	// $this->Board_model->set_where("(  cbr_value_kr = '".$skeyword."' )",'',false);
		            
		            $this->db->join("
						(select cit_id from cb_cmall_item where cit_name like '%".$skeyword."%'
						UNION
						select cit_id from cb_crawl_tag where cta_tag = '".$skeyword."' 
						UNION
						select cit_id from cb_cmall_attr_rel INNER JOIN cb_cmall_attr ON cb_cmall_attr_rel.cat_id = cb_cmall_attr.cat_id  where cat_value = '".$skeyword."'
						UNION
						select cit_id from cb_board INNER JOIN cb_cmall_item ON cb_board.brd_id = cb_cmall_item.brd_id  where brd_name like '%".$skeyword."%'
						UNION
						select cit_id from cb_cmall_brand INNER JOIN cb_cmall_item ON cb_cmall_brand.cbr_id = cb_cmall_item.cbr_id  where cbr_value_kr like '%".$skeyword."%' or cbr_value_en like '%".$skeyword."%'
						) as AAA",'cmall_item.cit_id = AAA.cit_id','inner');
				           

				            // $this->Board_model->set_join(array('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner'));
				            // $this->Board_model->set_join(array("(select cca_id,cca_value from cb_cmall_category where cca_value like '%".$skeyword."%') as cb_cmall_category",'cmall_category_rel.cca_id = cmall_category.cca_id','outter'));

				            // $this->Board_model->set_join(array("(select cit_id,cta_id,cta_tag from cb_crawl_tag where cta_tag like '%".$skeyword."%') as cb_crawl_tag",'cmall_item.cit_id = crawl_tag.cit_id','inner'));

				            // $this->Board_model->set_join(array('cmall_attr_rel','cmall_attr_rel.cit_id = cmall_item.cit_id','inner'));
				            // $this->Board_model->set_join(array("(select cat_id,cat_value from cb_cmall_attr where cat_value like '%".$skeyword."%') as cb_cmall_attr",'cmall_attr.cat_id = cmall_attr_rel.cat_id','outter'));

				            // $this->Board_model->set_join(array('cmall_brand','cmall_brand.cbr_id = cmall_item.cbr_id','inner'));

				        }
				if(!empty($category_child_id) && is_array($category_child_id)){
					

		            // $this->db->where_in('cmall_category_rel.cca_id',$category_child_id);
		            $this->db->join('(select cit_id,cca_id from cb_cmall_category_rel where cca_id in ('.implode(",",$category_child_id).') group by cit_id) as cb_cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner');

		        }

		        $_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in (17,18,19,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42) ) AS A ';
				if($sattr && is_array($sattr)){
        			$_join = '';
        			$sattr_id = array();
        			foreach($all_attr as $akey => $aval){
        				
        				foreach($aval as  $aaval){	
        					foreach($sattr as $cval){
        						if($cval === element('cat_id',$aaval)){
        							$sattr_id[$akey][] = $cval;
        						}
        					}	
        	        	}
                	}

                	 if(empty(element(9,$sattr_id)))
                        $_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in (17,18,19,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42) ) AS A ';
                    else
                        $_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",element(9,$sattr_id)).') ) AS A ';

                	
                	foreach($sattr_id as $skey => $sval){
                	
                		// if(empty($_join))
                		// 	$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).')) AS A ';
                		// else 
                		if($skey != '9')
                			$_join .= 'INNER JOIN (select cit_id,cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') group by cit_id) AS B'.$skey.') AS cb_cmall_attr_rel'.$skey.' ON `A`.`cit_id` = `cb_cmall_attr_rel'.$skey.'`.`cit_id`';
                			
                		// $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                		
                	}
  	

                	
                	
                }
                if($_join)
		        	$this->db->join('(select cit_id,cat_id from ('.$_join.') AS c) AS cb_cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel'.'.cit_id','inner');


		        if($skind && is_array($skind)){

	        		// $this->db->where_in('cmall_kind_rel.ckd_id',$skind);
	        		// $this->db->where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);
	        	 //    $this->db->join('cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');

	        	    $this->db->join('(select cit_id,ckd_id from cb_cmall_kind_rel where ckd_id in ('.implode(",",$skind).') group by cit_id) AS cb_cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');
		        	
		            if(empty($_join))
				        $this->db->join('(select cit_id,cat_id from cb_cmall_attr_rel ) as cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');	
		        }
				// $this->db->join('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');		
				$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
				// $this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
				

				
				$qry = $this->db->get();
				$result = $qry->result_array();
				// $cmall_age = $result;
				// print_r2($result);
				
				foreach($result as $key => $val){
		        		$cmall_color[] = array(
		        			'cat_id' => element('cat_id',$val),
		        			'cat_value' => element('cat_value',$val),
		        			'color_code' => element(element('cat_value',$val),$color_code),
		        			// 'url' => site_url(config_item('uploads_dir') . '/'.element(element('cat_value',$val),$color_url)),
		        			'color_url' => element(element('cat_value',$val),$color_url) ? thumb_url('etc', element(element('cat_value',$val),$color_url),50 )  : null,
		        			'check_code' => element(element('cat_value',$val),$check_code) ? element(element('cat_value',$val),$check_code)  : null,
		        			'rownum' => element('rownum',$val),		        			
		        			);


		        		
				}
				
				// print_r2($cmall_color);exit;
			}

 			if($option ==='size'){

				if (false) {
					$use_cache = true;
				} else {
					$cachename = 'cmall_kind_info_row' . cdate('Y-m-d') ;
					// if ( ! $result = $this->cache->get($cachename)) {

						$group_by='cmall_attr.cat_id';
						// if($sstart_price){            
			   //              $where['cit_price >= '] = $sstart_price;
				  //       }

				  //       if($send_price){            
				  //               $where['cit_price <='] = $send_price;
				  //       }

						if ($where) {			
							$this->db->where($where);			
						} 
						
						// if($sattr){


				  //           $this->db->where_in('cmall_attr_rel.cat_id',$sattr);
				  //           // $this->db->join('cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel.cit_id','inner');
				  //       }
				        

				        

				        

				       

						// $this->db->where(' (`cb_cmall_attr`.`cat_id` = 4
						// or `cb_cmall_attr`.`cat_id` = 5 
						// or `cb_cmall_attr`.`cat_id` = 6 
						// )','',false);

						

						$this->db->group_by($group_by);
						$this->db->select($group_by.',cat_value,count( cb_cmall_item.cit_id) as rownum');
						$this->db->from('board');
						$this->db->join("
							(select cit_id,brd_id from cb_cmall_item ".$cmallwhere.") as cb_cmall_item",'cmall_item.brd_id = board.brd_id','inner');
						// $this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');

						if($skeyword){
				        	
				        	// $this->Board_model->set_where("(cit_name like '%".$skeyword."%' OR cta_tag = '".$skeyword."' OR cca_value = '".$skeyword."' OR cat_value = '".$skeyword."' OR cbr_value_kr = '".$skeyword."' )",'',false);

				        	// $this->Board_model->set_where("(  cbr_value_kr = '".$skeyword."' )",'',false);
				            
				            $this->db->join("
								(select cit_id from cb_cmall_item where cit_name like '%".$skeyword."%'
								UNION
								select cit_id from cb_crawl_tag where cta_tag = '".$skeyword."' 
								UNION
								select cit_id from cb_cmall_attr_rel INNER JOIN cb_cmall_attr ON cb_cmall_attr_rel.cat_id = cb_cmall_attr.cat_id  where cat_value = '".$skeyword."'
								UNION
								select cit_id from cb_board INNER JOIN cb_cmall_item ON cb_board.brd_id = cb_cmall_item.brd_id  where brd_name like '%".$skeyword."%'
								UNION
								select cit_id from cb_cmall_brand INNER JOIN cb_cmall_item ON cb_cmall_brand.cbr_id = cb_cmall_item.cbr_id  where cbr_value_kr like '%".$skeyword."%' or cbr_value_en like '%".$skeyword."%'
								) as AAA",'cmall_item.cit_id = AAA.cit_id','inner');
						           

						            // $this->Board_model->set_join(array('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner'));
						            // $this->Board_model->set_join(array("(select cca_id,cca_value from cb_cmall_category where cca_value like '%".$skeyword."%') as cb_cmall_category",'cmall_category_rel.cca_id = cmall_category.cca_id','outter'));

						            // $this->Board_model->set_join(array("(select cit_id,cta_id,cta_tag from cb_crawl_tag where cta_tag like '%".$skeyword."%') as cb_crawl_tag",'cmall_item.cit_id = crawl_tag.cit_id','inner'));

						            // $this->Board_model->set_join(array('cmall_attr_rel','cmall_attr_rel.cit_id = cmall_item.cit_id','inner'));
						            // $this->Board_model->set_join(array("(select cat_id,cat_value from cb_cmall_attr where cat_value like '%".$skeyword."%') as cb_cmall_attr",'cmall_attr.cat_id = cmall_attr_rel.cat_id','outter'));

						            // $this->Board_model->set_join(array('cmall_brand','cmall_brand.cbr_id = cmall_item.cbr_id','inner'));

						}
						if(!empty($category_child_id) && is_array($category_child_id)){
						    

						    $this->db->join('(select cit_id,cca_id from cb_cmall_category_rel where cca_id in ('.implode(",",$category_child_id).') group by cit_id) as cb_cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner');

						}
						$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in (4,5,6) ) AS A ';
						if($sattr && is_array($sattr)){
	            			$_join = '';
	            			$sattr_id = array();
	            			foreach($all_attr as $akey => $aval){
	            				
	            				foreach($aval as  $aaval){	
	            					foreach($sattr as $cval){
	            						if($cval === element('cat_id',$aaval)){
	            							$sattr_id[$akey][] = $cval;
	            						}
	            					}	
	            	        	}
	                    	}

	                    	if(empty(element(1,$sattr_id)))
	                    	    $_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in (4,5,6) ) AS A ';
	                    	else
	                    	    $_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",element(1,$sattr_id)).') ) AS A ';

	                    	
	                    	foreach($sattr_id as $skey => $sval){
	                    	
	                    		// if(empty($_join))
	                    		// 	$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).')) AS A ';
	                    		// else 
	                    		if($skey != '1')
	                    			$_join .= 'INNER JOIN (select cit_id,cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') group by cit_id) AS B'.$skey.') AS cb_cmall_attr_rel'.$skey.' ON `A`.`cit_id` = `cb_cmall_attr_rel'.$skey.'`.`cit_id`';
	                    			
	                    		// $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
	                    		
	                    	}
	      	

	                    	
	                    	
	                    }
	                    if($_join)
	                    	$this->db->join('(select cit_id,cat_id from ('.$_join.') AS c) AS cb_cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel'.'.cit_id','inner');	
						if($skind && is_array($skind)){

							// $this->db->where_in('cmall_kind_rel.ckd_id',$skind);
							// $this->db->where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);
						 //    $this->db->join('cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');
						    $this->db->join('(select cit_id,ckd_id from cb_cmall_kind_rel where ckd_id in ('.implode(",",$skind).') group by cit_id) AS cb_cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');
						    
						    if(empty($_join))
				        		$this->db->join('(select cit_id,cat_id from cb_cmall_attr_rel ) as cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');	
						}
						// $this->db->join('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');		
						$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');	
						$qry = $this->db->get();
						$result = $qry->result_array();
						$this->cache->save($cachename, $result, '86400');
					// }
				}

				

				foreach($result as $key => $val){
		        		$cmall_size[element('cat_id',$val)] = array(
		        			'cat_id' => element('cat_id',$val),
		        			'cat_value' => element('cat_value',$val),
		        			'rownum' => element('rownum',$val),
		        			);
				}

				if(!array_key_exists(4,$cmall_size)){
					$cmall_size[4] = array(
						'cat_id' => 4,
						'cat_value' => '소형견',
						'rownum' => 0,
					);
				}
				if(!array_key_exists(5,$cmall_size)){
					$cmall_size[5] = array(
						'cat_id' => 5,
						'cat_value' => '중형견',
						'rownum' => 0,
					);
				}
				if(!array_key_exists(6,$cmall_size)){
					$cmall_size[6] = array(
						'cat_id' => 6,
						'cat_value' => '대형견',
						'rownum' => 0,
					);
				}
				
				
				// $this->db->where(array('cmall_item.brd_id' =>2));				
				// $this->db->where(array('cmall_category_rel.cca_id' =>7));
				
				
				
				// $this->db->where(array('cmall_item.brd_id' => 2));

					
						// $this->db->where($set_where, '',false);			
				
				
				// $this->db->join('crawl_tag', 'crawl_tag.cit_id = cmall_item.cit_id', 'inner');
				// $this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
				$is_kind=false;

				if($sattr && is_array($sattr)){


					foreach($sattr as $sval){

						if($sval == '4' || $sval == '5' || $sval == '6' ) $is_kind=true;
					}

					
				}
				if($is_kind){
					$this->db->group_start();
					foreach($sattr as $sval){
						if($sval == '4' || $sval == '5' || $sval == '6' ) $this->db->or_where('ckd_size',$sval);
					}
					$this->db->group_end();
				}
				if($is_kind){
					if (false) {
						$use_cache = true;
					} else {
						$cachename = 'cmall_kind_list_row' . cdate('Y-m-d') ;
						// if ( ! $result = $this->cache->get($cachename)) {

							$group_by='cmall_kind.ckd_id';
							// if($sstart_price){            
					  //          $where['cit_price >= '] = $sstart_price;
					  //       }

					  //       if($send_price){            
					  //          $where['cit_price <='] = $send_price;
					  //       }

							if ($where) {			
								$this->db->where($where);			
							} 
							
							
					        

					        

					        

					        

							// $this->db->where(' (`cb_cmall_kind`.`ckd_size` = 4
							// or `cb_cmall_kind`.`ckd_size` = 5 
							// or `cb_cmall_kind`.`ckd_size` = 6 
							// )','',false);

							// $this->db->where(array('ckd_parent' => 0));

							$this->db->group_by($group_by);
							$this->db->select($group_by.',ckd_size,ckd_value_kr,count( cb_cmall_item.cit_id) as rownum');
							$this->db->from('board');
							$this->db->join("
								(select cit_id,brd_id from cb_cmall_item ".$cmallwhere.") as cb_cmall_item",'cmall_item.brd_id = board.brd_id','inner');
							// $this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');

							if($skeyword){
					        	
					        	// $this->Board_model->set_where("(cit_name like '%".$skeyword."%' OR cta_tag = '".$skeyword."' OR cca_value = '".$skeyword."' OR cat_value = '".$skeyword."' OR cbr_value_kr = '".$skeyword."' )",'',false);

					        	// $this->Board_model->set_where("(  cbr_value_kr = '".$skeyword."' )",'',false);
					            
					            $this->db->join("
									(select cit_id from cb_cmall_item where cit_name like '%".$skeyword."%'
									UNION
									select cit_id from cb_crawl_tag where cta_tag = '".$skeyword."' 
									UNION
									select cit_id from cb_cmall_attr_rel INNER JOIN cb_cmall_attr ON cb_cmall_attr_rel.cat_id = cb_cmall_attr.cat_id  where cat_value = '".$skeyword."'
									UNION
									select cit_id from cb_board INNER JOIN cb_cmall_item ON cb_board.brd_id = cb_cmall_item.brd_id  where brd_name like '%".$skeyword."%'
									UNION
									select cit_id from cb_cmall_brand INNER JOIN cb_cmall_item ON cb_cmall_brand.cbr_id = cb_cmall_item.cbr_id  where cbr_value_kr like '%".$skeyword."%' or cbr_value_en like '%".$skeyword."%'
									) as AAA",'cmall_item.cit_id = AAA.cit_id','inner');
							           

							            // $this->Board_model->set_join(array('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner'));
							            // $this->Board_model->set_join(array("(select cca_id,cca_value from cb_cmall_category where cca_value like '%".$skeyword."%') as cb_cmall_category",'cmall_category_rel.cca_id = cmall_category.cca_id','outter'));

							            // $this->Board_model->set_join(array("(select cit_id,cta_id,cta_tag from cb_crawl_tag where cta_tag like '%".$skeyword."%') as cb_crawl_tag",'cmall_item.cit_id = crawl_tag.cit_id','inner'));

							            // $this->Board_model->set_join(array('cmall_attr_rel','cmall_attr_rel.cit_id = cmall_item.cit_id','inner'));
							            // $this->Board_model->set_join(array("(select cat_id,cat_value from cb_cmall_attr where cat_value like '%".$skeyword."%') as cb_cmall_attr",'cmall_attr.cat_id = cmall_attr_rel.cat_id','outter'));

							            // $this->Board_model->set_join(array('cmall_brand','cmall_brand.cbr_id = cmall_item.cbr_id','inner'));

							        }
							if(!empty($category_child_id) && is_array($category_child_id)){
								

							    // $this->db->where_in('cmall_category_rel.cca_id',$category_child_id);
							    // $this->db->join('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner');

							    $this->db->join('(select cit_id,cca_id from cb_cmall_category_rel where cca_id in ('.implode(",",$category_child_id).') group by cit_id) as cb_cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner');
							}

							// if($sattr){


					  //           $this->db->where_in('cmall_attr_rel.cat_id',$sattr);
					  //           $this->db->join('cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel.cit_id','inner');
					  //       }

					        
							if($sattr && is_array($sattr)){
		            		    $_join = '';
		            			$sattr_id = array();
		            			foreach($all_attr as $akey => $aval){
		            				
		            				foreach($aval as  $aaval){	
		            					foreach($sattr as $cval){
		            						if($cval === element('cat_id',$aaval)){
		            							$sattr_id[$akey][] = $cval;
		            						}
		            					}	
		            	        	}
		                    	}



		                    	
		                    	foreach($sattr_id as $skey => $sval){
		                    	
		                    		if(empty($_join))
		                    			$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') ) AS A ';
		                    		else 
		                    			$_join .= 'INNER JOIN (select cit_id,cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') ) AS B'.$skey.') AS cb_cmall_attr_rel'.$skey.' ON `A`.`cit_id` = `cb_cmall_attr_rel'.$skey.'`.`cit_id`';
		                    			
		                    		// $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
		                    		
		                    	}
		      	

		                    	if($_join)
		                    	$this->db->join('(select cit_id,cat_id from ('.$_join.') AS c group by cit_id) AS cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel'.'.cit_id','inner');	
		                    	
		                    }
		                    
					        if($skind && is_array($skind)){

				        		// $this->db->where_in('cmall_kind_rel.ckd_id',$skind);
				        		$this->db->where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);

				        		if(empty($_join))
				        			$this->db->join('(select cit_id,cat_id from cb_cmall_attr_rel group by cit_id) as cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');	
				        		
					        	$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
					            
					        	$this->db->join('(select cit_id,ckd_id from cb_cmall_kind_rel where ckd_id in ('.implode(",",$skind).') ) as cb_cmall_kind_rel', 'cmall_kind_rel.cit_id = cmall_item.cit_id', 'inner');		    
					        } else{
					        	$this->db->join('cmall_kind_rel', 'cmall_kind_rel.cit_id = cmall_item.cit_id', 'inner');	
					        }

							
							$this->db->join('cmall_kind', 'cmall_kind.ckd_id = cmall_kind_rel.ckd_id', 'inner');	
							$qry = $this->db->get();
							$result = $qry->result_array();
							$this->cache->save($cachename, $result, '86400');
						// }
					}
					

					$all_kind_size = array();
					

					foreach(element(0,$all_kind) as $val){
					
						$flag = true;
						foreach($result as $sval){
							if(element('ckd_id',$val) === element('ckd_id',$sval)){


								$flag=false;

								$all_kind_size[] = array(
			        			'ckd_id' => element('ckd_id',$sval),
			        			'ckd_value_kr' => element('ckd_value_kr',$sval),
			        			'ckd_size' => element('ckd_size',$sval),
			        			'rownum' => element('rownum',$sval),
			        			);

								break;
							}
						}

						if($flag){
							$all_kind_size[] = array(
			        			'ckd_id' => element('ckd_id',$val),
			        			'ckd_value_kr' => element('ckd_value_kr',$val),
			        			'ckd_size' => element('ckd_size',$val),
			        			'rownum' => 0,
			        			);
						}
						
					}

					

					// foreach($result as $val){
					// 	foreach($all_kind_size as $skey => $sval){
					// 		if( element($skey,element('kind_list',element($key,$cmall_size))) )
					// 			$cmall_kind[$i]['kind_list'][$skey] = element($skey,element('kind_list',element($key,$cmall_size)));
					// 		 else 
					// 		 	$cmall_kind[$i]['kind_list'][$skey] = $sval;
					// 	}
					// }
					
					foreach($all_kind_size as $val){
						foreach($cmall_size as $a_cvalue){

			                if(element('ckd_size',$val) == element('cat_id',$a_cvalue)){

			                	$cmall_size[element('ckd_size',$val)]['kind_list'][element('ckd_id',$val)] = array(
			        			'ckd_id' => element('ckd_id',$val),
			        			'ckd_value_kr' => element('ckd_value_kr',$val),
			        			'rownum' => element('rownum',$val),
			        			);
			                }
				            
				        }
					}
				}
				// print_r2($cmall_size);
				// exit;
				
			}
			
			// foreach($cmall_size as $a_cvalue){
			// 	foreach($a_cvalue as $a_cvalue_){
		 //            if(element('ckd_size',$val) == element('cat_id',$a_cvalue)){

		 //            	$cmall_size[element('ckd_size',$val)]['kind_list'][] = array(
		 //    			'ckd_id' => element('ckd_id',$val),
		 //    			'ckd_value_kr' => element('ckd_value_kr',$val),
		 //    			'rownum' => element('rownum',$val),
		 //    			);
		 //            }
		            
		 //        }
		 //    }
			
			
	 		if($option ==='age'){
				$group_by='cmall_attr.cat_id';

				// if($sstart_price){            
		  //               $where['cit_price >= '] = $sstart_price;
		  //       }

		  //       if($send_price){            
		  //               $where['cit_price <='] = $send_price;
		  //       }

				if ($where) {			
					$this->db->where($where);			
				} 
				

				


				// if($sattr){


		  //           $this->db->where_in('cmall_attr_rel.cat_id',$sattr);
		  //           // $this->db->join('cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel.cit_id','inner');
		  //       }
		        

		        

		        

		        
				
				$this->db->group_by($group_by);
				$this->db->select($group_by.',cat_value,count( cb_cmall_item.cit_id) as rownum');
				// $this->db->where(array('cmall_item.brd_id' =>2));				
				// $this->db->where(array('cmall_category_rel.cca_id' =>7));
				// $this->db->where(' (`cb_cmall_attr_rel`.`cat_id` = 12
				// or `cb_cmall_attr_rel`.`cat_id` = 13 
				// or `cb_cmall_attr_rel`.`cat_id` = 14 
				// )','',false);
				

					
						// $this->db->where($set_where, '',false);			
				
				$this->db->from('board');



	        	$this->db->join("
					(select cit_id,brd_id from cb_cmall_item ".$cmallwhere.") as cb_cmall_item",'cmall_item.brd_id = board.brd_id','inner');
				// $this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');

				if($skeyword){
		        	
		        	// $this->Board_model->set_where("(cit_name like '%".$skeyword."%' OR cta_tag = '".$skeyword."' OR cca_value = '".$skeyword."' OR cat_value = '".$skeyword."' OR cbr_value_kr = '".$skeyword."' )",'',false);

		        	// $this->Board_model->set_where("(  cbr_value_kr = '".$skeyword."' )",'',false);
		            
		            $this->db->join("
						(select cit_id from cb_cmall_item where cit_name like '%".$skeyword."%'
						UNION
						select cit_id from cb_crawl_tag where cta_tag = '".$skeyword."' 
						UNION
						select cit_id from cb_cmall_attr_rel INNER JOIN cb_cmall_attr ON cb_cmall_attr_rel.cat_id = cb_cmall_attr.cat_id  where cat_value = '".$skeyword."'
						UNION
						select cit_id from cb_board INNER JOIN cb_cmall_item ON cb_board.brd_id = cb_cmall_item.brd_id  where brd_name like '%".$skeyword."%'
						UNION
						select cit_id from cb_cmall_brand INNER JOIN cb_cmall_item ON cb_cmall_brand.cbr_id = cb_cmall_item.cbr_id  where cbr_value_kr like '%".$skeyword."%' or cbr_value_en like '%".$skeyword."%'
						) as AAA",'cmall_item.cit_id = AAA.cit_id','inner');
		           

		            // $this->Board_model->set_join(array('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner'));
		            // $this->Board_model->set_join(array("(select cca_id,cca_value from cb_cmall_category where cca_value like '%".$skeyword."%') as cb_cmall_category",'cmall_category_rel.cca_id = cmall_category.cca_id','outter'));

		            // $this->Board_model->set_join(array("(select cit_id,cta_id,cta_tag from cb_crawl_tag where cta_tag like '%".$skeyword."%') as cb_crawl_tag",'cmall_item.cit_id = crawl_tag.cit_id','inner'));

		            // $this->Board_model->set_join(array('cmall_attr_rel','cmall_attr_rel.cit_id = cmall_item.cit_id','inner'));
		            // $this->Board_model->set_join(array("(select cat_id,cat_value from cb_cmall_attr where cat_value like '%".$skeyword."%') as cb_cmall_attr",'cmall_attr.cat_id = cmall_attr_rel.cat_id','outter'));

		            // $this->Board_model->set_join(array('cmall_brand','cmall_brand.cbr_id = cmall_item.cbr_id','inner'));

		        }

		        if(!empty($category_child_id) && is_array($category_child_id)){
		        	

		            

		            $this->db->join('(select cit_id,cca_id from cb_cmall_category_rel where cca_id in ('.implode(",",$category_child_id).') group by cit_id) as cb_cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner');
		        }

		        $_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in (12,13,14) ) AS A ';
				if($sattr && is_array($sattr)){
        			$_join = '';
        			$sattr_id = array();
        			foreach($all_attr as $akey => $aval){
        				
        				foreach($aval as  $aaval){	
        					foreach($sattr as $cval){
        						if($cval === element('cat_id',$aaval)){
        							$sattr_id[$akey][] = $cval;
        						}
        					}	
        	        	}
                	}


                	if(empty(element(8,$sattr_id)))
                		$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in (12,13,14) ) AS A ';
                	else
                		$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",element(8,$sattr_id)).') ) AS A ';

                	foreach($sattr_id as $skey => $sval){
                		
                		// if(empty($_join))
                		// 	$_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).')) AS A ';
                		// else 
                		if($skey != '8')
                			$_join .= 'INNER JOIN (select cit_id,cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') group by cit_id) AS B'.$skey.') AS cb_cmall_attr_rel'.$skey.' ON `A`.`cit_id` = `cb_cmall_attr_rel'.$skey.'`.`cit_id`';
                			
                		// $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                		
                	}
  	

                	
                	
                }
                if($_join)
	            	$this->db->join('(select cit_id,cat_id from ('.$_join.')  AS c) AS cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel'.'.cit_id','inner');

		        if($skind && is_array($skind)){

		        			// $this->db->where_in('cmall_kind_rel.ckd_id',$skind);
		        			// $this->db->where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);
		        		 //    $this->db->join('cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');
		        		    $this->db->join('(select cit_id,ckd_id from cb_cmall_kind_rel where ckd_id in ('.implode(",",$skind).') group by cit_id) AS cb_cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');
			        		
			            
			        }
				// $this->db->join('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');		
				$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
				// $this->db->join('cmall_category_rel', 'cmall_item.cit_id = cmall_category_rel.cit_id', 'inner');
				

				
				$qry = $this->db->get();
				$result = $qry->result_array();
				// $cmall_age = $result;

				foreach($result as $key => $val){
		        		$cmall_age[element('cat_id',$val)] = array(
		        			'cat_id' => element('cat_id',$val),
		        			'cat_value' => element('cat_value',$val),
		        			'rownum' => element('rownum',$val),
		        			);
				}

				if(!array_key_exists(12,$cmall_age)){
					$cmall_age[12] = array(
						'cat_id' => 12,
						'cat_value' => '퍼피',
						'rownum' => 0,
					);
				}
				if(!array_key_exists(13,$cmall_age)){
					$cmall_age[13] = array(
						'cat_id' => 13,
						'cat_value' => '어덜트',
						'rownum' => 0,
					);
				}
				if(!array_key_exists(14,$cmall_age)){
					$cmall_age[14] = array(
						'cat_id' => 14,
						'cat_value' => '시니어',
						'rownum' => 0,
					);
				}
				
			}
			
			if($option ==='category'){
				$group_by='cmall_category.cca_id';
				// if($sstart_price){            
			 //                $where['cit_price >= '] = $sstart_price;
			 //        }

			 //        if($send_price){            
			 //                $where['cit_price <='] = $send_price;
			 //        }

				if ($where) {			
					$this->db->where($where);			
				} 
				
				
		        

		        

		        

			        
				
				$this->db->group_by($group_by);
				$this->db->select($group_by.',count(*) as rownum');
				// $this->db->where(array('cmall_item.brd_id' =>2));				
				
		// 		$this->db->where(' (`cb_cmall_attr_rel`.`cat_id` = 12
		// or `cb_cmall_attr_rel`.`cat_id` = 13 
		// or `cb_cmall_attr_rel`.`cat_id` = 14 
		// )','',false);
				

					
						// $this->db->where($set_where, '',false);			
				
				$this->db->from('board');
				$this->db->join("
					(select cit_id,brd_id from cb_cmall_item ".$cmallwhere.") as cb_cmall_item",'cmall_item.brd_id = board.brd_id','inner');
				// $this->db->join('cmall_item', 'cmall_item.brd_id = board.brd_id', 'inner');

				if($skeyword){
		        	
		        	// $this->Board_model->set_where("(cit_name like '%".$skeyword."%' OR cta_tag = '".$skeyword."' OR cca_value = '".$skeyword."' OR cat_value = '".$skeyword."' OR cbr_value_kr = '".$skeyword."' )",'',false);

		        	// $this->Board_model->set_where("(  cbr_value_kr = '".$skeyword."' )",'',false);
		            
		            $this->db->join("
						(select cit_id from cb_cmall_item where cit_name like '%".$skeyword."%'
						UNION
						select cit_id from cb_crawl_tag where cta_tag = '".$skeyword."' 
						UNION
						select cit_id from cb_cmall_attr_rel INNER JOIN cb_cmall_attr ON cb_cmall_attr_rel.cat_id = cb_cmall_attr.cat_id  where cat_value = '".$skeyword."'
						UNION
						select cit_id from cb_board INNER JOIN cb_cmall_item ON cb_board.brd_id = cb_cmall_item.brd_id  where brd_name like '%".$skeyword."%'
						UNION
						select cit_id from cb_cmall_brand INNER JOIN cb_cmall_item ON cb_cmall_brand.cbr_id = cb_cmall_item.cbr_id  where cbr_value_kr like '%".$skeyword."%' or cbr_value_en like '%".$skeyword."%'
						) as AAA",'cmall_item.cit_id = AAA.cit_id','inner');
				           

				            // $this->Board_model->set_join(array('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner'));
				            // $this->Board_model->set_join(array("(select cca_id,cca_value from cb_cmall_category where cca_value like '%".$skeyword."%') as cb_cmall_category",'cmall_category_rel.cca_id = cmall_category.cca_id','outter'));

				            // $this->Board_model->set_join(array("(select cit_id,cta_id,cta_tag from cb_crawl_tag where cta_tag like '%".$skeyword."%') as cb_crawl_tag",'cmall_item.cit_id = crawl_tag.cit_id','inner'));

				            // $this->Board_model->set_join(array('cmall_attr_rel','cmall_attr_rel.cit_id = cmall_item.cit_id','inner'));
				            // $this->Board_model->set_join(array("(select cat_id,cat_value from cb_cmall_attr where cat_value like '%".$skeyword."%') as cb_cmall_attr",'cmall_attr.cat_id = cmall_attr_rel.cat_id','outter'));

				            // $this->Board_model->set_join(array('cmall_brand','cmall_brand.cbr_id = cmall_item.cbr_id','inner'));

				        }
				// if($sattr){


		  //           $this->db->where_in('cmall_attr_rel.cat_id',$sattr);
		  //           $this->db->join('cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel.cit_id','inner');
		  //       }

				
				if($sattr && is_array($sattr)){
        			$_join = '';	
        			$sattr_id = array();
        			foreach($all_attr as $akey => $aval){
        				
        				foreach($aval as  $aaval){	
        					foreach($sattr as $cval){
        						if($cval === element('cat_id',$aaval)){
        							$sattr_id[$akey][] = $cval;
        						}
        					}	
        	        	}
                	}



                	
                	foreach($sattr_id as $skey => $sval){
                	
                		if(empty($_join))
                        $_join = 'select A.cit_id,A.cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).')  ) AS A ';
                    else 
                        $_join .= 'INNER JOIN (select cit_id,cat_id from (select cit_id,cat_id from cb_cmall_attr_rel where cat_id in ('.implode(",",$sval).') ) AS B'.$skey.') AS cb_cmall_attr_rel'.$skey.' ON `A`.`cit_id` = `cb_cmall_attr_rel'.$skey.'`.`cit_id`';
                			
                		// $this->Board_model->set_where_in('cmall_attr_rel.cat_id',$sval);
                		
                	}
  	

                	if($_join)
                		$this->db->join('(select cit_id,cat_id from ('.$_join.') AS c group by cit_id) AS cmall_attr_rel','cmall_item.cit_id = cmall_attr_rel'.'.cit_id','inner');	
                }
                

		        if($skind && is_array($skind)){

		        			// $this->db->where_in('cmall_kind_rel.ckd_id',$skind);
		        			// $this->db->where('cb_cmall_attr.cat_id in(select ckd_size from cb_cmall_kind where ckd_id in ('.implode(",",$skind).'))','',false);
		        		 //    $this->db->join('cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');

		        		    $this->db->join('(select cit_id,ckd_id from cb_cmall_kind_rel where ckd_id in ('.implode(",",$skind).') group by cit_id) AS cb_cmall_kind_rel','cmall_item.cit_id = cmall_kind_rel.cit_id','inner');

		        		if(empty($_join))
				        	$this->db->join('(select cit_id,cat_id from cb_cmall_attr_rel group by cit_id) as cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');	
			        	
		        		
						$this->db->join('cmall_attr', 'cmall_attr.cat_id = cmall_attr_rel.cat_id', 'inner');
			            
			        }
				// $this->db->join('cmall_attr_rel', 'cmall_attr_rel.cit_id = cmall_item.cit_id', 'inner');		
				// $this->db->join('crawl_tag', 'crawl_tag.cit_id = cmall_item.cit_id', 'inner');
				
				if(!empty($category_child_id) && is_array($category_child_id)){
		        	

		            // $this->db->where_in('cmall_category_rel.cca_id',$category_child_id);
		            // $this->db->join('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner');

		            $this->db->join('(select cit_id,cca_id from cb_cmall_category_rel where cca_id in ('.implode(",",$category_child_id).') ) as cb_cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner');
		        } else {
		        	$this->db->join('cmall_category_rel','cmall_item.cit_id = cmall_category_rel.cit_id','inner');
		        }

				$this->db->join('cmall_category', 'cmall_category.cca_id = cmall_category_rel.cca_id', 'inner');
				

				
				$qry = $this->db->get();
				$result = $qry->result_array();
				$list = array();
				foreach($result as $key => $value){

		            $list[element('cca_id',$value)] = $value;
		        }


				foreach($all_category as $key => $value){
					foreach($value as $key_ => $value_){
			            if(element(element('cca_id',$value_),$list))
							$all_category[$key][$key_]['rownum'] =  element('rownum',element(element('cca_id',$value_),$list));
						else
							$all_category[$key][$key_]['rownum'] =  0;
					}
		        }

        		$i=0;


        		foreach($all_category as $akey => $a_cvalue){

                    foreach($a_cvalue as $a_cvalue_){
                    	if($akey ==0){
                    		$cmall_category[] = array(
                    			'cca_id' =>element('cca_id',$a_cvalue_),
                    			'cca_value' =>element('cca_value',$a_cvalue_),            			
                    			'rownum' =>element('rownum',$a_cvalue_),            			
                    			'cca_child' =>element(element('cca_id',$a_cvalue_),$all_category),

                    			);
                    	}    
                       
                    }
                    $i++;
                }
		        // print_r2($all_category);exit;
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
			
			
			

			
			
			

			
	        $i = 0;

	        foreach($cmall_age as $key=>$val){

		        unset($cmall_age[$key]);



		        

		        $cmall_age[$i] = $val;



		        $i++;

	        }


	        $i = 0;

	        foreach($cmall_size as $key=>$val){

		        unset($cmall_size[$key]);



		        

		        $cmall_size[$i] = $val;



		        $i++;

	        }

	        
			$view['view']['config']['cmall_price'] = $cmall_price;        
	        $view['view']['config']['cmall_size'] = $cmall_size;        
	        $view['view']['config']['cmall_color'] = $cmall_color;
	        $view['view']['config']['cmall_age'] = $cmall_age;        
			$view['view']['config']['cmall_category'] = $cmall_category;
	        // $view['view']['config']['cmall_kind'] = element(0,$this->Cmall_kind_model->get_all_kind());
	    }	
	        $view['view']['search_url'] = site_url('search/show_list?' . $param->output());
	        $view['view']['search_price_url'] = site_url('search/price?' . $param->output());
	        $view['view']['search_size_url'] = site_url('search/size?' . $param->output());
	        $view['view']['search_color_url'] = '';
	        if($is_color)
	        	$view['view']['search_color_url'] = site_url('search/color?' . $param->output());
	        $view['view']['search_age_url'] = site_url('search/age?' . $param->output());
	        $view['view']['search_category_url'] = site_url('search/category/?' . $param->output());
	    

		return $view['view'];
	}

	public function index_get($option = 'show_list',$oth_id = 0)
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
			'ssort' => $this->input->get('ssort'),
			'option' => $option,
			// 'category_id' => $category_id,
			'scategory' => $this->input->get('scategory'),
			'skeyword' => $this->input->get('skeyword'),
			'sage' => $this->input->get('sage'),
			'sattr' => $this->input->get('sattr'),
			'skind' => $this->input->get('skind'),
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
		$scategory = element('scategory', $config) ? element('scategory', $config) : '0';
		$skeyword = element('skeyword', $config) ? element('skeyword', $config) : '';
		$sage = element('sage', $config) ? element('sage', $config) : '0';
		$sattr = element('sattr', $config) ? element('sattr', $config) : '0';
		$sstart_price = element('sstart_price', $config) ? element('sstart_price', $config) : '0';
		$send_price = element('send_price', $config) ? element('send_price', $config) : '0';

		$page = element('page', $config) ? element('page', $config) : 1;
		$sop = element('sop', $config) ? element('sop', $config) : '';
		$findex = element('findex', $config) ? element('findex', $config) : '(0.1/cit_order)';
		$forder = element('forder', $config) ? element('forder', $config) : 'DESC';
		$limit = element('limit', $config) ? element('limit', $config) : '';
		$period_second = element('period_second', $config);
		$cache_minute = element('cache_minute', $config) ? element('cache_minute', $config) : '1';

		$view['view']['child_category'] = $this->cmalllib->get_child_category($scategory);
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

		// $per_page = 15;
		$per_page = get_listnum(15);
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
        
        if($scategory){            
            if(is_array($scategory))
            	$this->Board_model->group_where_in('cca_id',impode(',',$scategory));
            else 
            	$this->Board_model->group_where_in('cca_id',$scategory);
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
			'scategory' => $this->input->get('scategory'),
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
