<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first"><a href="<?php echo base_url('mystuff/dashboard'); ?>">Home</a></li>
		<li class="last"><a href>Settings</a></li>
	</ul>
</div>
<div class="setting">SETTINGS</div>
<div class="col_12 column" align="center">
	<?php 
	echo 'Categories '.'<a href="'.base_url('settings/add/category').'" title="Add new category" class="tooltip"><i class="icon-plus"></i></a>'.br(2);
	echo $categories.br(3);
	
	echo 'Skills '.'<a href="'.base_url('settings/add/skill').'" title="Add new skill" class="tooltip"><i class="icon-plus"></i></a>'.br(2);
	echo $skills;
	?>
</div>