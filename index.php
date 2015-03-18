<?php
//é
	require_once('preheader.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="tinymce/jscripts/tiny_mce/tiny_mce.js" ></script >
<script type="text/javascript">
//function loadTinyMCE by Stéphane Delaune
function loadTinyMCE(id, them){
 tinyMCE.init({
      mode : "exact",
	  elements : id,
      theme : them
   });
}
</script >
<title>CRUD</title>
</head>

<body>
<a href="../">revenir au site</a><br />
<?php

//include 'produit.php';
include 'table_de_test1.php';

?>


</body>
</html>