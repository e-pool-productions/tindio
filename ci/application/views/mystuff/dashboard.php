<div class="row">
	<div class="col-md-7">
		<ol class="breadcrumb">
			<li class="active">Home</li>
		</ol>
	</div>
	<div class="col-md-5"><span class="pagetitle pagetitle-big">WELCOME</span></div>
</div>
<div class="row">
	<div class="col-md-3 col-md-offset-1">
		<h4><b>NEWS</b></h4>
		<ul class="list-unstyled" style="white-space:pre-line; word-break: break-all; word-wrap: break-word">
			<?php
				for ($i=0; $i < count($news); $i++) {
					if($news[$i]['event_id'] == LOGTYPE_NEW_PROJECT){
						echo '<li><i class="fa fa-film"></i> New Project: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
					}
					if($news[$i]['event_id'] == LOGTYPE_NEW_USER){
						echo '<li><i class="fa fa-group"></i> New User: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
					}
					if($news[$i]['event_id'] == LOGTYPE_DELETE_USER){
						echo '<li><i class="fa fa-group"></i> User deleted: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
					}
					if($news[$i]['event_id'] == LOGTYPE_DELETE_PROJECT){
						echo '<li><i class="fa fa-film"></i> Project deleted: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
					}
					if($news[$i]['event_id'] == LOGTYPE_FINISH_PROJECT){
						echo '<li><i class="fa fa-film"></i> Project finished: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
					}
				}
			?>
		</ul>
		<legend class="top-buffer" style="text-align: center;">GLOBAL STATS</legend>
		<fieldset>
			<p>PROJECTS - <?=$globalstats['totalprojects']?></p>
			<p>SCENES - <?=$globalstats['totalscenes']?></p>
			<p>SHOTS - <?=$globalstats['totalshots']?></p>	
			<p>TASKS - <?=$globalstats['totaltasks']?></p>
			<p>USERS - <?=$globalstats['totalusers']?></p>
			<p>LAST FINISHED PROJECT - <a href=" <?=$globalstats['lastfinishedproject_link']?> "><?=$globalstats['lastfinishedproject']?></a></p>
			<p>MOST RECENT PROJECT - <a href=" <?=$globalstats['mostrecentproject_link']?> "><?=$globalstats['mostrecentproject']?></a></p>
		</fieldset>
	</div>
	<div class="col-md-4 col-md-offset-2">
		<h4><b>NOTIFICATIONS</b></h4>
		<?php
			if($number_of_new_assignments > 0)
				echo '<div class="alert alert-success"><i class="fa fa-tasks fa-lg"></i> You have '.$number_of_new_assignments.' new tasks assigned!</div>';
			elseif($number_of_new_assignments == 0)
				echo '<div class="alert alert-default"><i class="fa fa-square-o fa-lg"></i>  Nothing new since last login.</div>';
		?>
		<h4><b>MY COMING DEADLINES</b></h4>
		<?=$deadlines?>
	</div>
</div>