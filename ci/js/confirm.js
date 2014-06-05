var val, isEdit = 0, link = true;

function confUnlinkAsset() {
    msg = "Remove Assetreference?\nThis will not delete the Assetfile!";
    return confirm(msg);
};
function confDestroyAsset() {
    msg = "Destroy Asset?\nThe Asset will be UNAVAILABLE afterwards!";
    return confirm(msg);
};

function confDelete(section) {
	
	if($.inArray(section, ["project", "scene", "shot"]) != -1)
	{
		var msg = 'Delete whole ' + section + '?\n\n'
					+ 'There might be associated\n';
			
		switch (section)
		{
			case 'project': msg += '\tScenes\n';
			case 'scene': msg += '\tShots\n';
			case 'shot': msg += '\tTasks\n';
		}
		
		msg += 'which will be deleted too!';
	}
	else
	{
		switch(section)
		{
			case 'task': 		msg = 	'Delete whole Task?\n'+
                        				'This will unassign all Artists and delete all not used assets!'; break;
            case 'workflow': 	msg =	'Delete whole workflow?\n' +
            							"This can't be undone!"; break;
         	case 'workflowTask':msg =	'Remove this Task form workflow?'; break;
         	case 'user': 		msg =	'Delete User completely from platform?\n'+
         								"This can't be undone!"; break;
		}
	}
	
	return confirm(msg);	
};

function confUnassign(section, firstname, lastname) {
	return confirm('Do you really want to unassign\n'+
                   '\t'+firstname+' '+lastname+'\n'+
                   'from ' + section + '?');
}