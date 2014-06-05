<?php
    /**
     * model for all methods that abstract over the section and don't start with get
     */
    class Section_Model extends CI_Model
    {
         /**
         * collects details relevant for the project
         * 
         * @param Array $project project to gather details for
         * @param Boolean $spez true if all details are relevant (show a single project only) , default false
         * @return Array details of the project
         */
        function gather_project_details($project, $spez = false)
        {
            $project_id = $project['project_id'];
            
            $data = $this->getCommonData('project', $project, $spez);

            $directors = $this->get_directors($project_id);
            
            //Generate UserListing vertical
            $data['directors'] ='';
            foreach ($directors as $director) 
                $data['directors'] .= '<a href="'.base_url().'users/view/'.$director['username'].'" data-target="#modal" data-toggle="modal">'.$director['firstname'].' '.$director['lastname'].' </a><br>';
            
            $data['scenesfinished'] = count($this->db_model->get('scene', "project_id = $project_id AND status_id = " . STATUS_FINISHED, 'title'));
            $data['shotcount'] = count($this->db_model->get('shot', "project_id = $project_id", 'title'));
            $data['shotsfinished'] = count($this->db_model->get('shot', "project_id = $project_id AND status_id = " . STATUS_FINISHED, 'title'));
			
			if($project['category_id'] != NULL)
            {
                $category = $this->db_model->get_single('category', array('category_id' => $project['category_id'])); 
                $data['category']['title'] = $category['title'];
				$data['category']['id'] = $project['category_id'];
            }
            else
			{
				$data['category']['title'] = 'No category set';
				$data['category']['id'] = -1;
			}

            if($spez)
            {
                $data['deadline'] = $this->page_model->convertDateString($project['deadline']);
                
                $data['scenes'] = $this->db_model-> get('scene', "project_id = $project_id ORDER BY orderposition");
                $data['scenecount'] = count($data['scenes']);
            }
            else
                $data['scenecount'] = count($this->db_model->get('scene', "project_id = $project_id", 'title'));                 

            return $data;
        }

        /**
         * collects details relevant for the scene
         * 
         * @param Array $scene scene to gather details for
         * @param Boolean $spez true if all details are relevant (show a single scene only) , default false
         * @return Array details of the scene
         */
        function gather_scene_details($scene, $spez = false)
        {
            $scene_id = $scene['scene_id'];
            
            $data = $this->getCommonData('scene', $scene, $spez);

            $data['shotsfinished'] = count($this->db_model->get('shot', "scene_id = $scene_id AND status_id = " . STATUS_FINISHED, 'title'));
            
            if($spez)
            {
                $data['shots'] = $this->db_model->get('shot', "scene_id = $scene_id ORDER BY orderposition");
                $data['shotcount'] = count($data['shots']);
            }
            else
				$data['shotcount'] = count($this->db_model->get('shot', "scene_id = $scene_id"));
                
            
            return $data;
        }
        
         /**
         * collects details relevant for the shot
         * 
         * @param Array $shot shot to gather details for
         * @param Boolean $spez true if all details are relevant (show a single shot only) , default false
         * @return Array details of the shot
         */ 
        function gather_shot_details($shot, $spez = false)
        {
            $shot_id = $shot['shot_id'];
            
            $data = $this->getCommonData('shot', $shot, $spez);
            
            $data['tasksfinished'] = count($this->db_model->get('task', "shot_id = $shot_id AND status_id = " . STATUS_FINISHED, 'title'));

            if($spez)
            {
                $data['tasks'] = $this->db_model->get('task', "shot_id = $shot_id ORDER BY orderposition");
                $data['taskcount'] = count($data['tasks']);
            }
            else
                $data['taskcount'] = count($this->db_model->get('task', "shot_id = $shot_id"));

            return $data;
        }

        /**
         * collects details relevant for the task
         * 
         * @param Array $task task to gather details for
         * @param Boolean $spez true if all details are relevant (show a single task only) , default false
         * @return Array details of the task
         */
        function gather_task_details($task, $spez = false)
        {           
            $data = $this->getCommonData('task', $task, $spez);
            
            if($spez)
			{
                $data['comments'] = $this->db_model->get('comment', 'task_id = '.$task['task_id']);
				
				$taskusers = $this->get_users('task', $task['task_id']);
	            $data['artist_string'] = '';
	            foreach($taskusers as $user)
	            {
	                $data['artist_string'] .=   '<img src="'.$this->gravatar->get_gravatar($user['gravatar_email']).'?s=15">
	                                             <a href="'.base_url('users/profile/'.$user['username']).'">'.$user['firstname'].' '.$user['lastname'].'</a> ';
	            }
	            $data['artist_string'] .= '<br/>';
			}
            else
            {
                $taskfiles = $this->assets->get_assets('task', $task['task_id']);

                $data['approved_files'] = 'Approved: ';
                $data['for_approval_files'] = 'For Approval: ';
                
                foreach($taskfiles as $taskfile)
                {
                    if(!$taskfile['local'])
                    {
                        $str =  $taskfile['type_name'] == 'link' ?
                                    '<a href="http://' . $taskfile['path'] . '" target="_blank" class="tooltip"><i class="icon-eye-open" title="show"></i></a>, ' :
                                    '<a href="' . base_url('all_assets/showcase/' . urlencode(strtolower($taskfile['type_name'])) . '/' . $taskfile['path']) . '" data-target="#modal" data-toggle="modal">'.$taskfile['title'].'</a>, ';
                        
                        if($taskfile['approved'])
                            $data['approved_files'] .= $str;
                        else
                            $data['for_approval_files'] .= $str;
                    }  
                }
            }
            
            return $data;
        }

        /**
         * sets common data for Tasks/Shots/Scenes and Projects
         * 
         * @param String $section section of the $item
         * @param Array $item object to get the data from
         * @return Array Common data of the object
         */
        private function getCommonData($section, $item, $spez = false)
        {
            if($section != 'task')
            {
                if($item['logo'] == NULL)
                    $logopath = 'system/button-no.png';
                else
                {
                    $query = $this->db_model->get_single('asset', array('asset_id' => $item['logo']), 'path');          
                    $logopath = 'image/'.$query['path'];
                }
            
                $data['logo']['path'] = MEDIA.$logopath;
				$data['logo']['id'] = $item['logo'];
            }
            
            $data['status'] = $this->get_status($item['status_id']);
            
            $data['startdate']  = $this->page_model->convertDateString($item['startdate']);
            $data['enddate']   = $this->page_model->convertDateString($item['enddate']);
            $data['duration']  = $this->getIntervall($item['enddate'], $item['startdate']);
            
            if($section != 'project')
            {
                $data['deadline']  = $this->page_model->convertDateString($item['deadline']);
                $data['shortcode'] = $this->create_shortcode($section, $item[$section.'_id']);
            }
                
            $crewcount = $this->get_usercount($section, $item[$section.'_id']);
			
			if($crewcount == 0)
				$data['crewtext'] = 'Nobody';
			else if ($crewcount == 1)
				$data['crewtext'] = '1 person';
			else 
				$data['crewtext'] = $crewcount . ' people';

            if($spez)
            {
                switch($section)
                {
                    case 'task':
                        $data['shot'] = $this->db_model->get_single('shot', array('shot_id' => $item['shot_id']), 'title, shot_id, scene_id');
                        $item['scene_id'] = $data['shot']['scene_id'];
                    case 'shot':
                        $data['scene'] = $this->db_model->get_single('scene', array('scene_id' => $item['scene_id']), 'title, scene_id');
                    case 'scene':
                        $data['project'] = $this->db_model->get_single('project', array('project_id' => $item['project_id']), 'title, project_id');
                        break;
                }
            }
			else
				$data[$section]['title'] = '<a href="'.base_url($section.'s/view/'.$item[$section.'_id']).'" onmouseover="link=false;" onmouseout="link=true;">'.$item['title'].'</a>';
            
            return $data;
        }

        /**
         * takes a status ID and returns the status title associated with that id
         * 
         * @param Integer $id status_id to convert to status title
         */
        function convertStatusID($id)
        {
            $status = $this->db_model->get_single('status', array('status_id' => $id));
            return $status['title'];
        }
        
        /**
         * calculates the time past between $enddate and $startdate
         * 
         * @param String $enddate end of interval
         * @param Stirng $startdate start of interval
         * 
         * @return String 'not started' if $startdate is not set, else 'X Days' with X beeing amount of days between $startdata and $enddate
         */
        function getIntervall($enddate, $startdate)
        {
            if($startdate == '')
                return 'not started';
            $enddate_f = new DateTime($enddate);
            $startdate_f = new DateTime($startdate);
            $interval = $enddate_f->diff($startdate_f);
            return $interval->format('%a Days');
        }
        
        /**
         * translates a status_id into the appropriate name, color and appearance
         * 
         * @param Integer $status id of the status to translate
         */
        function get_status($status)
        {
        	switch ($status)
            {
                case STATUS_UNASSIGNED:     return '<span style="color:#aaa;"> <i class="fa fa-question-circle"></i> Unassigned</span>';
                case STATUS_PRE_PRODUCTION: return '<span style="color:#c00;"><i class="fa fa-spinner"></i> Pre Production</span>';
                case STATUS_IN_PROGRESS:    return '<span style="color:#FC8402;"><i class="fa fa-spinner"></i> In Progress</span>';
                case STATUS_FOR_APPROVAL:   return '<span style="color:#b0b;"><i class="fa fa-circle-o"></i> For Approval</span>';
                case STATUS_FINISHED:       return '<span style="color:#0c0;"><i class="fa fa-check-circle-o"></i> Finished</span>';
            }
        }
        
        /**
         * creates a shortcode for a task/shot/scene
         * 
         * @version 0.6
         * 
         * @param $id Integer id of the task/shot/scene
         * @param $type String task/shot/scene
         */
        function create_shortcode($section, $section_id)
        {
            switch($section)
            {
                case 'task':
                    $task = $this->db_model->get_single('task', array('task_id' => $section_id), 'shot_id, orderposition, title');
                    $section_id = $task['shot_id'];
                case 'shot':
                    $shot = $this->db_model->get_single('shot', array('shot_id'=> $section_id), 'scene_id, orderposition');
                    $section_id = $shot['scene_id'];
                case 'scene':
                    $scene = $this->db_model->get_single('scene', array('scene_id' => $section_id),'project_id, orderposition');
                    $project = $this->db_model->get_single('project', array('project_id' => $scene['project_id']), 'shortcode');
                    break;
            }

            return  $project['shortcode'].'_sc'.$scene['orderposition'].
                        (isset($shot) ? '_sh'.$shot['orderposition'].
                            (isset($task) ? '_t'.$task['orderposition'].'_'.strtolower(strtr($task['title'],' ', '_')) : '')
                            : '');
        }

        /**
         * sets the specified item to in_progress
         * 
         * @param String $section section of the item
         * @param Integer $section_id id of the item
         */
        function setInProgress($section, $section_id = null)
        {
            if(is_null($section_id) || !$this->db_model->get_single("$section", "$section"."_id = $section_id"))
                return;
            
            if(!$this->permission->hasPermission('setInProgress', "$section", $section_id))
                return;
            
            $data['status_id'] = STATUS_IN_PROGRESS;
            $data['enddate'] = NULL;
			
			$item = $this->db_model->get_single($section, array($section.'_id'=>$section_id));
			if($section != 'task')
			{
				if($section == 'project') $subSection = 'scene';
				if($section == 'scene') $subSection = 'shot';
				if($section == 'shot') $subSection = 'task';
				if($item['status_id'] == STATUS_PRE_PRODUCTION && !$this->db_model->get_single($subSection, array($section.'_id'=>$section_id)))
					return;
			}
			if($item['startdate'] == NULL)
			{
				$now = new DateTime("now");
				$now = $now->format('Y/m/d h:m:s');
				$data['startdate'] = $now;
			}
            switch($section)
            {
                case 'scene':
                    $project = $this->db_model->get_single('project p, scene sc', "sc.scene_id = $section_id AND sc.project_id = p.project_id", 'p.project_id, p.status_id');
                    if($project['status_id'] == STATUS_FINISHED)
                        $this->db_model->update('project', array('project_id'=>$project['project_id']), $data);
                    break;
                case 'shot':
                    $scene = $this->db_model->get_single('shot sh, scene sc', "sh.shot_id = $section_id AND sh.scene_id = sc.scene_id", 'sc.scene_id, sc.project_id, sc.status_id');
                    $project = $this->db_model->get_single('project', array('project_id' => $scene['project_id']), 'status_id');
                    if($scene['status_id'] == STATUS_FINISHED || $scene['status_id'] == STATUS_FOR_APPROVAL)
                        $this->db_model->update('scene', array('scene_id'=> $scene['scene_id']), $data);
                    if($project['status_id'] == STATUS_FINISHED)
                        $this->db_model->update('project', array('project_id'=> $scene['project_id']), $data);
                    break;
                case 'task':
                    $task = $this->db_model->get_single('task', array('task_id' => $section_id), 'project_id, shot_id, status_id');
                    if($task['status_id'] == STATUS_UNASSIGNED && !$this->db_model->get_single('usertask', array('task_id'=>$task['task_id'])))
					{
						return;
					}
                    if($task['status_id'] != STATUS_FOR_APPROVAL)
                    {
                        if($task['status_id'] == STATUS_PRE_PRODUCTION)
                        {
                            $now = new DateTime("");
                            $now = $now->format('Y-m-d H:i:s');
                            $data['startdate'] = $now;
                        }
                        
                        $shot = $this->db_model->get_single('shot', array('shot_id' => $task['shot_id']), 'scene_id, status_id');
                        $scene = $this->db_model->get_single('scene', array('scene_id' => $shot['scene_id']), 'status_id');
                        $project = $this->db_model->get_single('project', array('project_id' => $task['project_id']), 'status_id');
                    
                        // shot kann nur APP oder FIN sein, wenn ALLE tasks gefinished sind => Zeit wird nur bei PRE_PROD gesetzt
                        if($shot['status_id'] == STATUS_FINISHED || $shot['status_id'] == STATUS_FOR_APPROVAL || $shot['status_id'] == STATUS_PRE_PRODUCTION)
                            $this->db_model->update('shot', array('shot_id' => $task['shot_id']), $data);
                        if($scene['status_id'] == STATUS_FINISHED || $scene['status_id'] == STATUS_FOR_APPROVAL || $scene['status_id'] == STATUS_PRE_PRODUCTION)
                            $this->db_model->update('scene', array('scene_id' => $shot['scene_id']), $data);               
                        if($project['status_id'] == STATUS_FINISHED || $project['status_id'] == STATUS_PRE_PRODUCTION)
                            $this->db_model->update('project', array('project_id' => $task['project_id']), $data);
                    }
                    break;
            }
			
            $this->db_model->update("$section", "$section"."_id = $section_id", $data);
        }

        /**
         * sets the specified item to for_approval
         * 
         * @param String $section section of the item
         * @param Integer $section_id id of the item
         */
        function setForApproval($section, $section_id)
        {
            if(is_null($section_id) || !$this->db_model->get_single("$section", "$section"."_id = $section_id"))
                return;
            
            if(!$this->permission->hasPermission('setForApproval', "$section", $section_id))
                return;
			
			$item = $this->db_model->get_single($section, array($section.'_id'=>$section_id));
			if($item['status_id']!= STATUS_FINISHED && $item['status_id'] != STATUS_IN_PROGRESS)
				return;
                
            $this->db_model->update("$section", "$section"."_id = $section_id", array('status_id'=> STATUS_FOR_APPROVAL));
        }

        /**
         * sets the specified item to finished
         * 
         * @param String $section section of the item
         * @param Integer $section_id id of the item
         */
        function finish($section, $section_id)
        {
            if(is_null($section_id) || !$this->db_model->get_single("$section", "$section"."_id = $section_id"))
                return;
            
            if(!$this->permission->hasPermission('finish', "$section", $section_id))
                return;

            $now = new DateTime("");
            $nowstring = $now->format('Y-m-d H:i:s');
            $this->db_model->update("$section", "$section"."_id = $section_id", array('status_id' => STATUS_FINISHED, 'enddate' => $nowstring));
        }
		
        function edit($section, $section_id, $field, $workflowTaskOrder = null)
        {
        	$newValue = $this->input->post('newValue');
			
			if($newValue === false)
				return 'No Value specified!';
			
            $this->load->library('form_validation');
            $valNeeded = false;

            switch($field)
            {
                case 'title' :      
                    $this->form_validation->set_rules('newValue', 'Title', 'required|trim|xss_clean|callback_check->title['.$section.','.$section_id.',edit]');
                    $valNeeded = true; break;
                case 'deadline':
                    $this->form_validation->set_rules('newValue', 'Deadline', 'required|trim|xss_clean');
                    $valNeeded = true; break;
				// only for Projects
				case 'shortcode':
                    $this->form_validation->set_rules('newValue', 'Shortcode', 'callback_check->shortcode['.$section_id.']|trim|xss_clean');
                    $valNeeded = true; break;
            }
            
            if ($this->form_validation->run() == FALSE && $valNeeded)
                return form_error('newValue', ' ', ' ');
            else
            {
            	if($field == 'orderposition')
				{
					switch($section)
					{
						case 'scene':	$parent = 'project'; break;
						case 'shot':	$parent = 'scene'; break;
						case 'task':	$parent = 'shot'; break;
					}
					
					$item = $this->db_model->get_single($section, $section."_id = $section_id", $parent.'_id, orderposition');
					$this->section_model->calc_orderposition($section, $parent, $item[$parent.'_id'], $newValue, $item['orderposition']);
				}

                $val = empty($newValue) && $field == 'logo' ? NULL : $newValue;
				if($section == 'workflowtask')
					$this->db_model->update('workflowstructure', "workflow_id = $section_id AND orderposition = $workflowTaskOrder", array($field == 'title' ? 'task_title' : $field  => $val));
				else
                	$this->db_model->update($section, $section."_id = $section_id", array($field => $val));
                return 'done';
            }
        }
        
        /**
         * deletes the specified item
         * 
         * @param String $section section of the item
         * @param Integer $section_id id of the item
		 * @param String $parent section above $section
         */ 
        function delete($section, $section_id, $parent)
        {
            if(!is_null($section_id) && ($sect = $this->db_model->get_single($section, $section."_id = $section_id", 'title')))
            {
                if($this->permission->hasPermission('delete', $section, $section_id))
                {
                    $this->load->model('assets');
                    switch($section)
                    {
                        case 'project':
                            $this->db_model->destroy('projectobserver', "project_id = $section_id");
                            
                            $project = $this->db_model->get_single('project', array('project_id'=>$section_id), 'title');
                            $this->db_model->update('globallog', array('logtype_id'=>LOGTYPE_NEW_PROJECT, 'link'=>$section_id), array('link'=>$project['title']));
                            $this->db_model->insert('globallog', array('logtype_id'=>LOGTYPE_DELETE_PROJECT, 'link'=>$project['title']));
                            
                            $data['scene'] = array_map(function($el){ return $el['scene_id']; }, $this->db_model->get('scene', "project_id = $section_id", 'scene_id'));
                        case 'scene':
                            $data['shot'] = array_map(function($el){ return $el['shot_id']; }, $this->db_model->get('shot', $section."_id = $section_id", 'shot_id'));
                        case 'shot':
                            if($section == 'scene')
                                $data['task'] = array_map(function($el){ return $el['task_id']; }, $this->db_model->get('task t, shot sh', "t.shot_id = sh.shot_id AND sh.scene_id = $section_id", 'task_id'));
                            else
                                $data['task'] = array_map(function($el){ return $el['task_id']; }, $this->db_model->get('task', $section."_id = $section_id", 'task_id'));
                            break;
                    }
                    $data = array_reverse($data);
                    $data[$section] = array($section_id);

                    if($section != 'project')
                    {       
                        $duty = $this->db_model->get_single($section, array($section.'_id' => $section_id));
                        $parent_id = $duty[$parent.'_id'];
						
						$this->calc_orderposition($section, $parent, $parent_id, $duty['orderposition'], '', -1);
                                
                        $finishedduties = $this->db_model->get($section, array($parent.'_id' => $parent_id, 'status_id' => STATUS_FINISHED));
                        $allduties = $this->db_model->get($section, array($parent.'_id' => $parent_id));
                        
                        if(count($allduties) > 0 && count($finishedduties) > 0)
                        {
                            if(count($finishedduties) == count($allduties) || count($allduties) - count($finishedduties) == 1 && $duty['status_id'] != STATUS_FINISHED)
                                $this->db_model->update($parent, array($parent.'_id' => $parent_id), array('status_id' => STATUS_FOR_APPROVAL));
                            elseif(count($finishedduties) == 1 && $duty['status_id'] == STATUS_FINISHED)
                                $this->db_model->update($parent, array($parent.'_id' => $parent_id), array('status_id' => STATUS_IN_PROGRESS));
                        }
                    }

                    foreach($data as $subSec => $ids)
                    {
                        if(empty($ids))
                            continue;
                        
                        $this->db_model->destroy('user'.$subSec, $subSec.'_id IN ('.implode(',', $ids).')');
                        
                        if($subSec == 'task')
                            $this -> db_model -> destroy('comment', 'task_id IN ('.implode(',', $ids).')');
                        
                        $assets = $this->db_model->get( $subSec.'asset sa, asset a',
                                                        $subSec.'_id IN ('.implode(',', $ids).') AND sa.asset_id = a.asset_id AND global = 0',
                                                        'sa.asset_id');
                        
                        $this->db_model->destroy($subSec.'asset', $subSec.'_id IN ('.implode(',', $ids).')');
                        
                        foreach($assets as $asset)
                            if(!($this->assets->is_Linked($asset['asset_id'])))
                                $this->db_model->destroy('asset', array('asset_id'=> $asset['asset_id']));
                        
                        $this->db_model->destroy($subSec, $subSec.'_id IN ('.implode(',', $ids).')');
                    }
                    
                    return $parent_id;
                }
            }
            return -1;
        }

        /**
         * calculates the orderposition for the specified item and updates the orderpositions of affected items
         * 
         * @param String $section section of the item to calculate the orderposition for
         * @param String $parent section above the item
         * @param Integer $parent_id id of the parent item
         * @param Integer $order orderposition of the item
         * @param Integer $oldOrder old orderposition of the item
         * @param Integer $amount amount of items to calculate the orderposition for
         * @return Integer calculated orderposition
         */
        function calc_orderposition($section, $parent, $parent_id, $order, $oldOrder, $amount = 1)
        {
            if(empty($oldOrder))
                $this->db_model->update("$section", "orderposition >= $order AND $parent"."_id = $parent_id", array('orderposition', "orderposition + $amount"), true);
            else
            {
                if($oldOrder > $order)
                    $this->db_model->update("$section", "orderposition >= $order AND orderposition <= $oldOrder AND $parent"."_id = $parent_id", array('orderposition', "orderposition + $amount"), true);
                else if($oldOrder < $order)
                    $this->db_model->update("$section", "orderposition <= $order AND orderposition >= $oldOrder AND $parent"."_id = $parent_id", array('orderposition', "orderposition - $amount"), true);
            }
        }
		
		function get_userprojectrole($username)
        {
            $projects = $this -> db_model -> get('project',null,'project_id, title');
            $userprojectrole = array();

            foreach($projects as $project)
            {
                $users = $this->get_users('project', $project['project_id']);
                foreach($users as $user)
                {
                    if($username == $user['username'])
                    {
                        $userprojectrole[] = array( 'title' => $project['title'],
                                                    'project_id' => $project['project_id'],
                                                    'role_title' => $user['role_title']);
                        break;
                    }
                }
            }
            return $userprojectrole;
        }	
		/**
		 * checks the given work items and returns true if all of them are finished
		 * returns false if there are no work items given
		 * 
		 * @param mixed $duties work items (tasks/shots/scenes)
		 * @return Boolean true if all work items are finished
		 */
		function all_finished($duties)
		{
			if(empty($duties))
				return FALSE;

			for ($i = 0; $i < count($duties); $i++) 
				if($duties[$i]['status_id'] != STATUS_FINISHED)
					return FALSE;
			return TRUE;
		}	
		
		function get_usercount($section, $section_id)
		{
			$num_of_people = 0;
			switch($section)
			{
				case 'project':	$num_of_people += count($this->get_directors($section_id));
				case 'scene':	$num_of_people += count($this->get_scene_supervisors($section, $section_id));
				case 'shot':	$num_of_people += count($this->get_shot_supervisors($section, $section_id));
				case 'task':	$num_of_people += count($this->get_artists($section, $section_id)); break;
			}
			return $num_of_people;
		}
		
		function get_users($section, $section_id)
		{
			$data = array();
			$persons = array();
			switch($section)
			{
				case 'project':	$persons[] = array($this->get_project_observers($section_id), 'Obs');
								$persons[] = array($this->get_directors($section_id), 'Dir');
				case 'scene':	$persons[] = array($this->get_scene_supervisors($section, $section_id), 'Sup');
				case 'shot': 	$persons[] = array($this->get_shot_supervisors($section, $section_id), 'Sup');
				case 'task': 	$persons[] = array($this->get_artists($section, $section_id), 'Art'); break;
			}

			foreach($persons as $person)
			{
				foreach($person[0] as $kindOf)
				{
					$data[] = array('username'=>$kindOf['username'],
									'firstname'=>$kindOf['firstname'],
									'lastname'=>$kindOf['lastname'],
			             			'lastaccess'=>$this->page_model->timesince($kindOf['lastaccess']),
			             			'gravatar_email'=>$kindOf['gravatar_email'],
                         			'role_title' => $person[1]);
				}
			}
			return $data;
		}
		
		function get_project_observers($project_id)
		{
		    $query =    "u.username = po.username AND po.project_id = $project_id";
            $section = 'observer';
            switch($section)
            {
                case 'observer':$query .= " AND u.username NOT IN (SELECT username FROM usertask ut, task t WHERE ut.task_id = t.task_id AND po.project_id = t.project_id)";
                case 'project': $query .= " AND u.username NOT IN (SELECT username FROM userproject WHERE project_id = $project_id)";
                case 'scene':   $query .= " AND u.username NOT IN (SELECT username FROM userscene usc, scene sc WHERE usc.scene_id = sc.scene_id AND sc.project_id = po.project_id)";
                case 'shot':    $query .= " AND u.username NOT IN (SELECT username FROM usershot ush, shot sh WHERE ush.shot_id = sh.shot_id AND sh.project_id = po.project_id)"; break;
            }

            return $this->db_model->get_distinct("user u, projectobserver po", $query, 'u.*', true);
		}

		function get_artists($section, $section_id)
		{
			$query =	"u.username = ut.username AND ut.task_id = t.task_id AND ". 
						($section == 'scene'? "t.shot_id = sh.shot_id AND sh.scene_id = $section_id"
											: "t.$section"."_id = $section_id");
											
			switch($section)
			{
				case 'project': $query .= " AND u.username NOT IN (SELECT username FROM userproject WHERE project_id = $section_id)";
				case 'scene':	$query .= " AND u.username NOT IN (SELECT username FROM userscene usc, shot sh WHERE usc.scene_id = sh.scene_id AND sh.shot_id = t.shot_id)";
				case 'shot':	$query .= " AND u.username NOT IN (SELECT username FROM usershot ush WHERE ush.shot_id = t.shot_id)"; break;
			}								

			return $this->db_model->get_distinct("user u, usertask ut, task t" . ($section == 'scene' ? ', shot sh' : ''), $query, 'u.*', true);
		}

		function get_shot_supervisors($section, $section_id)
		{
			$query = "u.username = us.username AND us.shot_id = sh.shot_id AND sh.$section"."_id = $section_id";
											
			switch($section)
			{
				case 'project': $query .= " AND u.username NOT IN (SELECT username FROM userproject WHERE project_id = $section_id)";
				case 'scene':	$query .= " AND u.username NOT IN (SELECT username FROM userscene usc WHERE usc.scene_id = sh.scene_id)"; break;
			}
			
			return $this->db_model->get_distinct("user u, usershot us, shot sh", $query, 'u.*', true);
		}
		
		function get_scene_supervisors($section, $section_id)
		{
			$query = "u.username = us.username AND us.scene_id = sc.scene_id AND sc.$section"."_id = $section_id";
			
			if($section == 'project')
				$query .= " AND u.username NOT IN (SELECT username FROM userproject WHERE project_id = $section_id)";
			
			return $this->db_model->get_distinct('user u, userscene us, scene sc', $query, 'u.*', true);
		}
		
		function get_directors($project_id)
		{
			return $this->db_model->get_distinct('user u, userproject up', "u.username = up.username AND up.project_id = $project_id", 'u.*', true);
		}
    }
?>