<script src="<?=JS.'assets.js'?>"></script>
<script src="<?=JS.'editAsset.js'?>"></script>

<script type="text/javascript">

    function confDelete() {
        return confirm('Delete whole Shot?\n\n' +
                       'There might be associated\n' +
                       '\tTasks\n' +
                       'which will be deleted too!');
    };
    function confUnassign(firstname, lastname) {
    	return confirm('Do you really want to unassign\n'+
                       '\t'+firstname+' '+lastname+'\n'+
                       'from scene?');
    };

</script>
<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first"><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
		<li><a href="<?=base_url('projects')?>">All Projects</a></li>
		<li><a href="<?=base_url('projects/view/'.$project['project_id'])?>"><?=$project['title']?></a></li>
		<li class="last"><a href=""><?=$scene['title']?></a></li>
	</ul>
</div>

<div class="col_12 column">
	<?php
	if($permissions['edit'])
	{?>
		<a href="<?=base_url('scenes/edit/'.$scene['scene_id'])?>" data-target="#modal" data-toggle="modal" class="edit_scene"><i class="icon-edit"></i>Edit Scene</a><br>
	<?php } ?>
	<div class="col_4 scene_logo column">
				<?= $logo?>
	</div>
	<div class="col_2 scene_details column">
			<legend>INFO</legend>
			<fieldset>
				<div style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;"><h6 class="scene_movietitle"><?=$scene['title']?></h6></div>
				<p>CODE: <?=$shortcode?></p>
				<p style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;">DESCRIPTION: <?=$scene['description']?></p>	
				<p>STATUS:<i id='actual_status_<?=$status['title']?>'><?=$status['status']?></i></p>
				<p align="center"><?=$button?></p>	
				
			</fieldset>
	</div>
	<div class="col_2 scene_details column">
			<legend>DETAILS</legend>
			<fieldset>
				<p>Deadline - <?=$deadline?></p>
				<p>Startdate - <?=$startdate?></p>
				<p>End date - <?=$enddate?></p>	
				<p>Duration - <?=$duration?></p>
				<p>Shots - <?=$shotcount?> [<?=$shotsfinished?> finished]</p>
				<p>Crew - <?=$crewtext?></p>
			</fieldset>
	</div>
	<div class="col_3 right column">
		<h6 class="scene_titleleft">SCENE FILES</h6>
		<?php
			if(isset($addNewFile))
			{
				echo $linkNewFile;
				echo $addNewFile;
			}
		?>
		<?=$scenefiles?>
	</div>
	<div class="col_9 right column">
		<h6 class="scene_titleleft">SHOTS</h6>
		<?php if(isset($addNewShot)) echo $addNewShot?>
		<?=$shottable?>
	</div>
	<div class="col_3 right column">
		<h6 class="scene_titleleft">SCENE MEMBERS</h6>
		<?php if(isset($addSceneSup)) echo $addSceneSup?>
		<?=$usertable?>
	</div>
</div>
