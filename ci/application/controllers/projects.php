<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
    /**
    * Controller for projects
    */
    class Projects extends MY_Controller 
    {
        /**
         * loads required models, libraries and helpers
         */
        function __construct()
        {
            parent::__construct();
            $this->load->model(array('page_model', 'section_model','user_model' ,'section_get_model', 'check', 'permission', 'assets'));
            $this->load->helper('form');
            $this->load->library('table');
        }
        
        /**
         * shows all projects
         */
        public function index()
        {
            // function available to every user
            $data['filter'] = '';
            $data['side'] = 'All Projects';
            $data['projects'] = $this->db_model->get('project');
            $this->overview($data);
        }
        
        /**
         * shows either all projects or a specific one, depending on the provided $project_id
         * 
         * @param Integer $project_id id of the project to show, default null. If left blank, then showing all projects
         */
        public function view($project_id = null)
        {
            // function available to every user
            if(is_null($project_id) || !$this->db_model->get_single('project', "project_id = $project_id"))
                redirect('projects');

            $project = $this->db_model->get_single('project', "project_id = $project_id");
            $data = $this->section_model->gather_project_details($project, true);
            
            $isAdmin = $this->permission->isAdmin();            
            $isDirector = $this->permission->isDirector('project', $project_id);
            
            //Set Button
            $data['button'] =   !$isAdmin && !$isDirector ||    
                                empty($data['scenes']) || 
                                $project['status_id'] == STATUS_FINISHED ||
                                $data['scenecount'] != $data['scenesfinished'] ? '' :
                                '<a href="'. base_url('projects/finish/'.$project_id) .'" class="button small"><i class="icon-for-approval"></i> Finish Project</a>';

            //Scene-Table
            $this->table->set_template($this->page_model->get_table_template('project_scenes'));
                                
            $this->table->set_heading(array('0' => array('data' => '', 'style' => 'width: 1px'),
                                            '1' => array('data' => '', 'style' => 'width: 1px'),
                                            '2' => array('data' => 'Scene', 'style' => 'width: 100px; min-width:50px'),
                                            '3' => array('data' => 'Code', 'style' => 'width: 90px'),
                                            '4' => array('data' => 'Description', 'style' => 'max-width: 50px;'),
                                            '5' => array('data' => 'Status', 'style' => 'width: 120px'),
                                            '6' => array('data' => 'Shots', 'style' => 'width: 65px'),
                                            '7' => array('data' => 'Details', 'style' => 'width: 1px'),
                                            '8' => array('data' => 'Actions', 'style' => 'width: 50px')
                                    ));

            foreach ($data['scenes'] as $scene)
            {           
                $info = $this->section_model->gather_scene_details($scene);
                
                $options = '';
                if($this->permission->hasPermission('edit', 'scene', $scene['scene_id']))
                    $options = '<a href="'.base_url('scenes/edit/'.$scene['scene_id']).'" data-target="#modal" data-toggle="modal" class="tooltip"><i class="icon-pencil" title="edit"></i></a> ';
                if($this->permission->hasPermission('delete', 'scene', $scene['scene_id']))
                    $options .= '<a onclick="return confDelete();" href="'.base_url('scenes/delete/'.$scene['scene_id']).'" class="tooltip"><i class="icon-remove" title="delete"></i></a>';

                $row = array(   '0' => array('data' => $scene['orderposition']),
                                '1' => array('data' => $info['logo']),
                                '2' => array('data' => '<div style=\'overflow-x:auto\'>'.$info['scene']['title'].'</div>', 'style'=> 'text-align:left; max-width:100px'),
                                '3' => array('data' => $info['shortcode']),
                                '4' => array('data' => '<div style=\'overflow-x:auto\'>'.$scene['description'].'</div>', 'style'=> 'max-width: 110px; text-align:left;'),
                                '5' => array('data' => $info['status']['status'], 'style'=>'color:'.$info['status']['color'].'; min-width: 100px;'),
                                '6' => array('data' => 'Total: '.br(1).$info['shotcount'].br(1).'
                                                        Finished: '.br(1).$info['shotsfinished']),
                                '7' => array('data' => 'Started:    '. $info['startdate'] .br(1).'
                                                        Finished:   '. $info['enddate'] .br(1).'    
                                                        Deadline:   '. $info['deadline'].br(1).'
                                                        Duration:   '. br(1).$info['duration'] .br(1).'
                                                        Crew:       '. br(1).$info['crewtext']),
                                '8' => array('data' => $options, 'style'=> 'text-align:center')
                                );
                $this->table->add_row($row);
            }
            $data['scenetable'] = $this->table->generate();
            
            $this->table->clear();
            
            // UserTable
            $this->table->set_template($this->page_model->get_table_template('scene_users'));
             
            $this->table->set_heading(array('0' => array('data' => 'Name'),
                                            '1' => array('data' => 'Role'),
                                            '2' => array('data' => 'Last access'),
                                            '3' => array('data' => 'Actions'),
                                    ));
            $projectusers = $this->section_get_model->get_users('project', $project_id);
            
            foreach($projectusers as $user)
            {
                $username = $user['username'];
                $unassign = $isAdmin || $isDirector ?
                                '<a onclick="return confUnassign(\''.$user['firstname'].'\', \''.$user['lastname'].'\');" href="'.base_url('users/unassign/'.$username.'/project/'.$project_id).'" class="tooltip"><i class="icon-minus-sign" title="unassign user"></i></a>' :
                                '';

                $row = array(   '0' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.base_url('users/view/'.$username).'" data-target="#modal" data-toggle="modal">'.$user['firstname'].' '.$user['lastname'].'</a></div>', 'style' => 'max-width: 100px'),
                                '1' => array('data' => $user['role_title']),
                                '2' => array('data' => $user['lastaccess']),
                                '3' => array('data' => $unassign, 'style'=>'text-align:center')
                            );
                $this->table->add_row($row);
            }   
            $data['usertable'] = $this->table->generate();
            
            $this->table->clear();
            
            //project Files
            $data['projectfiles'] = $this->page_model->createOutputFileTable('project', $project_id, null, $this->assets->get_assets('project', $project_id));
                            
            if($isAdmin || $isDirector)
            {                                       
                $data['addObserver'] = '<a href="'.base_url('users/show/project/'.$project_id).'"; class="button small"><i class="icon-user"></i> Add Projectmember</a>';
                $data['addNewScene'] = '<a href="'.base_url('/scenes/create/'.$project_id).'" data-target="#modal" data-toggle="modal" class="button small">Add new Scene</a>';
                $data['addNewFile']  = '<a href="'.base_url('/upload/choose_files/project_'.$project_id).'" data-target="#modal" data-toggle="modal" class="button small"><i class="icon-upload-alt"></i></a>';
                $data['linkNewFile'] = '<a href="'.base_url('/all_assets/link_asset/project_'.$project_id).'" class="button small"><i class="icon-link"></i></a>';
            }
            $data['project'] = $project;
            $data['title'] = $project['title'];
            $data['permissions']['edit']= $this -> permission -> hasPermission('edit', 'project', $project_id);
            $this->template->load('projects/project_infoview', $data);
        }

        /**
         * generates the view for either allProjects, all projects the currently logged in user takes part in, or a subset of those
         * 
         * @param Array $projects projects to display
         * @param Boolean $allProjects information for the view if the allProjects or myProjects side is shown
         */
        function overview($data)
        {
            $session = $this->session->userdata('logged_in');
            $isAdmin = $session['isAdmin'];
            
            $this->load->helper('form');
            $this->load->library('table');

            $this->table->set_template($this->page_model->get_table_template('allprojects'));
            
            $this->table->set_heading(array('0' => array('data' => 'Logo', 'style' => ' width:130px'),
                                            '1' => array('data' => 'Title', 'style' => 'width: 10px'),
                                            '2' => array('data' => 'Code', 'style' => 'width: 30px'),
                                            '3' => array('data' => 'Category', 'style' => 'width: 100px'),
                                            '4' => array('data' => 'Director', 'style' => 'min-width: 50px;'),
                                            '5' => array('data' => 'Description'),
                                            '6' => array('data' => 'Status', 'style' => 'width: 120px'),
                                            '7' => array('data' => 'Details', 'style' => 'width: 150px'),
                                            '8' => array('data' => 'Actions', 'style' => 'width: 50px')
                                            ));
            
            if(isset($data['projects']))
            {
                foreach ($data['projects'] as $project) 
                {
                    $info = $this->section_model->gather_project_details($project);
                    $options = '';
                    if($this->permission->hasPermission('edit', 'project', $project['project_id']))
                        $options = '<a href="'.base_url('projects/edit/' . $project['project_id']).'" data-target="#modal" data-toggle="modal" class="tooltip"><i class="icon-pencil" title="edit project"></i></a> ';
                    if($this->permission->hasPermission('delete', 'project', $project['project_id']))
                        $options .= '<a onclick="return confDelete();" href="'.base_url('projects/delete/' . $project['project_id']).'"class="tooltip"><i class="icon-remove" title="delete project"></i></a>';
                    
                    $row = array(   '0' => array('data' => $info['logo']),
                                    '1' => array('data' => '<div style=\'overflow-x:auto\'>'.$info['project']['title'].'</div>', 'style' => 'min-width: 100px; max-width: 200px;'),
                                    '2' => array('data' => $project['shortcode']),
                                    '3' => array('data' => $info['category']),
                                    '4' => array('data' => $info['directors']),
                                    '5' => array('data' => '<div style=\'overflow-x:auto\'>'.$project['description'].'</div>', 'style' => 'max-width: 150px; text-align:left;'),
                                    '6' => array('data' => $info['status']['status'], 'style'=>'color:'.$info['status']['color'].'; width: 110px'),
                                    '7' => array('data' => 'Started:    '. $info['startdate'] .br(1).'
                                                            Finished:   '. $info['enddate'] .br(1).'                 
                                                            Duration:   '. $info['duration'] .br(1).'
                                                            Scenes:     '. $info['scenecount']. ' ['. $info['scenesfinished'] . ' finished] '.br(1).'
                                                            Shots:      '. $info['shotcount'].' ['. $info['shotsfinished'] .' finished] '.br(1).'
                                                            Crew:       '. $info['crewtext']),
                                    '8' => array('data' => $options, 'style'=> 'text-align:center')
                                    );
                    $this->table->add_row($row);
                }
                unset($data['projects']);
            }

            $data['title'] = 'Projects';
            $data['all_projects'] = $this->table->generate();
            
            $data['addProject'] = $isAdmin ? '<a href="'.base_url('projects/create').'" data-target="#modal" data-toggle="modal" class="button small project"><i class="icon-film"></i> Add new project</a>' : '';
                                                                                                                                
            $this->template->load('projects/project_overview', $data);
        }

        /**
         * creates a shortcode for the project
         * 
         * @version 0.4
         * 
         * @param $title String full name of the project
         */
        private function create_shortcode($title)
        {
            $title = str_replace(' ', '', $title);  
            $i = 4;
            while(!isset($title[$i]))
            {
                $title[$i]=chr(65 + mt_rand(0, 25));
                $i --;
            }
            $shortcode = substr(strtoupper($title),0,5);
    
            $maxcodes = pow(26, 3);
            $protect = 0;
            $isDuplicate = $this->check->duplicate($shortcode);
            
            while($protect <= $maxcodes && $isDuplicate)
            {
                $randomfields = floor($protect/26);
                for ($i=4; $i >= 4-$randomfields ; $i--) { 
                    $shortcode[$i]=chr(65 + ($protect%26));
                }
                $isDuplicate = $this->check->duplicate($shortcode);
                $protect++;
            }

            if($isDuplicate)
                return 'CAPACITY';
            return $shortcode;
        }

        /**
         * create and edit form for projects.
         * 
         * @param String $atWork 'create' or 'edit'
         */
        public function form($atWork = null)
        {
            $this->load->library('form_validation');
        
            if($atWork == 'create')
                $this->form_validation->set_rules('title', 'Title', 'is_unique[project.title]|required|trim|xss_clean');
            else
                $this->form_validation->set_rules('title', 'Title', 'required|trim|xss_clean|callback_check->title[project,'.$this->input->post('id').',edit]');
            $this->form_validation->set_rules('deadline', 'Deadline', 'required|trim|xss_clean');
            $this->form_validation->set_rules('shortcode', 'Shortcode', 'callback_check->shortcode|trim|xss_clean');

            if ($this->form_validation->run() == FALSE)
            {
                if($atWork == 'create')
                    $this->create();
                else
                    $this->edit($this->input->post('id'));
            }
            else
            {                
                $title = $this->input->post('title');
                $shortcode = $this->input->post('shortcode');
                
                if($shortcode == '')
                    $shortcode=$this-> create_shortcode($title);
                // if ($shortcode == 'CAPACITY') -> problem, to many projects
                $logo = $this->input->post('logo');
            
                if($logo == '' || $logo === FALSE)
                    $logo =  NULL;
                                    
                $data = array(  'title' => $title,
                                'category_id' => $this -> input -> post('category'),
                                'shortcode' => $shortcode,
                                'logo'      => $logo,
                                'deadline' => date('Y-m-d H:i:s', strtotime($this->input->post('deadline'))));
                                
                if($atWork == 'edit')
                {
                    $data['project_id'] = $this->input->post('id');
                    $data['description'] = $this->input->post('description');
                    
                    if(!$this->db_model->get_single('userproject', 'project_id = '.$data['project_id'], 'username'))
                    {
                        $this->db_model->insert('userproject', array('project_id' =>$data['project_id'], 'username' => $this->input->post('director')));
                        $this->user_model->assign_to_project( $this->input->post('director'), $data['project_id']);
                    }
                    else
                       $this->db_model->update('userproject', array('project_id' =>$data['project_id']), array('username' => $this->input->post('director')));
                    
                    $this->db_model->update('project', array('project_id'=>$data['project_id']), $data);
                    echo 'done';
                }
                else
                {
                    $this->db_model->insert('project', $data);
                    
                    $project = $this->db_model->get_single('project', "title = '$title'");
                    $project_id = $project['project_id'];
                    
                    $new_project_event_id = $this->db_model->get_single('logtype', 'event = "new_project"');
                    $logdata['logtype_id'] = $new_project_event_id['logtype_id'];
                    $logdata['link'] = $project_id;
                    
                    $this->db_model->insert('globallog', $logdata);
                    $this->db_model->insert('userproject', array('username'=>$this->input->post('director'), 'project_id'=> $project_id));
                    $this->db_model->insert('projectobserver', array('username'=>$this->input->post('director'), 'project_id'=> $project_id));
                    echo 'done';
                }
            }
        }

        /**
         * opens the create project modal
         * 
         * @version 1.0
         */
        public function create()
        {
            $session = $this -> session->userdata('logged_in');
            if($this -> permission -> hasPermission('create', 'project'))
            {
                $this->load->helper(array('form'));
                $data['category'] = $this->db_model->get("category");
                $data['users'] = $this->db_model->get('user', NULL, 'username, firstname, lastname');
                $this->load->view('projects/project_creationview', $data);
            }
            else 
                redirect("mystuff/dashboard");
        }   

        /**
         * opens the create project modal
         * 
         * @version 1.0
         * 
         * @param Integer $iproject_d id of the project to edit
         */
        public function edit($project_id = null)
        {
            if(!isset($project_id))
                redirect('error/page_missing');
            if(!$this->permission->hasPermission('edit', 'project', $project_id))
                redirect("projects/view/".$project_id);
            
            $project = $this->db_model->get_single('project', array('project_id' => $project_id));
            $data['category'] = $this->db_model->get("category");
            $data['title'] = 'New Project';
            
            $data['users'] = $this->db_model->get('user', NULL, 'username, firstname, lastname');
            $oldDirector = $this -> db_model -> get_single('userproject', array('project_id' => $project_id), 'username');
            $data['oldDirector'] = $oldDirector['username'];
            
            $type_id = $this->assets->convert_name_id('Image');
            $data['logos'] = $this->assets->get_all_projectassets('project', $project['project_id'], "type_id = $type_id");
            $data['logos'][] = array('asset_id'=> NULL, 'title'=>'Default');
            
            $data['new'] = FALSE;
            $data['oldlogo'] = $project['logo'];
            $data['oldTitle'] = $project['title'];
            $data['oldShortcode'] = $project['shortcode'];
            $data['oldDescription'] = $project['description'];
            
            $category = $this->db_model->get_single('category', array('category_id'=>$project['category_id']));
            $category = $category['category_id'];
            $data['oldCategory'] = array($project['category_id']=>$category);
            $data['oldDeadline'] = $project['deadline'];
            $data['id'] = $project_id;
            
            $this->load->view('projects/project_editview', $data);
        }

        /**
         * sets the state of the project specified in $id to finished
         * 
         * @version 1.0
         * 
         * @param Integer $id The project_id of the project to finish
         */
        public function finish($id = null)
        {
            if(!$this->permission->hasPermission('finish', 'project'))
                redirect("projects/view/".$id);
            $this->section_model->finish('project', $id);
            redirect('projects/view/'.$id);
        }

        /**
         * deletes the given project
         * 
         * @version 0.1
         * 
         * @param Integer $id id of the project to delete, default = false
         */     
        public function delete($id = null)
        {
            if(!$this->permission->hasPermission('delete', 'project'))
                redirect("mystuff/dashboard");
            $this->section_model->delete('project', $id, null);
            redirect('projects');
        }
        
        /**
         * applies the given filter and shows projects accordingly
         * 
         * @param mixed $filter filter
         */
        public function filter($filter = null)
        {
            // function available to every user
            if($filter == 'myProjects')
            {
                $session = $this->session->userdata('logged_in');
                $data['projects'] = $this->section_get_model->get_myprojects($session['user']);
                $data['filter'] = 'my:';
            }
            else
            {
                $field = $this->input->post('fields');
                
                if(is_null($filter))
                    $filter = $this->input->post('filter_terms');
    
                if($filter == '' || $field == 'No_Select')
                    redirect('projects');
                    
                $data['projects'] = $this->filter_projects($field, $filter);
                $data['filter'] = $filter;
            }
            
            $data['side'] = strpos(strtolower($data['filter']),'my:') !== false ? 'My Projects' : 'All Projects';
                
            $this->overview($data);
        }

        /**
         * retrieves all projects that pass the given filter
         * 
         * @param String $field field to filter, default null
         * @param String $filter terms to filter the field with (divided by ','), default null
         */
        public function filter_projects($field = null, $filter = null)
        {
            // function available to every user
            if(is_null($filter) || $filter == '' || $field == 'No_Select')
                return;
            
            $filter_terms = array_filter(array_unique(array_map('trim', preg_split( "/(:|,)/", $filter, null, PREG_SPLIT_NO_EMPTY))));
            
            if(empty($filter_terms))
                return $this->db_model->get('project');

            $where = '';
            if(strpos(strtolower($filter),'my:') !== false)
            {
                $session = $this->session->userdata('logged_in');
                $projects = $this->section_get_model->get_myprojects($session['user']);
                
                if(count($filter_terms) == 1)
                    return $projects;

                $project_ids = array_map(function($ele){return $ele['project_id'];}, $projects);
                $where = ' AND p.project_id IN (' . implode(',', $project_ids) . ')';
            }
            
            switch($field)
            {
                case 'category':
                    foreach ($filter_terms as $filter_term)
                    {
                        $category = $this->db_model->get('category', "title LIKE '%$filter_term%'");
                        foreach ($category as $category_item)
                            $filter_conditions[] = $category_item['category_id'];
                    }
                    return empty($filter_conditions) ? array() : $this->db_model->get('project p', "p.category_id IN ('" . implode(',', $filter_conditions) . "') $where ORDER BY category_id");
                    
                case 'director':
                    foreach ($filter_terms as $filter_term)
                    {
                        $fullname = explode(' ', $filter_term);
                        $usernames = count($fullname) <= 1 ? $this->get_usernames($filter_term) : $this->get_usernames($fullname[0], $fullname[1]);
                        foreach ($usernames as $username)
                            $filter_conditions[] = $username['username'];
                    }
                    
                    return empty($filter_conditions) ? array() : $this->db_model->get(  'project p, userproject up',
                                                                                        "up.project_id = p.project_id AND up.username REGEXP '" . implode('(.*)|(.*)', $filter_conditions) . "' $where ORDER BY title", 'p.*');
                    
                case 'status':
                    foreach ($filter_terms as $filter_term)
                    {
                        $status = $this->db_model->get('status', "title LIKE '%$filter_term%'");
                        foreach ($status as $status_item)
                            $filter_conditions[] = $status_item['status_id'];
                    }
                    return empty($filter_conditions) ? array() : $this->db_model->get('project p', "p.status_id IN ('" . implode(',', $filter_conditions) . "') $where ORDER BY status_id");
                    
                case 'startdate':
                    $query = array();
                    foreach ($filter_terms as $filter_term)
                        $query[] = "p.startdate LIKE '$filter_term%'";
                    return $this->db_model->get('project p', implode(' OR ', $query).$where);
                    
                case 'enddate':
                    $query = array();
                    foreach ($filter_terms as $filter_term)
                        $query[] = "p.enddate LIKE '$filter_term%'";
                    return $this->db_model->get('project p', implode(' OR ', $query).$where);
                    
                default:
                    $filter_conditions = $filter_terms; break;
            }

            return empty($filter_conditions) ? array() : $this->db_model->get('project p', "p.$field REGEXP '" . implode('(.*)|(.*)', $filter_conditions) . "' $where ORDER BY $field");
        }

        private function get_name($username)
        {
            $query = $this->db_model->get_single('user', "username = '$username'", 'firstname, lastname');
            return implode(' ', $query);
        }
        
        private function get_usernames($firstname, $lastname = null)
        {
            return $this->db_model->get('user', "firstname = '$firstname' OR lastname = ".(is_null($lastname) ? "'$firstname'" : "'$lastname'"), 'username');
        }
    }
?>