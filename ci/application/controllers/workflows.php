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
			$this->load->model(array('section_model', 'permission'));
			$this->load->library('form_validation');
			$this->load->helper('form');
        }		
		
		/**
		 * presents all workflows to the user and allows him to edit or create workflows, if he has the required permissions
		 */
		public function index()
		{
			$this->load->library('table');
					
			$data['workflows'] = $this -> db_model -> get('workflow');
			
			// Task-Table
			$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
			
			for($i = 0; $i < count($data['workflows']); $i++)
			{
				$workflow_id = $data['workflows'][$i]['workflow_id'];
				
				$canEdit = $this->permission->hasPermission('edit', 'workflow', $workflow_id);
				if($canEdit)
					$data['workflows'][$i]['options'] = '<a href="'.base_url('workflows/add_task/'.$workflow_id).'" class="btn btn-default btn-sm"><i class="fa fa-plus"></i></a>'.
														' <a onclick="return confDelete(\'workflow\');" href="'.base_url('workflows/delete/'.$workflow_id).'" class="btn btn-default btn-sm"><i class="fa fa-times"></i></a>';
				
				$this->table->set_heading(array(array('data' => ''),
            									array('data' => 'Task'),
            									array('data' => 'Description'),
            									array('data' => '')));
												
				$tasks = $this->db_model->get('workflowstructure', "workflow_id = $workflow_id");
				$editUrl = base_url('workflows/edit/'.$workflow_id);
												
				foreach($tasks as $task)
				{
			      	$row = array(	array('data' => $task['orderposition']),
									array('data' => '<div class="wordwrap">'.$task['task_title'].'</div>', 'class' => 'wordwrap', 'onclick' => $canEdit ? 'edit(this, "'.$editUrl.'/'.$task['orderposition'].'/title")' : ''),
									array('data' => '<div class="wordwrap">'.$task['description'].'</div>', 'class' => 'wordwrap', 'onclick' => $canEdit ? 'edit(this, "'.$editUrl.'/'.$task['orderposition'].'/description")' : ''),
									array('data' => '<a href="'.base_url('workflows/delete/'.$workflow_id.'/'.$task['orderposition']).'" onclick="return confDelete(\'workflowTask\');"><i class="fa fa-times"></i></a>')
								);          	
	                $this->table->add_row($row);
                }

				$data['tables'][$workflow_id] = $this->table->generate();
				$this->table->clear();
			}

			$data['canCreate'] = $this->permission->hasPermission('create', 'workflow');
			$data['count'] = count($data['workflows']);
            $data['title'] = 'Workflow';
			$this->template->load('workflow/workflow_overview', $data);
		}		
		
		/**
		 * initiates the create-workflow process
		 */
		public function create()
		{
			if(!$this->permission->hasPermission('create', 'workflow'))
				redirect('workflows');
			$this->load->view('workflow/workflow_creationview');
		}

		/**
		 * secound stop of creating a workflow
		 */
		public function form()
		{
			$this->form_validation->set_rules('title', 'Title', 'is_unique[workflow.title]|required|trim|xss_clean');
            
            if ($this->form_validation->run() == FALSE)
                $this->create();
            else
            {
				$title = $this->input->post('title');
				
				$this->db_model->insert('workflow', array('title' => $title, 'username' => $this->session->userdata('user')));
				
				$workflow = $this->db_model->get_single('workflow', array('title' => $title), 'workflow_id');
				
				for ($i=0; $i < $this->input->post('num_of_tasks'); $i++)
				{
					$data[] = array('workflow_id' => $workflow['workflow_id'],
									'orderposition' => $i,
									'task_title' => $this->input->post('title'.$i),
									'description' => $this->input->post('description'.$i));
				}
				
				$this->db_model->insert('workflowstructure', $data, true);
	
				echo 'done';
			}
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
		 * opens a modal to select a worflow to add to the shot
		 * 
		 * @param Integer $shot_id id of the shot to add a workflow to
		 */
		public function select($shot_id = null)
		{
			if(!$this->permission->hasPermission('addWorkflow', 'shot', $shot_id))
				redirect("shots/view/".$shot_id);			
			
			$data['workflows'] = $this->db_model->get('workflow');
			$data['id'] = $shot_id;
			$data['tasks'] = $this->db_model->get('task', 'shot_id ="'.$shot_id.'" ORDER BY orderposition', 'title , task_id');
			$data['tasks'][] = array('title'=>'End of Shot', 'task_id'=> -1);
			$this->load->view('workflow/workflow_selectionview', $data);
		}

		/**
		 * adds the selected workflow to the shot, before redirecting the user to the shot
		 */
		public function selectionform()
        {        	
			// Getting values from form
			$shot_id = $this -> input -> post('id');
			$order = $this -> input -> post('order') + 1;
			
			$workflowtasks = $this->db_model->get('workflowstructure', 'workflow_id = '.$this->input->post('workflow'), 'task_title, orderposition');
            
            $this->section_model->calc_orderposition('task', 'shot', $shot_id, $order, '', count($workflowtasks));

            $shot = $this->db_model->get_single('shot', "shot_id = $shot_id", 'project_id, deadline');
			foreach ($workflowtasks as $flow_item) 
			{
				$data[] = array('shot_id' => $shot_id,
								'title' => $flow_item['task_title'],
								'orderposition' => $order + $flow_item['orderposition'],
								'project_id' => $shot['project_id'],
								'deadline' => $shot['deadline']);
			}

			$this->db_model->insert('task', $data, true);
			
          	echo 'done';
        }
		
		/**
		 * deletes an entire workflow (if no orderposition is provided) or just a single task in the workflow (if orderposition is provided)
		 * 
		 * @param Integer $workflow_id id of the workflow to delete, or delete a task in
		 * @param Integer $orderposition if only deleting a single task this determines the task that will be deleted. 
		 * 					If not provided, the entire workflow will be deleted. Default false
		 */
		public function delete($workflow_id, $order = null)
		{
			if(!$this->permission->hasPermission('delete', 'workflow', $workflow_id))
				redirect('workflows');
			
			if (is_null($order))
			{
				$this->db_model->destroy('workflowstructure', "workflow_id = $workflow_id");
				$this->db_model->destroy('workflow', "workflow_id = $workflow_id");
			}
			else 
			{
				$this->db_model->destroy('workflowstructure', "workflow_id = $workflow_id AND orderposition = $order");
                $this->section_model->calc_orderposition('workflowstructure', 'workflow', $workflow_id, $order, '', -1);
			}
			redirect('workflows');
		}

		public function edit($workflow_id, $order, $field)
		{
			if(!$this->permission->hasPermission('edit', 'workflow', $workflow_id))
				echo 'Permission denied!';
				
			echo $this->section_model->edit('workflowtask', $workflow_id, $field, $order);
		}
		
		public function changeTitle($workflow_id, $field)
		{
			if(!$this->permission->hasPermission('edit', 'workflow', $workflow_id))
				echo 'Permission denied!';
				
			echo $this->section_model->edit('workflow', $workflow_id, $field);
		}
	}
?>
