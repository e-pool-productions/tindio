<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
	/**
     * Controller for logging in and out of the system
     */
    class Login extends CI_Controller
    {
    	/**
		 * Constructor 
		 * loads the login_model to make it available in this class
		 * 
		 * @version 1.0
		 */    
        public function __construct()
        {
            parent::__construct();
            session_start();
            $this->load->model('check');
			$this->load->library('gravatar');
	        $this->load->helper('form');
        }
        
		/**
		 * shows the default login page
		 * 
		 * @version 1.0
		 */
        public function index()
        {
        	// function available to everyone
			if($this->session->userdata('logged_in') === FALSE)
            {
                $data['header'] = '';
                $data['title'] = 'Please Login';
            	$this->template->load('login/login_view', $data);
			}
			else
			{
                redirect('mystuff/dashboard');
			}
		}
        
		/**
		 * checks the login form and loads the home page if the login was successful
		 * 
		 * @version 1.0
		 */
        public function form()
        {
            // function available to everyone
			$this->load->library('form_validation');
            
            $this->form_validation->set_rules('user', 'Username', 'required|trim|xss_clean');
            $this->form_validation->set_rules('password', 'Password', 'required|trim|xss_clean|callback_check->database');
			
            if ($this->form_validation->run() == FALSE)
            {
                $this->load->view('login/login_view');
            }
            else
            {
                $session = $this->session->userdata('logged_in');
                $lastaccess = date("y-m-d H:i:s");
                $this->db_model->update('user', array('username'=>$session['user']), array('lastaccess'=>$lastaccess));

                redirect('mystuff/dashboard');
            }
        }
       
        /**
         * logs out the current user
         * 
         * @version 1.0
         */
        public function logout()
        {
            // function available to everyone
			$this->session->sess_destroy();
            redirect("login");
        }
    }
?>