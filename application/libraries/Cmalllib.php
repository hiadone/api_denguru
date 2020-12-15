<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cmalllib class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * cmall table 을 관리하는 class 입니다.
 */
class Cmalllib extends CI_Controller
{

	private $CI;

	public $paymethodtype = array(
			'point' => '포인트결제',
			'bank' => '무통장입금',
			'card' => '신용카드',
			'phone' => '핸드폰결제',
			'realtime' => '실시간계좌이체',
			'vbank' => '가상계좌',
			'service' => '서비스',
		);

	function __construct()
	{
		$this->CI = & get_instance();
		$this->CI->load->library(array('email', 'notelib'));
	}


	/**
	 * cmall 기능을 사용하는지 체크합니다.
	 */
	public function use_cmall()
	{
		$use = $this->CI->cbconfig->item('use_cmall');
		return $use;
	}


	public function get_all_category()
	{
		$this->CI->load->model('Cmall_category_model');
		$result = $this->CI->Cmall_category_model->get_all_category();
		return $result;
	}

	public function get_paymethodtype($method){

		$paymethodtype = $this->paymethodtype;

		if( isset( $paymethodtype[$method] ) ){
			return $paymethodtype[$method];
		}

		return $method;
	}

	public function get_nav_category($category_id = '')
	{
		if (empty($category_id)) {
			return;
		}

		$this->CI->load->model('Cmall_category_model');

		$my_category = $category_id;

		$result = array();
		while ($my_category) {
			$result[] = $data = $this->CI->Cmall_category_model->get_category_info($my_category);
			$my_category = element('cca_parent', $data);
		}
		$result = array_reverse($result);

		return $result;
	}


	public function addcart($mem_id = 0, $cit_id = 0, $detail_array = '', $qty_array = '')
	{
		$mem_id = (int) $mem_id;
		if (empty($mem_id) OR $mem_id < 1) {
			return;
		}
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}
		if (empty($detail_array)) {
			return;
		}

		$this->CI->load->model(array('Cmall_cart_model', 'Cmall_item_detail_model'));

		$deletewhere = array(
			'mem_id' => $mem_id,
			'cit_id' => $cit_id,
			'cct_cart' => 1,
		);
		$this->CI->Cmall_cart_model->delete_where($deletewhere);

