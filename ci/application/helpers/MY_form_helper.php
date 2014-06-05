<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    function form_group_open()
    { 
	    return '<div class="form-group">';
    }

    function form_group_close()
    { 
	    return '</div>';
    }
	
	function form_div_open($class = NULL, $id = NULL)
	{
	    $code   = '<div ';
	    $code   .= ( $class != NULL )   ? 'class="'. $class .'" '   : '';
	    $code   .= ( $id != NULL )      ? 'id="'. $id .'" '         : '';
	    $code   .= '>';
	    return $code;
	}
	
	function form_div_close()
	{
	    return '</div>';
	}
?>