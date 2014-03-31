<?php
	/**
	 * model providing functions for granting or denying permissions to the currently logged in user
	 */
	class Permission extends CI_Model
	{
		/**	
		 * grants or denies the currently logged in user permission 
		 * to execute the $action on the item specified by $section and $section_id
		 * 
		 * @param String $action action the user wants to perform
		 * @param String $section section of the item the user wants to perform the $action on
		 * @param Integer $section_id id of the item the user wants to perform the $action on. 
		 * 		This parameter is optional, because some actions do not require to specify an item within a section, default null 
		 */
		function hasPermission($action, $section, $section_id = null)
		{
			$session = $this->session->userdata('logged_in');
			$user = $session['user'];
			
			if($session['isAdmin'])
				return true;
			if($section == 'workflow')
			{
				$isDirector = $this->isDirector();
				$isScSup = $this->isScSup();
				$isShSup = $this->isShSup();
				switch($action)
				{
					case 'create': return $isDirector || $isScSup || $isShSup;
					default: return false;
				}
			}
			$isDirector = $this->isDirector($section, $section_id);
			switch($section)
			{
				case 'project':
					switch($action)
					{
						case 'create'  : return $session['isAdmin'];	//default
						case 'delete'  : return $session['isAdmin'];	//default
						case 'finish'  : return $isDirector;
						case 'edit'    : return $isDirector;
						case 'recruit' : return $isDirector;
						case 'unassign': return $isDirector;
						default: return false;
					}
				case 'scene':
					$isScSup = $this->isScSup($section_id);
					
					switch($action)
					{
						case 'finish':			return $isDirector || $isScSup;
						case 'setInProgress':	return $isDirector || $isScSup;
						case 'setForApproval':	return $isDirector || $isScSup;
						case 'create':			return $isDirector; 
						case 'delete':			return $isDirector;
						case 'approveFile':		return $isDirector;
						case 'recruit':			return $isDirector;
						case 'unassign':		return $isDirector;
						default:				return false;
					}
				case 'shot':
					$shot = $this->db_model->get_single('shot', "shot_id = $section_id", 'scene_id');
					$isScSup = $this->isScSup($shot['scene_id']);
					$isShSup = $this->isShSup($section_id);
					
					switch($action)
					{
						case 'setInProgress':
							if($shot['status_id'] == STATUS_FINISHED)
								return $isDirector;
							else
								return $isDirector || $isScSup || $isShSup;
						case 'setForApproval':	return $isDirector || $isScSup || $isShSup;
						case 'finish':			return $isDirector || $isScSup;
						case 'editTask': 		return $isDirector || $isScSup || $isShSup;
						case 'deleteTask': 		return $isDirector || $isScSup || $isShSup;
						case 'delete':			return $isDirector || $isScSup;
						case 'addWorkflow': 	return $isDirector || $isScSup || $isShSup;
						case 'approveFile':		return $isDirector || $isScSup;
						case 'recruit':			return $isDirector || $isScSup; 
						case 'unassign':		return $isDirector || $isScSup;
						case 'create':			return $isDirector || $isScSup;
						default:				return false;
					}
				case 'task':
					$task = $this->db_model->get_single('task t, shot s', "t.task_id = $section_id AND t.shot_id = s.shot_id", 't.status_id, t.shot_id, scene_id');
					$isScSup = $this->isScSup($task['scene_id']);
					$isShSup = $this->isShSup($task['shot_id']);
					$isArtist = $this->isArtist($section_id);
					
					switch($action)
					{
						case 'setInProgress':
							if($task['status_id'] == STATUS_PRE_PRODUCTION)
								return $isDirector || $isScSup || $isShSup || $isArtist;
							else
								return $isDirector || $isScSup || $isShSup;
						case 'setForApproval':	return $isDirector || $isScSup || $isShSup || $isArtist;
						case 'finish':			return $isDirector || $isScSup || $isShSup;
						case 'edit': 			return $isDirector || $isScSup || $isShSup;
						case 'delete': 			return $isDirector || $isScSup || $isShSup;
						case 'recruit':			return $isDirector || $isScSup || $isShSup;
						case 'create':			return $isDirector || $isScSup || $isShSup;
						case 'comment':			return $isDirector || $isScSup || $isShSup || $isArtist;
						case 'approveFile':		return $isDirector || $isScSup || $isShSup;
						case 'unassign':		return $isDirector || $isScSup || $isShSup;
						case 'upload':			return $isDirector || $isScSup || $isShSup || $isArtist;
						default:				return false;
					}
				// TODO, nur provisorisch zum Testen
				case 'workflow':
					$isScSup = $this->isScSup();
					$isShSup = $this->isShSup();
					switch($action)
					{
						case 'editWorkflow': 	return $isDirector;
						case 'addTask':			return $isDirector || $isScSup || $isShSup;
						default: 				return false;
					}
				case 'user':
					switch($action)
					{
						case 'create': 	return $isDirector;
						case 'recruit': return $isDirector; 
						case 'promoteToAdmin' : return $session['isAdmin'];	//default
						case 'demoteFromAdmin': return $session['isAdmin']; //default
						default:		return false;
					}
			}
		}

		/**
		 * checks if the currently logged in user, or a given user has the role 'admin'
		 * 
		 * @param String $username name of the user to check roles against, if left blank the currenty logged in user
		 * 		will be checked agains the role 'admin'. Default null
		 */
		function isAdmin($username = null)
		{
			if(isset($username))
				return $this -> db_model -> get_single('admin' , array('username' => $username));
			$session = $this->session->userdata('logged_in');
			return $session['isAdmin'];
		}

		/**
		 * checks if the currently logged in user has the role 'director' in a given item
		 * 
		 * @param String $section section of the item, default null. If left blank, will check if the currently logged in user
		 * 					has the role 'director' in any project
		 * @param String $section_id id of the item, default null.
		 */		
		function isDirector($section = null, $section_id = null)
		{
			$session = $this->session->userdata('logged_in');
			$user = $session['user'];
			
			if(!is_null($section_id) && !is_null($section))
			{
				if($section == 'project')
					return ($this->db_model->get_single("userproject", "username = '$user' AND project_id = $section_id", 'username') !== false);
				else
					return ($this->db_model->get_single("userproject up, $section s", "up.username = '$user' AND up.project_id = s.project_id AND s.project_id = $section_id", 'username') !== false);
			}
			else
				return ($this->db_model->get_single("userproject", "username = '$user'", 'username') !== false);
		}

		/**
		 * checks if the currently logged in user has the role 'SceneSupervisor' in a given scene
		 * 
		 * @param String $section_id id of the scene, default null. If left blank, checks if the currently logged in user
		 * 				is 'SceneSupervisor' in any scene.
		 */				
		function isScSup($section_id = null)
		{
			$session = $this->session->userdata('logged_in');
			$user = $session['user'];
			
			if(!is_null($section_id))
				return ($this->db_model->get_single('userscene', "username = '$user' AND scene_id = $section_id", 'username') !== false);
			else
				return ($this->db_model->get_single("userscene", "username = '$user'", 'username') !== false);
		}

		/**
		 * checks if the currently logged in user has the role 'ShotSupervisor' in a given Shot
		 * 
		 * @param String $section_id id of the shot, default null. If left blank, checks if the currently logged in user
		 * 				is 'ShotSupervisor' in any shot.
		 */			
		function isShSup($section_id = null)
		{
			$session = $this->session->userdata('logged_in');
			$user = $session['user'];
			
			if(!is_null($section_id))
				return ($this->db_model->get_single('usershot', "username = '$user' AND shot_id = $section_id", 'username') !== false);
			else
				return ($this->db_model->get_single('usershot', "username = '$user'", 'username') !== false);
		}

		/**
		 * checks if the currently logged in user has the role 'Artist' in a given Task
		 * 
		 * @param String $section_id id of the Task, default null. If left blank, checks if the currently logged in user
		 * 				is 'Artist' in any Task.
		 */					
		function isArtist($section_id = null)
		{
			$session = $this->session->userdata('logged_in');
			$user = $session['user'];
			
			if(!is_null($section_id))
				return ($this->db_model->get_single('usertask', "username = '$user' AND task_id = $section_id", 'username') !== false);
			else
				return ($this->db_model->get_single('usertask', "username = '$user'", 'username') !== false);
		}
	}
?>