<?php
$username="nowtracking";
$password="";
$database="nowtracking";

$DIS_ID = isset($_GET['DIS_ID'])?$_GET['DIS_ID']:0;
$DATE = trim(file_get_contents("/var/run/dailyaggregates_lastrun"));
//$DATE = isset($_GET['DATE'])?$_GET['DATE']:0;

if (is_numeric($DATE)){
        die("Date is empty");
}

if (!is_numeric($DIS_ID)){
	$DIS_ID = 0;
//        die("Need DIS_ID");
}



$TR_ID = isset($_GET['TR_ID'])?$_GET['TR_ID']:1;

if (!is_numeric($TR_ID)){
	$TR_ID = 1;
//        die("Need DIS_ID");
}

// Start XML file, create parent node

$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);


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


$queryadd = "";
if ($DIS_ID > 0){
	$queryadd = "AND a.DIS_ID = $DIS_ID";
}

$DATE = mysql_real_escape_string($DATE);

$query="
select state_alpha, county_name_long, group_concat(disease) as diseases, latitude, longitude, county_fips, dis_id
from (select distinct state_alpha, county_name_long, a.disease, latitude, longitude, county_fips, a.dis_id
from ALERTS a
inner join DISEASE d on d.dis_id = a.dis_id and d.isactive = 1 and d.SHOWALERTS = 1 and d.tr_id = $TR_ID
where date = '$DATE' 
$queryadd
ORDER BY disease
) as T
group by state_alpha, county_name_long
";


$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

$num=mysql_numrows($result);

//error_log("$num rows fetched");
header("Content-type: text/xml");


# Last modified time is previous midnight (local time)
//$mtime = mktime(0-$tz,0,0,$t['mon'],$t['mday'],$t['year']);
$mtime = strtotime('yesterday');

header('Last-Modified: '.date('D, d M Y H:i:s', $mtime).' GMT');

# Expires time is 1s to next midnight (local time)
$etime = strtotime('midnight');
//$etime = mktime(23-$tz,59,59,$t['mon'],$t['mday'],$t['year']);

header('Expires: '.date('D, d M Y H:i:s', $etime).' GMT');


// Iterate through the rows, adding XML nodes for each
while ($row = @mysql_fetch_assoc($result)){

  
  // ADD TO XML DOCUMENT NODE
  $node = $dom->createElement("marker");
  $newnode = $parnode->appendChild($node);
  $newnode->setAttribute("name",htmlentities($row['county_name_long']).', '.$row['state_alpha']);
  $newnode->setAttribute("address", $row['diseases']);
  $newnode->setAttribute("lat", $row['latitude']);
  $newnode->setAttribute("lng", $row['longitude']);
  $newnode->setAttribute("type", $row['diseases']);
  $newnode->setAttribute("county_fips", $row['county_fips']);
  $newnode->setAttribute("dis_id", $row['dis_id']);


}

mysql_close();

echo $dom->saveXML();

?>
