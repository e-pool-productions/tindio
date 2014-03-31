<script type="text/javascript" src="<?php echo(JS.'modalSubmit.js'); ?>"></script>
<script type="text/javascript" src="<?=JS.'datetime_picker.js'; ?>"></script>
<script type="text/javascript" src="<?=JS.'dp_config.js'; ?>"></script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Editing Project</h4>
        </div>
        <div class="modal-body">
        <?php   
            echo validation_errors();
            echo form_open('projects/form/edit', array('id' => 'subForm', 'name' => 'subForm'));
            echo "<p></p>";
            echo form_hidden('id', $id);
            echo form_label('Title: ', 'title');
            echo form_input(array('name' => 'title', 'size' => '20', 'maxlength' => '40', 'value'=>$oldTitle)) . br(1);
            echo form_label('Shortcode: ', 'shortcode');                    
            echo form_input(array('name' => 'shortcode', 'size' => '3','maxlength' => '5', 'value'=>$oldShortcode)) . br(1);
			echo form_label('Director: ','director');
            foreach ($users as $user)
                $directors[$user['username']] = $user['firstname'].' '.$user['lastname'];
            echo form_dropdown('director', $directors, $oldDirector);
            echo br(1);         
            echo form_label('Logo: ', 'logo');
            foreach ($logos as $item)
                $logo[$item['asset_id']] = $item['title'];
            echo form_dropdown('logo', $logo, $oldlogo,'onChange="select(this.options[this.selectedIndex].innerHTML);"'); 
            echo br(1);
            echo form_label('Description: ', 'description');
            echo form_textarea(array('name' => 'description', 'value'=>$oldDescription, 'style' => 'height: 130px;font-size: 12px;padding: 4px 2px;width: 150px;margin: 2px 0px 10px 10px;')) . br(1);              
            echo form_label('Category: ', 'category');
            foreach ($category as $category_item)
                $data[$category_item['category_id']] = $category_item['title'];
            echo form_dropdown('category', $data, $oldCategory,'onChange="select(this.options[this.selectedIndex].innerHTML);"');                          
            echo br(1);           
            echo form_label('Deadline: ', 'deadline');
            echo form_input(array('name' => 'deadline', 'value'=> $oldDeadline)) . br(1);                    
            unset($data);
			echo form_close();
        ?>
        </div>
        <div class="modal-footer">
        <?php 
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Edit', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submit()')) . br(1);
        ?>
        </div>
    </div>
</div>