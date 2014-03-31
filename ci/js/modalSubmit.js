function submit(){
    $.ajax({
        type: "POST",
        url: $('#subForm').attr('action'),
        data: $('#subForm').serialize(),
        success: function(data) {
            if(data == 'done')
            {
                $("#modal").modal("hide");
                document.location.reload();
            }
            else
                $("#modal").html(data);
        }
    });
    return false;
};