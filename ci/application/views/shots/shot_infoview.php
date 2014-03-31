<script src="<?=JS.'assets.js'?>"></script>
<script src="<?=JS.'editAsset.js'?>"></script>

<script type="text/javascript">

    function confDelete() {
        return confirm( 'Delete whole Task?\n'+
                        'This will unassign all Artists and delete all not used assets!');
    };

    function confUnassign(firstname, lastname) {
    	return confirm('Do you really want to unassign\n'+
                       '\t'+firstname+' '+lastname+'\n'+
                       'from shot?');
    };

</script>
<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first"><a href="<?=base_url('mystuff/dashboard'); ?>">Home</a></li>
		<li><a href="<?=base_url('projects'); ?>">All Projects</a></li>
		<li><a href="<?=base_url('projects/view/'.$project['project_id']);?>"><?=$project['title']?></a></li>
		<li><a href="<?=base_url('scenes/view/'.$scene['scene_id']);?>"><?=$scene['title']?></a></li>
		<li class="last"><a href=""><?=$shot['title']?></a></li>
	</ul>
</div>
<div class="col_12 column">
<?php
if($permissions['edit'])
{?>
<a href="<?=base_url('shots/edit/'.$shot['shot_id']);?>" data-target="#modal" data-toggle="modal" class="edit_shot"><i class="icon-edit"></i>Edit Shot</a><br>
<?php } ?>
	<div class="col_4 shot_logo column">
		<?= $logo?>
		
	</div>
	<div class="col_2 shot_details column">
			<legend>INFO</legend>
			<fieldset>
				<div style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;"><h6 class="shot_movietitle"><?=$shot['title']?></h6></div>
				<p>CODE: <?=$shortcode?></p>
				<p style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;">DESCRIPTION: <?=$shot['description']?></p>	
				<p>STATUS: <i id='actual_status_<?=$status['title']?>'><?=$status['status']?></i></p>
				<p align="center"><?=$button?></p>
				
			</fieldset>
	</div>
	<div class="col_2 shot_details column">
			<legend>DETAILS</legend>
			<fieldset>
				<p>Deadline - <?=$deadline?></p>
				<p>Startdate - <?=$startdate?></p>
				<p>End date - <?=$enddate?></p>	
				<p>Duration - <?=$duration?></p>
				<p>Tasks - <?=$taskcount?> [<?=$tasksfinished?> finished]</p>
				<p>Crew - <?=$crewtext?></p>
			</fieldset>
	</div>
	<div class="col_3 right column">
		<h6 class="shot_titleleft">SHOT FILES</h6>
		<?php
			if(isset($addNewFile))
			{
				echo $linkNewFile;
				echo $addNewFile;
			}
		?>
		<?=$shotfiles?>
	</div>
	<div class="col_9 right column">
		<h6 class="shot_titleleft">TASKS</h6>
		
		<?php 
			if($permission['addWorkflow']) 
				echo '<a href="'.base_url('/workflows/select/'.$shot['shot_id']).'" data-target="#modal" data-toggle="modal" class="button small">Add workflow</a>'; 
		?>
		<?php if(isset($addNewTask)) echo $addNewTask?>
		<?=$tasktable?>
	</div>
	<div class="col_3 right column">
		<h6 class="shot_titleleft">SHOT MEMBERS</h6>
		<?php if(isset($addShotSup)) echo $addShotSup?>
		<?=$usertable?>
	</div>
</div>
