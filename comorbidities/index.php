<?
include('../images/header.php');
?>
<title>Visualizing Health Data - Comorbidities</title>
<style>
#bodywrapper{
	min-width: 900px !important;
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
  height: 500px;
  min-width : 880px !important;
  display: block;
//  border: 1px solid #DEDEDE;
  border: 1px solid #000000;
//	margin-left: 0 auto;
//	margin-right: 0 auto;
    display: table-cell;
    vertical-align: middle;
}
</style>
<?
include('../images/header-body.php');
?>
<h1>Visualizing Health Data - Related Diseases</h1>
<div id="graphwrapper">
<iframe src="index.html" marginwidth="0" marginheight="0" scrolling="no"></iframe>
</div>
<p>The above graph represents related diseases found in a population of 15,000 patients. This visualisation is based on the <a href="http://www.practicefusion.com/pages/pr/practice-fusion-teams-up-with-microsoft-windows-azure-marketplace.html">Practice Fusion</a> de-identified dataset.</p>
<p align="center"><b>The views expressed on this website are my own personal views and opinions and do not reflect the views of my employer.</b></p>
<?
include('../images/footer.php');
?>
