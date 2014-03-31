<script type="text/javascript">
    function confDelete() {
        msg = "Are you sure to delete the workflow?";
        return confirm(msg);
    }
    function confDeleteTask(){
    	msg = "Are you sure to delete the task?";
        return confirm(msg);
    }
</script>
<div class="col_11 column">
	<ul class="breadcrumbs">
		<li class="first"><a href="<?php echo base_url('mystuff/dashboard'); ?>">Home</a></li>
		<li><a href>Tools</a></li>
		<li class="last"><a href>Workflow Editor</a></li>
	</ul>
</div>
<div class="workflow">WORKFLOWS</div>
<div class="col_12 column">
	<?= $new_workflow ?>
	<div class="col_12 column" align="center">
		<?php
			foreach($workflows as $workflow_item) {
				echo $workflow_item['title']." ".$workflow_item['options'].br(2); 
				echo $tables[$workflow_item['workflow_id']].br(1);
			}
		?>
	</div>
</div>
