<?php
/*CRUD-it by Stéphane Delaune
This program is free software. You can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License. */
/************************************************************************/
/* ajaxCRUD.class.php v4.0  modified by Stéphane Delaune for CRUD-it project */
/* ===========================                                          */
/* Copyright (c) 2008 by Loud Canvas Media (arts@loudcanvas.com)        */
/* http://www.loudcanvas.com                                            */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

// ::Use::
// Create an ajaxCRUD object.
// $table = new ajaxCRUD(name of item, table name, primary key);
// Example:
// $tblFAQ = new ajaxCRUD("FAQ", "tblFAQ", "pkFAQID");
// $tblFAQ->showTable();
//
// Note: !! Your table must have AUTO_INCREMENT enabled for the primary key !!
// Note: !! Your version of mySQL must support string->INT conversion (thus "1" = 1) and "" is a NULL value !!

class ajaxCRUD{

    var $ajaxcrud_root;
    var $ajax_file;
    var $css_file;
    var $css = true; 	//indicates a css spredsheet WILL be used
    var $add = true;    //adding is ok

    var $doActionOnShowTable; //boolean var. When true and showTable() is called, doAction() is also called. turn off when you want to only have a table show in certain conditions but CRUD operations can take place on the table "behind the scenes"

    var $item_plural;
	var $item;

	var $db_table;
	var $db_table_pk;
	var $db_main_field;
    var $row_count;

    var $table_html; //the html for the table (to be modified on ADD via ajax)

    var $cellspacing;

    var $showPaging = true;
    var $limit; // limit of rows to display on one page. defaults to 50
    var $sql_limit;

    var $filtered_table = false; //the table is by default unfiltered (eg no 'where clause' on it)
    var $ajaxFilter_fields = array(); //array of fields that can be are filtered by ajax (creates a textbox at the top of the table)
    var $ajaxFilterBoxSize = array(); //array (sub fieldname) holding size of the input box

    //all fields in the table
    var $fields = array();
	var $field_count;

    //field datatypes
    var $field_datatype = array(); //$field_datatype[field] = datatype

    //allow delete of fields | boolean variable set to true by default
    var $delete;

    //defines if the add function uses ajax
    var $ajax_add = true;

    //defines if the class allows you to edit all fields
    var $ajax_editing = true;

    //the fields to be displayed
    var $display_fields = array();

    //the fields to be inputted when adding a new entry (90% time will be all fields). can be changed via the omitAddField method
    var $add_fields = array();
    var $add_form_top = FALSE; //the add form (by default) is below the table. use displayAddFormTop() to bring it to the top

    //the fields which are displayed, but not editable
    var $uneditable_fields = array();

	var $sql_where_clause;
    var $sql_where_clauses = array(); //array for IF there is more than one where clause
	var $sql_order_by;
    var $num_where_clauses;

    var $on_add_specify_primary_key = false;

    //table border - default is off: 0
    var $border;

    //array containing values for a button next to the "go back" button at the bottom. [0] = value [1] = url [2] = extra tags/javascript
    var $bottom_button = array();

    //array with value being the url for the buttom to go to (passing the id) [0] = value [1] = url
    var $row_button = array();

    ################################################
    #
    # The following are parallel arrays to help in the definition of a defined db relationship
    #
    ################################################

    //values will be the name(s) of the foreign key(s) for a category table
	var $db_table_fk_array = array();

    //values will be the name(s) of the category table(s)
	var $category_table_array = array();

    //values will be the name(s) of the primary key for the category table(s)
	var $category_table_pk_array = array();

    //values will be the name(s) of the field to return in the category table(s)
	var $category_field_array = array();

    //values will be the (optional) name of the field to sort by in the category table(s)
    var $category_sort_field_array = array();

    //for dropdown (to make an empty box). (format: array[field] = true/false)
    var $category_required = array();

    // allowable values for a field. the key is the name of the field
    var $allowed_values = array();

	// "on" and "off" values for a checkbox. the key is the name of the field
    var $checkbox = array();

	// holds the field names of columns that will have a "check all" checkbox
	var $checkboxall = array();

    //values to be set to a particular field when a new row is added. the array is set as $field_name => $add_value
    var $add_values = array();

    //destination folder to be set for a particular field that allows uploading of files. the array is set as $field_name => $destination_folder
    var $file_uploads = array();
    var $file_upload_info = array(); //array[$field_name][destination_folder] and array[$field_name][relative_folder]
    var $filename_append_field = "";

    //array dictating that "dropdown" fields do not show dropdown (but text editor) on edit (format: array[field] = true/false);
    //used in defineAllowableValues function
    var $field_no_dropdown = array();

    //array holding the (user-defined) function to format a field with on display (format: array[field] = function_name);
    //used in formatFieldWithFunction function
    var $format_field_with_function = array();

    var $onAddExecuteCallBackFunction;
    var $onFileUploadExecuteCallBackFunction;
    var $onDeleteFileExecuteCallBackFunction;

    //(if true) put a checkbox before each row
    var $showCheckbox;

    var $loading_image_html;

    var $sort_direction; //used when sorting the table via ajaxé

    ################################################
    #
    # displayAs array is for linking a particular field to the name that displays for that field
    #
    ################################################

    //the indexes will be the name of the field. the value is the displayed text
    var $displayAs_array = array();

    //height of the textarea for certain fields. the index is the field and the value is the height
    var $textarea_height = array();
	
	################################################
    #
    # The following are for utf8 encoding, files uploading's options, language's options, Form validation functions, align, titles, comments, TinyMCE
    # author : Stéphane Delaune
    ################################################
	var $utf8_encode_unicode_urldecode_onupdate=false; //if true, active utf8 and unicode_urldecode on update
	var $utf8_encode_unicode_urldecode_onadd=false; //if true, active utf8 and unicode_urldecode on add
	var $unlinkfile=true;//if true, erase files on delete
	var $add_timestamp_to_file=false;//if true, add timestamp at the beginning of the name of file for uniq filenames
	var $accepfiletype=array();
	var $maxfilesize=array();
	var $filename_rename=array();
	var $messagearray=array();
	var $validValues=array();
	var $alignement;
	var $cancelfloat = TRUE;
	var $titleCrudTable;
	var $titleFilterForm;
	var $addFormComments=array();
	var $loadtinyMCE=array();



	// Constructor
    function ajaxCRUD($item, $db_table, $db_table_pk, $ajaxcrud_root = "") {

        //global variable - for allowing multiple ajaxCRUD tables on one page
        global $num_ajaxCRUD_tables_instantiated;
        if ($num_ajaxCRUD_tables_instantiated === "") $num_ajaxCRUD_tables_instantiated = 0;
        $this->showCheckbox     = false;
        $this->ajaxcrud_root    = $ajaxcrud_root;
        $this->ajax_file        = "ajax_ajaxCRUD.php";

		$this->item 			= $item;
		$this->item_plural		= $item . "s";

		$this->db_table			= $db_table;
		$this->db_table_pk		= $db_table_pk;

		$this->fields 			= $this->getFields($db_table);
		$this->field_count 		= count($this->fields);

        //by default paging is turned on; limit is 50
        $this->showPaging       = true;
        $this->limit            = 50;
        $this->num_where_clauses = 0;
        $this->delete           = true;
		$this->unlinkfile		= true;
		$this->add_timestamp_to_file = false;
        $this->add              = true;

        //assumes the primary key is auto incrementing
        $this->primaryKeyAutoIncrement = true;
		$this->alignement='center';
		//for utf8 encoding
		$this->utf8_encode_unicode_urldecode_onupdate = false;
		$_SESSION['utf8_encode_unicode_urldecode_onupdate']=$this->utf8_encode_unicode_urldecode_onupdate;
		$this->utf8_encode_unicode_urldecode_onadd = false;
		
		//init language
		$this->messagearray=array("error_no_fields"=>"No fields in this table!", 
		"error_doNotDisplay"=>"Error in your doNotDisplay function call. There is no field named ", 
		"error_omitAddField"=>"Error in your omitAddField function call. There is no field named ", 
		"error_inthetable"=>" in the table ", 
		"deleted"=>" Deleted", 
		"couldnotbedeleted"=>" could not be deleted. Please try again.", 
		"added"=>" Added", 
		"couldnotbeadded"=>" could not be added. Please try again.", 
		"allfieldsomitted"=>"All fields were omitted.", 
		"upsuccess"=>"File Uploaded Sucessfully.", 
		"uperror"=>"There was an error uploading your file (or none was selected or the file has the wrong extension).", 
		"delfilesuccess"=>"File Deleted Sucessfully.", 
		"delfileerror"=>"There was an error deleting your file.", 
		"norow"=>"This table has no rows.", 
		"edit"=>"edit", 
		"addfile"=>"Add File", 
		"delete"=>"delete", 
		"add"=>"Add", 
		"newrow"=>"New ", 
		"cancel"=>"Cancel", 
		"upload"=>"Upload", 
		"confirmdelete"=>"Are you sure you want to delete this item from the database? This cannot be undone.", 
		"confirmdeletefile"=>"Are you sure you want to delete this file? This cannot be undone.",
		"field_data_not_allowed"=>"A field is not valid : ");
		
		$this->accepfiletype=array();
		$this->maxfilesize=array();
		$this->validValues=array();
		$this->titleCrudTable="";
		$this->titleFilterForm="";
		$this->addFormComments=array();
		$this->loadtinyMCE=array();

        $this->border           = 0;
        $this->css              = true;
        $this->ajax_add         = true;

        $this->doActionOnShowTable = true;

        $this->loading_image_html = "<div style='text-align:center'><br /><br  /><img src=\'css/loading.gif\'><br /><br /></div>"; //changed via setLoadingImageHTML()

        $this->onAddExecuteCallBackFunction         = '';
        $this->onFileUploadExecuteCallBackFunction  = '';
        $this->onDeleteFileExecuteCallBackFunction  = '';

        //don't allow primary key to be editable
        $this->uneditable_fields[] = $this->db_table_pk;

        $this->display_fields   = $this->fields;
        $this->add_fields       = $this->fields;

		if ($this->field_count == 0){
			$error_msg[] = $this->messagearray["error_no_fields"];
			echo_msg_box();
			exit();
		}

		//for filtering if there is a request parameter
		$count_filtered = 0;
		$action = $_REQUEST['action'];
        foreach ($this->fields as $field){
			if ($_REQUEST[$field] != '' && ($action != 'add' && $action != 'delete' && $action != 'update' && $action != 'upload' && $action != 'delete_file')){
				$filter_field = $field;
				$filter_value = $_REQUEST[$field];
				$filter_where_clause = "WHERE $filter_field LIKE \"%" . $filter_value . "%\"";
				$this->addWhereClause($filter_where_clause);
				$this->filtered_table = true;
                $count_filtered++;
			}
		}
        if ($count_filtered > 0){
            $this->filtered_table;
        }
        else{
            $this->filtered_table = false;
        }


		return true;
	}
	
