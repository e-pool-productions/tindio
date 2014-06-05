<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
    class MY_Controller extends CI_Controller
    {
        public function __construct()
        {
            parent::__construct();
            session_start();
            
            $user = $this->session->userdata('user');
            
            if($user === FALSE)
            {
                redirect("login");
            }
            $this->load->helper('date');
            $tz = $this->db_model->get_single('user', "username = '$user'", 'timezone');
            date_default_timezone_set($this->timezone_by_offset(timezones($tz['timezone'])));
        }
        
        private function timezone_by_offset($offset)
        {
            $abbrarray = timezone_abbreviations_list();
            $offset = $offset * 60 * 60;
        
            foreach ($abbrarray as $abbr) {
                foreach ($abbr as $city) {
                    if ($city['offset'] == $offset && $city['dst'] == FALSE) { 
                        return $city['timezone_id'];                                
                    }
                }
            }
            return 'UTC';
        }
    }
?>