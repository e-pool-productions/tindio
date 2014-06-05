<div class="row">
	<div class="col-md-7">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li class="active">All Users</li>
		</ol>
	</div>
	<div class="col-md-5"><span class="pagetitle pagetitle-mini">USERS</span></div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
            <div class="panel-heading clearfix">
            	<?php
        		    $fields = array('No_Select' => '---Select field---',
				                    'username' => 'Username',
				                    'firstname' => 'Firstname',
				                    'lastname' => 'Lastname',
				                    'skills' => 'Skills',
				                    'roles' => 'Roles',
				                    'projects' => 'Projects'
				                    );
		
					echo form_open('users/filterform', array('class' => 'form-inline pull-left'));
					
					echo form_group_open();
				    echo form_dropdown('fields', $fields, null, 'class="form-control"');
				    echo form_input(array('name' => 'filter_terms', 'placeholder' => "Filter terms", 'value' => $filter, 'class' => 'form-control'));
				    echo form_submit(array('name' => 'submit', 'value' => 'Filter', 'class' => 'btn btn-default form-control'));
					echo form_group_close();
					
					echo form_close();
				?>
                <div class="btn-group pull-right">
                    <?php
                    	if(isset($section) && isset($section_id))
	            			echo '<a href="'.base_url($section.'s/view/'.$section_id).'" class="btn btn-default"><i class="fa fa-reply"></i> Back</a>';
							
                        if($isAdmin || $isDirector)
                        	echo '<a href="'.base_url('users/create').'" data-target="#modal" data-toggle="modal" class="btn btn-default"><i class="fa fa-user"></i> Add new user</a>';
                    ?>
                </div>
            </div>
            <div class="table-responsive">
                <?=$table;?>
            </div>
        </div>
	</div>
</div>
<script src="<?=JS.'confirm.js'?>"></script>