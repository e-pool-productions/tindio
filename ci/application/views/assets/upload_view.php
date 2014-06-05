<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Add new asset</h4>
        </div>
        <div class="modal-body">
            <div id="cont">
        	We can display the following formats: <?=FORMATS?><br />
        	But all other formats can be uploaded, too!
        <?php
            if(isset($error)) echo $error;
        
            echo form_open_multipart('', array('id' => 'formfiles', 'name' => 'formfiles'));
            echo form_input(array('type' => 'file', 'name' => 'userfile', 'id' => 'fileupload', 'multiple' => 'multiple', 'onchange' => 'handleFileSelect()'));
            echo form_close();
            
			$action = "upload/insertFiles/$section".(isset($section_id) ? "/$section_id" : '');
            echo form_open($action, array('id' => 'subForm', 'name' => 'subForm'));
            ?>
            <output id="hidden">
                <input type="hidden" id="amount" name="amount" value="0">
                <?php if(isset($setting)) echo '<input type="hidden" name="setting" value="1">';?>
            </output>
            <?php
            echo form_close();
        ?>
        </div>
        </div>
        <div class="modal-footer">
        <?php 
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Upload', 'id' => 'submit', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'validate(); return false;'));
        ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo(JS.'upload.js'); ?>"></script>
<script type="text/javascript" src="<?php echo(JS.'formSubmit.js'); ?>"></script>
<script>
	init('<?=base_url('upload/upload_file')?>', '<?=base_url('upload/merge_file')?>');
	
    function handleFileSelect() {
        
        var rows = [];
        var hiddens = [];
        var amount = document.getElementById('amount').value;
        
    	for(var i = 0, file; file = document.getElementById('fileupload').files[i]; i++)
            files.push(file);
                
        for (var i = amount, f; f = files[i]; i++)
        {
            hiddens.push('<input type="hidden" id="name' + i + '" name="name' + i + '" value="' + f.name + '">');
            rows.push.apply(rows, createRow(false));
        }
         
         document.getElementById('hidden').innerHTML += hiddens.join('');
         createTable(rows);
    }
    
    function createRow() {
        
        var row = new Array();
        var eleAmount = document.getElementById('amount');
        var next = eleAmount.value;

        row.push('<tbody><tr>',
                    '<th class="title">Title/Type</th>',
                    '<td colspan="3">',
                        '<div class="progress_bar" id="progress_bar' + next + '" name="progress_bar' + next + '">',
                            '<div class="percent" id="percent' + next + '" name="percent' + next + '"></div>',
                        '</div>',
                        '<a id="remove'+ next +'" onclick="removeRow(this); return false;" href=""><i class="fa fa-times"></i></a>',
                    '</td>',
                 '</tr>',
                 '<tr>');

        var predType = files[next].type || 'n/a';
        
        if(predType != 'n/a')
            predType = predType.substring(0, predType.indexOf('/'));
        
        var types = <?=json_encode($types);?>;
            
        var opt;
        types.forEach(function(type){
            if(type['assettype_id'] != 4)
            {
                var selected =  predType == type['type_name'].toLowerCase() ||
                                predType != 'video' && predType != 'audio' && predType != 'image';
                
                opt += '<option value="' + type['assettype_id'] + '"' + (selected ? 'selected' : '') + '>' + type['type_name'] + '</option>';
            }          
        });
        
        row.push(
        	'<td rowspan="3">',
					'<input id="title'+ next +'" name="title'+ next +'" placeholder="Name your Asset">',
                    '<select id="type'+ next +'" name="type'+ next +'">' + opt + '</select>',
                '</td>',
                '<th scope="col">Description</th>',
                '<th scope="col">Tags</th>',
                '<th scope="col">Details</th>',
			'</tr>',
			'<tr>',
                '<td class="wordwrap"><textarea id="description'+ next +'" name="description'+ next +'" style="width: 100%; height: 100%; border: none"></textarea></td>',
                '<td class="wordwrap"><textarea id="tags'+ next +'" name="tags' + next + '" style="width: 100%; height: 100%; border: none"></textarea></td>',
                '<td class="info">',
                    '<strong>' + files[next].name + '</strong><br />',
                    ' (', files[next].type || 'n/a', ') - ', files[next].size, ' bytes',
                '</td>',
			'</tr>',
		'</tbody>');
        		// '<td rowspan="3">',
					// '<input id="title'+ next +'" name="title'+ next +'" placeholder="Name your Asset">',
                    // '<select id="type'+ next +'" name="type'+ next +'">' + opt + '</select>',
                // '</td>',
                // '<th scope="row">Details</th>',
                // '<td class="up info">',
                    // '<strong>' + files[next].name + '</strong><br />',
                    // ' (', files[next].type || 'n/a', ') - ', files[next].size, ' bytes',
                // '</td>',
			// '</tr>',
			// '<tr>',
				// '<th scope="row">Description</th>',
                // '<td class="ta"><textarea id="description'+ next +'" name="description'+ next +'"></textarea></td>',
			// '</tr>',
			// '<tr>',
				// '<th scope="row">Tags</th>',
				// '<td class="ta"><textarea id="tags'+ next +'" name="tags' + next + '"></textarea></td>',
			// '</tr>',
			// '<tr>',
				// '<td colspan="3" class="blank"></td>',
			// '</tr>',
		// '</tbody>');

        eleAmount.value++;
        return row;
    }
    
    function createTable(rows) {
        var table = document.getElementById('uploadTable');
        
        if(table == null)
        {
            table = document.createElement('table');
            table.setAttribute('id', 'uploadTable');
            table.setAttribute('class', 'table table-bordered')
            table.innerHTML = rows.join('');
            document.subForm.appendChild(table);
        }
        else
            table.innerHTML += rows.join('');
    }
    
	function removeRow(el) {
        
        var str = el.getAttribute('id');
        var index = parseInt(str.substring(str.length - 1, str.length));
		
		var body = document.getElementById("uploadTable").getElementsByTagName('tbody')[index];
		body.parentNode.removeChild(body);
		
		files.splice(index, 1);
  
		var hidden = document.getElementById('name' + index);
		hidden.parentNode.removeChild(hidden);
		
		var amount = document.getElementById('amount');
		
		var objects = ['title', 'description', 'tags', 'progress_bar', 'percent', 'name', 'type', 'remove'];

		for(var rest = index; rest < amount.value; rest++)
		{
            var next = rest + 1;

            for(var i = 0, object; object = objects[i]; i++)
            {
                var ele = document.getElementById(object + next);
                
                if(ele)
                {
                    var id = ele.getAttribute('id');
                    var name = ele.getAttribute('name');
                    
                    ele.setAttribute('id', object + rest);
                    ele.setAttribute('name', object + rest);
                }
            }
		}
		amount.value--;
	}
	
	function validate() {
	    var amount = document.getElementById('amount');
	    
	    if(files.length > 0)
	    {
	        for(var i = 0, title; title = document.getElementById('title' + i); i++)
	        {
	            if(title.value == '')
	            {
	                alert("Please name all of your Assets!");
	                return;
	            }
	        }
	        sendRequest('fileupload');
	    }
	    else
	    {
	        if(amount.value > 0)
	        {
	            for(var i = 0, title; title = document.getElementById('title' + i); i++)
                {
                    if(title.value == '')
                    {
                        alert("Please name all of your Assets!");
                        return;
                    }
                }
                submit();
	        }
	    }
	}
</script>
