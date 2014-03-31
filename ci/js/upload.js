const BYTES_PER_CHUNK = 2097152;
var slices;
var slices2;
var loaded = 0;
var total = 0;
var files = new Array();
var ufilenum = 0;   // count uploaded Files
var mfilenum = 0;   // count merged Files
var upUrl;
var mergeUrl;

function init(uurl, murl) {
	upUrl = uurl;
	mergeUrl = murl;
}

function sendRequest(inputId) {

	if(files.length > 0)
	{
		updateBar(0, '0%', '0%');
    	proceedFile(0);
	}
}

function updateBar(i, width, text) {
    var percent = document.getElementById('percent' + i);
    
    percent.style.width = width;
    percent.textContent = text;
}

function proceedFile(i) {
    
    document.getElementById('progress_bar' + i).className = 'progress_bar loading';
    
    var blob = files[i];

    var start = 0;
    var end;
    var index = 0;
    
    loaded = 0;
    total = files[i].size; 

    // calculate the number of slices we will need
    slices = Math.ceil(blob.size / BYTES_PER_CHUNK);
    slices2 = slices;

    while(start < blob.size)
    {
        end = start + BYTES_PER_CHUNK;
        if(end > blob.size)
            end = blob.size;

        uploadFile(blob, index, start, end);

        start = end;
        index++;
	}
}

function uploadFile(blob, index, start, end) {
    var xhr = new XMLHttpRequest();
    var chunk;

    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4) {
            if(xhr.responseText) {
                alert(xhr.responseText);
            }

            slices--;
            loaded += BYTES_PER_CHUNK;
            
            var percentLoaded = Math.ceil((loaded / total) * 100);
            if (percentLoaded < 100)
                updateBar(ufilenum, percentLoaded + '%', percentLoaded + '%');
            

            // if we have finished all slices
            if(slices == 0) {
                updateBar(ufilenum, '100%', 'Upload complete. Processing...');
                
                mergeFile(blob);
                ufilenum++;
                if(ufilenum < files.length)
                	proceedFile(ufilenum);
            }
        }
    };
    
    var chunk = blob.slice(start, end);
    
    var fd = new FormData();
    fd.append("file", chunk);
    fd.append("name", blob.name.toLowerCase());
    fd.append("index", index);

	xhr.open("POST", upUrl, true);
    xhr.send(fd);
    delete fd;
    delete xhr;
}

function mergeFile(blob) {
    var xhr = new XMLHttpRequest();
    var fd = new FormData();
    
    xhr.onreadystatechange = function() {
        if(xhr.readyState == 4) {
            if(xhr.responseText) {
                alert(xhr.responseText);
            }

            updateBar(mfilenum, '100%', 'Done');
            mfilenum++;
            
            if(mfilenum == files.length)
                submit();
        }
    };
    
    var sel = document.getElementById('type' + ufilenum);

    fd.append("name", blob.name.toLowerCase());
    fd.append("index", slices2);
    fd.append("dir", sel.options[sel.selectedIndex].text);

    xhr.open("POST", mergeUrl, true);
    xhr.send(fd);
    delete fd;
    delete xhr;
}