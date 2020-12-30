<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Membermodify class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 회원 정보 수정시 담당하는 controller 입니다.
 */
class Membermodify extends CB_Controller
{

	/**
	 * 모델을 로딩합니다
	 */
	protected $models = array('Member_nickname', 'Member_meta', 'Member_auth_email', 'Member_extra_vars');

	/**
	 * 헬퍼를 로딩합니다
	 */
	protected $helpers = array('form', 'array', 'string','cmall');

	function __construct()
	{
		parent::__construct();

		/**
		 * 라이브러리를 로딩합니다
		 */
		$this->load->library(array('querystring', 'form_validation', 'email', 'notelib','denguruapi'));
	}


	/**
	 * 회원정보 수정 페이지입니다
	 */
	public function _index()
	{

		// 이벤트 라이브러리를 로딩합니다
		// $eventname = 'event_membermodify_index';
		// $this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$view = array();
		

		// 이벤트가 존재하면 실행합니다
		// $view['view']['event']['before'] = Events::trigger('before', $eventname);

		$mem_id = (int) $this->member->item('mem_id');

		if ( ! $this->member->item('mem_password')) {
			// return $this->defaultinfo();
			
		}

		$this->load->library(array('form_validation'));

		if ( ! function_exists('password_hash')) {
			$this->load->helper('password');
		}

		$login_fail = false;
		$valid_fail = false;

		/**
		 * 전송된 데이터의 유효성을 체크합니다
		 */
		$config = array(
			array(
				'field' => 'mem_password',
				'label' => '패스워드',
				'rules' => 'trim|required|min_length[4]|callback__cur_password_check',
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

			// $skin = 'member_password';

			// $view['view']['canonical'] = site_url('membermodify');

			// 이벤트가 존재하면 실행합니다
			// $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

			/**
			 * 레이아웃을 정의합니다
			 */
			
            
            

			
			
			
			$view['view']['data']['mem_email'] = $this->member->item('mem_email');
					
			
			if(validation_errors()){
            	$view['http_status_codes'] = 400;
            	$view['msg'] = validation_errors();
            	log_message('error', 'msg:' .validation_errors() .' pointer:'.current_url());
			}
            else 
            	$view['http_status_codes'] = 200;
            return $view;
		} else {
			/**
			 * 유효성 검사를 통과한 경우입니다.
			 * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
			 */

			// 이벤트가 존재하면 실행합니다
			// $view['view']['event']['formruntrue'] = Events::trigger('formruntrue', $eventname);

			$view['msg'] = '확인 되었습니다.';
            
            

			
			
			
			
            $view['http_status_codes'] = 200;
            return $view;
			
		}

	}

	public function index_get()
	{
		// 이벤트 라이브러리를 로딩합니다
		

		

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


	public function index_post()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_mypage_index';
		$this->load->event($eventname);

		

		$view = array();
		

		$view = $this->_index();

		return $this->response(array('msg' => $view['msg']), $view['http_status_codes']);
	}
	/**
	 * 회원정보 수정 페이지입니다
	 */
	public function _modify()
	{

		// 이벤트 라이브러리를 로딩합니다
		

		// if ( ! $this->session->userdata('membermodify')) {
		// 	redirect('membermodify');
		// }

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();
		$post = json_encode($this->input->post());
		log_message('error', $post);
		$mem_id = (int) $this->member->item('mem_id');

		$selfcert_type = $this->member->item('selfcert_type');
		$selfcert_company = $this->member->item('selfcert_company');
		$selfcert_phone = $this->member->item('selfcert_phone');
		$selfcert_username = $this->member->item('selfcert_username');
		$selfcert_birthday = $this->member->item('selfcert_birthday');
		$selfcert_sex = $this->member->item('selfcert_sex');
		$selfcert_is_adult = $this->member->item('selfcert_is_adult');


		 if ( ! function_exists('password_hash')) {
			$this->load->helper('password');
		}

		$password_length = $this->cbconfig->item('password_length');
		$view['view']['password_length'] = $password_length;

		$view = array();
		$view['view'] = array();



		$email_description = '';
		if ($this->cbconfig->item('use_register_email_auth')) {
			$email_description = '이메일을 변경하시면 메일 인증 후에 계속 사용이 가능합니다';
		}

		$configbasic = array();

		$can_update_nickname = false;
		$change_nickname_date = $this->cbconfig->item('change_nickname_date');
		if (empty($change_nickname_date)) {
			$can_update_nickname = true;
		} elseif (strtotime($this->member->item('meta_nickname_datetime')) < ctimestamp() - $change_nickname_date * 86400) {
			$can_update_nickname = true;
		}

		$when_can_update_nickname
			= cdate('Y-m-d H:s', strtotime($this->member->item('meta_nickname_datetime'))
			+ $change_nickname_date * 86400);

		$can_update_open_profile = false;
		$change_open_profile_date = $this->cbconfig->item('change_open_profile_date');
		if (empty($change_open_profile_date)) {
			$can_update_open_profile = true;
		} elseif (strtotime($this->member->item('meta_open_profile_datetime')) < ctimestamp() - $change_open_profile_date * 86400) {
			$can_update_open_profile = true;
		}
		// $view['view']['can_update_open_profile'] = $can_update_open_profile;
		$when_can_update_open_profile
			= cdate('Y-m-d H:s', strtotime($this->member->item('meta_open_profile_datetime'))
			+ $change_open_profile_date * 86400);

		$can_update_use_note = false;
		$change_use_note_date = $this->cbconfig->item('change_use_note_date');
		if (empty($change_use_note_date)) {
			$can_update_use_note = true;
		} elseif (strtotime($this->member->item('meta_use_note_datetime')) < ctimestamp() - $change_use_note_date * 86400) {
			$can_update_use_note = true;
		}
		// $view['view']['can_update_use_note'] = $can_update_use_note;
		$when_can_update_use_note
			= cdate('Y-m-d H:s', strtotime($this->member->item('meta_use_note_datetime'))
			+ $change_use_note_date * 86400);

		$nickname_description = '';
		if ($this->cbconfig->item('change_nickname_date')) {
			if ($can_update_nickname === false) {
				$nickname_description = '<br />닉네임을 변경하시면 ' . $this->cbconfig->item('change_nickname_date')
					. '일 이내에는 변경할 수 없습니다<br>회원님은 ' . $when_can_update_nickname
					. ' 이후에 닉네임 변경이 가능합니다';
			} else {
				$nickname_description = '<br />닉네임을 변경하시면 ' . $this->cbconfig->item('change_nickname_date') . '일 이내에는 변경할 수 없습니다';
			}
		}

		if ( ! $selfcert_username) {
			$configbasic['mem_username'] = array(
				'field' => 'mem_username',
				'label' => '이름',
				'rules' => 'trim|min_length[2]|max_length[20]',
			);
		}
		$configbasic['mem_nickname'] = array(
			'field' => 'mem_nickname',
			'label' => '닉네임',
			'rules' => 'trim|required|min_length[2]|max_length[20]|callback__mem_nickname_check',
			'description' => '공백없이 한글, 영문, 숫자만 입력 가능 2글자 이상' . $nickname_description,
		);

		$configbasic['mem_email'] = array(
			'field' => 'mem_email',
			'label' => '이메일',
			'rules' => 'trim|required|valid_email|max_length[50]|is_unique[member.mem_email.mem_id.' . $mem_id . ']|callback__mem_email_check',
			'description' => $email_description,
		);
		$configbasic['mem_homepage'] = array(
			'field' => 'mem_homepage',
			'label' => '홈페이지',
			'rules' => 'prep_url|valid_url',
		);
		if ( ! $selfcert_phone) {
			$configbasic['mem_phone'] = array(
				'field' => 'mem_phone',
				'label' => '휴대폰번호',
				'rules' => 'trim|valid_mobile|required|callback__mem_smsmap_check',
			);
		}
		if ( ! $selfcert_birthday) {
			$configbasic['mem_birthday'] = array(
				'field' => 'mem_birthday',
				'label' => '생년월일',
				'rules' => 'trim|exact_length[10]',
			);
		}
		if ( ! $selfcert_sex) {
			$configbasic['mem_sex'] = array(
				'field' => 'mem_sex',
				'label' => '성별',
				'rules' => 'trim|exact_length[1]',
			);
		}
		$configbasic['mem_zipcode'] = array(
			'field' => 'mem_zipcode',
			'label' => '우편번호',
			'rules' => 'trim|min_length[5]|max_length[7]',
		);
		$configbasic['mem_address1'] = array(
			'field' => 'mem_address1',
			'label' => '기본주소',
			'rules' => 'trim',
		);
		$configbasic['mem_address2'] = array(
			'field' => 'mem_address2',
			'label' => '상세주소',
			'rules' => 'trim',
		);
		$configbasic['mem_address3'] = array(
			'field' => 'mem_address3',
			'label' => '참고항목',
			'rules' => 'trim',
		);
		$configbasic['mem_address4'] = array(
			'field' => 'mem_address4',
			'label' => '지번',
			'rules' => 'trim',
		);
		$configbasic['mem_profile_content'] = array(
			'field' => 'mem_profile_content',
			'label' => '자기소개',
			'rules' => 'trim',
		);
		$configbasic['mem_open_profile'] = array(
			'field' => 'mem_open_profile',
			'label' => '정보공개',
			'rules' => 'trim|exact_length[1]',
		);
		if ($this->cbconfig->item('use_note')) {
			$configbasic['mem_use_note'] = array(
				'field' => 'mem_use_note',
				'label' => '쪽지사용',
				'rules' => 'trim|exact_length[1]',
			);
		}
		$configbasic['mem_receive_email'] = array(
			'field' => 'mem_receive_email',
			'label' => '이메일수신여부',
			'rules' => 'trim|exact_length[1]',
		);
		$configbasic['mem_receive_sms'] = array(
			'field' => 'mem_receive_sms',
			'label' => 'SMS 문자수신여부',
			'rules' => 'trim|exact_length[1]',
		);

		$this->load->library(array('form_validation'));
		$login_fail = false;
		$valid_fail = false;

		$registerform = $this->cbconfig->item('registerform');
		$form = json_decode($registerform, true);

		$config = array();

		// $config = array(
		// 	array(
		// 		'field' => 'cur_password',
		// 		'label' => '현재패스워드',
		// 		'rules' => 'trim|required|callback__cur_password_check',
		// 	),						
		// );

		if($this->input->post('new_password') || $this->input->post('new_password_re')){

			$config[] = array(
				'field' => 'new_password',
				'label' => '새로운패스워드',
				'rules' => 'trim|required|min_length[' . $password_length . ']|callback__mem_password_check',
			);
			$config[] = array(
				'field' => 'new_password_re',
				'label' => '새로운패스워드',
				'rules' => 'trim|required|min_length[' . $password_length . ']|matches[new_password]',
			);

		}

		

		if ($form && is_array($form)) {
			foreach ($form as $key => $value) {
				if ( ! element('use', $value)) {
					continue;
				}
				if ($key === 'mem_userid' OR $key === 'mem_password' OR $key === 'mem_recommend') {
					continue;
				}
				if ($key == 'mem_username' && $selfcert_username) {
					continue;
				}
				if ($key == 'mem_phone' && $selfcert_phone) {
					continue;
				}
				if ($key == 'mem_birthday' && $selfcert_birthday) {
					continue;
				}
				if ($key == 'mem_sex' && $selfcert_sex) {
					continue;
				}

				if (element('func', $value) === 'basic') {
					if ($key === 'mem_address') {
						if (element('required', $value) === '1') {
							$configbasic['mem_zipcode']['rules'] = $configbasic['mem_zipcode']['rules'] . '|required';
						}
						$config[] = $configbasic['mem_zipcode'];
						if (element('required', $value) === '1') {
							$configbasic['mem_address1']['rules'] = $configbasic['mem_address1']['rules'] . '|required';
						}
						$config[] = $configbasic['mem_address1'];
						if (element('required', $value) === '1') {
							$configbasic['mem_address2']['rules'] = $configbasic['mem_address2']['rules'] . '|required';
						}
						$config[] = $configbasic['mem_address2'];
					} else {
						if (element('required', $value) === '1') {
							$configbasic[$value['field_name']]['rules'] = $configbasic[$value['field_name']]['rules'] . '|required';
						}
						if (element('field_type', $value) === 'phone') {
							$configbasic[$value['field_name']]['rules'] = $configbasic[$value['field_name']]['rules'] . '|valid_phone';
						}
						$config[] = $configbasic[$value['field_name']];
					}
				} else {
					$required = element('required', $value) ? '|required' : '';
					if (element('field_type', $value) === 'checkbox') {
						$config[] = array(
							'field' => element('field_name', $value) . '[]',
							'label' => $value['display_name'],
							'rules' => 'trim' . $required,
						);
					} else {
						$config[] = array(
							'field' => element('field_name', $value),
							'label' => $value['display_name'],
							'rules' => 'trim' . $required,
						);
					}
				}
			}
		}

		$this->form_validation->set_rules($config);
		$form_validation = $this->form_validation->run();
		$file_error = '';
		$updatephoto = '';
		$file_error2 = '';
		$updateicon = '';

		if ($form_validation) {
			$this->load->library('upload');
			if ($this->cbconfig->item('use_member_photo')
				&& $this->cbconfig->item('member_photo_width') > 0
				&& $this->cbconfig->item('member_photo_height') > 0) {
				if (isset($_FILES) && isset($_FILES['mem_photo']) && isset($_FILES['mem_photo']['name']) && $_FILES['mem_photo']['name']) {
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
					$uploadconfig['max_size'] = '2000';
					$uploadconfig['max_width'] = '1000';
					$uploadconfig['max_height'] = '1000';
					$uploadconfig['encrypt_name'] = true;

					$this->upload->initialize($uploadconfig);

					if ($this->upload->do_upload('mem_photo')) {
						$img = $this->upload->data();
						$updatephoto = cdate('Y') . '/' . cdate('m') . '/' . $img['file_name'];
					} else {
						$file_error = $this->upload->display_errors();

					}
				}
			}

			if ($this->cbconfig->item('use_member_icon')
				&& $this->cbconfig->item('member_icon_width') > 0
				&& $this->cbconfig->item('member_icon_height') > 0) {
				if (isset($_FILES)
					&& isset($_FILES['mem_icon'])
					&& isset($_FILES['mem_icon']['name'])
					&& $_FILES['mem_icon']['name']) {
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
					$uploadconfig['max_size'] = '2000';
					$uploadconfig['max_width'] = '1000';
					$uploadconfig['max_height'] = '1000';
					$uploadconfig['encrypt_name'] = true;

					$this->upload->initialize($uploadconfig);

					if ($this->upload->do_upload('mem_icon')) {
						$img = $this->upload->data();
						$updateicon = cdate('Y') . '/' . cdate('m') . '/' . $img['file_name'];
					} else {
						$file_error2 = $this->upload->display_errors();

					}
				}
			}
		}

		/**
		 * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
		 * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
		 */
		if ($form_validation === false OR $file_error !== '' OR $file_error2 !== '') {

			$view['msg'] = $file_error . $file_error2.validation_errors();
            
            $post = json_encode($this->input->post());
            
			log_message('error', 'msg:'.$post.$file_error . $file_error2.validation_errors() .' pointer:'.current_url());
			
			$k = 0;
			if ($form && is_array($form)) {
				foreach ($form as $key => $value) {

					if ( ! element('use', $value)) {
						continue;
					}

					if ($key === 'mem_userid' OR $key === 'mem_password' OR $key === 'mem_recommend') {
						continue;
					}
					
					
					
					

					$view['view']['data'][$key] = $this->member->item($key);
					
				}
			}

			$view['view']['data']['mem_receive_email'] = $this->member->item('mem_receive_email');
			$view['view']['data']['mem_receive_sms'] = $this->member->item('mem_receive_sms');
            $view['http_status_codes'] = 400;

            
            return $view;

		} else {
			/**
			 * 유효성 검사를 통과한 경우입니다.
			 * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
			 */

			// 이벤트가 존재하면 실행합니다


			$updatedata = array();
			$metadata = array();

			if($this->input->post('new_password') || $this->input->post('new_password_re')){
				$hash = password_hash($this->input->post('new_password'), PASSWORD_BCRYPT);

				$updatedata = array(
					'mem_password' => $hash,
				);

				$metadata = array(
					'meta_change_pw_datetime' => cdate('Y-m-d H:i:s'),
				);
			}
			// $updatedata['mem_email'] = $this->input->post('mem_email');
			if ($this->member->item('mem_email') !== $this->input->post('mem_email')) {
				$updatedata['mem_email_cert'] = 0;
				$metadata['meta_email_cert_datetime'] = '';
			}
			if ($can_update_nickname
				&& $this->member->item('mem_nickname') !== $this->input->post('mem_nickname')) {
				$updatedata['mem_nickname'] = $this->input->post('mem_nickname');
				$metadata['meta_nickname_datetime'] = cdate('Y-m-d H:i:s');

				$upnick = array(
					'mni_end_datetime' => cdate('Y-m-d H:i:s'),
				);
				$nickwhere = array(
					'mem_id' => $mem_id,
					'mni_nickname' => $this->member->item('mem_nickname'),
				);
				$this->Member_nickname_model->update('', $upnick, $nickwhere);

				$nickinsert = array(
					'mem_id' => $mem_id,
					'mni_nickname' => $this->input->post('mem_nickname'),
					'mni_start_datetime' => cdate('Y-m-d H:i:s'),
				);
				$this->Member_nickname_model->insert($nickinsert);
			}
			if ($selfcert_username) {
				$updatedata['mem_username'] = $selfcert_username;
			} else if (isset($form['mem_username']['use']) && $form['mem_username']['use']) {
				$updatedata['mem_username'] = $this->input->post('mem_username', null, '');
			}
			if (isset($form['mem_homepage']['use']) && $form['mem_homepage']['use']) {
				$updatedata['mem_homepage'] = $this->input->post('mem_homepage', null, '');
			}
			if ($selfcert_phone) {
				$updatedata['mem_phone'] = $selfcert_phone;
			} else if (isset($form['mem_phone']['use']) && $form['mem_phone']['use']) {
				$updatedata['mem_phone'] = $this->input->post('mem_phone', null, '');
			}
			if ($selfcert_birthday) {
				$updatedata['mem_birthday'] = $selfcert_birthday;
			} else if (isset($form['mem_birthday']['use']) && $form['mem_birthday']['use']) {
				$updatedata['mem_birthday'] = $this->input->post('mem_birthday', null, '');
			}
			if ($selfcert_sex) {
				$updatedata['mem_sex'] = $selfcert_sex;
			} else if (isset($form['mem_sex']['use']) && $form['mem_sex']['use']) {
				$updatedata['mem_sex'] = $this->input->post('mem_sex', null, '');
			}
			if (isset($form['mem_address']['use']) && $form['mem_address']['use']) {
				$updatedata['mem_zipcode'] = $this->input->post('mem_zipcode', null, '');
				$updatedata['mem_address1'] = $this->input->post('mem_address1', null, '');
				$updatedata['mem_address2'] = $this->input->post('mem_address2', null, '');
				$updatedata['mem_address3'] = $this->input->post('mem_address3', null, '');
				$updatedata['mem_address4'] = $this->input->post('mem_address4', null, '');
			}
			$updatedata['mem_receive_email'] = $this->input->post('mem_receive_email') ? 1 : 0;
			if ($this->cbconfig->item('use_note')
				&& $can_update_use_note
				&& (
						($this->member->item('mem_use_note') === '1' && $this->input->post('mem_use_note') !== '1')
						OR
						($this->member->item('mem_use_note') !== '1' && $this->input->post('mem_use_note') === '1')
					)
				) {
				$updatedata['mem_use_note'] = $this->input->post('mem_use_note') ? 1 : 0;
				$metadata['meta_use_note_datetime'] = cdate('Y-m-d H:i:s');
			}
			$updatedata['mem_receive_sms'] = $this->input->post('mem_receive_sms') ? 1 : 0;
			if ($can_update_open_profile
				&& (
						($this->member->item('mem_open_profile') === '1' && $this->input->post('mem_open_profile') !== '1')
						OR
						($this->member->item('mem_open_profile') !== '1' && $this->input->post('mem_open_profile') === '1')
					)
				) {
				$updatedata['mem_open_profile'] = $this->input->post('mem_open_profile') ? 1 : 0;
				$metadata['meta_open_profile_datetime'] = cdate('Y-m-d H:i:s');
			}
			if (isset($form['mem_profile_content']['use']) && $form['mem_profile_content']['use']) {
				$updatedata['mem_profile_content'] = $this->input->post('mem_profile_content', null, '');
			}

			if ($this->input->post('mem_photo_del')) {
				$updatedata['mem_photo'] = '';
			} elseif ($updatephoto) {
				$updatedata['mem_photo'] = $updatephoto;
			}
			if ($this->member->item('mem_photo')
				&& ($this->input->post('mem_photo_del') OR $updatephoto)) {
				// 기존 파일 삭제
				@unlink(config_item('uploads_dir') . '/member_photo/' . $this->member->item('mem_photo'));
			}
			if ($this->input->post('mem_icon_del')) {
				$updatedata['mem_icon'] = '';
			} elseif ($updateicon) {
				$updatedata['mem_icon'] = $updateicon;
			}
			if ($this->member->item('mem_icon')
				&& ($this->input->post('mem_icon_del') OR $updateicon)) {
				// 기존 파일 삭제
				@unlink(config_item('uploads_dir') . '/member_icon/' . $this->member->item('mem_icon'));
			}

			
			$this->Member_model->update($mem_id, $updatedata);
			$this->Member_meta_model->save($mem_id, $metadata);

			$extradata = array();
			if ($form && is_array($form)) {
				foreach ($form as $key => $value) {
					if ( ! element('use', $value)) {
						continue;
					}
					if (element('func', $value) === 'basic') {
						continue;
					}
					$extradata[element('field_name', $value)] = $this->input->post(element('field_name', $value), null, '');
				}
				$this->Member_extra_vars_model->save($mem_id, $extradata);
			}

			if ($this->cbconfig->item('use_register_email_auth')
				&& $this->member->item('mem_email') !== $this->input->post('mem_email')) {

				$vericode = array('$', '/', '.');
				$verificationcode = str_replace(
					$vericode,
					'',
					password_hash($mem_id . '-' . $this->input->post('mem_email') . '-' . random_string('alnum', 10), PASSWORD_BCRYPT)
				);

				$beforeauthdata = array(
					'mem_id' => $mem_id,
					'mae_type' => 2,
				);
				$this->Member_auth_email_model->delete_where($beforeauthdata);
				$authdata = array(
					'mem_id' => $mem_id,
					'mae_key' => $verificationcode,
					'mae_type' => 2,
					'mae_generate_datetime' => cdate('Y-m-d H:i:s'),
				);
				$this->Member_auth_email_model->insert($authdata);

				$verify_url = site_url('verify/confirmemail?user=' . $this->member->item('mem_userid') . '&code=' . $verificationcode);

				$searchconfig = array(
					'{홈페이지명}',
					'{회사명}',
					'{홈페이지주소}',
					'{회원아이디}',
					'{회원닉네임}',
					'{회원실명}',
					'{회원이메일}',
					'{변경전이메일}',
					'{메일수신여부}',
					'{쪽지수신여부}',
					'{문자수신여부}',
					'{회원아이피}',
					'{메일인증주소}',
				);
				$receive_email = $this->member->item('mem_receive_email') ? '동의' : '거부';
				$receive_note = $this->member->item('mem_use_note') ? '동의' : '거부';
				$receive_sms = $this->member->item('mem_receive_sms') ? '동의' : '거부';
				$replaceconfig = array(
					$this->cbconfig->item('site_title'),
					$this->cbconfig->item('company_name'),
					site_url(),
					$this->member->item('mem_userid'),
					$this->member->item('mem_nickname'),
					$this->member->item('mem_username'),
					$this->input->post('mem_email'),
					$this->member->item('mem_email'),
					$receive_email,
					$receive_note,
					$receive_sms,
					$this->input->ip_address(),
					$verify_url,
				);

				$replaceconfig_escape = array(
					html_escape($this->cbconfig->item('site_title')),
					html_escape($this->cbconfig->item('company_name')),
					site_url(),
					$this->member->item('mem_userid'),
					html_escape($this->member->item('mem_nickname')),
					html_escape($this->member->item('mem_username')),
					html_escape($this->input->post('mem_email')),
					html_escape($this->member->item('mem_email')),
					$receive_email,
					$receive_note,
					$receive_sms,
					$this->input->ip_address(),
					$verify_url,
				);

				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_email_changeemail_user_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_email_changeemail_user_content')
				);

				$this->email->clear(true);
				$this->email->from($this->cbconfig->item('webmaster_email'), $this->cbconfig->item('webmaster_name'));
				$this->email->to($this->input->post('mem_email'));
				$this->email->subject($title);
				$this->email->message($content);
				$this->email->send();

				$view['view']['result_message'] = $this->input->post('mem_email') . '로 인증메일이 발송되었습니다. 발송된 인증메일을 확인하신 후에 사이트 이용이 가능합니다';

				$this->session->sess_destroy();

			} else {
				$view['view']['result_message'] = '회원정보가 변경되었습니다. 감사합니다';
			}

			

			$view['msg'] = $view['view']['result_message'];
			$view['http_status_codes'] = 201;

            return $view;
		}
	}


