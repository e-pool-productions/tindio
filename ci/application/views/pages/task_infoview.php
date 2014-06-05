<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li><a href="<?=base_url('projects'); ?>">All Projects</a></li>
			<li><a href="<?=base_url('projects/view/'.$project['project_id'])?>"><?=$project['title']?></a></li>
			<li><a href="<?=base_url('scenes/view/'.$scene['scene_id'])?>"><?=$scene['title']?></a></li>
			<li><a href="<?=base_url('shots/view/'.$shot['shot_id'])?>"><?=$shot['title']?></a></li>
			<li class="active"><?=$task['title']?></li>
		</ol>
	</div>
</div>
<div class="row top-buffer-sm">
    <div class="col-md-9">
        <div class="row">
        	<?php
        		$editUrl = base_url('tasks/edit/'.$task['task_id']);
				$edit = $permissions['edit'] ? EDIT_ICON : '';
				
				if($permissions['edit'])
				{
					$onclicks = array(	"onclick=\"edit(this, '$editUrl/title')\"",
										"onclick=\"edit(this, '$editUrl/description')\"",
										"onclick=\"edit(this, '$editUrl/deadline')\"");	
				}
				else
					$onclicks = array_fill(0, 4, '');
        	?>
        	<div class="col-md-4">
        		<legend>INFO</legend>
        		<div class="well well-sm">
        			<p class="wordwrap"><b><span <?=$onclicks[0]?>><?=$task['title']?></b></span> <?=$edit?></p>
        			<p class="wordwrap">CODE: <?=$shortcode?></p>
        			<p class="wordwrap">DESCRIPTION: <span <?=$onclicks[1]?>></span> <?=$edit?></p>
        			<p>ASSIGNED TO: <?=$artist_string?></p>
        			<?php
        				if($permissions['recruit'])
        					echo '<p align=\'center\'><a href="'. base_url('users/show/task/'.$task['task_id']) .'" class="btn btn-default btn-sm"><i class="fa fa-user"></i> Recruit new User</a></p>';
        			?>
        		</div>
        	</div>
        	
        	<div class="col-md-3">
        		<legend>DETAILS</legend>
        		<div class="well well-sm">
        			<p>DEADLINE - <span <?=$onclicks[2]?>><?=$deadline?></span> <?=$edit?></p>
        			<p>START DATE - <?=$startdate?></p>
        			<p>END DATE - <?=$enddate?></p>
        			<p>DURATION - <?=$duration?></p>
        		</div>
        	</div>
        	
        	<div class="col-md-3">
        		<legend>STATUS</legend>
        		<div class="well well-sm">
        			<h4 class="text-center"><?=$status?></h4>
        			<p align="center"><?=$button?></p>
        		</div>
        	</div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-primary">
                    <div class="panel-heading clearfix">
                        <b class="pull-left" style="padding-top: 5px;">COMMENTS</b>
                        <div class="btn-group pull-right">
                            <?php if($permissions['comment'])
                                echo '<a href="'.base_url('tasks/new_comment/'.$task['task_id']).'" data-target="'.'#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-comment-o"></i> Leave a Comment</a>';
                            ?>
                        </div>
                    </div>
                    <div id="comments">
                        <?php for ($i = count($comments) - 1; $i >= 0; $i--): ?>
                            <blockquote>
                                <?=$comments[$i]['message']?>
                                <small><?php echo $comments[$i]['username'] . ', ' . $comments[$i]['timestamp']?></small>
                            </blockquote>
                        <?php endfor ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	<div class="col-md-3">
		<div class="panel panel-default">
			<div class="panel-heading clearfix">
				<b class="pull-left" style="padding-top: 5px;">OUTPUT FILES</b>
				<div class="btn-group pull-right">
					<?php 
						if($permissions['upload']) 
							echo 	'<a href="'.base_url('/all_assets/link_asset/task/'.$task['task_id']).'" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a>
									 <a href="'.base_url('/upload/choose_files/task_'.$task['task_id'].'/true').'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-external-link-square"></i></a>
									 <a href="'.base_url('/upload/choose_files/task_'.$task['task_id']).'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-upload"></i></a>';
					?>
				</div>
			</div>
			<?=$outputfiles?>
		</div>
		<div class="panel panel-default top-buffer">
    		<div class="panel-heading clearfix">
                <b class="pull-left" style="padding-top: 5px;">LOCAL FILES</b>
                <div class="btn-group pull-right">
                    <?php 
                        if($permissions['upload'])
                            echo '<a href="'.base_url('/upload/choose_files/task/'.$task['task_id'].'_local').'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-upload"></i></a>';
                    ?>
                </div>
            </div>
            <?=$localfiles?>
        </div>
	</div>
</div>

<script src="<?=JS.'edit.js'?>"></script>
<script src="<?=JS.'confirm.js'?>"></script>
<script>
    $(document).on('focusin', function(e) {
        if ($(e.target).closest(".mce-window").length) {
            e.stopImmediatePropagation();
        }
    });
</script>