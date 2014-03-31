<link rel="stylesheet" type="text/css" href="<?=CSS.'css.css'; ?>">
<script type="text/javascript" src="<?=JS.'modalSubmit.js';?>"></script>
<script type="text/javascript" src="<?=base_url('tinymce/tinymce.min.js')?>"></script>

<script type="text/javascript">
    tinymce.init({
        selector: "textarea.message",
        theme: "modern",
        menubar: false,
        statusbar: false,
        plugins: ["link image preview media emoticons textcolor"],
        toolbar1: "fontsizeselect bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify",
        toolbar2: "link image media emoticons | preview",
    });
</script>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Send your Comment</h4>
        </div>
        <div class="modal-body">
			<?php 
                echo validation_errors();
                echo form_open('tasks/new_comment/' . $task_id . '/true', array('id' => 'subForm', 'name' => 'subForm'));
                echo form_textarea(array('name' => 'message', 'id' => 'message', 'class' => 'message'));
                echo form_reset(array('class' => 'btn', 'value' => 'Reset'));
                echo form_close();
			?>
			</div>
        <div class="modal-footer">
        <?php
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Submit', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'document.subForm.submit()'));
        ?>
        </div>
    </div>
</div> 