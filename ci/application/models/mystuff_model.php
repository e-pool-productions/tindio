<?php
    /**
     * model for the calendar and myWork
     */
    class Mystuff_model extends CI_Model {
        
        /**
         * calendar configuration
         */
        var $conf;
        
        /**
         * Constructor
         * sets the configuration variable
         */
        function __construct()
        {
            parent :: __construct();
            
            $this -> conf = array(
                'show_next_prev' => true,
                'next_prev_url' => base_url('mystuff/calendar'),
                'day_type' => 'long',
                'template' =>   '{table_open}<table border="0" cellpadding="0" cellspacing="0" class="calendar">{/table_open}
                
                                {heading_row_start}<tr>{/heading_row_start}
                                
                                {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
                                {heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
                                {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
                                
                                {heading_row_end}</tr>{/heading_row_end}
                                
                                {week_row_start}<tr>{/week_row_start}
                                {week_day_cell}<td>{week_day}</td>{/week_day_cell}
                                {week_row_end}</tr>{/week_row_end}
                            
                                {cal_row_start}<tr class="days">{/cal_row_start}
                                {cal_cell_start}<td>{/cal_cell_start}
                                
                                {cal_cell_content}
                                    <div class="day_num">{day}</div>
                                    <div class="content" style="overflow-x:auto; max-width: 174px; margin-right:-100px">{content}</div>
                                {/cal_cell_content}
                                {cal_cell_content_today}
                                    <div class="day_num highlight">{day}</div>
                                    <div class="content" style="overflow-x:auto; max-width: 174px; margin-right:-100px">{content}</div>
                                {/cal_cell_content_today}
                                
                                {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
                                {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
                                
                                {cal_cell_blank}&nbsp;{/cal_cell_blank}
                                
                                {cal_cell_end}</td>{/cal_cell_end}
                                {cal_row_end}</tr>{/cal_row_end}
                                
                                {table_close}</table>{/table_close}');
    
        }   

        /**
         * generates the calendar using the configuration, the given year and month
         * @version 1.0
         * @param $year year to display
         * @param $month month to display
         * @return the calendar
         */
        function generate($year, $month){
            
            $this->load->library('calendar', $this->conf);
            
            $session = $this->session->userdata('logged_in');
            $sections = array('task', 'shot', 'scene');

            $dates = array();
            foreach($sections as $section)
            {
                $query = $this->db_model->get("$section s, user$section us, project p", 
                                'us.username = "'.$session['user'].'" AND us.'.$section.'_id = s.'.$section.'_id AND 
                                 s.deadline LIKE "'.$year.'-'.$month.'%" AND s.project_id = p.project_id', 
                                's.title, s.'.$section.'_id, s.deadline, p.shortcode');
                                
                foreach($query as $entry)
                {
                    $date = new DateTime($entry['deadline']);
                    if(!isset($dates[(int)$date->format('d')]))
                        $dates[(int)$date->format('d')] = '';
                    $dates[(int)$date->format('d')] .= $entry['shortcode'].
                                                ' <a href="'.base_url($section.'s/view/'.$entry[$section.'_id']).'"> '.$entry['title'].'</a><br>';
                }
            }

            return $this->calendar->generate($year, $month, $dates);
        }

        /**
         * gets all tasks, shots and scenes the given user is involved in
         * 
         * @version 0.7
         * 
         * @param $user String username of the user to get work for
         * @return mixed work_items
         */
        public function getWork($user)
        {           
            return array_merge( $this->gather_work_info('task', $this->db_model->get('usertask ut, task t', "username = '$user' AND ut.task_id = t.task_id")),
                                $this->gather_work_info('shot', $this->db_model->get('usershot ush, shot sh', "username = '$user' AND ush.shot_id = sh.shot_id")),
                                $this->gather_work_info('scene', $this->db_model->get('userscene usc, scene sc', "username = '$user' AND usc.scene_id = sc.scene_id")));
        }

		/**
		 * collects work info
		 * 
		 * @param String $section section of the $items
		 * @param Array $items work items
		 * 
		 * return Array formated data
		 */
        private function gather_work_info($section, $items)
        {
            $data = array();
            foreach ($items as $item)
            {
                $item_id = $item[$section.'_id'];
                $assets = $this->assets->get_assets($section, $item_id);
                $data[] = array('title' => $item['title'],
                                'type'  => $section,
                                'code' => $this->section_model->create_shortcode($section, $item_id),
                                'description' => $item['description'],
                                'deadline' => $this->page_model->convertDateString($item['deadline']),
                                'startdate' => $this->page_model->convertDateString($item['startdate']),
                                'enddate' => $this->page_model->convertDateString($item['enddate']),
                                'duration' => $this->section_model->getIntervall($item['enddate'],$item['startdate']),
                                'link' => base_url($section.'s/view/'.$item_id),
                                'project' => $this->db_model->get_single('project', 'project_id = '.$item['project_id']),
                                'status' => $item['status_id'],
                                'assets' => $this->generate_asset_links($section, $assets));
            }
            return $data;
        }

		/**
		 * generates assets links
		 * 
		 * @param String $section section of the $assets
		 * @param Array $assets assets to get link from
		 * 
		 * @return String assets with links
		 */
        function generate_asset_links($section, $assets)
        {
            $erg = '';
            foreach($assets as $asset)
            {
                if($asset['type_name'] != 'Link')
                    $erg .= '<a href="' . base_url('all_assets/showcase/' . urlencode(strtolower($asset['type_name'])) . '/' . $asset['path']) . 
                            '" data-target="#modal" data-toggle="modal" class="tooltip">'.$asset['title'].' </a>, ';
                else 
                    $erg .= '<a href="http://' . $asset['path'] . '" target="_blank" class="tooltip">'.$asset['title'].'</a>, ';
            }

            return $erg;
        }
    }
?>
