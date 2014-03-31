var val, isEdit = 0, link = true;

function setApproval(ele, url)
{
	window.location = url + '/' + ele.getAttribute('id') + '/' + ele.options[ele.selectedIndex].value;
}

function edit(field, ele, asset_id) {
    switch(isEdit)
    {
        case 1: return;
        case 2: isEdit = 0; return;
    }

	val = ele.firstChild.innerHTML;
	alert(val);
    // val = ele.innerHTML;
    ele.setAttribute('id', 'edit');
    
    var html = '<input id="newValue" name="newValue" value="' + val + '"/>';
    
    ele.innerHTML = '<form method="post" accept-charset="utf-8" action="<?=base_url()?>/all_assets/edit/' + field + '" onsubmit="checkSubmit()" />' +
    				html + 
                    '<input type="submit" name="mysubmit" value="OK" />' +
                    '<input type="hidden" name="asset_id" value="' + asset_id + '" />' +
                    '<input type="Button" id="cancel" value="X" onclick="resetValue();"/>' + 
                    '</form>';
    isEdit = 1;
};

function resetValue() {
    var ele = document.getElementById('edit');

    ele.removeAttribute('id');
    ele.innerHTML = val;
    
    isEdit = 2;
};

function checkSubmit() {
	var ele = document.getElementById('newValue');

    if(ele.value == val || (ele.nodeName == 'SELECT' && ele.options[ele.value].text == val))
    {
    	resetValue();
    	isEdit = 0;
    	return false;
    }
    return true;
};