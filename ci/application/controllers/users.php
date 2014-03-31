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
            $this->load->model(array('page_model', 'check', 'section_get_model', 'user_model', 'permission'));
            $this->load->library(array('table', 'form_validation', 'gravatar'));
            $this->load->helper('form');
        }
        /**
         * function to create a new user
         * 
         * @version 1.0
         */
        public function create()
        {
            if($this->permission->hasPermission('create', 'user'))
                $this->load->view('users/user_view');
            else
                $this->index();
        }
        
        /**
         * sends a welcome mail to a given user
         * 
         * @param Array $user user to send the welcome mail to 
         */
        private function sendWelcomeMail($user)
        {
            $session = $this->session->userdata('logged_in');
            $logged_in_user = $session['user'];
            $admin = $this -> db_model -> get_single('user', array('username' =>$logged_in_user));
            
            $this->load->library('email');
            $this->email->from($admin['mail'], $admin['firstname'] . ' ' .$admin['lastname']);
            $this->email->to($user['mail']);
            $this->email->subject('Welcome to our Film-Project');
            
            $this->email->message("Hi " . $user['firstname'] .' '.$user['lastname']. ",\n\nWelcome to Tindio @ ".site_url()." \n \nYour email was used to create an account with the following data:\n".
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
            
            $this->form_validation->set_message('is_unique','Username already exists');
            $this->form_validation->set_message('required','Please insert your %s');
            
            $data = array(  'firstname' => $this->input->post('first'),
                            'lastname' => $this->input->post('last'),
                            'username' => $this->input->post('user'),
                            'password' => $this->input->post('password'),
                            'mail' => $this->input->post('mail'));
            
            if($data['password'] == '')
                $data['password'] = $this -> generate_pw();
            
            $new_user_event_id = $this->db_model->get_single('logtype', 'event = "new_user"');
            $logdata['logtype_id'] = $new_user_event_id['logtype_id'];
            $logdata['link'] = $this->input->post('user');
    
            if ($this->form_validation->run() == FALSE)
                $this->create();
            else
            {
                $this->sendWelcomeMail($data);
                $data['password'] = md5($data['password']);
                $this->db_model->insert('user',$data);
                $this->db_model->insert('globallog',$logdata);
                
                echo 'done';
            }
        }

        /**
         * generates a password
         * 
         * @return String generated password
         */
        public function generate_pw()
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
         * prepares to shows all users relevant for the item specified by section id
         * 
         * @param String $section section (Project/Scene/shot/Task) to display available users for
         * @param Integer $id id of the item (->section)
         * 
         * @version 1.0
         */
        public function index()
        {
            $data['users'] = $this->db_model->get('user');
            $data['filter'] = '';
            $this->overview($data);
        }

        function show($section = null, $id = null)
        {
            // function available to every user
            if(!is_null($section) && $section != 'task' && $section != 'shot' && $section != 'scene' && $section != 'project')
            {
                redirect('users');
            }
            if($section != 'project' && !is_null($id))//TODO: valid id? -> only show users already in the project
            {
                switch($section)
                {
                    case 'scene': $dutyid = $this->db_model->get_single('scene', "scene_id = $id", 'project_id'); break;
                    case 'shot' : $dutyid = $this->db_model->get_single('shot', "shot_id = $id", 'project_id'); break;
                    case 'task' : $dutyid = $this->db_model->get_single('task', "task_id = $id", 'project_id'); break;
                }
                $dutyid = $dutyid['project_id'];
                $data['users'] = $this->db_model->get('user, projectobserver', 'user.username = projectobserver.username AND projectobserver.project_id = "'.$dutyid.'"');
            }
            else 
                $data['users'] = $this->db_model->get('user'); //all users
                
            $data['filter'] = '';
            $this->overview($data, $section, $id);
        }
        
        /**
         * displays users
         * 
         * @param Array $users user items to display
         * @param String $section section the currently logged in user is coming from
         * @param Integer $id id of the work item the currently logged in user is coming from
         */
        function overview($data, $section = null, $id = null)
        {
            $session = $this->session->userdata('logged_in');
            $this->table->set_template($this->page_model->get_table_template('allusers'));
            
            $this->table->set_heading(array(
                                            '0' => array('data' => '', 'style' => 'width:50px'),
                                            '1' => array('data' => 'Username', 'style' => 'max-width: 120px'),
                                            '2' => array('data' => 'Firstname', 'style' => 'max-width: 120px'),
                                            '3' => array('data' => 'Lastname', 'style' => 'max-width: 120px'),
                                            '4' => array('data' => 'Skills'),
                                            '5' => array('data' => 'Roles'),
                                            '6' => array('data' => 'Projects',  'style' => 'max-width: 150px'),
                                            '7' => array('data' => 'Last access', 'style' => 'width: 100px'),
                                            '8' => array('data' => 'Actions', 'style' => 'width: 80px')
                                            ));                 
                                        
            $isAdmin = $this->permission->isAdmin();
            $isDirector = $this->permission->isDirector();

            if(isset($data['users']))
            {
                foreach ($data['users'] as $user)
                {
                    $username = $user['username'];
                    
                    $skills = $this->gString($this->db_model->get('userskill u, skill s', "username = '$username' AND u.skill_id = s.skill_id"), 'title');
                    $access = $this->page_model->timesince($user['lastaccess']);
                    $options= '';
    
    
                    if($this -> permission -> hasPermission('promoteToAdmin', 'user') && !$this -> permission -> isAdmin($username))
                        $options .= '<a href="'.base_url('users/promoteToAdmin/'.$username).'" class="tooltip"><i class="icon-levelup" title="Promote to admin"></i></a>'. " ";
                    else if($this -> permission -> hasPermission('demoteFromAdmin', 'user') && $this -> permission -> isAdmin($username) && $session['user'] != $username)
                        $options .= '<a href="'.base_url('users/demoteFromAdmin/'.$username).'" class="tooltip"><i class="icon-leveldown" title="Demote from admin"></i></a>'. " ";
                
                    if($this -> permission -> hasPermission('recruit', 'user') && is_null($id))                           //free recruit
                        $options .= '<a href="'.base_url('users/recruit/'.$username).'" class="tooltip" data-target="#modal" data-toggle="modal"><i class="icon-check" title="recruit user"></i></a>'. " ";
                    else if( $this -> permission -> hasPermission('recruit', 'user') &&                                   //recruit from $section
                            (count($this->db_model->get('user'.$section, array($section.'_id'=>$id, 'username'=>$username)))==0))//not assigned yet
                        $options .= '<a href="'.base_url('users/recruit/'.$username.'/'.$section.'/'.$id).'" class="tooltip"><i class="icon-check" title="recruit user"></i></a>'. " ";
        
                    if($username== $session['user'] && is_null($section))      //no edit if recruting!
                        $options .= '<a href="'.base_url('users/view/'.$username).'" data-target="#modal" data-toggle="modal" class="tooltip"><i class="icon-pencil" title="edit profile"></i></a>'." ";
                    if($isAdmin && is_null($section))                                       //no remove if recruting
                        $options .= '<a onclick="return confDelete();" href="'.base_url('users/delete/'.$username).'" title="" class="tooltip"><i class="icon-remove" title="delete user"></i></a>'. " ";
                    
                    $userprojectrole = $this->section_get_model->get_userprojectrole($username);
                    $roles = "";
                    $projects = "";
                    foreach($userprojectrole as $userprojectrole_item)
                    {
                        $projects .= '<a href="'.site_url('projects/view/'.$userprojectrole_item['project_id']).'" >'.$userprojectrole_item['title'].'</a>'.'<br/>';
                        $roles .= $userprojectrole_item['role_title'].'<br/>';
                    }
                    if($this->db_model->get('admin', array('username'=>$username))){
                        $projects .= '<br/>';
                        $roles .= 'Admin <br/>';
                    }
    
    
                    $row = array(
                            '0' => array('data' => '<img src="'.$this->gravatar->get_gravatar($user['gravatar_email']).'">'),
                            '1' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.base_url('users/view/'.$username).'" data-target="#modal" data-toggle="modal">'.$username.'</a></div>', 'style' => 'max-width:120px'),
                            '2' => array('data' => '<div style=\'overflow-x:auto\'>'.$user['firstname'].'</div>', 'style' => 'max-width:120px'),
                            '3' => array('data' => '<div style=\'overflow-x:auto\'>'.$user['lastname'].'</div>', 'style' => 'max-width:120px'),
                            '4' => array('data' => $skills, 'style'=>'text-align:left;'),
                            '5' => array('data' => $roles, 'style'=>'text-align:left;'),
                            '6' => array('data' => '<div style=\'overflow-x:auto\'>'.$projects.'</div>', 'style' => 'max-width:200px'),
                            '7' => array('data' => $access, 'style'=>'text-align:left;'),
                            '8' => array('data' => $options, 'style' => 'text-align: center')
                            ); 
                    $this->table->add_row($row);
                }
                unset($data['users']);
            }
            
            $data['table'] = $this->table->generate();
            $data['title'] = 'All Users';
            
            if(!is_null($section) && !is_null($id))
            {
                $data['section'] = $section;
                $data['section_id'] = $id;
            }
            $this->template->load('users/user_overview', $data);
        }

        public function gString($rows, $field)
        {
            $result = '';
            foreach($rows as $row)
                $result .= $row[$field].'</br>';
            return $result;
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
         * @param String $type type of the duty to assign the user to, default false
         * @param Integer $id id of the duty to assign the user to, default false
         */
        public function recruit($username = false, $type = false, $id = false)
        {
            if($username == false || count($this->db_model->get('user', array('username'=>$username)))==0)
            {
                redirect('users');
            }
            
            if($id != false)
            {
                $this->user_model->recruit($username, $type, $id);
                redirect($type.'s/view/'.$id);
                return;
            }
            
            if(!$this->permission->hasPermission('recruit', $type, $id))
                redirect("mystuff/dashboard");

            $session = $this->session->userdata('logged_in');
            
            $data['title'] = 'Recruit';
            $data['cuser'] = $username;
            $data['recruiter'] = $session['user'];
            $data['usert'] = $this->db_model->get('user', null, 'username');
            foreach($data['usert'] as $u)
            {
                $data['user'][$u['username']] = $u['username'];
            }
            
            $isAdmin = $this->permission->isAdmin();
            
            if($isAdmin)
            {
                $data['projects'] = $this->db_model->get('project');
            }
            else 
            {
                $data['projects'] = $this->db_model->get('project, userproject', 
                    'userproject.username = "'.$logged_in_user.'" AND userproject.project_id = project.project_id', 
                    'project.*');
            }
            $this->load->view('users/user_recruitview', $data);
        }
        
        /**
         * unassigns a given user form a given duty
         * 
         * @param String $username username of the user to unassign, default false
         * @param String $type type of the duty to unassign the user from
         * @param Integer $id id od the duty to anassign the user from
         */
        public function unassign($username = false, $type = false, $id = false)
        {
            if($username === false || $type === false || $id === false ||
                ($type != 'project' && $type != 'scene' && $type != 'shot' && $type != 'task'))
                redirect('users');
            if(!$this->permission->hasPermission('unassign', $type, $id))
                redirect("users");
            
            $this->user_model->unassign($username, $type, $id);
            
            redirect($type.'s/view/'.$id);
        }


        /**
         * shows the profile page of the user specified in $id.
         * if there is no user with the name $id in the database, 
         * it redirects to ./login [login redirects to start page, if logged in]
         * 
         * @version 0.01
         * @param String $id user name of the user to display
         */
        public function view($id = false)
        {
            // function available to every user
            $session = $this->session->userdata('logged_in');
            if($id === false || $id === $session['user'])
            {
                redirect("users/edit");
            }
            if(!$this->db_model->get('user', array('username'=>$id)))
            {
                redirect("login");      //TODO: $id not found
            }
            $data['username'] = $id;
            $data['deadlines'] = $this->page_model->get_deadlines($id);
            $data['skills'] = $this->db_model->get('skill, userskill', 
                                                    'username = "'.$id.'" AND skill.skill_id = userskill.skill_id', 
                                                    'title');
            $isAdmin = $this->permission->isAdmin();
            $isDirector = $this->permission->isDirector();
            $data['recruit'] = ($isAdmin || $isDirector);
            
            $this->load->view('users/profile', $data);
        }
        
        /**
         * function for the user to edit profile
         * 
         * @version -5.0
         */
        public function edit()
        {
            // function available to every user
            $session = $this->session->userdata('logged_in');
            $data['username']= $session['user'];
    
            $user = $this -> db_model -> get_single('user', array('username' => $data['username']));
            $data['oldUsername'] = $user['username'];
            $data['oldMail'] = $user['mail'];
            $data['oldGravatar'] = $user['gravatar_email'];
            $data['oldFirstname'] = $user['firstname'];
            $data['oldLastname'] = $user['lastname'];
            $data['timezone'] = $user['timezone'];
            $data['skills'] = $this->db_model->get('skill');
            $data['skills'][] = array('title'=>'No Skill', 'skill_id'=>-1);
            $data['oldskills'] = $this->db_model->get('userskill', array('username'=>$data['username']));

            $data['oldskill1'] = (isset($data['oldskills'][0]))? $data['oldskills'][0]['skill_id'] : -1;
            $data['oldskill2'] = (isset($data['oldskills'][1]))? $data['oldskills'][1]['skill_id'] : -1;
            
            $this->load->view('users/edit_profile', $data);
        }
        
        /**
         * writes the changes made to a user's profile into the database
         */
        public function editform()
        {
            $this->load->library('form_validation');
            
            $session = $this->session->userdata('logged_in');
            
            $this->form_validation->set_rules('oldPassword', 'old password', 'required|trim|xss_clean|callback_check->password['.$session['user'].']');         
            $this->form_validation->set_rules('newUsername', 'new username', 'trim|xss_clean|alpha_dash|callback_check->username['.$session['user'].']');
            $this->form_validation->set_rules('newFirstname', 'new firstname', 'required|trim|xss_clean');
            $this->form_validation->set_rules('newLastname', 'new lastname', 'required|trim|xss_clean');
            $this->form_validation->set_rules('newPassword', 'new password',  'trim|xss_clean|matches[newPassword2]|callback_check->secure_password|');
            $this->form_validation->set_rules('newPassword2','password confirmation','trim|xss_clean');
            $this->form_validation->set_rules('mail', 'mail', 'trim|xss_clean|valid_email');
            $this->form_validation->set_rules('gmail', 'Gravatar mail', 'trim|xss_clean|valid_email');
            
            $this->form_validation->set_message('required','Please insert your %s');
        
            if ($this->form_validation->run() == FALSE)
            {
                $this->edit();
            }
            else
            {
                $username = $this->input->post('newUsername');
                $pw1 = $this->input->post('newPassword');
                $pw2 = $this->input->post('newPassword2');
                $email = $this->input->post('newEMail');

                $gravatar_email = $this->input->post('gravatar_email');
        
                $data = array();
                if($username != '')
                {
                    $this->session->set_userdata('user', $username);
                    $data['username'] = $username;
                }
                    
                if($pw1 != '' && $pw1 === $pw2)
                    $data['password'] = md5($pw1);
                if($email != '')
                    $data['mail'] = $email;
                    
                if($gravatar_email != '')
                {
                    $this->session->set_userdata('gravatar_url', $this->gravatar->get_gravatar($gravatar_email));
                    $data['gravatar_email'] = $gravatar_email;
                }
                    
                
                $data['firstname'] = $this->input->post('newFirstname');
                $data['lastname'] = $this->input->post('newLastname');
                $data['timezone'] = $this->input->post('timezones');
                
                $skill1 = $this->input->post('first_skill');
                $skill2 = $this->input->post('second_skill');
                    
                $newSkills = '('.$skill1.','.$skill2.')';
                
                if($skill1 != -1 && !$this->db_model->get_single('userskill', array('username' =>$session['user'], 'skill_id'=>$skill1)))
                    $this->db_model->insert('userskill', array('username'=>$session['user'], 'skill_id'=>$skill1));
                if($skill2 != -1 && !$this->db_model->get_single('userskill', array('username' =>$session['user'], 'skill_id'=>$skill2)))
                    $this->db_model->insert('userskill', array('username'=>$session['user'], 'skill_id'=>$skill2));
                $this->db_model->destroy('userskill', 'username = "'.$session['user']. '" AND skill_id NOT IN '.$newSkills);
                
                $this->db_model->update('user', array('username'=> $session['user']),$data);
                echo 'done';
            }
        }

        /**
         * deletes a user
         * 
         * @param String $username name of the user to delete
         */
        public function delete($username)//TODO: stati von task/shot/scene updaten
        {
            $session = $this->session->userdata('logged_in');
            
            if(!$this->permission->hasPermission('delete', 'user'))
            {
                redirect('users');
            }
            else 
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
                redirect('users');
            }
        }


        /**
         * promotes the given user to an administrator
         * 
         * @param String $username username of the user to promote to administrator
         */
        public function promoteToAdmin($username)
        {
            if(!$this->permission->hasPermission('promoteToAdmin', 'user'))
                redirect('users');
            $this -> db_model -> insert('admin',array('username' =>$username));
            redirect('users');
        }
        
        /**
         * demotes the given user from an administrator to a regular user
         * 
         * @param String $username username of the user to demote from administrator to a regular user
         */
        public function demoteFromAdmin($username)
        {
            $session = $this->session->userdata('logged_in');
            if(!$this->permission->hasPermission('demoteFromAdmin', 'user') || $username == $session['user'])
                redirect('users');
            $this -> db_model -> destroy('admin',array('username' =>$username));
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
                                         $this->section_get_model->get_userprojectrole($users[$i]['username']));
                                         
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
                        $users = array_merge($users, $this->section_get_model->get_users('project', $project_id));

                    return array_unique($users, SORT_REGULAR);
                default:
                    $filter_conditions = $filter_terms; break;
            }

            return empty($filter_conditions) ? array() : $this->db_model->get('user', "$field REGEXP '" . implode('(.*)|(.*)', $filter_conditions) . "' ORDER BY $field");
        }
    }
?>
