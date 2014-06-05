<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Create workflow</h4>
        </div>
        <div class="modal-body">
			<?php				
				echo form_open('workflows/form', array('id' => 'subForm', 'name' => 'subForm'));
				echo form_label('Title: ', 'title');
				echo form_input(array('name' => 'title', 'size' => '20', 'maxlength' => '20'));
				echo form_label('Amount of tasks: ', 'num_of_tasks');
				echo form_input(array('name'=>'num_of_tasks', 'type' => 'number', 'min' => 1, 'max' => 100));
				echo form_div_open(NULL, 'settings');
				echo form_div_close();
				echo form_close();
			?>
		</div>
		<div class="modal-footer">
			<button class="btn btn-default" data-dismiss="modal">Close</button>
			<button class="btn btn-info" onclick="settings()">Update</button>
			<button id="finish" class="btn btn-success disabled" onclick="if(hasTasks) submitForm(true)">Finish</button>
        </div>
    </div>
</div>

<script src="<?=JS.'formSubmit.js'?>"></script>
<script>
	var hasTasks = 0;
	function settings(){
		if(	$('input[name=num_of_tasks]').val() > parseInt($('input[name=num_of_tasks]').attr('max')) ||
			$('input[name=num_of_tasks]').val() < parseInt($('input[name=num_of_tasks]').attr('min')))
		{
			$('#settings').html('');
			$('#finish').addClass('disabled');
			hasTasks = 0;
			return;
		}
			
		var settings = $('<div>');
		for(var i = 0; i < $('input[name=num_of_tasks]').val(); i++)
		{
			var num = i + 1;
			var lab = $('<label for="title'+i+'">').text('Title/Description of Task ' + num);
			var in1 = $('<input name="title'+i+'" value="Title '+num+'">');
			var in2 = $('<input name="description'+i+'" value="Description '+num+'">');
			settings.append(lab).append(in1).append(in2);
		}
		$('#settings').html(settings.html());
		$('#finish').removeClass('disabled');
		hasTasks = 1;
	}
</script> 