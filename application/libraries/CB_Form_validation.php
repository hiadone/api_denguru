<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Form Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/form_validation.html
 */
/*
CI_Form_validation 에서 is_unique 변형
db 업데이트시 자기 자신의 값과는 비교하지 않음
예를 들어 email 이 is_unique 임을 체크할 때
본인의 이메일을 수정 가능하다고 할 때, 기존 자기 이메일의 값과는 비교하지 않음
*/

class CB_Form_validation extends CI_Form_validation
{

	protected $CI;
	protected $_field_data = array();
	protected $_config_rules = array();
	protected $_error_array = array();
	protected $_error_messages = array();
	protected $_error_prefix = '<p>';
	protected $_error_suffix = '</p>';
	protected $error_string = '';
	protected $_safe_form_data = false;

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 *
	 * @access public
	 * @param string
	 * @param field
	 * @return bool
	 */
	public function is_unique($str, $field)
	{
		if (substr_count($field, '.') === 3) {
			list($table, $field, $id_field, $id_val) = explode('.', $field);
			$query = $this->CI->db->limit(1)->where($field, $str)->where($id_field . ' != ', $id_val)->get($table);
		} else {
			list($table, $field) = explode('.', $field);
			$query = $this->CI->db->limit(1)->get_where($table, array($field => $str));
		}
		return $query->num_rows() === 0;
	}
	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access public
	 * @param string
	 * @return bool
	 */
	public function alphanumunder($str)
	{
		return ( ! preg_match("/^([-a-z0-9_])+$/i", $str)) ? false : true;
	}

	public function valid_url($str)
	{
		$pattern = "|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i";
		if ( ! preg_match($pattern, $str)) {
			return false;
		}

		return true;
	}

	public function valid_phone($value)
	{
		$value = trim($value);
		if ($value === '') {
			return true;
		} else {
			if (preg_match('/^\(?[0-9]{2,3}\)?[-. ]?[0-9]{3,4}[-. ]?[0-9]{4}$/', $value)) {
				return preg_replace('/^\(?([0-9]{2,3})\)?[-. ]?([0-9]{3,4})[-. ]?([0-9]{4})$/', '$1-$2-$3', $value);
			} else {
				return false;
			}
		}
	}

	public function valid_mobile($value)
	{
        $value = trim($value);
        if ($value === '') {
            return '';
        } else {
            if(substr($value,0,3) =="010"){
                if (preg_match('/^\(?[0-9]{3}\)?[-. ]?[0-9]{4}[-. ]?[0-9]{4}$/', $value)) {
                    return preg_replace('/^\(?([0-9]{3})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/', '$1-$2-$3', $value);
                } else {
                    return false;
                }
            } else {
                if (preg_match('/^\(?[0-9]{2,3}\)?[-. ]?[0-9]{3,4}[-. ]?[0-9]{4}$/', $value)) {
                    return preg_replace('/^\(?([0-9]{2,3})\)?[-. ]?([0-9]{3,4})[-. ]?([0-9]{4})$/', '$1-$2-$3', $value);
                } else {
                    return false;
                }
            }
            
            
        }
    }

	public function set_rules($field, $label = '', $rules = array(), $errors = array())
	{
		// No reason to set rules if we have no POST data
		// or a validation array has not been specified
		if ($this->CI->input->method() !== 'post' && $this->CI->input->method() !== 'put' && empty($this->validation_data))
		{
			return $this;
		}

		// If an array was passed via the first parameter instead of individual string
		// values we cycle through it and recursively call this function.
		if (is_array($field))
		{
			foreach ($field as $row)
			{
				// Houston, we have a problem...
				if ( ! isset($row['field'], $row['rules']))
				{
					continue;
				}

				// If the field label wasn't passed we use the field name
				$label = isset($row['label']) ? $row['label'] : $row['field'];

				// Add the custom error message array
				$errors = (isset($row['errors']) && is_array($row['errors'])) ? $row['errors'] : array();

				// Here we go!
				$this->set_rules($row['field'], $label, $row['rules'], $errors);
			}

			return $this;
		}

		// No fields or no rules? Nothing to do...
		if ( ! is_string($field) OR $field === '' OR empty($rules))
		{
			return $this;
		}
		elseif ( ! is_array($rules))
		{
			// BC: Convert pipe-separated rules string to an array
			if ( ! is_string($rules))
			{
				return $this;
			}

			$rules = preg_split('/\|(?![^\[]*\])/', $rules);
		}

		// If the field label wasn't passed we use the field name
		$label = ($label === '') ? $field : $label;

		$indexes = array();

		// Is the field name an array? If it is an array, we break it apart
		// into its components so that we can fetch the corresponding POST data later
		if (($is_array = (bool) preg_match_all('/\[(.*?)\]/', $field, $matches)) === TRUE)
		{
			sscanf($field, '%[^[][', $indexes[0]);

			for ($i = 0, $c = count($matches[0]); $i < $c; $i++)
			{
				if ($matches[1][$i] !== '')
				{
					$indexes[] = $matches[1][$i];
				}
			}
		}

		// Build our master array
		$this->_field_data[$field] = array(
			'field'		=> $field,
			'label'		=> $label,
			'rules'		=> $rules,
			'errors'	=> $errors,
			'is_array'	=> $is_array,
			'keys'		=> $indexes,
			'postdata'	=> NULL,
			'error'		=> ''
		);

		return $this;
	}

	public function run($group = '')
	{	
		
		$validation_array = empty($this->validation_data)
			? $this->CI->input->method() === 'post' ? $_POST : $this->CI->put()
			: $this->validation_data;

		// Does the _field_data array containing the validation rules exist?
		// If not, we look to see if they were assigned via a config file
		if (count($this->_field_data) === 0)
		{
			// No validation rules?  We're done...
			if (count($this->_config_rules) === 0)
			{
				return FALSE;
			}

			if (empty($group))
			{
				// Is there a validation rule for the particular URI being accessed?
				$group = trim($this->CI->uri->ruri_string(), '/');
				isset($this->_config_rules[$group]) OR $group = $this->CI->router->class.'/'.$this->CI->router->method;
			}

			$this->set_rules(isset($this->_config_rules[$group]) ? $this->_config_rules[$group] : $this->_config_rules);

			// Were we able to set the rules correctly?
			if (count($this->_field_data) === 0)
			{
				log_message('debug', 'Unable to find validation rules');
				return FALSE;
			}
		}

		// Load the language file containing error messages
		$this->CI->lang->load('form_validation');

		// Cycle through the rules for each field and match the corresponding $validation_data item
		foreach ($this->_field_data as $field => &$row)
		{
			// Fetch the data from the validation_data array item and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.
			if ($row['is_array'] === TRUE)
			{
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($validation_array, $row['keys']);
			}
			elseif (isset($validation_array[$field]))
			{
				$this->_field_data[$field]['postdata'] = $validation_array[$field];
			}
		}

		// Execute validation rules
		// Note: A second foreach (for now) is required in order to avoid false-positives
		//	 for rules like 'matches', which correlate to other validation fields.
		foreach ($this->_field_data as $field => &$row)
		{
			// Don't try to validate if we have no rules set
			if (empty($row['rules']))
			{
				continue;
			}

			$this->_execute($row, $row['rules'], $row['postdata']);
		}

		// Did we end up with any errors?
		$total_errors = count($this->_error_array);
		if ($total_errors > 0)
		{
			$this->_safe_form_data = TRUE;
		}

		// Now we need to re-set the POST data with the new, processed data
		empty($this->validation_data) && $this->_reset_post_array();

		return ($total_errors === 0);
	}
}
