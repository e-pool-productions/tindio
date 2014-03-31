<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?=$title?></title>
        <link rel="icon" type="image/ico" href="<?=base_url('media/system/favicon.ico')?>">   
        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Monda"/>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" />
        <link rel="stylesheet" type="text/css" href="<?=CSS.'bootstrap.min.css'?>"/>
        <link rel="stylesheet" type="text/css" href="<?=CSS.'css.css'?>"/>
        <script type="text/javascript" src="<?=JS.'jquery-2.1.0.min.js'?>"></script>
        <script type="text/javascript" src="<?=JS.'bootstrap.min.js'?>"></script>
        <script type="text/javascript" src="<?=JS.'kickstart.js'?>"></script>
    </head>
    <body>
        <div id="container">
            <header><?=$header?></header>
          	<div id="main">
                	<div id="content"><?=$content?></div>
            </div>
            <footer><?=$footer?></footer>
        </div>
        <div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true"></div>
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
        });
        </script>
    </body>
</html>