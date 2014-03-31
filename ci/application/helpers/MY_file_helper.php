<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('get_extension'))
{
    function get_extension($file)
    {
        return strtolower(substr(strrchr($file, '.'), 0));
    }
}