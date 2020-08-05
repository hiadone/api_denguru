<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Smslib class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 문자 관련 class 입니다.
 * 그누보드 (http://www.sir.co.kr) 의 소스 참조
 */
class Smslib extends CI_Controller
{

    private $CI;
    var $Log = array();

    function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->helper( array('array'));
    }


    public function Init()
    {
        $this->Data = '';
        $this->Result = '';
    }


    public function get_icode_info($id, $pw)
    {   

        $sms_url = "https://apis.aligo.in/remain/"; // 전송요청 URL
        $sms['user_id'] = $id; // SMS 아이디
        $sms['key'] = $pw;//인증키
        /******************** 인증정보 ********************/

        $host_info = explode("/", $sms_url);
        $port = $host_info[0] == 'https:' ? 443 : 80;

        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, $port);
        curl_setopt($oCurl, CURLOPT_URL, $sms_url);
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sms);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);

        // CURL 접속이 안되는 경우 CURL 옵션안내를 참조 하셔서 옵션을 출력하여 확인해 주세요.
        // http://phpdoc.me/manual/kr/function.curl-setopt.php
        $ret = curl_exec($oCurl);
        curl_close($oCurl);
        
        $res = json_decode($ret); // 결과배열


        
        $userinfo = array(
            'code' => $res->result_code, // 결과코드
            'coin' => $res->SMS_CNT, // 고객 잔액 (충전제만 해당)
            // 'gpay' => $res[2], // 고객의 건수 별 차감액 표시 (충전제만 해당)
            // 'payment' => $res[3] // 요금제 표시, A:충전제, C:정액제
        );

        return $userinfo;
    }


    public function send($receiver = '', $sender = '', $content = '', $date = '', $memo = '')
    {
        $this->SMS_con();
        $date2 = $date ? str_replace(array(' ', '-', ':'), '', $date) : '';
        if (element('phone', $receiver)) {
            $list[0] = $receiver;
        } else {
            $list = $receiver;
        }
        if ($list) {
            foreach ($list as $key => $val) {
                $list[$key]['phone'] = str_replace('-', '', element('phone', $val));
            }
            if (empty($list[$key]['phone'])) unset($list[$key]);
        }
        $send_phone = (element('phone', $sender)) ? element('phone', $sender) : '0000';
        $send_mem_id = (element('mem_id', $sender)) ? element('mem_id', $sender) : '';

        $post_id = (element('post_id', $sender)) ? element('post_id', $sender) : 0;
        $multi_code = (element('multi_code', $sender)) ? element('multi_code', $sender) : '';
        $ssc_key = (element('ssc_key', $sender)) ? element('ssc_key', $sender) : 0;

        $total = count($list);
        if (empty($total)) {
            $return = array(
                'result' => 'error',
                'msg' => '발송할 대상이 없습니다.'
            );
            return $return;
            // return json_encode($return,JSON_UNESCAPED_UNICODE);
        }
        $result = $this->Add($list, $send_phone, '', '[본인인증]', $content, $date2, $total);

        if ($result) {
            $result = $this->SendaligoSMS();

            $this->CI->load->model(array('Sms_send_content_model', 'Sms_send_history_model'));

            if ($result) { //SMS 서버에 접속했습니다.
                $contentdata = array(
                    'ssc_content' => $content,
                    'send_mem_id' => $send_mem_id,
                    'ssc_send_phone' => $send_phone,
                    'ssc_total' => $total,
                    'ssc_datetime' => cdate('Y-m-d H:i:s'),
                    'ssc_memo' => $memo,
                    // 'post_id' => $post_id,
                    // 'multi_code' => $multi_code,
                    'ssc_key' => $ssc_key,

                );
                if ($date) {
                    $contentdata['ssc_booking'] = cdate('Y-m-d H:i:s', strtotime($date));
                }
                $ssc_id = $this->CI->Sms_send_content_model->insert($contentdata);

                $success = 0;
                $fail = 0;
                $count = 0;

                $ssh_memo = '';

                if ($this->Result) {
                    foreach ($this->Result as $result) {

                        

                        $ssh_memo = '';
                        $log = 'error';

                        if ($result->result_code <1) {
                            $hs_code = $result->result_code;
                            $ssh_memo = $result->message;
                            
                            $fail++;
                            $ssh_success = 0;
                        } else {
                            $hs_code = $result->result_code;
                            $ssh_memo =  '전송했습니다. msg_id :'.$result->msg_id;
                            $success++;
                            $ssh_success = 1;
                            $log = $result->msg_id ;
                        }

                        $row = array_shift($list);
                        $row['phone'] = $row['phone'];

                        
                        
                        

                        $insertdata = array(
                            'ssc_id' => $ssc_id,
                            'send_mem_id' => $send_mem_id,
                            'recv_mem_id' => element('mem_id', $row),
                            'ssh_name' => element('name', $row),
                            'ssh_phone' => element('phone', $row),
                            'ssh_success' => $ssh_success,
                            'ssh_datetime' => cdate('Y-m-d H:i:s'),
                            'ssh_memo' => $ssh_memo,
                            'ssh_log' => $log,
                            // 'post_id' => $post_id,
                            // 'multi_code' => $multi_code,
                            'ssh_key' => $ssc_key,
                        );
                        $ssh_id = $this->CI->Sms_send_history_model->insert($insertdata);
                    }
                }
                $this->Init(); // 보관하고 있던 결과값을 지웁니다.

                $updatedata = array(
                    'ssc_success' => $success,
                    'ssc_fail' => $fail,
                );
                $this->CI->Sms_send_content_model->update($ssc_id, $updatedata);

                if(empty($ssh_id)){

                    $return = array(
                        'result' => 'error',
                        'msg' => '에러: SMS 서버와 통신이 불안정합니다.'
                    );
                } else {

                    if($ssh_success){
                        $return = array(
                            'result' => 'success',
                            'msg' => '발송이 완료되었습니다.'
                        );    
                    } else {
                        $return = array(
                            'result' => 'error',
                            'msg' => $ssh_memo,
                        );    
                    }
                    
                    
                }
                
                return $return;
                // return json_encode($return,JSON_UNESCAPED_UNICODE);

            } else {
                $return = array(
                    'result' => 'error',
                    'msg' => '에러: SMS 서버와 통신이 불안정합니다.'
                );
                return $return;
                // return json_encode($return,JSON_UNESCAPED_UNICODE);

            }
        } else {
            $return = array(
                'result' => 'error',
                'msg' => '에러: SMS 데이터 입력도중 에러가 발생하였습니다.'
            );
            return $return;
            // return json_encode($return,JSON_UNESCAPED_UNICODE);
        }
    }


    public function SMS_con()
    {
        $this->ID = $this->CI->cbconfig->item('sms_icode_id');
        $this->PWD = $this->CI->cbconfig->item('sms_icode_pw');
        
        $this->SMS_Server = 'https://apis.aligo.in/send/';
        $this->SMS_Port = $this->CI->cbconfig->item('sms_icode_port');
        // $this->ID = $this->spacing($this->ID,10);
        // $this->PWD = $this->spacing($this->PWD,10);
    }


     /**
     * 발송번호의 값이 정확한 값인지 확인합니다.
     *
     * @param strDest 발송번호 배열입니다.
     *                nCount 배열의 크기입니다.
     * @return 처리결과입니다.
     */
    public function CheckCommonTypeDest($strDest, $nCount)
    {
        for ($i = 0; $i < $nCount; $i++) {
            $hp_number = preg_replace("/[^0-9]/", '', $strDest[$i]['phone']);

            if (strlen($hp_number) < 10 OR strlen($hp_number) > 11) {
                return '휴대폰 번호가 틀렸습니다';
            }

            $CID = substr($hp_number,0,3);

            if ( preg_match("/[^0-9]/", $CID) OR ($CID !== '010' && $CID !== '011' && $CID !== '016' && $CID !== '017' && $CID !== '018' && $CID !== '019')) {
                return '휴대폰 앞자리 번호가 잘못되었습니다';
            }
        }
    }


    /**
     * 회신번호의 값이 정확한 값인지 확인합니다.
     */
    public function CheckCommonTypeCallBack($strCallBack)
    {
        if (preg_match("/[^0-9]/", $strCallBack)) {
            return '회신 전화번호가 잘못되었습니다';
        }
    }


     /**
     * 예약날짜의 값이 정확한 값인지 확인합니다.
     */
    public function CheckCommonTypeDate($strDate)
    {
        $strDate = preg_replace("/[^0-9]/", '', $strDate);

        if ($strDate) {
            if (checkdate(substr($strDate,4,2), substr($strDate,6,2), substr($strDate,0,4)) === false) {
                return '예약날짜가 잘못되었습니다';
            }
            if (substr($strDate,8,2) >23 OR substr($strDate,10,2) >59) {
                return '예약시간이 잘못되었습니다';
            }
        }
    }


     /**
     * URL콜백용으로 메세지 크기를 수정합니다.
     *
     * @param    url        URL 내용입니다.
     *            msg        결과메시지입니다.
     *            desk    문자내용입니다.
     */
    public function CheckCallCenter($url, $dest, $data)
    {
        switch (substr($dest,0,3)) {
            case '010': //20바이트
                return $this->cut_char($data,20);
                break;
            case '011': //80바이트
                return $this->cut_char($data,80);
                break;
            case '016': // 80바이트
                return $this->cut_char($data,80);
                break;
            case '017': // URL 포함 80바이트
                return $this->cut_char($data,80 - strlen($url));
                break;
            case '018': // 20바이트
                return $this->cut_char($data,20);
                break;
            case '019': // 20바이트
                return $this->cut_char($data,20);
                break;
            default:
                return $this->cut_char($data,80);
                break;
        }
    }


    public function Add($strDest, $strCallBack, $strCaller, $strURL, $strMessage, $strDate = '', $nCount)
    {
        $strCallBack = str_replace('-', '', $strCallBack);

        $Error = $this->CheckCommonTypeDest($strDest, $nCount);
        $Error = $this->CheckCommonTypeCallBack($strCallBack);
        $Error = $this->CheckCommonTypeDate($strDate);

        $strCallBack = $this->spacing($strCallBack,11);
        $strCaller = $this->spacing($strCaller,10);
        $strDate = $this->spacing($strDate,12);

        for ($i = 0; $i < $nCount; $i++) {
            $hp_number = $this->spacing($strDest[$i]['phone'],11);
            $strData = $strMessage;

            if ( ! empty($strDest[$i]['name'])) {
                $strData = str_replace('{이름}', $strDest[$i]['name'], $strData);
            }

            // 아이코드에서는 문자에 utf-8 인코딩 형식을 아직 지원하지 않는다.
            // $strData = iconv('utf-8', 'euc-kr', stripslashes($strData));

            // if (empty($strURL)) {
            //     $strData = $this->spacing($this->cut_char($strData,80),80);
            //     $this->Data[$i] = '01144 ' . $this->ID . $this->PWD . $hp_number . $strCallBack . $strCaller . $strDate . $strData;
            // } else {
            //     $strURL = $this->spacing($strURL,50);
            //     $strData = $this->spacing($this->CheckCallCenter($strURL, $hp_number, $strData),80);

            //     $this->Data[$i] = '05173 ' . $this->ID . $this->PWD . $hp_number . $strCallBack . $strURL . $strDate . $strData;
            // }
            
            $this->Data[$i] = array(
                'receiver' => $hp_number,
                'sender' => $strCallBack,
                'title' => $strURL,
                'msg' => $strData,
                );
        }
        return true; // 수정대기
    }


    public function SendSMS()
    {
        $count = 1;

        $is_test = false;
        
        if ($is_test) {
            if ($this->Data) {
                foreach ($this->Data as $puts) {
                    if (rand(0,10)) {
                        $phone = substr($puts,26,11);
                        $code = '47022497 ';
                    } else {
                        $phone = substr($puts,26,11);
                        $code = 'Error(02)';
                    }
                    $this->Result[] = "$phone:$code";
                    $this->Log[] = $puts;
                }
            }
            $this->Data = '';

            return true;
        }

        $fsocket = fsockopen($this->SMS_Server, $this->SMS_Port);
        if (empty($fsocket)) {
            return false;
        }
        set_time_limit(300);

        /*
         * php4.3.10일경우
         * zend 최신버전으로 업해주세요..
         * 또는 69번째 줄을 $this->Data as $tmp => $puts 로 변경해 주세요.
         */

        foreach ($this->Data as $puts) {
            $dest = substr($puts,26,11);
            fputs($fsocket, $puts);
            while (empty($gets)) {
                $gets = fgets($fsocket,30);
            }
            if (substr($gets,0,19) === '0223 00' . $dest) {
                $this->Result[] = $dest . ':' . substr($gets,19,10);
                $this->Log[] = $puts;
            } else {
                $this->Result[$dest] = $dest . ':Error(' . substr($gets,6,2) . ')';
                $this->Log[] = $puts;
            }
            $gets = '';

            // 1천건씩 전송 후 5초 쉼
            if ($count++%1000 === 0) {
                sleep(5);
            }
        }
        fclose($fsocket);
        $this->Data = '';
        return true;
    }


    public function spacing($text, $size)
    {
        for ($i = 0; $i < $size; $i++) {
            $text .= ' ';
        }
        $text = substr($text,0, $size);
        return $text;
    }


    public function cut_char($word, $cut)
    {
        $word = substr($word,0, $cut);                        // 필요한 길이만큼 취함.
        for ($k = $cut-1; $k > 1; $k--) {
            if (ord(substr($word, $k,1)) < 128) {
                break;        // 한글값은 160 이상.
            }
        }
        $word = substr($word,0, $cut-($cut-$k+1)%2);
        return $word;
    }


    public function CheckCommonType($dest, $rsvTime)
    {
        $dest = preg_replace("/[^0-9]/i", '', $dest);
        if (strlen($dest) < 10 OR strlen($dest) >11) {
            return '휴대폰 번호가 틀렸습니다';
        }
        $CID = substr($dest,0,3);
        if ( preg_match("/[^0-9]/i", $CID) OR ($CID !== '010' && $CID !== '011' && $CID !== '016' && $CID !== '017' && $CID !== '018' && $CID !== '019')) {
            return '휴대폰 앞자리 번호가 잘못되었습니다';
        }
        $rsvTime = preg_replace("/[^0-9]/i", '', $rsvTime);
        if ($rsvTime) {
            if (checkdate(substr($rsvTime,4,2), substr($rsvTime,6,2), substr($rsvTime,0,4)) === false) {
                return '예약날짜가 잘못되었습니다';
            }
            if (substr($rsvTime,8,2) >23 OR substr($rsvTime,10,2) >59) {
                return '예약시간이 잘못되었습니다';
            }
        }
    }

    public function SendaligoSMS()
    {
        $count = 1;

        $is_test = false;


        /*
         * php4.3.10일경우
         * zend 최신버전으로 업해주세요..
         * 또는 69번째 줄을 $this->Data as $tmp => $puts 로 변경해 주세요.
         */

        $host_info = explode("/",  $this->SMS_Server);
        $port = $host_info[0] == 'https:' ? 443 : 80;

        $sms['user_id'] = $this->ID; // SMS 아이디
        $sms['key'] = $this->PWD;//인증키
        

        foreach ($this->Data as $val) {

            $sms['msg'] = stripslashes(element('msg',$val));
            $sms['receiver'] = element('receiver',$val);
            $sms['destination'] = '';
            $sms['sender'] = element('sender',$val);
            $sms['title'] = element('title',$val);
            $sms['testmode_yn'] = 'N';
            $sms['msg_type'] = 'SMS';

            
            $oCurl = curl_init();
            curl_setopt($oCurl, CURLOPT_PORT, $port);
            curl_setopt($oCurl, CURLOPT_URL, $this->SMS_Server);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sms);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            $ret = curl_exec($oCurl);
            curl_close($oCurl);

            $retArr = json_decode($ret);
            $this->Result[] = $retArr;
            // 1천건씩 전송 후 5초 쉼
            if ($count++%1000 === 0) {
                sleep(5);
            }
        }
        
        $this->Data = '';
        return true;
    }


}
