<?php
$username="nowtracking";
$password="";
$database="nowtracking";


$DISEASE = isset($_GET['DISEASE'])?$_GET['DISEASE']:0;
$DATE = isset($_GET['DATE'])?$_GET['DATE']:trim(file_get_contents("/var/run/dailyaggregates_lastrun"));

if (is_numeric($DATE)){
        die("Date is empty");
}


if (is_numeric($DISEASE)){
	die("Need DISEASE");
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
$DISEASE = mysql_real_escape_string($DISEASE);
//DATE_FORMAT(date, '%Y-%m-%dT%TZ') AS DATE

$query="
select 
DATE
,TRIM(LEADING '0' FROM COUNTY_FIPS) AS COUNTY_FIPS
,Tweets7D
, COUNTY_NAME_LONG County
,STATE_ALPHA as State
#,POPESTIMATE2011 as Pop
,Days
,Disease
,TweetsPM
,Score as Score
from HEATMAP 
where DISEASE = '$DISEASE' 
	AND DATE between '$DATE' - interval 60 day and '$DATE'
	and DAYOFWEEK(DATE) = 7
ORDER BY DATE DESC
";

$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

$num=mysql_numrows($result);

$rows = array();

$dates = 0;
$prevDate = 0;
$tweetsTotal = 0;
$DateList = array();
$Data = array();
$Node;
while ($row = @mysql_fetch_assoc($result)){
	if ($prevDate == 0){
		$prevDate = $row['DATE'];
//		$DateList[] = array('Date','Tweets per Million');
	}
	if ($prevDate <> $row['DATE']){
		$Node = array(dt => $prevDate,data=>$rows);
		$DateData[] = $Node;
		$rows = null;
		$DateList[] = array($prevDate, $tweetsTotal);
		$prevDate = $row['DATE'];
		$tweetsTotal = 0;
		$dates++;
	}
	$rows[] = $row;
//	$tweetsTotal += $row['TweetsPM'];
	$tweetsTotal += $row['Tweets7D'];
}

$DateList[] = array($prevDate, $tweetsTotal);
$Node = array(dt => $prevDate,data=>$rows);
$DateData[] = $Node;

$data = array(
	range => $dates,
	chartData => $DateList,
	data => $DateData
//	trends => $DateData
);

//$data['data'] = $rows;

mysql_close();

$json = json_encode($data);

echo isset($_GET['callback'])
    ? "{$_GET['callback']}($json)"
    : $json;


?>
