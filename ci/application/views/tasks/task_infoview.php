<script type="text/javascript" src="<?=JS.'assets.js'?>"></script>
<script src="<?=JS.'editAsset.js'?>"></script>

<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first"><a href="<?php echo base_url('mystuff/dashboard'); ?>">Home</a></li>
		<li><a href="<?php echo base_url('projects'); ?>">All Projects</a></li>
		<li><a href="<?php echo base_url('projects/view/'.$project['project_id']);?>"><?=$project['title']?></a></li>
		<li><a href="<?php echo base_url('scenes/view/'.$scene['scene_id']);?>"><?=$scene['title']?></a></li>
		<li><a href="<?php echo base_url('shots/view/'.$shot['shot_id']);?>"><?=$shot['title']?></a></li>
		<li class="last"><a href=""><?=$task['title']?></a></li>
	</ul>
</div>
<div class="col_12 column">
	<?php
		if($permissions['edit'])
		{?>
 			 <a href="<?=base_url('tasks/edit/'.$task['task_id'])?>" data-target="#modal" data-toggle="modal" class="edit_task"><i class="icon-edit"></i>Edit Task</a><br>
	<?php } ?>
	<div class="col_3  task_details column">
			<legend>INFO</legend>
			<fieldset>
				<div style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;"><h6 class="task_movietitle"><?=$task['title']?></h6></div>
				<div style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;"><p>CODE: <?=$shortcode?></p></div>
				<p style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word;">DESCRIPTION: <?=$task['description']?></p>
				<p>ASSIGNED TO: <?=$artist_string?></p>
				<?php
					if($permissions['recruit'])
						echo '<p align=\'center\'><a href="'. base_url('users/show/task/'.$task['task_id']) .'" class="button small task"><i class="icon-user"></i> Recruit new User</a></p>';
				?>
			</fieldset>
	</div>
	
	<div class="col_2 task_details column">
		<legend>DETAILS</legend>
		<fieldset>
			<p>DEADLINE - <?=$deadline?></p>
			<p>START DATE - <?=$startdate?></p>
			<p>END DATE - <?=$enddate?></p>
			<p>DURATION - <?=$duration?></p>
		</fieldset>
	</div>
	
	<div class="col_2 task_details column">			
		<legend>STATUS</legend>
		<fieldset class="task_status_<?=$status['title']?>">
			<h6 id='actual_status'><?=$status['status']?></h6>		
			<p align="center"><?=$button?></p>
		</fieldset>							

	</div>
	<div class="col_3 right column">
		<h6 class="task_titleleft">OUTPUT FILES</h6>
		<?php if($permissions['upload']) 
		{ ?>
			<a href="<?=base_url('/all_assets/link_asset/task_'.$task['task_id']);?>" class="button small"><i class="icon-link"></i></a>
			<a href="<?=base_url('/upload/choose_files/task_'.$task['task_id']);?>" data-target="#modal" data-toggle="modal" class="button small"><i class="icon-upload-alt"></i></a>
		<?php } ?>
		<?=$outputfiles?>	
	</div>	
	
</div>

<div class="col-md-8 column comments">
	<?php if($permissions['comment'])
    	echo '<a href="'.base_url('tasks/new_comment/'.$task['task_id']).'" data-target="'.'#modal" data-toggle="modal" class="button small"><i class="icon-comment"></i> Leave a Comment</a>';
    ?>
    <div id="comments">
        <?php for ($i = count($comments) - 1; $i >= 0; $i--): ?>
            <blockquote>
                <?=$comments[$i]['message']?>
                <small><?php echo $comments[$i]['username'] . ', ' . $comments[$i]['timestamp']?></small>
            </blockquote>
        <?php endfor ?>
    </div>
</div>

<div class="col_3 right column">
	<h6 class="task_titleleft">LOCAL FILES</h6>
	<?php if($permissions['upload'])
	{?>
		<a href="<?=base_url('/upload/choose_files/task_'.$task['task_id'].'_local');?>" data-target="#modal" data-toggle="modal" class="button small"><i class="icon-upload-alt"></i></a>
	<?php } ?>
	<?=$localfiles?>	
</div>

<script>
    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".mce-window").length) {
            e.stopImmediatePropagation();
        }
    });
</script>
