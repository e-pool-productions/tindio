<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Select Workflow</h4>
        </div>
        <div class="modal-body">
			<?php
				echo validation_errors();
				echo form_open('workflows/selectionform', array('id' => 'subForm', 'name' => 'subForm', 'class' => 'form-horizontal'));
				
				echo form_group_open();
                echo form_label('Workflow: ', 'workflow', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
				foreach ($workflows as $workflow)
			        $items[$workflow['workflow_id']] = $workflow['title'];
                echo form_div_open('col-sm-8');
                echo form_dropdown('workflow', $items, $this->input->post('workflow'), 'class="form-control"');
                echo form_div_close();
                echo form_group_close();
				
				unset($items);
				
				echo form_group_open();
                echo form_label('Position: Before ', 'order', array('class' => 'col-sm-3 col-sm-offset-1 control-label'));
				foreach ($tasks as $task)
			        $items[] = $task['title'];
                echo form_div_open('col-sm-7');
                echo form_dropdown('order', $items, $this->input->post('order'), 'class="form-control"');
                echo form_div_close();
                echo form_group_close();

				echo form_hidden('id', $id);
				echo form_close();
			?>
		</div>
		<div class="modal-footer">
        <?php
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Add workflow', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submitForm(false)'));
        ?>
        </div>
    </div>
</div>

<script src="<?=JS.'modalSubmit.js'?>"></script>