	//function changelanguage
	//author : Stéphane Delaune
	function changelanguage($lang='en'){
		if($lang=='fr')
		{
			$this->messagearray=array("error_no_fields"=>"Il n'y a pas de champ dans cette table!", 
			"error_doNotDisplay"=>"Erreur dans votre appel à la fonction doNotDisplay. Il n'y a pas de champ nommé ", 
			"error_omitAddField"=>"Erreur dans votre appel à la fonction omitAddField. Il n'y a pas de champ nommé ", 
			"error_inthetable"=>" dans la table ", 
			"deleted"=>" supprimé", 
			"couldnotbedeleted"=>" ne peut pas être supprimé. Veuillez essayer à nouveau.", 
			"added"=>" ajouté", 
			"couldnotbeadded"=>" ne peut pas être ajouté. Veuillez essayer à nouveau.", 
			"allfieldsomitted"=>"Tous les champs sont vides.", 
			"upsuccess"=>"Fichier envoyé avec succès.", 
			"uperror"=>"Une erreur s'est produite lors de l'envoi du fichier (ou aucun fichier n'était sélectionné ou le fichier n'a pas la bonne extension).", 
			"delfilesuccess"=>"Fichier supprimé avec succès.", 
			"delfileerror"=>"Une erreur s'est produite lors de la suppression de votre fichier.", 
			"norow"=>"Cette table n'a pas d'entrée", 
			"edit"=>"modifier", 
			"addfile"=>"Ajouter un fichier", 
			"delete"=>"supprimer", 
			"add"=>"Ajouter", 
			"newrow"=>"Nouvelle entrée : ", 
			"cancel"=>"Annuler", 
			"upload"=>"Envoyer", 
			"confirmdelete"=>"Etes-vous sûre de vouloir supprimer cette entrée de la base de données ? Cette action est définitive.", 
			"confirmdeletefile"=>"Etes-vous sûre de vouloir supprimer ce fichier ? Cette action est définitive.",
			"field_data_not_allowed"=>"champ(s) invalide(s) : ");
		}
    }
	
	//function defineValidValues
	//author : Stéphane Delaune
	function defineValidValues($field, $type, $empty)
	{
		$this->validValues[$field]['type']=$type;
		$this->validValues[$field]['empty']=$empty;
	}
	
	//function defineValidValues
	//author : Stéphane Delaune
	function load_tinyMCE($field, $theme)
	{
		$this->loadtinyMCE[$field]=$theme;
	}
	
	//function setTitleCrudTable
	//author : Stéphane Delaune
	function setTitleCrudTable($title)
	{
		$this->titleCrudTable=$title;
	}
	//function setTitleFilterForm
	//author : Stéphane Delaune
	function setTitleFilterForm($title)
	{
		$this->titleFilterForm=$title;
	}
	//function disableCancelFloat
	//author : Stéphane Delaune
	function disableCancelFloat(){
    	$this->cancelfloat = FALSE;
    }
	//function addAddFormComments
	//author : Stéphane Delaune
	function addAddFormComments($field, $comment)
	{
		$this->addFormComments[$field]=$comment;
	}
	
	function setAjaxFile($ajax_file){
        $this->ajax_file = $ajax_file;
    }

    function turnOffAjaxADD(){
        $this->ajax_add = false;
    }

    function turnOffAjaxEditing(){
        $this->ajax_editing = false;
    }

    function turnOffPaging($limit = ""){
        $this->showPaging = false;
        if ($limit != ''){
            $this->sql_limit = " LIMIT $limit";
        }
    }

    function setCSSFile($css_file){
        $this->css_file = $css_file;
    }

    function setLoadingImageHTML($html){
        $this->loading_image_html = $html;
    }

    function addTableBorder(){
        $this->border = 1;
    }

    function addAjaxFilterBox($field_name){
        $this->ajaxFilter_fields[] = $field_name;
        $this->setAjaxFilterBoxSize($field_name, 10);
    }

    function setAjaxFilterBoxSize($field_name, $size){
        $this->ajaxFilterBoxSize[$field_name] = $size;
    }

    function addAjaxFilterBoxAllFields(){
        //unset($this->ajaxFilter_fields);
        foreach ($this->display_fields as $field){
            $this->addAjaxFilterBox($field);
        }
    }

    function displayAddFormTop(){
    	$this->add_form_top = TRUE;
    }

    function addWhereClause($sql_where_clause){
        $this->num_where_clauses++;

        $this->sql_where_clauses[] = $sql_where_clause;

        if ($this->num_where_clauses <= 1){
            $this->sql_where_clause = " " . $sql_where_clause;
        }
        else{
            foreach($this->sql_where_clauses as $where_clause){
                $new_where = str_replace("WHERE", "AND", $where_clause);
                $this->sql_where_clause .= " $new_where ";
            }
        }

	}

	function addOrderBy($sql_order_by){
		$this->sql_order_by = " " . $sql_order_by;
	}

    function formatFieldWithFunction($field, $function_name){
        $this->format_field_with_function[$field] = $function_name;
    }

    function defineRelationship($fkCategoryID, $category_table, $category_table_pk, $category_field_name, $category_sort_field = "", $category_required = "1"){

        $this->db_table_fk_array[]          = $fkCategoryID;
        $this->category_table_array[]       = $category_table;
        $this->category_table_pk_array[]    = $category_table_pk;
        $this->category_field_array[]       = $category_field_name;
        $this->category_sort_field_array[]  = $category_sort_field;

        //make the relationship required for the field
        if ($category_required == "1"){
            $this->category_required[$fkCategoryID] = TRUE;
        }
    }

    function relationshipFieldOptional(){
        $this->cat_field_required = FALSE;
    }

	function defineAllowableValues($field, $array_values, $onedit_textbox = FALSE){
		//array with the setup [0] = value [1] = display name (both the same)
		$new_array = array();

		foreach($array_values as $array_value){
			if (!is_array($array_value)){
                //a two-dimentential array --> set both the value and dropdown text to be the same
                $new_array[] = array(0=> $array_value, 1=>$array_value);
            }
            else{
                //a 2-dimentential array --> value and dropdown text are different
                $new_array[] = $array_value;
            }
		}

		if ($onedit_textbox != FALSE){
			$this->field_no_dropdown[$field] = TRUE;
		}

		$this->allowed_values[$field] = $new_array;
	}

	function defineCheckbox($field, $value_on="1", $value_off="0"){
		$new_array = array($value_on, $value_off);

		$this->checkbox[$field] = $new_array;
	}

	function showCheckboxAll($field, $display_data) {
		$this->checkboxall[$field] = $display_data;
	}

    function displayAs($field, $the_field_name){
        $this->displayAs_array[$field] = $the_field_name;
    }

    function setTextareaHeight($field, $height){
        $this->textarea_height[$field] = $height;
    }

    function setLimit($limit){
        $this->limit = $limit;
    }

    function getRowCount(){
        if ($_SESSION['row_count'] == ""){
        	$count = q1("SELECT COUNT(*) FROM " . $this->db_table . $this->sql_where_clause . $this->sql_order_by);
        }
        else{
        	$count = $_SESSION['row_count'];
        }
        return "<span id='ajaxCRUD_RowCount'>" . $count . "</span>";
    }

    function getTotalRowCount(){
        $count = q1("SELECT COUNT(*) FROM " . $this->db_table);
        return $count;
    }

	function omitField($field_name){
        $key = array_search($field_name, $this->display_fields);

        if ($this->fieldInArray($field_name, $this->display_fields)){
            unset($this->display_fields[$key]);
        }
        else{
            $error_msg[] = $this->messagearray["error_doNotDisplay"]."<b>".$field_name."</b>".$this->messagearray["error_inthetable"]."<b>" . $this->db_table . "</b>";
        }
    }

    function omitAddField($field_name){
        $key = array_search($field_name, $this->add_fields);

        if ($key !== FALSE){
            unset($this->add_fields[$key]);
        }
        else{
            $error_msg[] = $this->messagearray["error_omitAddField"]."<b>".$field_name."</b>".$this->messagearray["error_inthetable"]."<b>" . $this->db_table . "</b>";
        }
    }

    function omitFieldCompletely($field_name){
        $this->omitField($field_name);
        $this->omitAddField($field_name);
    }

    function addValueOnInsert($field_name, $insert_value){
        //if the value is NOW() then it's a date field or if its an integer --> otherwise put quotes around it.
        //if ($insert_value != "NOW()" && !is_int($insert_value)){
        //    $insert_value = "\"" . $insert_value . "\"";
        //}
        $this->add_values[] = array(0 => $field_name, 1 => $insert_value);
    }

    function onAddExecuteCallBackFunction($function_name){
        $this->onAddExecuteCallBackFunction = $function_name;
        $this->ajax_add = false;
    }

    function onFileUploadExecuteCallBackFunction($function_name){
        $this->onFileUploadExecuteCallBackFunction = $function_name;
    }

    function onDeleteFileExecuteCallBackFunction($function_name){
        $this->onDeleteFileExecuteCallBackFunction = $function_name;
    }

    function primaryKeyNotAutoIncrement(){
        $this->primaryKeyAutoIncrement = false;
    }
	
	//function active_utf8_encode_unicode_urldecode_onupdate
	//author : Stéphane Delaune
	function active_utf8_encode_unicode_urldecode_onupdate(){
        $this->utf8_encode_unicode_urldecode_onupdate = true;
		$_SESSION['utf8_encode_unicode_urldecode_onupdate']=$this->utf8_encode_unicode_urldecode_onupdate;
    }
	//function active_utf8_encode_unicode_urldecode_onadd
	//author : Stéphane Delaune
	function active_utf8_encode_unicode_urldecode_onadd(){
        $this->utf8_encode_unicode_urldecode_onadd = true;
    }
	
