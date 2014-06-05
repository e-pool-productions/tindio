function submitForm(isModal){
    $.ajax({
        type: "POST",
        url: $('#subForm').attr('action'),
        data: $('#subForm').serialize(),
        success: function(data) {
            if(data == 'done')
            {
            	if(isModal)
                	$("#modal").modal("hide");
                document.location.reload();
            }
            else
            {
            	if(isModal)
            		$("#modal").html(data);
            	else
            		alert(data);
            }
        }
    });
    return false;
};