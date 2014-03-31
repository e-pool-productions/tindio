<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* MY_Form_validation extension
*
* Allows callback functions into Models
*
* Usage:
* Pass the caller to the validation class
* $this->validation->run($this);
*
* Version 0.3 (c) Wiredesignz 2008-04-24
* 
* Version 0.4 modified by Bradford Mar 2008-08-27
*/

class MY_Form_validation extends CI_Form_validation
{

    function get_fields()
    {
        return $this->_fields;
    }

    /**
     * Run the Validator
     *
     * This function does all the work.
     *
     * @access    public
     * @return    bool
     */        
    function run($group = '', &$parent = NULL)
    {
        // Do we even have any data to process?  Mm?
        if (count($_POST) == 0)
        {
            return FALSE;
        }

        isset($parent) OR $parent = $this->CI;

        // Does the _field_data array containing the validation rules exist?
        // If not, we look to see if they were assigned via a config file
        if (count($this->_field_data) == 0)
        {
            // No validation rules?  We're done...
            if (count($this->_config_rules) == 0)
            {
                return FALSE;
            }

            // Is there a validation rule for the particular URI being accessed?
            $uri = ($group == '') ? trim($parent->uri->ruri_string(), '/') : $group;

            if ($uri != '' AND isset($this->_config_rules[$uri]))
            {
                $this->set_rules($this->_config_rules[$uri]);
            }
            else
            {
                $this->set_rules($this->_config_rules);
            }

            // We're we able to set the rules correctly?
            if (count($this->_field_data) == 0)
            {
                log_message('debug', "Unable to find validation rules");
                return FALSE;
            }
        }

        // Load the language file containing error messages
        $parent->lang->load('form_validation');

        // Cycle through the rules for each field, match the 
        // corresponding $_POST item and test for errors
        foreach ($this->_field_data as $field => $row)
        {        
            // Fetch the data from the corresponding $_POST array and cache it in the _field_data array.
            // Depending on whether the field name is an array or a string will determine where we get it from.

            if ($row['is_array'] == TRUE)
            {
                $this->_field_data[$field]['postdata'] = $this->_reduce_array($_POST, $row['keys']);
            }
            else
            {
                if (isset($_POST[$field]))
                {
                    $this->_field_data[$field]['postdata'] = $_POST[$field];
                }
            }

            $this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata'], NULL, $parent);        
        }

        // Did we end up with any errors?
        $total_errors = count($this->_error_array);

        if ($total_errors > 0)
        {
            $this->_safe_form_data = TRUE;
        }

        // Now we need to re-set the POST data with the new, processed data
        $this->_reset_post_array();

        // No errors, validation passes!
        if ($total_errors == 0)
        {
            return TRUE;
        }

        // Validation fails
        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Executes the Validation routines
     *
     * @access    private
     * @param    array
     * @param    array
     * @param    mixed
     * @param    integer
     * @return    mixed
     */    
    function _execute($row, $rules, $postdata = NULL, $cycles = 0, &$parent)
    {
        // If the $_POST data is an array we will run a recursive call
        if (is_array($postdata))
        { 
            foreach ($postdata as $key => $val)
            {
                $this->_execute($row, $rules, $val, $cycles, $parent);
                $cycles++;
            }

            return;
        }

        // --------------------------------------------------------------------

        // If the field is blank, but NOT required, no further tests are necessary
        if ( ! in_array('required', $rules, TRUE) AND is_null($postdata))
        {
            return;
        }

        // --------------------------------------------------------------------

        // Isset Test. Typically this rule will only apply to checkboxes.
        if (is_null($postdata))
        {            
            if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
            {
                if ( ! isset($this->_error_messages['isset']))
                {
                    if (FALSE === ($line = $parent->lang->line('isset')))
                    {
                        $line = 'The field was not set';
                    }                            
                }
                else
                {
                    $line = $this->_error_messages['isset'];
                }

                // Build the error message
                $message = sprintf($line, $row['label']);

                // Save the error message
                $this->_field_data[$row['field']]['error'] = $message;

                if ( ! isset($this->_error_array[$row['field']]))
                {
                    $this->_error_array[$row['field']] = $message;
                }
            }

            return;
        }

        // --------------------------------------------------------------------

        // Cycle through each rule and run it
        foreach ($rules As $rule)
        {
            $_in_array = FALSE;

            // We set the $postdata variable with the current data in our master array so that
            // each cycle of the loop is dealing with the processed data from the last cycle
            if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
            {
                // We shouldn't need this safety, but just in case there isn't an array index
                // associated with this cycle we'll bail out
                if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
                {
                    continue;
                }

                $postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
                $_in_array = TRUE;
            }
            else
            {
                $postdata = $this->_field_data[$row['field']]['postdata'];
            }

            // --------------------------------------------------------------------

            // Is the rule a callback?            
            $callback = FALSE;
            if (substr($rule, 0, 9) == 'callback_')
            {
                $rule = substr($rule, 9);
                $callback = TRUE;
            }

            // Strip the parameter (if exists) from the rule
            // Rules can contain a parameter: max_length[5]
            $param = FALSE;
            if (preg_match("/(.*?)\[(.*?)\]/", $rule, $match))
            {
                $rule    = $match[1];
                $param    = $match[2];
            }

            // Call the function that corresponds to the rule
            if ($callback === TRUE)
            {

                /* Allows callbacks into Models */

                if (list($class, $method) = explode('->', $rule))
                {
                    if ( ! method_exists($parent->$class, $method))
                    {         
                        continue;
                    }

                    $result = $parent->$class->$method($postdata, $param);
                }
                else
                {
                    if ( ! method_exists($parent, $rule))
                    {         
                        continue;
                    }

                    $result = $parent->$rule($postdata, $param);    
                }

                /* Original code removed */

                /*if ( ! method_exists($parent, $rule))
                {         
                    continue;
                }

                // Run the function and grab the result
                $result = $parent->$rule($postdata, $param);*/

                /* Original code continues */

                // Re-assign the result to the master data array
                if ($_in_array == TRUE)
                {
                    $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
                }
                else
                {
                    $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
                }

                // If the field isn't required and we just processed a callback we'll move on...
                if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
                {
                    return;
                }
            }
            else
            {                
                if ( ! method_exists($this, $rule))
                {
                    /*
                     * Run the native PHP function if called for
                     *
                     * If our own wrapper function doesn't exist we see
                     * if a native PHP function does. Users can use
                     * any native PHP function call that has one param.
                     */
                    if (function_exists($rule))
                    {
                        $result = $rule($postdata);

                        if ($_in_array == TRUE)
                        {
                            $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
                        }
                        else
                        {
                            $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
                        }
                    }

                    continue;
                }

                $result = $this->$rule($postdata, $param);

                if ($_in_array == TRUE)
                {
                    $this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
                }
                else
                {
                    $this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
                }
            }

            // Did the rule test negatively?  If so, grab the error.
            if ($result === FALSE)
            {
                if ( ! isset($this->_error_messages[$rule]))
                {
                    if (FALSE === ($line = $parent->lang->line($rule)))
                    {
                        $line = 'Unable to access an error message corresponding to your field name.';
                    }                        
                }
                else
                {
                    $line = $this->_error_messages[$rule];
                }                

                // Build the error message
                $message = sprintf($line, $row['label'], $param);

                // Save the error message
                $this->_field_data[$row['field']]['error'] = $message;

                if ( ! isset($this->_error_array[$row['field']]))
                {
                    $this->_error_array[$row['field']] = $message;
                }

                return;
            }
        }
    }    
}
// END MY_Form_validation Class

/* End of file MY_Form_validation.php */
/* Location: ./system/application/libraries/MY_Form_validation.php */