	//function define_accept_filetype
	//author : Stéphane Delaune
	function define_accept_filetype($fieldname, $allowablefiletype){
        $this->accepfiletype[$fieldname] = $allowablefiletype;
		//print_r($this->accepfiletype);
    }
	
	//function maxUploadFilesize
	//author : Stéphane Delaune
	function maxUploadFilesize($fieldname, $maxSize){
        $this->maxfilesize[$fieldname] = $maxSize;
		//print_r($this->accepfiletype);
    }

    function setFileUpload($field_name, $destination_folder, $relative_folder = ""){
        //put values into array
        $this->file_uploads[] = $field_name;
        $this->file_upload_info[$field_name][destination_folder] = $destination_folder;
        $this->file_upload_info[$field_name][relative_folder] = $relative_folder;

        //the filenames that are saved are not editable
        $this->disallowEdit($field_name);

        //have to add the row via POST now
        $this->ajax_add = false;
    }

    function appendUploadFilename($append_field){
        $this->filename_append_field = $append_field;
    }
	
	//function renameUploadFilename
	//author : Stéphane Delaune
	function renameUploadFilename($fieldname, $append_fields, $separ="_", $tolower=false){
        $this->filename_rename[$fieldname]['fields'] = $append_fields;
		$this->filename_rename[$fieldname]['separ'] = $separ;
		$this->filename_rename[$fieldname]['tolower'] = $tolower;
    }
	
	//function setAlignement
	//author : Stéphane Delaune
	function setAlignement($alignement){
        $this->alignement = $alignement;
		//print_r($this->accepfiletype);
    }

    function omitPrimaryKey(){

        //99% time it'll be in key 0, but just in case do search
        $key = array_search($this->db_table_pk, $this->display_fields);
        unset($this->display_fields[$key]);
    }

    function insertHeader($ajax_file){

        if ($this->css_file == ''){
            $this->css_file = 'default.css';
        }


		/* Load Javascript dependencies */
		echo "<script type=\"text/javascript\" src=\"jquery1.4.2.min.js\"></script>\n"; //per release 8, using jquery instead of protoculous
        echo "<script src=\"" . $this->ajaxcrud_root . "javascript_functions.js\" type=\"text/javascript\"></script>\n";
        echo "<link href=\"" . $this->ajaxcrud_root . "css/ajaxcrud.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
        echo "
            <script>\n
                ajax_file = \"$this->ajax_file\"; \n
                this_page = \"" . $_SERVER['PHP_SELF'] . "\"; \n
                loading_image_html = \"$this->loading_image_html\"; \n
            </script>\n";

        //are we even using a stylesheet? (it can be turned off)
        if ($this->css){
        	echo "<link href=\"" . $this->ajaxcrud_root . "css/" . $this->css_file ."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
        }

		return true;
	}

    function disallowEdit($field){
        $this->uneditable_fields[] = $field;
    }

    function disallowDelete(){
        $this->delete = false;
    }
	//function disallowUnlinkfile
	//author : Stéphane Delaune
	function disallowUnlinkfile(){
        $this->unlinkfile = false;
    }
	//function allowAdd_timestamp_to_file
	//author : Stéphane Delaune
	function allowAdd_timestamp_to_file(){
        $this->add_timestamp_to_file = true;
    }

    function disallowAdd(){
        $this->add = false;
    }

    function addButton($value, $url, $tags = ""){
        $this->bottom_button = array(0 => $value, 1 => $url, 2 => $tags);
    }

    function addButtonToRow($value, $url, $attach_params = "", $javascript_tags = ""){
        $this->row_button[] = array(0 => $value, 1 => $url, 2 => $attach_params, 3 => $javascript_tags);
    }

    function onAddSpecifyPrimaryKey(){
        $this->on_add_specify_primary_key = true;
    }

    function doCRUDAction(){
        if ($_REQUEST['action'] != ''){
            $this->doAction($_REQUEST['action']);
        }
    }

	function doAction($action){

		global $error_msg;
		global $report_msg;

		$item = $this->item;

		if ($action == 'delete' && $_REQUEST['id'] != ''){
			$delete_id = $_REQUEST['id'];
            $success = qr("DELETE FROM $this->db_table WHERE $this->db_table_pk = \"$delete_id\"");
			if ($success){
				$report_msg[] = $item.$this->messagearray["deleted"];
			}
			else{
				$error_msg[] = $item.$this->messagearray["couldnotbedeleted"];
			}
		}//action = delete

		#adding new item (via traditional way, non-ajax -- this is the ONLY way files can be uploaded with ajaxCRUD)
		if ($action == 'add'){
            //this if condition is so MULTIPLE ajaxCRUD tables can be used on the same page.
            if ($_REQUEST['table'] == $this->db_table){
			
                //for sql insert statement
                $submitted_values = array();

                //for callback function (if defined)
                $submitted_array = array();

                //this new row has (a) file(s) coming with it
                $uploads_on = $_REQUEST['uploads_on'];
                if ($uploads_on == 'true' && $_FILES){
                    $uploads_on = true;
                }
				
				$booldataok=true;
				$datanotok='';
                foreach($this->fields as $field){
				
				
					if (array_key_exists($field, $this->validValues))
					{
						if(!verifdata($_REQUEST[$field], $this->validValues[$field]['type'],$this->validValues[$field]['empty']))
						{
							$booldataok=false;
							$datanotok.=$field.' ';
						}
					}

                    $submitted_value_cleansed = "";
                    if ($_REQUEST[$field] == ''){
                        if ($this->fieldIsInt($this->getFieldDataType($field)) || $this->fieldIsDecimal($this->getFieldDataType($field))){
                            $submitted_value_cleansed = 0;
                        }
                    }
                    else{
                        $submitted_value_cleansed = $_REQUEST[$field];
                    }


                    $submitted_values[] = str_replace("'","''", $submitted_value_cleansed);
                    //also used for callback function
                    $submitted_array[$field] = str_replace("'","''", $submitted_value_cleansed);
                }

				if($booldataok)
				{
	                //get rid of the primary key in the fields column
	                if (!$this->on_add_specify_primary_key){
	                    unset($submitted_values[0]);    //assumes the primary key is the FIRST field in the array
	                }

	                //for adding values to the row which were not in the ADD row table - but are specified by ADD on INSERT
	                if (count($this->add_values) > 0){
	                    foreach ($this->add_values as $add_value){
	                        $field_name = $add_value[0];
	                        $the_add_value = $add_value[1];

	                        if ($submitted_array[$field_name] == ''){
	                            $submitted_array[$field_name] = $the_add_value;
	                        }

	                        //reshuffle numeric indexed array
	                        unset($submitted_values);
	                        $submitted_values = array();
	                        foreach($submitted_array as $field){
	                            $submitted_values[] = $field;
	                        }

	                        //get rid of the primary key in the fields column
	                        if (!$this->on_add_specify_primary_key){
	                            unset($submitted_values[0]);    //assumes the primary key is the FIRST field in the array
	                        }

	                    }
	                }

	                //wrap each field in quotes
	                //$string_submitted_values = "\"" . implode("\",\"", $submitted_values) . "\"";
					$string_submitted_values = "'" . implode("','", $submitted_values) . "'";
					
	                //for getting datestamp of new row for mysql's "NOW" to work
	                $string_submitted_values = str_replace('"NOW()"', 'NOW()', $string_submitted_values);
					
					//les 2 lignes ci-dessous sont à commenter ou décommenter en fonction du fait que l'ajout d'entrée contenant des caractères utf-8 (é€) fonctionne ou non
					if ($this->utf8_encode_unicode_urldecode_onadd)
					{
						$string_submitted_values = utf8_encode($string_submitted_values);
						$string_submitted_values = unicode_urldecode($string_submitted_values);
					}

	                //print_r($submitted_values);

	                if ($string_submitted_values != ''){
	                    if (!$this->on_add_specify_primary_key && $this->primaryKeyAutoIncrement){
	                        //don't allow the primary key to be inputted
	                        $fields_array_without_pk = $this->fields;
	                        unset($fields_array_without_pk[0]);   //assumes the primary key is the FIRST field in the array
	                        $string_fields_without_pk = implode(",", $fields_array_without_pk);
	                        $query = "INSERT INTO $this->db_table($string_fields_without_pk) VALUES ($string_submitted_values)";
	                    }
	                    else{
	                        if (!$this->primaryKeyAutoIncrement){
	                            $primary_key_value = q1("SELECT MAX($this->db_table_pk) FROM $this->db_table");
	                            if ($primary_key_value > 0) $primary_key_value++;
	                            $primary_key_value = $primary_key_value . ", ";
	                        }

	                        $string_fields_with_pk = implode(",", $this->fields);
	                        $query = "INSERT INTO $this->db_table($string_fields_with_pk) VALUES ($primary_key_value $string_submitted_values)";
	                    }
	                    $success = qr($query);

	                    if ($success){
							if(CONNECTWITHPDO==true)
							{
								$pdo = class_pdo::getInstance();
								$insert_id = $pdo->lastInsertId();
							}
							else
							{
								$insert_id = mysql_insert_id();
							}
							$_SESSION['noclearadd']=false;

	                        $report_msg[] = $item.$this->messagearray["added"];

	                        if ($uploads_on){
	                            foreach($this->file_uploads as $field_name){
	                                $file_dest  = $this->file_upload_info[$field_name][destination_folder];

	                                if ($_FILES[$field_name]['name'] != ''){
	                                    $this->uploadFile($insert_id, $field_name, $file_dest);
	                                }
	                            }
	                        }

	                        if ($this->onAddExecuteCallBackFunction != ''){
	                            $submitted_array[id] = $insert_id;
	                            $submitted_array[$this->db_table_pk] = $insert_id;
	                            call_user_func($this->onAddExecuteCallBackFunction, $submitted_array);
	                        }

	                    }
	                    else{
	                        $error_msg[] = $item.$this->messagearray["couldnotbeadded"];
							$_SESSION['noclearadd']=true;
	                    }
	                }
	                else{
	                    $error_msg[] = $this->messagearray["allfieldsomitted"];
						$_SESSION['noclearadd']=true;
	                }
				}
				else
				{
					$error_msg[] = $this->messagearray["field_data_not_allowed"].$datanotok;
					$_SESSION['noclearadd']=true;
				}
            }//if POST parameter 'table' == db_table
		}//action = add

        if ($action == 'upload' && $_REQUEST['field_name'] && $_REQUEST['id'] != ''){
            $update_id      = $_REQUEST['id'];
            $file_field     = $_REQUEST['field_name'];
            $upload_folder  = $this->file_upload_info[$file_field][destination_folder];

            $success = $this->uploadFile($update_id, $file_field, $upload_folder);

            if ($success){
                $report_msg[] = $this->messagearray["upsuccess"];
            }
            else{
                $error_msg[] = $this->messagearray["uperror"];
            }
			/*echo "<pre>";
			print_r($_SESSION['test1']);
			echo "</pre><pre>";
			print_r($_SESSION['test2']);
			echo "</pre>";*/
        }//action = upload

        if ($action == 'delete_file' && $_REQUEST['field_name'] && $_REQUEST['id'] != ''){
            $delete_id      = $_REQUEST['id'];
            $file_field     = $_REQUEST['field_name'];
            $upload_folder  = $_REQUEST['upload_folder'];

            $filename = q1("SELECT $file_field FROM $this->db_table WHERE $this->db_table_pk = $delete_id");
            $success = qr("UPDATE $this->db_table SET $file_field = \"\" WHERE $this->db_table_pk = $delete_id");

            if ($success){
                $file_dest  = $this->file_upload_info[$file_field][destination_folder];
				if($this->unlinkfile)
				{
                	unlink($file_dest . $filename);
				}
                $report_msg[] = $this->messagearray["delfilesuccess"];

                if ($this->onDeleteFileExecuteCallBackFunction != ''){
                    $delete_file_array = array();
                    $delete_file_array[id]        = $delete_id;
                    $delete_file_array[field]     = $file_field;
                    call_user_func($this->onDeleteFileExecuteCallBackFunction, $delete_file_array);
                }

            }
            else{
                $error_msg[] = $this->messagearray["delfileerror"];
            }

        }//action = delete_file

	}//doAction


