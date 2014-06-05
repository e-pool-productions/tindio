<div class="row">
	<div class="col-md-7">
		<ol class="breadcrumb">
			<li><a href="<?=base_url('mystuff/dashboard')?>">Home</a></li>
			<li class="active">Settings</li>
		</ol>
	</div>
	<div class="col-md-5"><span class="pagetitle pagetitle-big">SETTINGS</span></div>
</div>
<div class="row">
	<div class="col-md-4 col-md-offset-4 top-buffer">
		<div class="panel panel-default">
			<div class="panel-heading clearfix">
				<b class="pull-left" style="padding-top: 5px;">Categories</b>
				<div class="btn-group pull-right">
					<?php if($isAdmin) echo '<a href="'.base_url('settings/add/category').'" title="Add new category"><i class="fa fa-plus"></i></a>'; ?>
				</div>
			</div>
			<?=$categories?>
		</div>
		<div class="panel panel-default top-buffer">
			<div class="panel-heading clearfix">
				<b class="pull-left" style="padding-top: 5px;">Skills</b>
				<div class="btn-group pull-right">
					<?php if($isAdmin) echo '<a href="'.base_url('settings/add/skill').'" title="Add new skill"><i class="fa fa-plus"></i></a>'; ?>
				</div>
			</div>
			<?=$skills?>
		</div>
	</div>
</div>
<script src="<?=JS.'edit.js'?>"></script>