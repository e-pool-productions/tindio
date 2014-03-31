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
            $this->load->model(array('page_model', 'section_model', 'section_get_model', 'assets', 'check', 'permission'));
            $this->load->library(array('form_validation', 'table'));
			$this->load->helper('form');
        }

		/**
		 * create and edit form for scenes.
		 * 
		 * @param String $atWork 'create' or 'edit'
		 */
        public function form($atWork) {
			
			$scene_id = $this -> input -> post('scene_id');
            $project_id = $this -> input -> post('project_id'); 
            
            if($atWork == 'create')
                $this->form_validation->set_rules('title', 'Title', 'callback_check->title[scene,'.$project_id.',create]|required|trim|xss_clean');
            else
                $this->form_validation->set_rules('title', 'Title', 'callback_check->title[scene,'.$scene_id.',edit]|required|trim|xss_clean');
            $this->form_validation->set_rules('deadline', 'Deadline', 'required|trim|xss_clean');
            
            if ($this->form_validation->run() == FALSE)
            {
                if($atWork == 'create')
                    $this->create($project_id);
                else
                    $this->edit($scene_id);
            }
            else
            {
            	$oldOrder = $this->input->post('oldOrder');	
            	if($atWork == 'edit')
				{
					$currentOrder = $this->db_model->get_single('scene', array('scene_id'=>$scene_id), 'orderposition');
					if(!$this->db_model->get_single('scene', array('scene_id'=>$scene_id), 'title') || $oldOrder != $currentOrder['orderposition'])
					{
						echo 'done';
						return;
					}
				}
               $logo = $this->input->post('logo');
                if(empty($logo))
                    $logo = NULL;
				
				$order = $this->section_model->calc_orderposition('scene', 'project', $project_id, $this->input->post('order'), $oldOrder);
                
				$maxPosOrder = count($this->db_model->get('scene', array('project_id'=>$project_id), 'scene_id'));
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
                    $data['project_id'] = $project_id; 
				    $this->db_model->insert('scene', $data);
                
                    $project = $this->db_model->get_single('project', array('project_id'=>$project_id), 'status_id');
                    if($project['status_id'] == STATUS_FINISHED)
                        $this->db_model->update('project', array('project_id'=>$project_id), array('status_id' => STATUS_IN_PROGRESS));
                }
                else
                    $this->db_model->update('scene', "scene_id = $scene_id", $data);

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
			
			$data['scene_id'] = '';
			$data['project_id'] = $project_id;
			
			$data['scenes'] = $this->db_model->get('scene', 'project_id ="'.$project_id.'" ORDER BY orderposition', 'title , scene_id');
			$data['scenes'][] = array('title'=>'End of Project', 'scene_id' => -1);
			
            $type_id = $this->assets->convert_name_id('Image');
            $data['logos'] = $this->assets->get_all_projectassets('project', $project_id, "type_id = $type_id");
            $data['logos'][] = array('asset_id'=> NULL, 'title'=>'Default');
			
			$data['new'] = TRUE;
			$data['oldLogo'] = '';
			$data['oldTitle'] = set_value('title');
			$data['oldDescription'] = '';
			$data['oldDeadline'] = set_value('deadline');
			$data['oldOrder'] = '';
			
			$this->load->view('scenes/scene_creationview', $data);
		}
        
		/**
		 * opens the edit scene modal
		 * 
		 * @version 1.0
		 * 
		 * @param Integer $scene_id id of the scene to edit, default = false
		 */
        public function edit($scene_id = null)
        {
			if(is_null($scene_id))
				redirect('error/page_missing');
			if(!$this->permission->hasPermission('edit', 'scene', $scene_id))
				redirect("scenes/view/".$scene_id);			
            
            $scene = $this->db_model->get_single('scene', array('scene_id' => $scene_id));
			
			$data['scene_id'] = $scene_id;
			$data['project_id'] = $scene['project_id'];
			
			$data['scenes'] = $this->db_model->get('scene', 'project_id ="'.$data['project_id'].'" ORDER BY orderposition', 'title , scene_id');
			$data['scenes'][] = array('title'=>'End of Project', 'scene_id'=>-1);
            
            $type_id = $this->assets->convert_name_id('Image');
            $data['logos'] = $this->assets->get_all_projectassets('scene', $scene_id, "type_id = $type_id");
            $data['logos'][] = array('asset_id'=> NULL, 'title'=>'Default');
			
			$data['new'] = FALSE;
			$data['oldLogo'] = $scene['logo'];
			$data['oldTitle'] = $scene['title'];
            $data['oldDeadline'] = $scene['deadline'];
            $data['oldOrder'] = $scene['orderposition'];
			$data['oldDescription'] = $scene['description'];
			
            $this->load->view('scenes/scene_creationview', $data);
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
			
			$isAdmin = $this->permission->isAdmin();
			$isDirector = $this->permission->isDirector('project', $scene['project_id']);
			$isScSup = $this->permission->isScSup($scene_id);
			
			$data = $this->section_model->gather_scene_details($scene, true);
			
			$data['button'] = '';
			
			$control = $isAdmin || $isDirector;
			$allFinished = $data['shotsfinished'] == $data['shotcount'];
	
			if($scene['status_id'] == STATUS_IN_PROGRESS && ($isScSup || $control) && $allFinished)
				$data['button'] = '<a href="'.base_url('scenes/setForApproval/'.$scene_id) .'" class="button small"><i class="icon-for-approval"></i> Set for Approval</a>';
			elseif(($scene['status_id'] == STATUS_FOR_APPROVAL || $scene['status_id'] == STATUS_FINISHED) && $control)
			{
           		$data['button'] = '<a href="'.base_url('scenes/setInProgress/'.$scene_id).'" class="button small"><i class="icon-in-progress"></i> Set to In Progress</a>';
				if($scene['status_id'] == STATUS_FOR_APPROVAL && $allFinished)
					$data['button'] .= '<a href="'.base_url('scenes/finish/'.$scene_id).'" class="button small"><i class="icon-finished"></i> Finish Scene</a>';
			}
					
			//Shot-Table
          	$this->table->set_template($this->page_model->get_table_template('scene_shots'));
            
            $this->table->set_heading(array('0' => array('data' => '', 'style' => 'width: 1px'),
            								'1' => array('data' => '', 'style' => 'width: 1px'),
            								'2' => array('data' => 'Shot', 'style' => 'width: 100px; min-width:50px'),
            								'3' => array('data' => 'Code', 'style' => 'width: 120px'),
            								'4' => array('data' => 'Description', 'style' => 'max-width: 50px'),
            								'5' => array('data' => 'Status', 'style' => 'width: 120px'),
            								'6' => array('data' => 'Tasks', 'style' => 'width: 65px'),
            								'7' => array('data' => 'Details', 'style' => 'width: 1px'),
            								'8' => array('data' => 'Actions', 'style' => 'width: 1px')
            						));

            foreach ($data['shots'] as $shot)
            {
            	$info = $this->section_model->gather_shot_details($shot);
				$actions = '';
				if($this->permission->hasPermission('edit', 'shot', $shot['shot_id']))
					$actions = '<a href="'.base_url('shots/edit/'.$shot['shot_id']).'" data-target="#modal" data-toggle="modal" class="tooltip"><i class="icon-pencil" title="edit"></i></a> ';
				if($this->permission->hasPermission('delete', 'shot', $shot['shot_id']))                    
                    $actions .='<a onclick="return confDelete();" href="'.base_url('shots/delete/'.$shot['shot_id']).'" title="" class="tooltip"><i class="icon-remove" title="delete"></i></a>';
			
				$row = array(	'0' => array('data' => $shot['orderposition']),
								'1' => array('data' => $info['logo']),
								'2' => array('data' => '<div style=\'overflow-x:auto\'>'.$info['shot']['title'].'</div>', 'style'=> 'text-align:left; max-width:100px'),
								'3' => array('data' => $info['shortcode']),
								'4' => array('data' => '<div style=\'overflow-x:auto\'>'.$shot['description'].'</div>', 'style'=> 'max-width: 110px; text-align:left'),
								'5' => array('data' => $info['status']['status'], 'style'=>'color:'.$info['status']['color'].'; min-width: 100px;'),
								'6' => array('data' => 'Total: '.br(1).$info['taskcount'].br(1).'
							  							Finished: '.br(1).$info['tasksfinished']),
								'7' => array('data' => 'Started:	'. $info['startdate'] .br(1).'
														Finished:	'. $info['enddate'] .br(1).'	
														Deadline: 	'. $info['deadline'].br(1).'
														Duration:	'. br(1).$info['duration'] .br(1).'
														Crew:		'. br(1).$info['crewtext']),
								'8' => array('data' => $actions, 'style'=>'text-align:center'));
				$this->table->add_row($row);
            }
		
            $data['shottable'] = $this->table->generate();
			
			$this->table->clear();
			unset($rows);
			
			// UserTable
			$this->table->set_template($this->page_model->get_table_template('scene_users'));
			 
			$this->table->set_heading(array('0' => array('data' => 'Name'),
            								'1' => array('data' => 'Role'),
            								'2' => array('data' => 'Last access'),
            								'3' => array('data' => 'Actions'),
            						));								
			$sceneusers = $this -> section_get_model -> get_users('scene', $scene_id);
			
			foreach($sceneusers as $user)
			{
				$username = $user['username'];
				$unassign = $isAdmin || $isDirector || $isScSup ?
								'<a onclick="return confUnassign(\''.$user['firstname'].'\', \''.$user['lastname'].'\');" href="'.base_url('users/unassign/'.$username.'/scene/'.$scene_id).'" class="tooltip"><i class="icon-minus-sign" title="unassign user"></i></a>' :
								'';
				
				$row = array(	'0' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.base_url('users/view/'.$username).'" data-target="#modal" data-toggle="modal">'.$user['firstname'].' '.$user['lastname'].'</a></div>', 'style' => 'max-width: 100px'),
								'1' => array('data' => $user['role_title']),
								'2' => array('data' => $user['lastaccess']),
								'3' => array('data' => $unassign, "style" => "text-align:center")
							);
				$this->table->add_row($row);
			}		
			$data['usertable'] = $this->table->generate();
			$this->table->clear();
            
			//scene Files
			$data['scenefiles'] = $this->page_model->createOutputFileTable('scene', $scene_id, 'Status', $this->assets->get_assets('scene', $scene_id));
			
			if($isAdmin || $isDirector || $isScSup)
			{
				$data['addNewShot'] = '<a href="'.base_url('/shots/create/'.$scene_id).'" data-target="#modal" data-toggle="modal" class="button small">Add new Shot</a>';
				$data['addSceneSup']= '<a href="'.base_url('/users/show/scene/'.$scene_id).'"; class="button small"><i class="icon-user"></i> Add Scene Sup</a>';
				$data['addNewFile'] = '<a href="'.base_url('/upload/choose_files/scene_'.$scene_id).'" data-target="#modal" data-toggle="modal" class="button small"><i class="icon-upload-alt"></i></a>';
				$data['linkNewFile']= '<a href="'.base_url('/all_assets/link_asset/scene_'.$scene_id).'" class="button small"><i class="icon-link"></i></a>';
			}

			$data['scene'] = $scene;
            $data['title'] = $scene['title'];
			$data['permissions']['edit'] = $this->permission->hasPermission('edit', 'scene', $scene_id);
		    $this->template->load('scenes/scene_infoview', $data);
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
