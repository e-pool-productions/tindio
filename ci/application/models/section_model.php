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

            $directors = $this->section_get_model->get_directors($project_id);
            
            //Generate UserListing vertical
            $data['directors'] ='';
            foreach ($directors as $director) 
                $data['directors'] .= '<a href="'.base_url().'users/view/'.$director['username'].'" data-target="#modal" data-toggle="modal">'.$director['firstname'].' '.$director['lastname'].' </a><br>';
            
            $data['scenesfinished'] = count($this->db_model->get('scene', "project_id = $project_id AND status_id = " . STATUS_FINISHED, 'title'));
            $data['shotcount'] = count($this->db_model->get('shot', "project_id = $project_id", 'title'));
            $data['shotsfinished'] = count($this->db_model->get('shot', "project_id = $project_id AND status_id = " . STATUS_FINISHED, 'title'));

            if($spez)
            {
                $data['deadline'] = $this->page_model->convertDateString($project['deadline']);
                
                $data['scenes'] = $this->db_model-> get('scene', "project_id = $project_id ORDER BY orderposition");
                $data['scenecount'] = count($data['scenes']);
            }
            else
            {
                $data['scenecount'] = count($this->db_model->get('scene', "project_id = $project_id", 'title'));
                
                if($project['category_id'] != NULL)
                {
                    $category = $this->db_model->get_single('category', array('category_id' => $project['category_id'])); 
                    $data['category'] = $category['title'];
                }
                else 
                    $data['category'] = 'No category set';
                    
                $data['project']['title'] = '<a href="'. base_url('projects/view/' . $project_id) .'">'.$project['title'].'</a>';
            }
            
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
            
            $data['scene']['title'] = '<a href="'.base_url('scenes/view/'.$scene_id).'">'.$scene['title'].'</a>';
            
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
            
            $data['shot']['title'] = '<a href="'.base_url('shots/view/'.$shot_id).'">'.$shot['title'].'</a>';
            
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
                $data['comments'] = $this->db_model->get('comment', 'task_id = '.$task['task_id']);
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
                                    '<a href="' . base_url('all_assets/showcase/' . urlencode(strtolower($taskfile['type_name'])) . '/' . $taskfile['path']) . '" data-target="#modal" data-toggle="modal" class="tooltip">'.$taskfile['title'].'</a>, ';
                        
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
            
                $data['logo'] = '<img src="'.MEDIA.$logopath.'" >';
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
                
            $crewcount = $this->section_get_model->get_usercount($section, $item[$section.'_id']);
            $data['crewtext'] = $this->section_get_model->get_crewtext($crewcount);

            if($spez)
            {
                $data['status']['title'] = $this->convertStatusID($item['status_id']);
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
                case STATUS_UNASSIGNED:     return array('color' => '#aaa', 'status' => '<i class="icon-unassigned"></i>Unassigned');
                case STATUS_PRE_PRODUCTION: return array('color' => '#c00', 'status' => '<i class="icon-pre-production"></i>Pre Production');
                case STATUS_IN_PROGRESS:    return array('color' => '#FC8402','status' => '<i class="icon-in-progress"></i>In Progress');
                case STATUS_FOR_APPROVAL:   return array('color' => '#b0b', 'status' => '<i class="icon-for-approval"></i>For Approval');
                case STATUS_FINISHED:       return array('color' => '#0c0', 'status' => '<i class="icon-finished"></i>Finished');
                default:                    return array('color' => '#000', 'status' => '');
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
            
            return;
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
            return;
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
            
            return;
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
            if(!is_null($section_id) && ($sect = $this->db_model->get_single("$section", "$section"."_id = $section_id", 'title')))
            {
                if($this->permission->hasPermission('delete', "$section", $section_id))
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
                        $this->db_model->update("$section", array('orderposition >=' => $duty['orderposition'], $parent.'_id' => $parent_id), array('orderposition', 'orderposition - 1'), true);
                                
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
            {
                $this->db_model->update("$section", "orderposition > $order AND $parent"."_id = $parent_id", array('orderposition', "orderposition + $amount"), true);
                $order++;
            }
            else
            {
                if($oldOrder > $order)
                {
                    $this->db_model->update("$section", "orderposition > $order AND orderposition <= $oldOrder AND $parent"."_id = $parent_id", array('orderposition', "orderposition + $amount"), true);
                    $order++;
                }
                else if($oldOrder < $order)
                    $this->db_model->update("$section", "orderposition <= $order AND orderposition >= $oldOrder AND $parent"."_id = $parent_id", array('orderposition', "orderposition - $amount"), true);
            }
            return $order;
        }
    }
?>
