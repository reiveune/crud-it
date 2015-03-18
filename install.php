<?php
//é
	require_once('preheader.php');

    $success = q1("CREATE TABLE tblDemo(pkID INT PRIMARY KEY AUTO_INCREMENT,fldField1 VARCHAR(45),fldField2 VARCHAR(45),fldCertainFields VARCHAR(40),fldLongField TEXT);");

    if ($success){
        echo "TABLE <b>tblDemo</b> CREATED <br /><br />\n";
    }

    $success = qr("INSERT INTO tblDemo (fldField1, fldField2, fldCertainFields, fldLongField) VALUES (\"Testing\", \"Testing2\", \"CRUD\", \"Final Test\")");

    if ($success){
        echo "An example row entered into <b>tblDemo</b><br /><br />\n";
    }

    echo "<p><a href='example.php'>Try out the demo</a></p>\n";

?>