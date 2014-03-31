<script type="text/javascript" src="<?=JS.'modalSubmit.js'?>"></script>
<script type="text/javascript" src="<?=JS.'datetime_picker.js'; ?>"></script>
<script type="text/javascript" src="<?=JS.'dp_config.js'; ?>"></script>

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Creating Project</h4>
        </div>
        <div class="modal-body">
        <?php         
            echo validation_errors();
            echo form_open('projects/form/create/', array('id' => 'subForm', 'name' => 'subForm'));
            echo "<p></p>";
            echo form_label('Title: ', 'title', array('title'=>'test'));
            echo form_input(array('name' => 'title', 'value' => set_value('title'), 'size' => '20', 'maxlength' => '40')) . br(1);
            echo form_label('Shortcode: ', 'shortcode');                    
            echo form_input(array('name' => 'shortcode', 'value' => set_value('shortcode'), 'size' => '5','maxlength' => '5')) . br(1);                
            echo form_label('Director: ','director');
            foreach ($users as $user)
                $directors[$user['username']] = $user['firstname'].' '.$user['lastname'];
            echo form_dropdown('director', $directors);
            echo br(1);         
            echo form_label('Category: ', 'category');
            foreach ($category as $category_item)
                $data[$category_item['category_id']] = $category_item['title'];
            echo form_dropdown('category', $data,'onChange="select(this.options[this.selectedIndex].innerHTML);"');                        
            echo br(1);            
            echo form_label('Deadline: ', 'deadline');
            echo form_input(array('name' => 'deadline', 'value' => set_value('deadline'))) . br(1);                   
            unset($data);
			echo form_close();
        ?>
        </div>
        <div class="modal-footer">
        <?php 
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Create', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submit()')) . br(1); 	
        ?>
        </div>
    </div>
</div>