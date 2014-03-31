<link rel="stylesheet" type="text/css" href="<?=CSS.'create.css'; ?>">

<div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Select workflow</h4>
            </div>
				<?php
					echo form_open('workflows/selectionform');
					echo form_hidden('id', $id);
					echo "<p></p>";
					echo form_label('Workflow:  ', 'workflows', array('style'=>'font-weight: bold; text-align: right; width: 200px;'));
					foreach ($workflows as $workflow_item)
				        $data[$workflow_item['workflow_id']] = $workflow_item['title'];
					echo form_dropdown('workflow', $data,'onChange="select(this.options[this.selectedIndex].innerHTML','style="font-weight: bold; text-align: right; width: 200px;"') . br(2);
					echo form_label('Position: Before  ', 'order', array('style'=>'font-weight: bold; text-align: right; width: 200px;'));
					foreach ($tasks as $item)
				        $task[] = $item['title'];
				    echo form_dropdown('order', $task, '' ,'style="font-weight: bold; text-align: right; width: 200px;"') . br(1);		
				?>
			<div class="modal-footer">
	            <?php
	                echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
	                echo form_button(array('content' => 'Add workflow', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'document.subForm.submit()')) . br(1);
	                echo form_close();  
	            ?>
            </div>
        </div>
</div> 