	public function modify_get()
	{
	    // 이벤트 라이브러리를 로딩합니다
	    $eventname = 'event_admin_member_memberpet_write';
	    // $this->load->event($eventname);

	    $view = array();
	    

	    // 이벤트가 존재하면 실행합니다
	    // $view['view']['event']['before'] = Events::trigger('before', $eventname);

	    $view = $this->_modify();
	    
	    $this->data = $view['view'];
		
		return $this->response($this->data, parent::HTTP_OK);
		
	}

	public function modify_post()
	{
	    // 이벤트 라이브러리를 로딩합니다
	    $eventname = 'event_admin_member_memberpet_write';
	    // $this->load->event($eventname);

	    $view = array();
	    $view['view'] = array();

	    // 이벤트가 존재하면 실행합니다
	    // $view['view']['event']['before'] = Events::trigger('before', $eventname);

	    $view = $this->_modify();
	    
	    return $this->response(array('msg' => $view['msg']), $view['http_status_codes']);
	}

	public function petwrite_put($pid = 0)
	{
	    // 이벤트 라이브러리를 로딩합니다
	    $eventname = 'event_admin_member_memberpet_write';
	    // $this->load->event($eventname);

	    $view = array();
	    $view['view'] = array();

	    // 이벤트가 존재하면 실행합니다
	    // $view['view']['event']['before'] = Events::trigger('before', $eventname);

	    $view = $this->_modify($mem_id);
		
		return $this->response(array('msg' => $view['msg']), $view['http_status_codes']);
		
	}

