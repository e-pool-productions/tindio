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
            $this->load->model(array('page_model', 'section_model','user_model' , 'check', 'permission', 'assets'));
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
			
			$data['permissions']['edit']= $this -> permission -> hasPermission('edit', 'project', $project_id);
            
            $isAdmin = $this->permission->isAdmin();            
            $isDirector = $this->permission->isDirector('project', $project_id);
            
            //Set Button
            $data['button'] =   !$isAdmin && !$isDirector ||    
                                empty($data['scenes']) || 
                                $project['status_id'] == STATUS_FINISHED ||
                                $data['scenecount'] != $data['scenesfinished'] ? '' :
                                '<a href="'. base_url('projects/finish/'.$project_id) .'" class="btn btn-default btn-sm"><i class="fa fa-circle-o"></i> Finish Project</a>';

			$hasOptions = $this->permission->hasPermission('delete', 'scene', $project_id);
			
            //Scene-Table
            if(!empty($data['scenes']))
			{
				$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
			
				$edit = $data['permissions']['edit'] ? EDIT_ICON : '';
	            
	            $heading = array(	array('data' => $edit),
	            					array('data' => $edit),
									array('data' => 'Title '.$edit),
									array('data' => 'Code'),
									array('data' => 'Description '.$edit),
									array('data' => 'Status'),
									array('data' => 'Shots'),
									array('data' => 'Details')
								);
									
				if($hasOptions)
					$heading[] = array('data' => '');
	                                    
	            $this->table->set_heading($heading);
	
	            foreach ($data['scenes'] as $scene)
	            {           
	                $info = $this->section_model->gather_scene_details($scene);
	 
	                $editUrl = base_url('scenes/edit/'.$scene['scene_id']);
	
	                $row = array(   array('data' => '<img src="'.$info['logo']['path'].'" id="'.$info['logo']['id'].'" class="img-responsive img-thumbnail">', 'onclick' => 'edit(this, "'.$editUrl.'/logo")'),
	                				array('data' => $scene['orderposition'], 'onclick' => 'edit(this, "'.$editUrl.'/orderposition")'),
	                                array('data' => '<div style=\'overflow-x:auto\'>'.$info['scene']['title'].'</div>', 'class' => 'wordwrap', 'onclick' => 'if(link) edit(this, "'.$editUrl.'/title")'),
	                                array('data' => $info['shortcode']),
	                                array('data' => '<div style=\'overflow-x:auto\'>'.$scene['description'].'</div>', 'class'=> 'wordwrap', 'onclick' => 'edit(this, "'.$editUrl.'/description")'),
	                                array('data' => $info['status']),
	                                array('data' => 'Total:      '.$info['shotcount'].br(1).'
	                                                        Finished:   '.$info['shotsfinished']),
	                                array('data' => 'Started:    '. $info['startdate'] .br(1).'
	                                                        Finished:   '. $info['enddate'] .br(1).'
	                                                        Deadline:   '. $info['deadline'].br(1).'
	                                                        Duration:   '. $info['duration'] .br(1).'
	                                                        Crew:       '. $info['crewtext'])
	                                );
					
					if($hasOptions)
	                    $row[] = array('data' => '<a href="'.base_url('scenes/delete/'.$scene['scene_id']).'" onclick="return confDelete(\'scene\');"><i class="fa fa-times"></i></a>');
	
	                $this->table->add_row($row);
	            }
			}
            
            $data['scenetable'] = !empty($data['scenes']) ? $this->table->generate() : ''; 
            $data['usertable'] = $this->page_model->createUserTable('project', $project_id);
            $data['projectfiles'] = $this->page_model->createOutputFileTable('project', $project_id, null, $this->assets->get_assets('project', $project_id));
            
			$data['isAdmin'] = $isAdmin;
			$data['isDirector'] = $isDirector;

            $data['project'] = $project;
            $data['categories'] = $this->db_model->get('category');
            $data['logos'] = $this->assets->get_all_projectassets('project', $project['project_id'], 'type_id = '.IMAGE);
            $data['logos'][] = array('asset_id'=> '', 'title'=>'Default');
            $data['maxOrderposition'] = count($this->db_model->get('scene', array('project_id' => $project['project_id']), 'scene_id'));
            $data['title'] = $project['title'];
            
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
			if(!empty($data['projects']))
            {
            	$isAdmin = $this->permission->isAdmin();
            
	            $this->load->helper('form');
	            $this->load->library('table');
				
	            $this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
				
				$edit = $isAdmin ? EDIT_ICON : '';
	
	            $heading = array(	array('data' => ''),
		                            array('data' => 'Title '.$edit),
		                            array('data' => 'Code '.$edit),
		                            array('data' => 'Category '.$edit),
		                            array('data' => 'Director'),
		                            array('data' => 'Description '.$edit),
		                            array('data' => 'Status'),
		                            array('data' => 'Details')
	                            );
												
				if($isAdmin)
					$heading[] = array('data' => '');
				
				$this->table->set_heading($heading);
	            
                foreach ($data['projects'] as $project) 
                {
                    $info = $this->section_model->gather_project_details($project);
					
					$editUrl = base_url('projects/edit/'.$project['project_id']);

                    $row = array(   array('data' => '<img src="'.$info['logo']['path'].'" id="'.$info['logo']['id'].'" class="img-responsive img-thumbnail">'),
                                    array('data' => '<div style="overflow-x:auto;">'.$info['project']['title'].'</div>', 'class' => 'wordwrap', 'onclick' => "if(link) edit(this, \"$editUrl/title\")"),
                                    array('data' => $project['shortcode'], 'onclick' => "edit(this, \"$editUrl/shortcode\")"),
                                    array('data' => $info['category']['title'], 'onclick' => "edit(this, \"$editUrl/category_id\")"),
                                    array('data' => $info['directors']),
                                    array('data' => '<div style="overflow-x:auto;">'.$project['description'].'</div>', 'class' => 'wordwrap', 'onclick' => "edit(this, \"$editUrl/description\")"),
                                    array('data' => $info['status']),
                                    array('data' => 'Started:    '. $info['startdate'] .br(1).'
                                                    Finished:   '. $info['enddate'] .br(1).'                 
                                                    Duration:   '. $info['duration'] .br(1).'
                                                    Scenes:     '. $info['scenecount']. ' ['. $info['scenesfinished'] . ' finished] '.br(1).'
                                                    Shots:      '. $info['shotcount'].' ['. $info['shotsfinished'] .' finished] '.br(1).'
                                                    Crew:       '. $info['crewtext'])
                                    );
									
					if($isAdmin) // delete Project
						$row[] = '<a onclick="return confDelete(\'project\');" href="'.base_url('projects/delete/' . $project['project_id']).'"><i class="fa fa-times"></i></a>';
					
                    $this->table->add_row($row);
                }

				$data['all_projects'] = $this->table->generate();
                unset($data['projects']);
            }
            
			if(!isset($data['all_projects']))
            	$data['all_projects'] = '';

			$data['title'] = 'Projects';
                                                                                                                                
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
        public function form()
        {
            $this->load->library('form_validation');
        
            $this->form_validation->set_rules('title', 'Title', 'is_unique[project.title]|required|trim|xss_clean');
            $this->form_validation->set_rules('deadline', 'Deadline', 'required|trim|xss_clean');
            $this->form_validation->set_rules('shortcode', 'Shortcode', 'callback_check->shortcode|trim|xss_clean');

            if ($this->form_validation->run() == FALSE)
                $this->create();
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

                $this->db_model->insert('project', $data);
                
                $project = $this->db_model->get_single('project', "title = '$title'", 'project_id');
                $project_id = $project['project_id'];
                
                $new_project_event_id = $this->db_model->get_single('logtype', 'event = "new_project"', 'logtype_id');
                $logdata['logtype_id'] = $new_project_event_id['logtype_id'];
                $logdata['link'] = $project_id;
                
                $this->db_model->insert('globallog', $logdata);
                $this->db_model->insert('userproject', array('username'=>$this->input->post('director'), 'project_id'=> $project_id));
                $this->db_model->insert('projectobserver', array('username'=>$this->input->post('director'), 'project_id'=> $project_id));
                echo 'done';
            }
        }

        /**
         * opens the create project modal
         * 
         * @version 1.0
         */
        public function create()
        {
            if($this -> permission -> hasPermission('create', 'project'))
            {
                $data['category'] = $this->db_model->get("category");
                $data['users'] = $this->db_model->get('user', NULL, 'username, firstname, lastname');
                $this->load->view('projects/project_creationview', $data);
            }
            else 
                redirect("mystuff/dashboard");
        }

        function edit($project_id, $field)
        {
            if(!$this->permission->hasPermission('edit', 'project', $project_id))
                echo 'Permission denied!';
            
 			echo $this->section_model->edit('project', $project_id, $field);
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
        function filterform($filter = null)
        {
            // function available to every user
            if($filter == 'myProjects')
            {
            	$user = $this->session->userdata('user');
                $data['projects'] = $this->db_model->get(	'project p, projectobserver po',
															"po.username = '$user' AND
															 po.project_id = p.project_id ORDER BY title");
                $data['filter'] = 'my:';
            }
            else
            {
                $field = $this->input->post('fields');
                
                if(is_null($filter))
                    $filter = $this->input->post('filter_terms');
    
                if($filter == '' || $field == 'No_Select')
                    redirect('projects');
                    
                $data['projects'] = $this->filter($field, $filter);
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
        function filter($field = null, $filter = null)
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
                $projects = $this->db_model->get(	'project p, projectobserver po',
													"po.username = '$username' AND
													 po.project_id = p.project_id ORDER BY title");
                
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
						$usernames = $this->db_model->get('user', "firstname = '".$fullname[0]."' OR lastname = '".(isset($fullname[1]) ? $fullname[1] : $fullname[0])."'", 'username');

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
    }
?>