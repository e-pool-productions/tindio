<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     *  Controller for tasks
     */
    class Tasks extends MY_Controller 
    {
        /**
         * loads required models, libraries and helpers
         */
        function __construct()
        {
            parent::__construct();
            $this->load->model(array('page_model', 'section_model', 'check', 'assets' ,'permission'));   
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
        function view($task_id = null)
        {
            // function available to every user
            if(is_null($task_id) || !$this->db_model->get('task', "task_id = $task_id"))
                redirect('mystuff/dashboard');
   
            $task = $this->db_model->get_single('task', "task_id = $task_id");
            $data = $this->section_model->gather_task_details($task, true);

            $data['permissions']['recruit'] = $this->permission->hasPermission('recruit', 'task', $task_id);
            $data['permissions']['edit'] = $this->permission->hasPermission('edit', 'task', $task_id);
            $data['permissions']['comment'] = $this->permission->hasPermission('comment', 'task', $task_id);

            //task Files                                   
            $taskfiles = $this->assets->get_assets('task', $task_id);
            $localfiles = array_filter($taskfiles, function($el){ return $el['local']; });
            $outputfiles = array_filter($taskfiles, function($el){ return !$el['local']; });
            
            $data['localfiles'] = $this->page_model->createOutputFileTable('task', $task_id, 'Description', $localfiles);
            $data['outputfiles'] = $this->page_model->createOutputFileTable('task', $task_id, 'Status', $outputfiles);
            
            $data['task'] = $task;
			$data['button'] = $this->page_model->createButton('task', $task_id, $task['status_id'], true);
            $data['permissions']['upload'] = $this->permission->hasPermission('upload', 'task' , $task_id);
			
            $this->template->load('pages/task_infoview', $data);
        }

        /**
         * inserts a new Task into the database, or edits an existing Task
         * 
         * @param String $atWork 'create' if creating a new Task, or 'edit' if editing an existing Task
         */
        function form()
        {
            $shot_id = $this->input->post('parent_id');
        
            $this->form_validation->set_rules('title', 'Title', 'callback_check->title[task,'.$shot_id.',create]|required|trim|xss_clean');
            $this->form_validation->set_rules('deadline', 'Deadline', 'required|trim|xss_clean');
        
            if ($this->form_validation->run() == FALSE)
                $this->create($shot_id);
            else
            {
                $order = $this->input->post('order') + 1;
                $this->section_model->calc_orderposition('task', 'shot', $shot_id, $order, $this->input->post('oldOrder'));
                
                $maxPosOrder = count($this->db_model->get('task', array('shot_id'=>$shot_id), 'task_id')) + 1;

                if($order > $maxPosOrder)
                    $order = $maxPosOrder;
                
                $shot = $this -> db_model -> get_single('shot', "shot_id = $shot_id", 'status_id, project_id');
                
                $data = array(  'title' => $this->input->post('title'),
                                'description' => $this->input->post('description'),
                                'deadline' => date('Y-m-d H:i:s', strtotime($this->input->post('deadline'))),
                                'orderposition' => $order,
                                'shot_id' => $shot_id,
                                'project_id' => $shot['project_id']);

                $this->db_model->insert('task', $data);
                    
                if($shot['status_id'] == STATUS_FOR_APPROVAL || $shot['status_id'] == STATUS_FINISHED)
                    $this -> db_model -> update('shot', "shot_id = $shot_id", array('status_id' => STATUS_IN_PROGRESS, 'enddate' => null));
                    
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
        function create($shot_id = null)
        {
            if(!isset($shot_id))
                redirect('error/page_missing');
            if(!$this->permission->hasPermission('create', 'task', $shot_id))
                redirect("mystuff/dashboard");
            
            $data['parent_id'] = $shot_id;
            $data['sec_items'] = $this->db_model->get('task', 'shot_id ="'.$shot_id.'" ORDER BY orderposition', 'title , task_id');
            $data['sec_items'][] = array('title'=>'End of Shot', 'task_id' => -1);
            $data['section'] = 'task';
            
            $this->load->view('pages/creationview', $data);
        }
        
        /**
         * redirects to the edit task page
         * 
         * @version 1.0
         * 
         * @param Intege/Boolean $task_id id of the task to edit , default = null
         */
        function edit($task_id, $field)
        {
            if(!$this->permission->hasPermission('edit', 'task', $task_id))
                echo 'Permission denied!';

            echo $this->section_model->edit('task', $task_id, $field);
        }

        /**
         * writes a new comment into the database
         * 
         * @param Integer $task_id id of the task to post the comment in
         * @param Boolean $submit determines if the comment is posted or not, default false
         */
        function new_comment($task_id, $submit = false)
        {
            if(!$this->permission->hasPermission('comment', 'task', $task_id))
                redirect("tasks/view/".$task_id);   
                        
            if(!$submit)
                $this->load->view('pages/new_comment', array('task_id' => $task_id));
            else
            {
                $message = $this->input->post('message');
            
                if($message != '')
                {                
                    $data = array(  'username'  =>  $this->session->userdata('user'),
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