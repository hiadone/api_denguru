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
class CI_Jwt {

    /**
     * Userdata array
     *
     * Just a reference to $_SESSION, for BC purposes.
     */
    public $userdata;
    private $CI;
    protected $_config;
    protected $_sid_regexp;

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

        // Configuration ...
        $this->_configure($params);


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
        $expiration = config_item('token_timeout');
        $params['default_Authorization'] = config_item('default_Authorization');

        if (isset($params['cookie_lifetime']))
        {
            $params['cookie_lifetime'] = (int) $params['cookie_lifetime'];
        }
        else
        {
            $params['cookie_lifetime'] = ( ! isset($expiration) && config_item('sess_expire_on_close'))
                ? 0 : (int) $expiration;
        }

        
        if (empty($expiration))
        {
            $params['expiration'] = 1;
        }
        else
        {
            $params['expiration'] = (int) $expiration;            
        }

        $this->_config = $params;        
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
        $headers = $this->CI->input->request_headers();
        
        $jwt=array();
        
        $data = $this->_verify_request($headers);            
        

        foreach ($data as $key => $value)
        {   
            $jwt[$key] = $value;
        }
        
        $this->userdata = $jwt;

    }

    private function _verify_request($headers)
    {
        
        if(empty($headers['Authorization']))
            $headers['Authorization'] = $this->_config['default_Authorization'];
        $token = $headers['Authorization'];

        

        
        // Use try-catch
        // JWT library throws exception if the token is not valid
        try {
            // Validate the token
            // Successfull validation will return the decoded user data else returns false
            $data = AUTHORIZATION::validateToken($token);
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
    public function __get($key)
    {
        // Note: Keep this order the same, just in case somebody wants to
        //       use 'session_id' as a session data key, for whatever reason
        if (isset($_SESSION[$key]))
        {
            return $_SESSION[$key];
        }
        elseif ($key === 'session_id')
        {
            return session_id();
        }

        return NULL;
    }

    // ------------------------------------------------------------------------

    /**
     * __isset()
     *
     * @param   string  $key    'session_id' or a session data key
     * @return  bool
     */
    public function __isset($key)
    {
        if ($key === 'session_id')
        {
            return (session_status() === PHP_SESSION_ACTIVE);
        }

        return isset($_SESSION[$key]);
    }

    // ------------------------------------------------------------------------

    /**
     * __set()
     *
     * @param   string  $key    Session data key
     * @param   mixed   $value  Session data value
     * @return  void
     */
    public function __set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

   
   

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

}
