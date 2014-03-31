<?php
	class Section_get_model extends CI_Model
	{
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
		public function all_finished($duties)
		{
			if(empty($duties))
				return FALSE;

			for ($i = 0; $i < count($duties); $i++) 
				if($duties[$i]['status_id'] != STATUS_FINISHED)
					return FALSE;
			return TRUE;
		}	

		public function get_myprojects($username)
		{
            $projects = $this->db_model->get(   'project p, projectobserver po',
                                                "po.username = '$username' AND
                                                po.project_id = p.project_id ORDER BY title");
			return $projects;
		}
		
		public function get_myscenes($username)
		{
			return $this->get_myprojects($username);	//identical due to no 'project entity' 
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
			$count = 0;
			switch($section)
			{
				case 'project':
					$observers = $this->get_project_observers($section_id);
					$directos = $this->get_directors($section_id);
					
					foreach($directos as $person)
					{
						$data[$count] = $this->set_username_and_last_access($person,'Dir');
						$count++;
					}
					foreach($observers as $person)
					{
						$data[$count] = $this->set_username_and_last_access($person, 'Obs');
						$count++;
					}
				case 'scene':
					$scenesupervisors = $this->get_scene_supervisors($section, $section_id);
					foreach($scenesupervisors as $person)
					{
						$data[$count] = $this->set_username_and_last_access($person, 'Sup');
						$count++;
					}
				case 'shot':
					$shotsupervisors = $this->get_shot_supervisors($section, $section_id);
					foreach($shotsupervisors as $person)
					{
						$data[$count] = $this->set_username_and_last_access($person, 'Sup');
						$count++;
					}
				case 'task':
					$artists = $this->get_artists($section, $section_id);
					foreach($artists as $person)
					{
						$data[$count] = $this->set_username_and_last_access($person, 'Art');
						$count++;
					}
					break;
			}
			return $data;
		}
		
		private function set_username_and_last_access($person, $role)
		{
			return array('username'=>$person['username'],
			             'firstname'=>$person['firstname'],
			             'lastname'=>$person['lastname'],
			             'lastaccess'=>$this->page_model->timesince($person['lastaccess']),
			             'gravatar_email'=>$person['gravatar_email'],
                         'role_title' => $role);
		}
		
		public function get_crewtext($crew)
		{
			if($crew == 0)
				return 'Nobody';
			else if ($crew == 1)
				return '1 person';
			else 
				return $crew . ' people';
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
