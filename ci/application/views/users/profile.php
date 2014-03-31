<script>
    $(document).on('click', "a[data-toggle=modal]", function(){
        var target = $(this).attr("href");
        $.get(target, function(data){
            $("#modal").html(data);
            $("#modal").modal("show");
        });
        return false;      
    });
</script>
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Profile of <?=$username?></h4>
        </div>
        <div class="modal-body">
			<p>deadlines:<br> 
			<?php
				foreach($deadlines as $deadline)
					echo $deadline['deadline'].' ('.$deadline['time_left'].')'. ' for '.$deadline['task_title'];
			?>
			</p>
			<p>skills:<br> 
			<?php
				foreach ($skills as $skill)
					echo $skill['title'];
			?>
			</p><br>
			<?php 
				if($recruit)
				{
					 echo ('<p><a href="'.base_url('users/recruit/'.$username).'" data-target="#modal" data-toggle="modal">recruit</a></p>'); 
				} 
			?>				
		</div>
        <div class="modal-footer">
            <?php
                echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            ?>
        </div>
    </div>
</div>