<div class="row">
	<div class="col-md-7">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li class="active"><?=$side;?></li>
		</ol>
	</div>
	<div class="col-md-5"><span class="pagetitle pagetitle-mini">PROJECTS</span></div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
            <div class="panel-heading clearfix">
				<?php
				    $fields = array('No_Select' => '---Select field---',
					                'title' => 'Title',
					                'shortcode' => 'Code',
					                'category' => 'Category',
					                'director' => 'Director',
					                'description' => 'Description',
					                'status' => 'Status',
					                'startdate' => 'Startdate',
									'enddate' => 'Enddate');
									
					echo form_open('projects/filterform', array('class' => 'form-inline pull-left'));
							
					echo form_group_open();
				    echo form_dropdown('fields', $fields, null, 'class="form-control"');
				    echo form_input(array('name' => 'filter_terms', 'placeholder' => "Filter terms", 'value' => $filter, 'class' => 'form-control'));
				    echo form_submit(array('name' => 'submit', 'value' => 'Filter', 'class' => 'btn btn-default form-control'));
					echo form_group_close();
					
					echo form_close();
				?>
				<div class="btn-group pull-right">
                    <?php
                        if($isAdmin)
                        	echo '<a href="'.base_url('projects/create').'" data-target="#modal" data-toggle="modal" class="btn btn-default"><i class="fa fa-film"></i> Add new project</a>';
                    ?>
                </div>
			</div>
            <div class="table-responsive">
                <?=$all_projects?>
            </div>
        </div>
	</div>
</div>

<script src="<?=JS.'edit.js'?>"></script>
<script src="<?=JS.'confirm.js'?>"></script>
<script type="text/javascript">
    $(window).load(function() {
        var categories = <?=json_encode($this->db_model->get('category'));?>;
        cateDrop = document.createElement('select');
        
        categories.forEach(function(category){
            var opt = new Option();
            opt.value = category['category_id'];
            opt.text = category['title'];
            cateDrop.options.add(opt);
        });
    });
</script>