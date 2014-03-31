var config = {	format: 'dd.mm.yyyy hh:ii',
    			weekStart: 1,
    			startDate: new Date(),
    			todayBtn: true,
    			todayHighlight: true,
    			autoclose: true};
	
$('*[name=deadline]').datepicker(config);