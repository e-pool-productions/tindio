<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	 * Controller for the administrator settings (add/edit/delete Categories and skills)
	 */
    class Settings extends MY_Controller
    {
    	/**
		 * constructor 
		 * 
		 * @version 1.0
		 */
        public function __construct()
        {
            parent::__construct();
			$this->load->model(array('permission', 'check'));
			$this->load->library(array('table'));
			$this->load->helper('form');
			
			if(!$this->permission->isAdmin())
				redirect('mystuff/dashboard');
        }
		
		/**
		 * shows all categories 
		 * and skills present in the system and offers functionality to create new, edit and delete categories and skills
		 */
		public function index()
		{
			$categories = $this->db_model->get('category');
			$skills = $this->db_model->get('skill');

			$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
			$this->table->set_heading(array(array('data' => 'Name '.EDIT_ICON),
            								array('data' => 'Actions'))
            							);
										
			foreach($categories as $category)
			{
				$row = array(	array('data' => $category['title'], 'class' => 'wordwrap', 'onclick' => 'edit(this, "'.base_url('settings/edit/category/'.$category['category_id']).'")'),
								array('data' => '<a href="'.base_url('settings/delete/category/'.$category['category_id']).'"><i class="fa fa-times"></i></a>')
							);          	
	           $this->table->add_row($row);
			}
			$data['categories'] = $this -> table-> generate();
			
			$this->table->clear();
			
			$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
			$this->table->set_heading(array(array('data' => 'Name '.EDIT_ICON),
            								array('data' => 'Actions'))
            							);
										
			foreach($skills as $skill)
			{
				$row = array(	array('data' => $skill['title'], 'class' => 'wordwrap', 'onclick' => 'edit(this, "'.base_url('settings/edit/skill/'.$skill['skill_id']).'")'),
								array('data' => '<a href="'.base_url('settings/delete/skill/'.$skill['skill_id']).'" ><i class="fa fa-times"></i></a>')
							);          	
	           $this->table->add_row($row);
			}
			$data['skills'] = $this -> table-> generate();
            $data['title'] = 'Settings';
			
			$this->template->load('admin/settingsview', $data);
		}
		
		/**
		 * Adds either a new category or a skill to the system
		 * 
		 * @param String $setting 'category' or 'skill'
		 */
		public function add($setting)
		{
			$this->db_model->insert($setting, array('title'=> 'New '.$setting));
			redirect('settings');
		}

		function edit($field, $field_id)
        {
        	$newValue = $this->input->post('newValue');
			
			if($newValue === false)
				echo 'No Value specified!';
			
            $this->load->library('form_validation');
			
			$this->form_validation->set_rules('newValue', 'Name', 'required|trim|xss_clean|callback_check->setting['.$field.','.$field_id.']');
            
            if ($this->form_validation->run() == FALSE)
                echo form_error('newValue', ' ', ' ');
            else
            {
            	$this->db_model->update($field, $field."_id = $field_id", array('title' => $newValue));
				echo 'done';
            }

        }

		/**
		 * @param String $setting 'category' or 'skill'
		 * @param Integer $id id of the setting entry to delete
		 */
		public function delete($setting, $id)
		{
			if( ($setting != 'category' && $setting != 'skill') || (!$this->db_model->get_single($setting, "$setting"."_id = $id")))
				redirect('settings');
			
			$this->db_model->destroy($setting, array($setting.'_id'=>$id));
			redirect('settings');
		}
	}
?>
