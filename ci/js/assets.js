var val, isEdit = 0, link = true;

function confUnlinkAsset() {
    msg = "Remove Assetreference?\nThis will not delete the Assetfile!";
    return confirm(msg);
};

function confDestroyAsset() {
    msg = "Destroy Asset?\nThe Asset will be UNAVAILABLE afterwards!";
    return confirm(msg);
};

function edit(field, ele, asset_id, action) {
    switch(isEdit)
    {
        case 1: return;
        case 2: isEdit = 0; return;
    }

    val = ele.innerHTML;
    ele.setAttribute('id', 'edit');
    var html;
    
    switch(field)
    {
        case 'title': html = '<input id="newValue" name="newValue" value="' + val + '"/>'; break;
        case 'description':
        case 'tags': html = '<textarea id="newValue" name="newValue">'+ val +'</textarea>'; break;
        case 'type_id' : 
        	select.setAttribute('id', 'newValue');
        	select.setAttribute('name', 'newValue');
            for(var i = 0; i < select.options.length; i++)
                if(select.options[i].innerHTML == val)
                {
                    select.options[i].setAttribute('selected', 'true');
                    break;
                }
            html = select.outerHTML;
            break;                                  
    }
    
    ele.innerHTML = '<form method="post" accept-charset="utf-8" action="' + action + '" onsubmit="checkSubmit()" />' +
	                    html + 
	                    '<input type="submit" name="mysubmit" value="OK" />' +
	                    '<input type="hidden" name="asset_id" value="' + asset_id + '" />' +
	                    '<input type="Button" id="cancel" value="X" onclick="resetValue();"/>' + 
                    '</form>';
    isEdit = 1;
};

function editSectionAsset(ele, asset_id, action) {
    switch(isEdit)
    {
        case 1: return;
        case 2: isEdit = 0; return;
    }

    val = ele.innerHTML;
    var linktext = ele.firstChild.innerHTML;

    ele.setAttribute('id', 'edit');
    
    ele.innerHTML = '<form method="post" accept-charset="utf-8" action="'+action+'" onsubmit="checkSubmit()" />' +
	                    '<input id="newValue" name="newValue" value="' + linktext + '"/>' +
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

    // if(ele.value == val || (ele.nodeName == 'SELECT' && ele.options[ele.value].text == val))
    // {
        // resetValue();
        // isEdit = 0;
        // return false;
    // }
    return true;
};