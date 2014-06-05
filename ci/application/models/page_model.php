<?php
	/**
	 * model for the dashoard
	 */
    class Page_Model extends CI_Model
    {
    	/**
		 * Formats a date to a String (Year/Month/Day)
		 * 
		 * @param date $date date to convert
		 */
        public function convertDateString($date)
        {
            if($date == '')
                return '';
            $date_f = new DateTime($date);
            $date_s = $date_f ->format('Y/m/d');
            return $date_s;
        }		
    	/**
		 * returns the time between now and the time specified in the parameter
		 * 
		 * @version 1.0
		 * 
		 * @param Date $lastaccess date to compare, NULL if not set
		 * @return String Time between parameter and now
		 */
        public function timesince($lastaccess)
        {
            if($lastaccess == 'not seen yet')
                return 'not seen yet';
            $now = new DateTime("now");
            $last = new DateTime($lastaccess);
            
            $interval = $now->diff($last);

            if($interval->days > 0)
                return ($interval->invert == 1) ? $interval->format('%d days ago') : $interval->format('in %d days');
            elseif($interval->h > 1)
                return ($interval->invert == 1) ? $interval->format('%h hours ago') : $interval->format('in %h hours');
			elseif($lastaccess != NULL && $interval->i == 0)
				return 'recently';
			elseif($interval->i != 0)
                return ($interval->invert == 1) ? $interval->format('%i min ago') : $interval->format('in %i min');
			else 
				return 'not seen yet';	
			
		}
		
		/**
		 * returns the users deadlines
		 * 
		 * @version -5.0
		 * 
		 * @param String $user user name of the user to get deadlines for
		 * @return deadlines, ready to display
		 */
		public function get_deadlines($user)
		{
			$this->load->model('section_model');
			
			$tasks = $this->db_model->get('task t, usertask ut', "ut.username = '$user' AND ut.task_id = t.task_id AND NOT t.status_id = ".STATUS_FINISHED);
			$now = new DateTime("now");
			
			$result = array();
			foreach($tasks as $task)
			{
				if(new DateTime($task['deadline']) < $now)
					continue;	//deadline in past
					
				$result[] = array(	'deadline' => $this->convertDateString($task['deadline']),
									'time_left' => $this->timesince($task['deadline']),
									'task_id' => $task['task_id'],
									'task_title' => '<a href="'.base_url('tasks/view/'.$task['task_id']).'">'.$task['title'].'</a>',
									'code' => $this->section_model->create_shortcode('task', $task['task_id']));
			}
			return $result;
		}
		
		/**
		 * returns global statistics
		 * 
		 * @version -5.0
		 * 
		 * @return global statistics
		 */
		public function get_globalstats()
		{
		    $lfp = $this->db_model->get_single('project', 'enddate IS NOT null ORDER BY enddate DESC', 'title, project_id');
            $mrp = $this->db_model->get_single('project', 'creationtime IS NOT null ORDER BY creationtime DESC', 'title, project_id');

			return array('totalprojects' => count($this->db_model->get('project', null, 'title')),
			             'totalscenes'   => count($this->db_model->get('scene', null, 'title')),
						 'totalshots'    => count($this->db_model->get('shot', null, 'title')),
						 'totaltasks'    => count($this->db_model->get('task', null, 'title')),
						 'totalusers'    => count($this->db_model->get('user', null, 'username')),
						 'lastfinishedproject' => $lfp['title'],
						 'lastfinishedproject_link' => base_url('projects/view/'.$lfp['project_id']),
						 'mostrecentproject'=> $mrp['title'],
						 'mostrecentproject_link' => base_url('projects/view/'.$mrp['project_id']));
		}

		/**
		 * Returns the amount of new task assignments for a given user and marks them as old
		 * 
		 * @param String $user user name of the user to get new assignment count for
		 * @return Integer number of notifications
		 */
		public function get_new_assignments($user)
		{
			$new_assignments = $this->db_model->get_single('user', array('username'=>$user),'newassignments');
			$this->db_model->update('user', array('username'=>$user), array('username'=>$user, 'newassignments'=> 0));
			return $new_assignments['newassignments'];
		}
		
		/**
		 * returns all news relevant to the user
		 * 
		 * @version 0.3
		 * 
		 * @param String $user username of the user to receive news for
		 * 
		 * @return mixed news_items
		 */
		public function get_news($user)
		{
			$erg = array();
			$rows = $this->db_model->get_special('globallog', null, null, array('order_by'=>'globallog_id desc limit 10'));
			$pos = 0;
			foreach ($rows as $row) {
				$event_id = $row['logtype_id'];
				$erg[$pos]['date'] = $this->convertDateString($row['timestamp']);
				$erg[$pos]['event_id'] = $event_id;
				
				if( (!is_numeric($row['link']) && $event_id != LOGTYPE_NEW_USER) || 	//fraglich
					($event_id == LOGTYPE_NEW_USER && !$this->db_model->get_single('user', array('username'=>$row['link']), 'username')))	//zB new_project event with already rmved project
				{
					$erg[$pos]['link'] = '<a href=""></a>';
					$erg[$pos]['name'] = $row['link'];
				}
				else
				{
					$erg[$pos]['link'] = '<a href="'.$this->get_linklocation($event_id, $row['link']).'"';
						if($row['logtype_id'] ==  LOGTYPE_NEW_USER)
							$erg[$pos]['link'] .= 'data-target="#modal" data-toggle="modal"';
					$erg[$pos]['link'] .= '>';
					$erg[$pos]['name'] = $this->get_name($event_id, $row['link']) . ' </a>';
				}
				$pos++;
			}
			return $erg;
		}

		/**
		 * Returns a link for the given event_id, link pair
		 * 
		 * @param Integer $event_id log event id
		 * @param String $link link dependent on type of event id
		 */
		private function get_linklocation($event_id, $link)
		{
			switch($event_id)
			{
				case LOGTYPE_NEW_PROJECT 		: return base_url('projects/view/'.$link); 
				case LOGTYPE_NEW_USER			: return base_url('users/view/'.$link);	//TODO: userProfile [name of user in $link]
				case LOGTYPE_DELETE_USER		: return base_url('users/show_users'); //TODO: vllt kein link in diesem fall
				case LOGTYPE_DELETE_PROJECT		: return base_url('projects');	//TODO: [not used?]
				case LOGTYPE_FINISH_PROJECT		: return base_url('projects/view/'.$link);
			}
		}

		/**
		 * Returns, dependent on the event type, either the name of the project or user in question
		 * 
		 * @param Integer $event_id log event id 
		 * @param String $link link dependent on type of event id
		 */
		private function get_name($event_id, $link)
		{
			switch($event_id)
			{
				case LOGTYPE_NEW_PROJECT 	: 	$result = $this->db_model->get_single('project', array('project_id'=>$link), 'title');
												return $result['title']; 
				case LOGTYPE_NEW_USER		:	return $link; 
				case LOGTYPE_DELETE_USER	:	return $link;
				case LOGTYPE_DELETE_PROJECT	:	return $link;	//TODO: [not used]
				case LOGTYPE_FINISH_PROJECT	:	$result = $this->db_model->get_single('project', array('project_id'=>$link), 'title');
												return $result['title'];
			}
		}

		/**
		 * creates a table for output files
		 * 
		 * @param String $section 'Level' of the outputfile table - project, scene, shot, task
		 * @param Integer $id id of the $section item
		 * @param String $status ....
		 * @param mixed $files the files to display
		 */
		function createOutputFileTable($section, $section_id, $status, $files)
		{
			$this->load->library('table');
			$this->load->model('assets');
			$this->load->helper('form');
			
			$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
			
			$canEdit = $this->permission->hasPermission('edit', $section, $section_id);
			$edit = $canEdit ? EDIT_ICON : '';
			
            if($section == 'project')
            {
                $this->table->set_heading(array(array('data' => 'File '.$edit),
                                                array('data' => 'Actions')));
            }
            else
            {
                $this->table->set_heading(array(array('data' => 'File '.$edit),
                                                array('data' => $status.($status == 'Description' ? ' '.$edit : '')),
                                                array('data' => 'Actions')));
            }

			$rows = null;
			foreach($files as $file)
			{
				$editUrl = base_url('all_assets/edit/specific/'.$file['asset_id']);
				
			    $showcase = array(	'data' => ($file['type_name'] == 'Link' ?
												'<a href="http://' . $file['path'] . '" target="_blank" onmouseover="link=false;" onmouseout="link=true;">'.$file['title'].'</a>' :
												'<a href="' . base_url('all_assets/showcase/' . strtolower($file['type_name']) . '/' . $file['path']) . '" onmouseover="link=false;" onmouseout="link=true;" data-target="#modal" data-toggle="modal">'.$file['title'].'</a>'),
								 	'onclick' => 'if(link) edit(this, "'.$editUrl.'/title")');
                
				if($section != 'project')
	                $stat = $status == 'Description' ?
	                			array('data' => '<div style="overflow-x:auto">'.$file['description'].'</div>', 'onclick' => 'edit(this, "'.$editUrl.'/description")') :
	               				array('data' => form_dropdown('appstatus', array('For Approval', 'Approved'), $file['approved'], 'id="'.$file['asset_id'].'" onchange="setApproval(this, \''.base_url($section.'s/approveFile/'.$section_id).'\')"'));
                
				$suburl = $this->assets->is_Linked($file['asset_id']) ? 'unlink_asset/'.$section.'/'.$section_id : 'destroy/'.$section.'_'.$section_id;
				$url = base_url('all_assets/'.$suburl.'/' . $file['asset_id']);
				
				$inhalt = '';
				
				if($status != 'Description' && $canEdit)
					$inhalt .= '<a href="' . base_url('all_assets/change_global/'.$section.'/'.$section_id.'/' . $file['asset_id'] .'/'. (int)!$file['global']) .'" data-toggle="tooltip" title="change visibility" style="'. ($file['global'] ? 'color: #4D99E0;' : 'color: gray;') .'"><i class="fa fa-globe"></i></a> ';
				
				if($file['type_name'] != 'Link')
					$inhalt .= '<a href="' . MEDIA . strtolower($file['type_name']) . '/' . $file['path'] . '" data-toggle="tooltip" title="download" download><i class="fa fa-download"></i></a> ';
    
				if($canEdit)
					$inhalt .= $this->assets->is_Linked($file['asset_id']) ? 
									'<a href="' . $url . '" onclick="return confUnlinkAsset();" data-toggle="tooltip" title="unlink"><i class="fa fa-chain-broken"></i></a>' :
									'<a href="' . $url . '" onclick="return confDestroyAsset();" data-toggle="tooltip" title="delete"><i class="fa fa-times"></i></a>';
				
                $actions = array('data' => $inhalt);

                $rows[] = $section == 'project' ? array($showcase, $actions) : array($showcase, $stat, $actions);
			}

			return is_null($rows) ? '' : $this->table->generate($rows);
		}

		function createUserTable($section, $section_id)
		{
            $this->table->set_template(array('table_open' => '<table class="table table-bordered">'));

			$canUnassign = $this->permission->haspermission('unassign', $section, $section_id);
			
			$heading = array(	array('data' => 'Name'),
								array('data' => 'Role'),
								array('data' => 'Last access'));
								
			if($canUnassign)
				$heading[] = array('data' => '');
             
            $this->table->set_heading($heading);
            
            $users = $this->section_model->get_users($section, $section_id);
			
			$row = null;
            foreach($users as $user)
            {
                $username = $user['username'];
				$unassign = $canUnassign ?
							'<a onclick="return confUnassign(\''.$section.'\', \''.$user['firstname'].'\', \''.$user['lastname'].'\');" href="'.base_url('users/unassign/'.$username.'/project/'.$section_id).'"><i class="fa fa-minus-circle"></i></a>' :
							'';

                $row = array(	array('data' => '<div style=\'overflow-x:auto\'><a href="'.base_url('users/profile/'.$username).'">'.$user['firstname'].' '.$user['lastname'].'</a></div>', 'class' => 'wordwrap'),
								array('data' => $user['role_title']),
                                array('data' => $user['lastaccess'])
                            );
							
				if($canUnassign)
					$row[] = array('data' => $unassign, 'style'=>'text-align:center');
				
				$this->table->add_row($row);
            }
			
			return is_null($row) ? '' : $this->table->generate();
		}

		function createButton($section, $section_id, $status, $allFinished)
		{
			if($status == STATUS_IN_PROGRESS && $allFinished && $this->permission->hasPermission('setForApproval', $section, $section_id))
				$conf = array('setForApproval', 'fa-circle-o', 'Set for Approval');
			
			elseif(in_array($status, array(STATUS_FOR_APPROVAL, STATUS_FINISHED, STATUS_PRE_PRODUCTION)) && $this->permission->hasPermission('setInProgress', $section, $section_id))
			{
				$conf = array('setInProgress', 'fa-spinner', 'Set to In Progress');
				
				if($section == 'task')
					$conf[1] = 'fa-caret-square-o-right';				
				
				if($status == STATUS_FOR_APPROVAL && $this->permission->hasPermission('finish', $section, $section_id) && $allFinished)
					array_push($conf, 'finish', 'fa-check-circle-o', 'Finish Scene');
			}
			
			elseif($section == 'task' && $status == STATUS_UNASSIGNED && $this->permission->hasPermission('recruit', 'task', $section_id))
				return '<a href="'.base_url('users/show/task/'.$section_id) .'" class="btn btn-default btn-sm"><i class="fa fa-user"></i> Recruit new User</a>';

			return !isset($conf) ? '' :
						'<a href="'.base_url($section.'s/'.$conf[0].'/'.$section_id) .'" class="btn btn-default btn-sm"><i class="fa '.$conf[1].'"></i> '.$conf[2].'</a>'.
						(count($conf) == 6 ?	'<a href="'.base_url($section.'s/'.$conf[3].'/'.$section_id).'" class="btn btn-default btn-sm"><i class="fa  '.$conf[4].'"></i> '.$conf[5].'</a>' :
											'');
		}
    }
?>
