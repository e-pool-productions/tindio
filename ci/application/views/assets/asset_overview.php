<div class="row">
	<div class="col-md-7">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li class="active">All Assets</li>
		</ol>
	</div>
	<div class="col-md-5"><span class="pagetitle pagetitle-mini">ASSETS</span></div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
	        <div class="panel-heading clearfix">
				<?php
				    $fields = array('No_Select' => '---Select filter---',
				                    'title' => 'Title',
				                    'author' => 'Author',
				                    'type' => 'Type',
				                    'description' => 'Description',
				                    'tags' => 'Tags',
				                    'uploaddate' => 'Date',
				                    'extension' => 'File Format',
				                    'used_in' => 'Used in');
									
					echo form_open('all_assets/filter', array('class' => 'form-inline pull-left'));
							
					echo form_group_open();
				    echo form_dropdown('fields', $fields, null, 'class="form-control"');
				    echo form_input(array('name' => 'filter_terms', 'placeholder' => "Filter terms", 'value' => $filter, 'class' => 'form-control'));
				    echo form_submit(array('name' => 'submit', 'value' => 'Filter', 'class' => 'btn btn-default form-control'));
					echo form_group_close();
					
					echo form_close();
					
					if(isset($isLinking) && isset($section) && isset($section_id))
						echo '<a href="'.base_url($section.'s/view/'.$section_id).'" class="btn btn-default pull-right"><i class="fa fa-reply"></i> Back</a>';
					
					else
						echo '<div class="btn-group pull-right">
								 <a href="'.base_url('upload/choose_files/general/true').'" data-target="#modal" data-toggle="modal" class="btn btn-default"><i class="fa fa-external-link-square"></i></a>
								 <a href="'.base_url('upload/choose_files/general').'" data-target="#modal" data-toggle="modal" class="btn btn-default"><i class="fa fa-upload"></i></a>
							  </div>';
				?>
			</div>
	        <div class="table-responsive">
	            <?php	    
					$this->table->set_template(array('table_open' => '<table class="table table-bordered">'));
					
					$this->table->set_heading(array(array('data' => '', 'style' => 'width: 9.188em;'),
				        							array('data' => 'Title '.EDIT_ICON),
				        							array('data' => 'Author'),
				        							array('data' => 'Type '.EDIT_ICON),
				        							array('data' => 'Description '.EDIT_ICON),
				        							array('data' => 'Tags '.EDIT_ICON),
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
						
						if($type_id != LINK)
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
						                '<video controls poster="' . MEDIA . 'system/cam.png' . ' class="img-responsive img-thumbnail">
						                    <source src="' . $path . '" type="video/mp4">
						                    <source src="' . $path . '" type="video/webm">
						                    <source src="' . $path . '" type="video/ogg">
						                </video>'; break;
						            // Audio
						            case 1: $preview =
						                '<audio controls class="img-responsive img-thumbnail">
						                    <source src="' . $path . '" type="audio/ogg">
						                    <source src="' . $path . '" type="audio/wav">
						                    <source src="' . $path . '" type="audio/mpeg">
						                    Your browser does not support the audio element.
						                </audio>'; break;
						            // 3D Model
						            case 2: $preview = 
							            '<canvas id="' . $asset_item['asset_id'] . '" width="200"" height="150" class="img-responsive img-thumbnail">
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
						            	'<object data="'.$path.'" class="img-responsive img-thumbnail">
						            		Sorry we can not display this file format
						            		 <!--<img src="'.MEDIA.'system/sad.png" />-->
						            	</object>';
						            	break;
						            // Other
						            default: $preview = '<img src="' . MEDIA . '/system/misc.png' . '" class="img-responsive img-thumbnail">'; break;
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
			            
			
						$editUrl = base_url('all_assets/edit/specific/'.$asset_id);
			
				 		$row = array(   array('data' => $preview),
			                            array('data' => '<div class="wordwrap">'.$asset_item['title'].'</div>', 'class' => 'wordwrap', 'onclick' => 'edit(this, "'.$editUrl.'/title")'),
										array('data' => '<a href="' . base_url('/users/view/' . $asset_item['author']) . '" data-target="#modal" data-toggle="modal">' . $name . '</a>'),
										array('data' => $type_name, 'onclick' => 'edit(this, "'.$editUrl.'/type_id")'),
										array('data' => '<div class="wordwrap">'.$asset_item['description'].'</div>', 'class' => 'wordwrap', 'onclick' => 'edit(this, "'.$editUrl.'/description")'),
										array('data' => $asset_item['tags'], 'onclick' => 'edit(this, "'.$editUrl.'/tags")'),
										$details,
										$asset_item['used_in']);
										
						if(isset($isLinking))
							$actions = '<a href="' . base_url('all_assets/link_asset/'.$section.'/'.$section_id.'/'.$asset_id) . '" class="tooltip"><i class="fa fa-check" title="choose"></i></a>';
						elseif($type_id == LINK)
							$actions = '<a href="http://' . $path . '" target="_blank" class="tooltip"><i class="fa fa-eye-open" title="show"></i></a>';
						else
							$actions = '<a href="'.base_url('all_assets/showcase/'.urlencode(strtolower($type_name)).'/'.$asset_item['path']).'" data-target="#modal" data-toggle="modal"><i class="fa fa-eye" title="show"></i></a>
					                   <a href="'.$path.'" download><i class="fa fa-download" title="download"></i></a>';
									   
						$actions .= ' <a href="'.base_url('all_assets/destroy/general/'.$asset_id).'" onclick="return confDestroyAsset();"><i class="fa fa-times" title="delete"></i></a>';

						$row[] = array('data' => $actions);
														 
				        $this->table->add_row($row);
				    }
				
				    echo $this->table->generate();
				?>
	        </div>
	    </div>
	</div>
</div>
<script src="<?=JS.'confirm.js'?>"></script>
<script src="<?=JS.'edit.js'?>"></script>

<script type="text/javascript">
    var select;
    var filter = <?=json_encode($filter);?>
    
    $(window).load(function() {
        var types = <?=json_encode($this->db_model->get('assettype'));?>;
        typeDrop = document.createElement('select');
        
        types.forEach(function(type){
            var opt = new Option();
            opt.value = type['assettype_id'];
            opt.text = type['type_name'];
            typeDrop.options.add(opt);
        });
    });
</script>