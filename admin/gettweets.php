<?php
$username="nowtracking";
$password="";
$database="nowtracking";

$DIS_ID = $_GET['DIS_ID'];
$DATE = isset($_GET['DATE'])?$_GET['DATE']:0;

if (is_numeric($DATE)){
        die("Date is empty");
}


if (!is_numeric($DIS_ID)){
        die("Need DIS_ID");
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



if ($DIS_ID == 0){
	$queryadd = "AND tx.DIS_ID in (SELECT DIS_ID FROM ALERTS WHERE DATE = '$DATE')";
} else if ($DIS_ID == -1){
	$queryadd = "AND tx.DIS_ID NOT in (SELECT DIS_ID FROM ALERTS WHERE DATE = '$DATE')";
} else {
	$queryadd = "AND tx.DIS_ID = $DIS_ID";
}



$DATE = mysql_real_escape_string($DATE);

// Select all the rows in the markers table


$queryall="
SELECT distinct t.screen_name, t.text,t.city, t.state, `dtm`
FROM `tweets` t
inner join TAXONOMY tx on tx.tax_id = t.tax_id and tx.isactive =1
inner join DISEASE d on d.dis_id = tx.dis_id and d.isactive =1 and d.tr_id = 1
where feature_id is not null and t.tax_id is not null 
        AND ISJUNK = 0
        AND PISJUNK <BAYES_P
#AND (t.isjunk + t.pisjunk) < 0.4
#AND t.isjunk = 0
and date(`dtm`) <=  '$DATE'
$queryadd
#AND date(`dtm`) >= '$DATE'
order by dtm desc
limit 0,100
";

$queryfromtrending="
SELECT distinct t.screen_name, t.text,t.city, t.state, `dtm`
FROM `tweets` t
inner join TAXONOMY tx on tx.tax_id = t.tax_id and tx.isactive =1
inner join DISEASE d on d.dis_id = tx.dis_id and d.isactive =1 and d.tr_id = 1
inner join ALERTS a on a.dis_id = tx.dis_id and tx.isactive =1 AND a.DATE = '$DATE'
inner join POP_PLACES p on p.county_FIPS = a.county_fips AND  p.feature_ID = t.feature_ID
where t.feature_id is not null and t.tax_id is not null 
        AND ISJUNK = 0
        AND PISJUNK <BAYES_P
#AND (t.isjunk + t.pisjunk) < 0.4
#AND t.isjunk = 0
and date(`dtm`) <=  '$DATE'
$queryadd
#AND date(`dtm`) >= '$DATE'
order by dtm desc
limit 0,100
";


$query = $queryfromtrending;

$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

$num=mysql_numrows($result);

if ($num==0){
	$query = $queryall;
	$result=mysql_query($query) or die( "Error in query". mysql_error());
	if (!$result) {
  		die('Invalid query: ' . mysql_error());
	}
}

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
