<?php
defined('BASEPATH') OR exit('No direct script access allowed');

     



class Api_test extends CB_Controller {
    
      /**
     * Get All Data from this method.
     *
     * @return Response
    */
   
   protected $models = array('Post', 'Post_meta', 'Post_extra_vars');

   protected $helpers = array('jwt', 'authorization');

    public function __construct() {
       parent::__construct();
    }
       
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
   

    public function index_get($post_id)
    {
        
        echo 'get';

        $input = $this->input->get();
       

        print_r($input);

     
       

       
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

        
     
        return $this->response($post, parent::HTTP_OK);

        
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
     
        $this->response(['Item created successfully.'], parent::HTTP_OK);
    } 
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_put($id)
    {
        echo 'put';

        $input = $this->put();
        print_r($input);
     
        $this->response(['Item updated successfully.'], parent::HTTP_OK);
    }
     
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index_delete($id)
    {
echo "delete";
        
        $input = $this->delete();
        print_r($input);   
       
        $this->response(['Item deleted successfully.'], parent::HTTP_OK);
    }

    public function hello_get()
       {

        

           $tokenData = 'Hello World!';
           
           // Create a token
           $token = AUTHORIZATION::generateToken($tokenData);
           // Set HTTP status code
           $status = parent::HTTP_OK;

           $this->output->set_header($token,'Authorization');
            
           // Prepare the response
           $response = ['status' => $status, 'token' => $token];
           // REST_Controller provide this method to send responses
            return $this->response($response, $status);
           
           
       }
    private function verify_request()
    {
        // Get all the headers
        $headers = $this->input->request_headers();
        print_r($headers);
        // Extract the token
        $token = $headers['Authorization'];

        // $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6IlRlc3QifQ.4uDjO81vPhxUBtJvBz2ziDEBfDKI9-0mIffc98aJb2E";

        
        // Use try-catch
        // JWT library throws exception if the token is not valid
        try {
            // Validate the token
            // Successfull validation will return the decoded user data else returns false
            $data = AUTHORIZATION::validateToken($token);
            if ($data === false) {
                $status = parent::HTTP_UNAUTHORIZED;
                $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
                $this->response($response, $status);
                exit();
            } else {
                return $data;
            }
        } catch (Exception $e) {
            // Token is invalid
            // Send the unathorized access message
            $status = parent::HTTP_UNAUTHORIZED;
            $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
            $this->response($response, $status);
        }
    }

    public function get_me_data_post()
    {
        // Call the verification method and store the return value in the variable
        $data = $this->verify_request();
        // Send the return data as reponse
        $status = parent::HTTP_OK;
        $response = ['status' => $status, 'data' => $data];
        $this->response($response, $status);

    }

   

    public function login_post()
     {
            // Have dummy user details to check user credentials
            // send via postman
            $dummy_user = [
                'username' => 'Test',
                'password' => 'test'
            ];
            // Extract user data from POST request
            $username = $this->input->post('username');
            $password = $this->input->post('password');

print_r($this->input->post());
            // Check if valid user
            if ($username === $dummy_user['username'] && $password === $dummy_user['password']) {
                
                // Create a token from the user data and send it as reponse
                $token = AUTHORIZATION::generateToken(['username' => $dummy_user['username']]);
                // Prepare the response
                $status = parent::HTTP_OK;
                $response = ['status' => $status, 'token' => $token];
                $this->response($response, $status);
            }
            else {
                $this->response(['msg' => 'Invalid username or password!'], parent::HTTP_NOT_FOUND);
            }

    }   
}