    //a file must have been "sent"/posted for this to work
    function uploadFile($row_id, $file_field, $upload_folder){
		@$fileName  = $_FILES[$file_field]['name'];
		if (array_key_exists($file_field, $this->filename_rename))
		{
			$filenametemp='';
			$i=0;
			foreach($this->filename_rename[$file_field]['fields'] as $file_partname)
			{
				if($i>0){$filenametemp.=$this->filename_rename[$file_field]['separ'];}
				$filenametemp .= q1("SELECT $file_partname FROM $this->db_table WHERE $this->db_table_pk = $row_id");
				$i++;
			}
			$extension=strrchr($_FILES[$file_field]['name'],'.');
			$extension=substr($extension,1) ;
			if($extension!==FALSE)
			{
				$filenametemp .='.'.$extension;
			}
			$filenametemp = str_replace(array('à', 'â', 'ä', 'á', 'ã', 'å', 'î', 'ï', 'ì', 'í', 'ô', 'ö', 'ò', 'ó', 'õ', 'ø', 'ù', 'û', 'ü', 'ú', 'é', 'è', 'ê', 'ë', 'ç', 'ÿ', 'ñ'), array('a', 'a', 'a', 'a', 'a', 'a', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'e', 'e', 'e', 'e', 'c', 'y', 'n'), $filenametemp);
			if($this->filename_rename[$file_field]['tolower']==true){$filenametemp=strtolower($filenametemp);}
			$filenametemp=make_filename_safe($filenametemp);
			if(($filenametemp!='') and (substr($filenametemp, 0, 1)!='.'))
			{
				@$fileName=$filenametemp;
			}
		}		
		if($this->add_timestamp_to_file)
		{
			@$fileName  = time().$_FILES[$file_field]['name'];
		}
		
        @$tmpName   = $_FILES[$file_field]['tmp_name'];
        @$fileSize  = $_FILES[$file_field]['size'];
        @$fileType  = $_FILES[$file_field]['type'];
		
		$incr=0;
		$boolfiletypeok==false;
		if (array_key_exists($file_field, $this->accepfiletype))
		{
			foreach($this->accepfiletype[$file_field] as $type_auth)
			{
				if($_FILES[$file_field]['type']==$type_auth)
				{
					$boolfiletypeok=true;
				}
				$incr++;
			}
		}
		
		$boolfilesizeok=true;
		if (array_key_exists($file_field, $this->maxfilesize))
		{
			if(intval($this->maxfilesize[$file_field])<$fileSize)
			{
				$boolfilesizeok=false;
			}
		}
		
		
		
		
		
		if(($incr==0 or $boolfiletypeok==true) and $boolfilesizeok==true)
		{
			$new_filename = make_filename_safe($fileName);
			if ($this->filename_append_field != ""){
				if ($_REQUEST[$this->filename_append_field] != ''){
					$new_filename = $_REQUEST[$this->filename_append_field] . "_" . $new_filename;
				}
				else{
					if ($this->filename_append_field == $this->db_table_pk){
						$new_filename = $row_id . "_" . $new_filename;
					}
					else{
						@$db_value_to_append = q1("SELECT $this->filename_append_field FROM $this->db_table WHERE $this->db_table_pk = $row_id");
						if ($db_value_to_append != ""){
							$new_filename = $db_value_to_append . "_" . $new_filename;
						}
					}
	
				}
			}
	
			$destination = $upload_folder . $new_filename;
	
			$success = move_uploaded_file ($tmpName, $destination);
	
			if ($success){
				$update_success = qr("UPDATE $this->db_table SET $file_field = \"$new_filename\" WHERE $this->db_table_pk = $row_id");
	
				if ($this->onFileUploadExecuteCallBackFunction != ''){
					$file_info_array = array();
					$file_info_array[id]        = $row_id;
					$file_info_array[field]     = $file_field;
					$file_info_array[fileName]  = $new_filename;
					$file_info_array[fileSize]  = $fileSize;
					$file_info_array[fldType]   = $fldType;
					call_user_func($this->onFileUploadExecuteCallBackFunction, $file_info_array);
				}
	
			}
	
			if ($update_success){
				return true;
				//$report_msg[] = "File Uploaded.";
			}
			else{
				return false;
				//$error_msg[] = "There was an error uploading your file (or none was selected).";
			}
		}
		else
		{
			return false;

		}

    }

