//é
var ajax_file = "ajax_ajaxCRUD.php";	// the ajax file returning get/post requests (default is 'ajax_ajaxCRUD.php')
var loading_image_html; //set via setLoadingImageHTML()

var filterReq = "";
var sortReq = "";
var sort_table_num = 1;
var this_page;		// the php file loading ajaxCRUD

/* Ajax functions */

function createRequestObject() {
     var http_request = false;
      if (window.XMLHttpRequest) { // Mozilla, Safari,...
         http_request = new XMLHttpRequest();
         if (http_request.overrideMimeType) {
         	// set type accordingly to anticipated content type
            //http_request.overrideMimeType('text/xml');
            http_request.overrideMimeType('text/html');
         }
      } else if (window.ActiveXObject) { // IE
         try {
            http_request = new ActiveXObject("Msxml2.XMLHTTP");
         } catch (e) {
            try {
               http_request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e) {}
         }
      }
      if (!http_request) {
         alert('Cannot create XMLHTTP instance');
         return false;
      }

      return http_request;
}

var http = createRequestObject();
var add_http = createRequestObject();
var filter_http = createRequestObject();
var sort_http = createRequestObject();
var other_http = createRequestObject();

function sndReq(action) {
    http.open('get', action);
    http.onreadystatechange = handleResponse;
    http.send(null);
}

function sndPostReq(url, parameters) {
    //alert(url);
    //alert(parameters);
    http.open('POST', url);
	http.onreadystatechange = handleResponse;
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", parameters.length);
	http.setRequestHeader("Connection", "close");
    http.send(parameters);
}

function sndDeleteReq(action) {
    http.open('get', action);
    http.onreadystatechange = handleDelete;
    http.send(null);
}

/* Ajax Adding */
function sndAddReq(action) {
    http.open('get', action);
    http.onreadystatechange = setAdd;
    http.send(null);
}

function setAdd(){
	if(http.readyState == 4){
		add_http.open('get', ajax_file + "?action=add");
		add_http.onreadystatechange = handleAdd;
		add_http.send(null);
	}
}

/* Ajax Filtering */
function sndFilterReq(action) {
    http.open('get', action);
    http.onreadystatechange = setFilter;
    http.send(null);

}
function setFilter(){
    filter_http.open("get", ajax_file + "?action=filter");
    filter_http.onreadystatechange = handleFilter;
    filter_http.send(null);
}

/* Ajax Sorting */

function sndSortReq(action, table_num) {
    http.open('get', action);
    http.onreadystatechange = doNothing;
    http.send(null);

	sort_http.open('get', ajax_file + "?action=sort&table_num=" + table_num);
	sort_http.onreadystatechange = handleSort;
	sort_http.send(null);
}

function sndReqNoResponse(action) {
    http.open('get', action);
    http.onreadystatechange = doNothing;
    http.send(null);
}

function sndAjaxReq(action, handleFunction) {
    other_http.open('get', action);
    other_http.onreadystatechange = handleFunction;
    other_http.send(null);
}

function doNothing(){
	return 0;
}



/* other necessary js functions */

function setLoadingImage(table_num){
	document.getElementById('the_table_div' + table_num).innerHTML = loading_image_html;
}

function updateRowCount(){
	sndAjaxReq(ajax_file + "?action=getRowCount", handleRowCount);
}

function filterTable(obj, field, query_string){
	var filter_fields = getFormValues(document.getElementById('filter_form'), '');
    if (filter_fields != ''){
    	var req = this_page + "?" + filter_fields + "&" + query_string;
    	filterReq = "&" + filter_fields + "&" + query_string;
    }
    else{
    	var req = this_page + "?action=unfilter";
    	filterReq = "&action=unfilter";
    }

	// function to send the filter
	var func = function() {
		document.getElementById('the_table_div' + sort_table_num).innerHTML = loading_image_html;
		sndFilterReq(req);
	};

	// Check to see if there is already a timeout and if so...cancel it and create a new one
	if ( obj.zid ) {
		clearTimeout(obj.zid);
	}

	//set a timeout after typing in filter field (reduces number of calls to db)
	obj.zid = setTimeout(func,1000);
}

