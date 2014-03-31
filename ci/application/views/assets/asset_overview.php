<script src="<?=JS.'assets.js'?>"></script>

<script type="text/javascript">
    var select;
    var filter = <?=json_encode($filter);?>
    
    $(window).load(function() {
        var types = <?=json_encode($this->db_model->get('assettype'));?>;
        select = document.createElement('select');
        
        types.forEach(function(type){
            var opt = new Option();
            opt.value = type['assettype_id'];
            opt.text = type['type_name'];
            select.options.add(opt);
        });
    });
</script>
<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first">
			<a href="<?=base_url('mystuff/dashboard');?>">Home</a>
		</li>
		<li class="last">
			<a href>All Assets</a>
		</li>
	</ul>
</div>
<div class="all_assets">ASSETS</div>
<div class="col_12 column">	
	<?php
	    echo form_open('all_assets/filter', array('class' => 'all_asset_form'));
	    
	    $fields = array('No_Select' => '---Select filter---',
	                    'title' => 'Title',
	                    'author' => 'Author',
	                    'type' => 'Type',
	                    'description' => 'Description',
	                    'tags' => 'Tags',
	                    'uploaddate' => 'Date',
	                    'extension' => 'File Format',
	                    'used_in' => 'Used in');
	
	    echo form_dropdown('fields', $fields);
	    echo form_input(array('name' => 'filter_terms', 'placeholder' => "Filter terms", 'value' => $filter));
	    echo form_submit(array('name' => 'submit', 'value' => 'Filter'));
	    echo form_close();
        echo '<a href="'.base_url('upload/choose_files/general').'" data-target="#modal" data-toggle="modal" class="button small asset"><i class="icon-picture"></i> Add new Asset</a>';
        if(isset($isLinking) && isset($section) && isset($section_id))
            echo '<a href="'.base_url($section.'s/view/'.$section_id).'" class="button small"><i class="icon-back"></i> Back</a>';
         
		$this->table->set_template($this->page_model->get_table_template('allassets'));
		
		$this->table->set_heading(array(array('data' => '', 'style' => 'width: 9.188em;'),
	        							array('data' => 'Title <i class="icon-pencil" title="editable"></i>'),
	        							array('data' => 'Author'),
	        							array('data' => 'Type <i class="icon-pencil" title="editable"></i>'),
	        							array('data' => 'Description <i class="icon-pencil" title="editable"></i>'),
	        							array('data' => 'Tags <i class="icon-pencil" title="editable"></i>'),
	        							array('data' => 'Details'),
	        							array('data' => 'Used in'),
	        							array('data' => 'Actions')));
	    
	
		
	    foreach ($assets as $asset_item)
	    {
	    	$asset_id = $asset_item['asset_id'];
	    	$type_id = $asset_item['type_id'];
			$type_name = $type_names[$type_id];
			$name = $this->assets->get_name($asset_item['author']);
			$position = strtolower($type_name) . '/' . $asset_item['path'];
			
			if($type_id != 4)
			{
				$path = MEDIA . $position;
				
				if(!is_readable('media/'.$position))
				{
					$preview = 'File not found! Contact your Administrator';
					$details = get_extension($position) . '<br/>?kb<br/>' . $asset_item['uploaddate'];
				}
				else
				{
					switch ($type_id)
			        {
			            // Video
			            case 0: $preview =
			                '<video controls poster="' . MEDIA . 'system/cam.png' . '">
			                    <source src="' . $path . '" type="video/mp4">
			                    <source src="' . $path . '" type="video/webm">
			                    <source src="' . $path . '" type="video/ogg">
			                </video>'; break;
			            // Audio
			            case 1: $preview =
			                '<audio controls>
			                    <source src="' . $path . '" type="audio/ogg">
			                    <source src="' . $path . '" type="audio/wav">
			                    <source src="' . $path . '" type="audio/mpeg">
			                    Your browser does not support the audio element.
			                </audio>'; break;
			            // 3D Model
			            case 2: $preview = 
				            '<canvas id="' . $asset_item['asset_id'] . '" width="200"" height="150">
			    				It seems you are using an outdated browser that does not support canvas :-(
			    			</canvas>
			    			<script type="text/javascript" src="' . JS.'jsc3d.js' . '"></script>
			    			<script type="text/javascript">
					            var viewer = new JSC3D.Viewer(document.getElementById(\'' . $asset_id . '\'));
					            viewer.setParameter(\'SceneUrl\', \'' . $path . '\');
					            viewer.setParameter(\'Definition\', \'high\');
					            viewer.setParameter(\'RenderMode\', \'texturesmooth\');
					            viewer.init();
					            viewer.update();
					        </script>';
					        break;
			            // Image
			            case 3: $preview =
			            	'<object data="'.$path.'">
			            		Sorry we can not display this file format
			            		 <!--<img src="'.MEDIA.'system/sad.png" />-->
			            	</object>';
			            	break;
			            // Other
			            default: $preview = '<img src="' . MEDIA . '/system/misc.png' . '">'; break;
			        }

					$details = get_extension($position) . '<br/>' . filesize('media/'.$position) . 'kb<br/>' . $asset_item['uploaddate'];
		        }
			}
			else
			{
				$path = $asset_item['path'];
                $asset_item['path'] = substr($asset_item['path'], 7);
                $preview = '<a href="http://' . $path . '" target="_blank">Remote Asset</a>';
				$details = 'Remote Asset<br />' .$asset_item['uploaddate'];
			}
            
            

	 		$row = array(   array('data' => $preview, 'style' => 'padding: 2px; font-size: 1.5em;'),
                            array('data' => $asset_item['title'], 'onclick' => 'edit("title", this, ' . $asset_id . ', "'.base_url('all_assets/edit/title').'")'),
							array('data' => '<a href="' . base_url('/users/view/' . $asset_item['author']) . '" data-target="#modal" data-toggle="modal">' . $name . '</a>'),
							array('data' => $type_name, 'onclick' => 'edit("type_id", this, ' . $asset_id . ', "'.base_url('all_assets/edit/type_id').'")'),
							array('data' => $asset_item['description'], 'onclick' => 'edit("description", this, ' . $asset_id . ', "'.base_url('all_assets/edit/description').'")'),
							array('data' => $asset_item['tags'], 'onclick' => 'edit("tags", this, ' . $asset_id . ', "'.base_url('all_assets/edit/tags').'")'),
							$details,
							$asset_item['used_in'],
							array('data' => isset($isLinking) ?
												'<a href="' . base_url('all_assets/link_asset/'.$section.'_'.$section_id.'/'.$asset_id) . '" class="tooltip"><i class="icon-check" title="choose"></i></a>' :
												($type_id == 4 ?
													'<a href="http://' . $path . '" target="_blank" class="tooltip"><i class="icon-eye-open" title="show"></i></a>' :
													'<a href="' . base_url('all_assets/showcase/' . urlencode(strtolower($type_name)) . '/' . $asset_item['path']) . '" data-target="#modal" data-toggle="modal" class="tooltip"><i class="icon-eye-open" title="show"></i></a>
		                        					 <a href="' . $path . '" class="tooltip" download><i class="icon-download-alt" title="download"></i></a>').
		                         				' <a href="' . base_url('all_assets/destroy/general/'.$asset_id) . '" onclick="return confDestroyAsset();" class="tooltip"><i class="icon-delete" title="delete"></i></a>',
	                                            'style' => 'padding: 2px; font-size: 1.3em;'));
											 
	        $this->table->add_row($row);
	    }
	
	    echo $this->table->generate();
	?>
</div>