	function showTable(){

        global $error_msg;
        global $report_msg;
        global $warning_msg_displayed;
        global $num_ajaxCRUD_tables_instantiated;

        $num_ajaxCRUD_tables_instantiated++;

        /* call functions based on sorting [cancels out default sorting set by addOrderBy] */
        if ($_REQUEST['sort_field'] != ''){
            $sort_field = $_REQUEST['sort_field'];
            $user_sort_order_direction = $_REQUEST['sort_direction'];

            if ($user_sort_order_direction == 'asc'){
                $this->sort_direction = "desc";
            }
            else{
                $this->sort_direction = "asc";
            }
            $sort_sql = " ORDER BY $sort_field $this->sort_direction";
            $this->addOrderBy($sort_sql);
            $this->sorted_table = true;
        }


        //the HTML to display
        $top_html = "";     //top header stuff
        $table_html = "";   //for the html table itself
        $bottom_html = "";
        $add_html = "";     //for the add form

        $html = ""; //all combined

        if ( $num_ajaxCRUD_tables_instantiated == 1 ){
            //pull in the  css and javascript files
            $this->insertHeader($this->ajax_file);
        }

        if ($this->doActionOnShowTable){
            if ($_REQUEST['action'] != ''){
                $this->doAction($_REQUEST['action']);
            }
        }

		$item = $this->item;

		$top_html .= "<a name='ajaxCRUD' id='ajaxCRUD'></a>\n";

        if (count($this->ajaxFilter_fields) > 0){
			
			if($this->titleFilterForm!="")
			{
				$top_html .= "<h3 class=\"alignement\">".$this->titleFilterForm."</h3>";
			}
            $top_html .= "<form id=\"filter_form\">\n";
            //$top_html .= "<table cellspacing='5' align='".$this->alignement."'><tr>";
			$top_html .= "<table cellspacing='5'";
			if($this->alignement=='center'){$top_html .= " align='".$this->alignement."'";}
			$top_html .= "><tr>";

            foreach ($this->ajaxFilter_fields as $filter_field){
                $display_field = $filter_field;
                if ($this->displayAs_array[$filter_field] != ''){
                    $display_field = $this->displayAs_array[$filter_field];
                }

                $textbox_size = $this->ajaxFilterBoxSize[$filter_field];

                //if ($_REQUEST['Dealer'] != ''){
                //	$extra_query_params = "Dealer=" . htmlentities($_REQUEST['Dealer']);
                //}

                $filter_value = "";
                if ($_REQUEST[$filter_field] != ''){
                	$filter_value = $_REQUEST[$filter_field];
                }

                $top_html .= "<td><b>$display_field</b>: <input type=\"text\" size=\"$textbox_size\" name=\"$filter_field\" value=\"$filter_value\" onKeyUp=\"filterTable(this, '$filter_field', '$extra_query_params');\"></td>";
            }

			$top_html .= "</tr></table>\n";


            $top_html .= "</form>\n";
        }

		#############################################
		#
		# Begin code for displaying database elements
		#
		#############################################

		$select_fields = implode(",", $this->fields);

        $sql = "SELECT * FROM " . $this->db_table . $this->sql_where_clause . $this->sql_order_by;

        if ($this->showPaging){
            $pageid        = $_GET['pid'];//Get the pid value
            if(intval($pageid) == 0) $pageid  = 1;
            $Paging        = new paging();
            $total_records = $Paging->myRecordCount($sql);//count records
            $totalpage     = $Paging->processPaging($this->limit,$pageid);
            $rows          = $Paging->startPaging($sql);//get records in the database
            $links         = $Paging->pageLinks(basename($PHP_SELF));//1234 links
            unset($Paging);
        }
        else{
            $rows = q($sql . $this->sql_limit);
        }

        //$rows = q("SELECT * FROM " . $this->db_table");
		$row_count = count($rows);
        $this->row_count = $row_count;
        $_SESSION['row_count'] = $row_count;

        if ($row_count == 0){
            $report_msg[] = $this->messagearray["norow"];
        }

        #this is an optional function which will allow you to display errors or report messages as desired. comment it out if desired
        //only show the message box if it hasn't been displayed already
        if ($warning_msg_displayed == 0 || $warning_msg_displayed == ''){
            echo_msg_box();
        }

		$dropdown_array = array();

		foreach ($this->category_table_array as $key => $category_table){
            $category_field_name = $this->category_field_array[$key];
            $category_table_pk   = $this->category_table_pk_array[$key];

            $order_by = '';
            if ($this->category_sort_field_array[$key] != ''){
                $order_by = " ORDER BY " . $this->category_sort_field_array[$key];
            }

            $dropdown_array[] = q("SELECT $category_table_pk, $category_field_name FROM $category_table $order_by");
		}

        $top_html .= "<div id='the_table_div" . $num_ajaxCRUD_tables_instantiated . "'>\n";
		
		
        if ($row_count > 0){

            //$edit_word = "Edit";
            //if ($row_count == 0) $edit_word = "No";

            //$top_html .= "<h3>Edit " . $this->item_plural . "</h3>\n";

            //$table_html .= "<table align='".$this->alignement."' name='the_table' id='the_table' cellspacing='" . $this->cellspacing . "' border=" . $this->border . ">\n";
			if($this->titleCrudTable!="")
			{
				$table_html .= "<h3 class=\"alignement\">".$this->titleCrudTable."</h3>";
			}
			$table_html .= "<table";
			if($this->alignement=='center'){$table_html .= " align='".$this->alignement."'";}
			$table_html .= " name='the_table' id='the_table' cellspacing='" . $this->cellspacing . "' border=" . $this->border . ">\n";

            $table_html .= "<tr>\n";


            //for an (optional) checkbox
            if ($this->showCheckbox){
                $table_html .= "<th>&nbsp;</th>";
            }

            foreach ($this->display_fields as $field){
                $field_name = $field;
                if ($this->displayAs_array[$field] != ''){
                    $field = $this->displayAs_array[$field];
                }
					if (array_key_exists($field_name, $this->checkboxall)) {
						$table_html .= "<th><input type=\"checkbox\" name=\"$field_name" . "_checkboxall\" value=\"checkAll\" onClick=\"
							if (this.checked) {
								setAllCheckboxes('$field_name" . "_fieldckbox',false);
							} else {
								setAllCheckboxes('$field_name" . "_fieldckbox',true);
							}
							\">";

						if ($this->checkboxall[$field_name] == true) {
							$table_html .= "<a href='javascript:;' onClick=\"changeSort('" . $field_name . "', '$this->sort_direction', $num_ajaxCRUD_tables_instantiated);\" >" . $field . "</a>";
						}
						$table_html .= "</th>";
					} else {
						$table_html .= "<th><a href='javascript:;' onClick=\"changeSort('" . $field_name . "', '$this->sort_direction', $num_ajaxCRUD_tables_instantiated);\" >" . $field . "</a></th>";
					}
            }

            if ($this->delete || (count($this->row_button)) > 0){
                $table_html .= "<th>Action</th>\n";
            }

            $table_html .= "</tr>\n";

            $count = 0;
            $class = "odd";
            $attach_params = "";

            foreach ($rows as $row){
                $id = $row[$this->db_table_pk];

                $table_html .= "<tr class='$class' id=\"row_$id\" valign='top'>\n";

                if ($this->showCheckbox){
                    $checkbox_selected = "";
                    if ($id == $_REQUEST[$this->db_table_pk]) $checkbox_selected = " checked";
                    $table_html .= "<td><input type='checkbox' $checkbox_selected onClick=\"window.location ='" . $_SERVER['PHP_SELF'] . "?$this->db_table_pk=$id'\" /></td>";
                }

                foreach($this->display_fields as $field){
                    $cell_data = $row[$field];

                    //for adding a button via addButtonToRow (using "all" as the "attach params" optional third parameter)
                    if (count($this->row_button) > 0){
                        $attach_params .= "&" . $field . "=" . $cell_data;
                    }

                    if ($this->format_field_with_function[$field] != ''){
                        $cell_data = call_user_func($this->format_field_with_function[$field], $cell_data);
                    }

                    //try to find a reference to another table relationship
                    $found_category_index = array_search($field, $this->db_table_fk_array);

                    //don't allow uneditable fields (which usually includes the primary key) to be editable
                    if ( ($this->fieldInArray($field, $this->uneditable_fields) || (!$this->ajax_editing)) && $found_category_index == ''){
                        $table_html .= "<td>";

                        $key = array_search($field, $this->display_fields);

                        if ($this->fieldInArray($field, $this->file_uploads)){

                            //a file exists for this field
                            if ($cell_data != ''){
                                $file_link = $this->file_upload_info[$field][relative_folder] . $row[$field];
                                $file_dest = $this->file_upload_info[$field][destination_folder];

                                $table_html .= "<span id='text_" . $field . $id . "'><a target=\"_new\" href=\"$file_link\">" . $cell_data . "</a> (<a style=\"font-size: 9px;\" href=\"javascript:\" onClick=\"document.getElementById('file_$field$id').style.display = ''; document.getElementById('text_$field$id').style.display = 'none'; \">".$this->messagearray["edit"]."</a> <a style=\"font-size: 9px;\" href=\"javascript:\" onClick=\"deleteFile('$field', '$id','".$this->messagearray["confirmdeletefile"]."')\">".$this->messagearray["delete"]."</a>)</span> \n";

                                $table_html .= "<div id='file_" . $field . $id . "' style='display:none;'>\n";
                                $table_html .= $this->showUploadForm($field, $file_dest, $id);
                                $table_html .= "</div>\n";
                            }

                            if ($cell_data == ''){
                                $table_html .= "<span id='text_" . $field . $id . "'><a style=\"font-size: 9px;\" href=\"javascript:\" onClick=\"document.getElementById('file_$field$id').style.display = ''; document.getElementById('text_$field$id').style.display = 'none'; \">".$this->messagearray["addfile"]."</a></span> \n";

                                $table_html .= "<div id='file_" . $field. $id . "' style='display:none;'>\n";
                                $table_html .= $this->showUploadForm($field, $file_dest, $id);
                                $table_html .= "</div>\n";
                            }
                        }
                        else{
                            $table_html .= $cell_data;
                        }

                    }//if field is not editable
                    else{
                        $table_html .= "<td onMouseOver=\"hover(this);\" onMouseOut=\"unHover(this);\">";

                        if (!is_numeric($found_category_index) && $found_category_index == ''){

                            //was allowable values for this field defined?
                            if (is_array($this->allowed_values[$field]) && !$this->field_no_dropdown[$field]){
                                $table_html .= $this->makeAjaxDropdown($id, $field, $cell_data, $this->db_table, $this->db_table_pk, $this->allowed_values[$field]);
                            }
                            else{

                                //if a checkbox
                                if (is_array($this->checkbox[$field])){
                                    $table_html .= $this->makeAjaxCheckbox($id, $field, $cell_data);
                                }
                                else{
                                    //is an editable field

                                    if ($cell_data == '') $cell_data = "&nbsp;&nbsp;";

                                    $field_onKeyPress = "";
                                    if ($this->fieldIsInt($this->getFieldDataType($field)) || $this->fieldIsDecimal($this->getFieldDataType($field))){
                                        $field_onKeyPress = "return fn_validateNumeric(event, this, 'n');";
                                        if ($this->fieldIsDecimal($this->getFieldDataType($field))){
                                            $field_onKeyPress = "return fn_validateNumeric(event, this, 'y');";
                                        }
                                    }

                                    if ($this->fieldIsEnum($this->getFieldDataType($field))){
                                        $allowed_enum_values_array = $this->getEnumArray($this->getFieldDataType($field));
                                        $table_html .= $this->makeAjaxDropdown($id, $field, $cell_data, $this->db_table, $this->db_table_pk, $allowed_enum_values_array);
                                    }
                                    else{

                                        $field_length = strlen($row[$field]);
                                        if ((strtolower($this->getFieldDataType($field))!='text') and $field_length < 51){
                                            $table_html .= $this->makeAjaxEditor($id, $field, $row[$field], 'text', $field_length, $cell_data, $field_onKeyPress);
                                        }
                                        else{
                                            $textarea_height = '';
                                            if ($this->textarea_height[$field] != '') $textarea_height = $this->textarea_height[$field];
                                            $table_html .= $this->makeAjaxEditor($id, $field, $cell_data, 'textarea', $textarea_height, "", $field_onKeyPress);
                                        }
                                    }
                                }
                            }
                        }
                        else{
                            //this field is a reference to another table's primary key (eg it must be a foreign key)
                            $category_field_name = $this->category_field_array[$found_category_index];
                            $category_table_name = $this->category_table_array[$found_category_index];
                            $category_table_pk 	 = $this->category_table_pk_array[$found_category_index];

                            $selected_dropdown_text = "&nbsp;&nbsp;";
                            if ($cell_data != ""){
                                $selected_dropdown_text = q1("SELECT $category_field_name FROM $category_table_name WHERE $category_table_pk = \"" . $cell_data . "\"");
                                if ($selected_dropdown_text == "") $selected_dropdown_text = "--";
                            }
                            if (!$this->fieldInArray($field, $this->uneditable_fields)){
                                $table_html .= $this->makeAjaxDropdown($id, $field, $cell_data, $category_table_name, $category_table_pk, $dropdown_array[$found_category_index], $selected_dropdown_text);
                            }
                            else{
                                $table_html .= $selected_dropdown_text;
                            }
                        }

                    }

                    $html .= "</td>";
                }

                if ($this->delete || (count($this->row_button)) > 0){
                    $table_html .= "<td>\n";

                    if ($this->delete){
                        $table_html .= "<input type=\"button\" class=\"editingSize\" onClick=\"confirmDelete('$id', '" . $this->db_table . "', '" . $this->db_table_pk ."', '" . $this->messagearray["confirmdelete"] ."');\" value=\"".$this->messagearray["delete"]."\" />\n";
                    }

                    if (count($this->row_button) > 0){
                        foreach ($this->row_button as $the_row_button){
                            $value = $the_row_button[0];
                            $url = $the_row_button[1];
                            $attach_param = $the_row_button[2];
                            $javascript_onclick_function = $the_row_button[3];
                            if ($attach_param == "all"){
                                $attach = "?attachments" . $attach_params;
                            }
                            else{
                                $attach = "?" . $this->db_table_pk . "=$id";
                            }

                            //its most likely a user-defined ajax function
                            if ($javascript_onclick_function != ""){
                                $javascript_for_button = "onClick=\"" . $javascript_onclick_function . "($id);\"";
                            }
                            else{
                                $javascript_for_button = "onClick=\"location.href='" . $url . $attach . "'\"";
                            }


                            $table_html .= "<input type=\"button\" $javascript_for_button class=\"btn editingSize\" value=\"$value\" />\n";
                        }
                    }

                    $table_html .= "</td>\n";
                }

                $table_html .= "</tr>";

                if($count%2==0){
                    $class="cell_row";
                }
                else{
                    $class="odd";
                }

                $count++;


            }//foreach row

            $table_html .= "</table>\n";

			//paging links
            if ($totalpage > 1){
                $table_html .= "<br /><div style='width: 800px; position: relative; left: 50%; margin-left: -400px; text-align: ".$this->alignement.";'><div class='alignement'> $links </div></div><br /><br />";
            }

        }//if rows > 0

        //end the div
        $bottom_html = "</div><br />\n";

        //now we come to the "add" fields
        if ($this->add){
            $add_html .= "<div class='alignement'>\n";
			if(isset($_SESSION['noclearadd']) and $_SESSION['noclearadd']==true)
			{
				$argumentClear="noclear";
			}
			else
			{
				$argumentClear="add_form_$this->db_table";
			}
            $add_html .= "   <input type=\"button\" value=\"".$this->messagearray["add"]." $item\" class=\"btn editingSize\" onClick=\"$('#add_form_$this->db_table').slideDown('slow');clearForm('$argumentClear');";
			
			
			foreach($this->add_fields as $field)
			{
				if (array_key_exists($field, $this->loadtinyMCE) and ((strtolower($this->getFieldDataType($field))=='text') or $this->textarea_height[$field] != ''))
				{
					$add_html .= "loadTinyMCE('add" . $field . "', '".$this->loadtinyMCE[$field] ."');";
				}
			}
			
			$add_html .= "\">\n";

            if (count($this->bottom_button) > 0){
                $button_value = $this->bottom_button[0];
                $button_url = $this->bottom_button[1];
                $button_tags = $this->bottom_button[2];

                if ($button_tags == ''){
                    $tag_stuff = "onClick=\"location.href = '$button_url';\"";
                }
                else{
                    $tag_stuff = $button_tags;
                }
                $add_html .= "  <input type=\"button\" value=\"$button_value\" href=\"$button_url\" class=\"btn\" $tag_stuff>\n";
            }

            //$add_html .= "  <input type=\"button\" value=\"Go Back\" class=\"btn\" onClick=\"history.back();\">\n";
            $add_html .= "</div>\n";

            $add_html .= "<form action=\"" . $_SERVER['PHP_SELF'] ."#ajaxCRUD\" id=\"add_form_$this->db_table\" method=\"POST\" ENCTYPE=\"multipart/form-data\"";
			
			if(!isset($_SESSION['noclearadd']) or $_SESSION['noclearadd']==false)
			{
				$add_html .= " style=\"display:none;\"";
			}
			$add_html .= ">\n";
			
            $add_html .= "  <br /><h3 class=\"alignement\">".$this->messagearray["newrow"]."<b>$item</b></h3>\n";
            //$add_html .= "  <table align='".$this->alignement."' name='form'>\n";
			
			$add_html .= "<table";
			if($this->alignement=='center'){$add_html .= " align='".$this->alignement."'";}
			$add_html .= " name='form'>\n";
			
			
            $add_html .= "<tr valign='top'>\n";

            //for here display ALL 'addable' fields
            foreach($this->add_fields as $field){
                if ($field != $this->db_table_pk || $this->on_add_specify_primary_key){
                    $field_value = "";
                    if ($_REQUEST[$field] != '') $field_value = $_REQUEST[$field];

                    if ($this->displayAs_array[$field] != ''){
                        $display_field = $this->displayAs_array[$field];
                    }
                    else{
                        $display_field = $field;
                    }

                    //if a checkbox
                    if (is_array($this->checkbox[$field])){
                        $values = $this->checkbox[$field];
                        $value_on = $values[0];
                        $value_off = $values[1];
                        $add_html .= "<th width='20%'>$display_field</th><td>\n";
                        $add_html .= "<input type='checkbox' name=\"$field\" value=\"$value_on\">\n";
						if (array_key_exists($field, $this->addFormComments))
						{
							$add_html .= " ".$this->addFormComments[$field];
						}
                        $add_html .= "</td></tr>\n";
                    }
                    else{
                        $found_category_index = array_search($field, $this->db_table_fk_array);
                        if (!is_numeric($found_category_index) && $found_category_index == ''){

                            //it's from a set of predefined allowed values for this field
                            if (is_array($this->allowed_values[$field])){
                                $add_html .= "<th width='20%'>$display_field</th><td>\n";
                                $add_html .= "<select name=\"$field\" class='editingSize'>\n";
                                foreach ($this->allowed_values[$field] as $dropdown){
                                    $selected = "";
                                    $dropdown_value = $dropdown[0];
                                    $dropdown_text  = $dropdown[1];
                                    if ($field_value == $dropdown_value) $selected = " selected";
                                    $add_html .= "<option value=\"$dropdown_value\" $selected>$dropdown_text</option>\n";
                                }
                                $add_html .= "</select>";
								if (array_key_exists($field, $this->addFormComments))
								{
									$add_html .= " ".$this->addFormComments[$field];
								}
								$add_html .= "</td></tr>\n";
								
                            }
                            else{
                                if ($this->fieldInArray($field, $this->file_uploads)){
                                    //this field is an file upload
                                    $add_html .= "<th width='20%'>$display_field</th><td><input class=\"editingSize\" type=\"file\" name=\"$field\" size=\"15\">";
									if (array_key_exists($field, $this->addFormComments))
									{
										$add_html .= " ".$this->addFormComments[$field];
									}
									$add_html .= "</td></tr>\n";
                                    $file_uploads = true;
                                }
                                else{
                                    if ($this->fieldIsEnum($this->getFieldDataType($field))){
                                        $allowed_enum_values_array = $this->getEnumArray($this->getFieldDataType($field));

                                        $add_html .= "<th width='20%'>$display_field</th><td>\n";
                                        $add_html .= "<select name=\"$field\" class='editingSize'>\n";
                                        foreach ($allowed_enum_values_array as $dropdown){
                                            $dropdown_value = $dropdown;
                                            $dropdown_text  = $dropdown;
                                            if ($field_value == $dropdown_value) $selected = " selected";
                                            $add_html .= "<option value=\"$dropdown_value\" $selected>$dropdown_text</option>\n";
                                        }
                                        $add_html .= "</select>";
										if (array_key_exists($field, $this->addFormComments))
										{
											$add_html .= " ".$this->addFormComments[$field];
										}
										$add_html .= "</td></tr>\n";
                                    }//if enum field
                                    else{
                                        $field_onKeyPress = "";
                                        if ($this->fieldIsInt($this->getFieldDataType($field)) || $this->fieldIsDecimal($this->getFieldDataType($field))){
                                            $field_onKeyPress = "return fn_validateNumeric(event, this, 'n');";
                                            if ($this->fieldIsDecimal($this->getFieldDataType($field))){
                                                $field_onKeyPress = "return fn_validateNumeric(event, this, 'y');";
                                            }
                                        }

                                        //textarea fields
										if ((strtolower($this->getFieldDataType($field))=='text') or $this->textarea_height[$field] != ''){
	                                        if ($this->textarea_height[$field] != ''){
	                                            $add_html .= "<th width='20%'>$display_field</th><td><textarea onKeyPress=\"$field_onKeyPress\" class=\"editingSize\" name=\"$field\" id=\"add$field\" style='width: 97%; height: " . $this->textarea_height[$field] . "px;'>$field_value</textarea>";
												if (array_key_exists($field, $this->addFormComments))
												{
													$add_html .= " ".$this->addFormComments[$field];
												}
												 $add_html .= "</td></tr>\n";
											}
											else
											{
	                                            $add_html .= "<th width='20%'>$display_field</th><td><textarea onKeyPress=\"$field_onKeyPress\" class=\"editingSize\" name=\"$field\" id=\"add$field\" style='width: 97%; height: 80px;'>$field_value</textarea>";
												if (array_key_exists($field, $this->addFormComments))
												{
													$add_html .= " ".$this->addFormComments[$field];
												}
												$add_html .= "</td></tr>\n";
											}
                                        }
                                        else{
                                            //any ol' data will do
                                            $field_size = "";
                                            if ($this->fieldIsInt($this->getFieldDataType($field)) || $this->fieldIsDecimal($this->getFieldDataType($field))){
                                                $field_size = 7;
                                            }
                                            $add_html .= "<th width='20%'>$display_field</th><td><input onKeyPress=\"$field_onKeyPress\" class=\"editingSize\" type=\"text\" name=\"$field\" size=\"$field_size\" value=\"$field_value\" maxlength=\"150\">";
												if (array_key_exists($field, $this->addFormComments))
												{
													$add_html .= " ".$this->addFormComments[$field];
												}
												$add_html .= "</td></tr>\n";
                                        }
                                    }//else not enum field
                                }//not an uploaded file
                            }//not a pre-defined value
                        }//not from a foreign/primary key relationship
                        else{
                            //field is from a defined relationship
                            $key = $found_category_index;
                            $add_html .= "<th>$display_field</th><td>\n";
                            $add_html .= "<select name=\"$field\" class='editingSize'>\n";

                            if ($this->category_required[$field] != TRUE){
                                if ($this->fieldIsInt($this->getFieldDataType($field)) || $this->fieldIsDecimal($this->getFieldDataType($field))){
                                    $add_html .= "<option value=0>--Select--</option>\n";
                                }
                                else{
                                    $add_html .= "<option value=''>--Select--</option>\n";
                                }
                            }

                            foreach ($dropdown_array[$key] as $dropdown){
                                $selected = "";
                                $dropdown_value = $dropdown[$this->category_table_pk_array[$key]];
                                $dropdown_text  = $dropdown[$this->category_field_array[$key]];
                                if ($field_value == $dropdown_value) $selected = " selected";
                                $add_html .= "<option value=\"$dropdown_value\" $selected>$dropdown_text</option>\n";
                            }
                            $add_html .=  "</select>";
							if (array_key_exists($field, $this->addFormComments))
							{
								$add_html .= " ".$this->addFormComments[$field];
							}
							$add_html .=  "</td></tr>\n";
							
                        }
                    }//not a checkbox
                }//not the primary pk
            }//foreach

            $add_html .= "</tr><tr><td>\n";

            if ($this->ajax_add){
                $add_html .= "<input class=\"editingSize\" type=\"button\" onClick=\"tinyMCE.triggerSave();
                    setLoadingImage($num_ajaxCRUD_tables_instantiated);
                    var fields = getFormValues(document.getElementById('add_form_$this->db_table'), '');
                    fields = fields + '&table=$this->db_table';
                    var req = '" . $_SERVER[PHP_SELF] . "?action=add&' + fields;
                    clearForm('add_form_$this->db_table');
                    sndAddReq(req);
                    return false;\" value=\"".$this->messagearray["add"]." $item\">";
            }
            else{
                $add_html .= "<input class=\"editingSize\" onClick=\"tinyMCE.triggerSave();\" type=\"submit\" value=\"".$this->messagearray["add"]." $item\">";
            }

            $add_html .= "</td><td><input ";
			if($this->cancelfloat)
			{
				$add_html .= "style='float: right;' ";
			}
			$add_html .= "class=\"editingSize\" type=\"button\" onClick=\"$('#add_form_$this->db_table').slideUp('slow');clearForm('add_form_$this->db_table');clearError();\" value=\"".$this->messagearray["cancel"]."\"></td></tr>\n</table>\n";

            $add_html .= "<input type=\"hidden\" name=\"action\" value=\"add\">\n";
            $add_html .= "<input type=\"hidden\" name=\"table\" value=\"$this->db_table\">\n";

            if ($file_uploads){
                $add_html .= "<input type=\"hidden\" name=\"uploads_on\" value=\"true\">\n";
            }

            $add_html .= "</form>\n";

        }//if adding fields is "allowed"

        //for ajax adding
        if ($this->ajax_add){
            $_SESSION['the_table_div'] = $table_html;
        }

        //when table is being filtered via ajax, it is retrieved via the session var
        $_SESSION['filtered_table_div'] = $table_html;
        $_SESSION['sorted_table_div' . $num_ajaxCRUD_tables_instantiated] = $table_html;

        $html = $top_html . $table_html . $bottom_html . $add_html;
        if ($this->add_form_top){
        	$html = $add_html . $top_html . $table_html . $bottom_html;
        }

        echo $html;

	}

