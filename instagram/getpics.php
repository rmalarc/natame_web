<?php
$username="instagram";
$password="";
$database="instagram";

$HASHTAG = isset($_GET['HASHTAG'])?$_GET['HASHTAG']:0;



if (is_numeric($HASHTAG)){
        die("Need hashtag $HASHTAG");
}


// Opens a connection to a mySQL server
$connection = mysql_connect("localhost",$username,$password);
if (!$connection) {
  die('Not connected : ' . mysql_error());
}

mysql_set_charset('utf8');

// Set the active mySQL database

$db_selected = mysql_select_db($database, $connection) or die( "Unable to select database: ". mysql_error());
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
}


$query="
SELECT URL
FROM `instagram`
WHERE object_id = '$HASHTAG'
ORDER BY UpdateDtm desc
limit 0,25
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
