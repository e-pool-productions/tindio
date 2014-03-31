<script type="text/javascript" src="<?=JS.'modalSubmit.js';?>"></script>
<script type="text/javascript" src="<?=JS.'datetime_picker.js'; ?>"></script>
<script type="text/javascript" src="<?=JS.'dp_config.js'; ?>"></script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title"><?php if($new) echo 'Creating Task'; else echo 'Editing Task'; ?></h4>
        </div>
        <div class="modal-body">
			<?php 
				echo validation_errors(); 
    			$link = $new ? 'tasks/form/create' : 'tasks/form/edit';
                echo form_open($link, array('id' => 'subForm', 'name' => 'subForm'));
				echo "<p></p>";   
			    echo form_label('Title: ', 'title');
				echo form_input(array('name' => 'title', 'size' => '20', 'maxlength' => '40', 'value'=>$oldTitle)) . br(1);	    
			    echo form_label('Description: ', 'description');
			    echo form_textarea(array('name' => 'description', 'value'=>$oldDescription, 'style' => 'color: #000; height: 130px;font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px; overflow-y: hidden; resize: both;')) . br(1);	
			    echo form_hidden('task_id', $task_id);	
				echo form_hidden('shot_id', $shot_id);
				if(!$new) echo form_hidden('oldOrder', $oldOrder);	
			    echo form_label('Deadline: ', 'deadline');
			    echo form_input(array('name' => 'deadline', 'value'=>$oldDeadline)) . br(1);
				echo form_label('Position: Before', 'order');
				foreach ($tasks as $item)
			        $task[] = $item['title'];
			    echo form_dropdown('order', $task, $oldOrder - 1) . br(1);
				echo form_close();
			?>
			</div>
        <div class="modal-footer">
        <?php
        	if($new) $button = 'Create'; else $button = 'Edit';
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => $button, 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submit()')) . br(1);
        ?>
        </div>
    </div>
</div> 