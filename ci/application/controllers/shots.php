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
            $this->load->model(array('page_model', 'section_model', 'assets', 'check', 'permission'));
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
			
			$data['permissions']['edit'] = $this->permission->hasPermission('edit', 'shot', $shot_id);
			$data['permissions']['create'] = $this->permission->hasPermission('create', 'task', $shot_id);

			$hasOptions = $this->permission->hasPermission('delete', 'task', $shot_id);
			
			// Task-Table
			if(!empty($data['tasks']))
			{
				$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
				
				$edit = $data['permissions']['edit'] ? EDIT_ICON : '';
				
				$heading = array(	array('data' => $edit),
									array('data' => 'Task '.$edit),
									array('data' => 'Description '.$edit),
									array('data' => 'Assigned to'),
									array('data' => 'Status'),
									array('data' => 'Details'),
									array('data' => 'Files')
								);
								
				if($hasOptions)
					$heading[] = array('data' => '');
								
				$this->table->set_heading($heading);
				
				foreach ($data['tasks'] as $task)
	            {
	            	$info = $this->section_model->gather_task_details($task);
	
					$editUrl = base_url('tasks/edit/'.$task['task_id']);
					
	            	$row = array(	'0' => array('data' => $task['orderposition'], 'onclick' => 'edit(this, "'.$editUrl.'/orderposition")'),
									'1' => array('data' => '<div style="overflow-x:auto">'.$info['task']['title'].'</div>', 'class' => 'wordwrap', 'onclick' => 'if(link) edit(this, "'.$editUrl.'/title")'),
									'2' => array('data' => '<div style="overflow-x:auto">'.$task['description'].'</div>', 'class' => 'wordwrap', 'onclick' => 'edit(this, "'.$editUrl.'/description")'),
									'3' => array('data' => $this -> task_assignments($task)),
									'4' => array('data' => $info['status']),
									'5' => array('data' => 'Started:	'. $info['startdate'] .br(1).'
															Finished:	'. $info['enddate'] .br(1).'	
															Deadline: 	'. $info['deadline'].br(1).'
															Duration:	'. $info['duration'] .br(1).'
															Crew:		'. $info['crewtext']),
									'6' => array('data' => $info['approved_files'] . '<br/>' . $info['for_approval_files'])
									);
									
					if($hasOptions)
						$row[] = array('data' => '<a onclick="return confDelete(\'task\');" href="' . base_url('tasks/delete/' . $task['task_id']). '"><i class="fa fa-times" title="delete"></i></a>');
									
									
					$this->table->add_row($row);
				}
			}

			$data['tasktable'] = !empty($data['tasks']) ? $this->table->generate() : '';
			$data['usertable'] = $this->page_model->createUserTable('shot', $shot_id);
			$data['shotfiles'] = $this->page_model->createOutputFileTable('shot', $shot_id, 'Status', $this->assets->get_assets('shot', $shot_id));

			$data['shot'] = $shot;
			$data['button']= $this->page_model->createButton('shot', $shot_id, $shot['status_id'], $data['tasksfinished'] == $data['taskcount']);
			$data['maxOrderposition'] = count($this->db_model->get('task', array('shot_id' => $shot_id), 'task_id'));
			
            $this->template->load('pages/shot_infoview', $data);
        }

		/**
		 * inserts a new Shot into the database, or edits an existing Shot
		 * 
		 * @param String $atWork 'create' if creating a new Shot, or 'edit' if editing an existing Shot
		 */
		public function form()
		{
			$scene_id = $this -> input -> post('parent_id');
        
            $this->form_validation->set_rules('title', 'Title', 'callback_check->title[shot,'.$scene_id.',create]|required|trim|xss_clean');
            $this->form_validation->set_rules('deadline', 'Deadline', 'required|trim|xss_clean');
        
            if ($this->form_validation->run() == FALSE)
            	$this->create($scene_id);
            else
            {
				$logo = $this->input->post('logo');
				if($logo == '')
					$logo = NULL;
                
				$order = $this->input->post('order') + 1;
				$this->section_model->calc_orderposition('shot', 'scene', $scene_id, $order, $this->input->post('oldOrder'));
				
				$maxPosOrder = count($this->db_model->get('shot', array('scene_id'=>$scene_id), 'shot_id')) + 1;

                if($order > $maxPosOrder)
					$order = $maxPosOrder;
				
                $scene = $this -> db_model -> get_single('scene', "scene_id = $scene_id", 'status_id, project_id');
                
                $data = array(  'title' => $this->input->post('title'),
                                'description' => $this->input->post('description'),
                                'deadline' => date('Y-m-d H:i:s', strtotime($this->input->post('deadline'))),
                                'logo' => $logo,
                                'orderposition' => $order,
								'scene_id' => $scene_id,
								'project_id' => $scene['project_id']);

				$this->db_model->insert('shot', $data);

            	if($scene['status_id'] == STATUS_FOR_APPROVAL || $scene['status_id'] == STATUS_FINISHED)
              		$this -> db_model -> update('scene', "scene_id = $scene_id", array('status_id' => STATUS_IN_PROGRESS, 'enddate' => null)); 

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
                
            $data['parent_id'] = $scene_id;
            $data['sec_items'] = $this->db_model->get('shot', 'scene_id ="'.$scene_id.'" ORDER BY orderposition', 'title , shot_id');   
            $data['sec_items'][] = array('title'=>'End of Scene', 'shot_id' => -1);
            
            $data['logos'] = $this->assets->get_all_projectassets('scene', $scene_id, 'type_id = '.IMAGE);
            $data['logos'][] = array('asset_id'=> NULL, 'title'=>'Default');
            
            $data['section'] = 'shot';

            $this->load->view('pages/creationview', $data);
        }

		/**
		 * performs the edit of the specific field
		 * 
		 * @version 1.0
		 * 
		 * @param Integer $shot_id -> id of the shot to edit
		 * @param String $field -> field to edit, e.g. title, description
		 */
		function edit($shot_id, $field)
        {
        	if(!$this->permission->hasPermission('edit', 'shot', $shot_id))
				echo 'Permission denied!';
			
			echo $this->section_model->edit('shot', $shot_id, $field);
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
			$taskusers = $this -> section_model -> get_users('task', $task_item['task_id']);
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
