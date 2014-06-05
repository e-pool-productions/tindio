<?php
	/**
	 * model for the all-users and edit profile pages
	 */
	class user_model extends CI_Model
	{
		
        /**
         * generates a password
         * 
         * @return String generated password
         */
        function generate_pw()
        {
            $pw = '';
            for ($i=0; $i < 10; $i++) { 
                $pw.= chr(mt_rand(97, 122));
            }
            $num_index = mt_rand(0,10);
            $pw[$num_index] = chr(mt_rand(48, 57));
            $cap_index = mt_rand(0,10);
            while($cap_index == $num_index)
            {
                $cap_index = mt_rand(0,10);
            }
            $pw[$cap_index] = chr(mt_rand(65, 90));
            return $pw;
        }

        /**
         * sends a welcome mail to a given user
         * 
         * @param Array $user user to send the welcome mail to 
         */
        function sendWelcomeMail($user)
        {
            $admin = $this -> db_model -> get_single('user', array('username' => $this->session->userdata('user')));
            
            $this->load->library('email');
            $this->email->from($admin['mail'], $admin['firstname'] . ' ' .$admin['lastname']);
            $this->email->to($user['mail']);
            $this->email->subject('Welcome to our Film-Project');
            
            $this->email->message("Hi " . $user['firstname'] .' '.$user['lastname']. ",\n\nWelcome to Tindio @ ".base_url()." \n \nYour email was used to create an account with the following data:\n".
            "\nUsername: ".$user['username'].
            "\nFirstname: ".$user['firstname'].
            "\nLastname: ".$user['lastname'].
            "\nPasswort: ".$user['password'].
            "\nEmail: ".$user['mail'].
            "\n\nPlease log in and edit your profile (check the right side of the top menu to change your password)".
            "\n\nHave fun with Tindio!".
            "\n\nYours truly".
            "\n\nAdmin");
            $this->email->send();

            //Comment in to see the report
            //echo $this->email->print_debugger();
        }

		/**
		 * adds the person ($username) either to the project (if $section = 'project') as an project observer
		 * or assigns the person to the given duty ($section, $section_id)
		 * 
		 * @param String $username name of the person to recruit
		 * @param String $section task/shot/scene/project
		 * @param Integer $section_id id of the duty to assign the person to
		 */
		function recruit($username, $section, $section_id)
		{
			$isAdmin = $this->permission->isAdmin();
			$isDirector = $this->permission->isDirector();			

			if(!$isAdmin && !$isDirector || !in_array($section, array('task', 'shot', 'scene', 'project')) ||
				!$this->db_model->get_single('user', "username = '$username'", 'username') ||
				!$this->db_model->get_single($section, $section."_id = $section_id", 'title') ||
				$this->db_model->get_single('user'.$section, $section."_id = $section_id AND username = '$username'", 'username'))
			{
				echo 'Maybe something went wrong? (umZ22-25)';
				return;
			}

			if($section != 'project')
				$this -> db_model ->insert('user'.$section, array($section.'_id'=>$section_id, 'username'=>$username)); 
			elseif(!$this->db_model->get_single('projectobserver', "project_id = $section_id AND username = '$username'", 'project_id'))
				$this->db_model->insert('projectobserver', array('project_id'=>$section_id, 'username'=>$username));
			
			if($section == 'task')
			{
				$task = $this -> db_model -> get_single('task', "task_id = $section_id");
				if($task['status_id'] == STATUS_UNASSIGNED)
					$this -> db_model ->update('task', "task_id = $section_id", array('status_id' => STATUS_PRE_PRODUCTION));
				$nA = $this->db_model->get_single('user', array('username'=>$username), 'newassignments');
				$this->db_model->update('user', "username = '$username'", array('newassignments' => $nA['newassignments'] + 1));
			}
		}

		/**
		 * 	unassigns a given user from a specified item
		 * 
		 * @param String $username name of the person to dissociate
		 * @param String $section type of the duty to dissociate the person from
		 * @param Integer $section_id id of the duty to dissociate the person from
		 * @return TRUE if user was unassigned
		 */
		function unassign($username, $section, $section_id)
		{
			switch($section)
            {
                case 'project':
                    $this->db_model->destroy('projectobserver', "project_id = $section_id");
                    
                    $data['scene'] = array_map(function($el){ return $el['scene_id']; }, $this->db_model->get('scene', "project_id = $section_id", 'scene_id'));
                case 'scene':
                    $data['shot'] = array_map(function($el){ return $el['shot_id']; }, $this->db_model->get('shot', $section."_id = $section_id", 'shot_id'));
                case 'shot':
                    if($section == 'scene')
                        $data['task'] = array_map(function($el){ return $el['task_id']; }, $this->db_model->get('task t, shot sh', "t.shot_id = sh.shot_id AND sh.scene_id = $section_id", 'task_id'));
                    else
                        $data['task'] = array_map(function($el){ return $el['task_id']; }, $this->db_model->get('task', $section."_id = $section_id", 'task_id'));
                    break;
            }
            $data = array_reverse($data);
			$data[$section] = array($section_id);
			
			foreach($data as $subSec => $ids)
            {
                if(empty($ids))
                    continue;
				
				if($subSec == 'task')
				{
					$task = $this->db_model->get_single('task', "task_id = $section_id", 'status_id');
					
					//Users assigned?
					if(!$this->db_model->get_single('usertask', "task_id = $section_id", 'task_id') && $task['status_id'] == STATUS_PRE_PRODUCTION)
						$this -> db_model -> update('task', array('task_id' => $section_id), array('status_id' => STATUS_UNASSIGNED));
				}
                
                $this->db_model->destroy('user'.$subSec, "username = '$username' AND ".$subSec.'_id IN ('.implode(',', $ids).')');
            }
		}

		function edit($username, $field, $optID)
		{
			if($field == "removeSkill" && !is_null($optID))
			{
				$this->db_model->destroy('userskill', "username = '$username' AND skill_id = $optID");
				redirect("users/profile/$username");
			}
				
			$newValue = $this->input->post('newValue');
			
			if($newValue === false)
				return 'No Value specified!';
							
            $this->load->library('form_validation');
            $valNeeded = false;

            switch($field)
            {
                case 'username' :      
                    $this->form_validation->set_rules('newValue', 'Username', 'trim|xss_clean|alpha_dash|callback_check->username['.$username.']');
                    $valNeeded = true; break;
                case 'firstname':
                    $this->form_validation->set_rules('newValue', 'First Name', 'required|trim|xss_clean');
                    $valNeeded = true; break;
				case 'lastname':
                    $this->form_validation->set_rules('newValue', 'Last Name', 'required|trim|xss_clean');
                    $valNeeded = true; break;
				case 'mail':
                    $this->form_validation->set_rules('newValue', 'E-Mail', 'trim|xss_clean|valid_email');
                    $valNeeded = true; break;
				case 'gravatar_email':
                    $this->form_validation->set_rules('newValue', 'Gravatar E-Mail', 'trim|xss_clean|valid_email');
                    $valNeeded = true; break;
				case 'password':
					$this->form_validation->set_rules('curpass', 'current password', 'required|trim|xss_clean|callback_check->password['.$username.']');
                    $this->form_validation->set_rules('newValue', 'new password',  'trim|xss_clean|callback_check->secure_password');
					$this->form_validation->set_rules('newpassconf','password confirmation','trim|xss_clean|matches[newValue]');
					$this->form_validation->set_message('matches', 'The password confirmation does not match with your new password.');
                    $valNeeded = true; break;
            }
            
            if ($this->form_validation->run() == FALSE && $valNeeded)
                echo validation_errors(' ', ' ');
            else
            {
            	if($field == "addSkill")
					$this->db_model->insert('userskill', array('username' => $username, 'skill_id' => $newValue));
				else
				{
	            	switch($field)
					{
						case 'username': 		$this->session->set_userdata('user', $newValue); break;
						case 'gravatar_email': 	$this->session->set_userdata('gravatar_url', $this->gravatar->get_gravatar($newValue)); break;
						case 'password': 		$newValue = md5($newValue); break;
					}
					
	               	$this->db_model->update('user', "username = '$username'", array($field => $newValue));
				}
				echo 'done';
            }
		}
	}
?>