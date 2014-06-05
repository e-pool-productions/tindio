<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Creating user</h4>
        </div>
        <div class="modal-body">
		<?php
            echo validation_errors();
			echo form_open('users/form', array('id' => 'subForm', 'name' => 'subForm', 'class' => 'form-horizontal'));
			
			echo form_group_open();
		    echo form_label('First Name:', 'first', array('class' => 'col-sm-3 control-label'));
			echo form_div_open('col-sm-8');
			echo form_input(array('name' => 'first', 'maxlength' => '20', 'class' => 'form-control'));
			echo form_div_close();
			echo form_group_close();
			
			echo form_group_open();
			echo form_label('Last Name:', 'last', array('class' => 'col-sm-3 control-label'));
			echo form_div_open('col-sm-8');
			echo form_input(array('name' => 'last', 'maxlength' => '20', 'class' => 'form-control'));
			echo form_div_close();
			echo form_group_close();
			
			echo form_group_open();
			echo form_label('Username:', 'user', array('class' => 'col-sm-3 control-label'));
			echo form_div_open('col-sm-8');
			echo form_input(array('name' => 'user', 'maxlength' => '20', 'class' => 'form-control'));
			echo form_div_close();
			echo form_group_close();
			
			echo form_group_open();
			echo form_label('<i class="fa fa-info-circle" title="The length of your passwort has to be at least 6 letters; it also has to contain at least one capital letter and one number"></i> Password:', 'password', array('class' => 'col-sm-3 control-label'));
			echo form_div_open('col-sm-8');
			echo form_password(array('name' => 'password', 'maxlength' => '32', 'class' => 'form-control'));
			echo form_div_close();
			echo form_group_close();
			
			echo form_group_open();
		   	echo form_label('Mail:', 'mail', array('class' => 'col-sm-3 control-label'));
			echo form_div_open('col-sm-8');
		   	echo form_input(array('name' => 'mail', 'maxlength' => '50', 'class' => 'form-control'));
			echo form_div_close();
		    echo form_close();
		?>
		</div>
        <div class="modal-footer">
        <?php 
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Create', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submitForm(true);'));
        ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?=JS.'formSubmit.js';?>"></script>
