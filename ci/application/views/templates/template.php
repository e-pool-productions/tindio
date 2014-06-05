<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?=$title?></title>
        <link rel="icon" type="image/ico" href="<?=MEDIA.'system/favicon.ico'?>">
        <link rel="stylesheet" type="text/css" href="<?=CSS.'font-awesome.min.css'?>"/>
        <link rel="stylesheet" type="text/css" href="<?=CSS.'bootstrap.min.css'?>"/>
        <link rel="stylesheet" type="text/css" href="<?=CSS.'bootstrap-theme.min.css'?>"/>
        <link rel="stylesheet" type="text/css" href="<?=CSS.'style.css'?>"/>
        <script type="text/javascript" src="<?=JS.'jquery-2.1.0.min.js'?>"></script>
    </head>
    <body>
        <div id="wrap">
        	<?=$header?>
        	<div class="container-fluid">
        		<?=$content?>
        	</div>
        </div>
        <footer>
        	<?=$footer?>
       	</footer>
        <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true"></div>
        <script type="text/javascript" src="<?=JS.'bootstrap.min.js'?>"></script>
        <script>
	        $(document).ready(function(){
	            
	            $(document).on('click', "a[data-toggle=modal]", function(){
	         
	                var target = $(this).attr("href");
	                $.get(target, function(data){
	                    $("#modal").html(data);
	                    $("#modal").modal("show");
	                });
	                return false;      
	            });
	            
				$('.dropdown').hover(function() {
				  $(this).find('.dropdown-menu').first().stop(true, true).delay(0).slideDown();
				}, function() {
				  $(this).find('.dropdown-menu').first().stop(true, true).delay(0).slideUp();
				});
				
				$(document).ready(function() {
			        $('[data-toggle="tooltip"]').tooltip();
				});
	        });
        </script>
    </body>
</html>