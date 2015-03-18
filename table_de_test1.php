<?php
//é
	//require_once('preheader.php');
	echo "<div id='crud'>";

	#the code for the class
	include ('ajaxCRUD.class.php');

    #this one line of code is how you implement the class
    ########################################################
    ##

    $tbl = new ajaxCRUD("table de test 1", "table_de_test1", "id");
	$tbl->active_utf8_encode_unicode_urldecode_onupdate();
	$tbl->changelanguage('fr');
	$tbl->setCSSFile('table.css');
    #i could set a field to accept file uploads (the filename is stored) if wanted
    $tbl->setFileUpload("image", "../upload/", "../upload/");
	$tbl->maxUploadFilesize("image", "500000");
	$allowablefiletype = array("image/gif", "image/jpeg", "image/png");
	$tbl->define_accept_filetype("image", $allowablefiletype);
    #set the number of rows to display (per page)
    $tbl->setLimit(5);
	#set a filter box at the top of the table
    $tbl->addAjaxFilterBox('nom');
	$tbl->addAjaxFilterBox('prenom');
	
	
	$tbl->load_tinyMCE('description', 'simple');
	#actually show the table
	$tbl->showTable();

	
	echo "</div>";
	

/*	#i can define a relationship to another table
    #the 1st field is the fk in the table, the 2nd is the second table, the 3rd is the pk in the second table, the 4th is field i want to retrieve as the dropdown value (http://ajaxcrud.com/api/index.php?id=defineRelationship)
    $tbl->defineRelationship("site", "site", "id", "nom");

    #i could set a field to accept file uploads (the filename is stored) if wanted
    $tbl->setFileUpload("image", "../../../croisitour/galerie/logo_partenaires/", "http://www.croisitour.com/galerie/logo_partenaires/");
	
	#i can rename upload's file with specific fields for a field : first argument is the fieldname of file upload, second is array of fields's values to add at the name, third is saparator between fields's values and last is boolean for strtolower
	$fieldsname = array("nom", "id");
	$tbl->renameUploadFilename("image", $fieldsname, "_", true);
	
	#i can specify max file size in octets
	$tbl->maxUploadFilesize("image", "500000");

	//$tbl->appendUploadFilename("id");
	
	#i can allow only a list of type mime to upload for a field
	$allowablefiletype = array("image/gif", "image/jpeg", "image/png");
	$tbl->define_accept_filetype("image", $allowablefiletype);

    #i can order my table by whatever i want
    $tbl->addOrderBy("ORDER BY nom ASC");

    #i can set certain fields to only allow certain values (http://ajaxcrud.com/api/index.php?id=defineAllowableValues)
    $allowableValues = array("compagnie", "hotel", "autre");
    $tbl->defineAllowableValues("type", $allowableValues);
	
	#i can do verif on data before add or update with the options : isEmail, isURL, isBool, isIP, isRegex, is0or1, isDate, isTel, isTelbis, isWord, isPhrase, isAdresse, isCivilite, isCP, isInteger, isDay, isMonth, isYear, the last argument is for empty values(true=allowed)
	$tbl->defineValidValues("nom", "isPhrase", false);
	$tbl->defineValidValues("url", "isURL", false);
	
    #set the number of rows to display (per page)
    $tbl->setLimit(15);

	#set a filter box at the top of the table
    $tbl->addAjaxFilterBox('nom');
	$tbl->addAjaxFilterBox('image');
	$tbl->addAjaxFilterBox('url');
	$tbl->addAjaxFilterBox('site');
	$tbl->addAjaxFilterBox('type');
	
	#to define align, for an entire align, don't forget to modify the the class .alignement in the ajaxcrud.css file
	$tbl->setAlignement('left');
	
	#to display the add form at top
	$tbl->displayAddFormTop();
	#to disable float on Cancel button
	$tbl->disableCancelFloat();
	#i can give a title to the crud table and the filter form
	$tbl->setTitleCrudTable("Partenaires existants");
	$tbl->setTitleFilterForm("Tri / Sélection d’un partenaire");
	
	#i can add comments after inputs in the add form. first argument is the name of the field, second is comment
	$tbl->addAddFormComments('image', '(poids max 500ko)');
	$tbl->addAddFormComments('url', '(http://www.exempledesite.com)');
	
	#actually show the table
	$tbl->showTable();

	#my self-defined functions used for formatFieldWithFunction
	//$tbl->onAddExecuteCallBackFunction("makeBold");
	/*function makeBold($val){
		return "<b>$val</b>";
	}

	function makeBlue($val){
		return "$val";
	}*/

?>