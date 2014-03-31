<script type="text/javascript">
    function confDelete() {
        msg = "Do you really want to remove this User completely?";
        return confirm(msg);
    }
</script>

<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first">
			<a href="<?=base_url('mystuff/dashboard');?>">Home</a>
		</li>
		<li class="last">
			<a href>All Users</a>
		</li>
	</ul>
</div>
<div class="all_users">USERS</div>
<div class="col_12 column">
	<?php
		if($isAdmin || $isDirector) echo'<a href="'.base_url('users/create').'" data-target="#modal" data-toggle="modal" class="button small user"><i class="icon-user"></i> Add new user</a>';

		echo form_open('users/filterform', array('class' => 'user_form'));
	    
	    $fields = array('No_Select' => '---Select field---',
	                    'username' => 'Username',
	                    'firstname' => 'Firstname',
	                    'lastname' => 'Lastname',
	                    //'skills' => 'Skills',
	                    'roles' => 'Roles',
	                    'projects' => 'Projects'
                        );
	
	    echo form_dropdown('fields', $fields);
	    echo form_input(array('name' => 'filter_terms', 'placeholder' => "Filter terms", 'value' => $filter));
	    echo form_submit(array('name' => 'submit', 'value' => 'Filter'));
		echo form_close();
        if(isset($section) && isset($section_id))
            echo '<a href="'.base_url($section.'s/view/'.$section_id).'" class="button small"><i class="icon-back"></i> Back</a>';
        echo $table;
	?>
</div>