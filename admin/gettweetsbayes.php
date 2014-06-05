<?php
$username="nowtracking";
$password="";
$database="nowtracking";

$TAX_ID = $_GET['TAX_ID'];
$DATE = isset($_GET['DATE'])?$_GET['DATE']:0;

if (is_numeric($DATE)){
        die("Date is empty");
}

if (!is_numeric($TAX_ID)){
        die("Need TAX_ID");
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



//$DATE = mysql_real_escape_string($DATE);

// Select all the rows in the markers table

/*
$queryall="
SELECT convert(t.tid,char) as tid, t.screen_name, t.text,t.city, t.state, `dtm`
FROM `tweets` t
#inner join TAXONOMY tx on tx.tax_id = t.tax_id and tx.isactive =1
#inner join DISEASE d on d.dis_id = tx.dis_id and d.isactive =1 and d.tr_id = 1
where feature_id is not null and t.tax_id is not null 
AND t.isjunk = 0
AND t.tisjunk is null
AND t.TAX_ID = $TAX_ID
order by dtm desc
limit 0,50
";
*/

$queryall="
SELECT convert(t.tid,char) as tid, t.screen_name,(t.pisjunk+isjunk) as pisjunk, t.text,t.city, t.state, `dtm`
FROM `tweets` t
#inner join TAXONOMY tx on tx.tax_id = t.tax_id and tx.isactive =1
#inner join DISEASE d on d.dis_id = tx.dis_id and d.isactive =1 and d.tr_id = 1
where feature_id is not null and t.tax_id is not null 
#AND t.pisjunk is not null
and t.isjunk = 0
#AND t.tisjunk is null
AND t.TAX_ID = $TAX_ID
AND cast(dtm as DATE) = '$DATE'
#and text like '%http%'
order by pisjunk, dtm desc
limit 0,750
";

//error_log($queryall);


$query = $queryall;

$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

//header("Content-type: application/json");
header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");

$rows = array();

$n = 0;
while ($row = @mysql_fetch_assoc($result)){
	$n++;
	$rows[] = $row;
}

if ($n==0){
	$rows = array(
		"isempty" => "1"
	);
}



mysql_close();

$json = json_encode($rows);

echo isset($_GET['callback'])
    ? "{$_GET['callback']}($json)"
    : $json;


// $output['server_id'] = '123';
//    print json_encode($output);
?>
