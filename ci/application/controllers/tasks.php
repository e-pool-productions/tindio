<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	 * 	Controller for tasks
	 */
    class Tasks extends MY_Controller 
    {
    	/**
		 * loads required models, libraries and helpers
		 */
        function __construct()
        {
            parent::__construct();
            $this->load->model(array('page_model', 'section_model', 'section_get_model', 'check', 'assets' ,'permission'));   
            $this->load->library(array('gravatar', 'table', 'form_validation'));
			$this->load->helper('form');
        }
        
        /**
         * redirects to the page of the individual task with the id $id
         * 
         * @version 1.0
         * 
         * @param Integer/Boolean $task_id id of the task to view        , default = -1
         */
        public function view($task_id = null)
        {
        	// function available to every user
            if(is_null($task_id) || !$this->db_model->get('task', "task_id = $task_id"))
            {
                redirect('mystuff/dashboard', 'refresh');
            }
   
            $task = $this->db_model->get_single('task', "task_id = $task_id");
            $data = $this->section_model->gather_task_details($task, true);

            $isAdmin = $this->permission->isAdmin();            
            $isDirector = $this->permission->isDirector('task', $task['project_id']);
            $isScSup = $this->permission->isScSup($data['scene']['scene_id']);
            $isShSup = $this->permission->isShSup($task['shot_id']);
            $isArtist = $this->permission->isArtist($task['task_id']);
            
            $data['button'] = '';
            $control = $isAdmin || $isDirector || $isScSup || $isShSup;
            
            if($control)
            {
                switch($task['status_id'])
                {
                    case STATUS_UNASSIGNED:     $data['button'] = '<a href="'.base_url('users/show/task/'.$task_id) .'" class="button small task"><i class="icon-user"></i> Recruit new User</a>'; break;
                    case STATUS_PRE_PRODUCTION: $data['button'] = '<a href="'.base_url('tasks/setInProgress/'.$task['task_id']).'" class="button small task"><i class="icon-start"></i> Start Task</a>'; break;
                    case STATUS_FOR_APPROVAL:   $data['button'] = '<a href="'.base_url('tasks/setInProgress/'.$task['task_id']).'" class="button small task"><i class="icon-in-progress"></i> Set to In Progress</a>
                                                                 <a href="'.base_url('tasks/finish/'.$task['task_id']).'" class="button small task "><i class="icon-finished"></i> Finish Task</a>'; break;
                    case STATUS_FINISHED:       $data['button'] = '<a href="'.base_url('tasks/setInProgress/'.$task['task_id']).'" class="button small task"><i class="icon-in-progress"></i> Set to In Progress</a>'; break;
                }
            }
            
            if($isArtist)
            {
                if($task['status_id'] == STATUS_PRE_PRODUCTION)
                    $data['button'] = '<a href="'.base_url('tasks/setInProgress/'.$task['task_id']).'" class="button small task"><i class="icon-start"></i> Start Task</a>';
            
                if($task['status_id'] == STATUS_IN_PROGRESS)
                    $data['button'] = '<a href="'.base_url('tasks/setForApproval/'.$task['task_id']).'" class="button small task"><i class="icon-for-approval"></i> Set for Approval</a>';
            }
                
            $taskusers = $this->section_get_model -> get_users('task', $task['task_id']);
            $data['artist_string'] = '';
            foreach($taskusers as $user_item)
            {
                $data['artist_string'] .=   '<img src="'.$this->gravatar->get_gravatar($user_item['gravatar_email']).'?s=15" >
                                             <a href="'.base_url('users/view/'.$user_item['username']).'"data-target="#modal" data-toggle="modal">'.$user_item['firstname'].' '.$user_item['lastname'].'</a>'.'  ';
            }
            $data['artist_string'] .= '<br/>';

            $data['permissions']['recruit'] = $this->permission->hasPermission('recruit', 'task', $task_id);
            $data['permissions']['edit'] = $this->permission->hasPermission('edit', 'task', $task_id);
			$data['permissions']['comment'] = $control;

            //task Files                                   
            $taskfiles = $this->assets->get_assets('task', $task_id);
            $localfiles = array_filter($taskfiles, function($el){ return $el['local']; });
            $outputfiles = array_filter($taskfiles, function($el){ return !$el['local']; });
            
            $data['localfiles'] = $this->page_model->createOutputFileTable('task', $task_id, 'Description', $localfiles);
            $data['outputfiles'] = $this->page_model->createOutputFileTable('task', $task_id, 'Status', $outputfiles);
            
            $data['task'] = $task;
            $data['title'] = $task['title'];
			$data['permissions']['upload'] = $this->permission->hasPermission('upload', 'task' , $task_id);
            $this->template->load('tasks/task_infoview', $data);
        }

		/**
		 * inserts a new Task into the database, or edits an existing Task
		 * 
		 * @param String $atWork 'create' if creating a new Task, or 'edit' if editing an existing Task
		 */
        public function form($atWork)
        {
            $task_id = $this -> input -> post('task_id');
            $shot_id = $this-> input->post('shot_id');
        
            if($atWork == 'create')
                $this->form_validation->set_rules('title', 'Title', 'callback_check->title[task,'.$shot_id.',create]|required|trim|xss_clean');
            else
                $this->form_validation->set_rules('title', 'Title', 'callback_check->title[task,'.$task_id.',edit]|required|trim|xss_clean');
           
            $this->form_validation->set_rules('deadline', 'Deadline', 'required|trim|xss_clean');
        
            if ($this->form_validation->run() == FALSE)
            {
                if($atWork == 'create')
                    $this->create($shot_id);
                else
                    $this->edit($task_id);
            }
            else
            {
            	$oldOrder = $this->input->post('oldOrder');	
            	if($atWork == 'edit')
				{
					$currentOrder = $this->db_model->get_single('task', array('task_id'=>$task_id), 'orderposition');
					if(!$this->db_model->get_single('task', array('task_id'=>$task_id), 'title') || $oldOrder != $currentOrder['orderposition'])
					{
						echo 'done';
						return;
					}
				}
                $order = $this->section_model->calc_orderposition('task', 'shot', $shot_id, $this->input->post('order'), $this->input->post('oldOrder'));
                $maxPosOrder = count($this->db_model->get('task', array('shot_id'=>$shot_id), 'task_id'));
                if($atWork == 'create')
                    $maxPosOrder++;
                if($order > $maxPosOrder)
                    $order = $maxPosOrder;
                $data = array(  'title' => $this->input->post('title'),
                                'description' => $this->input->post('description'),
                                'deadline' => date('Y-m-d H:i:s', strtotime($this->input->post('deadline'))),
                                'orderposition' => $order);
                                
                if($atWork == 'create')
                {
                    $shot = $this -> db_model -> get_single('shot', "shot_id = $shot_id", 'status_id, project_id');
                    
                    $data['shot_id'] = $shot_id;
                    $data['project_id'] = $shot['project_id'];
                    
                    $this->db_model->insert('task', $data);
                    
                    if($shot['status_id'] == STATUS_FOR_APPROVAL || $shot['status_id'] == STATUS_FINISHED)
                        $this -> db_model -> update('shot', "shot_id = $shot_id", array('status_id' => STATUS_IN_PROGRESS, 'enddate' => null));
                }
                else
                    $this->db_model->update('task', "task_id = $task_id" ,$data);
                    
                echo 'done';
            }
        }

        /**
         * redirects to the create task page
		 * 
		 * @param Integer $shot_id id of the shot to create the task in, default null
         * 
         * @version 1.0
         */
        public function create($shot_id = null)
        {
            if(!isset($shot_id))
                redirect('error/page_missing');
            if(!$this->permission->hasPermission('create', 'task', $shot_id))
				redirect("mystuff/dashboard");
            
            $data['shot_id'] = $shot_id;
            
            $data['tasks'] = $this->db_model->get('task', 'shot_id ="'.$shot_id.'" ORDER BY orderposition', 'title , task_id');
            $data['tasks'][] = array('title'=>'End of Shot', 'task_id' => -1);
            
            $data['new'] = TRUE;
            $data['oldTitle'] = set_value('title');
            $data['oldDescription'] = '';
            $data['task_id'] = '';
            $data['oldDeadline'] = set_value('deadline');
            $data['oldOrder'] = '';
            
            $this->load->view('tasks/task_creationview', $data);
        }
        
        /**
         * redirects to the edit task page
         * 
         * @version 1.0
         * 
         * @param Intege/Boolean $task_id id of the task to edit , default = null
         */
        public function edit($task_id = null)
        {
            if(!isset($task_id))
                redirect('error/page_missing');
			if(!$this->permission->hasPermission('edit', 'task', $task_id))
				redirect("tasks/view/".$task_id);	
            
            $data['task_item'] = $this->db_model->get_single('task', array('task_id' => $task_id));
            
            $data['new'] = FALSE;
            $data['oldTitle'] = $data['task_item']['title'];
            $data['oldDeadline'] = $data['task_item']['deadline'];
            $data['oldOrder'] = $data['task_item']['orderposition'];
            $data['oldDescription'] = $data['task_item']['description'];
            
            $data['task_id'] = $task_id;
            $data['shot_id'] = $data['task_item']['shot_id'];
            
            $data['tasks'] = $this->db_model->get('task', 'shot_id ="'.$data['shot_id'].'" ORDER BY orderposition', 'title , task_id');
            $data['tasks'][] = array('title'=>'End of Shot', 'task_id'=>-1);
            
            $this->load->view('tasks/task_creationview', $data);
        }

		/**
		 * writes a new comment into the database
		 * 
		 * @param Integer $task_id id of the task to post the comment in
		 * @param Boolean $submit determines if the comment is posted or not, default false
		 */
        public function new_comment($task_id, $submit = false)
        {
			if(!$this->permission->hasPermission('comment', 'task', $task_id))
				redirect("tasks/view/".$task_id);	
			        	
            if(!$submit)
            {
                $this->load->view('tasks/new_comment', array('task_id' => $task_id));
            }
            else
            {
                $session = $this->session->userdata('logged_in');
                $message = $this->input->post('message');
            
                if($message != '')
                {                
                    $data = array(  'username'  =>  $session['user'],
                                    'message'   =>  $message,
                                    'task_id'   =>  $task_id);
                                    
                    $this->db_model->insert('comment',$data);
                    unset($data);
                }
                redirect('tasks/view/'.$task_id);
            } 
        }
        
		/**
		 * sets the given task to in_progress
		 * 
		 * @param Integer $id id of the Task to set to in_progress, default false
		 */
        public function setInProgress($id = null)
        {
			if(!$this->permission->hasPermission('setInProgress', 'task', $id))
				redirect("tasks/view/".$id);
			
            $this->section_model->setInProgress('task', $id);
            redirect('tasks/view/'.$id);
        }
        
		/**
		 * sets a given Task to for_approval
		 * 
		 * @param Integer $id id of the Task to set to for_approval, default false
		 */
        public function setForApproval($id = null)
        {
			if(!$this->permission->hasPermission('setForApproval', 'task', $id))
				redirect("tasks/view/".$id);        	
            $this->section_model ->setForApproval('task', $id);
            redirect('tasks/view/'.$id,'refresh');
        }
        
		/**
		 * sets a given Task to finished.
		 * Also checks and updates the status of the corresponding Shot and sets the Endtime of the task
		 * 
		 * @param Integer $id id of the Shot to set to finished
		 */
        public function finish($id = null)
        {
			if(!isset($id))
				redirect('error/page_missing');
			if(!$this->permission->hasPermission('finish', 'task', $id))
				redirect("tasks/view/".$id);
			        	
            $task = $this -> db_model -> get_single('task', array('task_id' => $id));
            
            if($task['status_id'] == STATUS_IN_PROGRESS || $task['status_id'] == STATUS_FOR_APPROVAL)
            {
                $now = new DateTime("");
                $nowstring = $now->format('Y-m-d H:i:s');
            
                $this -> db_model -> update('task', array('task_id' => $task['task_id']), array('status_id' => STATUS_FINISHED, 'enddate' => $nowstring));
                $shot = $this ->  db_model -> get_single('shot', array('shot_id' => $task['shot_id']));
                $finishedtasks = $this -> db_model -> get('task', array('shot_id' => $task['shot_id'], 'status_id' => STATUS_FINISHED));
                $alltasks = $this -> db_model -> get('task', array('shot_id' => $task['shot_id']));
                if(count($finishedtasks) == count($alltasks))
                   $this -> db_model -> update('shot', array('shot_id' => $task['shot_id']), array('status_id' => STATUS_FOR_APPROVAL));
            }
            else
            {
                echo '<script type="text/javascript">alert("No Cheating allowed, please finish the work first!");</script>';
            }
            redirect('tasks/view/'.$id,'refresh');
        }

        /**
         * deletes a Task
         * 
         * @version 1.0
         * 
         * @param int $id id of the Task to delete
         */
        public function delete($id = null)
        {
       		if(!$this->permission->hasPermission('delete', 'task', $id))
				redirect("mystuff/dashboard");
						
            $parent_id = $this->section_model->delete('task', $id, 'shot');
            
            if($parent_id == -1)
                redirect('projects');
            
            redirect('shots/view/'.$parent_id);
        }
		
		/**
		 * sets the approved status of a given asset in a given Task
		 * 
		 * @param Integer $task_id id of the Task to set the status of the asset in
		 * @param Integer $asset_id id of the asset to  set the status of
		 * @param Boolean $approved status to set the asset to
		 */
        function approveFile($task_id, $asset_id, $approved)
        {
			if(!$this->permission->hasPermission('approveFile', 'task', $task_id))
				redirect("tasks/view/".$task_id);        	
            $this->db_model->update('taskasset', "asset_id = $asset_id", array('approved' => $approved));
            redirect('tasks/view/' . $task_id);
        }
    }
?>