	function getFields($table){
		$query = "SHOW COLUMNS FROM $table";
		$rs = q($query);

		//print_r($rs);
		$fields = array();
		foreach ($rs as $r){
			//r sub0 is the name of the field (tricky ... but it works)
			$fields[] = $r[0];
            $this->field_datatype[$r[0]] = $r[1];
		}

		if (count($fields) > 0){
			return $fields;
		}

		return false;
	}

    function getFieldDataType($field_name){
        return $this->field_datatype[$field_name];
    }

    function fieldIsInt($datatype){
        if (stristr($datatype, "int") !== FALSE){
            return true;
        }
        return  false;
    }

    function fieldIsDecimal($datatype){
        if (stristr($datatype, "decimal") !== FALSE || stristr($datatype, "double") !== FALSE){
            return true;
        }
        return  false;

    }

    function fieldIsEnum($datatype){
        if (stristr($datatype, "enum") !== FALSE){
            return true;
        }
        return  false;
    }

    function getEnumArray($datatype){
        $enum = substr($datatype, 5);
        $enum = substr($enum, 0, (strlen($enum) - 1));
        $enum = str_replace("'", "", $enum);
        $enum = str_replace('"', "", $enum);
        $enum_array = explode(",", $enum);

        return ($enum_array);
    }


