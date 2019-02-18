function copyToClipFromId(id){
	var inp =document.createElement('input');
	document.body.appendChild(inp)
	inp.value = id;
	inp.select();
	document.execCommand('copy',false);
	inp.remove();
}

