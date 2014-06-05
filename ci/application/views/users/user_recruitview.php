<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Assigning <?=$cuser?></h4>
        </div>
        <div class="modal-body">
			<?php
				echo form_open('users/recruitform', array('id' => 'subForm', 'name' => 'subForm', 'class' => 'form-horizontal'));
				
				echo form_div_open(NULL, 'recruit_user');
				
				echo form_group_open();
                echo form_label('to Project: ', 'projects', array('class' => 'col-sm-3 col-sm-offset-1 control-label'));
				foreach ($projects as $project)
                    $items[$project['project_id']] = $project['title'];
                echo form_div_open('col-sm-7');
                echo form_dropdown('projects', $items, $this->input->post('projects'), 'class="form-control"');
                echo form_div_close();
                echo form_group_close();

				echo form_hidden('user', $cuser);
				echo form_close();
			?>
			</div>
		</div>
        <div class="modal-footer">
            <?php
                echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
                echo form_button(array('content' => 'Assign', 'class'=>'btn btn-primary','type' => 'submit', 'onclick' => 'submitForm(true)'));
            ?>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo(JS.'formSubmit.js'); ?>"></script>