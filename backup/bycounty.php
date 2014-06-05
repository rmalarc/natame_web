<html><!-- #BeginTemplate "/Templates/natame2.dwt" --><!-- DW6 -->
<head>
<!-- #BeginEditable "doctitle" --> 
<title>Natame - Contagious disease tracker</title>
  <script src="http://code.jquery.com/jquery-latest.js" type="text/javascript"></script> 
<script type="text/javascript"> 
var swapColor = 0;
var scrollbarDiv;
var tweetIndex =0;
var tweets = [];
var showTweet = 0;
var loadingTweets = 0;
var loadingMenu = 0;
var TR_ID = <? echo isset($_GET['TR_ID'])?$_GET['TR_ID']:1 ?>;
var DIS_ID = <? echo isset($_GET['DIS_ID'])?$_GET['DIS_ID']:7 ?>;
var COUNTY_FIPS = "<? echo isset($_GET['COUNTY_FIPS'])?$_GET['COUNTY_FIPS']:01?>";

var showDate ="";

function getMenu(){
  loadingMenu = 1;
  var jqxhr = $.getJSON("/nt/getmenu.php?callback=?", 
	{ "TR_ID": TR_ID
	 },
	function(data) {
//		DIS_ID = data[0].dis_id;
	    loadingMenu = 0;
		$('#menu > option').remove();
		for ( var i=0, len=data.length; i<len; ++i ){
			$('#menu').append('<option value="' + data[i].dis_id + '">' + data[i].disease + '</option>');
		}
		$('#menu').change(function() {
 			$("#tweetdiv").animate({
   			 opacity: 0.4
  			}, 800 );
			DIS_ID = $('#menu').val();
			UpdateChart(COUNTY_FIPS.toString(),DIS_ID.toString(),$('#days').val());

//			COUNTY_FIPS = 0;

			tweets=[];
			showTweets();
		});
  	    $('#menu option[value="'+DIS_ID+'"]').prop('selected', true);
	  	return false;
	  	})
	  	.error(function(jqxhr, textStatus, errorThrown) { 
	        console.log("error " + textStatus);
	        console.log("incoming Text " + jqxhr.responseText);
	        });
};



function getjsonData(){
  var getimages = 6;
  var imgloaded = 0;
  loadingTweets = 1;
  $('#tweetdiv > div').remove();  
  var jqxhr = $.getJSON("/nt/gettweets.php?callback=?", 
	{ "DIS_ID": DIS_ID
	 , "TR_ID": TR_ID
	 , "DATE" : showDate
	 , "COUNTY_FIPS" : COUNTY_FIPS
	},
	function(data) {
	  	tweets = data;
	  	loadingTweets = 0; 
		showTweet = 0;
  		$("#tweetdiv").animate({
		    opacity: 1
  		}, 800 );
		showTweets();
	  	return false;
	  	})
//	  	.success(function() { alert("second success"); })
	  	.error(function(jqxhr, textStatus, errorThrown) { 
	        console.log("error " + textStatus);
	        console.log("incoming Text " + jqxhr.responseText);
	        });
}


function dateFromUTC( dateAsString, ymdDelimiter )
{
  var pattern = new RegExp( "(\\d{4})" + ymdDelimiter + "(\\d{2})" + ymdDelimiter + "(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})" );
  var parts = dateAsString.match( pattern );

  return new Date( Date.UTC(
      parseInt( parts[1] )
    , parseInt( parts[2], 10 ) - 1
    , parseInt( parts[3], 10 )
    , parseInt( parts[4], 10 )
    , parseInt( parts[5], 10 )
    , parseInt( parts[6], 10 )
    , 0
  ));
}

function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}


function showTweets(){
	if(tweets.length > 0 ){
  		var i=0;
	    for (i=0; i<tweets.length ;i++){
			$("#tweetdiv").prepend("<div style='width:100%'></div>") ;
 			$("#tweetdiv div:first").hide();
	 		if ( swapColor ){
	 			$("#tweetdiv div:first").css({'padding': '9px','background-color' : '#FFFFFF' });
	 		} else {
	 			$("#tweetdiv div:first").css({'padding': '9px', 'background-color' : '#EEEEEE' });
	 		}
	 		swapColor = !swapColor;
			var ct = tweets[showTweet];
			var d = dateFromUTC( ct.dtm, '-' ) ;
			var datestr = d.getMonthName('en') +' '+ d.getUTCDate();
	 		$("#tweetdiv div:first").prepend("<div style='margin-right:20px;font-size:medium;'><strong>@"+ct.screen_name+"</strong> "+" "+ct.text+" <strong>"+capitaliseFirstLetter(ct.city)+", "+ct.state.toUpperCase()+" - "+datestr+"</strong></div>");
			$("#tweetdiv div:first").fadeIn();
			showTweet++;
		}
		if(tweets.length == showTweet ){
			showTweet = 0;
		}
	}else {
		if (!loadingTweets){
			$("#tweetdiv").prepend("<div style='width:100%'></div>") ;
 			$("#tweetdiv div:first").css({'padding': '9px','background-color' : '#CCCCCC' });
			getjsonData();
		}
	}
	return false;
}


