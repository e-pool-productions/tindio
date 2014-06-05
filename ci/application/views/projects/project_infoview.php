<div class="row">
	<div class="col-md-7">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li><a href="<?=base_url('projects')?>">All Projects</a></li>
			<li class="active"><?=$project['title']?></li>
		</ol>
	</div>
</div>
<div class="row top-buffer-sm">
    <div class="col-md-9">
        <div class="row">
        	<?php
        		$editUrl = base_url('projects/edit/'.$project['project_id']);
				$edit = $permissions['edit'] ? EDIT_ICON : '';
				
				if($permissions['edit'])
				{
					$onclicks = array(	'logo' => "onclick=\"edit(this, '$editUrl/logo')\"",
										'title' => "onclick=\"edit(this, '$editUrl/title')\"",
										'shortcode' => "onclick=\"edit(this, '$editUrl/shortcode')\"",
										'category' => "onclick=\"edit(this, '$editUrl/category_id')\"",
										'description' => "onclick=\"edit(this, '$editUrl/description')\"",
										'deadline' => "onclick=\"edit(this, '$editUrl/deadline')\"");
										
				}
				else
					$onclicks = array('logo' => '', 'title' => '', 'shortcode' => '', 'category' => '', 'description' => '', 'deadline' => '');
        	?>
            <div class="col-md-4" <?=$onclicks['logo']?>>
            	<img src="<?=$logo['path']?>" class="img-responsive">
            </div>
            <div class="col-md-4">
                <legend>INFO <?=$edit?></legend>
                <div class="well well-sm">
                	<p class="wordwrap"><b><span <?=$onclicks['title']?>><?=$project['title']?></span></b></p>
                    <p>CODE: <span <?=$onclicks['shortcode']?>><?=$project['shortcode']?></span></p>
                    <p>CATEGORY: <span <?=$onclicks['category']?>><?=$category['title']?></span></p>
                    <p>DIRECTOR: <?=$directors?></p>
                    <p class="wordwrap">DESCRIPTION: <span <?=$onclicks['description']?>><?=$project['description']?></span></p>    
                    <p>STATUS: <?=$status?></p>
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
                    <p>Scenes - <?=$scenecount?> [<?=$scenesfinished?> finished]</p>
                    <p>Shots - <?=$shotcount?> [<?=$shotsfinished?> finished]</p>
                    <p>Crew - <?=$crewtext?></p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading clearfix">
                        <b class="pull-left" style="padding-top: 5px;">SCENES</b>
                        <div class="btn-group pull-right">
                            <?php
		                    	if($isAdmin || $isDirector)
		                    		echo '<a href="'.base_url('/scenes/create/'.$project['project_id']).'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-file-o"></i> Add new Scene</a>';
		                    ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <?=$scenetable?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                <b class="pull-left" style="padding-top: 5px;">PROJECT FILES</b>
                <div class="btn-group pull-right">
                    <?php
                        if($isAdmin || $isDirector)
                            echo 	'<a href="'.base_url('/all_assets/link_asset/project/'.$project['project_id']).'" class="btn btn-default btn-sm"><i class="fa fa-link"></i></a>
                            		 <a href="'.base_url('/upload/choose_files/project_'.$project['project_id'].'/true').'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-external-link-square"></i></a>
                            		 <a href="'.base_url('/upload/choose_files/project_'.$project['project_id']).'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm"><i class="fa fa-upload"></i></a>';
                    ?>
                </div>
            </div>
            <?=$projectfiles?>
        </div>
        <div class="panel panel-default top-buffer">
            <div class="panel-heading clearfix">
                <b class="pull-left" style="padding-top: 5px;">PROJECT MEMBERS</b>
                <div class="btn-group pull-right">
                    <?php
                    	if($isAdmin || $isDirector)
                    		echo '<a href="'.base_url('users/show/project/'.$project['project_id']).'"; class="btn btn-default btn-sm"><i class="fa fa-user"></i> Add Member</a>';
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
        var categories = <?=json_encode($categories);?>;
        cateDrop = document.createElement('select');
        
        categories.forEach(function(category){
            var opt = new Option();
            opt.value = category['category_id'];
            opt.text = category['title'];
            cateDrop.options.add(opt);
        });
        
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