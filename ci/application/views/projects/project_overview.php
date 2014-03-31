<script type="text/javascript">
    function confDelete() {
        return confirm('Delete whole Project?\n\n' +
                       'There might be associated\n' +
                       '\tScenes\n' +
                       '\tShots\n' +
                       '\tTasks\n' +
                       'which will be deleted too!');
    }
</script>
<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first">
			<a href="<?=base_url('mystuff/dashboard');?>">Home</a>
		</li>
		<li class="last">
			<a href><?=$side;?></a>
		</li>
	</ul>
</div>
<div class="all_projects">PROJECTS</div>

<div class="col_12 column">
	<?php
		echo $addProject;
		
	    $fields = array('No_Select' => '---Select field---',
		                'title' => 'Title',
		                'shortcode' => 'Code',
		                'category' => 'Category',
		                'director' => 'Director',
		                'description' => 'Description',
		                'status' => 'Status',
		                'startdate' => 'Startdate',
						'enddate' => 'Enddate');
						
		echo form_open('projects/filter');						
	    echo form_dropdown('fields', $fields);
	    echo form_input(array('name' => 'filter_terms', 'placeholder' => "Filter terms", 'value' => $filter));
	    echo form_submit(array('name' => 'submit', 'value' => 'Filter'));
	    echo form_close();
		
		echo $all_projects;		
	?>
</div>
