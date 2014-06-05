<div class="row">
	<div class="col-md-6">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li>Tools</li>
			<li class="active">Workflow Editor</li>
		</ol>
	</div>
	<div class="col-md-6"><span class="pagetitle">WORKFLOWS</span></div>
</div>
<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<?php
			if($canCreate)
				echo '<a href="'.base_url('workflows/create').'" data-target="#modal" data-toggle="modal" class="btn btn-default btn-sm pull-right"><i class="fa fa-star"></i> Add new workflow</a>';
		?>
	</div>
</div>
<div class="row top-buffer-sm">
	<div class="col-md-5 col-md-offset-1">
		<?php
			for($i = 0; $i < ceil($count / 2); $i++) {
				 $workflow = $workflows[$i];
		?>
			<div class="panel panel-default">
				<div class="panel-heading clearfix">
					<b class=" pull-left" style="padding-top: 5px;"><span onclick="edit(this, '<?=base_url('workflows/changeTitle/'.$workflow['workflow_id'].'/title')?>')"><?=$workflow['title']?></span> <i class="fa fa-pencil"></i></b>
					<div class="btn-group pull-right">
						<?php if(isset($workflow['options'])) echo $workflow['options']?>
					</div>
				</div>
				<?=$tables[$workflow['workflow_id']]?>
			</div>
		<?php
			}
		?>
	</div>
	<div class="col-md-5">
		<?php
			for($i = ceil($count / 2); $i < $count; $i++) {
				 $workflow = $workflows[$i];
		?>
			<div class="panel panel-default">
				<div class="panel-heading clearfix">
					<b class=" pull-left" style="padding-top: 5px;"><span onclick="edit(this, '<?=base_url('workflows/changeTitle/'.$workflow['workflow_id'].'/title')?>')"><?=$workflow['title']?></span> <i class="fa fa-pencil"></i></b>
					<div class="btn-group pull-right">
						<?php if(isset($workflow['options'])) echo $workflow['options']?>
					</div>
				</div>
				<?=$tables[$workflow['workflow_id']]?>
			</div>
		<?php
			}
		?>
	</div>
</div>

<script src="<?=JS.'edit.js'?>"></script>
<script src="<?=JS.'confirm.js'?>"></script>