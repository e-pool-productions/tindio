<?php
    /**
     * model for the calendar and myWork
     */
    class Mystuff_model extends CI_Model {

        /**
         * generates the calendar using the configuration, the given year and month
         * @version 1.0
         * @param $year year to display
         * @param $month month to display
         * @return the calendar
         */
        function generate($year, $month){
        	
			$conf = array(  'show_next_prev' => true,
			                'next_prev_url' => base_url('mystuff/calendar'),
			                'day_type' => 'long',
			                'start_day' => 'monday',
			                'template' =>   '{table_open}<div class="panel panel-default">{/table_open}
							
			                				{heading_row_start}<div class="panel-heading clearfix text-center">{/heading_row_start}
			                				 
			                				{heading_previous_cell}<a href="{previous_url}" class="btn btn-default pull-left">&lt;&lt;</a>{/heading_previous_cell}
			                				{heading_title_cell}<span style="font-size:1.7em;">{heading}</span>{/heading_title_cell}
											{heading_next_cell}<a href="{next_url}" class="btn btn-default pull-right">&gt;&gt;</a>{/heading_next_cell}
			                				
			                				{heading_row_end}</div>{/heading_row_end}
			                					
			                				{week_row_start}<table class="table table-bordered calendar text-center"><tr>{/week_row_start}
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
			                                
			                                {table_close}</table></div>{/table_close}'
											);
            $this->load->library('calendar', $conf);
            
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
        function getWork($user, $sections = array('task', 'shot', 'scene'), $where = true)
        {
        	if(is_null($sections))
				$sections = array('task', 'shot', 'scene');
				
        	foreach($sections as $section)
				$sec_work[] = $this->gather_work_info($section, $this->db_model->get("user$section us, $section s", "username = '$user' AND us.".$section."_id = s.".$section."_id AND $where"));
			
			while (count($sec_work) < 3)
				$sec_work[] = array();			
			
			return array_merge($sec_work[0], $sec_work[1], $sec_work[2]);
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
                            '" data-target="#modal" data-toggle="modal">'.$asset['title'].'</a>, ';
                else 
                    $erg .= '<a href="http://' . $asset['path'] . '" target="_blank">'.$asset['title'].'</a>, ';
            }

            return $erg;
        }
    }
?>
