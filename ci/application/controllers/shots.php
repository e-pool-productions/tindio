<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	 * Controller of Shots
	 */
	class Shots extends MY_Controller 
	{
		/**
		 * loads required models, libraries and helpers
		 */
		function __construct()
        {
            parent::__construct();
            $this->load->model(array('page_model', 'section_model', 'assets', 'check', 'section_get_model', 'permission'));
        	$this->load->helper('form');
            $this->load->library(array('table', 'form_validation'));
		}
		
		/**
		 * displays the shot with shot_id $id
		 * 
		 * @param Integer $shot_id shot_id of the shot to display
		 */
        public function view($shot_id = null)
        {
        	// function available to every user
        	if(is_null($shot_id) || !$this->db_model->get_single('shot', "shot_id = $shot_id"))
                redirect('mystuff/dashboard', 'refresh');
			
			$shot = $this->db_model->get_single('shot', "shot_id = $shot_id");
			$data = $this->section_model->gather_shot_details($shot, true);
			
			$isAdmin = $this->permission->isAdmin();			
            $isDirector = $this->permission->isDirector('shot', $shot['project_id']);
            $isScSup = $this->permission->isScSup($shot['scene_id']);
            $isShSup = $this->permission->isShSup($shot['shot_id']);
			
			$data['button']='';
			$control = $isAdmin || $isDirector || $isScSup;
			$allFinished = $data['tasksfinished'] == $data['taskcount'];
			
			if($shot['status_id'] == STATUS_IN_PROGRESS && ($isShSup || $control) && $allFinished)
				$data['button'] = '<a href="'.base_url('shots/setForApproval/'.$shot_id) .'" class="button small"><i class="icon-for-approval"></i> Set for Approval</a>';
			elseif($shot['status_id'] == STATUS_FOR_APPROVAL && $control || $shot['status_id'] == STATUS_FINISHED && ($isAdmin || $isDirector))
			{
				$data['button'] = '<a href="'.base_url('shots/setInProgress/'.$shot_id).'" class="button small"><i class="icon-in-progress"></i> Set to In Progress</a>';
				if($shot['status_id'] == STATUS_FOR_APPROVAL && $control && $allFinished)
					$data['button'] .=	'<a href="'.base_url('shots/finish/'.$shot_id).'" class="button small"><i class="icon-finished"></i> Finish Shot</a>';
			}
			
			// Task-Table
			$this->table->set_template($this->page_model->get_table_template('shot_tasks'));
			
			$this->table->set_heading(array('0' => array('data' => '', 'style' => 'width: 1px'),
            								'1' => array('data' => 'Task', 'style' => 'width: 110px; min-width: 50px'),
            								'2' => array('data' => 'Description', 'style' => 'max-width: 50px'),
            								'3' => array('data' => 'Assigned to', 'style' => 'width: 1px ;max-width: 100px; min-width: 90px'),
            								'4' => array('data' => 'Status', 'style' => 'width: 120px'),
            								'5' => array('data' => 'Details', 'style' => 'width: 75px'),
            								'6' => array('data' => 'Files', 'style'=>'width: 100px'),
            								'7' => array('data' => 'Actions', 'style' => 'width: 1px'))
            							);
			
			foreach ($data['tasks'] as $task)
            {
            	$info = $this->section_model->gather_task_details($task);
				
				$actions = '';
				if($this->permission->hasPermission('editTask','shot', $shot_id))
					$actions = '<a href="'.base_url('tasks/edit/'.$task['task_id']).'" data-target="#modal" data-toggle="modal" class="tooltip"><i class="icon-pencil" title="edit"></i></a> ';
				if($this->permission->hasPermission('deleteTask', 'shot', $shot_id))
					$actions .='<a onclick="return confDelete();" href="' . base_url('tasks/delete/' . $task['task_id']). '" class="tooltip"><i class="icon-remove" title="delete"></i></a>';

				
            	$row = array(	'0' => array('data' => $task['orderposition']),
								'1' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.base_url('tasks/view/'.$task['task_id']).'">'.$task['title'].'</a></div>', 'style' => 'text-align:left; max-width:100px'),
								'2' => array('data' => '<div style=\'overflow-x:auto\'>'.$task['description'].'</div>', 'style'=> 'max-width: 110px; text-align:left'),
								'3' => array('data' => '<div style=\'overflow-x:auto\'>'.$this -> task_assignments($task).'</div>', 'style' => 'max-width: 100px; text-align:left'),
								'4' => array('data' => $info['status']['status'], 'style'=>'color:'.$info['status']['color'].'; min-width: 100px;text-align:left'),
								'5' => array('data' => 'Started:	'. $info['startdate'] .br(1).'
														Finished:	'. $info['enddate'] .br(1).'	
														Deadline: 	'. $info['deadline'].br(1).'
														Duration:	'. br(1).$info['duration'] .br(1).'
														Crew:		'. br(1).$info['crewtext'], 'style'=>'min-width: 55px'),
								'6' => array('data' => $info['approved_files'] . '<br/>' . $info['for_approval_files']),
								'7' => array('data' => $actions , 'style'=>'text-align: center')
								);
				$this->table->add_row($row);
			}

			$data['tasktable'] = $this->table->generate();
			
			$this->table->clear();
			unset($rows);
 			
			// Usertable
			$this->table->set_template($this->page_model->get_table_template('shot_users'));
			
			$this->table->set_heading(array('0' => array('data' => 'Name'),
            								'1' => array('data' => 'Role'),
            								'2' => array('data' => 'Last access'),
            								'3' => array('data' => 'Actions'),
            						));
			$shotusers = $this -> section_get_model -> get_users('shot', $shot_id);

			foreach($shotusers as $user)
			{
				$username = $user['username'];
				$unassign = $isAdmin || $isDirector || $isScSup || $isShSup ?
								'<a onclick="return confUnassign(\''.$user['firstname'].'\', \''.$user['lastname'].'\');" href="'.base_url('users/unassign/'.$username.'/shot/'.$shot_id).'" class="tooltip"><i class="icon-minus-sign" title="unassign user"></i></a>' :
								'';
				
				$row = array(	'0' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.base_url('users/view/'.$username).'" data-target="#modal" data-toggle="modal">'.$user['firstname'].' '.$user['lastname'].'</a></div>', 'style' => 'max-width: 100px'),
								'1' => array('data' => $user['role_title']),
								'2' => array('data' => $user['lastaccess']),
								'3' => array('data' => $unassign, 'style'=> 'text-align:center')
							);
				$this->table->add_row($row);
			}
			
			$data['usertable'] = $this->table->generate();
			
			$this->table->clear();
			
			//shot Files
			$data['shotfiles'] = $this->page_model->createOutputFileTable('shot', $shot_id, 'Status', $this->assets->get_assets('shot', $shot_id));

			if($isAdmin || $isDirector || $isScSup || $isShSup)
			{
				$data['addNewTask'] = '<a href="'.base_url('tasks/create/'.$shot_id).'" data-target="#modal" data-toggle="modal" class="button small">Add new Task</a>';
				$data['addShotSup'] = '<a href="'.base_url('users/show/shot/'.$shot_id).'" class="button small"><i class="icon-user"></i> Add Shot Sup</a>';
				$data['addNewFile'] = '<a href="'.base_url('upload/choose_files/shot_'.$shot_id).'" data-target="#modal" data-toggle="modal" class="button small"><i class="icon-upload-alt"></i></a>';
				$data['linkNewFile']= '<a href="'.base_url('all_assets/link_asset/shot_'.$shot_id).'" class="button small"><i class="icon-link"></i></a>';
			}
			$data['permission']['addWorkflow'] = $this->permission->hasPermission('addWorkflow', 'shot', $shot_id);

			$data['shot'] = $shot;
            $data['title'] = $shot['title'];
			$data['permissions']['edit'] = $this->permission->hasPermission('edit', 'scene', $shot_id);
            $this->template->load('shots/shot_infoview', $data);
        }

		/**
		 * inserts a new Shot into the database, or edits an existing Shot
		 * 
		 * @param String $atWork 'create' if creating a new Shot, or 'edit' if editing an existing Shot
		 */
		public function form($atWork)
		{			
			$shot_id = $this -> input -> post('shot_id');
			$scene_id = $this -> input -> post('scene_id');
        
            if($atWork == 'create')
                $this->form_validation->set_rules('title', 'Title', 'callback_check->title[shot,'.$scene_id.',create]|required|trim|xss_clean');
            else
                $this->form_validation->set_rules('title', 'Title', 'callback_check->title[shot,'.$shot_id.',edit]|required|trim|xss_clean');
            $this->form_validation->set_rules('deadline', 'Deadline', 'required|trim|xss_clean');
        
            if ($this->form_validation->run() == FALSE)
            {
            	if($atWork == 'create')
                    $this->create($scene_id);
                else
                    $this->edit($shot_id);
			}
            else
            {
            	$oldOrder = $this->input->post('oldOrder');	
            	if($atWork == 'edit')
				{
					$currentOrder = $this->db_model->get_single('shot', array('shot_id'=>$shot_id), 'orderposition');
					if(!$this->db_model->get_single('shot', array('shot_id'=>$shot_id), 'title') || $oldOrder != $currentOrder['orderposition'])
					{
						echo 'done';
						return;
					}
				}

				$logo = $this->input->post('logo');
				if($logo == '')
					$logo = NULL;
                
				$order = $this->section_model->calc_orderposition('shot', 'scene', $scene_id, $this->input->post('order'), $this->input->post('oldOrder'));
				$maxPosOrder = count($this->db_model->get('shot', array('scene_id'=>$scene_id), 'shot_id'));
				if($atWork == 'create')
					$maxPosOrder++;
                if($order > $maxPosOrder)
					$order = $maxPosOrder;
                $data = array(  'title' => $this->input->post('title'),
                                'description' => $this->input->post('description'),
                                'deadline' => date('Y-m-d H:i:s', strtotime($this->input->post('deadline'))),
                                'logo' => $logo,
                                'orderposition' => $order);
								
				if($atWork == 'create')
                {
					$scene = $this -> db_model -> get_single('scene', "scene_id = $scene_id", 'status_id, project_id');
					
					$data['scene_id'] = $scene_id;
					$data['project_id'] = $scene['project_id'];
					
					$this->db_model->insert('shot', $data);
					
                	if($scene['status_id'] == STATUS_FOR_APPROVAL || $scene['status_id'] == STATUS_FINISHED)
                  		$this -> db_model -> update('scene', "scene_id = $scene_id", array('status_id' => STATUS_IN_PROGRESS, 'enddate' => null)); 
					
                }
                else
                    $this->db_model->update('shot', "shot_id = $shot_id", $data);
				echo 'done';
            }
		}		

        /**
         * redirects to the create shot page
         * 
		 * @param Integer $scene_id id of the scene to create the shot in
		 * 
         * @version 1.0
         */
        public function create($scene_id = null)
        {
        	if(!isset($scene_id))
				redirect('error/page_missing');
			if(!$this->permission->hasPermission('create', 'shot', $scene_id))
				redirect("shots/view/".$scene_id);   			
			
			$data['scene_id'] = $scene_id;
			$data['shots'] = $this->db_model->get('shot', 'scene_id ="'.$scene_id.'" ORDER BY orderposition', 'title , shot_id');	
			$data['shots'][] = array('title'=>'End of Scene', 'shot_id' => -1);
			
			$type_id = $this->assets->convert_name_id('Image');
            $data['logos'] = $this->assets->get_all_projectassets('scene', $scene_id, "type_id = $type_id");
            $data['logos'][] = array('asset_id'=> NULL, 'title'=>'Default');
			
			$data['new'] = TRUE;
			$data['shot_id'] = '';
			$data['oldLogo'] = '';
			$data['oldTitle'] = set_value('title');
			$data['oldDescription'] = '';
			$data['oldDeadline'] = set_value('deadline');
			$data['oldOrder'] = '';
			
			$scene = $this->db_model->get_single('scene', array('scene_id' => $scene_id));
			$project = $this->db_model->get_single('project', array('project_id' => $scene['project_id']));
			$data['projectName'] = $project['title'];
			
            $this->load->view('shots/shot_creationview', $data);
        }

		/**
		 * redirects to the edit Shot page
		 * 
		 * @version 1.0
		 * 
		 * @param Integer $shot_id id of the shot to edit, default = false
		 */
        public function edit($shot_id = null)
        {
			if(!isset($shot_id))
                redirect('error/page_missing');
			if(!$this->permission->hasPermission('edit', 'shot', $shot_id))
				redirect("shots/view/".$shot_id);
            
            $data['shot_item'] = $this->db_model->get_single('shot', array('shot_id' => $shot_id));

			$data['new'] = FALSE;
			$data['oldLogo'] = $data['shot_item']['logo'];
			$data['oldTitle'] = $data['shot_item']['title'];
            $data['oldDeadline'] = $data['shot_item']['deadline'];
            $data['oldOrder'] = $data['shot_item']['orderposition'];
			$data['oldDescription'] = $data['shot_item']['description'];
			
			$data['shot_id'] = $shot_id;
			$data['scene_id'] = $data['shot_item']['scene_id'];
			
			$data['shots'] = $this->db_model->get('shot', 'scene_id ="'.$data['scene_id'].'" ORDER BY orderposition', 'title , shot_id');
			$data['shots'][] = array('title'=>'End of Scene', 'shot_id' => -1);
            
            $type_id = $this->assets->convert_name_id('Image');
            $data['logos'] = $this->assets->get_all_projectassets('shot', $shot_id, "type_id = $type_id");
            $data['logos'][] = array('asset_id'=> NULL, 'title'=>'Default');
			
            $this->load->view('shots/shot_creationview', $data);
        }

		/**
		 * sets the given Shot to in_progress
		 * 
		 * @param Integer $id id of the Shot to set to in_progress, default false
		 */
		public function setInProgress($id = null)
		{
			if(!$this->permission->hasPermission('setInProgress', 'shot', $id))
				redirect("shots/view/".$id);	
			$this->section_model ->setInProgress('shot', $id);
			redirect('shots/view/'.$id);
		}

		/**
		 * sets a given Shot to for_approval
		 * 
		 * @param Integer $id id of the Shot to set to for_approval, default false
		 */
		public function setForApproval($id = null)
		{
			if(!$this->permission->hasPermission('setForApproval', 'shot', $id))
				redirect("shots/view/".$id);				
			$this->section_model->setForApproval('shot', $id);
			redirect('shots/view/'.$id);
		}

		/**
		 * sets a given Shot to finished.
		 *  
		 * @param Integer $id id of the Shot to set to finished
		 */
		public function finish($id = null)
		{
			if(!$this->permission->hasPermission('finish', 'shot', $id))
				redirect("shots/view/".$id);				
			$this->section_model->finish('shot', $id);
			redirect('shots/view/'.$id);
		}
		
		/**
		 * deletes the given shot
		 * 
		 * @version 0.1
		 * 
		 * @param Integer $id id of the shot to delete, default = false
		 */
        public function delete($id = null)
        {
			if(!$this->permission->hasPermission('delete', 'shot', $id))
				redirect("shots/view/".$id);	        	
        	$parent_id = $this->section_model->delete('shot', $id, 'scene');
			
			if($parent_id == -1)
				redirect('projects');
			
			redirect('scenes/view/'.$parent_id);
        }

		/**
		 * returns the users assigned to the given task
		 * 
		 * @param Array $task_item task to get the assigned user for
		 * 
		 * @return String links and names of the users assigned to the task
		 */
		private function task_assignments($task_item)
		{
			$taskusers = $this -> section_get_model -> get_users('task', $task_item['task_id']);
			$output = '';
			foreach ($taskusers as $user) 
			{
				$user_url = base_url('users/view/'.$user['username']);
				$output .= '<a href="'.$user_url.'">'.$user['firstname'].' '.$user['lastname'].'</a>'.br(1);
			}
			return $output;
		}
 
 		/**
		 * sets the approved status of a given asset in a given Shot
		 * 
		 * @param Integer $shot_id id of the Shot to set the status of the asset in
		 * @param Integer $asset_id id of the asset to  set the status of
		 * @param Boolean $approved status to set the asset to
		 */       
        function approveFile($shot_id, $asset_id, $approved)
        {
			if($this->permission->hasPermission('approveFile', 'shot', $shot_id))
		    	$this->db_model->update('shotasset', "asset_id = $asset_id", array('approved' => $approved));
            redirect('shots/view/' . $shot_id);
        }
	}
?>
