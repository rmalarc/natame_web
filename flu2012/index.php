<?
include('../images/header.php');
?>
<title>Visualizing Health Data - 2012 Twitter Flu Map</title>
<meta name="description" content="This interactive map is based on the volume of flu-related tweets during the 2012-2013 flu season from Nov 2012 to it's peak (around Jan 18, 2013).">
<meta name="keywords" content="flu map 2012">
<meta name="description" content="Mauricio Alarcon">
<style>
#bodywrapper{
	min-width: 970px !important;
	width: 75%;
	margin-left: auto;
	margin-right: auto;
	background-color: #FFFFFF;
	border-color: #000000;
	border-left-style: solid;
	border-left-width: 1px;
	border-right-style: solid;
	border-right-width: 1px;
}
h1{
    padding: 0px;
    margin: 10px;
}

h3{
    padding: 0px;
    margin:  0px;
}

#graphwrapper {
  text-align:center;
  margin-left: 0 auto;
  margin-right: 0 auto;
}

iframe {
  display: table;
  width: 100%;
  height: 605px;
  min-width : 955px !important;
  display: block;
//  border: 1px solid #DEDEDE;
  border: 1px solid #000000;
//	margin-left: 0 auto;
//	margin-right: 0 auto;
    display: table-cell;
    vertical-align: top;
}
</style>
<?
include('../images/header-body.php');
?>
<div id="graphwrapper">
<h1 align="left"  style="margin-bottom: 0px;">Twitter Flu Map - 2012-2013 Season</h1>
<iframe src="flu.php?FLU2012" marginwidth="0" marginheight="0" scrolling="no"></iframe>
</div>
<p>This interactive map is based on the volume of flu-related tweets during the 2012-2013 flu season from Nov 2012 to it's peak (around Jan 18, 2013).</p>
<!-- <p align="center"><b>The views expressed on this website are my own personal views and opinions and do not reflect the views of my employer.</b></p>-->
<?
include('../images/footer.php');
?>
