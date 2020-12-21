<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mypage class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 마이페이지와 관련된 controller 입니다.
 */
class Mypage extends CB_Controller
{

	/**
	 * 모델을 로딩합니다
	 */
	protected $models = array();

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
		$this->load->library(array('pagination', 'querystring','denguruapi'));

	}


	/**
	 * 마이페이지입니다
	 */
	protected function _index()
	{
		

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$view = $data = array();
		$view['view'] = array();

		// $registerform = $this->cbconfig->item('registerform');
		// $view['view']['memberform'] = json_decode($registerform, true);
		$data['member'] = $this->denguruapi->get_mem_info($this->member->item('mem_id'));					
		
		$this->load->model(array('Cmall_order_model'));
		
		$data['member_group_name'] = '';
		$member_group = $this->member->group();
		if ($member_group && is_array($member_group)) {

			$this->load->model('Member_group_model');

			foreach ($member_group as $gkey => $gval) {
				$item = $this->Member_group_model->item(element('mgr_id', $gval));
				if ($data['member_group_name']) {
					$data['member_group_name'] .= ', ';
				}
				$data['member_group_name'] .= element('mgr_title', $item);
			}
		}

		



		
			$owhere = array(
				'mem_id' => $this->member->item('mem_id'),
				'cor_status' => 0,
				// 'brd_id' => 11,
			);
			$order_crawl = $this->Cmall_order_model->get('', 'cor_id,brd_id,cor_key,cor_pay_type', $owhere);
			
			$data['orderstatus'] = array();
			if ($order_crawl) {
				foreach ($order_crawl as $okey => $oval) {
					$board_crawl = $this->denguruapi->get_all_crawl(element('brd_id',$oval));	

					$param =& $this->querystring;
					$brd_url_key_ = parse_url(trim(element('brd_url_key',$board_crawl)));

					$brd_info = $this->denguruapi->get_brd_info(element('brd_id', $oval));

					if(element('brd_order_key',$board_crawl)==='sixshop' || element('brd_order_key',$board_crawl)==='parse'){
						$data['orderstatus'][$okey] = array('brd_orderstatus_url' => element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).element('cor_key',$oval),'cor_id' =>element('cor_id',$oval),'brd_id' =>element('brd_id',$oval),'brd_name' =>element('brd_name',$brd_info));
					} else {
						$data['orderstatus'][$okey] = array('brd_orderstatus_url' => element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).'?'.$param->replace(element('brd_order_key',$board_crawl),element('cor_key',$oval),element('query',$brd_url_key_)),'cor_id' =>element('cor_id',$oval),'brd_id' =>element('brd_id',$oval),'brd_name' =>element('brd_name',$brd_info));
					}

					if(element('cor_pay_type',$oval) =='naverpay'){
						$brd_url_key_ = parse_url(trim('https://m.pay.naver.com/o/orderStatus'));
						$data['orderstatus'][$okey] = array('brd_orderstatus_url' => element('scheme',$brd_url_key_)."://".element('host',$brd_url_key_).element('path',$brd_url_key_).element('cor_key',$oval),'cor_id' =>element('cor_id',$oval),'brd_id' =>element('brd_id',$oval),'brd_name' =>element('brd_name',$brd_info));
					}
					
				}
			}
		

		$view['view']['data'] = $data;
		
		return $view;
		
	}

	public function index_get()
	{
		

		

		$view = array();
		

		

		$view = $this->_index();

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage');
		$page_name = $this->cbconfig->item('site_page_name_mypage');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'main',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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


	/**
	 * 마이페이지>나의작성글 입니다
	 */
	public function post_get()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_post';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model(array('Post_model', 'Post_file_model'));

		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$findex = $this->Post_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'post.mem_id' => $mem_id,
			'post_del' => 0,
		);
		$result = $this->Post_model
			->get_post_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;

		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$brd_key = $this->board->item_id('brd_key', element('brd_id', $val));
				$result['list'][$key]['post_url'] = post_url($brd_key, element('post_id', $val));
				$result['list'][$key]['num'] = $list_num--;
				if (element('post_image', $val)) {
					$filewhere = array(
						'post_id' => element('post_id', $val),
						'pfi_is_image' => 1,
					);
					$file = $this->Post_file_model
						->get_one('', '', $filewhere, '', '', 'pfi_id', 'ASC');
					$result['list'][$key]['thumb_url'] = thumb_url('post', element('pfi_filename', $file), 50, 40);
				} else {
					$result['list'][$key]['thumb_url'] = get_post_image_url(element('post_content', $val), 50, 40);
				}
			}
		}

		$view['view']['data'] = $result;

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/post') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;


		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_post');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_post');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_post');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_post');
		$page_name = $this->cbconfig->item('site_page_name_mypage_post');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'post',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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
	 * 마이페이지>나의작성글(댓글) 입니다
	 */
	public function comment()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_comment';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$this->load->model(array('Post_model', 'Comment_model'));

		$findex = $this->Comment_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'comment.mem_id' => $mem_id,
		);
		$result = $this->Comment_model
			->get_comment_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;
		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$post = $this->Post_model
					->get_one(element('post_id', $val), 'brd_id');
				$brd_key = $this->board->item_id('brd_key', element('brd_id', $post));
				$result['list'][$key]['comment_url'] = post_url($brd_key, element('post_id', $val)) . '#comment_' . element('cmt_id', $val);
				$result['list'][$key]['num'] = $list_num--;
			}
		}
		$view['view']['data'] = $result;

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/comment') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;


		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_comment');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_comment');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_comment');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_comment');
		$page_name = $this->cbconfig->item('site_page_name_mypage_comment');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'comment',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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
	 * 마이페이지>포인트 입니다
	 */
	public function point()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_point';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		if ( ! $this->cbconfig->item('use_point')) {
			alert('이 웹사이트는 포인트 기능을 제공하지 않습니다');
		}

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model('Point_model');
		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$findex = $this->Point_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'point.mem_id' => $mem_id,
		);
		$result = $this->Point_model
			->get_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;
		$result['plus'] = 0;
		$result['minus'] = 0;
		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$result['list'][$key]['num'] = $list_num--;
				if (element('poi_point', $val) > 0) {
					$result['plus'] += element('poi_point', $val);
				} else {
					$result['minus'] += element('poi_point', $val);
				}
			}
		}
		$view['view']['data'] = $result;

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/point') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;


		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_point');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_point');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_point');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_point');
		$page_name = $this->cbconfig->item('site_page_name_mypage_point');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'point',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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
	 * 마이페이지>팔로우 입니다
	 */
	public function followinglist()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_followinglist';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model('Follow_model');

		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$findex = $this->Follow_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'follow.mem_id' => $mem_id,
		);
		$result = $this->Follow_model
			->get_following_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;
		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$result['list'][$key]['display_name'] = display_username(
					element('mem_userid', $val),
					element('mem_nickname', $val),
					element('mem_icon', $val)
				);
				$result['list'][$key]['num'] = $list_num--;
			}
		}
		$view['view']['data'] = $result;

		$view['view']['following_total_rows'] = $result['total_rows'];
		$countwhere = array(
			'target_mem_id' => $mem_id,
		);
		$view['view']['followed_total_rows'] = $this->Follow_model->count_by($countwhere);

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/followinglist') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;


		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_followinglist');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_followinglist');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_followinglist');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_followinglist');
		$page_name = $this->cbconfig->item('site_page_name_mypage_followinglist');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'followinglist',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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
	 * 마이페이지>팔로우(Followed) 입니다
	 */
	public function followedlist()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_followedlist';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model('Follow_model');
		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$findex = $this->Follow_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'follow.target_mem_id' => $mem_id,
		);
		$result = $this->Follow_model
			->get_followed_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;
		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$result['list'][$key]['display_name'] = display_username(
					element('mem_userid', $val),
					element('mem_nickname', $val),
					element('mem_icon', $val)
				);
				$result['list'][$key]['num'] = $list_num--;
			}
		}
		$view['view']['data'] = $result;

		$view['view']['followed_total_rows'] = $result['total_rows'];
		$countwhere = array(
			'mem_id' => $mem_id,
		);
		$view['view']['following_total_rows'] = $this->Follow_model->count_by($countwhere);

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/followedlist') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;


		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_followedlist');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_followedlist');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_followedlist');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_followedlist');
		$page_name = $this->cbconfig->item('site_page_name_mypage_followedlist');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'followedlist',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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
	 * 마이페이지>추천 입니다
	 */
	public function like_post()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_like_post';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model(array('Like_model', 'Post_file_model'));
		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$findex = $this->Like_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'like.mem_id' => $mem_id,
			'lik_type' => 1,
			'target_type' => 1,
			'post.post_del' => 0,
		);
		$result = $this->Like_model
			->get_post_like_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;

		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$brd_key = $this->board->item_id('brd_key', element('brd_id', $val));
				$result['list'][$key]['post_url'] = post_url($brd_key, element('post_id', $val));
				$result['list'][$key]['num'] = $list_num--;
				$images = '';
				if (element('post_image', $val)) {
					$filewhere = array(
						'post_id' => element('post_id', $val),
						'pfi_is_image' => 1,
					);
					$images = $this->Post_file_model
						->get_one('', '', $filewhere, '', '', 'pfi_id', 'ASC');
				}
				$result['list'][$key]['images'] = $images;
			}
		}
		$view['view']['data'] = $result;

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/like_post') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;


		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_like_post');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_like_post');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_like_post');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_like_post');
		$page_name = $this->cbconfig->item('site_page_name_mypage_like_post');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'like_post',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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
	 * 마이페이지>추천(댓글) 입니다
	 */
	public function like_comment()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_like_comment';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model(array('Like_model', 'Post_model'));
		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$findex = $this->Like_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'like.mem_id' => $mem_id,
			'lik_type' => 1,
			'target_type' => 2,
		);
		$result = $this->Like_model
			->get_comment_like_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;

		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$post = $this->Post_model->get_one(element('post_id', $val), 'brd_id');
				$brd_key = $this->board->item_id('brd_key', element('brd_id', $post));
				$result['list'][$key]['comment_url'] = post_url($brd_key, element('post_id', $val)) . '#comment_' . element('cmt_id', $val);
				$result['list'][$key]['num'] = $list_num--;
			}
		}
		$view['view']['data'] = $result;

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/like_comment') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;


		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_like_comment');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_like_comment');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_like_comment');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_like_comment');
		$page_name = $this->cbconfig->item('site_page_name_mypage_like_comment');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'like_comment',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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
	 * 마이페이지>스크랩 입니다
	 */
	public function scrap()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_scrap';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model('Scrap_model');
		/**
		 * Validation 라이브러리를 가져옵니다
		 */
		$this->load->library('form_validation');
		/**
		 * 전송된 데이터의 유효성을 체크합니다
		 */
		$config = array(
			array(
				'field' => 'scr_id',
				'label' => 'SCRAP ID',
				'rules' => 'trim|required|numeric',
			),
			array(
				'field' => 'scr_title',
				'label' => '제목',
				'rules' => 'trim',
			),
		);
		$this->form_validation->set_rules($config);


		/**
		 * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
		 * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
		 */
		$alert_message = '';
		if ($this->form_validation->run() === false) {

			// 이벤트가 존재하면 실행합니다
			$view['view']['event']['formrunfalse'] = Events::trigger('formrunfalse', $eventname);

		} else {
			/**
			 * 유효성 검사를 통과한 경우입니다.
			 * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
			 */

			// 이벤트가 존재하면 실행합니다
			$view['view']['event']['formruntrue'] = Events::trigger('formruntrue', $eventname);

			$scr_title = $this->input->post('scr_title', null, '');
			$updatedata = array(
				'scr_title' => $scr_title,
			);
			$this->Scrap_model->update($this->input->post('scr_id'), $updatedata);
			$alert_message = '제목이 저장되었습니다';
		}

		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
		$findex = $this->Scrap_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'scrap.mem_id' => $mem_id,
			'post.post_del' => 0,
		);
		$result = $this->Scrap_model
			->get_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;

		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				$result['list'][$key]['board'] = $board = $this->board->item_all(element('brd_id', $val));

				$result['list'][$key]['post_url'] = post_url(element('brd_key', $board), element('post_id', $val));
				$result['list'][$key]['board_url'] = board_url(element('brd_key', $board));
				$result['list'][$key]['delete_url'] = site_url('mypage/scrap_delete/' . element('scr_id', $val) . '?' . $param->output());
				$result['list'][$key]['num'] = $list_num--;
			}
		}
		$view['view']['data'] = $result;
		$view['view']['alert_message'] = $alert_message;

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/scrap') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_scrap');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_scrap');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_scrap');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_scrap');
		$page_name = $this->cbconfig->item('site_page_name_mypage_scrap');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'scrap',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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
	 * 마이페이지>스크랩삭제 입니다
	 */
	public function scrap_delete($scr_id = 0)
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_scrap_delete';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		// 이벤트가 존재하면 실행합니다
		Events::trigger('before', $eventname);

		$scr_id = (int) $scr_id;
		if (empty($scr_id) OR $scr_id < 1) {
			show_404();
		}

		$this->load->model('Scrap_model');
		$scrap = $this->Scrap_model->get_one($scr_id);

		if ( ! element('scr_id', $scrap)) {
			show_404();
		}
		if ((int) element('mem_id', $scrap) !== $mem_id) {
			show_404();
		}

		$this->Scrap_model->delete($scr_id);

		// 이벤트가 존재하면 실행합니다
		Events::trigger('after', $eventname);

		/**
		 * 삭제가 끝난 후 목록페이지로 이동합니다
		 */
		$this->session->set_flashdata(
			'message',
			'정상적으로 삭제되었습니다'
		);
		$param =& $this->querystring;

		redirect('mypage/scrap?' . $param->output());
	}


	/**
	 * 마이페이지>로그인기록 입니다
	 */
	public function loginlog()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_loginlog';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		/**
		 * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
		 */
		$param =& $this->querystring;
		$page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;

		$this->load->model('Member_login_log_model');

		$findex = $this->Member_login_log_model->primary_key;
		$forder = 'desc';

		// $per_page = $this->cbconfig->item('list_count') ? (int) $this->cbconfig->item('list_count') : 20;
		$per_page = get_listnum();
		$offset = ($page - 1) * $per_page;

		/**
		 * 게시판 목록에 필요한 정보를 가져옵니다.
		 */
		$where = array(
			'mem_id' => $mem_id,
		);
		$result = $this->Member_login_log_model
			->get_list($per_page, $offset, $where, '', $findex, $forder);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;
		if (element('list', $result)) {
			foreach (element('list', $result) as $key => $val) {
				if (element('mll_useragent', $val)) {
					$userAgent = get_useragent_info(element('mll_useragent', $val));
					$result['list'][$key]['browsername'] = $userAgent['browsername'];
					$result['list'][$key]['browserversion'] = $userAgent['browserversion'];
					$result['list'][$key]['os'] = $userAgent['os'];
					$result['list'][$key]['engine'] = $userAgent['engine'];
				}
				$result['list'][$key]['num'] = $list_num--;
			}
		}
		$view['view']['data'] = $result;

		/**
		 * 페이지네이션을 생성합니다
		 */
		$config['base_url'] = site_url('mypage/loginlog') . '?' . $param->replace('page');
		$config['total_rows'] = $result['total_rows'];
		$config['per_page'] = $per_page;
		$this->pagination->initialize($config);
		$view['view']['paging'] = $this->pagination->create_links();
		$view['view']['page'] = $page;


		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_loginlog');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_loginlog');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_loginlog');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_loginlog');
		$page_name = $this->cbconfig->item('site_page_name_mypage_loginlog');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'loginlog',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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

	protected function _review()
	{
		// // 이벤트 라이브러리를 로딩합니다
		// $eventname = 'event_mypage_post';
		// $this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		// $view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model(array( 'Cmall_review_model'));

		
        
        
        
        
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->input->get('findex', null, 'cre_like');
        $forder = $this->input->get('forder', null, 'desc');
        $sfield = '';
        $skeyword = '';

        // $per_page = 10;
        $per_page = get_listnum(10);
        $offset = ($page - 1) * $per_page;

        $is_admin = $this->member->is_admin();

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        // $where['cre_status'] = 1;
        // if($cit_id) $where['cit_id'] = $cit_id;

        $where = array(
			'cmall_review.mem_id' => $mem_id,
			'cre_status' => 1,
		);

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
                
                

                $result['list'][$key]['content'] = display_html_content(
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
                // $result['list'][$key] = $this->board->get_default_info($result['list'][$key]['brd_id'],$result['list'][$key]);                   
                $result['list'][$key] = $this->denguruapi->convert_review_info($result['list'][$key]);                   



                $result['list'][$key]['num'] = $list_num--;
                
            }
        }
        $view['view']['data'] = $result;
        

        /**
         * 페이지네이션을 생성합니다
         */
        $config['base_url'] = site_url('mypage/review/') . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;

        if ( ! $this->input->get('page')) {
            $_GET['page'] = (string) $page;
        }

        
        if ($this->cbconfig->get_device_view_type() === 'mobile') {
            $config['num_links'] = 3;
        } else {
            $config['num_links'] = 5;
        }
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['next_link'] = $this->pagination->get_next_link();
        $view['view']['page'] = $page;
        
   
        return $view;

		
	}

	public function review_get()
	{
		

		

		$view = array();
		

		

		$view = $this->_review();

		

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_post');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_post');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_post');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_post');
		$page_name = $this->cbconfig->item('site_page_name_mypage_post');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'review',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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

	protected function _likereview()
	{
		// 이벤트 라이브러리를 로딩합니다
		// $eventname = 'event_mypage_post';
		// $this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		// $view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model(array('Like_model', 'Cmall_review_model'));

		
        
        
        
        
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = $this->Like_model->primary_key;
		$forder = 'desc';
        $sfield = '';
        $skeyword = '';

        // $per_page = 10;
        $per_page = get_listnum(10);
        $offset = ($page - 1) * $per_page;

        $is_admin = $this->member->is_admin();

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        // $where['cre_status'] = 1;
        // if($cit_id) $where['cit_id'] = $cit_id;

        $where = array(
			'like.mem_id' => $mem_id,
			'lik_type' => 1,
			'target_type' => 3,
			// 'cre_status' => 1,
		);

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
        	'like' => array('lik_id,target_mem_id'),
            'cmall_review' => array('cre_id','cit_id','cre_good','cre_bad','cre_tip','mem_id','cre_score','cre_datetime','cre_like','cre_update_datetime'),
        );
        
        $select = get_selected($field);
        
        $this->Like_model->_select = $select;


        $result = $this->Like_model
            ->get_review_like_list($per_page, $offset, $where, '', $findex, $forder);
        
        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                
                

                

                $result['list'][$key]['can_update'] = false;
                $result['list'][$key]['can_delete'] = false;
                if ($is_admin !== false
                    OR (element('mem_id', $val) && $mem_id === (int) element('mem_id', $val))) {
                    $result['list'][$key]['can_update'] = true;
                    $result['list'][$key]['can_delete'] = true;
                }
                $result['list'][$key] = $this->denguruapi->get_cit_info(element('cit_id', $val),$result['list'][$key]);                   
                // $result['list'][$key] = $this->board->get_default_info($result['list'][$key]['brd_id'],$result['list'][$key]);
                $result['list'][$key] = $this->denguruapi->get_mem_info(element('target_mem_id', $val),$result['list'][$key]);
                $result['list'][$key]['review_cnt'] = $this->Cmall_review_model->count_by(array('mem_id' => element('target_mem_id', $val)));

                $result['list'][$key] = $this->denguruapi->convert_review_info($result['list'][$key]);                   

                $result['list'][$key]['num'] = $list_num--;
                
            }
        }
        $view['view'] = $result;
        

        /**
         * 페이지네이션을 생성합니다
         */
        $config['base_url'] = site_url('mypage/likereview/') . '?' . $param->replace('page');
        $config['total_rows'] = $result['total_rows'];
        $config['per_page'] = $per_page;

        if ( ! $this->input->get('page')) {
            $_GET['page'] = (string) $page;
        }

        
        if ($this->cbconfig->get_device_view_type() === 'mobile') {
            $config['num_links'] = 3;
        } else {
            $config['num_links'] = 5;
        }
        $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        $view['view']['next_link'] = $this->pagination->get_next_link();
        $view['view']['page'] = $page;
        
   
        return $view;

		
	}

	public function likereview_get()
	{
		// 이벤트 라이브러리를 로딩합니다
		

		

		$view = array();
		

		

		$view = $this->_likereview();

		

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_post');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_post');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_post');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_post');
		$page_name = $this->cbconfig->item('site_page_name_mypage_post');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'review',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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


	protected function _applyevent()
	{
		// 이벤트 라이브러리를 로딩합니다
		// $eventname = 'event_mypage_post';
		// $this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		// $view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model(array('Event_model'));

		
        
        
        
        
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = '(CASE WHEN eve_order=0 THEN -999 ELSE eve_order END),eve_id';
        $forder = 'desc';
        $sfield = $this->input->get('sfield', null, '');
        $skeyword = $this->input->get('skeyword', null, '');

        $per_page = '';
        $offset = '';

        // $per_page = 10;
        // $offset = ($page - 1) * $per_page;
        

        $is_admin = $this->member->is_admin();

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        // $where['cre_status'] = 1;
        // if($cit_id) $where['cit_id'] = $cit_id;

        $where = array();
        
        $where['eve_activated'] = '1';

        $thumb_width = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_review_mobile_thumb_width')
            : $this->cbconfig->item('cmall_product_review_thumb_width');
        

        $result = $this->Event_model
            ->get_admin_list($per_page, $offset, $where, '', $findex, $forder, $sfield, $skeyword);
        $list_num = $result['total_rows'];
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                $result['list'][$key]['post_url'] = base_url('event/post/'.element('eve_id', $val));

                $result['list'][$key]['display_datetime'] = display_datetime(
                    element('eve_datetime', $val),'full'
                );

                $result['list'][$key]['thumb_url'] = '';
                $result['list'][$key]['origin_image_url'] = '';
            
                
                if (element('eve_image', $val)) {
                    
                    $result['list'][$key]['thumb_url'] = thumb_url('event', element('eve_image', $val));
                    $result['list'][$key]['origin_image_url'] = thumb_url('event', element('eve_image', $val));
                } else {
                    $thumb_url = get_post_image_url(element('eve_content', $val));
                    $result['list'][$key]['thumb_url'] = $thumb_url
                        ? $thumb_url
                        : thumb_url('', '');

                    $result['list'][$key]['origin_image_url'] = $thumb_url;
                }
              

                if (empty($val['eve_start_date']) OR $val['eve_start_date'] === '0000-00-00') {
                    $result['list'][$key]['eve_start_date'] = display_datetime(
                                        element('eve_datetime', $val),'full'
                                        );


                }
                if (empty($val['eve_end_date']) OR $val['eve_end_date'] === '0000-00-00') {
                    $result['list'][$key]['eve_end_date'] = '지속';
                }
                // $result['list'][$key]['num'] = $list_num--;
            }
        }
        $view['view'] = $result;
        

        /**
         * 페이지네이션을 생성합니다
         */
        // $config['base_url'] = site_url('mypage/likereview/') . '?' . $param->replace('page');
        // $config['total_rows'] = $result['total_rows'];
        // $config['per_page'] = $per_page;

        // if ( ! $this->input->get('page')) {
        //     $_GET['page'] = (string) $page;
        // }

        
        // if ($this->cbconfig->get_device_view_type() === 'mobile') {
        //     $config['num_links'] = 3;
        // } else {
        //     $config['num_links'] = 5;
        // }
        // $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        // $view['view']['page'] = $page;
        
   
        return $view;

		
	}

	public function applyevent_get()
	{
		// 이벤트 라이브러리를 로딩합니다
		

		

		$view = array();
		

		
		

		$view = $this->_applyevent();

		

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_post');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_post');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_post');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_post');
		$page_name = $this->cbconfig->item('site_page_name_mypage_post');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'review',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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


	protected function _resultevent()
	{
		// 이벤트 라이브러리를 로딩합니다
		// $eventname = 'event_mypage_post';
		// $this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		// $view['view']['event']['before'] = Events::trigger('before', $eventname);

		$this->load->model(array('Event_model'));

		
        
        
        
        
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->querystring;
        $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;
        $findex = '(CASE WHEN eve_order=0 THEN -999 ELSE eve_order END),eve_id';
        $forder = 'desc';
        $sfield = $this->input->get('sfield', null, '');
        $skeyword = $this->input->get('skeyword', null, '');

        $per_page = '';
        $offset = '';

        // $per_page = 10;
        // $offset = ($page - 1) * $per_page;
        

        $is_admin = $this->member->is_admin();

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        // $where['cre_status'] = 1;
        // if($cit_id) $where['cit_id'] = $cit_id;

        $where = array();
        
        $where['eve_activated'] = '1';

        $thumb_width = ($this->cbconfig->get_device_view_type() === 'mobile')
            ? $this->cbconfig->item('cmall_product_review_mobile_thumb_width')
            : $this->cbconfig->item('cmall_product_review_thumb_width');
        

        $result = $this->Event_model
            ->get_admin_list($per_page, $offset, $where, '', $findex, $forder, $sfield, $skeyword);
        $list_num = $result['total_rows'];
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                $result['list'][$key]['post_url'] = base_url('event/post/'.element('eve_id', $val));

                $result['list'][$key]['display_datetime'] = display_datetime(
                    element('eve_datetime', $val),'full'
                );

                $result['list'][$key]['thumb_url'] = '';
                $result['list'][$key]['origin_image_url'] = '';
            
                
                if (element('eve_image', $val)) {
                    
                    $result['list'][$key]['thumb_url'] = thumb_url('event', element('eve_image', $val));
                    $result['list'][$key]['origin_image_url'] = thumb_url('event', element('eve_image', $val));
                } else {
                    $thumb_url = get_post_image_url(element('eve_content', $val));
                    $result['list'][$key]['thumb_url'] = $thumb_url
                        ? $thumb_url
                        : thumb_url('', '');

                    $result['list'][$key]['origin_image_url'] = $thumb_url;
                }
              

                if (empty($val['eve_start_date']) OR $val['eve_start_date'] === '0000-00-00') {
                    $result['list'][$key]['eve_start_date'] = display_datetime(
                                        element('eve_datetime', $val),'full'
                                        );


                }
                if (empty($val['eve_end_date']) OR $val['eve_end_date'] === '0000-00-00') {
                    $result['list'][$key]['eve_end_date'] = '지속';
                }
                $result['list'][$key]['num'] = $list_num--;
            }
        }
        $view['view'] = $result;
        

        /**
         * 페이지네이션을 생성합니다
         */
        // $config['base_url'] = site_url('mypage/likereview/') . '?' . $param->replace('page');
        // $config['total_rows'] = $result['total_rows'];
        // $config['per_page'] = $per_page;

        // if ( ! $this->input->get('page')) {
        //     $_GET['page'] = (string) $page;
        // }

        
        // if ($this->cbconfig->get_device_view_type() === 'mobile') {
        //     $config['num_links'] = 3;
        // } else {
        //     $config['num_links'] = 5;
        // }
        // $this->pagination->initialize($config);
        // $view['view']['paging'] = $this->pagination->create_links();
        // $view['view']['page'] = $page;
        
   
        return $view;

		
	}

	public function resultevent_get()
	{
		

		

		$view = array();

		$view = $this->_resultevent();

		

		/**
		 * 레이아웃을 정의합니다
		 */
		$page_title = $this->cbconfig->item('site_meta_title_mypage_post');
		$meta_description = $this->cbconfig->item('site_meta_description_mypage_post');
		$meta_keywords = $this->cbconfig->item('site_meta_keywords_mypage_post');
		$meta_author = $this->cbconfig->item('site_meta_author_mypage_post');
		$page_name = $this->cbconfig->item('site_page_name_mypage_post');

		$layoutconfig = array(
			'path' => 'mypage',
			'layout' => 'layout',
			'skin' => 'review',
			'layout_dir' => $this->cbconfig->item('layout_mypage'),
			'mobile_layout_dir' => $this->cbconfig->item('mobile_layout_mypage'),
			'use_sidebar' => $this->cbconfig->item('sidebar_mypage'),
			'use_mobile_sidebar' => $this->cbconfig->item('mobile_sidebar_mypage'),
			'skin_dir' => $this->cbconfig->item('skin_mypage'),
			'mobile_skin_dir' => $this->cbconfig->item('mobile_skin_mypage'),
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

	/**
	 * 게시판 글쓰기 또는 수정 페이지를 가져오는 메소드입니다
	 */
	protected function _petwrite($pid = 0)
	{
	    

	    

	    // 이벤트 라이브러리를 로딩합니다
        // $eventname = 'event_admin_member_memberpet_write';
        // $this->load->event($eventname);

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

	    /**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

	    $this->load->model(array('Member_model','Member_pet_model','Pet_allergy_model','Pet_attr_model','Cmall_kind_model','Pet_allergy_rel_model','Pet_attr_rel_model','Member_pethistory_model'));
	    $primary_key = $this->Member_pet_model->primary_key;

	    /**
	     * 수정 페이지일 경우 기존 데이터를 가져옵니다
	     */
	    $getdata = array();
	    if ($pid) {
	        $getdata = $this->Member_pet_model->get_one($pid);
	        if(empty(element('pet_id',$getdata)))
	        	alert('이 펫은 현재 존재하지 않습니다',"",406);

	        $getdata['pet_photo_url'] = cdn_url('member_photo',element('pet_photo',$getdata));

	        $getdata['pet_attr'] = $this->Pet_attr_model->get_attr(element('pet_id',$getdata));
	        
	        
	        $getdata['pet_allergy_rel'] = $this->Pet_allergy_model->get_allergy(element('pet_id',$getdata));

	        $is_admin = $this->member->is_admin();
            if ($is_admin === false
                && (int) element('mem_id', $getdata) !== $mem_id) {
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
                'field' => 'pet_name',
                'label' => '펫 이름',
                'rules' => 'trim|required|min_length[2]|max_length[20]',
            ),
            array(
                'field' => 'pet_birthday',
                'label' => '펫 생일',
                'rules' => 'trim|required|exact_length[10]',
            ),
	        array(
	            'field' => 'pet_sex',
	            'label' => '성별',
	            'rules' => 'trim|exact_length[1]',
	        ),
	        array(
	            'field' => 'pet_neutral',
	            'label' => '중성화 ',
	            'rules' => 'trim|numeric',
	        ),
	        array(
	            'field' => 'pat_id',
	            'label' => '체형 ',
	            'rules' => 'trim|numeric|required',
	        ),
	        array(
	            'field' => 'ckd_id',
	            'label' => '품종',
	            'rules' => 'trim|numeric|required',
	        ),
	        array(
	            'field' => 'pet_attr',
	            'label' => '우리 아이 특성',
	            'rules' => 'trim|callback__pet_attr',
	        ),
	        array(
	            'field' => 'pet_weight',
	            'label' => '몸무게',
	            'rules' => 'trim|numeric',
	        ),
	        array(
	            'field' => 'pet_is_allergy',
	            'label' => '알레르기',
	            'rules' => 'trim|numeric|required|callback__pet_is_allergy',
	        ),
	        
	        array(
	            'field' => 'pet_main',
	            'label' => '메인 펫',
	            'rules' => 'trim|numeric',
	        ),

	        

	        
	        // array(
	        //     'field' => 'pet_profile_content',
	        //     'label' => '펫 자기소개',
	        //     'rules' => 'trim',
	        // ),
	        
	        
	    );
	    
	    $this->form_validation->set_rules($config);
	    $form_validation = $this->form_validation->run();
	    $file_error = '';
	    $updatephoto = '';
	    $file_error2 = '';
	    $updateicon = '';

	    if ($form_validation) {
	        $this->load->library('upload');
	        $this->load->library('aws_s3');
	        if (isset($_FILES) && isset($_FILES['pet_photo']) && isset($_FILES['pet_photo']['name']) && $_FILES['pet_photo']['name']) {
	            $upload_path = config_item('uploads_dir') . '/member_photo/';
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

	            $uploadconfig = array();
	            $uploadconfig['upload_path'] = $upload_path;
	            $uploadconfig['allowed_types'] = 'jpg|jpeg|png|gif';
	            $uploadconfig['max_size'] = 100 * 1024;
	            // $uploadconfig['max_width'] = '2000';
	            // $uploadconfig['max_height'] = '1000';
	            $uploadconfig['encrypt_name'] = true;

	            $this->upload->initialize($uploadconfig);

	            if ($this->upload->do_upload('pet_photo')) {
	                $img = $this->upload->data();
	                $updatephoto = cdate('Y') . '/' . cdate('m') . '/' . element('file_name', $img);

	                $upload = $this->aws_s3->upload_file($this->upload->upload_path,$this->upload->file_name,$upload_path);
	            } else {
	                $file_error = $this->upload->display_errors();

	            }
	        }

	        if (isset($_FILES)
	            && isset($_FILES['pet_backgroundimg'])
	            && isset($_FILES['pet_backgroundimg']['name'])
	            && $_FILES['pet_backgroundimg']['name']) {
	            $upload_path = config_item('uploads_dir') . '/member_icon/';
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
	            $uploadconfig = array();
	            $uploadconfig['upload_path'] = $upload_path;
	            $uploadconfig['allowed_types'] = 'jpg|jpeg|png|gif';
	            $uploadconfig['max_size'] = 100 * 1024;
	            // $uploadconfig['max_width'] = '2000';
	            // $uploadconfig['max_height'] = '1000';
	            $uploadconfig['encrypt_name'] = true;

	            $this->upload->initialize($uploadconfig);

	            if ($this->upload->do_upload('pet_backgroundimg')) {
	                $img = $this->upload->data();
	                $updateicon = cdate('Y') . '/' . cdate('m') . '/' . element('file_name', $img);
	                $upload = $this->aws_s3->upload_file($this->upload->upload_path,$this->upload->file_name,$upload_path);
	            } else {
	                $file_error2 = $this->upload->display_errors();

	            }
	        }
	    }

	    /**
	     * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
	     * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
	     */
	    if ($form_validation === false OR $file_error !== '' OR $file_error2 !== '') {

	        
	        

	        

	        $view['msg'] = $file_error . $file_error2.validation_errors();
            
            $view['view']['data'] = $getdata;
            $pet_attr = array();
            
            

            $pet_attr = $this->Pet_attr_model->get_all_attr();
            
            

            
	        $view['view']['config']['pet_form'] = element(2,$pet_attr);
	        $view['view']['config']['pet_kind'] = element(0,$this->Cmall_kind_model->get_all_kind());
	        $view['view']['config']['pet_attr'] = element(1,$pet_attr);
	        $view['view']['config']['pet_age'] = element(3,$pet_attr);;
            
            $view['view']['config']['pet_allergy_rel'] = $this->Pet_allergy_model->get_all_allergy();
            

            // $view['view']['primary_key'] = $primary_key;

            if ($file_error . $file_error2.validation_errors()) {
            	log_message('error', 'msg:'.$file_error . $file_error2.validation_errors() .' pointer:'.current_url());

            	$view['http_status_codes'] = 400;
            } else {
            	$view['http_status_codes'] = parent::HTTP_OK;
            }
            

            
            return $view;
	        
	        
	        
	    } else {
	        /**
	         * 유효성 검사를 통과한 경우입니다.
	         * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
	         */

	        // 이벤트가 존재하면 실행합니다
	        // $view['view']['event']['formruntrue'] = Events::trigger('formruntrue', $eventname);
	        
	        $pet_sex = $this->input->post_put('pet_sex') ? $this->input->post_put('pet_sex') : 0;
	        $pet_neutral = $this->input->post_put('pet_neutral') ? $this->input->post_put('pet_neutral') : 0;
	        $pet_weight = $this->input->post_put('pet_weight') ? $this->input->post_put('pet_weight') : 0;
	        $pat_id = $this->input->post_put('pat_id') ? $this->input->post_put('pat_id') : 0;
	        $ckd_id = $this->input->post_put('ckd_id') ? $this->input->post_put('ckd_id') : 0;
	        $pet_is_allergy = $this->input->post_put('pet_is_allergy') ? $this->input->post_put('pet_is_allergy') : 0;

	        $updatedata = array(
	            
	            'pet_name' => $this->input->post_put('pet_name', null, ''),
	            'pet_birthday' => $this->input->post_put('pet_birthday', null, ''),
	            'pet_sex' => $pet_sex,
	            'pet_neutral' => $pet_neutral,
	            'pet_weight' => $pet_weight,                
	            'pet_is_allergy' => $pet_is_allergy,
	            'pat_id' => $pat_id,
	            'ckd_id' => $ckd_id,
	            
	        );

	        
	        
	        $metadata = array();

	       
	        // if (element('pet_nickname', $getdata) !== $this->input->post('pet_nickname')) {
	        //     $updatedata['pet_nickname'] = $this->input->post('pet_nickname', null, '');
	        // }
	        

	        if ($this->input->post_put('pet_photo_del')) {
	            $updatedata['pet_photo'] = '';
	        } 
	        if ($updatephoto) {
	            $updatedata['pet_photo'] = $updatephoto;
	        }
	        if (element('pet_photo', $getdata) && ($this->input->post_put('pet_photo_del') OR $updatephoto)) {
	            // 기존 파일 삭제
	            @unlink(config_item('uploads_dir') . '/member_photo/' . element('pet_photo', $getdata));
	            $deleted = $this->aws_s3->delete_file(config_item('s3_folder_name') . '/member_photo/' . element('pet_photo', $getdata));
	        }
	        if ($this->input->post_put('pet_backgroundimg_del')) {
	            $updatedata['pet_backgroundimg'] = '';
	        } elseif ($updateicon) {
	            $updatedata['pet_backgroundimg'] = $updateicon;
	        }
	        if (element('pet_backgroundimg', $getdata) && ($this->input->post_put('pet_backgroundimg_del') OR $updateicon)) {
	            // 기존 파일 삭제
	            @unlink(config_item('uploads_dir') . '/member_icon/' . element('pet_backgroundimg', $getdata));
	            $deleted = $this->aws_s3->delete_file(config_item('s3_folder_name') . '/member_icon/' . element('pet_backgroundimg', $getdata));
	        }

	        /**
	         * 게시물을 수정하는 경우입니다
	         */
	        // if ($this->input->post_put($primary_key)) {
	        //     $pet_id = $this->input->post_put($primary_key);
	        //     $this->Member_pet_model->update($pet_id, $updatedata);
	            
	        //     $view['msg'] = '정상적으로 수정되었습니다';
	            
	                
	                
	            
	        // } else {
	        if ($pid) {
	            
	            $this->Member_pet_model->update($pid, $updatedata);
	            
	            $pet_attr = $this->input->post('pet_attr', null, '');
	            $pet_allergy_rel = $this->input->post('pet_allergy_rel', null, '');

	            if($pet_allergy_rel){
	            	array_push($pet_allergy_rel,1);
	            	array_push($pet_allergy_rel,2);
	            }
	            
	            $this->Pet_allergy_rel_model->save_attr($pid, $pet_allergy_rel);
				$this->Pet_attr_rel_model->save_attr($pid, $pet_attr);


				$historydata = array(
                    'pet_id' => $getdata['pet_id'],
                    'mem_id' => $getdata['mem_id'],
                    'pet_name' => $getdata['pet_name'],
                    'pet_birthday' => $getdata['pet_birthday'],
                    'pet_sex' => $getdata['pet_sex'],
                    'pet_register_datetime' => $getdata['pet_register_datetime'],                    
                    'pet_profile_content' => $getdata['pet_profile_content'],
                    'pet_neutral' => $getdata['pet_neutral'],
                    'pet_weight' => $getdata['pet_weight'],
                    'pat_id' => $getdata['pat_id'],
                    'ckd_id' => $getdata['ckd_id'],
                    'pet_main' => $getdata['pet_main'],
                    'pet_is_allergy' => $getdata['pet_is_allergy'],
                    'pet_var3' => $getdata['pet_var3'],
                    'pet_modify_datetime' => cdate('Y-m-d H:i:s'),
                );
                $this->Member_pethistory_model->insert($historydata);
                
	            $view['msg'] = '정상적으로 수정되었습니다';
	            
	                
	                
	            
	        } else {
	            /**
	             * 게시물을 새로 입력하는 경우입니다
	             */
	            $updatedata['pet_register_datetime'] = cdate('Y-m-d H:i:s');
	            $updatedata['mem_id'] = $mem_id;
	            
	            $pid = $this->Member_pet_model->insert($updatedata);

	            
	            $pet_attr = $this->input->post('pet_attr', null, '');
	            $pet_allergy_rel = $this->input->post('pet_allergy_rel', null, '');
	            
	            if($pet_allergy_rel){
	            	array_push($pet_allergy_rel,1);
	            	array_push($pet_allergy_rel,2);
	            }
	            $this->Pet_allergy_rel_model->save_attr($pid, $pet_allergy_rel);
				$this->Pet_attr_rel_model->save_attr($pid, $pet_attr);

	            $view['msg'] = '정상적으로 입력되었습니다';
	            
	        }

	        if($pid && $this->input->post_put('pet_main', null, '')){

	        	$petdata = $this->Member_pet_model->get_one($pid);

	        	$this->Member_pet_model->update('',array('pet_main' => 0),array('mem_id' => element('mem_id',$petdata)));

	            $this->Member_pet_model->update($pid,array('pet_main' => 1));
	        }

	        $view['http_status_codes'] = 201;

            return $view;
	    }
	}

	public function petwrite_get($pet_id = 0)
	{
	    

	    $view = array();
	    

	    // 이벤트가 존재하면 실행합니다
	    // $view['view']['event']['before'] = Events::trigger('before', $eventname);

	    $view = $this->_petwrite($pet_id);
	    
	    $this->data = $view['view'];
		
		return $this->response($this->data, parent::HTTP_OK);
		
	}

	public function petwrite_post($pet_id = 0)
	{
	    // 이벤트 라이브러리를 로딩합니다
	    
	    // $this->load->event($eventname);

	    $view = array();
	    

	    // 이벤트가 존재하면 실행합니다
	    // $view['view']['event']['before'] = Events::trigger('before', $eventname);

	    $view = $this->_petwrite($pet_id);
	    
	    return $this->response(array('msg' => $view['msg']), $view['http_status_codes']);
	}

	public function petwrite_put($pet_id = 0)
	{
	    

	    $view = array();
	    

	    // 이벤트가 존재하면 실행합니다
	    // $view['view']['event']['before'] = Events::trigger('before', $eventname);

	    $view = $this->_petwrite($pet_id);
		
		return $this->response(array('msg' => $view['msg']), $view['http_status_codes']);
		
	}

	

	public function pet_delete($pet_id = 0)
    {
        // 이벤트 라이브러리를 로딩합니다
        $eventname = 'event_admin_member_memberpet_listdelete';
        // $this->load->event($eventname);

        

        // 이벤트가 존재하면 실행합니다
        // Events::trigger('before', $eventname);

        /**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$this->load->model(array('Member_pet_model'));

		$mem_id = (int) $this->member->item('mem_id');
		
		$pet_id = (int) $pet_id;
		if (empty($pet_id) OR $pet_id < 1) {
		    show_404();
		}

		$getdata = $this->Member_pet_model->get_one($pet_id);
		if(empty(element('pet_id',$getdata)))
			alert('이 펫은 현재 존재하지 않습니다',"",406);

		$is_admin = $this->member->is_admin();
        if ($is_admin === false 
            && (int) element('mem_id', $getdata) !== $mem_id) {
            alert_close('본인의 글 외에는 접근하실 수 없습니다');
        }


        /**
         * 체크한 게시물의 삭제를 실행합니다
         */

        $this->member->delete_pet($pet_id);
        

        // 이벤트가 존재하면 실행합니다
        Events::trigger('after', $eventname);

        /**
         * 삭제가 끝난 후 목록페이지로 이동합니다
         */
        
            
        
        return $this->response(array('msg' => '정상적으로 삭제되었습니다'),204);
    }

    public function _pet_is_allergy($str)
    {   

        
        
        
        if(!empty($str)){
        	if(empty($this->input->post('pet_allergy_rel'))){
        		$this->form_validation->set_message(
        		    '_pet_is_allergy',
        		    '상세한 알레르기를 선택해 주세요.'
        		);
        		return false;
        	}
        }

        return true;
    }

    public function _pet_attr($str)
    {   
    	
    	
    	if(count($this->input->post('pet_attr')) < 1){
        	
        		$this->form_validation->set_message(
        		    '_pet_attr',
        		    '우리 아이 특징을 선택해 주세요'
        		);
        		return false;
        	
        }
        
        if(count($this->input->post('pet_attr')) > 5){
        	
        		$this->form_validation->set_message(
        		    '_pet_attr',
        		    '우리 아이 특징은 5개 까지만 선택이 가능합니다..'
        		);
        		return false;
        	
        }

        return true;
    }

    public function setup_get()
    {   
    	
    	/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

    	$view = array();
    	$view['data']['mem_receive_email'] = $this->member->item('mem_receive_email');
    	$view['data']['mem_receive_sms'] = $this->member->item('mem_receive_sms');

        return $this->response($view,200);
    }

    public function setup_post($mem_receive_type,$flag=0)
    {   
    	
    	/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');
		$view = array();
		if($mem_receive_type === 'mem_receive_email'){

			$updatedata['mem_receive_email'] = $flag;			

			$this->Member_model->update($mem_id, $updatedata);

		    return $this->response(array('msg' => '정상적으로 처리 되었습니다'),201);

        	
        } elseif($mem_receive_type === 'mem_receive_sms'){
        		$updatedata['mem_receive_sms'] = $flag;			

        		$this->Member_model->update($mem_id, $updatedata);

        	    return $this->response(array('msg' => '정상적으로 처리 되었습니다'),201);
      	} else{
      		alert('잘못된 접근입니다.',"",403);
      	}
    	
    	alert('잘못된 접근입니다.',"",403);
    }
}

