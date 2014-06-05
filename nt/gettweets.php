<?php
$username="nowtracking";
$password="";
$database="nowtracking";

$DIS_ID = isset($_GET['DIS_ID'])?$_GET['DIS_ID']:0;
//$DATE = isset($_GET['DATE'])?$_GET['DATE']:0;
$DATE = trim(file_get_contents("/var/run/dailyaggregates_lastrun"));
//error_log($DATE);
$COUNTY_FIPS = isset($_GET['COUNTY_FIPS'])?$_GET['COUNTY_FIPS']:0;
$STATE_FIPS = isset($_GET['STATE_FIPS'])?$_GET['STATE_FIPS']:0;


$TR_ID = isset($_GET['TR_ID'])?$_GET['TR_ID']:1;

if (!is_numeric($TR_ID)){
        $TR_ID = 1;
//        die("Need DIS_ID");
}



if (is_numeric($DATE)){
        die("Date is empty");
}


if (!is_numeric($DIS_ID)){
	$DIS_ID = 0;
//        die("Need DIS_ID");
}

if (!is_numeric($TR_ID)){
        die("Need TR_ID");
}

if (!is_numeric($COUNTY_FIPS)){
        die("COUNTY_FIPS must be numeric");
}

if (!is_numeric($STATE_FIPS)){
        die("COUNTY_FIPS must be numeric");
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


if ($DIS_ID == 0){
	$queryadd = "AND tx.DIS_ID in (SELECT DISTINCT DIS_ID FROM ALERTS WHERE DATE = '$DATE')";
} else if ($DIS_ID == -1){
	$queryadd = "AND tx.DIS_ID NOT in (SELECT DISTINCT DIS_ID FROM ALERTS WHERE DATE = '$DATE')";
} else {
	$queryadd = "AND tx.DIS_ID = $DIS_ID";
}

if ($COUNTY_FIPS != 0){
	$queryfips = "AND p.county_FIPS = $COUNTY_FIPS";
} else {
	$queryfips = "";
}

if ($DIS_ID == 0){
	$query_alerts = "INNER join ALERTS a on a.dis_id = tx.dis_id and tx.isactive =1 AND a.DATE = '$DATE' and  p.county_FIPS = a.county_fips";

#	$query_alerts = "inner join ALERTS a on p.county_FIPS = a.county_fips AND a.dis_id = tx.dis_id AND a.DATE = '$DATE'";
} else {
	$query_alerts = "";
}

$DATE = mysql_real_escape_string($DATE);

// Select all the rows in the markers table


$queryall="
SELECT distinct t.screen_name, t.text,t.city, t.state, `dtm`
FROM `tweets` t
inner join TAXONOMY tx on tx.tax_id = t.tax_id and tx.isactive =1
inner join DISEASE d on d.dis_id = tx.dis_id and d.isactive =1 and d.tr_id = $TR_ID
where feature_id is not null and t.tax_id is not null 
        AND ISJUNK = 0
        AND PISJUNK <BAYES_P
#AND (t.isjunk + t.pisjunk) < 0.4
#AND t.isjunk = 0
#and date(`dtm`) <=  '$DATE'
and `dt` <=  '$DATE'
$queryadd
#AND date(`dtm`) >= '$DATE'
order by dtm desc
limit 0,50
";

$queryfromtrending="
SELECT distinct t.screen_name, t.text,t.city, t.state, `dtm`
FROM `tweets` t
inner join TAXONOMY tx on tx.tax_id = t.tax_id and tx.isactive =1
inner join DISEASE d on d.dis_id = tx.dis_id and d.isactive =1 and d.tr_id = $TR_ID
inner join POP_PLACES p on p.feature_ID = t.feature_ID $queryfips
$query_alerts
where t.feature_id is not null and t.tax_id is not null 
        AND ISJUNK = 0
        AND PISJUNK <BAYES_P
and `dt` <=  '$DATE'
$queryadd
order by dtm desc
limit 0,50
";
//#inner join POP_PLACES p on p.feature_ID = t.feature_ID $queryfips
//#LEFT join ALERTS a on a.dis_id = tx.dis_id and tx.isactive =1 AND a.DATE = '$DATE' and  p.county_FIPS = a.county_fips
//#order by a.date desc, dtm desc


if ($STATE_FIPS != 0){
	$queryfromtrending="
		SELECT distinct t.screen_name, t.text,t.city, t.state, `dtm`
		FROM `tweets` t
		inner join TAXONOMY tx on tx.tax_id = t.tax_id and tx.isactive =1
		inner join DISEASE d on d.dis_id = tx.dis_id and d.isactive =1 and d.tr_id = $TR_ID
		inner join POP_PLACES p on p.feature_ID = t.feature_ID AND p.STATE_FIPS = $STATE_FIPS
		where t.feature_id is not null and t.tax_id is not null 
		        AND ISJUNK = 0
		        AND PISJUNK <BAYES_P
#			AND (t.isjunk + t.pisjunk) < 0.4
			and `dt` <=  '$DATE'
			$queryadd
		order by dtm desc
		limit 0,50
	";
//	error_log($queryfromtrending);

}

$query = $queryfromtrending;
//error_log($query,3,"/tmp/gettweets.log");

$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

$num=mysql_numrows($result);

if ($num==0){
//error_log("no results from : $query");
	$query = $queryall;
	//error_log("QUERYALL: "+$query,3,"/tmp/gettweets.log");
//error_log("trying: $query");
	$result=mysql_query($query) or die( "Error in query". mysql_error());
	if (!$result) {
  		die('Invalid query: ' . mysql_error());
	}
}

//header("Content-type: application/json");
header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");
# Last modified time is previous midnight (local time)
$mtime = strtotime('yesterday');

//$mtime = mktime(0-$tz,0,0,$t['mon'],$t['mday'],$t['year']);

//header('Last-Modified: '.date('D, d M Y H:i:s', $mtime).' GMT');

# Expires time is 1s to next midnight (local time)
//$etime = mktime(23-$tz,59,59,$t['mon'],$t['mday'],$t['year']);
//$etime = strtotime('midnight');

//header('Expires: '.date('D, d M Y H:i:s', $etime).' GMT');


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
