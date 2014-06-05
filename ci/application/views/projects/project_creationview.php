<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Creating Project</h4>
        </div>
        <div class="modal-body">
	        <?php         
	            echo validation_errors();
	            echo form_open('projects/form/create/', array('id' => 'subForm', 'name' => 'subForm', 'class' => 'form-horizontal'));

				echo form_group_open();
	            echo form_label('Title: ', 'title', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
				echo form_div_open('col-sm-8');
	            echo form_input(array('name' => 'title', 'maxlength' => '40', 'value' => set_value('title'), 'class' => 'form-control'));
				echo form_div_close();
				echo form_group_close();
				
				echo form_group_open();
	            echo form_label('Shortcode: ', 'shortcode', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
	            echo form_div_open('col-sm-8');
	            echo form_input(array('name' => 'shortcode', 'maxlength' => '5', 'value' => set_value('shortcode'), 'class' => 'form-control'));
				echo form_div_close();
				echo form_group_close();
				
				echo form_group_open();
	            echo form_label('Director: ','director', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
	            foreach ($users as $user)
	                $directors[$user['username']] = $user['firstname'].' '.$user['lastname'];
				echo form_div_open('col-sm-8');
	            echo form_dropdown('director', $directors, $this->input->post('director'), 'class="form-control"');
				echo form_div_close();
				echo form_group_close();
				
				echo form_group_open();
	            echo form_label('Category: ', 'category', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
	            foreach ($category as $category_item)
	                $data[$category_item['category_id']] = $category_item['title'];
				echo form_div_open('col-sm-8');
	            echo form_dropdown('category', $data, $this->input->post('category'), 'class="form-control"');
				echo form_div_close();
				echo form_group_close();
				
				echo form_group_open();
	            echo form_label('Deadline: ', 'deadline', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
				echo form_div_open('col-sm-8');
	            echo form_input(array('name' => 'deadline', 'value' => set_value('deadline'), 'class' => 'form-control'));
				echo form_div_close();
				echo form_group_close();

				echo form_close();
	        ?>
        </div>
        <div class="modal-footer">
        <?php 
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Create', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submitForm(true)')); 	
        ?>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?=JS.'formSubmit.js'?>"></script>
<script type="text/javascript" src="<?=JS.'datetime_picker.js'; ?>"></script>
<script type="text/javascript" src="<?=JS.'dp_config.js'; ?>"></script>