Date.prototype.getMonthName = function(lang) {
    lang = lang && (lang in Date.locale) ? lang : 'en';
    return Date.locale[lang].month_names[this.getMonth()];
};

Date.prototype.getMonthNameShort = function(lang) {
    lang = lang && (lang in Date.locale) ? lang : 'en';
    return Date.locale[lang].month_names_short[this.getMonth()];
};

Date.locale = {
    en: {
       month_names: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
       month_names_short: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    }
};

function waitMenuLoop() {
//	alert("WML");
	if (!loadingMenu){
		UpdateChart(COUNTY_FIPS.toString(),DIS_ID.toString(),$('#days').val());
		tweets=[];
		showTweets();
	} else {
		setTimeout(waitMenuLoop, 500);
	}
	return true;
};

		$(document).ready(function(){
			var d = new Date();
			d.setDate(d.getDate() -1);
			for ( var i=0; i<1; ++i ){
				var optionstr = d.getMonthName('en') +' '+ d.getUTCDate();
				var optionval = d.getUTCFullYear()+'-'+('0'+(d.getUTCMonth()+1)).slice(-2)+'-'+ ('0'+d.getUTCDate()).slice(-2);
				if (i ==0 ){
					if (!showDate){
						showDate = optionval;
					}
				}
				d.setDate(d.getDate() -1);
			}

			$('#days').change(function() {
				UpdateChart(COUNTY_FIPS.toString(),DIS_ID.toString(),$('#days').val());	
			});
	  	        $('#menu option[value="'+DIS_ID+'"]').prop('selected', true);
			getMenu();
			showTweets();
		});


function UpdateChart(COUNTY_FIPS,DIS_ID,DAYS){
	$("#chart_img").attr("src","/nt/getchart_county.php?DIS_ID="+DIS_ID+"&COUNTY_FIPS="+COUNTY_FIPS+"&DAYS="+DAYS);
	$('#chart_img').load(function() {
//
	});

}

</script>

<style type="text/css">
<!--
.style1 {
	font-size: normal;
	font-weight: bold;
	font-style: italic;
	color: #000000;
	margin: 15px;
}
.cellunderlined {
	border-bottom-width: 1px;
	border-bottom-style: solid;
	border-bottom-color: #666666;
}
-->
</style>
<style type="text/css">
<!--
.style2 {color: #FF5328}
-->
</style>
<style type="text/css">
<!--
.style4 {font-size: small}
-->
</style>
<!-- #EndEditable -->
<meta http-equiv="Content-Type" content="text/html;">
<link rel="stylesheet" href="images/style.css" type="text/css">
<!--[if IE]>
        <link rel="stylesheet" href="images/style-ie8.css" type="text/css">
<![endif]-->

</head>
<body  onload="load()" onUnload="GUnload()">
<div id="header">
<img name="header" src="images/header2.jpg" width="900" height="48" border="0" usemap="#headerMap" alt="Natame Analytics - Disease trends">
</div>
<div id="navmenu">
<img name="menuimg" src="/images/menu3.jpg" width="900" height="21" border="0" usemap="#menuMap"></div>

<!-- #BeginEditable "body" --> 
<div id="title">
	<div style='padding:3px'><? echo isset($_GET['TR_ID'])?"Wild Nature Trends":"Contagious Disease Twitter Trends"?></div>
</div>
<div id="pagemenu">Disease: 
  <select name="menu" id="menu" tabindex="1"><option>-----------------------------------------</option></select> showing   <select name="days" id="days" tabindex="1">
<option value="14">last 14 days</option>
<option value="30">last 30 days</option>
<option value="60">last 60 days</option>
</select>

</div>
<div id="wrapper">
	<div id="map"><img id="chart_img" src="http://www.natame.com/nt/getchart_county.php?DIS_ID=<?echo isset($_GET['DIS_ID'])?$_GET['DIS_ID']:"7"?>&COUNTY_FIPS=<?echo isset($_GET['COUNTY_FIPS'])?$_GET['COUNTY_FIPS']:"01"?>" alt="Chart of tweet traffic for contagious disease for state" width="100%" height="100%"></div>
	<div id="tweetdiv"></div>
</div>

            <!-- #EndEditable -->
<div id="footer">
</div>
<map name="headerMap">
  <area shape="rect" coords="13,5,206,44" href="/" alt="go to homepage">
</map>

<map name="menuMap">
  <area shape="rect" coords="10,4,113,16" href="/index.php" alt="Contagious disease tracker"><area shape="rect" coords="129,5,244,16" href="/bystate.php" alt="Get details by state of contagious disease twitter traffic">
<area shape="rect" coords="368,5,407,17" href="/about.php" alt="About Natame">
<area shape="rect" coords="263,5,356,17" href="/index.php?TR_ID=2"></map></body>
<!-- #EndTemplate --></html>
