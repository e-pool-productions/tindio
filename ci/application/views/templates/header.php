<nav class="navbar-wrapper navbar-default" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a href="<?=base_url('mystuff/dashboard')?>" class="navbar-brand"><img id="logo" src="<?=MEDIA.'system/tindio.png'?>" style="max-height: 25px;"></a>
		</div>

	    <div class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li><a href="<?=base_url('mystuff/dashboard');?>"><i class="fa fa-tachometer"></i> Dashboard</a></li>
		        <li class="dropdown">
		        	<a href="<?=base_url('mystuff/work');?>"><i class="fa fa-tasks"></i> My Stuff<b class="caret"></b></a>
		        	<ul class="dropdown-menu">
		        		<li><a href="<?=base_url('projects/filterform/myProjects');?>">My Projects</a></li>
		        		<li><a href="<?=base_url('all_assets/filter/myAssets')?>">My Assets</a></li>
		        		<li><a href="<?=base_url('mystuff/work');?>">My Work</a></li>
		        	</ul>
		        </li>
				<?php
		            if($isAdmin)
		                echo '<li class="dropdown">
		                		<a href="'.base_url('projects').'"><i class="fa fa-film"></i> Projects<b class="caret"></b></a>
		                        <ul class="dropdown-menu">
		                            <li><a href="'.base_url('projects/create').'" data-target="#modal" data-toggle="modal">New Project</a></li>
		                        </ul>
		                      </li>';
		            else
		                echo '<li><a href="'.base_url('projects').'"><i class="fa fa-film"></i> Projects</a>';
		        ?>
		        <li><a href="<?=base_url('all_assets');?>"><i class="fa fa-picture-o"></i> Global Assets</a></li>
		        <?php
		            if($isAdmin || $isDirector)
		                echo '<li class="dropdown">
		                		<a href="'.base_url('users').'"><i class="fa fa-users"></i> Users<b class="caret"></b></a>
		                        <ul class="dropdown-menu">
		                            <li><a href="'.base_url('users/create').'" data-target="#modal" data-toggle="modal">New User</a></li>
		                        </ul>
		                      </li>';
		            else
		                echo '<li><a href="'.base_url('users').'"><i class="fa fa-group"></i> Users</a>';
		        ?>
		        <li class="dropdown">
		        	<a href="#"><i class="fa fa-wrench"></i> Tools<b class="caret"></b></a>
		            <ul class="dropdown-menu">
		                <li><a href="<?=base_url('workflows');?>">Workflow Editor</a></li>
		                <li><a href="<?=base_url('mystuff/calendar');?>">Calendar</a></li>
		            </ul>
		        </li>
		        <?php
		            if($isAdmin)
		                echo '<li class="dropdown">
		                		<a href="#"><i class="fa fa-cog"></i> Admin<b class="caret"></b></a>
		                        <ul class="dropdown-menu">
		                            <li><a href="'.base_url('settings').'">Settings</a></li>
		                        </ul>
		                      </li>';
		       ?>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown">
					<a href="#" class="navbar-brand"><b class="caret"></b> <img src="<?=$gravatar_url?>" id="menu_smallavatar" style="max-height: 25px;"></a>
					<ul class="dropdown-menu">
						<li><a href="<?=base_url('users/profile/'.$this->session->userdata('user'));?>">My Profile</a></li>
						<li><a href="<?=MEDIA.'system/userguide.pdf'?>" target="_blank">Help</a></li>
						<li><a href="<?=base_url('login/logout');?>">Logout</a></li>
					</ul>
				</li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</div>
</nav>