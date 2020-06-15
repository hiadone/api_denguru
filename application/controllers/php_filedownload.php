protected function _reviewwrite($cit_id = 0, $cre_id = 0)
    {

        /**
         * 로그인이 필요한 페이지입니다
         */
        required_user_login();


        $view = array();
        $view['view'] = array();

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
        
        if ($cit_id) {
            $item = $this->Cmall_item_model->get_one($cit_id,'cit_id,cit_status');
            
            
            if ( ! element('cit_id', $item) )
                alert('이 상품은 현재 존재하지 않습니다',"",406);

            if(! element('cit_status', $item)) 
                alert('이 상품은 현재 판매하지 않습니다',"",406);
            

            $item = $this->denguruapi->get_cit_info($cit_id);
        }
        /**
         * 수정 페이지일 경우 기존 데이터를 가져옵니다
         */
        $getdata = array();
        if ($cre_id) {
            $getdata = $this->Cmall_review_model->get_one($cre_id);
            if ( ! element('cre_id', $getdata)) {
                alert('이 리뷰는 현재 존재하지 않습니다',"",406);
            }
            $is_admin = $this->member->is_admin();
            if ($is_admin === false
                && (int) element('mem_id', $getdata) !== $mem_id) {
                alert_close('본인의 글 외에는 접근하실 수 없습니다');
            }

            $getdata = $this->denguruapi->convert_review_info($getdata);
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
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'cre_bad',
                'label' => '아쉬운 점',
                'rules' => 'trim|required',
            ),
            array(
                'field' => 'cre_tip',
                'label' => '나만의 팁',
                'rules' => 'trim',
            ),
            array(
                'field' => 'cre_score',
                'label' => '평점',
                'rules' => 'trim|required|numeric|is_natural_no_zero|greater_than_equal_to[1]|less_than_equal_to[5]',
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
            log_message('error', $file);
            log_message('error', $post);
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
                        $uploadconfig['allowed_types'] = 'jpg|jpeg|png|gif|mp4|m4v|f4v|mov|flv|webm';
                        $uploadconfig['max_size'] = '100000';
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
                        $uploadconfig['allowed_types'] = 'jpg|jpeg|png|gif|mp4|m4v|f4v|mov|flv|webm';
                        $uploadconfig['max_size'] = '100000';
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
                            if ((int) element('cre_id', $oldcrefile) !== (int) element('cre_id', $post)) {
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
            

            $view['msg'] = validation_errors().$file_error;

            $view['view']['wishlist_url'] = base_url('cmall/wishlist');
            $view['view']['itemlists_url'] = base_url('cmall/itemlists');
            $view['view']['data']['review'] = $getdata;
            $view['view']['data']['item'] = $item;

            /**
             * primary key 정보를 저장합니다
             */
            $view['view']['primary_key'] = $primary_key;

            $view['http_status_codes'] = parent::HTTP_OK;

            
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
            );

            

            /**
             * 게시물을 수정하는 경우입니다
             */
            $param =& $this->querystring;
            $page = (((int) $this->input->get('page')) > 0) ? ((int) $this->input->get('page')) : 1;



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
                    if ($val === '1' && ! isset($uploadfiledata2[$key])) {
                        $oldcrefile = $this->Review_file_model->get_one($key);
                        if ( ! element('cre_id', $oldcrefile) OR (int) element('cre_id', $oldcrefile) !== (int) element('cre_id', $item)) {
                            alert('잘못된 접근입니다.');
                        }
                        @unlink(config_item('uploads_dir') . '/cmall_review/' . element('rfi_filename', $oldcrefile));

                        $deleted = $this->aws_s3->delete_file(config_item('s3_folder_name') . '/cmall_review/' . element('rfi_filename', $oldcrefile));
                        $this->Review_file_model->delete($key);
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