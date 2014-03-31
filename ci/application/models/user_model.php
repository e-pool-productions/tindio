<?php
	/**
	 * model for the all-users and edit profile pages
	 */
	class user_model extends CI_Model
	{		
		/**
		 * adds the person ($username) either to the project (if $type = 'project') as an project observer
		 * or assigns the person to the given duty ($type, $id)
		 * 
		 * @param String $username name of the person to recruit
		 * @param String $type task/shot/scene/project
		 * @param Integer $id id of the duty to assign the person to
		 */
		public function recruit($username, $type, $id)
		{
			$session = $this->session->userdata('logged_in');
			if(!isset($session['user']))
			{
				redirect('login');
			}
			
			$admin = count($this->db_model->get('admin',array('username'=>$session['user'])))==1;
			$director = count($this->db_model->get('userproject',array('username'=>$session['user'])))>0;
			if(!($admin || $director))
			{
				echo 'You don\'t have the rights to recruit someone!';
				return;
			}
			if($type != 'task' && $type != 'shot' && $type != 'scene' && $type != 'project')
			{
				echo 'bad type'; return;
			}
			
			if(count($this->db_model->get('user',array('username'=>$username)))==0)	//TODO: This might be where 'no invites' can go
			{
				echo 'user not found!';
				return;
			}
			if(count($this->db_model->get($type, array($type.'_id'=>$id)))==0)
			{
				echo 'invalid '.$type.' id!'; return;
			}
			if(count($this->db_model->get('user'.$type, array($type.'_id'=>$id, 'username'=>$username)))>0)
			{
				echo 'user already assigned'; return;
			}
			
			if($type != 'project')
				$this -> db_model ->insert('user'.$type, array($type.'_id'=>$id, 'username'=>$username)); 
			else 
				$this->assign_to_project($username, $id);	
			
			if($type == 'task')
			{
				$task = $this -> db_model -> get_single('task', array('task_id' => $id));
				if($task['status_id'] == 0)
					$this -> db_model ->update('task', array('task_id' => $id), array('status_id' => 1));
				$newAssignments =$this->db_model->get_single('user', array('username'=>$username), 'newassignments');
				$newAssignments = $newAssignments['newassignments']+1;
				$this->db_model->update('user', array('username'=>$username), array('newassignments'=>$newAssignments));
				
			}
		}

		/**
		 * adds the person ($username) to the project ($id) as observer
		 * @param String $username name of the person to add to the project
		 * @param Integer $id id of the project to add the person to
		 */
		public function assign_to_project($username, $id)
		{
			//TODO: check data again!
			if(count($this->db_model->get('projectobserver', array('project_id'=>$id, 'username'=>$username)))>0)
				return;
			$this->db_model->insert('projectobserver', array('project_id'=>$id, 'username'=>$username)); 
		}

		/**
		 * 	unassigns a given user from a specified item
		 * 
		 * @param String $username name of the person to dissociate
		 * @param String $type type of the duty to dissociate the person from
		 * @param Integer $id id of the duty to dissociate the person from
		 * @return TRUE if user was unassigned
		 */
		public function unassign($username, $type, $id)
		{
			if($id == false)
			{
				echo "no duty specified!";
				//redirect('users/show_users');
				//return;
			}
			$session = $this->session->userdata('logged_in');
			$logged_in_user = $session['user'];
			
			if($type != 'task' && $type != 'shot' && $type != 'scene' && $type != 'project')
				{echo 'bad type'; return FALSE;}
			if(count($this->db_model->get('user',array('username'=>$username)))==0)
				{echo 'user not found!'; return FALSE;}
			if(count($this->db_model->get($type, array($type.'_id'=>$id)))==0)
				{echo 'invalid '.$type.' id! ('.$id.')'; return FALSE;}
			$project_id = -1;
			switch($type)
			{
				case 'task': $project_id = $this->db_model->get_single('task', 'task_id = "'.$id.'"', 'project_id'); break;
				case 'shot': $project_id = $this->db_model->get_single('shot', 'shot_id = "'.$id.'"', 'project_id'); break;
				case 'scene': $project_id = $this->db_model->get_single('scene', array('scene_id'=>$id), 'project_id'); break;
				case 'project': $project_id = array('project_id'=>$id);
			}
			$project_id = $project_id['project_id'];
			$admin = $session['isAdmin'];
			$dir_temp =$this->db_model->get('userproject',array('username'=>$logged_in_user, 'project_id'=>$project_id));
			$director = !empty($dir_temp);
			echo $project_id;
			if(!$admin && !$director)
			{echo 'You don\'t have the rights to unassign.'; return FALSE;}

			switch($type)
			{
				case 'task': $this->unassign_from_task($username, $id); break;
				case 'shot': $this->unassign_from_shot($username, $id); break;
				case 'scene':$this->unassign_from_scene($username, $id);break;
				case 'project':$this->unassign_from_project($username, $id);break;
			}
			return TRUE;
		}

		/**
		 * dissociates the user ($username) from the task ($id)
		 * 
		 * @param String $username name of the user to dissociate
		 * @param Integer $id id of the task to dissociate the user from
		 */
		private function unassign_from_task($username, $id)
		{
			$this->db_model->destroy('usertask', array('username'=>$username, 'task_id'=>$id), TRUE);
			$usertask = $this->db_model->get('usertask', array('task_id' => $id));
			
			$task = $this -> db_model -> get_single('task', array('task_id' => $id));
			
			//Users assigned?
			if(count($usertask)==0 && $task['status_id'] == 1)
			{
				$this -> db_model -> update('task', array('task_id' => $id), array('status_id' => 0));
			}
		}
		
		/**
		 * dissociates the user ($username) from the shot ($id) and all tasks associated with the shot
		 * 
		 * @param String $username name of the user to dissociate
		 * @param Integer $id id of the shot to dissociate the user from
		 */
		private function unassign_from_shot($username, $id)
		{
			$task_ids = $this->db_model->get('task', array('shot_id'=>$id), 'task_id');
			foreach($task_ids as $task_id)
			{
				$this->unassign_from_task($username, $task_id['task_id']);
			}
			$this->db_model->destroy('usershot', array('username'=>$username, 'shot_id'=>$id), TRUE);
		}
		
		/**
		 * dissociates the user ($username) from the scene ($id) and all shots and tasks associated with the scene
		 * 
		 * @param String $username name of the user to dissociate
		 * @param Integer $id id of the scene to dissociate the user from
		 */
		private function unassign_from_scene($username, $id)
		{
			$shot_ids = $this->db_model->get('shot', array('scene_id'=>$id), 'shot_id');
			foreach($shot_ids as $shot_id)
			{
				$this->unassign_from_shot($username, $shot_id['shot_id']);
			}
			$this->db_model->destroy('userscene', array('username'=>$username, 'scene_id'=>$id));
		}
		
		/**
		 * dissociates the user ($username) from the project ($id) and all scenes, shots and tasks 
		 * associated with the project
		 * 
		 * @param String $username name of the user to dissociate
		 * @param Integer $id id of the project to dissociate the user from
		 */
		private function unassign_from_project($username, $id)
		{
			$scene_ids = $this->db_model->get('scene', array('project_id'=>$id), 'scene_id');
			foreach($scene_ids as $scene_id)
			{
				$this->unassign_from_scene($username, $scene_id['scene_id']);
			}
			$this->db_model->destroy('userproject', array('username'=>$username, 'project_id'=>$id));
			$this->db_model->destroy('projectobserver', array('username'=>$username, 'project_id'=>$id));
		}
		
	}
?>