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
        function work()
        {
            // function available to every user
            $this->load->library('table');
            $this->load->helper('form');
            $this->load->model(array('page_model', 'section_model', 'assets'));
            
            $session = $this->session->userdata('logged_in');
            
            $data = $this->mystuff_model->getWork($session['user']);
            
            // TODO: $data[pos]['status']['title'] and not $data['pos']['status'] !!!
            $this->table->set_template($this->page_model->get_table_template('mytasks'));
                                
            $this->table->set_heading(array('0' => array('data' => 'Title'),
                                            '1' => array('data' => 'Type'),
                                            '2' => array('data' => 'Code', 'style'=>'width:175px;'),
                                            '3' => array('data' => 'Project'),
                                            '4' => array('data' => 'Status', 'style' =>'min-width: 120px'),
                                            '5' => array('data' => 'Details', 'style'=>'width: 160px'),
                                            '6' => array('data' => 'Files'),
                                            '7' => array('data' => 'Description')
                                            ));
            if(!is_null($data))
            {
                foreach($data as $work_item)
                {   
                    $status = $this->section_model->get_status($work_item['status']);
                    $color = $status['color'];
                    $status = $status['status'];
                    
                    $row = array(
                            '0' => array('data' => '<div style=\'overflow-x:auto\'><a href="'.$work_item['link'].'">'.$work_item['title'].'</div>','style'=>'max-width:175px; text-align:left'),
                            '1' => array('data' => $work_item['type']),
                            '2' => array('data' => '<div style=\'overflow-x:auto\'>'.$work_item['code'].'</div>', 'style'=>'max-width:175px'),
                            '3' => array('data' => '<div style=\'overflow-x:auto\'><a href="projects/view/'.$work_item['project']['project_id'].'">'.$work_item['project']['title'].'</div>', 'style'=>'max-width:175px; text-align:left'),
                            '4' => array('data' => $status, 'style'=>'color:'.$color.'; min-width: 85px;'),
                            '5' => array('data' => 'DEADLINE - '.$work_item['deadline'].br(1).' Started in '.$work_item['startdate'].br(1).' Ended in '.$work_item['enddate'].br(1).'Duration: '.$work_item['duration']),
                            '6' => array('data' => $work_item['assets']),
                            '7' => array('data' => '<div style=\'overflow-x:auto\'>'.$work_item['description'].'</div>', 'style'=>'max-width:175px')
    
                            ); 
                    $this->table->add_row($row);
                }
            }
            $data['table'] = $this->table->generate();
            $data['title'] = 'My Work';
            
            $this->template->load('mystuff/work', $data);
        }

        /**
		 * Displays the dashboard
		 */
        function dashboard()
        {
            // function available to every user
            $this->load->model('page_model');
            
            $session = $this->session->userdata('logged_in');
            
            $data['news'] = $this->page_model->get_news($session['user']);
            
            $this->load->library('table');
            
            $this->table->set_template($this->page_model->get_table_template('dashboard_tasks'));
            
            $this->table->set_heading(array('0' => array('data' => 'Code'),
                                            '1' => array('data' => 'Task'),
                                            '2' => array('data' => 'Deadline')
                                    ));
            
            $deadlines = $this->page_model->get_deadlines($session['user']);
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
            $data['number_of_new_assignments'] = $this->page_model->get_new_assignments($session['user']);
            $data['title'] = 'Home';

            $this->template->load('mystuff/dashboard', $data);
        }

        // TODO
        // /**
         // * applies the filter to the user table
         // * 
         // * @param $filter 
         // */
        // public function filterform($filter = null)
        // {
            // $field = $this->input->post('fields');
//             
            // if(is_null($filter))
                // $filter = $this->input->post('filter_terms');
// 
            // if($filter == '' || $field == 'No_Select')
                // redirect('users');
//                 
            // $data['users'] = $this->filter($field, $filter);
            // $data['filter'] = $filter;            
            // $this->overview($data);
        // }
// 
        // /**
         // * filters dependent on the given field and filter_terms
         // * 
         // * @param String $field field to filter, default null
         // * @param String $filter <filter_term>,<filter_term>,<filter_term>
         // * 
         // * 
         // */
        // public function filter($field = null, $filter = null)
        // {
            // if(is_null($filter) || $filter == '' || $field == 'No_Select')
                // return;
//             
            // $filter_terms = array_unique(array_map('trim', explode(',', $filter)));
//             
            // switch($field)
            // {
                // case 'roles':
                    // $users = $this->db_model->get('user');
//                     
                    // if(in_array('admin', array_map('strtolower', $filter_terms)))
                        // $admins = array_map(function($ele){return $ele['username'];}, $this->db_model->get('admin'));
// 
                    // $amount = count($users);
// 
                    // for($i = 0; $i < $amount; $i++)
                    // {
                        // if(isset($admins) && in_array($users[$i]['username'], $admins))
                            // continue;
//                         
                        // $upr = array_map(function($ele){return strtolower($ele['role_title']);}, 
                                         // $this->section_get_model->get_userprojectrole($users[$i]['username']));
//                                          
                        // if(!array_intersect(array_map('strtolower', $filter_terms), $upr))
                            // unset($users[$i]);
                    // }
// 
                    // return $users;
                // case 'projects':
                    // $project_ids = array_map(   function($ele){return $ele['project_id'];},
                                                // $this->db_model->get(   'project p',
                                                                        // 'p.title REGEXP "' . implode('(.*)|(.*)', $filter_terms).'"',
                                                                        // 'project_id'));
                    // $users = array();
                    // foreach($project_ids as $project_id)
                        // $users = array_merge($users, $this->section_get_model->get_users('project', $project_id));
// 
                    // return array_unique($users, SORT_REGULAR);
                // default:
                    // $filter_conditions = $filter_terms; break;
            // }
// 
            // return empty($filter_conditions) ? array() : $this->db_model->get('user', "$field REGEXP '" . implode('(.*)|(.*)', $filter_conditions) . "' ORDER BY $field");
        // }
    }
?>     
