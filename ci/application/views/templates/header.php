<div id="topmenu">
    <a href="<?=base_url('mystuff/dashboard')?>"><img id="logo" src="<?=MEDIA.'system/tindio.png'?>" style="max-width: 225px;"></a>
    <ul class="menu center">
        <li><a href="<?=base_url('mystuff/dashboard');?>"><i class="icon-dashboard"></i> Dashboard</a></li>
        <li class="has-menu"><a href="<?=base_url('mystuff/work');?>"><i class="icon-tasks"></i> My Stuff<span class="arrow">&nbsp;</span></a>
        	<ul style="display: none;">
        		<li class="first"><a href="<?=base_url('projects/filter/myProjects');?>">My Projects</a></li>
        		<li><a href="<?=base_url('all_assets/filter/myAssets')?>">My Assets</a></li>
        		<li class="last"><a href="<?=base_url('mystuff/work');?>">My Work</a></li>
        	</ul>
        </li>
        <?php
            if($isAdmin)
                echo '<li class="has-menu"><a href="'.base_url('projects').'"><i class="icon-film"></i> Projects<span class="arrow">&nbsp;</span></a>
                        <ul style="display: none;">
                            <li class="first last"><a href="'.base_url('projects/create').'" data-target="#modal" data-toggle="modal">New Project</a></li>
                        </ul>
                      </li>';
            else
                echo '<li><a href="'.base_url('projects').'"><i class="icon-film"></i> Projects</a>';
        ?>
        <li><a href="<?=base_url('all_assets');?>"><i class="icon-picture"></i> Global Assets</a></li>
        <?php
            if($isAdmin || $isDirector)
                echo '<li class="has-menu"><a href="'.base_url('users').'"><i class="icon-group"></i> Users<span class="arrow">&nbsp;</span></a>
                        <ul style="display: none;">
                            <li class="first last"><a href="'.base_url('users/create').'" data-target="#modal" data-toggle="modal">New User</a></li>
                        </ul>
                      </li>';
            else
                echo '<li><a href="'.base_url('users').'"><i class="icon-group"></i> Users</a>';
        ?>
        <li class="has-menu"><a href="#"><i class="icon-wrench"></i> Tools<span class="arrow">&nbsp;</span></a>
            <ul>
                <li class="first"><a href="<?=base_url('workflows');?>">Workflow Editor</a></li>
                <li class="last"><a href="<?=base_url('mystuff/calendar');?>">Calendar</a></li>
            </ul>
        </li>
        <?php
            if($isAdmin)
                echo '<li class="has-menu"><a href="#"><i class="icon-cog"></i> Admin<span class="arrow">&nbsp;</span></a>
                        <ul>
                            <li class="first last"><a href="'.base_url('settings').'">Settings</a></li>
                        </ul>
                      </li>';
       ?>

       <div id="avatar">
   			<li class="has-menu last">
    			<a href="#"><img src="<?=$gravatar_url?>" id="menu_smallavatar"></a>
    			<ul style="display: none;">
    				<li class="first">
    					<a href="<?=base_url('users/view');?>" data-target="#modal" data-toggle="modal">My Profile</a>
    				</li>
    				<li>
    					<a href="<?=MEDIA.'system/userguide.pdf'?>" target="_blank">Help</a>
    				</li>
    				<li class="divider last">
    					<a href="<?=base_url('login/logout');?>">Logout</a>
    				</li>
    			</ul>
    		</li>
		</div>
    </ul>
</div>