    function fieldInArray($field, $the_array){

        //try to find index for arrays with array[key] = field_name
        $found_index = array_search($field, $the_array);
        if ($found_index !== FALSE){
            return true;
        }

        //for arrays with array[0] = field_name and array[1] = value
        foreach ($the_array as $the_array_values){
            $field_name = $the_array_values[0];
            if ($field_name == $field){
                return true;
            }
        }

        return false;
    }

	function makeAjaxEditor($unique_id, $field_name, $field_value, $type = 'textarea', $field_size = "", $field_text = "", $onKeyPress_function = ""){

        $prefield = trim($this->db_table . $field_name . $unique_id);

		$input_name = $type . "_" . $prefield;

        $return_html = "";

		if ($field_text == "") $field_text = $field_value;

        $return_html .= "<span class=\"editable\" id=\"" . $prefield ."_show\" style=\"cursor: hand;\" onClick=\"
			document.getElementById('" . $prefield . "_edit').style.display = '';
			document.getElementById('" . $prefield . "_show').style.display = 'none';
			document.getElementById('" . $input_name . "').focus();";
			
		if (array_key_exists($field_name, $this->loadtinyMCE) and ((strtolower($this->getFieldDataType($field_name))=='text') or $this->textarea_height[$field_name] != ''))
		{
			$return_html .= "loadTinyMCE('" . $input_name . "', '".$this->loadtinyMCE[$field_name] ."');";
		}
			
			
			
		$return_html .= "
            \">" . $field_text . "</span>
        <span id=\"" . $prefield ."_edit\" style=\"display: none;\">
            <form style=\"display: inline;\" name=\"form_" . $prefield . "\" id=\"form_" . $prefield . "\" onsubmit=\"";
				//if (array_key_exists($field_name, $this->loadtinyMCE))
				//{
					$return_html .= "tinyMCE.triggerSave();";
				//}
				$return_html .= "document.getElementById('" . $prefield . "_edit').style.display='none';
				document.getElementById('" . $prefield . "_save').style.display='';";
				
				$fieldverification='';
				if (array_key_exists($field_name, $this->validValues))
				{
					$fieldverification="&verification=1&verificationtype=".$this->validValues[$field_name]['type']."&verificationempty=".$this->validValues[$field_name]['empty'];
				}

				$return_html .= "var req = '" . $this->ajax_file . "?action=update&id=" . $unique_id . $fieldverification."&field=" . $field_name . "&table=" . $this->db_table . "&pk=" . $this->db_table_pk . "&val=' + escape(document.getElementById('" . $input_name . "').value);";
				$return_html .= "
				sndReq(req);
				return false;
			\">";

            //for getting rid of the html space, replace with actual no text
            if ($field_value == "&nbsp;&nbsp;") $field_value = "";

            if ($type == 'text'){
                if ($field_size == "") $field_size = 50;
				/*$field_valuebis=str_replace('"', '#guill#', $field_value);
                $return_html .= "<input ONKEYPRESS=\"$onKeyPress_function\" id=\"text_$prefield\" name=\"$input_name\" type=\"text\" class=\"editingSize editMode\" size=\"$field_size\" value=\"$field_value\"/>\n";*/
				$field_valuebis=str_replace('"', '``', $field_value);
                $return_html .= "<input ONKEYPRESS=\"$onKeyPress_function\" id=\"text_$prefield\" name=\"$input_name\" type=\"text\" class=\"editingSize editMode\" size=\"$field_size\" value=\"$field_value\"/>\n";
			}
			else{
                if ($field_size == "") $field_size = 80;
                $return_html .= "<textarea ONKEYPRESS=\"$onKeyPress_function\" id=\"$input_name\" name=\"textarea_$prefield\" class=\"editingSize editMode\" style=\"width: 100%; height: " . $field_size . "px;\">$field_value</textarea>\n";
                $return_html .= "<br /><input type=\"submit\" class=\"editingSize\" value=\"Ok\">\n";
			}

        $return_html .= "
			<input type=\"button\" class=\"editingSize\" value=\"".$this->messagearray["cancel"]."\" onClick=\"
				document.getElementById('" . $prefield . "_show').style.display = '';
				document.getElementById('" . $prefield . "_edit').style.display = 'none';
			\"/>
			</form>
		</span>
        <span style=\"display: none;\" id=\"" . $prefield . "_save\" class=\"savingAjaxWithBackground\">Saving...</span>";

        return $return_html;

	}//makeAjaxEditor

    function makeAjaxDropdown($unique_id, $field_name, $field_value, $dropdown_table, $dropdown_table_pk, $array_list, $selected_dropdown_text = "NOTHING_ENTERED"){
        $return_html = "";

        if ($selected_dropdown_text == "NOTHING_ENTERED"){

            $selected_dropdown_text = $field_value;

            foreach ($array_list as $list){
                if (is_array($list)){
                    $list_val = $list[0];
                    $list_option = $list[1];
                }
                else{
                    $list_val = $list;
                    $list_option = $list;
                }

                if ($list_val == $field_value) $selected_dropdown_text = $list_option;
            }
        }

        if ($selected_dropdown_text == ''){
            $no_text = true;
            $selected_dropdown_text = "&nbsp;&nbsp;";
        }

        $prefield = trim($this->db_table . $field_name . $unique_id);

        $return_html = "<span class=\"editable\" id=\"" . $prefield . "_show\" style=\"cursor: hand;\" onClick=\"
			document.getElementById('" . $prefield . "_edit').style.display = '';
			document.getElementById('" . $prefield . "_show').style.display = 'none';
			\">" . $selected_dropdown_text . "</span>

            <span style=\"display: none;\" id=\"" . $prefield . "_edit\">
                <form style=\"display: inline;\" name=\"form_" . $prefield . "\" id=\"form_" . $prefield . "\">
                <select class=\"editingSize editMode\" id=\"" . $prefield . "\" onChange=\"
                    var selected_index_value = document.getElementById('" . $prefield . "').value;
                    document.getElementById('" . $prefield . "_edit').style.display='none';
                    document.getElementById('" . $prefield . "_save').style.display='';";
					
				$fieldverification='';
				if (array_key_exists($field_name, $this->validValues))
				{
					$fieldverification="&verification=1&verificationtype=".$this->validValues[$field_name]['type']."&verificationempty=".$this->validValues[$field_name]['empty'];
				}
				$return_html .= "var req = '" . $this->ajax_file . "?action=update&id=" . $unique_id .$fieldverification. "&field=" . $field_name . "&table=" . $this->db_table . "&pk=" . $this->db_table_pk . "&dropdown_tbl=" . $dropdown_table . "&val=' + selected_index_value;";
				$return_html .= "
                    sndReq(req);
                    return false;
                \">";

            if ($no_text || $this->category_required[$field_name] != TRUE){
                if ($this->fieldIsInt($this->getFieldDataType($field_name)) || $this->fieldIsDecimal($this->getFieldDataType($field_name))){
                    $return_html .= "<option value='0'>--Select--</option>\n";
                }
                else{
                    $return_html .= "<option value=''>--Select--</option>\n";
                }
            }

            foreach($array_list as $list){
				$selected = '';
                if (is_array($list)){
                    $list_val = $list[0];
                    $list_option = $list[1];
                }
                else{
                    $list_val = $list;
                    $list_option = $list;
                }

				if ($list_val == $field_value) $selected = " selected";
                $return_html .= "<option value=\"$list_val\" $selected >$list_option</option>";
			}
            $return_html .= "</select>";

			$return_html .= "<input type=\"button\" value=\"".$this->messagearray["cancel"]."\" onClick=\"
				document.getElementById('" . $prefield . "_show').style.display = '';
				document.getElementById('" . $prefield . "_edit').style.display = 'none';
			\"/>
		</span>
		</form>

        <span style=\"display: none;\" id=\"" . $prefield . "_save\" class=\"savingAjaxWithBackground\">Saving...</span>\n";

        return $return_html;

	}//makeAjaxDropdown


