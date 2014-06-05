var val, isEdit = 0, isDropdown = false, link = true;

function setApproval(ele, url)
{
	window.location = url + '/' + ele.getAttribute('id') + '/' + ele.options[ele.selectedIndex].value;
}

function edit(ele, action) {
    switch(isEdit)
    {
        case 1: return;
        case 2: isEdit = 0; return;
    }
    
    var field = action.substring(action.lastIndexOf('/') + 1);
    
    val = ele.innerHTML;
    ele.setAttribute('id', 'edit');
    var html;

    switch(field)
    {
        case 'title':			var tag = ele.tagName == 'TD' && ele.getElementsByTagName('a').length > 0 ? 'a' : 'div';
        						var child = ele.getElementsByTagName(tag)[0];
        						html = '<input id="newValue" name="newValue" value="'+ (!child ? val : child.innerHTML) +'" />'; break;
        case 'shortcode': 		html = '<input id="newValue" name="newValue" value="'+ val +'" />'; break;
        case 'description':		html = '<textarea id="newValue" name="newValue">'+ (ele.tagName == 'TD' ? ele.firstChild.innerHTML : val) +'</textarea>'; break;
        case 'tags': 			html = '<textarea id="newValue" name="newValue">'+ val +'</textarea>'; break;
        case 'type_id' : 		html = prepareSelect(typeDrop, true); break;
        case 'category_id' : 	html = prepareSelect(cateDrop, true); break;
        case 'orderposition': 	html = prepareSelect(posDrop, true); break;
        case 'addSkill' : 		html = val + prepareSelect(skillDrop, false); break;
        case 'logo': 			html = prepareSelect(logoDrop, true); break;
        case 'timezone': 		html = times; break;
        default: 				html = '<input id="newValue" name="newValue" value="'+ val +'" />'; break;
    }
    
    ele.innerHTML = '<form method="post" id="subForm" accept-charset="utf-8" action="' + action + '" onsubmit="return submitChange();"/>' +
	                    html + 
	                    '<input type="submit" name="mysubmit" value="OK"/>' +
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

function submitChange(){
	var ele = document.getElementById('newValue');

    if(!isDropdown && ele.value == val)
    {
        resetValue();
        isEdit = 0;
        return false;
    }
    
    return submitForm(false);
};

function prepareSelect(select, preSelect) {
	isDropdown = true;
	select.setAttribute('id', 'newValue');
	select.setAttribute('name', 'newValue');
	
	if(preSelect)
	    for(var i = 0; i < select.options.length; i++)
	        if(select.options[i].innerHTML == val)
	        {
	            select.options[i].setAttribute('selected', 'true');
	            break;
	        }
    return select.outerHTML;
}

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