function confirmDelete(id, table, pk, message){
	if(confirm(message)) {
		ajax_deleteRow(id, table, pk);
	}
}
function deleteFile(field, id, message){
	if(confirm(message)) {
		location.href="?action=delete_file&field_name=" + field + "&id=" + id;
	}
}

function ajax_deleteRow(id, table, pk){
	var req = ajax_file + '?action=delete&id=' + id + '&table=' + table + '&pk=' + pk;
	sndDeleteReq(req);
}

//for handling all ajax editing
//TODO: make function name less generic
function handleResponse() {
    if(http.readyState == 4){

        var return_string = http.responseText;

        //if there's an error in the update
        if (return_string.substring(0,5) == 'error'){
            var broken_string = return_string.split("|");
            var id = broken_string[1];
            var old_value = broken_string[2];

            //only enter an alert if you want to. we removed because so many people compained
            //window.alert('No changes made to cell.');

            //display the display section, fill it with prior content
            document.getElementById(id+'_show').innerHTML = old_value;
            document.getElementById(id+'_show').style.display = '';
            //hide editing and saving sections
            document.getElementById(id+'_edit').style.display = 'none';
            document.getElementById(id+'_save').style.display = 'none';
        }

        else{
            var broken_string = return_string.split("|");
            var id = broken_string[0];
            var replaceText = myStripSlashes(broken_string[1]);

			//display the display section, fill it with new content
			if (replaceText != "{selectbox}"){
				if (replaceText != null){
					document.getElementById(id+'_show').innerHTML = replaceText;
				}
				else{
					document.getElementById(id+'_show').innerHTML = "";
				}
			}
			else{
				var the_selectbox = document.getElementById(id);
				document.getElementById(id+'_show').innerHTML = the_selectbox.options[the_selectbox.selectedIndex].text;
			}
            document.getElementById(id+'_show').style.display = '';
            //hide editing and saving sections
            document.getElementById(id+'_edit').style.display = 'none';
            document.getElementById(id+'_save').style.display = 'none';
        }
    }
}

function handleDelete() {
	if(http.readyState == 4){
		var return_string = http.responseText;

		//if there's an error in the delete
		if (return_string.substring(0,5) == 'error'){
			var broken_string = return_string.split("|");
			var id = broken_string[1];
		}
		else{
			var id = return_string;
			$('#row_' + id).fadeOut('slow');
		}
	}
}

function handleAdd() {
	if(add_http.readyState == 4){
		var table_html = add_http.responseText;
		document.getElementById('the_table_div' + sort_table_num).innerHTML = table_html;
	}
}

function handleFilter() {
	if(filter_http.readyState == 4){
		var table_html = filter_http.responseText;

		document.getElementById('the_table_div' + sort_table_num).innerHTML = table_html;
	}
}

function handleSort() {
	if(sort_http.readyState == 4){
		var table_html = sort_http.responseText;
		document.getElementById('the_table_div' + sort_table_num).innerHTML = table_html;
	}
}

function handleRowCount(){
	if(other_http.readyState == 4){
		var row_count = other_http.responseText;
		document.getElementById('ajaxCRUD_RowCount').innerHTML = row_count;
	}
}

function changeSort(field_name, sort_direction, table_num){
	//this will also maintain the filtering when sorting
	sort_table_num = table_num;
	sortReq = "&sort_field=" + field_name + "&sort_direction=" + sort_direction;
	var req = this_page + "?table_num=" + table_num + sortReq + filterReq;

	sndSortReq(req, table_num);
	return false;
}

function pageTable(params, table_num){
	var req = this_page + "?table_num=" + table_num + params + sortReq + filterReq;

	document.getElementById('the_table_div' + sort_table_num).innerHTML = loading_image_html;
	sndSortReq(req, table_num);
	return false;
}

function getFormValues(fobj,valFunc) {

	var str = "";
	var valueArr = null;
	var val = "";
	var cmd = "";
	var element_type;
	for(var i = 0;i < fobj.elements.length;i++) {
		element_type = fobj.elements[i].type;

		if (element_type == 'text' || element_type == 'textarea'){
			if(valFunc) {
				//use single quotes for argument so that the value of
				//fobj.elements[i].value is treated as a string not a literal
				cmd = valFunc + "(" + 'fobj.elements[i].value' + ")";
				val = eval(cmd)
			}

			str += fobj.elements[i].name + "=" + escape(fobj.elements[i].value) + "&";
		}
		else if(element_type == 'select-one'){
			str += fobj.elements[i].name + "=" + fobj.elements[i].options[fobj.elements[i].selectedIndex].value + "&";
		}
	}

	str = str.substr(0,(str.length - 1));
	return str;
}

