<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">File processing complete</h4>
        </div>
        <div class="modal-body">
            <ul>
            <?php
                for($i = 0; $i < count($success); $i++)
                    echo '<li>' . ($success[$i] ? 'Sucessfully uploaded ' . $filenames[$i] : 'Failed to upload ' . $filenames[$i]) . '</li>';    
            ?>
            </ul>
        </div>
        <div class="modal-footer">
        <?php 
            echo form_button(array('content' => 'Close', 'class'=>'btn btn-default', 'data-dismiss' => 'modal', 'onclick' => 'javascript:window.location.reload()'));
        ?>
        </div>
    </div>
</div>