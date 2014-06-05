<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Add a new Link</h4>
        </div>
        <div class="modal-body">
            <?php 
                echo validation_errors();
				$action = "upload/addLink/$section".(isset($section_id) ? "/$section_id" : '');
                echo form_open($action, array('id' => 'subForm', 'name' => 'subForm', 'class' => 'form-horizontal'));
                
                echo form_group_open();
                echo form_label('Title: ', 'title', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
                echo form_div_open('col-sm-8');
                echo form_input(array('name' => 'title', 'placeholder' => 'Name your Asset', 'maxlength' => '40', 'value' => set_value('title'), 'class' => 'form-control'));
                echo form_div_close();
                echo form_group_close();
				
                echo form_group_open();
                echo form_label('Url:', 'url', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
                echo form_div_open('col-sm-8');
                echo form_input(array('name' => 'url', 'placeholder' => 'http://example.com/tree.obj', 'maxlength' => '40', 'value' => set_value('url'), 'class' => 'form-control'));
                echo form_div_close();
                echo form_group_close();
                
                echo form_group_open();
                echo form_label('Description: ', 'description', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
                echo form_div_open('col-sm-8');
                echo form_textarea(array('name' => 'description', 'value' => $this->input->post('description'), 'class' => "form-control", 'rows' => '3'));
                echo form_div_close();
                echo form_group_close();
				
				echo form_group_open();
                echo form_label('Tags: ', 'tags', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
                echo form_div_open('col-sm-8');
                echo form_textarea(array('name' => 'tags', 'placeholder' => 'tree, obj', 'value' => $this->input->post('tags'), 'class' => "form-control", 'rows' => '3'));
                echo form_div_close();
                echo form_group_close();

				if(isset($setting)) echo form_hidden('setting', $setting);
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
<script type="text/javascript" src="<?=JS.'formSubmit.js';?>"></script>