<?php
	/**
	 * Model to validate forms
	 */
    class Check extends CI_Model {

        /**
         * checks whether the given password is correct or not
         * 
         * @version 1.0
         * 
         * @param String $password password to check
         */
        public function database($password)
        {
            $this->form_validation->set_message('check->database', 'Invalid username or password');
                        
            $user = $this->input->post('user');
            
            $result = ($this->db_model->get_single( 'user','username = BINARY "' .$user. '" AND password = "'.$password.'"', 'username') !== false);
            
            if($result)
            {
                $gravatar_email = $this->db_model->get_single('user', array('username'=>$user), 'gravatar_email');
                $gravatar_email = $gravatar_email['gravatar_email'];
                $gravatarUrl = $this->gravatar->get_gravatar($gravatar_email);
                
                // Check if User is Admin
                $isAdmin = ($this -> db_model -> get_single('admin', array('username'=>$user), 'username') !== false);
                $sess_array = array('user' => $user, 'gravatar_url' => $gravatarUrl, 'isAdmin' => $isAdmin);
                $this->session->set_userdata($sess_array);
            }
          
            return $result;
        }

        /**
         * @param String $data <section>,<id>,<create/edit>
         */
        public function title($title, $data)
        {
            $this->form_validation->set_message('check->title', 'Title already exists');
            $data = explode(',', $data);
            $section = $data[0];
            $section_id = $data[1];

            switch($section)
            {
                case 'task' : $parent = 'shot';         break;
                case 'shot' : $parent = 'scene';        break;
                case 'scene': $parent = 'project';      break;
                case 'project': return ($this->db_model->get_single('project', array('title'=>$title, 'project_id !='=>$section_id)) === false);
				case 'workflow': return ($this->db_model->get_single('workflow', array('title'=>$title, 'workflow_id !='=>$section_id)) === false);
				case 'workflowtask': return ($this->db_model->get_single('workflowstructure', array('task_title' => $title,
																								 'workflow_id' => $section_id)) === false);
            }
    
            if($data[2] == 'create')
            {
                return ($this->db_model->get_single($section, array($parent.'_id'=>$section_id, 'title'=>$title)) === false);
            }
            else
            {
                $section_item = $this->db_model->get_single($section, array($section.'_id' => $section_id));
                return ($this->db_model->get_single($section, array('title' => $title,
                                                                    $section.'_id !=' => $section_id,
                                                                    $parent.'_id' => $section_item[$parent.'_id'])) === false);
            }
        }
        
		/**
		 * checks if the given shortcode is unique
		 * @param String $shortcode shortcode to check
		 */
        public function shortcode($shortcode, $project_id = false)
        {
            $this->form_validation->set_message('check->shortcode', 'Shortcode already exists');
            // $id = $this->input->post('section_id');
            if($project_id === false)
                return ($this->db_model->get_single('project', "shortcode = '$shortcode'", 'title') === false);
            return ($this->db_model->get_single('project', "shortcode = '$shortcode' AND project_id != $project_id", 'title') === false);
        }
        
		/**
		 * checks if the given shortcode is a duplicate
		 * @param String $shortcode shortcode to check
		 */
        public function duplicate($shortcode)
        {
            return ($this->db_model-> get_single('project', "shortcode = '$shortcode'", 'shortcode') !== false);
        }
        
		/**
		 * checks if the given password and user match with a user password pair in the database
		 * 
		 * @param String $password password to check
		 * @param String $user user to check
		 */
        public function password($password, $user)
        {
            $this->form_validation->set_message('check->password', 'Wrong Password');
            return ($this->db_model->get_single('user', array('username'=>$user, 'password'=>md5($password))) !== false);
        }
        
		/**
		 * checks if the new username ($username) is unique in the system
		 * 
		 * @param String $username new username
		 * @param String $oldname old username
		 */
        public function username($username, $oldname)
        {
            $this->form_validation->set_message('check->username','Username already exists');
            if($username == $oldname)
                return true;
            return ($this->db_model->get('user', "username = '$username'") !== false);
        }
        
		/**
		 * checks if the password matches the security guidelines
		 * 
		 * @param String $password password to check
		 */
        public function secure_password($password)
        {
            $this->form_validation->set_message('check->secure_password', 'Your new password has to contain at least one capital letter and one number');
            if($password == '')
                return false;
            return (preg_match("/[0-9]+/",$password) && preg_match("/[A-Z]+/",$password) && strlen($password) >= 6);
        }

        /**
         *  checks if the title of the setting is unique in the system
		 * 
		 * @param String $title tile of the setting
		 * @param String $data <setting>,<id>
         */
        public function setting($title, $data)
        {
            $data = explode(',', $data);
            
            $this->form_validation->set_message('check->setting', 'Name already in use');
            return !$this->db_model->get_single($data[0], array('title'=>$title, $data[0].'_id !='=>$data[1]));
        }
    }
?>