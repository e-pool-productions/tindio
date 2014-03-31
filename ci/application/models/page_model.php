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
		$this->load->model('section_model');;
			
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
            $mrp = $this->db_model->get_single('project', 'startdate IS NOT null ORDER BY startdate', 'title, project_id');

			return array('totalprojects' => count($this->db_model->get('project', null, 'title')),
			             'totalscenes'   => count($this->db_model->get('scene', null, 'title')),
						 'totalshots'    => count($this->db_model->get('shot', null, 'title')),
						 'totaltasks'    => count($this->db_model->get('task', null, 'title')),
						 'totalusers'    => count($this->db_model->get('user', null, 'username')),
						 'lastfinishedproject' => $lfp['title'],
						 'lastfinishedproject_link' => base_url('projects/view/'.$lfp['project_id']),
						 'mostrecentproject'=> $mrp['title'],
						 'mostrecentproject_link' => base_url('projects/view/'.$mrp['project_id']));
						 return array('totalprojects'=> $totalprojects);
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
		 * creates a table tamplate with classname $classname
		 * 
		 * @param String $classname class attribute of the table
		 * @param $width width if the table (in percent), default 100.
		 */
		public function get_table_template($classname, $width = 100)
		{
            return array(   'table_open'          => '<table class="'.$classname.'" style="width: '.$width.'%;">',
                            'heading_row_start'   => '<tr class="form" style="font-size: 0.750em;">',
                            'heading_row_end'     => '</tr>',
                            'heading_cell_start'  => '<th class="cell" style="text-align: left;">',
                            'heading_cell_end'    => '</th>',
	                                
                            'row_start'           => '<tr class="form" style="font-size: 0.750em;">',
                            'row_end'             => '</tr>',
                            'cell_start'          => '<td class="cell" style="text-align: left;">',
                            'cell_end'            => '</td>',
	                                
                            'row_alt_start'       => '<tr class="form" style="font-size: 0.750em">',
                            'row_alt_end'         => '</tr>',
                            'cell_alt_start'      => '<td class="cell" style="text-align: left;">',
                            'cell_alt_end'        => '</td>',
	                                
                            'table_close'         => '</table>');
		}

		/**
		 * creates a table for output files
		 * 
		 * @param String $section 'Level' of the outputfile table - project, scene, shot, task
		 * @param Integer $id id of the $section item
		 * @param String $status ....
		 * @param mixed $files the files to display
		 */
		public function createOutputFileTable($section, $id, $status, $files)
		{
			$this->load->library('table');
			$this->load->model('assets');
			$this->load->helper('form');
			
			
			$this->table->set_template($this->page_model->get_table_template($section.'_files'));
			
            if($section == 'project')
            {
                $this->table->set_heading(array(array('data' => 'File <i class="icon-pencil" title="edit"></i>'),
                                                array('data' => 'Actions')));
            }
            else
            {
                $this->table->set_heading(array(array('data' => 'File <i class="icon-pencil" title="edit"></i>'),
                                                array('data' => $status),
                                                array('data' => 'Actions')));
            }
			
			$rows = null;
            $rows2 = null;
			foreach($files as $file)
			{
			    $showcase = array(	'data' => ($file['type_name'] == 'Link' ?
												'<a href="http://' . $file['path'] . '" target="_blank" onmouseover="link=false;" onmouseout="link=true;">'.$file['title'].'</a>' :
												'<a href="' . base_url('all_assets/showcase/' . strtolower($file['type_name']) . '/' . $file['path']) . '" onmouseover="link=false;" onmouseout="link=true;" data-target="#modal" data-toggle="modal">'.$file['title'].'</a>'),
								 	'onclick' => 'if(link) editSectionAsset(this, ' . $file['asset_id'] . ', "'.base_url('all_assets/edit/title/'.$section.'_'.$id).'")');
                
                $stat = $status == 'Description' ?
                			array('data' => $file['description'], 'onclick' => 'edit("description", this, ' . $file['asset_id'] . ', "'.base_url('all_assets/edit/description/'.$section.'_'.$id).'")') :
                			($section == 'project' ? '' :
                				array('data' => form_dropdown('appstatus', array('For Approval', 'Approved'), $file['approved'], 'id="'.$file['asset_id'].'" onchange="setApproval(this, \''.base_url($section.'s/approveFile/'.$id).'\')"')));
                
                $actions = array('data' => '<a href="' . base_url('all_assets/change_global/'.$section.'_'.$id.'/' . $file['asset_id'] .'/'. (int)!$file['global']) .'" class="tooltip" style="'. ($file['global'] ? 'color: #4D99E0;' : 'color: gray;') .'"><i class="icon-globe" title="change visibility"></i></a> '.
                            				($file['type_name'] == 'Link' ? '' : '<a href="' . MEDIA . strtolower($file['type_name']) . '/' . $file['path'] . '" class="tooltip" download><i class="icon-download-alt" title="download"></i></a> ').
				                            ($this->assets->is_Linked($file['asset_id']) ? 
				                                '<a href="' . base_url('all_assets/unlink_asset/'.$section.'_'.$id.'/' . $file['asset_id']) . '" onclick="return confUnlinkAsset();" class="tooltip"><i class="icon-unlink" title="unlink"></i></a>' :
												'<a href="' . base_url('all_assets/destroy/'.$section.'_'.$id.'/'.$file['asset_id']).'" onclick="return confDestroyAsset();" class="tooltip"><i class="icon-remove" title="delete"></i></a>'), 'style'=>'text-aling:center');
			    
                $rows[] = $section == 'project' ? array($showcase, $actions) : array($showcase, $stat, $actions);
			}

			return $this->table->generate($rows);
		}
    }
?>
