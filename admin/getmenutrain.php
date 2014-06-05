<?php
$username="nowtracking";
$password="";
$database="nowtracking";


$TR_ID = $_GET['TR_ID'];
$DATE = isset($_GET['DATE'])?$_GET['DATE']:0;

if (is_numeric($DATE)){
        die("Date is empty");
}


if (!is_numeric($TR_ID)){
	die("Need TR_ID");
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

// Select all the rows in the markers table


$DATE = mysql_real_escape_string($DATE);

$query="
SELECT t.tax_id, d.disease, t.term FROM TAXONOMY t
inner join DISEASE d on d.DIS_ID = t.DIS_ID and d.TR_ID = $TR_ID
#where t.isactive = 1
order by d.disease, t.term

";

$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

$num=mysql_numrows($result);

//header("Content-type: application/json");
header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");

$rows = array();

while ($row = @mysql_fetch_assoc($result)){
	$rows[] = $row;
}

mysql_close();

$json = json_encode($rows);

echo isset($_GET['callback'])
    ? "{$_GET['callback']}($json)"
    : $json;


// $output['server_id'] = '123';
//    print json_encode($output);
?>
