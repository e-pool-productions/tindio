<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * 
     */
    class All_assets extends MY_Controller
    {
        public function __construct()
        {
            parent::__construct();
            $this->load->library('table');
			$this->load->model(array('page_model', 'assets', 'permission'));
            $this->load->helper(array('form', 'file'));
        }
		
        /**
         * shows all assets
		 * @version 1.0
         */
        public function index()
        {
        	//every user can see assets  
            $data['assets'] = $this->assets->get_assets();
            $data['filter'] = '';

            $this->create_view($data);
        }

        function create_view($data)
        {
            for($i = 0; $i < count($data['assets']); $i++)
                $data['assets'][$i]['used_in'] = implode(', ', $this->assets->get_used_in($data['assets'][$i]['asset_id'])); // Array of Projecttitles for this Asset
            
            $data['type_names'] = $this->assets->get_type_names();
            $data['title'] = 'Assets';
            $this->template->load('assets/asset_overview', $data);
        }

        function link_asset($section, $section_id, $id = null)
        {
			if(is_null($id))
			{
	            $data['section'] = $section;
	            $data['section_id'] = $section_id;
				
				$data['assets'] = $this->assets->get_linkable_assets($section, $section_id);

				$data['isLinking'] = true;
                $data['filter'] = '';

	            $this->create_view($data);
			}
			else
			{
				$this->assets->link_asset($str[0], $str[1], $id);
				redirect($section . 's/view/' . $section);
			}
        }

		function unlink_asset($section, $section_id, $asset_id)
		{
			$this->assets->unlink_asset($section, $section_id, $asset_id);
			redirect($section . 's/view/' . $section_id);
		}
        
        function change_global($section, $section_id, $asset_id, $global)
        {
            $this->assets->edit($asset_id, 'global', $global);
            redirect($section . 's/view/' . $section_id);
        }
		   
        function edit($section, $asset_id, $field)
        {
            $this->assets->edit($asset_id, $field, nl2br($this->input->post('newValue')));
            
            if($section == 'general')
                redirect('all_assets/filter/' . $this->input->post('filter'));
            else
                echo 'done';
        }
		
		function destroy($section, $asset_id)
        {
            $this->assets->destroy($asset_id);
			
			if($section == 'general')
				redirect('all_assets/filter/' . $this->input->post('filter'));
			else
			{
				$str = explode('_', $section);
				redirect($str[0] . 's/view/' . $str[1]);
			}
        }
        
        function showcase($dir = null, $file = null)
        {
            if(!isset($dir) || !isset($file))
                return;

            $path = urldecode($dir . '/' . $file);

            switch(get_extension($path))
            {
                case '.obj': $data['ext'] = '.obj'; break;
                default: $data['ext'] = 'other';
            }
            $data['path'] = $path;
            $this->load->view('assets/showcase', $data);
        }
		
		function filter($filter = null)
        {
        	//every user can filter assets
        	if($filter == 'myAssets')
			{
				$data['assets'] = $this->db_model->get('asset', 'author = \''.$this->session->userdata('user').'\' ORDER BY title');
                $data['filter'] = 'my:';
			}
			else
			{
				$field = $this->input->post('fields');
            
	            if(is_null($filter))
	                $filter = $this->input->post('filter_terms');
	
	            if($filter == '' || $field == 'No_Select')
	                redirect('all_assets');
				
				$data['assets'] = $this->assets->filter($field, $filter);
            	$data['filter'] = $filter;
			}
			
            $this->create_view($data);
        }
    }
?>
