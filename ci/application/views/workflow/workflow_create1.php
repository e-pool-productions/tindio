<script type="text/javascript" src="<?=JS.'modalSubmit.js';?>"></script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?=$name?></h4>
        </div>
			<?php
				echo form_open('workflows/create1form', array('id' => 'subForm', 'name' => 'subForm'));
				for ($i=0; $i < $num_of_tasks; $i++) {
					$num = $i + 1; 
					echo form_label('Title of Task ' . $num. ': ', 'title'.$i, array('style'=>'font-weight: bold; text-align: right; width: 200px;'));
					echo form_input(array('name' => 'title'.$i , 'size' => '20', 'maxlength' => '20', 'value'=> 'TaskTitle '.$num,'style' => 'font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px;color: #000;border-radius: 0px;'));
					echo form_input(array('name' => 'description'.$i ,'size' => '100', 'value' => 'Description of Task '.$num,'style' => 'font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px;color: #000;border-radius: 0px;')) .br(1);
				}
				echo form_hidden('name_of_workflow', $name);
				echo form_hidden('num_of_tasks', $num_of_tasks);
                echo form_close(); 
			?>
		<div class="modal-footer">
            <?php
                echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
                echo form_button(array('content' => 'Create', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submit()')) . br(1); 
            ?>
        </div>
    </div>
</div> 