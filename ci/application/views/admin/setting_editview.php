<script type="text/javascript" src="<?php echo(JS.'modalSubmit.js'); ?>"></script>
<script type="text/javascript" src="<?php echo(JS.'datetime_picker.js'); ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo(CSS.'create.css'); ?>">

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Edit <?= $setting?></h4>
        </div>
        <div class="modal-body">
        	<?php   
            	echo validation_errors();
				echo form_open('settings/form', array('id' => 'subForm', 'name' => 'subForm'));
           		echo "<p></p>";
				echo form_hidden('id', $id);
				echo form_hidden('setting', $setting);
				echo form_label('Name: ', 'title');
				echo form_input(array('name' => 'title', 'size' => '20', 'maxlength' => '20', 'value'=> $oldTitle,'style' => 'font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px;color: #000;border-radius: 0px;'));
				unset($data);
				echo form_close();
			?>
		</div>
        <div class="modal-footer">
	        <?php 
	            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
	            echo form_button(array('content' => 'Edit', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submit()')) . br(1);
	        ?>
        </div>
    </div>
</div>