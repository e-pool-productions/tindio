<div class="row">
	<div class="col-md-5">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li class="active">Profile</li>
		</ol>
	</div>
	<div class="col-md-7"><span class="pagetitle pagetitle-big">PROFILE</span></div>
</div>
<div class="row">
	<div class="col-md-6 col-md-offset-3">
		<div class="panel panel-default top-buffer">
            <div class="panel-heading clearfix">
				<b class="pull-left" style="padding-top: 5px;">Profile of <?=$user['username']?> <?php if($ownProfile) echo '<i class="fa fa-pencil" title="Editable"></i>'?></b>
				<?php if($canRecruit) { ?>
					<div class="btn-group pull-right">
						<a href="<?=base_url('users/recruit/'.$user['username'])?>" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm">Recruit</a>
					</div>
				<?php } ?>
			</div>
			<table class="table table-bordered">
				<?php
					$editUrl = base_url('users/edit/'.$user['username']);
					if($ownProfile)
					{
						$onclicks = array(	"onclick=\"edit(this, '$editUrl/username')\"",
											"onclick=\"edit(this, '$editUrl/firstname')\"",
											"onclick=\"edit(this, '$editUrl/lastname')\"",
											"onclick=\"edit(this, '$editUrl/mail')\"",
											"onclick=\"edit(this, '$editUrl/gravatar_email')\"",
											"onclick=\"edit(this, '$editUrl/timezone')\"");	
					}
					else
						$onclicks = array_fill(0, 6, '');
					
				?>
				<tr>
			        <th scope="row">Username</th>
					<td <?=$onclicks[0]?>><?=$user['username']?></td>
			    </tr>
			    <tr>
					<th scope="row">First Name</th>
					<td <?=$onclicks[1]?>><?=$user['firstname']?></td>
			    </tr>
			    <tr>
			        <th scope="row">Last Name</th>
			        <td <?=$onclicks[2]?>><?=$user['lastname']?></td>
			    </tr>
			    <?php
			    	if($ownProfile)
					{
				?>
						<tr>
			        		<th scope="row">E-Mail</th>
			        		<td <?=$onclicks[3]?>><?=$user['mail']?></td>
			    		</tr>
			    		<tr>
			        		<th scope="row">Gravatar</th>
			        		<td <?=$onclicks[4]?>><?=$user['gravatar_email']?></td>
			    		</tr>
			    		<tr>
			        		<th scope="row">Timezone</th>
			        		<td <?=$onclicks[5]?>><?=$user['timezone']?></td>
			    		</tr>
			    <?php
					}
			    ?>
			    <tr>
			        <th scope="row">Skills</th>
			        <td>
			        <?php
			        	foreach ($ownSkills as $skill)
						{
							echo $ownProfile ?
								'<a href="'.base_url('users/edit/'.$user['username'].'/removeSkill/'.$skill['skill_id']).'"><i class="fa fa-minus"></i> '.$skill['title'].'</a>' :
								$skill['title'];
							echo br(1);
						}
							
			        	if(!$hasAllSkills && $ownProfile)
			        		echo '<span class="hand" onclick="edit(this, \''.$editUrl.'/addSkill\')"><i class="fa fa-plus"></i> </span>';
			        ?>
			        </td>
			    </tr>
			    <tr>
			        <th scope="row">Deadlines</th>
			        <td>
			        <?php
						foreach($deadlines as $deadline)
							echo $deadline['deadline'].' ('.$deadline['time_left'].')'. ' for '.$deadline['task_title'];
					?>
			        </td>
			    </tr>		    
			</table>
		</div>
		<?php
			if($ownProfile)
			{
		?>
			<div class="panel panel-default top-buffer">
	            <div class="panel-heading clearfix">
					<b class="pull-left" style="padding-top: 5px;">Change Password</b>
				</div>
				<?php
					echo validation_errors(); 
	                echo form_open('users/edit/'.$user['username'].'/password', array('id' => 'subForm', 'name' => 'subForm', 'class' => 'form-horizontal top-buffer-sm'));
	                
	                echo form_group_open();
					echo form_error('curpass');
	                echo form_label('Current Password: ', 'curpass', array('class' => 'col-md-3 control-label'));
	                echo form_div_open('col-md-8');
	                echo form_password(array('name' => 'curpass', 'size' => '32', 'maxlength' => '32', 'class' => 'form-control'));
	                echo form_div_close();
					echo form_group_close();
					
					echo form_group_open();
					echo form_label('New Password: <i class="fa fa-info-circle" title="The length of your passwort has to be at least 6 letters; it also has to contain at least one capital letter and one number"></i>', 'newpass', array('class' => 'col-md-3 control-label'));
					echo form_div_open('col-md-8');
	                echo form_password(array('name' => 'newValue', 'size' => '32', 'maxlength' => '32', 'class' => 'form-control'));
	                echo form_div_close();
					echo form_group_close();
					
					echo form_group_open();
					echo form_label('Confirm Password: ' , 'newpassconf', array('class' => 'col-md-3 control-label'));
					echo form_div_open('col-md-8');
	                echo form_password(array('name' => 'newpassconf', 'size' => '32', 'maxlength' => '32', 'class' => 'form-control'));
	                echo form_div_close();
	                echo form_group_close();
					
					echo form_group_open();
					echo form_div_open('col-md-11');
					echo form_button(array('content' => 'Save', 'class' => 'btn btn-default pull-right', 'onclick' => 'submitForm(false)'));
	                echo form_div_close();
					echo form_group_close();
	
					echo form_close();
				?>
			</div>
		<?php
			}
		?>
	</div>
</div>
<script src="<?=JS.'edit.js'?>"></script>
<script>
	var times;
	$(window).load(function() {
        times = $.parseJSON(JSON.stringify(<?=json_encode(timezone_menu($user['timezone']));?>));
        
        var index = times.indexOf('name');
        var name = times.substring(index, index + 16);  // name="timezones" = 16
        times = [times.slice(0, index), 'id="newValue" name="newValue" ', times.slice(index + 16)].join('');

        var skills = <?=json_encode($otherSkills)?>;
        skillDrop = document.createElement('select');
        
        skills.forEach(function(skill){
            var opt = new Option();
            opt.value = skill['skill_id'];
            opt.text = skill['title'];
            skillDrop.options.add(opt);
        });
	});
</script>