		if ($detail_array && is_array($detail_array)) {
			foreach ($detail_array as $cde_id) {
				$detail = $this->CI->Cmall_item_model->get_one($cde_id, 'cit_id');
				if ( ! element('cit_id', $detail) OR (int) element('cit_id', $detail) !== $cit_id) {
					return;
				}
				if ( ! element($cde_id, $qty_array)) {
					return;
				}
			}
			foreach ($detail_array as $cde_id) {
				$insertdata = array(
					'mem_id' => $mem_id,
					'cit_id' => $cit_id,
					'cde_id' => $cde_id,
					'cct_count' => element($cde_id, $qty_array),
					'cct_cart' => 1,
					'cct_datetime' => cdate('Y-m-d H:i:s'),
					'cct_ip' => $this->CI->input->ip_address(),
				);
				$cct_id = $this->CI->Cmall_cart_model->insert($insertdata);
			}
		}
		return $cit_id;
	}


	public function addorder($mem_id = 0, $cit_id = 0, $detail_array = '', $qty_array = '')
	{
		$mem_id = (int) $mem_id;
		if (empty($mem_id) OR $mem_id < 1) {
			return;
		}
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}
		if (empty($detail_array)) {
			return;
		}

		$this->CI->load->model(array('Cmall_cart_model', 'Cmall_item_detail_model'));

		$deletewhere = array(
			'mem_id' => $mem_id,
			'cct_order' => 1,
		);
		$this->CI->Cmall_cart_model->delete_where($deletewhere);

		if ($detail_array && is_array($detail_array)) {
			foreach ($detail_array as $cde_id) {
				$detail = $this->CI->Cmall_item_model->get_one($cde_id, 'cit_id');
				if ( ! element('cit_id', $detail) OR (int) element('cit_id', $detail) !== $cit_id) {
					return;
				}
				if ( ! element($cde_id, $qty_array)) {
					return;
				}
			}
			foreach ($detail_array as $cde_id) {
				$insertdata = array(
					'mem_id' => $mem_id,
					'cit_id' => $cit_id,
					'cde_id' => $cde_id,
					'cct_count' => element($cde_id, $qty_array),
					'cct_order' => 1,
					'cct_datetime' => cdate('Y-m-d H:i:s'),
					'cct_ip' => $this->CI->input->ip_address(),
				);
				$cct_id = $this->CI->Cmall_cart_model->insert($insertdata);
			}
		}
		return $cit_id;
	}


	public function cart_to_order($mem_id = 0, $cit_id_array = '')
	{
		$mem_id = (int) $mem_id;
		if (empty($mem_id) OR $mem_id < 1) {
			return;
		}
		if (empty($cit_id_array)) {
			return;
		}

		$this->CI->load->model(array('Cmall_cart_model'));

		$deletewhere = array(
			'mem_id' => $mem_id,
			'cct_order' => 1,
		);
		$this->CI->Cmall_cart_model->delete_where($deletewhere);

		if ($cit_id_array && is_array($cit_id_array)) {
			foreach ($cit_id_array as $cit_id) {
				$where = array(
					'mem_id' => $mem_id,
					'cit_id' => $cit_id,
				);
				$result = $this->CI->Cmall_cart_model->get('', '', $where);
				if ($result) {
					foreach ($result as $value) {
						$insertdata = array(
							'mem_id' => $mem_id,
							'cit_id' => $cit_id,
							'cde_id' => element('cde_id', $value),
							'cct_count' => element('cct_count', $value),
							'cct_order' => 1,
							'cct_datetime' => cdate('Y-m-d H:i:s'),
							'cct_ip' => $this->CI->input->ip_address(),
						);
						$cct_id = $this->CI->Cmall_cart_model->insert($insertdata);
					}
				}
			}
		}

		return true;
	}


	public function get_my_cart($limit = 5)
	{
		$mem_id = (int) $this->CI->member->item('mem_id');
		if (empty($mem_id)) {
			return;
		}
		$this->CI->load->model(array('Cmall_cart_model'));
		$where = array(
			'cmall_cart.mem_id' => $mem_id,
		);
		$result = $this->CI->Cmall_cart_model->get_cart_list($where, 'cct_id', 'desc', $limit);

		return $result;
	}


	public function get_my_wishlist($limit = 5)
	{
		$mem_id = (int) $this->CI->member->item('mem_id');
		if (empty($mem_id)) {
			return;
		}
		$this->CI->load->model(array('Cmall_wishlist_model'));
		$where = array(
			'cmall_wishlist.mem_id' => $mem_id,
			'cit_status' => 1,
			'cit_is_del' => 0,			
		);
		$result = $this->CI->Cmall_wishlist_model
			->get_list($limit, $offset = '', $where, $like = '', $findex = 'cwi_id', $forder = 'desc');

		return element('list', $result);
	}


	public function addwish($mem_id = 0, $cit_id = 0)
	{
		$mem_id = (int) $mem_id;
		if (empty($mem_id) OR $mem_id < 1) {
			return;
		}
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}

		$this->CI->load->model(array('Cmall_item_model', 'Cmall_wishlist_model'));

		$insertdata = array(
			'mem_id' => $mem_id,
			'cit_id' => $cit_id,
			'cwi_datetime' => cdate('Y-m-d H:i:s'),
			'cwi_ip' => $this->CI->input->ip_address(),
		);
		$cwi_id = $this->CI->Cmall_wishlist_model->replace($insertdata);

		$where = array(
			'cit_id' => $cit_id,
		);
		$count = $this->CI->Cmall_wishlist_model->count_by($where);

		$updatedata = array(
			'cit_wish_count' => $count,
		);
		$this->CI->Cmall_item_model->update($cit_id, $updatedata);

		return $cwi_id;
	}

	public function addstore($mem_id = 0, $brd_id = 0)
	{
		$mem_id = (int) $mem_id;
		if (empty($mem_id) OR $mem_id < 1) {
			return;
		}
		$brd_id = (int) $brd_id;
		if (empty($brd_id) OR $brd_id < 1) {
			return;
		}

		$this->CI->load->model(array( 'Cmall_storewishlist_model'));

		$insertdata = array(
			'mem_id' => $mem_id,
			'brd_id' => $brd_id,
			'csi_datetime' => cdate('Y-m-d H:i:s'),
			'csi_ip' => $this->CI->input->ip_address(),
		);
		$csi_id = $this->CI->Cmall_storewishlist_model->replace($insertdata);

		$where = array(
			'brd_id' => $brd_id,
		);
		$count = $this->CI->Cmall_storewishlist_model->count_by($where);

		$updatedata = array(
			'brd_storewish_count' => $count,
		);
		$this->CI->Board_model->update($brd_id, $updatedata);

		return $csi_id;
	}

	public function delwish($mem_id = 0, $cit_id = 0)
	{
		$mem_id = (int) $mem_id;
		if (empty($mem_id) OR $mem_id < 1) {
			return;
		}
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}

		$this->CI->load->model(array('Cmall_item_model', 'Cmall_wishlist_model'));

		
		$deletewhere = array(
			'mem_id' => $mem_id,
			'cit_id' => $cit_id
		);
		$this->CI->Cmall_wishlist_model->delete_where($deletewhere);

		$where = array(
			'cit_id' => $cit_id,
		);
		$count = $this->CI->Cmall_wishlist_model->count_by($where);

		$updatedata = array(
			'cit_wish_count' => $count,
		);
		$result = $this->CI->Cmall_item_model->update($cit_id, $updatedata);

		return $result;
	}

	public function delstore($mem_id = 0, $brd_id = 0)
	{
		$mem_id = (int) $mem_id;
		if (empty($mem_id) OR $mem_id < 1) {
			return;
		}
		$brd_id = (int) $brd_id;
		if (empty($brd_id) OR $brd_id < 1) {
			return;
		}

		$this->CI->load->model(array( 'Cmall_storewishlist_model','Board_model'));
		

		$deletewhere = array(
			'mem_id' => $mem_id,
			'brd_id' => $brd_id
		);
		$this->CI->Cmall_storewishlist_model->delete_where($deletewhere);

		$where = array(
			'brd_id' => $brd_id,
		);
		$count = $this->CI->Cmall_storewishlist_model->count_by($where);

		$updatedata = array(
			'brd_storewish_count' => $count,
		);
		$result = $this->CI->Board_model->update($brd_id, $updatedata);

		return $result;
	}

	public function is_ordered_item($mem_id = 0, $cit_id = 0)
	{
		$mem_id = (int) $mem_id;
		if (empty($mem_id) OR $mem_id < 1) {
			return;
		}
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}

		$this->CI->load->model(array('Cmall_order_model'));
		$result = $this->CI->Cmall_order_model->is_ordered_item($mem_id, $cit_id);

		return $result;
	}


	public function update_review_count($cit_id = 0)
	{
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}
		$this->CI->load->model(array('Cmall_item_model', 'Cmall_review_model'));
		$result = $this->CI->Cmall_review_model->get_review_count($cit_id);

		$avg = 0;

		if (element('cnt', $result)) {
			$avg = round(10 * element('cre_score', $result) / element('cnt', $result)) / 10;
		}

		$updatedata = array(
			'cit_review_count' => element('cnt', $result),
			'cit_review_average' => $avg,
		);
		$this->CI->Cmall_item_model->update($cit_id, $updatedata);

		return json_encode($updatedata);
	}


	public function update_qna_count($cit_id = 0)
	{
		$cit_id = (int) $cit_id;
		if (empty($cit_id) OR $cit_id < 1) {
			return;
		}
		$this->CI->load->model(array('Cmall_item_model', 'Cmall_qna_model'));
		$result = $this->CI->Cmall_qna_model->get_qna_count($cit_id);

		$updatedata = array(
			'cit_qna_count' => element('cnt', $result),
		);
		$this->CI->Cmall_item_model->update($cit_id, $updatedata);

		return json_encode($updatedata);
	}


	public function review_alarm($cre_id = 0)
	{
		$cre_id = (int) $cre_id;
		if (empty($cre_id) OR $cre_id < 1) {
			return;
		}

		$this->CI->load->model(array('Cmall_review_model', 'Cmall_item_model', 'Member_model'));

		$review = $this->CI->Cmall_review_model->get_one($cre_id);
		$item = $this->CI->Cmall_item_model->get_one(element('cit_id', $review), 'cit_name, cit_key');
		$member = $this->CI->Member_model->get_one(element('mem_id', $review));

		if ( ! element('cre_id', $review)) {
			return;
		}

		$emailsendlistadmin = array();
		$notesendlistadmin = array();
		$smssendlistadmin = array();
		$emailsendlistuser = array();
		$notesendlistuser = array();
		$smssendlistuser = array();

		$superadminlist = '';
		if ($this->CI->cbconfig->item('cmall_email_admin_write_product_review')
			OR $this->CI->cbconfig->item('cmall_note_admin_write_product_review')
			OR $this->CI->cbconfig->item('cmall_sms_admin_write_product_review')) {

			$mselect = 'mem_id, mem_email, mem_nickname, mem_phone';
			$superadminlist = $this->CI->Member_model->get_superadmin_list($mselect);

		}
		if ($this->CI->cbconfig->item('cmall_email_admin_write_product_review') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$emailsendlistadmin[$value['mem_id']] = $value;
			}
		}
		if (($this->CI->cbconfig->item('cmall_email_user_write_product_review') && element('mem_receive_email', $member))
			OR $this->CI->cbconfig->item('cmall_email_alluser_write_product_review')) {
			$emailsendlistuser['mem_email'] = element('mem_email', $member);
		}
		if ($this->CI->cbconfig->item('cmall_note_admin_write_product_review') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$notesendlistadmin[$value['mem_id']] = $value;
			}
		}
		if ($this->CI->cbconfig->item('cmall_note_user_write_product_review') && element('mem_use_note', $member)) {
			$notesendlistuser['mem_id'] = element('mem_id', $member);
		}
		if ($this->CI->cbconfig->item('cmall_sms_admin_write_product_review') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$smssendlistadmin[$value['mem_id']] = $value;
			}
		}
		if (($this->CI->cbconfig->item('cmall_sms_user_write_product_review') && element('mem_receive_sms', $member))
			OR $this->CI->cbconfig->item('cmall_sms_alluser_write_product_review')) {
			if (element('mem_phone', $member)) {
				$smssendlistuser = $member;
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
			'{상품명}',
			'{상품주소}',
			'{후기제목}',
			'{후기내용}',
		);
		$receive_email = element('mem_receive_email', $member) ? '동의' : '거부';
		$receive_note = element('mem_use_note', $member) ? '동의' : '거부';
		$receive_sms = element('mem_receive_sms', $member) ? '동의' : '거부';
		$thumb_width = $this->CI->cbconfig->item('cmall_product_review_thumb_width');
		$autolink = $this->CI->cbconfig->item('use_cmall_product_review_auto_url');
		$popup = $this->CI->cbconfig->item('cmall_product_review_content_target_blank');
		$review_content = display_html_content(
			element('cre_content', $review),
			element('cre_content_html_type', $review),
			$thumb_width,
			$autolink,
			$popup
		);

		$replaceconfig = array(
			$this->CI->cbconfig->item('site_title'),
			$this->CI->cbconfig->item('company_name'),
			site_url(),
			element('mem_userid', $member),
			element('mem_nickname', $member),
			element('mem_username', $member),
			element('mem_email', $member),
			$receive_email,
			$receive_note,
			$receive_sms,
			$this->CI->input->ip_address(),
			element('cit_name', $item),
			cmall_item_url(element('cit_key', $item)),
			element('cre_title', $review),
			$review_content,
		);
		$replaceconfig_escape = array(
			html_escape($this->CI->cbconfig->item('site_title')),
			html_escape($this->CI->cbconfig->item('company_name')),
			site_url(),
			html_escape(element('mem_userid', $member)),
			html_escape(element('mem_nickname', $member)),
			html_escape(element('mem_username', $member)),
			html_escape(element('mem_email', $member)),
			$receive_email,
			$receive_note,
			$receive_sms,
			$this->CI->input->ip_address(),
			html_escape(element('cit_name', $item)),
			cmall_item_url(element('cit_key', $item)),
			html_escape(element('cre_title', $review)),
			$review_content,
		);

		if ($emailsendlistadmin) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_email_admin_write_product_review_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_email_admin_write_product_review_content')
			);
			foreach ($emailsendlistadmin as $akey => $aval) {
				$this->CI->email->clear(true);
				$this->CI->email->from($this->CI->cbconfig->item('webmaster_email'), $this->CI->cbconfig->item('webmaster_name'));
				$this->CI->email->to(element('mem_email', $aval));
				$this->CI->email->subject($title);
				$this->CI->email->message($content);
				$this->CI->email->send();
			}
		}
		if ($emailsendlistuser) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_email_user_write_product_review_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_email_user_write_product_review_content')
			);
			$this->CI->email->clear(true);
			$this->CI->email->from($this->CI->cbconfig->item('webmaster_email'), $this->CI->cbconfig->item('webmaster_name'));
			$this->CI->email->to(element('mem_email', $emailsendlistuser));
			$this->CI->email->subject($title);
			$this->CI->email->message($content);
			$this->CI->email->send();
		}
		if ($notesendlistadmin) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_note_admin_write_product_review_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_note_admin_write_product_review_content')
			);
			foreach ($notesendlistadmin as $akey => $aval) {
				$note_result = $this->CI->notelib->send_note(
					$sender = 0,
					$receiver = element('mem_id', $aval),
					$title,
					$content,
					1
				);
			}
		}
		if ($notesendlistuser) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_note_user_write_product_review_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_note_user_write_product_review_content')
			);
			$note_result = $this->CI->notelib->send_note(
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
					$this->CI->cbconfig->item('cmall_sms_admin_write_product_review_content')
				);
				$sender = array(
					'phone' => $this->CI->cbconfig->item('sms_admin_phone'),
				);
				$receiver = array();
				foreach ($smssendlistadmin as $akey => $aval) {
					$receiver[] = array(
						'mem_id' => element('mem_id', $aval),
						'name' => element('mem_nickname', $aval),
						'phone' => element('mem_phone', $aval),
					);
				}
				$smsresult = $this->CI->smslib->send($receiver, $sender, $content, $date = '', '상품리뷰작성알림');
			}
		}
		if ($smssendlistuser) {
			if (file_exists(APPPATH . 'libraries/Smslib.php')) {
				$this->load->library(array('smslib'));
				$content = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->CI->cbconfig->item('cmall_sms_user_write_product_review_content')
				);
				$sender = array(
					'phone' => $this->CI->cbconfig->item('sms_admin_phone'),
				);
				$receiver = array();
				$receiver[] = $smssendlistuser;
				$smsresult = $this->CI->smslib->send($receiver, $sender, $content, $date = '', '상품리뷰작성알림');
			}
		}
	}


	public function qna_alarm($cqa_id = 0)
	{
		$cqa_id = (int) $cqa_id;
		if (empty($cqa_id) OR $cqa_id < 1) {
			return;
		}

		$this->CI->load->model(array('Cmall_qna_model', 'Cmall_item_model', 'Member_model'));

		$qna = $this->CI->Cmall_qna_model->get_one($cqa_id);
		$item = $this->CI->Cmall_item_model->get_one(element('cit_id', $qna), 'cit_name, cit_key');
		$member = $this->CI->Member_model->get_one(element('mem_id', $qna));

		if ( ! element('cqa_id', $qna)) {
			return;
		}

		$emailsendlistadmin = array();
		$notesendlistadmin = array();
		$smssendlistadmin = array();
		$emailsendlistuser = array();
		$notesendlistuser = array();
		$smssendlistuser = array();

		$superadminlist = '';
		if ($this->CI->cbconfig->item('cmall_email_admin_write_product_qna')
			OR $this->CI->cbconfig->item('cmall_note_admin_write_product_qna')
			OR $this->CI->cbconfig->item('cmall_sms_admin_write_product_qna')) {

			$mselect = 'mem_id, mem_email, mem_nickname, mem_phone';
			$superadminlist = $this->CI->Member_model->get_superadmin_list($mselect);

		}
		if ($this->CI->cbconfig->item('cmall_email_admin_write_product_qna') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$emailsendlistadmin[$value['mem_id']] = $value;
			}
		}
		if ($this->CI->cbconfig->item('cmall_email_user_write_product_qna') && $member && element('cqa_receive_email', $qna)) {
			$emailsendlistuser['mem_email'] = element('mem_email', $member);
		}
		if ($this->CI->cbconfig->item('cmall_note_admin_write_product_qna') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$notesendlistadmin[$value['mem_id']] = $value;
			}
		}
		if ($this->CI->cbconfig->item('cmall_note_user_write_product_qna') && element('mem_use_note', $member)) {
			$notesendlistuser['mem_id'] = element('mem_id', $member);
		}
		if ($this->CI->cbconfig->item('cmall_sms_admin_write_product_qna') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$smssendlistadmin[$value['mem_id']] = $value;
			}
		}
		if ($this->CI->cbconfig->item('cmall_sms_user_write_product_qna') && $member && element('cqa_receive_sms', $qna)) {
			if (element('mem_phone', $member)) {
				$smssendlistuser = $member;
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
			'{상품명}',
			'{상품주소}',
			'{문의제목}',
			'{문의내용}',
		);
		$receive_email = element('mem_receive_email', $member) ? '동의' : '거부';
		$receive_note = element('mem_use_note', $member) ? '동의' : '거부';
		$receive_sms = element('mem_receive_sms', $member) ? '동의' : '거부';
		$thumb_width = $this->CI->cbconfig->item('cmall_product_qna_thumb_width');
		$autolink = $this->CI->cbconfig->item('use_cmall_product_qna_auto_url');
		$popup = $this->CI->cbconfig->item('cmall_product_qna_content_target_blank');
		$qna_content = display_html_content(
			element('cqa_content', $qna),
			element('cqa_content_html_type', $qna),
			$thumb_width,
			$autolink,
			$popup
		);

		$replaceconfig = array(
			$this->CI->cbconfig->item('site_title'),
			$this->CI->cbconfig->item('company_name'),
			site_url(),
			element('mem_userid', $member),
			element('mem_nickname', $member),
			element('mem_username', $member),
			element('mem_email', $member),
			$receive_email,
			$receive_note,
			$receive_sms,
			$this->CI->input->ip_address(),
			element('cit_name', $item),
			cmall_item_url(element('cit_key', $item)),
			element('cqa_title', $qna),
			$qna_content,
		);
		$replaceconfig_escape = array(
			html_escape($this->CI->cbconfig->item('site_title')),
			html_escape($this->CI->cbconfig->item('company_name')),
			site_url(),
			html_escape(element('mem_userid', $member)),
			html_escape(element('mem_nickname', $member)),
			html_escape(element('mem_username', $member)),
			html_escape(element('mem_email', $member)),
			$receive_email,
			$receive_note,
			$receive_sms,
			$this->CI->input->ip_address(),
			html_escape(element('cit_name', $item)),
			cmall_item_url(element('cit_key', $item)),
			html_escape(element('cqa_title', $qna)),
			$qna_content,
		);

		if ($emailsendlistadmin) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_email_admin_write_product_qna_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_email_admin_write_product_qna_content')
			);
			foreach ($emailsendlistadmin as $akey => $aval) {
				$this->CI->email->clear(true);
				$this->CI->email->from($this->CI->cbconfig->item('webmaster_email'), $this->CI->cbconfig->item('webmaster_name'));
				$this->CI->email->to(element('mem_email', $aval));
				$this->CI->email->subject($title);
				$this->CI->email->message($content);
				$this->CI->email->send();
			}
		}
		if ($emailsendlistuser) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_email_user_write_product_qna_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_email_user_write_product_qna_content')
			);
			$this->CI->email->clear(true);
			$this->CI->email->from($this->CI->cbconfig->item('webmaster_email'), $this->CI->cbconfig->item('webmaster_name'));
			$this->CI->email->to(element('mem_email', $emailsendlistuser));
			$this->CI->email->subject($title);
			$this->CI->email->message($content);
			$this->CI->email->send();
		}
		if ($notesendlistadmin) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_note_admin_write_product_qna_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_note_admin_write_product_qna_content')
			);
			foreach ($notesendlistadmin as $akey => $aval) {
				$note_result = $this->CI->notelib->send_note(
					$sender = 0,
					$receiver = element('mem_id', $aval),
					$title,
					$content,
					1
				);
			}
		}
		if ($notesendlistuser) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_note_user_write_product_qna_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_note_user_write_product_qna_content')
			);
			$note_result = $this->CI->notelib->send_note(
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
					$this->CI->cbconfig->item('cmall_sms_admin_write_product_qna_content')
				);
				$sender = array(
					'phone' => $this->CI->cbconfig->item('sms_admin_phone'),
				);
				$receiver = array();
				foreach ($smssendlistadmin as $akey => $aval) {
					$receiver[] = array(
						'mem_id' => element('mem_id', $aval),
						'name' => element('mem_nickname', $aval),
						'phone' => element('mem_phone', $aval),
					);
				}
				$smsresult = $this->CI->smslib->send($receiver, $sender, $content, $date = '', '상품문의작성알림');
			}
		}
		if ($smssendlistuser) {
			if (file_exists(APPPATH . 'libraries/Smslib.php')) {
				$this->load->library(array('smslib'));
				$content = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->CI->cbconfig->item('cmall_sms_user_write_product_qna_content')
				);
				$sender = array(
					'phone' => $this->CI->cbconfig->item('sms_admin_phone'),
				);
				$receiver = array();
				$receiver[] = $smssendlistuser;
				$smsresult = $this->CI->smslib->send($receiver, $sender, $content, $date = '', '상품문의작성알림');
			}
		}
	}


	public function qna_reply_alarm($cqa_id = 0)
	{
		$cqa_id = (int) $cqa_id;
		if (empty($cqa_id) OR $cqa_id < 1) {
			return;
		}

		$this->CI->load->model(array('Cmall_qna_model', 'Cmall_item_model', 'Member_model'));

		$qna = $this->CI->Cmall_qna_model->get_one($cqa_id);
		$item = $this->CI->Cmall_item_model->get_one(element('cit_id', $qna), 'cit_name, cit_key');
		$member = $this->CI->Member_model->get_one(element('mem_id', $qna));

		if ( ! element('cqa_id', $qna)) {
			return;
		}

		$emailsendlistadmin = array();
		$notesendlistadmin = array();
		$smssendlistadmin = array();
		$emailsendlistuser = array();
		$notesendlistuser = array();
		$smssendlistuser = array();

		$superadminlist = '';
		if ($this->CI->cbconfig->item('cmall_email_admin_write_product_qna_reply')
			OR $this->CI->cbconfig->item('cmall_note_admin_write_product_qna_reply')
			OR $this->CI->cbconfig->item('cmall_sms_admin_write_product_qna_reply')) {

			$mselect = 'mem_id, mem_email, mem_nickname, mem_phone';
			$superadminlist = $this->CI->Member_model->get_superadmin_list($mselect);

		}
		if ($this->CI->cbconfig->item('cmall_email_admin_write_product_qna_reply') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$emailsendlistadmin[$value['mem_id']] = $value;
			}
		}
		if ($this->CI->cbconfig->item('cmall_email_user_write_product_qna_reply') && $member && element('cqa_receive_email', $qna)) {
			$emailsendlistuser['mem_email'] = element('mem_email', $member);
		}
		if ($this->CI->cbconfig->item('cmall_note_admin_write_product_qna_reply') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$notesendlistadmin[$value['mem_id']] = $value;
			}
		}
		if ($this->CI->cbconfig->item('cmall_note_user_write_product_qna_reply') && element('mem_use_note', $member)) {
			$notesendlistuser['mem_id'] = element('mem_id', $member);
		}
		if ($this->CI->cbconfig->item('cmall_sms_admin_write_product_qna_reply') && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$smssendlistadmin[$value['mem_id']] = $value;
			}
		}
		if ($this->CI->cbconfig->item('cmall_sms_user_write_product_qna_reply') && $member && element('cqa_receive_sms', $qna)) {
			if (element('mem_phone', $member)) {
				$smssendlistuser = $member;
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
			'{상품명}',
			'{상품주소}',
			'{문의제목}',
			'{문의내용}',
			'{답변내용}',
		);
		$receive_email = element('mem_receive_email', $member) ? '동의' : '거부';
		$receive_note = element('mem_use_note', $member) ? '동의' : '거부';
		$receive_sms = element('mem_receive_sms', $member) ? '동의' : '거부';
		$thumb_width = $this->CI->cbconfig->item('cmall_product_qna_thumb_width');
		$autolink = $this->CI->cbconfig->item('use_cmall_product_qna_auto_url');
		$popup = $this->CI->cbconfig->item('cmall_product_qna_content_target_blank');
		$qna_content = display_html_content(
			element('cqa_content', $qna),
			element('cqa_content_html_type', $qna),
			$thumb_width,
			$autolink,
			$popup
		);
		$reply_content = display_html_content(
			element('cqa_reply_content', $qna),
			element('cqa_reply_html_type', $qna),
			$thumb_width,
			$autolink,
			$popup
		);

		$replaceconfig = array(
			$this->CI->cbconfig->item('site_title'),
			$this->CI->cbconfig->item('company_name'),
			site_url(),
			element('mem_userid', $member),
			element('mem_nickname', $member),
			element('mem_username', $member),
			element('mem_email', $member),
			$receive_email,
			$receive_note,
			$receive_sms,
			$this->CI->input->ip_address(),
			element('cit_name', $item),
			cmall_item_url(element('cit_key', $item)),
			element('cqa_title', $qna),
			$qna_content,
			$reply_content,
		);
		$replaceconfig_escape = array(
			html_escape($this->CI->cbconfig->item('site_title')),
			html_escape($this->CI->cbconfig->item('company_name')),
			site_url(),
			html_escape(element('mem_userid', $member)),
			html_escape(element('mem_nickname', $member)),
			html_escape(element('mem_username', $member)),
			html_escape(element('mem_email', $member)),
			$receive_email,
			$receive_note,
			$receive_sms,
			$this->CI->input->ip_address(),
			html_escape(element('cit_name', $item)),
			cmall_item_url(element('cit_key', $item)),
			html_escape(element('cqa_title', $qna)),
			$qna_content,
			$reply_content,
		);

		if ($emailsendlistadmin) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_email_admin_write_product_qna_reply_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_email_admin_write_product_qna_reply_content')
			);
			foreach ($emailsendlistadmin as $akey => $aval) {
				$this->CI->email->clear(true);
				$this->CI->email->from($this->CI->cbconfig->item('webmaster_email'), $this->CI->cbconfig->item('webmaster_name'));
				$this->CI->email->to(element('mem_email', $aval));
				$this->CI->email->subject($title);
				$this->CI->email->message($content);
				$this->CI->email->send();
			}
		}
		if ($emailsendlistuser) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_email_user_write_product_qna_reply_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_email_user_write_product_qna_reply_content')
			);
			$this->CI->email->clear(true);
			$this->CI->email->from($this->CI->cbconfig->item('webmaster_email'), $this->CI->cbconfig->item('webmaster_name'));
			$this->CI->email->to(element('mem_email', $emailsendlistuser));
			$this->CI->email->subject($title);
			$this->CI->email->message($content);
			$this->CI->email->send();
		}
		if ($notesendlistadmin) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_note_admin_write_product_qna_reply_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_note_admin_write_product_qna_reply_content')
			);
			foreach ($notesendlistadmin as $akey => $aval) {
				$note_result = $this->CI->notelib->send_note(
					$sender = 0,
					$receiver = element('mem_id', $aval),
					$title,
					$content,
					1
				);
			}
		}
		if ($notesendlistuser) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_note_user_write_product_qna_reply_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_note_user_write_product_qna_reply_content')
			);
			$note_result = $this->CI->notelib->send_note(
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
					$this->CI->cbconfig->item('cmall_sms_admin_write_product_qna_reply_content')
				);
				$sender = array(
					'phone' => $this->CI->cbconfig->item('sms_admin_phone'),
				);
				$receiver = array();
				foreach ($smssendlistadmin as $akey => $aval) {
					$receiver[] = array(
						'mem_id' => element('mem_id', $aval),
						'name' => element('mem_nickname', $aval),
						'phone' => element('mem_phone', $aval),
					);
				}
				$smsresult = $this->CI->smslib->send($receiver, $sender, $content, $date = '', '상품문의답변작성알림');
			}
		}
		if ($smssendlistuser) {
			if (file_exists(APPPATH . 'libraries/Smslib.php')) {
				$this->load->library(array('smslib'));
				$content = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->CI->cbconfig->item('cmall_sms_user_write_product_qna_reply_content')
				);
				$sender = array(
					'phone' => $this->CI->cbconfig->item('sms_admin_phone'),
				);
				$receiver = array();
				$receiver[] = $smssendlistuser;
				$smsresult = $this->CI->smslib->send($receiver, $sender, $content, $date = '', '상품문의답변작성알림');
			}
		}
	}


	public function orderalarm($type = '', $cor_id = 0)
	{
		if (empty($type)) {
			return;
		}
		$cor_id = (int) $cor_id;
		if (empty($cor_id) OR $cor_id < 1) {
			return;
		}

		$this->CI->load->model(array('Cmall_item_model', 'Cmall_order_model', 'Cmall_order_detail_model', 'Member_model'));
		$order = $this->CI->Cmall_order_model->get_one($cor_id);
		if ( ! element('cor_id', $order)) {
			return;
		}
		$orderdetail = $this->CI->Cmall_order_detail_model->get_by_item($cor_id);
		if ($orderdetail) {
			foreach ($orderdetail as $key => $value) {
				$orderdetail[$key]['item'] = $this->CI->Cmall_item_model->get_one(element('cit_id', $value));
				$orderdetail[$key]['itemdetail'] = $this->CI->Cmall_order_detail_model->get_detail_by_item($cor_id, element('cit_id', $value));
			}
		}

		$member = $this->CI->Member_model->get_one(element('mem_id', $order));

		$emailsendlistadmin = array();
		$notesendlistadmin = array();
		$smssendlistadmin = array();
		$emailsendlistuser = array();
		$notesendlistuser = array();
		$smssendlistuser = array();

		$superadminlist = '';
		if ($this->CI->cbconfig->item('cmall_email_admin_' . $type)
			OR $this->CI->cbconfig->item('cmall_note_admin_' . $type)
			OR $this->CI->cbconfig->item('cmall_sms_admin_' . $type)) {

			$mselect = 'mem_id, mem_email, mem_nickname, mem_phone';
			$superadminlist = $this->CI->Member_model->get_superadmin_list($mselect);

		}
		if ($this->CI->cbconfig->item('cmall_email_admin_' . $type) && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$emailsendlistadmin[$value['mem_id']] = $value;
			}
		}
		if (($this->CI->cbconfig->item('cmall_email_user_' . $type) && element('mem_receive_email', $member)) OR $this->CI->cbconfig->item('cmall_email_alluser_' . $type)) {
			$emailsendlistuser['mem_email'] = element('mem_email', $member);
		}
		if ($this->CI->cbconfig->item('cmall_note_admin_' . $type) && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$notesendlistadmin[$value['mem_id']] = $value;
			}
		}
		if ($this->CI->cbconfig->item('cmall_note_user_' . $type) && element('mem_use_note', $member)) {
			$notesendlistuser['mem_id'] = element('mem_id', $member);
		}
		if ($this->CI->cbconfig->item('cmall_sms_admin_' . $type) && $superadminlist) {
			foreach ($superadminlist as $key => $value) {
				$smssendlistadmin[$value['mem_id']] = $value;
			}
		}
		if (($this->CI->cbconfig->item('cmall_sms_user_' . $type) && element('mem_receive_sms', $member))
			OR $this->CI->cbconfig->item('cmall_sms_alluser_' . $type)) {
			if (element('mem_phone', $member)) {
				$smssendlistuser = $member;
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
			'{결제금액}',
			'{은행계좌안내}',
		);
		$receive_email = element('mem_receive_email', $member) ? '동의' : '거부';
		$receive_note = element('mem_use_note', $member) ? '동의' : '거부';
		$receive_sms = element('mem_receive_sms', $member) ? '동의' : '거부';

		$replaceconfig = array(
			$this->CI->cbconfig->item('site_title'),
			$this->CI->cbconfig->item('company_name'),
			site_url(),
			element('mem_userid', $member),
			element('mem_nickname', $member),
			element('mem_username', $member),
			element('mem_email', $member),
			$receive_email,
			$receive_note,
			$receive_sms,
			$this->CI->input->ip_address(),
			number_format(abs(element('cor_cash_request', $order))),
			$this->CI->cbconfig->item('payment_bank_info'),
		);
		$replaceconfig_escape = array(
			html_escape($this->CI->cbconfig->item('site_title')),
			html_escape($this->CI->cbconfig->item('company_name')),
			site_url(),
			html_escape(element('mem_userid', $member)),
			html_escape(element('mem_nickname', $member)),
			html_escape(element('mem_username', $member)),
			html_escape(element('mem_email', $member)),
			$receive_email,
			$receive_note,
			$receive_sms,
			$this->CI->input->ip_address(),
			number_format(abs(element('cor_cash_request', $order))),
			html_escape($this->CI->cbconfig->item('payment_bank_info')),
		);
		$emailform = array();
		$emailform['emailform']['order'] = $order;
		$emailform['emailform']['orderdetail'] = $orderdetail;

		if ($emailsendlistadmin) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_email_admin_' . $type . '_title')
			);
			$content = $this->CI->load->view('emailform/cmall/email_admin_' . $type, $emailform, true);
			foreach ($emailsendlistadmin as $akey => $aval) {
				$this->CI->email->clear(true);
				$this->CI->email->from($this->CI->cbconfig->item('webmaster_email'), $this->CI->cbconfig->item('webmaster_name'));
				$this->CI->email->to(element('mem_email', $aval));
				$this->CI->email->subject($title);
				$this->CI->email->message($content);
				$this->CI->email->send();
			}
		}
		if ($emailsendlistuser) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_email_user_' . $type . '_title')
			);
			$content = $this->CI->load->view('emailform/cmall/email_user_' . $type, $emailform, true);
			$this->CI->email->clear(true);
			$this->CI->email->from($this->CI->cbconfig->item('webmaster_email'), $this->CI->cbconfig->item('webmaster_name'));
			$this->CI->email->to(element('mem_email', $emailsendlistuser));
			$this->CI->email->subject($title);
			$this->CI->email->message($content);
			$this->CI->email->send();
		}
		if ($notesendlistadmin) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_note_admin_' . $type . '_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_note_admin_' . $type . '_content')
			);
			foreach ($notesendlistadmin as $akey => $aval) {
				$note_result = $this->CI->notelib->send_note(
					$sender = 0,
					$receiver = element('mem_id', $aval),
					$title,
					$content,
					1
				);
			}
		}
		if ($notesendlistuser) {
			$title = str_replace(
				$searchconfig,
				$replaceconfig,
				$this->CI->cbconfig->item('cmall_note_user_' . $type . '_title')
			);
			$content = str_replace(
				$searchconfig,
				$replaceconfig_escape,
				$this->CI->cbconfig->item('cmall_note_user_' . $type . '_content')
			);
			$note_result = $this->CI->notelib->send_note(
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
					$this->CI->cbconfig->item('cmall_sms_admin_' . $type . '_content')
				);
				$sender = array(
					'phone' => $this->CI->cbconfig->item('sms_admin_phone'),
				);
				$receiver = array();
				foreach ($smssendlistadmin as $akey => $aval) {
					$receiver[] = array(
						'mem_id' => element('mem_id', $aval),
						'name' => element('mem_nickname', $aval),
						'phone' => element('mem_phone', $aval),
					);
				}
				$smsresult = $this->CI->smslib->send($receiver, $sender, $content, $date = '', '컨텐츠몰');
			}
		}
		if ($smssendlistuser) {
			if (file_exists(APPPATH . 'libraries/Smslib.php')) {
				$this->load->library(array('smslib'));
				$content = str_replace(
					$searchconfig,
					$replaceconfig,
					$this->CI->cbconfig->item('cmall_sms_user_' . $type . '_content')
				);
				$sender = array(
					'phone' => $this->CI->cbconfig->item('sms_admin_phone'),
				 );
				$receiver = array();
				$receiver[] = $smssendlistuser;
				$smsresult = $this->CI->smslib->send($receiver, $sender, $content, $date = '', '컨텐츠몰');
			}
		}
	}

	public function _storewishlist_delete($csi_id = 0)
	{
		
		$this->CI->load->model(array( 'Cmall_storewishlist_model','Board_model'));

		
		$wishlist = $this->CI->Cmall_storewishlist_model->get_one($csi_id);
		

		$this->CI->Cmall_storewishlist_model->delete($csi_id);

		$where = array(
			'brd_id' => element('brd_id', $wishlist),
		);
		$count = $this->CI->Cmall_storewishlist_model->count_by($where);

		$updatedata = array(
			'brd_storewish_count' => $count,
		);
		$this->CI->Board_model->update(element('brd_id', $wishlist), $updatedata);

		return true;
	}

	public function _wishlist_delete($cwi_id = 0)
	{
		

		$this->CI->load->model(array('Cmall_item_model', 'Cmall_wishlist_model'));
		
		$wishlist = $this->CI->Cmall_wishlist_model->get_one($cwi_id);

		$this->CI->Cmall_wishlist_model->delete($cwi_id);

		$where = array(
			'cit_id' => element('cit_id', $wishlist),
		);
		$count = $this->CI->Cmall_wishlist_model->count_by($where);

		$updatedata = array(
			'cit_wish_count' => $count,
		);
		$this->CI->Cmall_item_model->update(element('cit_id', $wishlist), $updatedata);

		return true;
	}

	public function _review_delete($cre_id = 0)
    {
        $cre_id = (int) $cre_id;
        if (empty($cre_id) OR $cre_id < 1) {
            return;
        }

        $view['view'] = array();
        $this->CI->load->model(array('Cmall_review_model','Review_file_model'));
        $this->CI->load->library(array('aws_s3'));
        
        $review = $this->CI->Cmall_review_model->get_one($cre_id);
        
        
        $this->CI->Cmall_review_model->delete($cre_id);
        $cntresult = $this->update_review_count(element('cit_id', $review));
        
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

	public function _itemlists($category_id = 0,$brd_id = 0,$swhere = array(),$cfg = array())
	{

        

        $view = array();
        $view['view'] = array();

        $this->CI->load->model(array('Board_model'));

        $this->CI->load->library('denguruapi');
        /**
         * 페이지에 숫자가 아닌 문자가 입력되거나 1보다 작은 숫자가 입력되면 에러 페이지를 보여줍니다.
         */
        $param =& $this->CI->querystring;
        $page = (((int) $this->CI->input->get('page')) > 0) ? ((int) $this->CI->input->get('page')) : 1;

        

        $findex =  '(0.1/cit_order) desc,cit_id desc';
        
        $sfield = $this->CI->input->get('sfield', null, '');
        if ($sfield === 'cit_both') {
            $sfield = array('cit_name', 'cit_content');
        }
        $skeyword = $this->CI->input->get('skeyword', null, '');


        // $per_page = $this->CI->cbconfig->item('list_count') ? (int) $this->CI->cbconfig->item('list_count') : 20;  
        $per_page = get_listnum(20);      
        
        $offset = ($page - 1) * $per_page;

        $this->CI->Board_model->allow_search_field = array('brd_name','cit_id', 'cit_name', 'cit_content', 'cit_both', 'cit_price'); // 검색이 가능한 필드
        $this->CI->Board_model->search_field_equal = array('cit_id'); // 검색중 like 가 아닌 = 검색을 하는 필드

        /**
         * 게시판 목록에 필요한 정보를 가져옵니다.
         */
        $where = array();
        $where['cit_status'] = 1;
        $where['cit_is_del'] = 0;
        $where['cit_is_soldout'] = 0;

        
        $where['brd_blind'] = 0;
        // $field = array(
        //  'board' => array('brd_name'),
        //  'cmall_item' => array('cit_id','cit_name','cit_file_1','cit_review_average','cit_price','cit_price_sale'),
        //  'cmall_brand' => array('cbr_value_kr','cbr_value_en'),
        // );
        
        // $select = get_selected($field);

        // $this->CI->Board_model->select = $select;

        // $item_ids = $chk_item_id;
        // if($item_ids && is_array($item_ids)){
        //  $this->CI->Board_model->set_where_in('cit_id',$item_ids);
        //  $per_page = 9999;
        //  $offset = '';
        // }

        if($brd_id){
            $where['board.brd_id'] = $brd_id;
            // $per_page = 18;
            // $offset = '';
        }
        if(element('per_page', $cfg)){
            $per_page = element('per_page', $cfg);
            $offset = ($page - 1) * $per_page;  
        }

        if(element('findex', $cfg)){
            $findex = element('findex', $cfg);
            
        }

        if(element('set_join', $cfg)){
            $set_join[] = element('set_join', $cfg);            
            $this->CI->Board_model->set_join($set_join);
            
        }
        
        if($swhere && is_array($swhere)){
            foreach($swhere as $skey => $sval){
                if(!empty($sval)){
                    if(is_array($sval) )
                        $this->CI->Board_model->group_where_in($skey,$sval);
                    else
                        $where[$skey] = $sval;
                }
            }
        }

        
        $result = $this->CI->Board_model
            ->get_item_list($per_page, $offset, $where, $category_id, $findex);


        $list_num = $result['total_rows'] - ($page - 1) * $per_page;
        if (element('list', $result)) {
            foreach (element('list', $result) as $key => $val) {

                $result['list'][$key] = $this->CI->denguruapi->convert_cit_info($result['list'][$key]);
                $result['list'][$key] = $this->CI->denguruapi->convert_brd_info($result['list'][$key]);
                $result['list'][$key]['num'] = $list_num--;
            }
        }
        $view['view'] = $result;
        if($category_id){
            // $view['view']['category_nav'] = $this->get_nav_category($category_id);
            // $view['view']['category_all'] = $this->get_all_category();
            $view['view']['category_id'] = $category_id;
        }
        /**
         * 페이지네이션을 생성합니다
         */
        // if(empty($cfg) && empty($swhere)){
        // if(empty($brd_id)){
            $config['base_url'] = site_url('cmall/itemlists/' . $category_id.'/' . $brd_id) . '?' . $param->replace('page');
            $config['total_rows'] = $result['total_rows'];
            $config['per_page'] = $per_page;
            $this->CI->pagination->initialize($config);
            // $view['view']['paging'] = $this->CI->pagination->create_links();
            $view['view']['next_link'] = $this->CI->pagination->get_next_link();
            $view['view']['page'] = $page;


        // }
        

        
        
        return $view['view'];
        
    
	}
}
