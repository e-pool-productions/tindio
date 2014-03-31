<link rel="stylesheet" type="text/css" href="<?=CSS.'create.css'; ?>">
<script type="text/javascript" src="<?=JS.'modalSubmit.js';?>"></script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php echo 'Edit your profile'; ?></h4>
        </div>
        <div class="modal-body">				
			<?php 
			    $this->load->helper('Date');
				echo validation_errors(); 		
				echo form_open('users/editform', array('id' => 'subForm', 'name' => 'subForm'));
				echo form_label('Enter actual password: ', 'oldPassword');
				echo form_password(array('name' => 'oldPassword', 'size' => '32', 'maxlength' => '32')). br(1);		
				echo form_label('Enter new password: <i class="icon-info-circle" title="The length of your passwort has to be at least 6 letters; it also has to contain at least one capital letter and one number"></i>', 'newPassword');
				echo form_password(array('name' => 'newPassword', 'size' => '32', 'maxlength' => '32')) . br(1);
				echo form_label('Confirm password: ' , 'newPassword2');
				echo form_password(array('name' => 'newPassword2', 'size' => '32', 'maxlength' => '32')) .br(1);
				echo form_label('Username: ', 'newUsername');
				echo form_input(array('name' => 'newUsername', 'value' =>$oldUsername,'size' => '20', 'maxlength' => '20')) . br(1);
				echo form_label('Firstname: ', 'newFirstname');
				echo form_input(array('name' => 'newFirstname', 'value' =>$oldFirstname,'size' => '20', 'maxlength' => '20')) . br(1);
				echo form_label('Lastname: ', 'newLastname');
				echo form_input(array('name' => 'newLastname', 'value' =>$oldLastname,'size' => '20', 'maxlength' => '20')) . br(1);
				echo form_label('E-Mail: ', 'newEMail');
				echo form_input(array('name' => 'newEMail', 'value' => $oldMail,'size' => '20', 'maxlength' => '50')) . br(1);
				echo form_label('Gravatar E-Mail: ', 'gravatar_email');
				echo form_input(array('name' => 'gravatar_email', 'value' => $oldGravatar ,'size' => '20', 'maxlength' => '50')). br(1);
				echo form_label('Skills:', 'skills');
 
	           foreach ($skills as $skill)
	                $data[$skill['skill_id']] = $skill['title'];                     
 
				echo form_dropdown('first_skill', $data, $oldskill1).form_dropdown('second_skill', $data, $oldskill2);
                echo form_label('Timezonel:', 'skills');
                echo timezone_menu($timezone);
				echo form_close(); //
			?>
        <div class="modal-footer">
        <?php
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Edit', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submit()')) . br(1);
        ?>
        </div>
    </div>
</div>