	function makeAjaxCheckbox($unique_id, $field_name, $field_value){
		$prefield = trim($this->db_table) . trim($field_name) . trim($unique_id);

        $return_html = "";

		$values = $this->checkbox[$field_name];
		$value_on = $values[0];
		$value_off = $values[1];

		$checked = '';
		if ($field_value == $value_on) $checked = "checked";

		$show_value = '';
		if ($checked == '') {
			$show_value = $value_off;
		} else {
			$show_value = $value_on;
		}

		//strip quotes
		$value_on = str_replace('"', "'", $value_on);
		$value_off = str_replace('"', "'", $value_off);

        $return_html .= "<input type=\"checkbox\" $checked name=\"$field_name" . "_fieldckbox\" id=\"$field_name$unique_id\" onClick=\"
			var " . $prefield . "_value = '';

			if (this.checked){
				" . $prefield . "_value = '$value_on';
				if (" . (int)$this->checkboxall[$field_name] . ") {
					document.getElementById('$field_name$unique_id" . "_label').innerHTML = '$value_on';
				}
			}
			else{
				". $prefield . "_value = '$value_off';
				if (" . (int)$this->checkboxall[$field_name] . ") {
					document.getElementById('$field_name$unique_id" . "_label').innerHTML = '$value_off';
				}
			}";
			$fieldverification='';
			if (array_key_exists($field_name, $this->validValues))
				{
					$fieldverification="&verification=1&verificationtype=".$this->validValues[$field_name]['type']."&verificationempty=".$this->validValues[$field_name]['empty'];
				}
				$return_html .= "
			var req = '" . $this->ajax_file . "?action=update&id=$unique_id".$fieldverification."&field=$field_name&table=$this->db_table&pk=$this->db_table_pk&val=' + " . $prefield . "_value;";
				$return_html .= "
			sndReqNoResponse(req);
		\">";
		if ($this->checkboxall[$field_name] == true) {
			$return_html .= "<label for=\"$field_name$unique_id\" id=\"" . $field_name . $unique_id . "_label\">$show_value</label>";
		}

        return $return_html;

	}//makeAjaxCheckbox

    function showUploadForm($field_name, $upload_folder, $row_id){
        $return_html = "";

        $return_html .= "<form action=\"" . $_SERVER['PHP_SELF'] . "#ajaxCRUD\" name=\"Uploader\" method=\"POST\" ENCTYPE=\"multipart/form-data\">\n";
        $return_html .=  "  <input type=\"file\" size=\"10\" name=\"$field_name\" />\n";
        $return_html .= "  <input type=\"hidden\" name=\"upload_folder\" value=\"$upload_folder\" />\n";
        $return_html .= "  <input type=\"hidden\" name=\"field_name\" value=\"$field_name\" />\n";
        $return_html .= "  <input type=\"hidden\" name=\"id\" value=\"$row_id\" />\n";
        $return_html .= "  <input type=\"hidden\" name=\"action\" value=\"upload\" />\n";
        $return_html .= "  <input type=\"submit\" name=\"submit\" value=\"".$this->messagearray["upload"]."\" />\n";
        $return_html .= "</form>\n";

        return $return_html;
    }


}//class


/* In an effect to minimize filecount, I am attaching this (paging) class and a few functions all in this file */
class paging{

	var $pRecordCount;
	var $pStartFile;
	var $pRowsPerPage;
	var $pRecord;
	var $pCounter;
	var $pPageID;
	var $pShowLinkNotice;

	function processPaging($rowsPerPage,$pageID){
       $record = $this->pRecordCount;
       if($record >=$rowsPerPage)
            $record=ceil($record/$rowsPerPage);
       else
            $record=1;
        if(empty($pageID) or $pageID==1){
            $pageID=1;
            $startFile=0;
        }
        if($pageID>1)
            $startFile=($pageID-1)*$rowsPerPage;

        $this->pStartFile   = $startFile;
        $this->pRowsPerPage = $rowsPerPage;
        $this->pRecord      = $record;
        $this->pPageID      = $pageID;

        return $record;
	}
	function myRecordCount($query){
		if(CONNECTWITHPDO==true)
		{
			$pdo = class_pdo::getInstance();
			$rsCount = $pdo->numRows($query);
			$this->pRecordCount = $rsCount;
		}
		else
		{
			$rs = mysql_query($query) or die(mysql_error()."<br>".$query);
			$rsCount = mysql_num_rows($rs);
			$this->pRecordCount = $rsCount;
			unset($rs);
		}
		return $rsCount;
	}

	function startPaging($query){
		$query    = $query." LIMIT ".$this->pStartFile.",".$this->pRowsPerPage;
		$rs = q($query);
		//$rs       = mysql_query($query) or die(mysql_error()."<br>".$query);
		//mysql_free_result($rs);
		return $rs;
	}

	function pageLinks($url){
        global $choose_category,$sort, $num_ajaxCRUD_tables_instantiated;
        $cssclass = "paging_links";
		$this->pShowLinkNotice = "&nbsp;";
		if($this->pRecordCount>$this->pRowsPerPage){
			$this->pShowLinkNotice = "Page ".$this->pPageID. " of ".$this->pRecord;
			//Previous link
			if($this->pPageID!==1){
                $prevPage = $this->pPageID - 1;
                $link = "<a href=\"javascript:;\" onClick=\"" . $this->getOnClick("&pid=1&mid=$ltype&cid=$catid") . "\" class=\"$cssclass\">|<<</a>\n ";
                $link .= "<a href=\"javascript:;\" onClick=\"" . $this->getOnClick("&pid=$prevPage&mid=$ltype&cid=$catid") ."\" class=\"$cssclass\"><<</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
			}
			//Number links 1.2.3.4.5.
			for($ctr=1;$ctr<=$this->pRecord;$ctr++){
				if($this->pPageID==$ctr)
                $link .=  "<a href=\"javascript:;\" onClick=\"" . $this->getOnClick("&pid=$ctr") . "\" class=\"$cssclass\"><b>$ctr</b></a>\n";
				else
                $link .= "  <a href=\"javascript:;\" onClick=\"" . $this->getOnClick("&pid=$ctr") . "\" class=\"$cssclass\">$ctr</a>\n";
			}
			//Previous Next link
			if($this->pPageID<($ctr-1)){
                $nextPage = $this->pPageID + 1;
                $link .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript:;\" onClick=\"" . $this->getOnClick("&pid=$nextPage&mid=$ltype&cid=$catid") . "\" class=\"$cssclass\">>></a>\n";
                $link .="<a href=\"javascript:;\" onClick=\"" . $this->getOnClick("&pid=".$this->pRecord."&mid=$ltype&cid=$catid") . "\" class=\"$cssclass\">>>|</a>\n";
			}
			return $link;
		}
	}

	function getOnClick($paging_query_string){
		global $num_ajaxCRUD_tables_instantiated;
		$extra_query_params = "Dealer=" . htmlentities($_REQUEST['Dealer']);
		return "pageTable('&" . $extra_query_params . "$paging_query_string', $num_ajaxCRUD_tables_instantiated);";
	}
}


/* Random functions which may or may not be used */
if (!function_exists('echo_msg_box')){

    function echo_msg_box(){

        global $error_msg;
        global $report_msg;

        if (is_string($error_msg)){
            $error_msg = array();
        }
        if (is_string($report_msg)){
            $report_msg = array();
        }
        //for passing errors/reports over get variables
        if ($_REQUEST['err_msg'] != ''){
            $error_msg[] = $_REQUEST['err_msg'];
        }
        if ($_REQUEST['rep_msg'] != ''){
            $report_msg[] = $_REQUEST['rep_msg'];
        }

        if(is_array($report_msg)){
            $first = true;
                foreach ($report_msg as $e){
                    if($first){
                        $reports.= "&nbsp;&nbsp; $e";
                        $first = false;
                    }
                    else
                        $reports.= "<br /> $e";
                }
        }
        if($reports != ''){
            echo "<div class='report'>$reports</div>";
        }

        if(is_array($error_msg)){
            $first = true;
                foreach ($error_msg as $e){
                    if($first){
                        $errors.= "&nbsp;&nbsp; $e";
                        $first = false;
                    }
                    else
                        $errors.= "<br />$e";
                }
        }
        if($errors != ''){
            echo "<div id='cruderror' class='error'>$errors</div>";
        }
    }
}

if (!function_exists('make_filename_safe')){

    function make_filename_safe($filename){
        $filename = trim(str_replace(" ","_",$filename));
        $filename = str_replace("'", "", $filename);
        $filename = str_replace('"', '', $filename);
        $filename = str_replace('#', '_', $filename);
        $filename = str_replace('%20', '_', $filename);

        return stripslashes($filename);
    }
}


?>