<div class="row">
	<div class="col-md-7">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li class="active">My Work</li>
		</ol>
	</div>
	<div class="col-md-5"><span class="pagetitle pagetitle-mini">MY WORK</span></div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
            <div class="panel-heading clearfix">
            	<?php
				    $fields = array('No_Select' => '---Select field---',
				                    'title' => 'Title',
				                    'type' => 'Type',
				                    'code' => 'Code',
				                    'project' => 'Project',
				                    'status' => 'Status',
					                'startdate' => 'Startdate',
									'enddate' => 'Enddate',
									'files' => 'Files',
									'description' => 'Description'
			                        );
					
					echo form_open('mystuff/filterform', array('class' => 'form-inline pull-left'));
							
					echo form_group_open();
				    echo form_dropdown('fields', $fields, null, 'class="form-control"');
				    echo form_input(array('name' => 'filter_terms', 'placeholder' => "Filter terms", 'value' => $filter, 'class' => 'form-control'));
				    echo form_submit(array('name' => 'submit', 'value' => 'Filter', 'class' => 'btn btn-default form-control'));
					echo form_group_close();
					
					echo form_close();
				?>
			</div>
            <div class="table-responsive">
                <?=$myWorkTable?>
            </div>
        </div>
	</div>
</div>