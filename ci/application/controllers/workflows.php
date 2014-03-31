<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * controller for workflows
     */
	class Workflows extends MY_Controller 
	{
		/**
		 * loads all required models, libraries and helpers
		 */
		function __construct()
        {
            parent::__construct();
			$this->load->model(array('page_model', 'section_model', 'permission'));
			$this->load->library('form_validation');
			$this->load->helper(array('form'));
        }
		
		/**
		 * adds the selected workflow to the shot, before redirecting the user to the shot
		 */
		public function selectionform()
        {        	
			// Getting values from form
        	$workflow_id = $this -> input -> post('workflow');
			$shot_id = $this -> input -> post('id');
			$order = $this -> input -> post('order');
			$this -> add_to_shot($shot_id, $workflow_id, $order);
          	redirect('shots/view/'.$shot_id);
        }		
		
		/**
		 * presents all workflows to the user and allows him to edit or create workflows, if he has the required permissions
		 */
		public function index()
		{
			// function available to every user
			$data['new_workflow'] = ($this->permission->hasPermission('create', 'workflow'))? '<a href="'.base_url('workflows/create').'" data-target="#modal" data-toggle="modal" class="button small" id="workflow_button">Add new workflow</a>' : '';
			
			$data['workflows'] = $this -> db_model -> get('workflow');
			$this->load->library('table');
			// Task-Table
			$this->table->set_template($this->page_model->get_table_template('workflow_tasks', 50));
			$index=0;
			foreach ($data['workflows'] as $workflow_item) {
				$canEditDelete = $this -> canEditDelete($workflow_item['workflow_id']);
				if($canEditDelete)
					$data['workflows'][$index]['options']= '<a href="'.base_url('workflows/edit/'.$workflow_item['workflow_id']).'" data-target="#modal" data-toggle="modal" title="edit workflow" class="tooltip"><i class="icon-pencil"></i></a>'
					.'  <a href="'.base_url('workflows/add_task/'.$workflow_item['workflow_id']).'" title="add task to workflow" class="tooltip"><i class="icon-plus"></i></a>'
					.'  <a onclick="return confDelete();" href="'.base_url('workflows/delete/'.$workflow_item['workflow_id']).'" title="delete workflow" class="tooltip"><i class="icon-remove"></i></a>';
				else 
					$data['workflows'][$index]['options'] ='';
				$index++;
				$tasks = $this -> db_model -> get('workflowstructure', 'workflow_id = "'.$workflow_item['workflow_id'].'"');
				$this->table->set_heading(array(
            								'0' => array('data' => '', 'style' => 'width: 1px;'),
            								'1' => array('data' => 'Task', 'style' => 'width: 1px'),
            								'2' => array('data' => 'Description', 'style' => 'width: 1px'),
            								'3' => array('data' => 'Actions', 'style' => 'width: 1px; text-align: center'))
            							);
				foreach($tasks as $task_item)
				{
			      	$row = array(
							'0' => array('data' =>$task_item['orderposition']),
							'1' => array('data' =>$task_item['task_title'], 'style' => 'overflow: hidden'),
							'2' => array('data' =>$task_item['description'], 'style'=> 'overflow-x: auto; max-width: 50px'),
							'3' => array('data' =>$canEditDelete ? '<a href="'.base_url().'workflows/editTask/'.$task_item['workflow_id'].'/'.$task_item['orderposition'].'" data-target="#modal" data-toggle="modal" title="edit task" class="tooltip"><i class="icon-pencil"></i></a>
                             					   <a onclick="return confDeleteTask();" href="'.base_url().'workflows/delete/'.$workflow_item['workflow_id'].'/'.$task_item['orderposition'].'" title="delete task" class="tooltip"><i class="icon-remove"></i></a>': '', 'style'=> 'text-align: center')
							);          	
	                $this->table->add_row($row);
                }
				$data['tables'][$workflow_item['workflow_id']] = $this -> table-> generate();
				$this -> table -> clear();
			}
            $data['title'] = 'Workflow';
			$this->template->load('workflow/workflow_index', $data);
		}		
		
		/**
		 * initiates the create-workflow process
		 */
		public function create()
		{
			if(!$this->permission->hasPermission('create', 'workflow'))
				redirect('workflows');
			$this->load->view('workflow/workflow_create0');
		}
		
		/**
		 * first step of creating a workflow
		 */
		public function create0form()
		{
			$this->load->helper(array('form'));
			$num_of_tasks = $this->input->post('num_of_tasks');
			$data['num_of_tasks'] = $num_of_tasks;
			$data['name'] = $this->input->post('title');
			$this->load->view('workflow/workflow_create1', $data);
		}
		
		/**
		 * secound stop of creating a workflow
		 */
		public function create1form()
		{
			$name = $this->input->post('name_of_workflow');
			$num_of_taks = $this->input->post('num_of_tasks');
			$tasks = array();
			for ($i=0; $i < $num_of_taks; $i++) {
				$task_name = $this->input->post('title'.$i); 
				$task_description = $this->input->post('description'.$i);
				array_push($tasks,$task_name);
			}
			$session = $this -> session -> userdata('logged_in');
			$this->db_model->insert('workflow', array('title'=>$name, 'username' => $session['user']));	//Title has to be unique!
			$workflow_id = $this->db_model->get_single('workflow', array('title'=>$name));
			$workflow_id = $workflow_id['workflow_id'];
			
			for ($i=0; $i < $num_of_taks; $i++) {
				$task_name = $this->input->post('title'.$i); 
				$task_description = $this->input->post('description'.$i);
				$this->db_model->insert('workflowstructure', 
					array(	'workflow_id'=>$workflow_id,
							'orderposition'=>$i,
							'task_title'=> $task_name,
							'description'=> $task_description
				));									
				//TODO: change to single operation!
			}

			echo 'done';
		}

		/**
		 * adds a new Task to the given workflow
		 * @param Integer $workflow_id id of the workflow to add a task in
		 */
		public function add_task($workflow_id)
		{
			if(!$this->permission->hasPermission('addTask', 'workflow', $workflow_id))
				redirect("workflows");			
			$data['workflow_id'] = $workflow_id;
			$data['task_title'] = 'New Task';
			$data['orderposition'] = count($this->db_model->get('workflowstructure', array('workflow_id'=>$workflow_id)));
			$this->db_model->insert('workflowstructure', $data);
			redirect('workflows');
		}
		
		/**
		 * adds a given workflow to a shot
		 * 
		 * @param Integer $shot_id id of the shot to insert the workflow in
		 * @param Integer $workflow_id id of the workflow to add in the shot
		 * @param Integer $order position in the shot, where the workflow will be added
		 */
		private function add_to_shot($shot_id, $workflow_id, $order)
		{	
			$workflowtasks = $this -> db_model -> get('workflowstructure', "workflow_id = $workflow_id", 'task_title, orderposition');
			$num_of_tasks = count($workflowtasks);
            
            $order = $this->section_model->calc_orderposition('task', 'shot', $shot_id, $order, '', $amount = $num_of_tasks);

            $shot = $this -> db_model -> get_single('shot', array('shot_id' => $shot_id), 'project_id, deadline');
			foreach ($workflowtasks as $flow_item) 
			{
				$new_order = $order + $flow_item['orderposition'];
				$this -> db_model -> insert('task', array('shot_id' => $shot_id, 'title' => $flow_item['task_title'], 'orderposition' => $new_order, 'project_id' => $shot['project_id'], 'deadline' => $shot['deadline']));
			}
		}
		
		/**
		 * opens a modal to select a worflow to add to the shot
		 * 
		 * @param Integer $shot_id id of the shot to add a workflow to
		 */
		public function select($shot_id = null)
		{
			if($shot_id == null || !$this->permission->hasPermission('addWorkflow', 'shot', $shot_id))
				redirect("shots/view/".$shot_id);			
			
			$data['workflows'] = $this -> db_model -> get('workflow');
			$data['id'] = $shot_id;
			$data['tasks'] = $this->db_model->get('task', 'shot_id ="'.$shot_id.'" ORDER BY orderposition', 'title , task_id');
			$data['tasks'][] = array('title'=>'End of Shot', 'task_id'=> -1);
			$this->load->view('workflow/workflow_selectionview', $data);
		}

		/**
		 * deletes an entire workflow (if no orderposition is provided) or just a single task in the workflow (if orderposition is provided)
		 * 
		 * @param Integer $workflow_id id of the workflow to delete, or delete a task in
		 * @param Integer $orderposition if only deleting a single task this determines the task that will be deleted. 
		 * 					If not provided, the entire workflow will be deleted. Default false
		 */
		public function delete($workflow_id, $order = false)
		{
			$canDelete = $this -> canEditDelete($workflow_id);
			if(!$canDelete)
				redirect('workflows');
			else if ($order === false)
			{
				$this->db_model->destroy('workflowstructure', array('workflow_id'=> $workflow_id));
				$this->db_model->destroy('workflow', array('workflow_id'=> $workflow_id));

			}
			else 
			{
					$this->db_model->destroy('workflowstructure', array('workflow_id'=>$workflow_id, 'orderposition'=>$order));
                    $this->section_model->calc_orderposition('workflowstructure', 'workflow', $workflow_id, $order, '');
			}
			redirect('workflows');
		}
		
		/**
		 * determines if the current user can edit or delete a given workflow
		 * 
		 * @param Integer $workflow_id id of the workflow to check
		 * @return Boolean true, iff the current user is allowed to edit/delete the given workflow
		 */
		public function canEditDelete($workflow_id)
		{
			$session = $this -> session -> userdata('logged_in');
			$isAdminOrDirector = $this -> permission -> hasPermission('editWorkflow','workflow');
			if($isAdminOrDirector)
				return TRUE;
			else
			{
				$created_tmp = $this -> db_model -> get('workflow', array('workflow_id' => $workflow_id, 'username' => $session['user']));
				$created_workflow = !empty($created_tmp);
				return $created_workflow;
			}
		}
		
		/**
		 * loads a modal to edit a workflow
		 * 
		 * @param Integer $workflow_id id of the workflow to edit
		 */
		public function edit($workflow_id)
		{
			if(!$this->canEditDelete($workflow_id))
				redirect('workflows');	
			$workflow = $this->db_model->get_single('workflow', array('workflow_id' => $workflow_id));
			$data['tasks'] = $this -> db_model -> get('workflowstructure', array('workflow_id' => $workflow_id));
			$data['id'] = $workflow['workflow_id'];
			$data['oldTitle'] = $workflow['title'];
			$data['singleTask'] =FALSE;
			$this->load->view('workflow/workflow_editview', $data);
		}
		
		/**
		 * loads a modal to edit a task in a workflow
		 * 
		 * @param Integer $workflow_id id of the workflow to edit a task in
		 * @param Integer $orderposition position of the task in the workflow to edit
		 */
		public function editTask($workflow_id, $orderposition)
		{
			if(!$this->canEditDelete($workflow_id))
				redirect('workflows');			
			$workflow = $this->db_model->get_single('workflow', array('workflow_id' => $workflow_id));
			$data['tasks'] = $this -> db_model -> get('workflowstructure', array('workflow_id' => $workflow_id, 'orderposition' => $orderposition));
			$data['id'] = $workflow['workflow_id'];
			$data['oldTitle'] = $workflow['title'];
			$data['singleTask'] =TRUE;
			$data['orderposition']=$orderposition;
			$this->load->view('workflow/workflow_editview', $data);
		}
		
		/**
		 * transfers the changes made to a task in a workflow into the database
		 */
		public function editformtask()
		{
        	$this->form_validation->set_rules('title', 'Title', 'required|trim|xss_clean');
			if ($this->form_validation->run() == FALSE)
            {
                 $this->edit($this->input->post('id'));
            }
			$workflow_id = $this->input ->post('id');
			$i = $this -> input -> post('orderposition');
			$data = array( 'task_title' => $this -> input -> post('title'.$i),
								'description' => $this -> input -> post('description'.$i)
								);
			$this->db_model->update('workflowstructure', array('workflow_id'=>$workflow_id, 'orderposition' =>$i), $data);
			
			echo 'done';
		}
		
		/**
		 * transmits the changes made to a workflow into the database
		 */
		public function editform()
		{
			$workflow_id = $this->input ->post('id');
			$singleTask = $this -> input -> post('singleTask');
			if(!$singleTask)
			{	
				$data = array(  'title' => $this->input ->post('title'),
                             'workflow_id' => $workflow_id );
				$this->db_model->update('workflow', array('workflow_id'=>$data['workflow_id']), $data);
				unset($data);
				$num_of_tasks = $this->input->post('num_of_tasks');
				$start=0;
			}
			else 
			{
				$start=$this->input->post('orderposition');
				$num_of_tasks=$start+1;
			}
			for ($i=$start; $i < $num_of_tasks; $i++)
			{
				$data = array( 'task_title' => $this -> input -> post('title'.$i),
								'description' => $this -> input -> post('description'.$i)
								);
				$this->db_model->update('workflowstructure', array('workflow_id'=>$workflow_id, 'orderposition' =>$i), $data);
			}	
			echo 'done';
		}
	}
?>
