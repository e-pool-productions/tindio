<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
    class Template
    {
        var $ci;
           
        function __construct()
        {
            $this->ci =& get_instance();
			$this-> ci-> load ->model('permission');
        } 
        
        public function load($content, $data = null)
        {
			if(!isset($data['isAdmin']))	
				$data['isAdmin'] = $this->ci->permission->isAdmin();
			if(!isset($data['isDirector']))
				$data['isDirector'] = $this ->ci->permission->isDirector();
			$data['gravatar_url'] = $this->ci->session->userdata('gravatar_url');
            
            $data['content'] = $this->ci->load->view($content, $data, true);
            
            if(!isset($data['header']))
                $data['header'] = $this->ci->load->view('templates/header', $data , true);
            if(!isset($data['footer']))
                $data['footer'] = $this->ci->load->view('templates/footer', '', true);

            if(!isset($data['title']))
                $data['title'] = '';
            else
                $data['title'] = 'Tindio | '.$data['title'];
            
            $this->ci->load->view('templates/template', $data);
        }
    }
?>