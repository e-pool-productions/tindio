<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	* Controller for scenes
	*/
	class Scenes extends MY_Controller 
	{
		/**
		 * loads required models, libraries and helpers
		 */
		function __construct()
        {
            parent::__construct();
            $this->load->model(array('page_model', 'section_model', 'assets', 'check', 'permission'));
            $this->load->library(array('form_validation', 'table'));
			$this->load->helper('form');
        }
		
				/**
		 * redirects to the edit scene page
		 * 
		 * @version 1.0
		 * 
		 * @param Integer $scene_id id of the scene to edit, default = false
		 */
        public function view($scene_id = null)
        {
        	//function available to every user
            if(is_null($scene_id) || !$this->db_model->get('scene', "scene_id = $scene_id"))
                redirect('projects', 'refresh');
			
			$scene = $this->db_model->get_single('scene', "scene_id = $scene_id");
			$data = $this->section_model->gather_scene_details($scene, true);
			
			$data['permissions']['edit'] = $this->permission->hasPermission('edit', 'scene', $scene_id);
			$data['permissions']['create'] = $this->permission->hasPermission('create', 'shot', $scene_id);
			
			$hasOptions = $this->permission->hasPermission('delete', 'shot', $scene_id);
					
			//Shot-Table
			if(!empty($data['shots']))
			{
	          	$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
				
				$edit = $data['permissions']['edit'] ? EDIT_ICON : '';
	
	            $heading = array(	array('data' => $edit),
									array('data' => $edit),
									array('data' => 'Title '.$edit),
									array('data' => 'Code'),
									array('data' => 'Description '.$edit),
									array('data' => 'Status'),
									array('data' => 'Tasks'),
									array('data' => 'Details')
	            				);
										
				if($hasOptions)
					$heading[] = array('data' => '');
					
				$this->table->set_heading($heading);
	
	            foreach ($data['shots'] as $shot)
	            {
	            	$info = $this->section_model->gather_shot_details($shot);
	
					$editUrl = base_url('shots/edit/'.$shot['shot_id']);
					
					$row = array(	array('data' => '<img src="'.$info['logo']['path'].'" id="'.$info['logo']['id'].'" class="img-responsive img-thumbnail">', 'onclick' => 'edit(this, "'.$editUrl.'/logo")'),
									array('data' => $shot['orderposition'], 'onclick' => 'edit(this, "'.$editUrl.'/orderposition")'),
									array('data' => '<div class="wordwrap">'.$info['shot']['title'].'</div>', 'class' => 'wordwrap', 'onclick' => 'if(link) edit(this, "'.$editUrl.'/title")'),
									array('data' => $info['shortcode']),
									array('data' => '<div class="wordwrap">'.$shot['description'].'</div>', 'class' => 'wordwrap', 'onclick' => 'edit(this, "'.$editUrl.'/description")'),
									array('data' => $info['status']),
									array('data' => 'Total: 		'. $info['taskcount'].br(1).'
								  							Finished: 	'. $info['tasksfinished']),
									array('data' => 'Started:	'. $info['startdate'] .br(1).'
															Finished:	'. $info['enddate'] .br(1).'	
															Deadline: 	'. $info['deadline'].br(1).'
															Duration:	'. $info['duration'] .br(1).'
															Crew:		'. $info['crewtext'])
									);
									
					if($hasOptions)
						$row[] = array('data' => '<a onclick="return confDelete(\'shot\');" href="'.base_url('shots/delete/'.$shot['shot_id']).'"><i class="fa fa-times" title="delete"></i></a>');
	
					$this->table->add_row($row);
	            }
            }
		
            $data['shottable'] = !empty($data['shots']) ? $this->table->generate() : '';

			$data['usertable'] = $this->page_model->createUserTable('scene', $scene_id);
			$data['scenefiles'] = $this->page_model->createOutputFileTable('scene', $scene_id, 'Status', $this->assets->get_assets('scene', $scene_id));
			
			$data['scene'] = $scene;
			$data['button'] = $this->page_model->createButton('scene', $scene_id, $scene['status_id'], $data['shotsfinished'] == $data['shotcount']);
			$data['logos'] = $this->assets->get_all_projectassets('scene', $scene_id, 'type_id = '.IMAGE);
			$data['logos'][] = array('asset_id'=> '', 'title'=>'Default');
			$data['maxOrderposition'] = count($this->db_model->get('shot', array('scene_id' => $scene_id), 'shot_id'));

		    $this->template->load('pages/scene_infoview', $data);
        }

		/**
		 * create and edit form for scenes.
		 * 
		 * @param String $atWork 'create' or 'edit'
		 */
        public function form()
        {
            $project_id = $this -> input -> post('parent_id'); 
            
            $this->form_validation->set_rules('title', 'Title', 'callback_check->title[scene,'.$project_id.',create]|required|trim|xss_clean');
            $this->form_validation->set_rules('deadline', 'Deadline', 'required|trim|xss_clean');
            
            if ($this->form_validation->run() == FALSE)
                $this->create($project_id);
            else
            {
                $logo = $this->input->post('logo');
                if(empty($logo))
                    $logo = NULL;
				
                $order = $this->input->post('order') + 1;
				$this->section_model->calc_orderposition('scene', 'project', $project_id, $order, $this->input->post('oldOrder'));
                
				$maxPosOrder = count($this->db_model->get('scene', array('project_id'=>$project_id), 'scene_id')) + 1;

                if($order > $maxPosOrder)
					$order = $maxPosOrder;
                
                $data = array(  'title' => $this->input->post('title'),
                                'description' => $this->input->post('description'),
                                'deadline' => date('Y-m-d H:i:s', strtotime($this->input->post('deadline'))),
                                'logo' => $logo,
                                'orderposition' => $order,
                                'project_id' => $project_id);

			    $this->db_model->insert('scene', $data);
            
                $project = $this->db_model->get_single('project', array('project_id'=>$project_id), 'status_id');
                if($project['status_id'] == STATUS_FINISHED)
                    $this->db_model->update('project', array('project_id'=>$project_id), array('status_id' => STATUS_IN_PROGRESS));

                echo 'done';
            }
        }

		/**
		 * opens the create scene modal
		 * 
		 * @param Integer $project_id id of the project to create the scene in, default null
		 * 
		 * @version 1.0
		 */
		public function create($project_id = null)
		{
			if(is_null($project_id))
				redirect('error/page_missing');
			if(!$this->permission->hasPermission('create', 'scene', $project_id))
				redirect("mystuff/dashboard");
                
            $data['parent_id'] = $project_id;
            $data['sec_items'] = $this->db_model->get('scene', 'project_id ="'.$project_id.'" ORDER BY orderposition', 'title , scene_id');
            $data['sec_items'][] = array('title'=>'End of Project', 'scene_id' => -1);
            
            $data['logos'] = $this->assets->get_all_projectassets('project', $project_id, 'type_id = '.IMAGE);
            $data['logos'][] = array('asset_id'=> NULL, 'title'=>'Default');
            
            $data['section'] = 'scene';
            
            $this->load->view('pages/creationview', $data);
		}
		
		function edit($scene_id, $field)
        {
        	if(!$this->permission->hasPermission('edit', 'scene', $scene_id))
			{
				echo 'Permission denied!';
				return;
			}
				
			echo $this->section_model->edit('scene', $scene_id, $field);
        }
		
		/**
		 * sets the state of the scene specified in $id to InProgess
		 * 
		 * @version 1.0
		 * 
		 * @param Integer $id The scene_id of the scene to InProgess
		 */
		public function setInProgress($id = null)
		{
			if(!$this->permission->hasPermission('setInProgress', 'scene', $id))
				redirect("scenes/view/".$id);
			$this->section_model->setInProgress('scene', $id);
			redirect('scenes/view/'.$id);
		}
		
        /**
		 * sets the state of the scene specified in $id to ForApproval
		 * 
		 * @version 1.0
		 * 
		 * @param Integer $id The scene_id of the scene to set ForApproval
		 */
		public function setForApproval($id = null)
		{
			if(!$this->permission->hasPermission('setForApproval', 'scene', $id))
				redirect("scenes/view/".$id);
			$this->section_model->setForApproval('scene', $id);
			redirect('scenes/view/'.$id);
		}
		
		/**
		 * sets the state of the scene specified in $id to finished
		 * 
		 * @version 1.0
		 * 
		 * @param Integer $id The scene_id of the scene to finish
		 */
		public function finish($id = null)
		{
			if(!$this->permission->hasPermission('finish', 'scene', $id))
				redirect("scenes/view/".$id);
			$this->section_model->finish('scene', $id);
			redirect('scenes/view/'.$id);
		}
        
		/**
		 * deletes the given scene
		 * 
		 * @version 0.1
		 * 
		 * @param Integer $id id of the scene to delete, default = false
		 */
        public function delete($id = null)
        {
			if(!$this->permission->hasPermission('delete', 'scene', $id))
				redirect("scenes/view/".$id);
        	$parent_id = $this->section_model ->delete('scene', $id, 'project');
			
			if($parent_id == -1)
				redirect('projects');
			
			redirect('projects/view/'.$parent_id);
        }
		
 		/**
		 * sets the approved status of a given asset in a given Scene
		 * 
		 * @param Integer $scene_id id of the Scene to set the status of the asset in
		 * @param Integer $asset_id id of the asset to  set the status of
		 * @param Boolean $approved status to set the asset to
		 */       
        function approveFile($scene_id, $asset_id, $approved)
        {
			if(!$this->permission->hasPermission('approveFile', 'scene', $id))
				redirect("scenes/view/".$scene_id);
            $this->db_model->update('sceneasset', "asset_id = $asset_id", array('approved' => $approved));
            redirect('scenes/view/' . $scene_id);
        }
	}
?>
