<div class="col-md-2 col-md-offset-5 top-buffer">
	<?php
		echo form_open('login/form', array('class' => 'top-buffer'));
		echo '<img src="'.base_url('media/system/tindio.png').'" class="img-responsive">';
		echo '<h3>Please sign in</h3>';
		echo form_input(array('type' => 'text', 'name' => 'user', 'id' => 'user', 'value' => set_value('user'), 'class'=>'form-control', 'placeholder'=>'Your username', 'required', 'autofocus'));
		echo form_input(array('type' => 'password', 'name' => 'password', 'id'=>'password', 'class' => 'form-control', 'placeholder'=>'Your password','required'));
		echo validation_errors("<span class='error'>", "</span>");
		echo form_button(array('type' => 'submit', 'onclick' => "$('#password').val(hex_md5($('#password').val()));", 'content'=> 'Sign in', 'class'=> 'btn btn-lg btn-primary btn-block top-buffer-sm'));
	?>
</div>
<script type="text/javascript" src="<?=JS.'md5.js';?>"></script>