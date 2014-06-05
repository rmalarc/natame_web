<?php
$username="nowtracking";
$password="";
$database="nowtracking";

$DIS_ID = $_GET['DIS_ID'];
$DATE = trim(file_get_contents("/var/run/dailyaggregates_lastrun"));

$DAYS = isset($_GET['DAYS'])?$_GET['DAYS']:0;
$STATE_FIPS = isset($_GET['STATE_FIPS'])?$_GET['STATE_FIPS']:0;

$TR_ID = isset($_GET['TR_ID'])?$_GET['TR_ID']:1;

if (!is_numeric($DAYS)){
        die("Date is empty");
}


if (!is_numeric($DIS_ID)){
        die("Need DIS_ID");
}

if (!is_numeric($TR_ID)){
        die("Need DIS_ID");
}

if (!is_numeric($STATE_FIPS)){
        die("COUNTY_FIPS must be numeric");
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



$DATE = mysql_real_escape_string($DATE);

// Select all the rows in the markers table


$query="
select date_format(d.DATE,'%b %e') as DATE, total
from DATES d
LEFT JOIN (
	select date, avg(normavg) as total
	from TRENDS t 
	INNER JOIN POP_PLACES_COUNTY pc on pc.COUNTY_FIPS = t.COUNTY_FIPS AND STATE_FIPS = $STATE_FIPS
	where DIS_ID = $DIS_ID
	group by date
) ad on ad.date = d.date 
where d.date >= '$DATE' - interval $DAYS day and d.date <= '$DATE';
";


$query="
select date_format(d.DATE,'%b %e') as DATE, total
from DATES d
LEFT JOIN (
   select date, avg(normalized) as total
   from (
        select t.date,(sum(UCOUNT) - AVG14d) /STD14d as normalized
        from AGGREGATES t
        inner join TAXONOMY tx on tx.TAX_ID = t.TAX_ID and tx.DIS_ID = $DIS_ID
        inner join MOVAVG ma on ma.TAX_ID = t.TAX_ID AND t.date = ma.date and ma.STATE_fips = t.STATE_fips AND t.STATE_FIPS = $STATE_FIPS
        GROUP BY t.date, t.STATE_FIPS, t.TAX_ID
   ) as xx
   group by date
) ad on ad.date = d.date
where d.date >= '$DATE' - interval $DAYS day and d.date <= '$DATE';
";



$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}



//header("Content-type: application/json");
//header("Content-type: application/json");
//header("Access-Control-Allow-Origin: *");
# Last modified time is previous midnight (local time)
$mtime = mktime(0-$tz,0,0,$t['mon'],$t['mday'],$t['year']);

header('Last-Modified: '.date('D, d M Y H:i:s', $mtime).' GMT');

# Expires time is 1s to next midnight (local time)
$etime = mktime(23-$tz,59,59,$t['mon'],$t['mday'],$t['year']);

header('Expires: '.date('D, d M Y H:i:s', $etime).' GMT');


$xdata = array();
$ydata =  array();
$max = 0;
while ($row = @mysql_fetch_assoc($result)){
	$xdata[] = $row["DATE"];
	$ydata[] = $row["total"];
	if ($row["total"] > $max){
		$max = $row["total"];
	}
}

mysql_close();



include ("/usr/share/jpgraph/jpgraph.php");
include ("/usr/share/jpgraph/jpgraph_scatter.php");


function mycallback($l) {
        return sprintf("%02.2f",$l);
}

// Setup the basic parameters for the graph
$graph = new Graph(800,600,"auto");
$graph->SetScale("intlin");
$graph->SetShadow();
$graph->SetBox();
$graph->title->Set("Impuls Example 3");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Set format for labels
$graph->yaxis->SetLabelFormatString("%-02.1f");
$graph->yaxis->SetLabelFormatCallback("mycallback");

// Set X-axis at the minimum value of Y-axis (default will be at 0)
$graph->xaxis->SetPos("min");   // "min" will position the x-axis at the minimum value of the Y-axis

// Extend the margin for the labels on the Y-axis and reverse the direction
// of the ticks on the Y-axis
$graph->yaxis->SetTickLabelMargin(12);
$graph->xaxis->SetTickLabelMargin(6);
$graph->yaxis->SetTickDirection(SIDE_LEFT);
$graph->xaxis->SetTickDirection(SIDE_DOWN);
$graph->xaxis->SetTickLabels($xdata);

// Create a new impuls type scatter plot
$sp1 = new ScatterPlot($ydata);
$sp1->mark->SetType(MARK_SQUARE);
$sp1->mark->SetFillColor("red");
$sp1->SetImpuls();
$sp1->SetColor("blue");
$sp1->SetWeight(1);
$sp1->mark->SetWidth(3);

$graph->Add($sp1);

$graph->Stroke();



?>
