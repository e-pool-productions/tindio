<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first"><a href="<?php echo base_url('mystuff/dashboard'); ?>">Home</a></li>
		<li><a href>Workflow</a></li>
		<li class="last"><a href>Workflow...</a></li>
	</ul>
</div>
<div class="col_12 column">
	<div class="outer">
	    <div class="inner">
	        <div id="stylized" class="myform">	
				<?php
				foreach($workflows as $workflow_item)
				{
					echo $workflow_item['title']. '  <a href="" title="edit2 workflow" class="tooltip"><i class="icon-pencil"></i></a>'.'  <a href="" title="delete workflow" class="tooltip"><i class="icon-cross"></i></a>'.br(2);
					echo $tables[$workflow_item['workflow_id']].br(1);
				}
				?>
			 </div>
		</div>
	</div>  
</div>