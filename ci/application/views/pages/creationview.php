<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Creating <?=ucfirst($section)?></h4>
        </div>
        <div class="modal-body">
            <?php 
                echo validation_errors(); 
                echo form_open($section.'s/form', array('id' => 'subForm', 'name' => 'subForm', 'class' => 'form-horizontal'));
                
                echo form_group_open();
                echo form_label('Title: ', 'title', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
                echo form_div_open('col-sm-8');
                echo form_input(array('name' => 'title', 'maxlength' => '40', 'value' => set_value('title'), 'class' => 'form-control'));
                echo form_div_close();
                echo form_group_close();
                
                if($section != 'task')
                {
                    echo form_group_open();
                    echo form_label('Logo: ', 'logo', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
                    foreach ($logos as $item)
                        $logo[$item['asset_id']] = $item['title'];
                    echo form_div_open('col-sm-8');
                    echo form_dropdown('logo', $logo, $this->input->post('logo'), 'class="form-control"');
                    echo form_div_close();
                    echo form_group_close();
                }
                
                echo form_group_open();
                echo form_label('Description: ', 'description', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
                echo form_div_open('col-sm-8');
                echo form_textarea('description', $this->input->post('description'), 'class="form-control"', 'rows = 3');
                echo form_div_close();
                echo form_group_close();

                echo form_group_open();
                echo form_label('Deadline: ', 'deadline', array('class' => 'col-sm-2 col-sm-offset-1 control-label'));
                echo form_div_open('col-sm-8');
                echo form_input(array('name' => 'deadline', 'value' => set_value('deadline'), 'class' => 'form-control'));
                echo form_div_close();
                echo form_group_close();
                
                echo form_group_open();
                echo form_label('Position: Before', 'order', array('class' => 'col-sm-3 col-sm-offset-1 control-label'));
                foreach ($sec_items as $sec_item)
                    $items[] = $sec_item['title'];
                echo form_div_open('col-sm-7');
                echo form_dropdown('order', $items, null, 'class="form-control"');
                echo form_div_close();
                echo form_group_close();
                
                echo form_hidden('parent_id', $parent_id);
                echo form_close();
            ?>
        </div>
        <div class="modal-footer">
        <?php
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal'));
            echo form_button(array('content' => 'Create', 'class'=>'btn btn-primary', 'type' => 'submit', 'onclick' => 'submitForm(true)'));
        ?>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?=JS.'formSubmit.js';?>"></script>
<script type="text/javascript" src="<?=JS.'datetime_picker.js'; ?>"></script>
<script type="text/javascript" src="<?=JS.'dp_config.js'; ?>"></script> 