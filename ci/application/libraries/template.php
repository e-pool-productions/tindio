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
        	$session = $this->ci -> session->userdata('logged_in');
			$data['isAdmin'] = $session['isAdmin'];
			// If user is admin, it doesn't matter whether he is director or not 
			$data['isDirector'] = $data['isAdmin'] ? TRUE : $this ->ci->permission->isDirector();
			$data['gravatar_url'] = $session['gravatar_url'];
            
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