<div class="row">
	<div class="col-md-12">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li><a href="<?=base_url('projects')?>">All Projects</a></li>
			<li><a href="<?=base_url('projects/view/'.$project['project_id'])?>"><?=$project['title']?></a></li>
			<li class="active"><?=$scene['title']?></li>
		</ol>
	</div>
</div>
<div class="row top-buffer-sm">
    <div class="col-md-9">
        <div class="row">
        	<?php
        		$editUrl = base_url('scenes/edit/'.$scene['scene_id']);
				$edit = $permissions['edit'] ? EDIT_ICON : '';
				
				if($permissions['edit'])
				{
					$onclicks = array(	'logo' => "onclick=\"edit(this, '$editUrl/logo')\"",
										'title' => "onclick=\"edit(this, '$editUrl/title')\"",
										'description' => "onclick=\"edit(this, '$editUrl/description')\"",
										'deadline' => "onclick=\"edit(this, '$editUrl/deadline')\"");	
				}
				else
					$onclicks = array('logo' => '', 'title' => '', 'description' => '', 'deadline' => '');
        	?>
        	<div class="col-md-4" <?=$onclicks['logo']?>>
        		<img src="<?=$logo['path']?>" class="img-responsive">
        	</div>
        	<div class="col-md-4">
        		<legend>INFO</legend>
        		<div class="well well-sm">
        			<p class="wordwrap"><b><span <?=$onclicks['title']?>><?=$scene['title']?></span></b> <?=$edit?></p>
        			<p>CODE: <?=$shortcode?></p>
        			<p class="wordwrap">DESCRIPTION: <span <?=$onclicks['description']?>><?=$scene['description']?></span> <?=$edit?></p>	
        			<p>STATUS: <?=$status?></span></p>
        			<p align="center"><?=$button?></p>
        		</div>
        	</div>
        	<div class="col-md-3">
        		<legend>DETAILS</legend>
        		<div class="well well-sm">
        			<p>Deadline - <span <?=$onclicks['deadline']?>><?=$deadline?></span> <?=$edit?></p>
        			<p>Startdate - <?=$startdate?></p>
        			<p>End date - <?=$enddate?></p>	
        			<p>Duration - <?=$duration?></p>
        			<p>Shots - <?=$shotcount?> [<?=$shotsfinished?> finished]</p>
        			<p>Crew - <?=$crewtext?></p>
        		</div>
        	</div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading clearfix">
                        <b class="pull-left" style="padding-top: 5px;">SHOTS</b>
                        <div class="btn-group pull-right">
                            <?php
                                if($permissions['create'])
									echo '<a href="'.base_url('/shots/create/'.$scene['scene_id']).'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-file-o"></i> Add new Shot</a>';
                            ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <?=$shottable?>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	<div class="col-md-3">
		<div class="panel panel-default">
			<div class="panel-heading clearfix">
				<b class=" pull-left" style="padding-top: 5px;">SCENE FILES</b>
				<div class="btn-group pull-right">
					<?php
						if($permissions['create'])
							echo 	'<a href="'.base_url('/all_assets/link_asset/scene/'.$scene['scene_id']).'" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a>
									 <a href="'.base_url('/upload/choose_files/scene_'.$scene['scene_id'].'/true').'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-external-link-square"></i></a>
									 <a href="'.base_url('/upload/choose_files/scene_'.$scene['scene_id']).'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-upload"></i></a>';
					?>
				</div>
			</div>
			<?=$scenefiles?>
		</div>
        <div class="panel panel-default top-buffer">
            <div class="panel-heading clearfix">
                <b class="pull-left" style="padding-top: 5px;">SCENE MEMBERS</b>
                <div class="btn-group pull-right">
                    <?php
                    	if($permissions['create'])
                    		echo '<a href="'.base_url('/users/show/scene/'.$scene['scene_id']).'"; class="btn btn-default btn-sm"><i class="fa fa-user"></i> Add Scene Sup</a>';
                    ?>
                </div>
            </div>
            <?=$usertable?>
        </div>
	</div>
</div>

<script src="<?=JS.'edit.js'?>"></script>
<script src="<?=JS.'confirm.js'?>"></script>
<script type="text/javascript">
    $(window).load(function() {
	    var maxOrderposition = <?=$maxOrderposition?>;
	    posDrop = document.createElement('select');
	    
	    for(var i = 1; i <= maxOrderposition; i++)
	    {
	    	var opt = new Option();
	    	opt.value = i;
	    	opt.text = i;
	    	posDrop.options.add(opt);
	    }
	    
	    var logos = <?=json_encode($logos);?>;
	    logoDrop = document.createElement('select');
	    
	    logos.forEach(function(logo){
	    	var opt = new Option();
	    	opt.value = logo['asset_id'];
	    	opt.text = logo['title'];
	    	logoDrop.options.add(opt);
	    });
	});

</script>