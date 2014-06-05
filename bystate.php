<html><!-- #BeginTemplate "/Templates/natame2.dwt" --><!-- DW6 -->
<head>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<!-- #BeginEditable "doctitle" --> 
<title>Natame - Contagious disease tracker</title>
  <script src="http://code.jquery.com/jquery-1.7.2.min.js" type="text/javascript"></script> 
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
var STATE_FIPS = "<? echo isset($_GET['STATE_FIPS'])?$_GET['STATE_FIPS']:01?>";

var showDate ="";

function getMenu(){
  loadingMenu = 1;
  var jqxhr = $.getJSON("/nt/getmenustate.php?callback=?", 
	{ "TR_ID": TR_ID
	, "STATE_FIPS" : STATE_FIPS
	 },
	function(data) {
//		DIS_ID = data[0].dis_id;
	    loadingMenu = 0;
		$('#menu > option').remove();
		for ( var i=0, len=data.length; i<len; ++i ){
			$('#menu').append('<option value="' + data[i].dis_id + '">' + data[i].disease + '</option>');
		}
		$('#menu').change(function() {
			DIS_ID = $('#menu').val();
//			STATE_FIPS = $('#state').val();
			UpdateChart(STATE_FIPS.toString(),DIS_ID.toString(),$('#days').val());

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
  $("body").css("cursor", "progress");
  $("#tweetdiv").css("background-image", "url(/images/loading.gif)");
//  $("#tweetdiv").animate({
//    opacity: 0.4
//  }, 800 );
  var jqxhr = $.getJSON("/nt/gettweets.php?callback=?", 
	{ "DIS_ID": DIS_ID
	 , "TR_ID": TR_ID
	 , "DATE" : showDate
	 , "STATE_FIPS" : STATE_FIPS
	},
	function(data) {
                $("#tweetdiv").css("background-image", "url(/images/spacer.gif)");
                $("body").css("cursor", "auto");
	  	tweets = data;
	  	loadingTweets = 0; 
		showTweet = 0;
		showTweets();
//  		$("#tweetdiv").animate({
//		    opacity: 1
//  		}, 800 );
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
		UpdateChart(STATE_FIPS.toString(),DIS_ID.toString(),$('#days').val());
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
			$('#state').change(function() {
//		 			$("#tweetdiv").animate({
//			   			 opacity: 0.4
//		  			}, 800 );
//					alert(loadingMenu);
//					DIS_ID = $('#menu').val();
					STATE_FIPS = $('#state').val();
					getMenu();
					setTimeout(waitMenuLoop, 500);
			});

			$('#days').change(function() {
				UpdateChart(STATE_FIPS.toString(),DIS_ID.toString(),$('#days').val());	
			});
//			$('#state option[value="<?echo isset($_GET['STATE_FIPS'])?$_GET['STATE_FIPS']:"01"?>"]').prop('selected', true);
	  	    $('#state option[value="'+STATE_FIPS.toString()+'"]').prop('selected', true);
	  	    $('#menu option[value="'+DIS_ID+'"]').prop('selected', true);
//			$('#menu option[value="<?echo isset($_GET['DIS_ID'])?$_GET['DIS_ID']:"7"?>"]').prop('selected', true);
/*			$('#date').change(function() {
				showDate= $('#date').val();
 				$("#tweetdiv").animate({
   				 opacity: 0.4
  				}, 800 );
				tweets=[];
				getMenu();
				DIS_ID = 0;
				showTweets();
			});
*/			getMenu();
//			COUNTY_FIPS = 0;
			showTweets();
//			setInterval(showTweets, 5000);
		});


function UpdateChart(STATE_FIPS,DIS_ID,DAYS){
	$("#chart_img").attr("src","/nt/getchart.php?DIS_ID="+DIS_ID+"&STATE_FIPS="+STATE_FIPS+"&DAYS="+DAYS);
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
<link rel="stylesheet" href="/images/style.css" type="text/css">
<!--[if IE]>
        <link rel="stylesheet" href="/images/style-ie8.css" type="text/css">
<![endif]-->

</head>
<body  onload="load()" onunload="GUnload()">
<div id="header">
<img name="header" src="images/header2.jpg" width="900" height="48" border="0" usemap="#headerMap" alt="Natame Analytics - Disease trends">
</div>
<div id="navmenu">
<img name="menuimg" src="/images/menu3.jpg" width="900" height="21" border="0" usemap="#menuMap"></div>

<!-- #BeginEditable "body" --> 
<div id="ad">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Natame - Banner -->
<ins class="adsbygoogle"
     style="display:inline-block;width:970px;height:90px"
     data-ad-client="ca-pub-4523314683520205"
     data-ad-slot="8565154505"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
<div id="title">
	<div style='padding:3px'>Contagious Disease Twitter Trends</div>
</div>
<div id="pagemenu">
<div style='padding:2px'>
Show chart for state: <select name="state" id="state" tabindex="2">
<option value="01">Alabama</option>
<option value="02">Alaska</option>
<option value="04">Arizona</option>
<option value="05">Arkansas</option>
<option value="06">California</option>
<option value="08">Colorado</option>
<option value="09">Connecticut</option>
<option value="10">Delaware</option>
<option value="11">District of Columbia</option>
<option value="12">Florida</option>
<option value="13">Georgia</option>
<option value="15">Hawaii</option>
<option value="16">Idaho</option>
<option value="17">Illinois</option>
<option value="18">Indiana</option>
<option value="19">Iowa</option>
<option value="20">Kansas</option>
<option value="21">Kentucky</option>
<option value="22">Louisiana</option>
<option value="23">Maine</option>
<option value="24">Maryland</option>
<option value="25">Massachusetts</option>
<option value="26">Michigan</option>
<option value="27">Minnesota</option>
<option value="28">Mississippi</option>
<option value="29">Missouri</option>
<option value="30">Montana</option>
<option value="31">Nebraska</option>
<option value="32">Nevada</option>
<option value="33">New Hampshire</option>
<option value="34">New Jersey</option>
<option value="35">New Mexico</option>
<option value="36">New York</option>
<option value="37">North Carolina</option>
<option value="38">North Dakota</option>
<option value="39">Ohio</option>
<option value="40">Oklahoma</option>
<option value="41">Oregon</option>
<option value="42">Pennsylvania</option>
<option value="44">Rhode Island</option>
<option value="45">South Carolina</option>
<option value="46">South Dakota</option>
<option value="47">Tennessee</option>
<option value="48">Texas</option>
<option value="49">Utah</option>
<option value="50">Vermont</option>
<option value="51">Virginia</option>
<option value="53">Washington</option>
<option value="54">West Virginia</option>
<option value="55">Wisconsin</option>
<option value="56">Wyoming</option>
</select> disease: 
  <select name="menu" id="menu" tabindex="1"><option>-----------------------------------------</option></select> showing   <select name="days" id="days" tabindex="1">
<option value="14">last 14 days</option>
<option value="30">last 30 days</option>
<option value="60">last 60 days</option>
<option value="90">last 90 days</option>
<option value="180">last 180 days</option>
<option value="365">last 365 days</option>
</select>

</div>
</div>
<div id="wrapper">
	<div id="map"><img src="http://www.natame.com/nt/getchart.php?DIS_ID=<?echo isset($_GET['DIS_ID'])?$_GET['DIS_ID']:"7"?>&STATE_FIPS=<?echo isset($_GET['STATE_FIPS'])?$_GET['STATE_FIPS']:"01"?>" alt="Chart of tweet traffic for contagious disease for state" name="chart_img" width="100%" height="100%" align="absmiddle" id="chart_img"></div>
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
