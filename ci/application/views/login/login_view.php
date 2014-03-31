<html>
    <head>       
        <link rel="stylesheet" type="text/css" href="<?php echo(CSS.'bootstrap.min.css'); ?>">
        <link rel="stylesheet" type="text/css" href="<?php echo(CSS.'css.css'); ?>">
        <script type="text/javascript" src="<?=JS.'md5.js';?>"></script>
    </head>
    <body>
    	<div class="form-signin loginpage">
    		<?php
    			echo form_open('login/form');
				echo "<img src=".base_url('media/system/tindio.png').">";
    			echo "<h1 class='form-signin-heading'>Please sign in</h1>";
				echo form_input(array('type' => 'text', 'name' => 'user', 'id' => 'user', 'value' => set_value('user'), 'class'=>'form-control', 'placeholder'=>'Your username', 'required', 'autofocus'));
				echo form_input(array('type' => 'password', 'name' => 'password', 'id'=>'password', 'class' => 'form-control', 'placeholder'=>'Your password','required'));
				echo validation_errors("<span class='error'>", "</span>");
				echo form_button(array('type' => 'submit', 'onclick'=>'document.getElementById(\'password\').value = hex_md5(document.getElementById(\'password\').value)', 'content'=> 'Sign in', 'class'=> 'btn btn-lg btn-primary btn-block'));
    		?>
    	</div>     
    </body>
</html>