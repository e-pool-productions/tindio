<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Showcase</h4>
        </div>
        <div class="modal-body">
			<?php
			    if($ext == '.obj')
			    { ?>
			        <canvas id="cv" width="960" height="720" style="max-width: 100%; max-height: 100%;">
			            It seems you are using an outdated browser that does not support canvas :-(
			        </canvas>
			        
			        <script type="text/javascript" src="<?=JS.'jsc3d.js'?>"></script>
			        <script type="text/javascript">
			            var viewer = new JSC3D.Viewer(document.getElementById('cv'));
			            viewer.setParameter('SceneUrl', '<?=MEDIA . $path?>');
			            viewer.setParameter('ModelColor',       '#CAA618');
			            viewer.setParameter('Definition', 'high');
			            viewer.setParameter('RenderMode',       'texturesmooth');
			            viewer.setParameter('Renderer', 'webgl');
			            viewer.init();
			            viewer.update();
			        </script>
			    <?php
			    }
			    else 
			    {?>
			    	<object onload="setStyle()" id="obj" data="<?=MEDIA . $path?>" width=960 height=720>
						<img src="<?=MEDIA.'system/sad.png'?>" class="showcase" />
						<h1>Sorry we can not display this file format</h1>
					</object>
			    <?php 
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
<script>
	function setStyle() {
		var obj = document.getElementById("obj");
		var doc = obj.contentDocument;
		
		obj.style.maxWidth = obj.style.maxHeight = '100%';
		
		if(!doc)
			obj.style.height = obj.style.width = 'auto';
	}
</script>