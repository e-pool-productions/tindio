<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     * controller for file upload
     */
    class Upload extends MY_Controller
    {
    	/**
		 * loads required libraries, models and helpers
		 */
        public function __construct()
        {
            parent::__construct();
            $this->load->model('assets');
			$this->load->helper(array('form', 'file'));
			$this->load->library(array('form_validation', 'table'));
        }

        /**
         * functionality to upload an asset
		 * 
		 * @param String $section .. ,delimiter: '_'
		 * 
         * @version 1.0
        */
        function choose_files($section, $isLink = false)
        {
        	$str = explode('_', $section);
			
			$data['section'] = $str[0];
			$data['section_id'] = count($str) > 1 ? $str[1] : '';

			if(count($str) > 2)
				$data['setting'] = $str[2];
			
			if($isLink)
				$this->load->view('assets/newlink_view', $data);
			else
			{
				$data['types'] = $this->db_model->get('assettype');
            	$this->load->view('assets/upload_view', $data);	
			}
        }
		
		/**
		 * uploads a (huge) file
		 */
		public function upload_file()
        {
        	// function available to every user
            if(!isset($_REQUEST['name'])) throw new Exception('Name required');
            
            if(!isset($_REQUEST['index'])) throw new Exception('Index required');
            if(!preg_match('/^[0-9]+$/', $_REQUEST['index'])) throw new Exception('Index error');
            
            if(!isset($_FILES['file'])) throw new Exception('Upload required');
            if($_FILES['file']['error'] != 0) throw new Exception('Upload error');
            
            $target = 'media/processing/' . strtolower($_REQUEST['name']) . '-' . $_REQUEST['index'];
            
            move_uploaded_file($_FILES['file']['tmp_name'], $target);
            
            // Might execute too quickly.
            sleep(1);
        }
        
		/**
		 * merges the parts of a (huge) file back together
		 */
        public function merge_file()
        {
            if(!isset($_REQUEST['name'])) throw new Exception('Name required');
            
            if(!isset($_REQUEST['index'])) throw new Exception('Index required');
            if(!preg_match('/^[0-9]+$/', $_REQUEST['index'])) throw new Exception('Index error');
            
            $index = $_REQUEST['index'];
            $target = "media/processing/" . strtolower($_REQUEST['name']);
            $dst = fopen($target, 'wb');
            
            for($i = 0; $i < $index; $i++) {
                $slice = $target . '-' . $i;
                $src = fopen($slice, 'rb');
                stream_copy_to_stream($src, $dst);
                fclose($src);
                unlink($slice);
            }
            
            fclose($dst);
        }

		/**
		 * inserts and uploads files
		 */
        function insertFiles($section, $section_id = null)
        {
            $fileAmount = $this->input->post('amount');
            $type_names = $this->assets->get_type_names();

            for($i = 0; $i < $fileAmount; $i++)
            {
                $fileNames[] = strtolower($this->input->post('name'.$i));
                
                $path = "media/" . strtolower($type_names[$this->input->post('type'.$i)]) . '/';
                
                if(file_exists($path.$fileNames[$i]))
                {
                    $ext = get_extension($path.$fileNames[$i]);
                    $filename = str_replace($ext, '', $fileNames[$i]);

                    $new_filename = '';
                    for ($j = 1; $j < 100; $j++)
                    {
                        if ( ! file_exists($path.$filename.$j.$ext))
                        {
                            $new_filename = $filename.$j.$ext;
                            break;
                        }
                    }
                }
                else
                    $new_filename = $fileNames[$i];
                
                $success[] = rename("media/processing/" . $fileNames[$i], $path . $new_filename);
                
                if($success[$i])
                    $files[] = array(   'title' => $this->input->post('title'.$i),
                                        'author' => $this->session->userdata('user'),
                                        'type_id' => $this->input->post('type'.$i),
                                        'description' => $this->input->post('description'.$i),
                                        'path' => $new_filename,
                                        'tags' => $this->input->post('tags'.$i),
                                        'global' => ($section == 'general'));
            }
            
            $data['section'] = $section;
            $data['success'] = $success;
            $data['filenames'] = $fileNames;
            
            if(isset($files))
            {
                $this->db_model->insert('asset', $files, true);
                if($section != 'general' && !is_null($section_id))
                {
                    $asset_id = $this->db_model->get_single('asset', null, 'MAX(asset_id) as value');
					
					for($i = 0; $i < count($files); $i++)
					{
	                    $insert[$i] = array($section . '_id' => $section_id, 'asset_id' => $asset_id['value']);
	                    if($section == 'task')
	                        $insert[$i]['local'] = $this->input->post('setting');
						$asset_id['value']--;					   
					}
                    $this->db_model->insert($section . 'asset', $insert, true);
                    
                    $data['section_id'] = $section_id;
                }
            }
            
            $this->load->view('assets/upload_successfull', $data);
        }

		function addLink($section, $section_id = null)
		{
			$this->form_validation->set_rules('title', 'Title', 'required|trim|xss_clean');
			$this->form_validation->set_rules('url', 'Url', 'required|trim|xss_clean');
			
			if ($this->form_validation->run() == FALSE)
                $this->choose_files($section, true);
            else
            {
				$url = $this->input->post('url');
	            $fileName =  strncmp($url, 'http://', 7) ? $url : substr($url, 7);
				
				$file = array(	'title' => $this->input->post('title'),
								'author' => $this->session->userdata('user'),
								'type_id' => LINK,
								'description' => $this->input->post('description'),
								'path' => $fileName,
								'tags' => $this->input->post('tags'),
								'global' => ($section == 'general'));
								
				$this->db_model->insert('asset', $file);
				
	            if($section != 'general' && !is_null($section_id))
	            {
	                $asset_id = $this->db_model->get_single('asset', null, 'MAX(asset_id) as value');
					
	                $insert = array($section . '_id' => $section_id, 'asset_id' => $asset_id['value']);
					
	                if($section == 'task')
	                    $insert['local'] = $this->input->post('setting');
									   
	                $this->db_model->insert($section . 'asset', $insert, true);
	            }
				
				echo 'done';
			}
		} 
    }
?>