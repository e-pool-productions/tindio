<link rel="stylesheet" type="text/css" href="<?=CSS.'css.css'; ?>">
<script type="text/javascript" src="<?=JS.'modalSubmit.js';?>"></script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Creating user</h4>
        </div>
        <div class="modal-body">
		<?php
            echo validation_errors();
			echo form_open('users/form', array('id' => 'subForm', 'name' => 'subForm'));
		    echo form_label('First Name: ');
			echo form_input(array('name' => 'first', 'size' => '20', 'maxlength' => '20', 'id' => 'first')) . br(1);
			echo form_label('Last Name: ');
			echo form_input(array('name' => 'last', 'size' => '20', 'maxlength' => '20', 'id' => 'last')) . br(1);
			echo form_label('Username: ');
			echo form_input(array('name' => 'user', 'size' => '20', 'maxlength' => '20', 'id' => 'user')) . br(1);
			echo form_label('Password: <i class="icon-info-circle" title="The length of your passwort has to be at least 6 letters; it also has to contain at least one capital letter and one number"></i>');
			echo form_password(array('name' => 'password', 'size' => '32', 'maxlength' => '32')) .br(1);
		   	echo form_label('Mail: ', 'mail');
		   	echo form_input(array('name' => 'mail'));
		    echo form_close();
		?>
		</div>
        <div class="modal-footer">
        <?php 
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Create', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submit();')) . br(1);
        ?>
        </div>
    </div>
</div>