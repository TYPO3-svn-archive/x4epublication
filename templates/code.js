function showPersonSearch(kindOfPerson) {
	var iframe = document.getElementById(kindOfPerson+'Search');
	iframe.style.height = "450px";
	iframe.style.width = "450px";
	iframe.style.visibility='visible';
}

function hidePersonSearch(kindOfPerson) {
	var iframe = document.getElementById(kindOfPerson+'Search');
	iframe.style.height = "0px";
	iframe.style.width = "0px";
	iframe.style.visibility='hidden';
}

function searchPerson(kindOfPerson) {
	var usedUids = document.getElementById(kindOfPerson+'Uids');
	var uids = parent.document.getElementById(kindOfPerson+'Ids');
	usedUids.value = uids.value;
	document.getElementById(kindOfPerson+'SearchForm').submit();
}

function addPerson(uid,name,firstname,kindOfPerson) {
	var ids = $(kindOfPerson+'Ids').value.split(',');
	/*var tmp = ids.value.split(",");*/
	ids.push(uid);
	$(kindOfPerson+'Ids').value = ids.join(",");
	var box = document.getElementById(kindOfPerson+'Container');
	var tmpl = document.getElementById(kindOfPerson+'Template').innerHTML;
	
	tmpl = tmpl.replace(/###actualUserUid###/g, uid);
	tmpl = tmpl.replace(/###actualUserFirstname###/g,firstname);
	tmpl = tmpl.replace(/###actualUserName###/g,name);
	box.innerHTML += tmpl;
	sortablePersons(kindOfPerson);
}

/**
 * Removes a person from the list
 */
function removePerson(uid,kindOfPerson,name) {
	// check if uid is an integer
	if (parseInt(uid) == uid) {
			// only ask for confirmation if author is supposed to be removed
		if (confirm('Wenn Sie '+name+' entfernen, erscheint die Publikation nicht der entsprechenden persönlichen Liste und '+name+' kann die Publikation nicht mehr bearbeiten')) {
			var ids = document.getElementById(kindOfPerson+'Ids');
			var tmp = ids.value.split(",");
			var tmp2 = new Array();
			// loop over authorids and remove selected
			for (i = 0; i < tmp.length; i++) {
				if (tmp[i] != uid) {
					tmp2.push(tmp[i]);
				}
			}
			// add uids to input
			ids.value = tmp2.join(",");
			
			
			/*document.getElementById(kindOfPerson+'_'+uid).innerHTML = "";
			var box = document.getElementById(kindOfPerson+'Container');
			// if apperance of author-list changes, make changes accordingly
			box.innerHTML = box.innerHTML.replace('<p id="'+kindOfPerson+'_'+uid+'"></p>','');*/
			$(kindOfPerson+'_'+uid).remove();
		}
	} else {
		removeExternalPerson(uid,kindOfPerson,name);
	}
	sortablePersons(kindOfPerson);
}

function removePublication(uid) {
	if (confirm('Wenn Sie die Publikation löschen erscheint Sie bei keiner Person mehr.')) {
		var f = document.getElementById('removePublicationForm');
		f.action = window.location;
		var ih = document.getElementById('removePublicationId');
		ih.value=uid;
		f.submit();
	}
}

/**
 * Removes a person from 
 */
function removeExternalPerson(uid,kindOfPerson,name) {
	var check = confirm('Möchten Sie '+name+' wirklich entfernen?');
	if (check) {
		var authors = $(kindOfPerson+'s_ext').value.split("\n");
		var output = new Array();
		var id = uid.split('-')[1];
		for(i=0;i<authors.length;i++) {
			temp = authors[i].split(',');
			if (temp.length > 1) {
				if (temp[2]!=id) {
					output.push(authors[i]);
				}
			}
		}
		$(kindOfPerson+'s_ext').value = output.join("\n");
		$(kindOfPerson+'_'+uid).remove();
		updateSorting(kindOfPerson);
	}
}


function addPerson(uid,name,firstname,kindOfPerson) {
	var ids = $(kindOfPerson+'Ids').value.split(',');
	/*var tmp = ids.value.split(",");*/
	ids.push(uid);
	$(kindOfPerson+'Ids').value = ids.join(",");
	var box = document.getElementById(kindOfPerson+'Container');
	var tmpl = document.getElementById(kindOfPerson+'Template').innerHTML;
	
	tmpl = tmpl.replace(/###actualUserUid###/g, uid);
	tmpl = tmpl.replace(/###actualUserFirstname###/g,firstname);
	tmpl = tmpl.replace(/###actualUserName###/g,name);
	box.innerHTML += tmpl;
	sortablePersons(kindOfPerson);
}


/**
 * Checks the textarea "ext_authors" for the highes possible "id", adds 1 and returns this id
 *
 * @since 29.05.2008
 */
function getExtAuthorId(kindOfPerson) {
	var authors = $(kindOfPerson+'s_ext').value.split("\n");
	var max = 0;
	for(i=0;i<authors.length;i++) {
		temp = authors[i].split(',');
		if (temp.length > 1) {
			if (temp[2]>max) {
				max = temp[2];
			}
		}
	}
	return parseInt(parseInt(max) + 1);
}

/**
 * @since 29.05.2008
 */
function updateSorting(kindOfPerson) {
	var v = Sortable.serialize(kindOfPerson+'Container');
	var vArr = v.split('&');
	var idArray = new Array();
	for(i=0;i<vArr.length;i++) {
		temp = vArr[i].split('=');
		idArray.push(temp[1]);
	}
	$(kindOfPerson+'Ids').value = idArray.join(',');
};

/**
 * Makes the author list sortable
 *
 * @since 29.05.2008
 */
function sortablePersons(kindOfPerson) {
	Sortable.create(kindOfPerson+'Container',{onUpdate:function(){updateSorting(kindOfPerson);}});
}


/**
 * @since 29.05.2008
 */
function movePersonUp(kindOfPerson,key) {
	var sequence=Sortable.sequence(kindOfPerson+'Container');
	var newsequence=[];
	var reordered=false;

	//move only, if there is more than one element in the list
	if (sequence.length>1) for (var j=0; j<sequence.length; j++) {
		
		//move, if not already first element, the element is not null
		if (j>0 &&
		  sequence[j].length>0 &&
		  sequence[j]==key) {
		
		  var temp=newsequence[j-1];
		  newsequence[j-1]=key;
		  newsequence[j]=temp;
		  reordered=true;
		} else {//if element not found, just copy array
		  newsequence[j]=sequence[j];
		}
	}
	
	if (reordered) {
		Sortable.setSequence(kindOfPerson+'Container',newsequence);
		updateSorting(kindOfPerson);
	}
    	//return reordered;
}

function movePersonDown(typeOfPerson,key) {
	try {
		if ($(typeOfPerson+'_'+key).nextSibling != null) {
			movePersonUp(typeOfPerson,$(typeOfPerson+'_'+key).nextSibling.id.split('_')[1]);
		}
	} catch (e) {}
}

/**
 * Adds an external author to the list
 */
function addExternalPerson(kindOfPerson) {
	var persons = $(kindOfPerson+'s_ext').value.split("\n");
	var id = getExtAuthorId(kindOfPerson);
	if (kindOfPerson == 'author') {
		name = $('extAutName').value;
		firstname = $('extAutFirstname').value;
		$('extAutName').value = '';
		$('extAutFirstname').value = '';
	} else {
		name = $('extPublName').value;
		firstname = $('extPublFirstname').value;
		$('extPublName').value = '';
		$('extPublFirstname').value = '';
	}
	persons.push(name+','+firstname+','+id);
	$(kindOfPerson+'s_ext').value = persons.join("\n");
	addPerson('ext-'+id,name,firstname,kindOfPerson);
}

function showIframe(id,width,height) {
	var iframe = document.getElementById(id);
	iframe.style.height = height;
	iframe.style.width = width;
	iframe.style.visibility='visible';
}

function addPublication(id) {
	if (checkMaxPublications()) {
		if (addSelectedPublication(id)) {
			var code = parent.document.getElementById('publisherTemplate').innerHTML;
			var content = document.getElementById('content'+id).innerHTML;
			code = code.replace('###content###',content);
			code = code.replace('###id###',id);
			code = code.replace('###uid###',id);
			code = code.replace('###uid###',id);
			code = code.replace('###uid###',id);
			code = code.replace('###uid###',id);
			node = parent.document.createElement("div");
			node.innerHTML = code;
			node.id = 'publ_'+id;
			parent.document.getElementById('publicationList').appendChild(node);
			enableSave();
		}
	}
}

function checkMaxPublications() {
	var publications = parent.document.getElementById('selectedPublications').value;
	var pubArr = publications.split(",");
	if (pubArr.length > 2) {
		alert("Sie haben bereits drei Publikationen ausgewählt.");
		return false;
	} else {
		return true;
	}
}

function addSelectedPublication(id) {
	var sel = trim(parent.document.getElementById('selectedPublications').value);
	var selArr = sel.split(',');
	if (!alreadySelectedPublication(id,selArr)) {
		selArr = clearEmptyValues(selArr);
		selArr.push(id);
		parent.document.getElementById('selectedPublications').value = selArr.join(',');
		return true;
	} else {
		return false;
	}
}

function trim(s) {
	if (s.length>0){
	  while (s.substring(0,1) == ' ') {
	    s = s.substring(1,s.length);
	  }
	  while (s.substring(s.length-1,s.length) == ' ') {
	    s = s.substring(0,s.length-1);
	  }
	  return s;
	} else {
		return '';
	}
}

function alreadySelectedPublication(id,arr) {
	for(i=0;i<arr.length;i++) {
		if (arr[i] == id) {
			alert('Diese Publikation ist bereits ausgewählt.');
			return true;
		}
	}
	return false;
}

function clearEmptyValues(arr) {
	var tmp = new Array();
	for(i=0;i<arr.length;i++) {
		if (trim(arr[i]) != '') {
			tmp.push(arr[i]);
		}
	}
	return tmp;
}


function enableSave() {
	try {
		document.getElementById('saveButton').disabled = false;
	} catch (e) {
		parent.document.getElementById('saveButton').disabled = false;
	}

}

function moveDown(id) {
	var elm = parent.document.getElementById('publ_'+id);
	if (elm.nextSibling != null) {
		var i = elm.nextSibling.id;
		try {
			var tmp = i.split('_')[1];
			moveUp(tmp);
		} catch(e) {

		}
	}
}

function moveUp(id) {
	var elm = parent.document.getElementById('publ_'+id);
	var publs = parent.document.getElementById('selectedPublications');
	var sel = trim(parent.document.getElementById('selectedPublications').value);
	var selArr = sel.split(',');
	var n = new Array(selArr.length);

	var elmArr = new Array();

	if (selArr[0]!=id) {

		if (selArr[2]==id) {
			n[0]=selArr[0];
			n[1]=selArr[2];
			n[2]=selArr[1];

			elmArr[0] = parent.document.getElementById('publ_'+selArr[0]);
			elmArr[1] = parent.document.getElementById('publ_'+selArr[2]);
			elmArr[2] = parent.document.getElementById('publ_'+selArr[1]);
		}

		if (selArr[1]==id) {
			n[0]=selArr[1];
			n[1]=selArr[0];
			if (n.length>2) {
				n[2]=selArr[2];
			}
			elmArr[0] = parent.document.getElementById('publ_'+selArr[1]);
			elmArr[1] = parent.document.getElementById('publ_'+selArr[0]);
			elmArr[2] = parent.document.getElementById('publ_'+selArr[2]);
		}

		for(i=0;i<selArr.length;i++) {
			try {
				parent.document.getElementById('publicationList').removeChild(elmArr[i]);
			} catch (e) {

			}
		}

		for(i=0;i<selArr.length;i++) {
			try {
				parent.document.getElementById('publicationList').appendChild(elmArr[i]);
			} catch(e) {}
		}
		parent.document.getElementById('selectedPublications').value = n.join(',');
		enableSave();
	}
	//parent.document.getElementById('publicationList').removeChild(elm);
	//parent.document.getElementById('publicationList').insertBefore(elm, parent.document.getElementById('publicationList').firstChild);

}


function removePublicationFromSelection(id) {
	var sel = trim(parent.document.getElementById('selectedPublications').value);
	var selArr = sel.split(',');
	var remain = new Array();
	for(i=0;i<selArr.length;i++) {
		if (selArr[i] != id) {
			remain.push(selArr[i])
		}
	}

	parent.document.getElementById('selectedPublications').value = remain.join(',');
	parent.document.getElementById('publicationList').removeChild(parent.document.getElementById('publ_'+id));
	enableSave();
	//parent.document.getElementById('publ_'+id).innerHTML = '';
	//parent.document.getElementById('publ_'+id).id = '';
}