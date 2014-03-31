<div class="grid flex">
	<div class="col_11 column">
		<ul class="breadcrumbs">
			<li class="first last">
				<a href>Home</a>
			</li>
		</ul>
	</div>
	<div class="col_12 column">

		<div class="dashboard_pagetitle">WELCOME</div>

		<div class="col_4 dashboard_log">
			<h6 class="dashboard_titleleft">NEWS</h6>
			<ul class="icons" style="white-space:pre-wrap; word-break: break-all; word-wrap: break-word">
				<?php
					for ($i=0; $i < count($news); $i++) {
						if($news[$i]['event_id'] == LOGTYPE_NEW_PROJECT){
							echo '<li><i class="icon-film"></i> New Project: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
						}
						if($news[$i]['event_id'] == LOGTYPE_NEW_USER){
							echo '<li><i class="icon-group"></i> New User: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
						}
						if($news[$i]['event_id'] == LOGTYPE_DELETE_USER){
							echo '<li><i class="icon-group"></i> User deleted: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
						}
						if($news[$i]['event_id'] == LOGTYPE_DELETE_PROJECT){
							echo '<li><i class="icon-film"></i> Project deleted: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
						}
						if($news[$i]['event_id'] == LOGTYPE_FINISH_PROJECT){
							echo '<li><i class="icon-film"></i> Project finished: '.$news[$i]['link'].$news[$i]['name'].'<span class="dashboard_log_date"> | '.$news[$i]['date'].'</span></li>';
						}
					}
				?>
			</ul>		
		</div>

		<div class="col_6 column">
			<h6 class="dashboard_titleleft">MY COMING DEADLINES</h6>
			<table class="dashboard_tasks striped tight sortable">
				<?=$deadlines?>	<!-- table containing the user's upcoming deadlines -->
			</table>
		</div>

		<div class="col_5 column">
			<h6 class="dashboard_titleleft">NOTIFICATIONS</h6>
			<?php
				if($number_of_new_assignments > 0) {
					echo '<div class="notice tasks"><i class="icon-tasks icon-large"></i>  You have '.$number_of_new_assignments.' new tasks assigned!</div>';
				}
				if($number_of_new_assignments == 0) {
					echo '<div class="notice nothingnew"><i class="icon-check-empty icon-large"></i>  Nothing new since last login.</div>';
				}
			?>
			
			<div class="col_4 column"> </div>

			<div class="col_8 column">
				<fieldset class="dashboard_statdetails">
					<legend style="text-align: center;">GLOBAL STATS</legend>
						<p>PROJECTS - <?=$globalstats['totalprojects']?></p>
						<p>SCENES - <?=$globalstats['totalscenes']?></p>
						<p>SHOTS - <?=$globalstats['totalshots']?></p>	
						<p>TASKS - <?=$globalstats['totaltasks']?></p>
						<p>USERS - <?=$globalstats['totalusers']?></p>
						<p>LAST FINISHED PROJECT - <a href=" <?=$globalstats['lastfinishedproject_link']?> "><?=$globalstats['lastfinishedproject']?></a></p>
						<p>MOST RECENT PROJECT - <a href=" <?=$globalstats['mostrecentproject_link']?> "><?=$globalstats['mostrecentproject']?></a></p>
				</fieldset>
			</div>
		</div>
	</div>
</div>