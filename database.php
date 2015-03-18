<?php
//é
//CONSTANTES
define('DBHOST', 'localhost');
define('DBBASE', 'base_de_donnees_de_test');
define('DBUSER', 'utilisateur');
define('DBPASS', 'mdptest');
include_once ('class_pdo.php');
//CRUD-it , author : Stéphane Delaune

/*$MYSQL_HOST = "localhost";
$MYSQL_LOGIN = "root";
$MYSQL_PASS = "1732";
$MYSQL_DB = "ajaxcrud";*/

if(!defined('CONNECTWITHPDO'))
{
	define("CONNECTWITHPDO",true);//if true, connect db use PDO else mysql
}

if(CONNECTWITHPDO==true)
{
	$pdo = class_pdo::getInstance();
	
	if (!function_exists('q')) {
		function q($q, $debug = 0){
			$pdo = class_pdo::getInstance();
			$r = $pdo->query($q);
	
			if($debug == 1)
				echo "<br>$q<br>";
	
	
			if(stristr(substr($q,0,8),"delete") ||	stristr(substr($q,0,8),"insert") || stristr(substr($q,0,8),"update")){
				if($r > 0)
					return true;
				else
					return false;
			}
			
	
			if($r > 1){
				foreach($r as $row){
					$results[] = $row;
				}
			}
			else if($r == 1){
				$results = array();
				foreach($r as $row){
					$results[] = $row;
				}
			}
	
			else
				$results = array();
			
			return $results;
		}
	}
	
	if (!function_exists('q1')) {
		function q1($q, $debug = 0){
			$pdo = class_pdo::getInstance();
			$r = $pdo->query($q);
			
			if($debug == 1)
				echo "<br>$q<br>";
			$row = array();
			foreach($r as $ro){
					$row[] = $ro;
				}
			
			if(count($row) > 0)
				return $row[0][0];
			else
				return $row;
		}
	}
	
	if (!function_exists('qr')) {
		function qr($q, $debug = 0){
			$pdo = class_pdo::getInstance();
			$r = $pdo->query($q);
	
			if($debug == 1)
				echo "<br>$q<br>";
				
			if(stristr(substr($q,0,8),"delete") ||	stristr(substr($q,0,8),"insert") || stristr(substr($q,0,8),"update")){
				if($r > 0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			
			$results = array();
			foreach($r as $row){
				$results[] = $row;
			}
			$results = $results[0];
	
			return $results;
		}
	}
}
else
{
	$db = mysql_connect($DBHOST,$DBUSER,$DBPASS);
	
	if(!$db){
		echo('<td><font class=content2>Unable to connect to db' . mysql_error());
		exit;
	}
	
	mysql_select_db($DBBASE);
	
	#below follows my custom database handling functions - required for the class
	
	if (!function_exists('q')) {
		function q($q, $debug = 0){
			$r = mysql_query($q);
			if(mysql_error()){
				echo mysql_error();
				echo "$q<br>";
			}
	
			if($debug == 1)
				echo "<br>$q<br>";
	
			if(stristr(substr($q,0,8),"delete") ||	stristr(substr($q,0,8),"insert") || stristr(substr($q,0,8),"update")){
				if(mysql_affected_rows() > 0)
					return true;
				else
					return false;
			}
			if(mysql_num_rows($r) > 1){
				while($row = mysql_fetch_array($r)){
					$results[] = $row;
				}
			}
			else if(mysql_num_rows($r) == 1){
				$results = array();
				$results[] = mysql_fetch_array($r);
			}
	
			else
				$results = array();
			return $results;
		}
	}
	
	if (!function_exists('q1')) {
		function q1($q, $debug = 0){
			$r = mysql_query($q);
			if(mysql_error()){
				echo mysql_error();
				echo "<br>$q<br>";
			}
	
			if($debug == 1)
				echo "<br>$q<br>";
			$row = @mysql_fetch_array($r);
	
			if(count($row) == 2)
				return $row[0];
			else
				return $row;
		}
	}
	
	if (!function_exists('qr')) {
		function qr($q, $debug = 0){
			$r = mysql_query($q);
			if(mysql_error()){
				echo mysql_error();
				echo "<br>$q<br>";
			}
	
			if($debug == 1)
				echo "<br>$q<br>";
	
			if(stristr(substr($q,0,8),"delete") ||	stristr(substr($q,0,8),"insert") || stristr(substr($q,0,8),"update")){
				if(mysql_affected_rows() > 0)
					return true;
				else
					return false;
			}
	
			$results = array();
			$results[] = mysql_fetch_array($r);
			$results = $results[0];
	
			return $results;
		}
	}
}

?>