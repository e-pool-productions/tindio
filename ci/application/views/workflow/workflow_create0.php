<script type="text/javascript" src="<?php echo(JS.'modalSubmit.js'); ?>"></script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Create workflow</h4>
        </div>
			<?php				
				echo form_open('workflows/create0form', array('id' => 'subForm', 'name' => 'subForm'));
				echo "<p></p>";					
				echo form_label('Title: ', 'title', array('style'=>'font-weight: bold; text-align: right; width: 200px;'));
				echo form_input(array('name' => 'title', 'size' => '20', 'maxlength' => '20', 'style' => 'font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px;color: #000;border-radius: 0px;')) . br(1);
				echo form_label('Amount of tasks: ', 'num_of_tasks', array('style'=>'font-weight: bold; text-align: right; width: 200px;'));
				echo form_input(array('name'=>'num_of_tasks', 'size' => '20', 'maxlength' => '20', 'style' => 'font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px;color: #000;border-radius: 0px;')) . br(2);
				echo form_close();
			?>
		<div class="modal-footer">
            <?php
                echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
                echo form_button(array('content' => 'continue', 'class'=>'btn btn-primary','type' => 'submit', 'onclick' => 'submit()')) . br(1);
            ?>
        </div>
    </div>
</div> 