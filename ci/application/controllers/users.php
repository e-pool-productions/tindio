<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * controller of all user-pages, edit profile and recruit
     */
    class Users extends MY_Controller
    {
        /**
         * constructor 
         * makes the mail_model and the mail_model available to this class
         * 
         * @version 1.0
         */
        public function __construct()
        {
            parent::__construct();
            $this->load->model(array('page_model', 'check', 'section_model', 'user_model', 'permission'));
            $this->load->library(array('table', 'form_validation', 'gravatar'));
            $this->load->helper('form');
        }
		
		/**
         * prepares to shows all users relevant for the item specified by section id
         * 
         * @param String $section section (Project/Scene/shot/Task) to display available users for
         * @param Integer $section_id id of the item (->section)
         * 
         * @version 1.0
         */
        public function index()
        {
            $data['users'] = $this->db_model->get('user');
            $data['filter'] = '';
            $this->overview($data);
        }
		
        /**
         * function to create a new user
         * 
         * @version 1.0
         */
        public function create()
        {
        	if(!$this->permission->hasPermission('create', 'user'))
				$this->index();
            
            $this->load->view('users/user_creationview');
        }
        
        /**
         * functionality to add a new user to the system
         * 
         * @version 1.0
         */
        public function form()
        {
            $this->form_validation->set_rules('first', 'First Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('last', 'Last Name', 'required|trim|xss_clean');
            $this->form_validation->set_rules('user', 'Username', 'required|trim|xss_clean|is_unique[user.username]|alpha_dash');
            $this->form_validation->set_rules('password', 'password', 'trim|xss_clean|callback_check->secure_password');
            $this->form_validation->set_rules('mail', 'mail', 'required|trim|xss_clean|valid_email');
            
            $data = array(  'firstname' => $this->input->post('first'),
                            'lastname' => $this->input->post('last'),
                            'username' => $this->input->post('user'),
                            'password' => $this->input->post('password'),
                            'mail' => $this->input->post('mail'));
            
            if($data['password'] == '')
                $data['password'] = $this->user_model->generate_pw();
            
            $new_user_event_id = $this->db_model->get_single('logtype', 'event = "new_user"');
            $logdata['logtype_id'] = $new_user_event_id['logtype_id'];
            $logdata['link'] = $this->input->post('user');
    
            if ($this->form_validation->run() == FALSE)
                $this->create();
            else
            {
                $data['password'] = md5($data['password']);
                $this->db_model->insert('user', $data);
                $this->db_model->insert('globallog', $logdata);
				$this->user_model->sendWelcomeMail($data);
                
                echo 'done';
            }
        }

        function show($section = null, $section_id = null)
        {
            if(is_null($section) || is_null($section_id) || !in_array($section, array('task', 'shot', 'scene', 'project')))
                redirect('users');
			
			$item = $this->db_model->get_single($section, $section."_id = $section_id", 'project_id');
			 
            if($section != 'project')
			{
				$where = '';
				if($this->db_model->get_single("user$section us", "us.".$section."_id = $section_id", $section.'_id'))
					$where = "AND us.".$section."_id = $section_id AND u.username != us.username";
				
				$data['users'] = $this->db_model->get(	'user u'.(!empty($where) ? ', user'.$section.' us' : ''),
														"u.username IN (SELECT username FROM projectobserver po
														WHERE po.project_id = ".$item['project_id'].") $where", 'u.*');
			}
            else
            	$data['users'] = $this->db_model->get('user', "username NOT IN (SELECT username FROM projectobserver WHERE project_id = $section_id)"); 
              
            $data['filter'] = '';
            $this->overview($data, $section, $section_id);
        }
        
        /**
         * displays users
         * 
         * @param Array $users user items to display
         * @param String $section section the currently logged in user is coming from
         * @param Integer $section_id id of the work item the currently logged in user is coming from
         */
        function overview($data, $section = null, $section_id = null)
        {
            $this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
            
            $this->table->set_heading(array('0' => array('data' => ''),
                                            '1' => array('data' => 'Username'),
                                            '2' => array('data' => 'Firstname'),
                                            '3' => array('data' => 'Lastname'),
                                            '4' => array('data' => 'Skills'),
                                            '5' => array('data' => 'Roles'),
                                            '6' => array('data' => 'Projects'),
                                            '7' => array('data' => 'Last access'),
                                            '8' => array('data' => 'Actions')
                                            ));                 
            
			$user = $this->session->userdata('user');
            $isAdmin = $this->permission->isAdmin();
            $isDirector = $this->permission->isDirector();

            if(isset($data['users']))
            {
                foreach ($data['users'] as $user_item)
                {
                    $username = $user_item['username'];
                    
					$skills = implode('</br>', array_map(function($ele){return $ele['title'];}, $this->db_model->get(	'userskill u, skill s',
																														"username = '$username' AND u.skill_id = s.skill_id",
																														'title')));
                    $access = $this->page_model->timesince($user_item['lastaccess']);
                    $options= '';
    
    				if(is_null($section))
					{
						if(!$this->permission->isAdmin($username) && $this->permission->hasPermission('promoteToAdmin', 'user'))
	                        $options .= '<a href="'.base_url('users/promoteToAdmin/'.$username).'"><i class="fa fa-level-up"></i></a> ';
	                    else if($user != $username && $this->permission->isAdmin($username) && $this->permission->hasPermission('demoteFromAdmin', 'user'))
	                        $options .= '<a href="'.base_url('users/demoteFromAdmin/'.$username).'"><i class="fa fa-level-down"></i></a> ';

						if($this->permission->hasPermission('recruit', 'user')) // free recruit
							$options .= '<a href="'.base_url('users/recruit/'.$username).'" data-target="#modal" data-toggle="modal"><i class="fa fa-plus"></i></a> ';
						
						if($isAdmin)
							$options .= '<a onclick="return confDelete(\'user\');" href="'.base_url('users/delete/'.$username).'"><i class="fa fa-times"></i></a>';
					}
					elseif($this->permission->hasPermission('recruit', 'user'))
					{
						$table = $section == 'project' ? 'projectobserver' : 'user'.$section;
						if(!$this->db_model->get_single($table, array($section.'_id'=>$section_id, 'username'=>$username)))
							$options = '<a href="'.base_url('users/recruit/'.$username.'/'.$section.'/'.$section_id).'"><i class="fa fa-check"></i></a>';
					}						

                    $userprojectrole = $this->section_model->get_userprojectrole($username);
					
                    $roles = "";
                    $projects = "";
                    foreach($userprojectrole as $item)
                    {
                        $projects .= '<a href="'.base_url('projects/view/'.$item['project_id']).'" >'.$item['title'].'</a><br/>';
                        $roles .= $item['role_title'].'<br/>';
                    }

                    if($this->db_model->get_single('admin', "username = '$username'", 'username'))
                    {
                        $projects .= '<br/>';
                        $roles .= 'Admin <br/>';
                    }
    
    
                    $row = array(
                            '0' => array('data' => '<img src="'.$this->gravatar->get_gravatar($user_item['gravatar_email']).'">'),
                            '1' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.base_url('users/profile/'.$username).'">'.$username.'</a></div>'),
                            '2' => array('data' => '<div style=\'overflow-x:auto\'>'.$user_item['firstname'].'</div>'),
                            '3' => array('data' => '<div style=\'overflow-x:auto\'>'.$user_item['lastname'].'</div>'),
                            '4' => array('data' => $skills),
                            '5' => array('data' => $roles),
                            '6' => array('data' => '<div style=\'overflow-x:auto\'>'.$projects.'</div>'),
                            '7' => array('data' => $access),
                            '8' => array('data' => $options, 'style' => 'text-align: center')
                            ); 
                    $this->table->add_row($row);
                }
                unset($data['users']);
            }
            
            $data['table'] = $this->table->generate();
            $data['title'] = 'All Users';
            
            if(!is_null($section) && !is_null($section_id))
            {
                $data['section'] = $section;
                $data['section_id'] = $section_id;
            }
            $this->template->load('users/user_overview', $data);
        }

        /**
         * adds the user picked in the modal to the project
         */
        public function recruitform()
        {
            $this->user_model->assign_to_project($this->input->post('user'), $this->input->post('duty'));
            echo 'done';
        }

        /**
         * opens a modal to recruit a user
         * 
         * @param String $username name of the user to assign, default false
         * @param String $section type of the duty to assign the user to, default false
         * @param Integer $section_id id of the duty to assign the user to, default false
         */
        public function recruit($username = null, $section = null, $section_id = null)
        {
            if(is_null($username) || !$this->db_model->get_single('user', "username = '$username'"))
                redirect('users');
            
            if(!is_null($section_id))
            {
                $this->user_model->recruit($username, $section, $section_id);
                redirect($section.'s/view/'.$section_id);
            }
			
			$isAdmin = $this->permission->isAdmin();
			
            if(!$isAdmin && !$this->permission->isDirector())
                redirect('users');
			
			$user = $this->session->userdata('user');

            $data['title'] = 'Recruit';
            $data['cuser'] = $username;
            $data['recruiter'] = $user;
			
			$data['projects'] = $isAdmin ? $this->db_model->get('project') : $this->db_model->get(	'project p, userproject up', 
                    																				"up.username = '$user' AND up.project_id = p.project_id", 
                    																				'p.*');
            $this->load->view('users/user_recruitview', $data);
        }
        
        /**
         * unassigns a given user form a given duty
         * 
         * @param String $username username of the user to unassign, default false
         * @param String $section type of the duty to unassign the user from
         * @param Integer $section_id id od the duty to anassign the user from
         */
        public function unassign($username = false, $section = false, $section_id = false)
        {
            if($username === false || $section === false || $section_id === false || !in_array($section, array('task', 'shot', 'scene', 'project')))
                redirect('users');
            if(!$this->permission->hasPermission('unassign', $section, $section_id))
                redirect("users");
            
            $this->user_model->unassign($username, $section, $section_id);
            
            redirect($section.'s/view/'.$section_id);
        }

        /**
         * shows the profile page of the user specified in $section_id.
         * if there is no user with the name $section_id in the database, 
         * it redirects to ./login [login redirects to start page, if logged in]
         * 
         * @version 0.01
         * @param String $section_id user name of the user to display
         */
        function profile($username = null)
        {
            // function available to every user
            if(is_null($username) || !$this->db_model->get_single('user', "username = '$username'"))
                redirect("users");
			
            $data['user'] = $this -> db_model -> get_single('user', "username = '$username'");
            $data['deadlines'] = $this->page_model->get_deadlines($username);
			
			$data['ownSkills'] = $this->db_model->get('skill s, userskill us', "username = '$username' AND s.skill_id = us.skill_id", 's.*');
			$skill_ids = array_map(function($ele){return $ele['skill_id'];}, $data['ownSkills']);
			
			$data['otherSkills'] = empty($skill_ids) ? $this->db_model->get('skill') : $this->db_model->get('skill', 'skill_id NOT IN ('.implode(',', $skill_ids).')');
			$data['hasAllSkills'] = empty($data['otherSkills']);
			
			$data['ownProfile'] = $username == $this->session->userdata('user');
            $data['canRecruit'] = $this->permission->hasPermission('recruit', 'user');
            
            $this->template->load('users/profile', $data);
        }

		function edit($username, $field, $optID = null)
        {
        	if(!$this->permission->hasPermission('edit', 'user', $username))
			{
				echo 'Permission denied!';
				return;
			}
			
			echo $this->user_model->edit($username, $field, $optID);
        }

        /**
         * deletes a user
         * 
         * @param String $username name of the user to delete
         */
        function delete($username)
        {
            if($this->permission->hasPermission('delete', 'user'))
            {
                $this->db_model->destroy('userskill', array('username'=>$username));
	            $this->db_model->destroy('projectobserver', array('username'=>$username));
	            $this->db_model->destroy('usertask', array('username'=>$username));
	            $this->db_model->destroy('usershot', array('username'=>$username));
	            $this->db_model->destroy('userscene', array('username'=>$username));
	            $this->db_model->destroy('userproject', array('username'=>$username));
	            $this->db_model->destroy('admin', array('username'=>$username));
	            $this->db_model->destroy('user', array('username'=>$username));
	        
	            $event_id = $this->db_model->get_single('logtype', 'event = "delete_user"', 'logtype_id');
	            $event_id = $event_id['logtype_id'];
	            $this->db_model->insert('globallog', array('logtype_id'=>$event_id, 'link'=>$username));
            }
            
            redirect('users');
        }


        /**
         * promotes the given user to an administrator
         * 
         * @param String $username username of the user to promote to administrator
         */
        function promoteToAdmin($username)
        {
            if(!$this->permission->hasPermission('promoteToAdmin', 'user'))
                redirect('users');
			
            $this -> db_model -> insert('admin', array('username' => $username));
            redirect('users');
        }
        
        /**
         * demotes the given user from an administrator to a regular user
         * 
         * @param String $username username of the user to demote from administrator to a regular user
         */
        public function demoteFromAdmin($username)
        {
            if(!$this->permission->hasPermission('demoteFromAdmin', 'user') || $username == $this->session->userdata('user'))
                redirect('users');
			
            $this -> db_model -> destroy('admin', array('username' => $username));
            redirect('users');
        }
        
        /**
         * applies the filter to the user table
         * 
         * @param $filter 
         */
        public function filterform($filter = null)
        {
            $field = $this->input->post('fields');
            
            if(is_null($filter))
                $filter = $this->input->post('filter_terms');

            if($filter == '' || $field == 'No_Select')
                redirect('users');
                
            $data['users'] = $this->filter($field, $filter);
            $data['filter'] = $filter;            
            $this->overview($data);
        }

        /**
         * filters dependent on the given field and filter_terms
         * 
         * @param String $field field to filter, default null
         * @param String $filter <filter_term>,<filter_term>,<filter_term>
         * 
         * 
         */
        public function filter($field = null, $filter = null)
        {
            if(is_null($filter) || $filter == '' || $field == 'No_Select')
                return;
            
            $filter_terms = array_filter(array_unique(array_map('trim', explode(',', $filter))));
            
            if(empty($filter_terms))
                return $this->db_model->get('user');
            
            switch($field)
            {
				case 'skills':
					$skills = $this->db_model->get('skill', "title REGEXP '" . implode('(.*)|(.*)', $filter_terms) . "'");
					$filter_conditions = array_map(function($ele){return $ele['skill_id'];}, $skills); 
                    return empty($skills) ? array() : $this->db_model->get('user u, userskill us', 'u.username = us.username AND skill_id IN ('.implode(',', $filter_conditions).')');
                case 'roles':
                    $users = $this->db_model->get('user');
                    
                    if(in_array('admin', array_map('strtolower', $filter_terms)))
                        $admins = array_map(function($ele){return $ele['username'];}, $this->db_model->get('admin'));

                    $amount = count($users);

                    for($i = 0; $i < $amount; $i++)
                    {
                        if(isset($admins) && in_array($users[$i]['username'], $admins))
                            continue;
                        
                        $upr = array_map(function($ele){return strtolower($ele['role_title']);}, 
                                         $this->section_model->get_userprojectrole($users[$i]['username']));
                                         
                        if(!array_intersect(array_map('strtolower', $filter_terms), $upr))
                            unset($users[$i]);
                    }

                    return $users;
                case 'projects':
                    $project_ids = array_map(   function($ele){return $ele['project_id'];},
                                                $this->db_model->get(   'project p',
                                                                        'p.title REGEXP "' . implode('(.*)|(.*)', $filter_terms).'"',
                                                                        'project_id'));
                    $users = array();
                    foreach($project_ids as $project_id)
                        $users = array_merge($users, $this->section_model->get_users('project', $project_id));

                    return array_unique($users, SORT_REGULAR);
                default:
                    $filter_conditions = $filter_terms; break;
            }

            return empty($filter_conditions) ? array() : $this->db_model->get('user', "$field REGEXP '" . implode('(.*)|(.*)', $filter_conditions) . "' ORDER BY $field");
        }
    }
?>