<?php
$username="nowtracking";
$password="";
$database="nowtracking";


$STATE_FIPS = isset($_GET['STATE_FIPS'])?$_GET['STATE_FIPS']:0;
$DATE = trim(file_get_contents("/var/run/dailyaggregates_lastrun"));

$TR_ID = isset($_GET['TR_ID'])?$_GET['TR_ID']:1;


if (!is_numeric($TR_ID)){
        die("Need DIS_ID");
}

if (!is_numeric($STATE_FIPS)){
        die("COUNTY_FIPS must be numeric");
}

//$DATE = isset($_GET['DATE'])?$_GET['DATE']:0;

//if (is_numeric($DATE)){
//        die("Date is empty");
//}


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



$DATE = mysql_real_escape_string($DATE);

$query="
   select d.dis_id, CASE 
#			WHEN avg(normalized) >0 then concat_ws(' ',d.disease, '(UP)')
#			WHEN avg(normalized) <0 then concat_ws(' ',d.disease , '(DOWN)')
#			WHEN avg(normalized) is not null then concat_ws(' ',d.disease , '(Active)')
			WHEN avg(avg14d) is not null then concat_ws(' ',d.disease , '(Active)')
			ELSE d.disease
		   END AS disease, avg(normalized) as total
   from DISEASE d 
   LEFT JOIN (
        select t.date,(sum(UCOUNT) - AVG14d) /STD14d as normalized, tx.DIS_ID, avg14d
        from AGGREGATES t
        inner join TAXONOMY tx on tx.TAX_ID = t.TAX_ID 
        inner join MOVAVG ma on ma.TAX_ID = t.TAX_ID AND t.date = ma.date and ma.STATE_fips = t.STATE_fips AND t.STATE_FIPS = $STATE_FIPS
		WHERE t.date = '$DATE'
        GROUP BY t.date, t.STATE_FIPS, t.TAX_ID
   ) as a on a.DIS_ID = d.DIS_ID
   WHERE d.TR_ID = $TR_ID
   group by date, DIS_ID
   order by 3 desc,2;
";

//$query="
//   select d.dis_id, d.disease, null as total
//   from DISEASE d 
//   WHERE d.TR_ID = $TR_ID
//   order by 2;
//";

//error_log($query);

$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

$num=mysql_numrows($result);

//header("Content-type: application/json");
header("Content-type: application/json");
header("Access-Control-Allow-Origin: *");

# Last modified time is previous midnight (local time)
$mtime = strtotime('yesterday');


header('Last-Modified: '.date('D, d M Y H:i:s', $mtime).' GMT');

# Expires time is 1s to next midnight (local time)
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
