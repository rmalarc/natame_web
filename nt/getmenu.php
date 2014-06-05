<?php
$username="nowtracking";
$password="";
$database="nowtracking";


$TR_ID = $_GET['TR_ID'];
//$DATE = isset($_GET['DATE'])?$_GET['DATE']:0;
$DATE = trim(file_get_contents("/var/run/dailyaggregates_lastrun"));

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
select dis_id,disease
from MENU 
where TR_ID = $TR_ID 
	AND DATE = '$DATE'
ORDER BY DISPLAY_ORDER,DISEASE;
";

$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

$num=mysql_numrows($result);

//header("Content-type: application/json");
header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");

# Last modified time is previous midnight (local time)
//$mtime = mktime(0-$tz,0,0,$t['mon'],$t['mday'],$t['year']);
$mtime = strtotime('yesterday');


header('Last-Modified: '.date('D, d M Y H:i:s', $mtime).' GMT');

# Expires time is 1s to next midnight (local time)
//$etime = mktime(23-$tz,59,59,$t['mon'],$t['mday'],$t['year']);
$etime = strtotime('midnight');


header('Expires: '.date('D, d M Y H:i:s', $etime).' GMT');


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
