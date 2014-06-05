<?php
$username="nowtracking";
$password="";
$database="nowtracking";


$TID = $_GET['TID'];
//error_log($TID);
$TISJUNK = $_GET['TISJUNK'];

if (!is_numeric($TID)){
        die("TID is empty");
}


if (!is_numeric($TISJUNK)){
	die("Need TISJUNK");
}

// Opens a connection to a mySQL server
$connection = mysql_connect("localhost",$username,$password);

if (!$connection) {
  die('Not connected : ' . mysql_error());
}

// Set the active mySQL database

$db_selected = mysql_select_db($database, $connection) or die( "Unable to select database: ". mysql_error());
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
}

$query="
UPDATE tweets 
SET TISJUNK = $TISJUNK 
where TID = $TID
";

//error_log($query);

$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}


//header("Content-type: application/json");
header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");


mysql_close();

$json = "{ \"status\" : \"OK\"}";
echo isset($_GET['callback'])
    ? "{$_GET['callback']}($json)"
    : $json;


// $output['server_id'] = '123';
//    print json_encode($output);
?>
