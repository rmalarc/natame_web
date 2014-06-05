<?php
$username="nowtracking";
$password="";
$database="nowtracking";

$DIS_ID = $_GET['DIS_ID'];
$DAYS = isset($_GET['DAYS'])?$_GET['DAYS']:14;
$DATE = trim(file_get_contents("/var/run/dailyaggregates_lastrun"));
//$DAYS = 40;
$COUNTY_FIPS = isset($_GET['COUNTY_FIPS'])?$_GET['COUNTY_FIPS']:0;

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

if (!is_numeric($COUNTY_FIPS)){
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



//$DATE = mysql_real_escape_string($DATE);

// Select all the rows in the markers table


$query="
select date_format(d.DATE,'%b %e') as DATE, total
from DATES d
LEFT JOIN (
	select date, sum(UCOUNT) as total
	from AGGREGATES t 
	inner join TAXONOMY tx on tx.TAX_ID = t.TAX_ID and tx.DIS_ID = $DIS_ID and tx.ISACTIVE =1
	where COUNTY_FIPS = $COUNTY_FIPS
	group by date
) ad on ad.date = d.date 
where d.date >= '$DATE' - interval $DAYS day and d.date <= '$DATE';
";


$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}


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
//$xdata = array(1,'',3,'',5,'',7,'',9,'',11,'',13,14);

// get name of disease

$query="
select DISEASE 
from DISEASE
where DIS_ID = $DIS_ID
";


$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}


$disname = "";
while ($row = @mysql_fetch_assoc($result)){
	$disname = $row["DISEASE"];
}



// get name of state


$query="
select COUNTY_NAME_LONG
from POP_PLACES_COUNTY
where COUNTY_FIPS = $COUNTY_FIPS
";


$result=mysql_query($query) or die( "Error in query". mysql_error());
if (!$result) {
  die('Invalid query: ' . mysql_error());
}


$statename = "";
while ($row = @mysql_fetch_assoc($result)){
	$statename = $row["COUNTY_NAME_LONG"];
}






mysql_close();



//header("Content-type: application/json");
//header("Content-type: application/json");
//header("Access-Control-Allow-Origin: *");
# Last modified time is previous midnight (local time)
//$mtime = mktime(0-$tz,0,0,$t['mon'],$t['mday'],$t['year']);
$mtime = strtotime('yesterday');
header('Last-Modified: '.date('D, d M Y H:i:s', $mtime).' GMT');

# Expires time is 1s to next midnight (local time)
//$etime = mktime(23-$tz,59,59,$t['mon'],$t['mday'],$t['year']);
$etime = strtotime('midnight');


header('Expires: '.date('D, d M Y H:i:s', $etime).' GMT');




include ("/usr/share/jpgraph/jpgraph.php");
include ("/usr/share/jpgraph/jpgraph_bar.php");
//include ("/usr/share/jpgraph/jpgraph_utils.inc.php");


// Now get labels at the start of each month
//$dateUtils = new DateScaleUtils();
//list($tickPositions,$minTickPositions) = $dateUtils->GetTicks($xdata);
//$ydata = array(11,11,11,12,12,12);
//$ydata = $rows["DATE"];

// Create the graph.
//list($tickPositions,$minTickPositions) = 
//	DateScaleUtils::GetTicks($xdata);

$graph = new Graph(598,400,"auto");

//$graph->SetScale('textlin',0,$max*1.2);
$graph->SetScale('textlin');
$graph->SetMarginColor('white');
$graph->SetBox(false);


// Add a drop shadow
//$graph->SetShadow();

$graph->img->SetMargin(25,25,25,40);

// Create a bar pot
$bplot = new BarPlot($ydata);


// Adjust fill color
//$bplot->SetFillColor('orange');
$bplot->SetFillColor('chocolate1');
$bplot->ShowValue(true);
$bplot->SetValueMargin(0);

//$bplot->SetWidth(1);
//$bplot->value->Show();
//$bplot->value->SetColor("red");
//$bplot->value->SetFont(FF_FONT1,FS_BOLD);




$graph->Add($bplot);

// Setup the titles
$graph->title->Set("Tweet count for: $statename - $disname ($DAYS days)");
//$graph->xaxis->title->Set('X-title');

$graph->xaxis->SetPos("min");   // "min" will position the x-axis at the minimum value of the Y-axis
$graph->xaxis->SetTickLabelMargin(6);
$graph->xaxis->SetTickDirection(SIDE_DOWN);
$graph->xaxis->SetTickLabels($xdata);
//$graph->xaxis->SetTextLabelInterval(2);
$graph->xaxis->SetTextTickInterval(round(count($xdata)/8));

// Extend the margin for the labels on the Y-axis and reverse the direction
// of the ticks on the Y-axis
$graph->yaxis->title->Set('Tweet Count');
$graph->yaxis->SetTickLabelMargin(8);
$graph->yaxis->scale->SetGrace(20);
$graph->yaxis->SetTickDirection(SIDE_LEFT);
$graph->yaxis->Hide();




$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);




// Display the graph
$graph->Stroke();
//$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
//$graph->title->Set("Example 1.1 same y-values");

// Create the linear plot
//$lineplot=new LinePlot($ydata);//, $xdata);
//$lineplot->SetLegend("Test 1");
//$lineplot->SetColor("gray");
//$lineplot->SetWeight(2);

// Add the plot to the graph
//$graph->Add($lineplot);

// Display the graph
//$graph->Stroke();


?>
