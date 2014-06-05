<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * controller for the calendar, myWork-page and the dashboard 
     */
    class Mystuff extends MY_Controller
    {
    	/**
		 * loads all required models
		 */
        function __construct()
        {
            parent::__construct();
            $this->load->model('mystuff_model');
        }
        
		/**
		 * shows the calendar
		 * 
		 * @param date $year year to display, default null. If left blank, shows the current year and month
		 * @param data $month month to display, default null. If left blank, shows the current year and month
		 */
        function calendar($year = null, $month = null)
        {
            // function available to every user
            if(is_null($year) | is_null($month)){
                $year = date('Y');
                $month = date('m');
            }           
            
            $data['calendar'] = $this->mystuff_model->generate($year, $month);
            
            $data['title'] = 'Calendar';
            
            $this->template->load('mystuff/calendar', $data);
        }
        
		/**
		 * Displays the myWork-page
		 */
        function work($data = null)
        {
            // function available to every user
            $this->load->library('table');
            $this->load->helper('form');
            $this->load->model(array('page_model', 'section_model', 'assets'));
            
            $works = isset($data['works']) ? $data['works'] : $this->mystuff_model->getWork($this->session->userdata('user'));
			unset($data['works']);
            
            $this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
                                
            $this->table->set_heading(array('0' => array('data' => 'Title'),
                                            '1' => array('data' => 'Type'),
                                            '2' => array('data' => 'Code'),
                                            '3' => array('data' => 'Project'),
                                            '4' => array('data' => 'Status'),
                                            '5' => array('data' => 'Details'),
                                            '6' => array('data' => 'Files'),
                                            '7' => array('data' => 'Description')
                                            ));
            if(!empty($works))
            {
                foreach($works as $work)
                {   
                    $status = $this->section_model->get_status($work['status']);
                    
                    $row = array(
                            '0' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.$work['link'].'">'.$work['title'].'</div>', 'class' => 'wordwrap'),
                            '1' => array('data' => $work['type']),
                            '2' => array('data' => '<div style=\'overflow-x:auto\'>'.$work['code'].'</div>', 'class' => 'wordwrap'),
                            '3' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.base_url('projects/view/'.$work['project']['project_id']).'">'.$work['project']['title'].'</div>', 'class' => 'wordwrap'),
                            '4' => array('data' => $status),
                            '5' => array('data' => 'DEADLINE - '.$work['deadline'].br(1).'
                            						Started: '.$work['startdate'].br(1).'
                            						Ended: '.$work['enddate'].br(1).'
                            						Duration: '.$work['duration']),
                            '6' => array('data' => $work['assets']),
                            '7' => array('data' => '<div style=\'overflow-x:auto\'>'.$work['description'].'</div>', 'class' => 'wordwrap')
    
                            ); 
                    $this->table->add_row($row);
                }
            }
            $data['myWorkTable'] = !empty($works) ? $this->table->generate() : '';
            $data['title'] = 'My Work';
			if(!isset($data['filter']))
				$data['filter'] = '';
            
            $this->template->load('mystuff/work', $data);
        }

        /**
		 * Displays the dashboard
		 */
        function dashboard()
        {
            // function available to every user
            $this->load->model('page_model');
			
			$user = $this->session->userdata('user');

            $data['news'] = $this->page_model->get_news($user);
            
            $this->load->library('table');
			
			$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
            
            // $this->table->set_template($this->page_model->get_table_template('dashboard_tasks'));
            
            $this->table->set_heading(array(array('data' => 'Code'),
                                            array('data' => 'Task'),
                                            array('data' => 'Deadline')));
            
            $deadlines = $this->page_model->get_deadlines($user);
            foreach($deadlines as $deadlines_item)
            {
                $row = array(   '0' => array('data' => '<div style=\'overflow-x:auto\'>'.$deadlines_item['code'].'</div>', 'style'=>'max-width:175px'),
                                '1' => array('data' => '<div style=\'overflow-x:auto\'>'.$deadlines_item['task_title'].'</div>', 'style'=>'max-width:90px'),
                                '2' => array('data' => $deadlines_item['deadline'].' '.$deadlines_item['time_left'])
                            );  
                $this->table->add_row($row);
            }
            $data['deadlines'] = $this->table->generate();
            
            $data['globalstats'] = $this->page_model->get_globalstats();
            $data['number_of_new_assignments'] = $this->page_model->get_new_assignments($user);
            $data['title'] = 'Home';

            $this->template->load('mystuff/dashboard', $data);
        }

        /**
         * applies the filter to the user table
         * 
         * @param $filter 
         */
        function filterform($filter = null)
        {
            $field = $this->input->post('fields');
            
            if(is_null($filter))
                $filter = $this->input->post('filter_terms');

            if($filter == '' || $field == 'No_Select')
                redirect('mystuff/work');
            
			$this->load->model(array('page_model', 'section_model', 'assets'));   
			
            $data['works'] = $this->filter($field, $filter);
            $data['filter'] = $filter;            
            $this->work($data);
        }

        /**
         * filters dependent on the given field and filter_terms
         * 
         * @param String $field field to filter, default null
         * @param String $filter <filter_term>,<filter_term>,<filter_term>
         * 
         * 
         */
        function filter($field = null, $filter = null)
        {
            if(is_null($filter) || $filter == '' || $field == 'No_Select')
                return;
            
            $filter_terms = array_filter(array_unique(array_map('trim', explode(',', $filter))));

			if(empty($filter_terms))
				return $this->work();
            
            switch($field)
            {
				case 'type':
					$sections = array('task', 'shot', 'scene');
					foreach ($filter_terms as $filter_term)
                        $filter_conditions = preg_grep("/$filter_term(\w+)/i", $sections);
					
					return empty($filter_conditions) ? array() : $this->mystuff_model->getWork($this->session->userdata('user'), $filter_conditions);
				case 'project':
					foreach ($filter_terms as $filter_term)
                    {
                        $projects = $this->db_model->get('project', "title LIKE '%$filter_term%'");
                        foreach ($projects as $project)
                            $filter_conditions[] = $project['project_id'];
                    }
					
					return empty($filter_conditions) ? array() : $this->mystuff_model->getWork($this->session->userdata('user'), null, 'project_id IN ('.implode(',', $filter_conditions).')');
				case 'status':
					$filter_terms = array_map(function($ele){return str_replace(' ', '_', $ele);}, $filter_terms);
					
					foreach ($filter_terms as $filter_term)
                    {
                        $status = $this->db_model->get('status', "title LIKE '%$filter_term%'");
                        foreach ($status as $sta)
                            $filter_conditions[] = $sta['status_id'];
                    }
					
					return empty($filter_conditions) ? array() : $this->mystuff_model->getWork($this->session->userdata('user'), null, 'status_id IN ('.implode(',', $filter_conditions).')');
				case 'startdate':
                    foreach ($filter_terms as $filter_term)
                        $filter_conditions[] = "startdate LIKE '$filter_term%'";
					return empty($filter_conditions) ? array() : $this->mystuff_model->getWork($this->session->userdata('user'), null, implode(' OR ', $filter_conditions));
                    
                case 'enddate':
                    foreach ($filter_terms as $filter_term)
                        $filter_conditions[] = "enddate LIKE '$filter_term%'";
					return empty($filter_conditions) ? array() : $this->mystuff_model->getWork($this->session->userdata('user'), null, implode(' OR ', $filter_conditions));
				case 'files':
					
                default:
                    $filter_conditions = $filter_terms; break;
            }

            return empty($filter_conditions) ? array() : $this->mystuff_model->getWork($this->session->userdata('user'), null, "$field REGEXP '" . implode('(.*)|(.*)', $filter_conditions) . "' ORDER BY $field"); 
            //$this->db_model->get('user', "$field REGEXP '" . implode('(.*)|(.*)', $filter_conditions) . "' ORDER BY $field");
        }
    }
?>