	/**
	 * 소셜로그인 한 회원의 회원정보 수정 페이지입니다
	 */
	public function defaultinfo()
	{

		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_membermodify_defaultinfo';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		if ($this->member->item('mem_password')) {
			redirect('membermodify');
		}

		 if ( ! function_exists('password_hash')) {
			$this->load->helper('password');
		}

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);


		$password_length = $this->cbconfig->item('password_length');
		$view['view']['password_length'] = $password_length;

		$config = array();

		// $config['mem_userid'] = array(
		// 	'field' => 'mem_userid',
		// 	'label' => '아이디',
		// 	'rules' => 'trim|required|alphanumunder|min_length[3]|max_length[20]|is_unique[member_userid.mem_userid]|callback__mem_userid_check',
		// );
		// $config['mem_password'] = array(
		// 	'field' => 'mem_password',
		// 	'label' => '패스워드',
		// 	'rules' => 'trim|required|min_length[' . $password_length . ']|callback__mem_password_check',
		// );
		// $config['mem_password_re'] = array(
		// 	'field' => 'mem_password_re',
		// 	'label' => '패스워드 확인',
		// 	'rules' => 'trim|required|min_length[' . $password_length . ']|matches[mem_password]',
		// );
		$config['mem_nickname'] = array(
			'field' => 'mem_nickname',
			'label' => '닉네임',
			'rules' => 'trim|required|min_length[2]|max_length[20]|callback__mem_nickname_check',
		);
		// $config['mem_email'] = array(
		// 	'field' => 'mem_email',
		// 	'label' => '이메일',
		// 	'rules' => 'trim|required|valid_email|max_length[50]|is_unique[member.mem_email.mem_id.' . $mem_id . ']|callback__mem_email_check',
		// );

		$this->load->library(array('form_validation'));

		$this->form_validation->set_rules($config);
		/**
		 * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
		 * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
		 */
		if ($this->form_validation->run() === false) {

			// 이벤트가 존재하면 실행합니다
			$view['view']['event']['formrunfalse'] = Events::trigger('formrunfalse', $eventname);

			/**
			 * 레이아웃을 정의합니다
			 */
			$page_title = $this->cbconfig->item('site_meta_title_membermodify');
			$meta_description = $this->cbconfig->item('site_meta_description_membermodify');
			$meta_keywords = $this->cbconfig->item('site_meta_keywords_membermodify');
			$meta_author = $this->cbconfig->item('site_meta_author_membermodify');
			$page_name = $this->cbconfig->item('site_page_name_membermodify');

			$layoutconfig = array(
				'path' => 'mypage',
				'layout' => 'layout',
				'skin' => 'member_defaultinfo',
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

		} else {
			/**
			 * 유효성 검사를 통과한 경우입니다.
			 * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
			 */

			// 이벤트가 존재하면 실행합니다
			$view['view']['event']['formruntrue'] = Events::trigger('formruntrue', $eventname);

			$updatedata = array();
			$metadata = array();

			$updatedata['mem_userid'] = $this->input->post('mem_userid');
			$updatedata['mem_email'] = $this->input->post('mem_email');
			if ($this->member->item('mem_email') !== $this->input->post('mem_email')) {
				$updatedata['mem_email_cert'] = 0;
				$metadata['meta_email_cert_datetime'] = '';
			}

			if ($this->member->item('mem_nickname') !== $this->input->post('mem_nickname')) {
				$updatedata['mem_nickname'] = $this->input->post('mem_nickname');
				$metadata['meta_nickname_datetime'] = cdate('Y-m-d H:i:s');

				$upnick = array(
					'mni_end_datetime' => cdate('Y-m-d H:i:s'),
				);
				$nickwhere = array(
					'mem_id' => $mem_id,
					'mni_nickname' => $this->member->item('mem_nickname'),
				);
				$this->Member_nickname_model->update('', $upnick, $nickwhere);

				$nickinsert = array(
					'mem_id' => $mem_id,
					'mni_nickname' => $this->input->post('mem_nickname'),
					'mni_start_datetime' => cdate('Y-m-d H:i:s'),
				);
				$this->Member_nickname_model->insert($nickinsert);
			}
			$updatedata['mem_password'] = password_hash($this->input->post('mem_password'), PASSWORD_BCRYPT);

			$this->Member_model->update($mem_id, $updatedata);
			$this->Member_meta_model->save($mem_id, $metadata);

			if ($this->cbconfig->item('use_register_email_auth')
				&& $this->member->item('mem_email') !== $this->input->post('mem_email')) {

				$vericode = array('$', '/', '.');
				$verificationcode = str_replace(
					$vericode,
					'',
					password_hash($mem_id . '-' . $this->input->post('mem_email') . '-' . random_string('alnum', 10), PASSWORD_BCRYPT)
				);

				$beforeauthdata = array(
					'mem_id' => $mem_id,
					'mae_type' => 2,
				);
				$this->Member_auth_email_model->delete_where($beforeauthdata);
				$authdata = array(
					'mem_id' => $mem_id,
					'mae_key' => $verificationcode,
					'mae_type' => 2,
					'mae_generate_datetime' => cdate('Y-m-d H:i:s'),
				);
				$this->Member_auth_email_model->insert($authdata);

				$verify_url = site_url('verify/confirmemail?user=' . $this->input->post('mem_userid') . '&code=' . $verificationcode);

				$searchconfig = array(
					'{홈페이지명}',
					'{회사명}',
					'{홈페이지주소}',
					'{회원아이디}',
					'{회원닉네임}',
					'{회원실명}',
					'{회원이메일}',
					'{변경전이메일}',
					'{메일수신여부}',
					'{쪽지수신여부}',
					'{문자수신여부}',
					'{회원아이피}',
					'{메일인증주소}',
				);
				$receive_email = $this->member->item('mem_receive_email') ? '동의' : '거부';
				$receive_note = $this->member->item('mem_use_note') ? '동의' : '거부';
				$receive_sms = $this->member->item('mem_receive_sms') ? '동의' : '거부';
				$replaceconfig = array(
					$this->cbconfig->item('site_title'),
					$this->cbconfig->item('company_name'),
					site_url(),
					$this->member->item('mem_userid'),
					$this->member->item('mem_nickname'),
					$this->member->item('mem_username'),
					$this->input->post('mem_email'),
					$this->member->item('mem_email'),
					$receive_email,
					$receive_note,
					$receive_sms,
					$this->input->ip_address(),
					$verify_url,
				);

				$replaceconfig_escape = array(
					html_escape($this->cbconfig->item('site_title')),
					html_escape($this->cbconfig->item('company_name')),
					site_url(),
					$this->member->item('mem_userid'),
					html_escape($this->member->item('mem_nickname')),
					html_escape($this->member->item('mem_username')),
					html_escape($this->input->post('mem_email')),
					html_escape($this->member->item('mem_email')),
					$receive_email,
					$receive_note,
					$receive_sms,
					$this->input->ip_address(),
					$verify_url,
				);

				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_email_changeemail_user_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_email_changeemail_user_content')
				);

				$this->email->clear(true);
				$this->email->from($this->cbconfig->item('webmaster_email'), $this->cbconfig->item('webmaster_name'));
				$this->email->to($this->input->post('mem_email'));
				$this->email->subject($title);
				$this->email->message($content);
				$this->email->send();

				$view['view']['result_message'] = $this->input->post('mem_email') . '로 인증메일이 발송되었습니다. <br />발송된 인증메일을 확인하신 후에 사이트 이용이 가능합니다';


			} else {
				$view['view']['result_message'] = '회원정보가 변경되었습니다. <br />감사합니다';
			}

			// 이벤트가 존재하면 실행합니다
			$view['view']['event']['before_result_layout'] = Events::trigger('before_result_layout', $eventname);

			$page_title = $this->cbconfig->item('site_meta_title_membermodify');
			$meta_description = $this->cbconfig->item('site_meta_description_membermodify');
			$meta_keywords = $this->cbconfig->item('site_meta_keywords_membermodify');
			$meta_author = $this->cbconfig->item('site_meta_author_membermodify');
			$page_name = $this->cbconfig->item('site_page_name_membermodify');

			$layoutconfig = array(
				'path' => 'mypage',
				'layout' => 'layout',
				'skin' => 'member_modify_result',
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
	}


	/**
	 * 회원정보 수정중 패스워드 변경 페이지입니다
	 */
	public function password_modify_post($nochange=0)
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_membermodify_password_modify';
		$this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();

		$mem_id = (int) $this->member->item('mem_id');

		// if ( ! $this->session->userdata('membermodify')) {
		// 	redirect('membermodify');
		// }

		$view = array();
		$view['view'] = array();

		// 이벤트가 존재하면 실행합니다
		$view['view']['event']['before'] = Events::trigger('before', $eventname);

		if($nochange){
			$metadata = array(
				'meta_change_pw_datetime' => cdate('Y-m-d H:i:s'),
			);
			$this->Member_meta_model->save($mem_id, $metadata);

			return $this->response(array('msg' => '처리 되었습니다'), 201);
		}
		/**
		 * Validation 라이브러리를 가져옵니다
		 */
		$this->load->library('form_validation');

		 if ( ! function_exists('password_hash')) {
			$this->load->helper('password');
		}

		/**
		 * 전송된 데이터의 유효성을 체크합니다
		 */

		$password_length = $this->cbconfig->item('password_length');
		$view['view']['password_length'] = $password_length;

		$config = array(
			array(
				'field' => 'cur_password',
				'label' => '현재패스워드',
				'rules' => 'trim|required|callback__cur_password_check',
			),
			array(
				'field' => 'new_password',
				'label' => '새로운패스워드',
				'rules' => 'trim|required|min_length[' . $password_length . ']|callback__mem_password_check',
			),
			array(
				'field' => 'new_password_re',
				'label' => '새로운패스워드',
				'rules' => 'trim|required|min_length[' . $password_length . ']|matches[new_password]',
			),
		);
		$this->form_validation->set_rules($config);

		/**
		 * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
		 * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
		 */
		if ($this->form_validation->run() === false) {

			

			// $password_description = '비밀번호는 ' . $password_length . '자리 이상이어야 ';
			// if ($this->cbconfig->item('password_uppercase_length')
			// 	OR $this->cbconfig->item('password_numbers_length')
			// 	OR $this->cbconfig->item('password_specialchars_length')) {
			// 	$password_description .= '하며 ';
			// 	if ($this->cbconfig->item('password_uppercase_length')) {
			// 		$password_description .= ', ' . $this->cbconfig->item('password_uppercase_length') . '개의 대문자';
			// 	}
			// 	if ($this->cbconfig->item('password_numbers_length')) {
			// 		$password_description .= ', ' . $this->cbconfig->item('password_numbers_length') . '개의 숫자';
			// 	}
			// 	if ($this->cbconfig->item('password_specialchars_length')) {
			// 		$password_description .= ', ' . $this->cbconfig->item('password_specialchars_length') . '개의 특수문자';
			// 	}
			// 	$password_description .= '를 포함해야 ';
			// }
			// $password_description .= '합니다';

			$view['msg'] = validation_errors();
			log_message('error', 'msg:'. validation_errors() .' pointer:'.current_url());
			return $this->response(array('msg' => $view['msg']), 400);

		} else {

			// 이벤트가 존재하면 실행합니다
			$view['view']['event']['formruntrue'] = Events::trigger('formruntrue', $eventname);

			$hash = password_hash($this->input->post('new_password'), PASSWORD_BCRYPT);

			$updatedata = array(
				'mem_password' => $hash,
			);
			$this->Member_model->update($mem_id, $updatedata);
			$metadata = array(
				'meta_change_pw_datetime' => cdate('Y-m-d H:i:s'),
			);
			$this->Member_meta_model->save($mem_id, $metadata);


			$emailsendlistadmin = array();
			$notesendlistadmin = array();
			$smssendlistadmin = array();
			$emailsendlistuser = array();
			$notesendlistuser = array();
			$smssendlistuser = array();

			$superadminlist = '';
			if ($this->cbconfig->item('send_email_changepw_admin')
				OR $this->cbconfig->item('send_note_changepw_admin')
				OR $this->cbconfig->item('send_sms_changepw_admin')) {
				$mselect = 'mem_id, mem_email, mem_nickname, mem_phone';
				$superadminlist = $this->Member_model->get_superadmin_list($mselect);
			}
			if ($this->cbconfig->item('send_email_changepw_admin') && $superadminlist) {
				foreach ($superadminlist as $key => $value) {
					$emailsendlistadmin[$value['mem_id']] = $value;
				}
			}
			if (($this->cbconfig->item('send_email_changepw_user') && $this->member->item('mem_receive_email'))
				OR $this->cbconfig->item('send_email_changepw_alluser')) {
				$emailsendlistuser['mem_email'] = $this->member->item('mem_email');
			}
			if ($this->cbconfig->item('send_note_changepw_admin') && $superadminlist) {
				foreach ($superadminlist as $key => $value) {
					$notesendlistadmin[$value['mem_id']] = $value;
				}
			}
			if ($this->cbconfig->item('send_note_changepw_user')
				&& $this->member->item('mem_use_note')) {
				$notesendlistuser['mem_id'] = $mem_id;
			}
			if ($this->cbconfig->item('send_sms_changepw_admin') && $superadminlist) {
				foreach ($superadminlist as $key => $value) {
					$smssendlistadmin[$value['mem_id']] = $value;
				}
			}
			if (($this->cbconfig->item('send_sms_changepw_user') && $this->member->item('mem_receive_sms'))
				OR $this->cbconfig->item('send_sms_changepw_alluser')) {
				if ($this->member->item('mem_phone')) {
					$smssendlistuser['mem_id'] = $mem_id;
					$smssendlistuser['mem_nickname'] = $this->member->item('mem_nickname');
					$smssendlistuser['mem_phone'] = $this->member->item('mem_phone');
				}
			}

			$searchconfig = array(
				'{홈페이지명}',
				'{회사명}',
				'{홈페이지주소}',
				'{회원아이디}',
				'{회원닉네임}',
				'{회원실명}',
				'{회원이메일}',
				'{메일수신여부}',
				'{쪽지수신여부}',
				'{문자수신여부}',
				'{회원아이피}',
			);
			$receive_email = $this->member->item('mem_receive_email') ? '동의' : '거부';
			$receive_note = $this->member->item('mem_use_note') ? '동의' : '거부';
			$receive_sms = $this->member->item('mem_receive_sms') ? '동의' : '거부';
			$replaceconfig = array(
				$this->cbconfig->item('site_title'),
				$this->cbconfig->item('company_name'),
				site_url(),
				$this->member->item('mem_userid'),
				$this->member->item('mem_nickname'),
				$this->member->item('mem_username'),
				$this->member->item('mem_email'),
				$receive_email,
				$receive_note,
				$receive_sms,
				$this->input->ip_address(),
			);
			$replaceconfig_escape = array(
				html_escape($this->cbconfig->item('site_title')),
				html_escape($this->cbconfig->item('company_name')),
				site_url(),
				html_escape($this->member->item('mem_userid')),
				html_escape($this->member->item('mem_nickname')),
				html_escape($this->member->item('mem_username')),
				html_escape($this->member->item('mem_email')),
				$receive_email,
				$receive_note,
				$receive_sms,
				$this->input->ip_address(),
			);
			if ($emailsendlistadmin) {
				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_email_changepw_admin_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_email_changepw_admin_content')
				);
				foreach ($emailsendlistadmin as $akey => $aval) {
					$this->email->clear(true);
					$this->email->from($this->cbconfig->item('webmaster_email'), $this->cbconfig->item('webmaster_name'));
					$this->email->to(element('mem_email', $aval));
					$this->email->subject($title);
					$this->email->message($content);
					$this->email->send();
				}
			}
			if ($emailsendlistuser) {
				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_email_changepw_user_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_email_changepw_user_content')
				);
				$this->email->clear(true);
				$this->email->from($this->cbconfig->item('webmaster_email'), $this->cbconfig->item('webmaster_name'));
				$this->email->to(element('mem_email', $emailsendlistuser));
				$this->email->subject($title);
				$this->email->message($content);
				$this->email->send();
			}
			if ($notesendlistadmin) {
				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_note_changepw_admin_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_note_changepw_admin_content')
				);
				foreach ($notesendlistadmin as $akey => $aval) {
					$note_result = $this->notelib->send_note(
						$sender = 0,
						$receiver = element('mem_id', $aval),
						$title,
						$content,
						1
					);
				}
			}
			if ($notesendlistuser && element('mem_id', $notesendlistuser)) {
				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_note_changepw_user_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_note_changepw_user_content')
				);
				$note_result = $this->notelib->send_note(
					$sender = 0,
					$receiver = element('mem_id', $notesendlistuser),
					$title,
					$content,
					1
				);
			}
			if ($smssendlistadmin) {
				if (file_exists(APPPATH . 'libraries/Smslib.php')) {
					$this->load->library(array('smslib'));
					$content = str_replace(
						$searchconfig,
						$replaceconfig,
						$this->cbconfig->item('send_sms_changepw_admin_content')
					);
					$sender = array(
						'phone' => $this->cbconfig->item('sms_admin_phone'),
					);
					$receiver = array();
					foreach ($smssendlistadmin as $akey => $aval) {
						$receiver[] = array(
							'mem_id' => element('mem_id', $aval),
							'name' => element('mem_nickname', $aval),
							'phone' => element('mem_phone', $aval),
						);
					}
					$smsresult = $this->smslib->send($receiver, $sender, $content, $date = '', '회원패스워드변경알림');
				}
			}
			if ($smssendlistuser) {
				if (file_exists(APPPATH . 'libraries/Smslib.php')) {
					$this->load->library(array('smslib'));
					$content = str_replace(
						$searchconfig,
						$replaceconfig,
						$this->cbconfig->item('send_sms_changepw_user_content')
					);
					$sender = array(
						'phone' => $this->cbconfig->item('sms_admin_phone'),
					);
					$receiver = array();
					$receiver[] = $smssendlistuser;
					$smsresult = $this->smslib->send($receiver, $sender, $content, $date = '', '회원패스워드변경알림');
				}
			}


			$view['view']['result_message'] = '회원님의 패스워드가 변경되었습니다';

			

			return $this->response(array('msg' => $view['view']['result_message']), 201);
		}
	}


	/**
	 * 회원탈퇴 페이지입니다
	 */
	
	public function memberleave_get()
	{

		// 이벤트 라이브러리를 로딩합니다
		// $eventname = 'event_membermodify_memberleave';
		// $this->load->event($eventname);

		/**
		 * 로그인이 필요한 페이지입니다
		 */
		required_user_login();
		$this->load->model(array('Cmall_wishlist_model','Cmall_storewishlist_model','Cmall_review_model'));
		$mem_id = (int) $this->member->item('mem_id');
		
		$view = array();
		$view['view'] = array();
		
		$page = 1;
		$per_page = 999; 
		
        $offset = ($page - 1) * $per_page;
        
        
        if(empty($pet_id)) $pet_id = $this->member->item('pet_id');

        $config = array(
            'mem_id' => $mem_id,
            'pet_id' => $pet_id,            
            'sort' => $this->input->get('sort'),
        );

        $view['view']['data']['ai_recom'] = $this->_itemairecomlists($config);  
        
        
		$where = array();
		$where['cmall_wishlist.mem_id'] = $this->member->item('mem_id');
		// $where['cit_status'] = 1;
		$result = $this->Cmall_wishlist_model
		    ->get_list($per_page, $offset, $where);
		$list_num = $result['total_rows'] - ($page - 1) * $per_page;
		if (element('list', $result)) {
		    foreach (element('list', $result) as $key => $val) {
		        $result['list'][$key] = $this->denguruapi->get_cit_info(element('cit_id',$val),$result['list'][$key]);

		        $board_crawl = $this->denguruapi->get_all_crawl(element('brd_id',$result['list'][$key]));

		        // $result['list'][$key]['brd_register_url'] = element('brd_register_url',$board_crawl);    
		        // $result['list'][$key]['brd_order_url'] = element('brd_order_url',$board_crawl);

		        
		        $result['list'][$key]['num'] = $list_num--;
		    }
		}

		$view['view']['data']['cit_wish'] = $result;

		$where = array();
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
                
                
                
                
                // $result['list'][$key]['num'] = $list_num--;
            }
        }
        $view['view']['data']['brd_wish'] = $result;

        $where = array();
        // $where['cre_status'] = 1;
        // if($cit_id) $where['cit_id'] = $cit_id;

        $where = array(
			'cmall_review.mem_id' => $mem_id,
			'cre_status' => 1,
		);

        

        // $field = array(
        //     'cmall_review' => array('cre_id','cit_id','cre_title','cre_content','cre_content_html_type','mem_id','cre_score','cre_datetime','cre_like','cre_update_datetime'),
        // );
        
        // $select = get_selected($field);
        
        // $this->Cmall_review_model->_select = $select;


        $result = $this->Cmall_review_model
            ->get_list($per_page, $offset, $where);
        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {
                
                

                

                
                

                $result['list'][$key] = $this->denguruapi->get_cit_info(element('cit_id', $val),$result['list'][$key]);                   
                // $result['list'][$key] = $this->board->get_default_info($result['list'][$key]['brd_id'],$result['list'][$key]);                   
                $result['list'][$key] = $this->denguruapi->convert_review_info($result['list'][$key]);                   



                $result['list'][$key]['num'] = $list_num--;
                
            }
        }
        $view['view']['data']['review'] = $result;
		
		

		
		
		
		return $this->response($view['view'], parent::HTTP_OK);
	
	}

	public function memberleave_put()
	{
		// 이벤트 라이브러리를 로딩합니다
		// $eventname = 'event_membermodify_memberleave';
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

		$this->load->library(array('form_validation'));
		$login_fail = false;
		$valid_fail = false;

		$password_length = $this->cbconfig->item('password_length');
		$view['view']['password_length'] = $password_length;
		/**
		 * 전송된 데이터의 유효성을 체크합니다
		 */
		$config = array(
			array(
				'field' => 'mem_password',
				'label' => '패스워드',
				'rules' => 'trim|required|min_length[' . $password_length . ']|callback__cur_password_check',
			),
		);

		
		
		$this->form_validation->set_rules($config);
		/**
		 * 유효성 검사를 하지 않는 경우, 또는 유효성 검사에 실패한 경우입니다.
		 * 즉 글쓰기나 수정 페이지를 보고 있는 경우입니다
		 */
		// if ($this->form_validation->run() === false) {
		if (false) {
			// 이벤트가 존재하면 실행합니다
			// $view['view']['event']['formrunfalse'] = Events::trigger('formrunfalse', $eventname);

			/**
			 * 레이아웃을 정의합니다
			 */
			$view['msg'] = validation_errors();
            
            

			log_message('error', 'msg:'.validation_errors() .' pointer:'.current_url());
			

            $view['http_status_codes'] = 400;

            return $this->response(array('msg' => $view['msg']), 400);
            

		} else {
			
			/**
			 * 유효성 검사를 통과한 경우입니다.
			 * 즉 데이터의 insert 나 update 의 process 처리가 필요한 상황입니다
			 */

			// 이벤트가 존재하면 실행합니다
			// $view['view']['event']['formruntrue'] = Events::trigger('formruntrue', $eventname);

			$emailsendlistadmin = array();
			$notesendlistadmin = array();
			$smssendlistadmin = array();
			$emailsendlistuser = array();
			$notesendlistuser = array();
			$smssendlistuser = array();

			$superadminlist = '';
			if ($this->cbconfig->item('send_email_memberleave_admin')
				OR $this->cbconfig->item('send_note_memberleave_admin')
				OR $this->cbconfig->item('send_sms_memberleave_admin')) {
				$mselect = 'mem_id, mem_email, mem_nickname, mem_phone';
				$superadminlist = $this->Member_model->get($mselect);
			}

			if ($this->cbconfig->item('send_email_memberleave_admin') && $superadminlist) {
				foreach ($superadminlist as $key => $value) {
					$emailsendlistadmin[$value['mem_id']] = $value;
				}
			}
			if (($this->cbconfig->item('send_email_memberleave_user') && $this->member->item('mem_receive_email'))
				OR $this->cbconfig->item('send_email_memberleave_alluser')) {
				$emailsendlistuser['mem_email'] = $this->member->item('mem_email');
			}
			if ($this->cbconfig->item('send_note_memberleave_admin') && $superadminlist) {
				foreach ($superadminlist as $key => $value) {
					$notesendlistadmin[$value['mem_id']] = $value;
				}
			}
			if ($this->cbconfig->item('send_sms_memberleave_admin') && $superadminlist) {
				foreach ($superadminlist as $key => $value) {
					$smssendlistadmin[$value['mem_id']] = $value;
				}
			}
			if (($this->cbconfig->item('send_sms_memberleave_user') && $this->member->item('mem_receive_sms'))
				OR $this->cbconfig->item('send_sms_memberleave_alluser')) {
				if ($this->member->item('mem_phone')) {
					$smssendlistuser['mem_id'] = $mem_id;
					$smssendlistuser['mem_nickname'] = $this->member->item('mem_nickname');
					$smssendlistuser['mem_phone'] = $this->member->item('mem_phone');
				}
			}

			$searchconfig = array(
				'{홈페이지명}',
				'{회사명}',
				'{홈페이지주소}',
				'{회원아이디}',
				'{회원닉네임}',
				'{회원실명}',
				'{회원이메일}',
				'{메일수신여부}',
				'{쪽지수신여부}',
				'{문자수신여부}',
				'{회원아이피}',
			);
			$receive_email = $this->member->item('mem_receive_email') ? '동의' : '거부';
			$receive_note = $this->member->item('mem_use_note') ? '동의' : '거부';
			$receive_sms = $this->member->item('mem_receive_sms') ? '동의' : '거부';
			$replaceconfig = array(
				$this->cbconfig->item('site_title'),
				$this->cbconfig->item('company_name'),
				site_url(),
				$this->member->item('mem_userid'),
				$this->member->item('mem_nickname'),
				$this->member->item('mem_username'),
				$this->member->item('mem_email'),
				$receive_email,
				$receive_note,
				$receive_sms,
				$this->input->ip_address(),
			);
			$replaceconfig_escape = array(
				html_escape($this->cbconfig->item('site_title')),
				html_escape($this->cbconfig->item('company_name')),
				site_url(),
				html_escape($this->member->item('mem_userid')),
				html_escape($this->member->item('mem_nickname')),
				html_escape($this->member->item('mem_username')),
				html_escape($this->member->item('mem_email')),
				$receive_email,
				$receive_note,
				$receive_sms,
				$this->input->ip_address(),
			);
			if ($emailsendlistadmin) {
				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_email_memberleave_admin_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_email_memberleave_admin_content')
				);
				foreach ($emailsendlistadmin as $akey => $aval) {
					$this->email->clear(true);
					$this->email->from($this->cbconfig->item('webmaster_email'), $this->cbconfig->item('webmaster_name'));
					$this->email->to(element('mem_email', $aval));
					$this->email->subject($title);
					$this->email->message($content);
					$this->email->send();
				}
			}
			if ($emailsendlistuser) {
				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_email_memberleave_user_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_email_memberleave_user_content')
				);
				$this->email->clear(true);
				$this->email->from($this->cbconfig->item('webmaster_email'), $this->cbconfig->item('webmaster_name'));
				$this->email->to(element('mem_email', $emailsendlistuser));
				$this->email->subject($title);
				$this->email->message($content);
				$this->email->send();
			}
			if ($notesendlistadmin) {
				$title = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->cbconfig->item('send_note_memberleave_admin_title')
				);
				$content = str_replace(
					$searchconfig,
					$replaceconfig_escape,
					$this->cbconfig->item('send_note_memberleave_admin_content')
				);
				foreach ($notesendlistadmin as $akey => $aval) {
					$note_result = $this->notelib->send_note(
						$sender = 0,
						$receiver = element('mem_id', $aval),
						$title,
						$content,
						1
					);
				}
			}
			if ($smssendlistadmin) {
				if (file_exists(APPPATH . 'libraries/Smslib.php')) {
					$this->load->library(array('smslib'));
					$content = str_replace(
						$searchconfig,
						$replaceconfig,
						$this->cbconfig->item('send_sms_memberleave_admin_content')
					);
					$sender = array(
						'phone' => $this->cbconfig->item('sms_admin_phone'),
					);
					$receiver = array();
					foreach ($smssendlistadmin as $akey => $aval) {
						$receiver[] = array(
							'mem_id' => element('mem_id', $aval),
							'name' => element('mem_nickname', $aval),
							'phone' => element('mem_phone', $aval),
						);
					}
					$smsresult = $this->smslib->send($receiver, $sender, $content, $date = '', '회원탈퇴알림');
				}
			}
			if ($smssendlistuser) {
				if (file_exists(APPPATH . 'libraries/Smslib.php')) {
					$this->load->library(array('smslib'));
					$content = str_replace(
						$searchconfig,
						$replaceconfig,
						$this->cbconfig->item('send_sms_memberleave_user_content')
					);
					$sender = array(
						'phone' => $this->cbconfig->item('sms_admin_phone'),
					);
					$receiver = array();
					$receiver[] = $smssendlistuser;
					$smsresult = $this->smslib->send($receiver, $sender, $content, $date = '', '회원탈퇴알림');
				}
			}

			$this->member->delete_member($mem_id);
			// $this->session->sess_destroy();

			// 이벤트가 존재하면 실행합니다
			// $view['view']['event']['before_layout'] = Events::trigger('before_layout', $eventname);

			/**
			 * 레이아웃을 정의합니다
			 */
			return $this->response(array('msg' => '탈퇴 되었습니다 감사합니다.'), 201);
		}
	}


	/**
	 * 회원가입시 회원아이디를 체크하는 함수입니다
	 */
	public function _mem_userid_check($str)
	{
		if (preg_match("/[\,]?{$str}/i", $this->cbconfig->item('denied_userid_list'))) {
			$this->form_validation->set_message(
				'_mem_userid_check',
				$str . ' 은(는) 예약어로 사용하실 수 없는 회원아이디입니다'
			);
			return false;
		}
		return true;
	}


	/**
	 * 닉네임체크 함수입니다
	 */
	public function _mem_nickname_check($str)
	{
		if ($str === $this->member->item('mem_nickname')) {
			return true;
		}

		$this->load->helper('chkstring');
		if (chkstring($str, _HANGUL_ + _ALPHABETIC_ + _NUMERIC_) === false) {
			$this->form_validation->set_message(
				'_mem_nickname_check',
				'닉네임은 공백없이 한글, 영문, 숫자만 입력 가능합니다'
			);
			return false;
		}

		if (preg_match("/[\,]?{$str}/i", $this->cbconfig->item('denied_nickname_list'))) {
			$this->form_validation->set_message(
				'_mem_nickname_check',
				$str . ' 은(는) 예약어로 사용하실 수 없는 닉네임입니다'
			);
			return false;
		}
		// $countwhere = array(
		// 	'mem_nickname' => $str,
		// );
		// $row = $this->Member_model->count_by($countwhere);

		// if ($row > 0) {
		// 	$this->form_validation->set_message(
		// 		'_mem_nickname_check',
		// 		$str . ' 는 이미 다른 회원이 사용하고 있는 닉네임입니다'
		// 	);
		// 	return false;
		// }

		// $countwhere = array(
		// 	'mni_nickname' => $str,
		// );
		// $row = $this->Member_nickname_model->count_by($countwhere);

		// if ($row > 0) {
		// 	$this->form_validation->set_message(
		// 		'_mem_nickname_check',
		// 		$str . ' 는 이미 다른 회원이 사용하고 있는 닉네임입니다'
		// 	);
		// 	return false;
		// }
		return true;
	}


	/**
	 * 이메일 체크 함수입니다
	 */
	public function _mem_email_check($str)
	{	

		if(empty($str)) {

           $this->form_validation->set_message(
				'_mem_email_check',
				'이메일을 입력해 주세요.'
			);
           return false;
        }

		list($emailid, $emaildomain) = explode('@', $str);
		$denied_list = explode(',', $this->cbconfig->item('denied_email_list'));
		$emaildomain = trim($emaildomain);
		$denied_list = array_map('trim', $denied_list);
		if (in_array($emaildomain, $denied_list)) {
			$this->form_validation->set_message(
				'_mem_email_check',
				$emaildomain . ' 은(는) 사용하실 수 없는 이메일입니다'
			);
			return false;
		}
		return true;
	}


	/**
	 * 현재 패스워드가 맞는지 체크합니다
	 */
	public function _cur_password_check($str)
	{
		 if ( ! function_exists('password_hash')) {
			$this->load->helper('password');
		}


		if ( ! $this->member->item('mem_id') OR ! $this->member->item('mem_password')) {
			$this->form_validation->set_message(
				'_cur_password_check',
				'비밀번호를 정확하게 입력 해 주세요'
			);
			return false;
		} elseif ( ! password_verify($str, $this->member->item('mem_password'))) {
			$this->form_validation->set_message(
				'_cur_password_check',
				'현재 비밀번호를 정확하게 입력 해 주세요'
			);
			return false;
		}
		return true;
	}


	/**
	 * 새로운 패스워드가 환경설정에 정한 글자수를 채웠는지를 체크합니다
	 */
	public function _mem_password_check($str)
	{
		$uppercase = $this->cbconfig->item('password_uppercase_length');
		$number = $this->cbconfig->item('password_numbers_length');
		$specialchar = $this->cbconfig->item('password_specialchars_length');

		$this->load->helper('chkstring');
		$str_uc = count_uppercase($str);
		$str_num = count_numbers($str);
		$str_spc = count_specialchars($str);

		if ($str_uc < $uppercase OR $str_num < $number OR $str_spc < $specialchar) {

			$description = '비밀번호는 ';
			if ($str_uc < $uppercase) {
				$description .= ' ' . $uppercase . '개 이상의 대문자';
			}
			if ($str_num < $number) {
				$description .= ' ' . $number . '개 이상의 숫자';
			}
			if ($str_spc < $specialchar) {
				$description .= ' ' . $specialchar . '개 이상의 특수문자';
			}
			$description .= '를 포함해야 합니다';

			$this->form_validation->set_message(
				'_mem_password_check',
				$description
			);
			return false;
		}
		return true;
	}

	public function ajax_smssend_post()
	{
		// 이벤트 라이브러리를 로딩합니다
		$eventname = 'event_register_ajax_nickname_check';
		$this->load->event($eventname);

		$result = array();
		// $this->output->set_content_type('application/json');

		// 이벤트가 존재하면 실행합니다
		Events::trigger('before', $eventname);

		$mem_phone = trim($this->input->post('mem_phone'));
		if (empty($mem_phone)) {
			$result = array(
				'result' => 'error',
				'msg' => '잘못된 휴대폰 번호입니다.',
			);
			return $this->response($result, 200);
		}

		$this->load->library('form_validation');

		$mem_phone = $this->form_validation->valid_mobile($this->input->post('mem_phone'));

		if (empty($mem_phone)) {
		    $result = array(
				'result' => 'error',
				'msg' => '잘못된 휴대폰 번호입니다.',
			);
			return $this->response($result, 200);
		}

		$this->load->model( 'Sms_send_history_model');

		$timestamp = strtotime("-1 hours");

		$sendwhere= array(
		    // 'post_id' => $this->input->post('post_id'),
		    'ssh_phone' => str_replace("-","",$mem_phone),
		    'ssh_success' => 1,
		    'ssh_datetime >=' => date("Y-m-d H:i:s", $timestamp),
		);

		$cnt = 0 ;
		$cnt = $this->Sms_send_history_model->count_by($sendwhere);

		if($cnt > 5){

			$result = array(
				'result' => 'error',
				'msg' => '인증 횟수가 초과 되었습니다 한시간 이후 다시 시도해 주세요.',
			);
			// return $this->response($result, 200);

		    
		    
		    // exit(json_encode($result));
		}

		$ssc_key = rand(111111,999999);
		

		$sender = array(
		    'phone' => $this->cbconfig->item('sms_admin_phone'),
		    // 'post_id' => $this->input->post('post_id'),
		    // 'multi_code' => $this->input->post('multi_code'),
		    'ssc_key' => $ssc_key,
		);
		$receiver = array();
		
		$content= "인증번호 (".$ssc_key.") 입력하시면 정상처리 됩니다.";

		$receiver['phone'] = $mem_phone;
		$receiver['mem_id'] = 1;
		$receiver['name'] = $this->input->post('mem_nickname',null,'익명사용자');
		$this->load->library('smslib');
		$smsresult = $this->smslib->send($receiver, $sender, $content, $date = '',$receiver['name'].'에게 전송');

		if(empty($smsresult)){

			$result = array(
				'result' => 'error',
				'msg' => 'sms 전송시 알 수 없는 오류가 발생하였습니다..',
			);
			return $this->response($result, 200);
		}

		// if($smsresult['result'] ==='success')
			return $this->response($smsresult, 200);
		// else 
		// 	return $this->response($smsresult, 200);
		

		// if ($this->member->item('mem_nickname')
		// 	&& $this->member->item('mem_nickname') === $nickname) {
		// 	$result = array(
		// 		'result' => 'success',
		// 		'msg' => '사용 가능한 닉네임입니다',
		// 	);
		// 	return $this->response(array('msg' => $result['msg']), 200);
		// }

		// $where = array(
		// 	'mem_nickname' => $nickname,
		// );
		// $count = $this->Member_model->count_by($where);
		// if ($count > 0) {
		// 	$result = array(
		// 		'result' => 'error',
		// 		'msg' => '이미 사용중인 닉네임입니다',
		// 	);
		// 	return $this->response(array('msg' => $result['msg']), 200);
		// }

		// if ($this->_mem_nickname_check($nickname) === false) {
		// 	$result = array(
		// 		'result' => 'error',
		// 		'msg' => '이미 사용중인 닉네임입니다',
		// 	);
		// 	return $this->response(array('msg' => $result['msg']), 200);
		// }

		// $result = array(
		// 	'result' => 'success',
		// 	'msg' => '사용 가능한 닉네임입니다',
		// );
		// return $this->response(array('msg' => $result['msg']), 200);
	}


	

	public function _mem_smsmap_check($str)
	{

		$this->load->library('form_validation');

		$mem_phone = $this->form_validation->valid_mobile($str);
		
		

		if ($mem_phone === $this->member->item('mem_phone')) {
			return true;
		}

	   

       $cfc_num = $this->input->post('cfc_num');

       
       
      	
       
       
       
       
       if(empty($mem_phone)) {

           $this->form_validation->set_message(
				'_mem_smsmap_check',
				'잘못된 휴대폰 번호입니다.'
			);
           return false;
       }

       if(empty($cfc_num)) {
           
			$this->form_validation->set_message(
				'_mem_smsmap_check',
				'인증 번호를 입력해 주세요.'
			);
			return false;
       }

       $this->load->model( 'Sms_send_history_model');

       $timestamp = strtotime("-1 hours");

       $sendwhere= array(
           // 'post_id' => $this->input->post('post_id'),
           'ssh_phone' => str_replace("-","",$mem_phone),
           'ssh_success' => 1,
           'ssh_datetime >=' => date("Y-m-d H:i:s", $timestamp),
           'ssh_key' => $cfc_num,
       );

       $cnt = 0 ;
       $cnt = $this->Sms_send_history_model->count_by($sendwhere);
       
       if($cnt < 1){

           $this->form_validation->set_message(
				'_mem_smsmap_check',
				'인증 번호가 맞지 앖습니다. 다시 확인해 주세요 '
			);
           return false;
       }

       

       
       
     	return true;  
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

        
        // $result['pet_info'] = $pet_info;
        $view['view'] = $result;
        
        return $view['view'];
        
    }
}
