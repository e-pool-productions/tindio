<script type="text/javascript" src="<?php echo(JS.'datetime_picker.js'); ?>"></script>
<script type="text/javascript" src="<?php echo(JS.'modalSubmit.js'); ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo(CSS.'create.css'); ?>">

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Editing Workflow</h4>
        </div>
        <div class="modal-body">
        <?php   
            echo validation_errors();
            echo form_open('workflows/editform', array('id' => 'subForm', 'name' => 'subForm'));
            echo "<p></p>";
            echo form_hidden('id', $id);
			echo form_hidden('singleTask', $singleTask);
			if($singleTask)
				echo form_hidden('orderposition', $orderposition);
            echo form_label('Workflow title: ', 'title');
			if(!$singleTask)
            	echo form_input(array('name' => 'title', 'size' => '20', 'maxlength' => '20', 'value'=>$oldTitle)) . br(1);
			else 
				echo $oldTitle.br(1);
			$i = $singleTask ? $orderposition : 0;
			foreach ($tasks as $taskitem) {
				echo form_label('Task: ');
				echo form_input(array('name' => 'title'.$i , 'size' => '20', 'maxlength' => '20', 'value'=> $taskitem['task_title'],'style' => 'font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px;color: #000;border-radius: 0px;'));
				echo form_input(array('name' => 'description'.$i ,'size' => '100', 'value' => $taskitem['description'],'style' => 'font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px;color: #000;border-radius: 0px;')) .br(1);
				$i++;	
			}
			echo form_hidden('num_of_tasks', $i);
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