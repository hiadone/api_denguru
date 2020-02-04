<?php
   
require APPPATH . 'core/REST_Controller.php';


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
  
class Api_test extends REST_Controller {
    
      /**
     * Get All Data from this method.
     *
     * @return Response
    */
   
   protected $models = array('Post', 'Post_meta', 'Post_extra_vars');

    public function __construct() {
       parent::__construct();
       
    }
       
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
   

    public function index_get($post_id = 0)
    {
        
        $eventname = 'event_board_post_post';
        $this->load->event($eventname);

        $view = array();
        $view['view'] = array();

        // 이벤트가 존재하면 실행합니다
        $view['view']['event']['before'] = Events::trigger('before', $eventname);

        /**
         * 프라이머리키에 숫자형이 입력되지 않으면 에러처리합니다
         */
        $post_id = (int) $post_id;
        
        if (empty($post_id) OR $post_id < 1) {

            show_404();
        }

        $post = $this->Post_model->get_one($post_id);

        
     
        $this->response($post, REST_Controller::HTTP_BAD_REQUEST);
    }
      
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_post()
    {
        $input = $this->input->post();
        $this->db->insert('items',$input);
     
        $this->response(['Item created successfully.'], REST_Controller::HTTP_OK);
    } 
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_put($id)
    {
        $input = $this->put();
        $this->db->update('items', $input, array('id'=>$id));
     
        $this->response(['Item updated successfully.'], REST_Controller::HTTP_OK);
    }
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_delete($id)
    {
        $this->db->delete('items', array('id'=>$id));
       
        $this->response(['Item deleted successfully.'], REST_Controller::HTTP_OK);
    }
    
    public function hello_get()
       {
           $tokenData = 'Hello World!';
           
           // Create a token
           $token = AUTHORIZATION::generateToken($tokenData);
           // Set HTTP status code
           $status = parent::HTTP_OK;
           // Prepare the response
           $response = ['status' => $status, 'token' => $token];
           // REST_Controller provide this method to send responses
           $this->response($response, $status);
       }

}