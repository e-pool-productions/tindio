<script src="<?=JS.'assets.js'?>"></script>

<script type="text/javascript">
    function confDelete() {
        return confirm('Delete whole Scene?\n\n' +
                       'There might be associated\n' +
                       '\tShots\n' +
                       '\tTasks\n' +
                       'which will be deleted too!');
    };
    
    function confUnassign(firstname, lastname) {
    	return confirm('Do you really want to unassign\n'+
    	               '\t'+firstname+' '+lastname+'\n'+
    	               'from project?');
    };
</script>

<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first"><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
		<li><a href="<?=base_url('projects')?>">All Projects</a></li>
		<li class="last"><a href=""><?=$project['title']?></a></li>
	</ul>
</div>
<div class="col_12 column">
	<?php
		if($permissions['edit'])
		{?>
   			 <a href="<?=base_url('projects/edit/' . $project['project_id'])?>" data-target="#modal" data-toggle="modal" class="tooltip"><i class="icon-edit"></i>Edit Project</a><br>
		<?php } ?>
	<div class="col_4 project_logo column">
		<?=$logo?>
	</div>
	<div class="col_2 project_details column">
			<legend>INFO</legend>
			<fieldset>
				<div style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;"><h6 class="project_movietitle"><?=$project['title']?></h6></div>
				<p>CODE: <?=$project['shortcode']?></p>
				<p>DIRECTOR: <?=$directors?></a></p>
				<div style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;"><p>DESCRIPTION: <?=$project['description']?></p></div>	
				<p>STATUS: <i id='actual_status_<?=$status['title']?>'><?=$status['status']?></i></p>
				<p align="center"><?=$button?></p>
			</fieldset>
	</div>
	<div class="col_2 project_details column">
			<legend>DETAILS</legend>
			<fieldset>
				<p>Deadline  - <?=$deadline?></p>
				<p>Startdate - <?=$startdate?></p>
				<p>End date - <?=$enddate?></p>	
				<p>Duration - <?=$duration?></p>
				<p>Scenes - <?=$scenecount?> [<?=$scenesfinished?> finished]</p>
				<p>Shots - <?=$shotcount?> [<?=$shotsfinished?> finished]</p>
				<p>Crew - <?=$crewtext?></p>
			</fieldset>
	</div>
	<div class="col_3 right column">
		<h6 class="project_titleleft">PROJECT FILES</h6>
		<?php
			if(isset($addNewFile))
			{
				echo $linkNewFile;
				echo $addNewFile;
			}
			echo $projectfiles;
		?>
	</div>
	<div class="col_9 right column">
		<h6 class="project_titleleft">SCENES</h6>
		<?php
			if(isset($addNewScene))
				echo $addNewScene;
			echo $scenetable;
		?>
	</div>
	<div class="col_3 right column">
		<h6 class="project_titleleft">PROJECT MEMBERS</h6>
		<?php
			if(isset($addObserver))
				echo $addObserver;
			echo $usertable;
		?>
	</div>
</div>
