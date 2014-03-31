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
        
        public function filter($filter = null)
        {
        	//every user can filter assets
        	if($filter == 'myAssets')
			{
				$session = $this->session->userdata('logged_in');
				$data['assets'] = $this->db_model->get('asset', 'author = \''.$session['user'].'\' ORDER BY title');
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

        function create_view($data)
        {
            for($i = 0; $i < count($data['assets']); $i++)
            {
                $used_in = $this->assets->get_used_in($data['assets'][$i]['asset_id']); // Array of Projecttitles for this Asset
                $data['assets'][$i]['used_in'] = implode(', ', $used_in);
            }
            
            $data['type_names'] = $this->assets->get_type_names();
            $data['title'] = 'Assets';
            $this->template->load('assets/asset_overview', $data);
        }

        function link_asset($section, $id = null)
        {
            $str = explode('_', $section);
            
			if(is_null($id))
			{
	            $data['section'] = $str[0];
	            $data['section_id'] = $str[1];
				
				$data['assets'] = $this->assets->get_linkable_assets($str[0], $str[1]);

				$data['isLinking'] = true;
                $data['filter'] = '';

	            $this->create_view($data);
			}
			else
			{
				$this->assets->link_asset($str[0], $str[1], $id);
				redirect($str[0] . 's/view/' . $str[1]);
			}
        }

		function unlink_asset($section, $asset_id)
		{
			$str = explode('_', $section);
			$this->assets->unlink_asset($str[0], $str[1], $asset_id);
			redirect($str[0] . 's/view/' . $str[1]);
		}
        
        function change_global($section, $asset_id, $global)
        {
            $str = explode('_', $section);
            $this->assets->edit($asset_id, 'global', $global);
            redirect($str[0] . 's/view/' . $str[1]);
        }
        
        function edit($field = null, $section = 'general')
        {
            $this->assets->edit($this->input->post('asset_id'), $field, nl2br($this->input->post('newValue')));
            
            if($section == 'general')
                redirect('all_assets/filter/' . $this->input->post('filter'));
            else
            {
                $str = explode('_', $section);
                redirect($str[0] . 's/view/' . $str[1]);
            }
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
    }
?>
