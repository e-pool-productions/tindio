<script type="text/javascript" src="<?php echo(JS.'modalSubmit.js'); ?>"></script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Assigning User</h4>
        </div> 
			<?php
				echo form_open('users/recruitform', array('id' => 'subForm', 'name' => 'subForm'));
				?> 
				<div id="recruit_user">
					<?php echo " Assign user: ".form_dropdown('user', $user, $cuser). " to project: "; ?>
				
				<?php
				$pros = array();
				foreach( $projects as $project)
				{
					$pros[$project['project_id']] = $project['title'];
				}
				echo form_dropdown('duty', $pros, '', 'id="selectedId"');
				echo form_close();
			?>
			</div>
        <div class="modal-footer">
            <?php
                echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
                echo form_button(array('content' => 'Assign', 'class'=>'btn btn-primary','type' => 'submit', 'onclick' => 'submit()')) . br(1);
            ?>
        </div>
    </div>
</div>