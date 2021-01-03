<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package CodeIgniter
 * @author  EllisLab Dev Team
 * @copyright   Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright   Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT  MIT License
 * @link    https://codeigniter.com
 * @since   Version 2.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Session Class
 *
 * @package     CodeIgniter
 * @subpackage  Libraries
 * @category    Sessions
 * @author      Andrey Andreev
 * @link        https://codeigniter.com/user_guide/libraries/sessions.html
 */
class CB_Jwt {

    /**
     * Userdata array
     *
     * Just a reference to $_SESSION, for BC purposes.
     */
    public $userdata;
    private $CI;
    protected $_token;    

    // ------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * @param   array   $params Configuration parameters
     * @return  void
     */
    public function __construct(array $params = array())
    {   
        // No sessions under CLI
        if (is_cli())
        {
            log_message('debug', 'Jwt: Initialization under CLI aborted.');
            return;
        }        
        

        $this->CI = & get_instance();

        $this->CI->load->helper('array');

        // Configuration ...
        $this->_token = $this->_configure($params);

        
        $this->_ci_init_vars();

        log_message('info', "Jwt:  initialized ");
    }

    // ------------------------------------------------------------------------

    /**
     * CI Load Classes
     *
     * An internal method to load all possible dependency and extension
     * classes. It kind of emulates the CI_Driver library, but is
     * self-sufficient.
     *
     * @param   string  $driver Driver name
     * @return  string  Driver class name
     */
    

    // ------------------------------------------------------------------------

    /**
     * Configuration
     *
     * Handle input parameters and configuration defaults
     *
     * @param   array   &$params    Input parameters
     * @return  void
     */
    protected function _configure($params)
    {   

        $headers = $this->CI->input->request_headers();

        if(empty($headers['Authorization']))
            return AUTHORIZATION::validateToken(config_item('default_Authorization'));

        $token = $headers['Authorization'];

        $token = AUTHORIZATION::validateToken($token);     
        
echo "0<br>";
        if(!empty($token)){
            if(!empty($token->timestamp)){
                echo "a<br>";
                if ($token != false && !empty($token->timestamp) && (ctimestamp() - $token->timestamp < (config_item('token_timeout') * 60))) {                     
                    echo "b<br>";
                    return $token;
                } elseif(!empty($token)){   
                    echo "c<br>";
                    $this->CI->load->database();                 
                    $this->CI->db->select('mem_id,jwt_refresh_token');            
                    $this->CI->db->from('jwt');
                    $this->CI->db->where('mem_Id', $token->mem_id);
                    $this->CI->db->limit(1,0);
                    $result = $this->CI->db->get();
                    $jwt = $result->row_array();

                    $refresh_token = AUTHORIZATION::validateToken(element('jwt_refresh_token',$jwt));

                    if ($refresh_token != false && !empty($refresh_token->timestamp) && (ctimestamp() - $refresh_token->timestamp < (config_item('refresh_token_timeout') * 60))) {
                        
                        $tokenData = array();
                        $tokenData['mem_id'] = element('mem_id',$jwt); //TODO: Replace with data for token
                        $tokenData['timestamp'] = ctimestamp(); //TODO: Replace with data for token
                        $output['token'] = AUTHORIZATION::generateToken($tokenData);                        
                        
                        $this->CI->db->where('mem_id', element('mem_id',$jwt));
                        $this->CI->db->set(array('jwt_refresh_token'=>element('token', $output),'jwt_datetime'=>cdate('Y-m-d H:i:s')));
                        $result = $this->CI->db->update('jwt');
                        return $refresh_token;                        

                    } else {                        
                        echo "c<br>";          
                        return AUTHORIZATION::validateToken(config_item('default_Authorization'));
                    }

                } 
            } else {      
             echo "g<br>";          
                return AUTHORIZATION::validateToken(config_item('default_Authorization'));
            }
        }
        

        return false;
        
    }

   
    // ------------------------------------------------------------------------

    /**
     * Handle temporary variables
     *
     * Clears old "flash" data, marks the new one for deletion and handles
     * "temp" data deletion.
     *
     * @return  void
     */
    protected function _ci_init_vars()
    {
        
        
        $jwt=array();
        
        $data = $this->_verify_request();            
        


        foreach ($data as $key => $value)
        {   
            $jwt[$key] = $value;
        }
        
        $this->userdata = $jwt;

    }

    private function _verify_request()
    {
        
        

        

        
        // Use try-catch
        // JWT library throws exception if the token is not valid
        try {
            // Validate the token
            // Successfull validation will return the decoded user data else returns false
            $data = $this->_token;
            
            if ($data === false) {
                $status = 300;
                $response = ['status' => $status, 'msg' => 'Unauthorized Access!'];
                $this->CI->output->set_status_header($status);
                exit(json_encode($response));
                
                
            } else {
                return $data;
            }
        } catch (Exception $e) {
            // Token is invalid
            // Send the unathorized access message
            $status = 400;
            $response = ['status' => $status, 'msg' => 'Unauthorized Access! '];
            // $this->response($response, $status);
             $this->CI->output->set_status_header($status);
            exit(json_encode($response));
            
        }
    }

    // ------------------------------------------------------------------------


    /**
     * __get()
     *
     * @param   string  $key    'session_id' or a session data key
     * @return  mixed
     */
    

   
   

    // ------------------------------------------------------------------------

    /**
     * Get userdata reference
     *
     * Legacy CI_Session compatibility method
     *
     * @returns array
     */
    public function get_userdata()
    {
        return $this->userdata;
    }

    // ------------------------------------------------------------------------

    /**
     * Get flash keys
     *
     * @return  array
     */
    public function get_userdata_keys()
    {
        if ( ! isset($this->userdata))
        {
            return array();
        }

        $keys = array();
        foreach (array_keys($this->userdata) as $key)
        {
            $keys[] = $key;
        }

        return $keys;
    }

    // ------------------------------------------------------------------------

    
    
    


    /**
     * Userdata (fetch)
     *
     * Legacy CI_Session compatibility method
     *
     * @param   string  $key    Session data key
     * @return  mixed   Session data value or NULL if not found
     */
    public function userdata($key = NULL)
    {
        if (isset($key))
        {
            return isset($this->userdata[$key]) ? $this->userdata[$key] : NULL;
        }
        elseif (empty($this->userdata))
        {
            return array();
        } else {
            return $this->userdata;
        }

        $userdata = array();
        

        if($this->userdata[$key]) $userdata = $this->userdata[$key];
        else $userdata='';
        

        return $userdata;
    }

    
    public function set_userdata($data, $value = NULL)
    {
        if (is_array($data))
        {
            foreach ($data as $key => &$value)
            {
                $this->userdata[$key] = $value;
            }

            return;
        }

        $this->userdata[$data] = $value;
    }

    public function validateTimestamp($token)
    {
        
        
        
        
    }

}
