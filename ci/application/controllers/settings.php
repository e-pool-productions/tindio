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
			$this->load->model(array('page_model', 'user_model', 'permission', 'check'));
			$this->load->library(array('table', 'form_validation', 'gravatar'));
			$this->load->helper('form');	
        }
		
		/**
		 * shows all categories 
		 * and skills present in the system and offers functionality to create new, edit and delete categories and skills
		 */
		public function index()
		{
			$session = $this->session->userdata('logged_in');
			if(!$session['isAdmin'])
				redirect('page');

			$categories = $this->db_model->get('category');
			$skills = $this->db_model->get('skill');

			$this->table->set_template($this->page_model->get_table_template('setting_categories', 40));	
			$this->table->set_heading(array('0' => array('data' => 'Name', 'style' => 'width: 1px;'),
            								'1' => array('data' => 'Actions', 'style' => 'width: 1px; text-align: center'))
            							);
			foreach($categories as $category)
			{
				$row = array(
							'0' => array('data' =>$category['title']),
							'1' => array('data' =>'<a href="'.base_url('settings/edit/category/'.$category['category_id']).'" data-target="#modal" data-toggle="modal" title="edit category" class="tooltip"><i class="icon-pencil"></i></a>
                             					   <a href="'.base_url('settings/delete/category/'.$category['category_id']).'" title="delete category" class="tooltip"><i class="icon-remove"></i></a>', 'style'=> 'text-align: center')
							);          	
	           $this->table->add_row($row);
			}
			$data['categories'] = $this -> table-> generate();
			$this->table->clear();
			$this->table->set_template($this->page_model->get_table_template('setting_skills', 40));
			$this->table->set_heading(array('0' => array('data' => 'Name', 'style' => 'width: 1px;'),
            								'1' => array('data' => 'Actions', 'style' => 'width: 1px; text-align: center'))
            							);
			foreach($skills as $skill)
			{
				$row = array(
							'0' => array('data' =>$skill['title']),
							'1' => array('data' =>'<a href="'.base_url('settings/edit/skill/'.$skill['skill_id']).'" data-target="#modal" data-toggle="modal" title="edit skill" class="tooltip"><i class="icon-pencil"></i></a>
                             					   <a href="'.base_url('settings/delete/skill/'.$skill['skill_id']).'" title="delete skill" class="tooltip"><i class="icon-remove"></i></a>', 'style'=> 'text-align: center')
							);          	
	           $this->table->add_row($row);
			}
			$data['skills'] = $this -> table-> generate();
            $data['title'] = 'Settings';
			
			$this->template->load('admin/admin_settingsview', $data);
		}
		
		/**
		 * edit form for skills or categories
		 */
		public function form()
		{
			$title = $this->input->post('title');
			$id = $this->input->post('id');
			$setting = $this->input->post('setting');
			$this->form_validation->set_rules('title', 'Title', 'required|trim|xss_clean|callback_check->setting['.$setting.','.$id.']');
			if ($this->form_validation->run() == FALSE)
			{
				$this->edit($setting, $id);
				return;
			}
			if(!$this->db_model->get_single($setting, array($setting.'_id'=>$id)))
			{
				echo 'done';
				return;
			}
			$this->db_model->update($setting, array($setting.'_id'=>$id), array('title'=>$title));
			
			echo 'done';	
		}
		
		/**
		 * shows a modal to edit either a category or a skill
		 * 
		 * @param String $setting 'category' or 'skill
		 * @param Integer $id id of the category or skill to edit
		 */
		public function edit($setting, $id)
		{
			$session = $this->session->userdata('logged_in');
			if(!$session['isAdmin'])
				redirect('page');
			
			if(!$this->db_model->get_single($setting, array($setting.'_id'=>$id)))
				redirect('settings');

			$data['setting']  = $setting;
			$data['id'] 	  = $id;
			$data['oldTitle'] = $this->db_model->get_single($setting, array($setting.'_id'=>$id), 'title');
			$data['oldTitle'] = $data['oldTitle']['title'];

			$this->load->view('admin/setting_editview', $data); //TODO: HIER!
		}

		/**
		 * @param String $setting 'category' or 'skill'
		 * @param Integer $id id of the setting entry to delete
		 */
		public function delete($setting, $id)
		{
			$session = $this->session->userdata('logged_in');
			if(!$session['isAdmin'])
				redirect('page');
			if( ($setting != 'category' && $setting != 'skill') || (!$this->db_model->get_single($setting, array($setting.'_id'=>$id))))
				redirect('settings');
			
			$this->db_model->destroy($setting, array($setting.'_id'=>$id));
			redirect('settings');
		}
		
		/**
		 * Adds either a new category or a skill to the system
		 * 
		 * @param String $setting 'category' or 'skill'
		 */
		public function add($setting)
		{
			$session = $this->session->userdata('logged_in');
			if(!$session['isAdmin'])
				redirect('page');
			$this->db_model->insert($setting, array('title'=> 'New '.$setting));
			redirect('settings');
		}
	}
?>