function clearForm(formIdent){
	if(formIdent!='noclear')
	{
		var form, elements, i, elm;
		form = document.getElementById ? document.getElementById(formIdent) : document.forms[formIdent];
	
		if (document.getElementsByTagName){
			elements = form.getElementsByTagName('input');
			for( i=0, elm; elm=elements.item(i++); ){
				if (elm.getAttribute('type') == "text"){
					elm.value = '';
				}
			}
			elements = form.getElementsByTagName('select');
			for( i=0, elm; elm=elements.item(i++); ){
				elm.options.selectedIndex=0;
			}
			elements = form.getElementsByTagName('textarea');
			for( i=0, elm; elm=elements.item(i++); ){
				elm.value = '';
			}
		}
		else{
			elements = form.elements;
			for( i=0, elm; elm=elements[i++]; ){
				if (elm.type == "text"){
					elm.value ='';
				}
			}
		}
	}
}
//function clearError by Stéphane Delaune
function clearError(){
		var obj = document.getElementById('crud');
		var old = document.getElementById('cruderror');
		obj.removeChild(old);
}

/*
 * This function is to not allow non-numeric values for fields with an INT or DECIMAL datatype
 * Colaborator Juan David Ramírez
 * fenixjuano@gmail.com
 */
function fn_validateNumeric(evento, elemento, dec) {
    var valor=elemento.value;
    var charWich=evento.which;
    var charCode=evento.keyCode;
    if(charWich==null){
        charWich=charCode;
    }
    if ( (charWich>=48 && charWich<=57) || charCode==8 || charCode==39 || charCode==37 || charCode==46 || charWich==46 || charWich==13) {
        if(dec=="n" && charWich == 46){
            return false;
        }
        else{
            if(valor.indexOf('.')!=-1 && charWich==46){
                return false;
            }
        }
        return true;
    }
    else{
        return false;
    }
}


function myAddSlashes(str) {
    str=str.replace(/\"/g,'\\"');
    return str;
}

function myStripSlashes(str) {
    str=str.replace(/\\'/g,'\'');
    str=str.replace(/\\"/g,'"');
    return str;
}

var prior_class = '';
function hover(obj){
    obj.className='class_hover';
}

function unHover(obj){
    obj.className = '';
}

function setAllCheckboxes(str, ck) {
	var ckboxes = document.getElementsByName(str);
	for (var i=0; i < ckboxes.length; i++){
		if (ckboxes[i].checked == ck) {
			ckboxes[i].checked = ck;
			ckboxes[i].click();
		}
	}
}

//I don't know why javascript doesn't have this function built into the language!
Array.prototype.findIndex = function(value){
	var ctr = "";
	for (var i=0; i < this.length; i++) {
		// use === to check for Matches. ie., identical (===), ;
		if (this[i] == value) {
			return i;
		}
	}
	return ctr;
};

if('function' != typeof Array.prototype.splice) {
	Array.prototype.splice = function(s, dC) {
		s = +s || 0;
		var a = [],
		n = this.length,
		nI = Math.min(arguments.length - 2, 0), i, j;
		s = (0 > s) ? Math.max(s + n, 0) : Math.min(s, n);
		dC = Math.min(Math.max(+dC || 0, 0), n - s);
		for(i = 0; i < dC; ++i) {a[i] = this[s + i];}
		if(nI < dC) {
			for(i = s, j = n - dC; i < j; ++i) {
				this[i + nI] = this[i + dC];
			}
		} else if(nI > dC) {
			for(i = n - 1, j = s + dC; i >= j; --i) {
				this[i + nI - dC] = this[i];
			}
		}
		for(i = s, j = 2; j < nI; ++i, ++j) {this[i] = arguments[j];}
		this.length = n - dC + nI;
		return a;
	};
}