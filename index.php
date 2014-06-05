<!DOCTYPE html>
<html>
<!-- #BeginTemplate "/Templates/natame2.dwt" --><!-- DW6 -->
<head>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<!-- #BeginEditable "doctitle" --> 
<?
$DIS_ID = $_GET['DIS_ID'];
$title = "Natame - Contagious disease tracker - Outbreaks around the US!";
$description = "Get the latest on contagious diseases in your area including Flu, Common cold, stomach flu, pertusis, etc. This website provides trends of infectious diseases across the US based on twitter data. It provides daily results.";
$keywords = "contagious disease tracker,twitter disease tracker, infectious diseases tracker, outbreaks, flu tracker, common cold tracker, flu map, common cold map";
switch($DIS_ID) {
	case 7: 
		$title = "Flu Tracker";
		$description = "Check the status of the flu in your area and around the US on a map!. This service also tracks a variety of other infectious diseases based on social media data.";
		$keywords = "flu tracker, influenza tracker, flu map, influenza map";
		break;
	case 3: 
		$title = "Common Cold Tracker";
		$description = "Check the status of the common cold in your area and around the US on a map!. This service also tracks a variety of other infectious diseases based on social media data.";
		$keywords = "common cold tracker, cold tracker, cold map, common cold map";
		break;
	case 6: 
		$title = "Stomach Flu Tracker";
		$description = "Check the status of the stomach flu in your area and around the US on a map!. This service also tracks a variety of other infectious diseases based on social media data.";
		$keywords = "stomach flu tracker, stomach bug tracker, stomach flu map, gastrointeritis map";
		break;
}

print "
<title>$title</title>
<meta name='description' content='$description'>
<meta name='keywords' content='$keywords'>
";
//    <script src="http://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyB7OiYDUiBLt7seggA-DBsOxJfknXtextU&sensor=false"


?>
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCRD6w4xesc6U5GDRigBOeF0bLZK6c4wMY&sensor=false"
            type="text/javascript"></script>
  <script src="http://code.jquery.com/jquery-1.7.2.min.js" type="text/javascript"></script> 
<script type="text/javascript"> 
var swapColor = 0;
var scrollbarDiv;
var tweetIndex =0;
var tweets = [];
var showTweet = 0;
var loadingTweets = 0;
var TR_ID = <? echo isset($_GET['TR_ID'])?$_GET['TR_ID']:1 ?>;
var DIS_ID = <? echo isset($_GET['DIS_ID'])?$_GET['DIS_ID']:0 ?>;
var InitialRun = 1;
var COUNTY_FIPS = 0;
var showDate ="";
var infowindow = null;
var markers = [];

function getMenu(){
  $("body").css("cursor", "progress");
  $.ajaxSetup({timeout: 30000});
  var jqxhr = $.getJSON("/nt/getmenu.php?callback=?", 
	{ "TR_ID": TR_ID
	, "DATE" : showDate
	 },
	function(data) {
 	    $("body").css("cursor", "auto");
		$('#menu > option').remove();
		for ( var i=0, len=data.length; i<len; ++i ){
			var selected = "";
			if (DIS_ID == data[i].dis_id && DIS_ID !=0){
				selected = " selected";
				$("#titleText").html(data[i].disease + " tracker"); 
			};
			$('#menu').append('<option value="' + data[i].dis_id+ '"'+selected+'>' + data[i].disease + '</option>');
		}
		$('#menu').change(function() {
 			$("#tweetdiv").animate({
   			 opacity: 0.4
  			}, 800 );
			DIS_ID = $('#menu').val();
			COUNTY_FIPS = 0;
			tweets=[];
			showTweets();
			updateMapMarkers();
		});
	  	return false;
	  	})
	  	.error(function(jqxhr, textStatus, errorThrown) { 
	        //console.log("error " + textStatus);
	        //console.log("incoming Text " + jqxhr.responseText);
	        });
};



function getjsonData(){
  var getimages = 6;
  var imgloaded = 0;
  loadingTweets = 1;
  $("body").css("cursor", "progress");
  $("#tweetdiv").css("background-image", "url(/images/loading.gif)");
  var jqxhr = $.getJSON("/nt/gettweets.php?callback=?", 
	{ "DIS_ID": DIS_ID
	 , "TR_ID": TR_ID
	 , "DATE" : showDate
	 , "COUNTY_FIPS" : COUNTY_FIPS
	},
	function(data) {
  		$("#tweetdiv").css("background-image", "url(/images/spacer.gif)");
		$("body").css("cursor", "auto");
	  	tweets = data;
//		tweets.sort(function() {return 0.5 - Math.random()});
	  	loadingTweets = 0; 
		showTweet = 0;
  		$("#tweetdiv").animate({
		    opacity: 1
  		}, 800 );
		if (InitialRun == 1){
			for (var i = 0;i<15;i++){
				showTweets();
			}
			InitialRun = 0;
		}
	  	return false;
	  	})
//	  	.success(function() { alert("second success"); })
	  	.error(function(jqxhr, textStatus, errorThrown) { 
	        //console.log("error " + textStatus);
	        //console.log("incoming Text " + jqxhr.responseText);
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
			getMenu();
			COUNTY_FIPS = 0;
			showTweets();
			setInterval(showTweets, 3000);
		});

function tweetsforCounty(CFIPS){
	$("#tweetdiv").animate({
   		opacity: 0.4
  	}, 800 );
	COUNTY_FIPS = CFIPS;
	tweets=[];
	showTweets();
	infowindow.close();
}


</script>

<script type="text/javascript">
    //<![CDATA[
    var map;

   function updateMapMarkers(){
	while ( markers.length > 0){
		var marker = markers.pop();
		marker.setMap(null);
	}
	   $("body").css("cursor", "progress");
        jQuery.get("phpsqlajax_genxml.php?DIS_ID="+DIS_ID+"&DATE="+showDate+"&TR_ID="+TR_ID,{}, function(data) {
		$("body").css("cursor", "progress");
		jQuery(data).find("marker").each(function(){
			var marker = jQuery(this);
           	 	var county = marker[0].getAttribute("name");
           	 	var alert = marker[0].getAttribute("address");
           	 	var cfips = marker[0].getAttribute("county_fips");
           	 	var dis_id = marker[0].getAttribute("dis_id");
           	 	var point = new google.maps.LatLng(parseFloat(marker[0].getAttribute("lat")),
                                    parseFloat(marker[0].getAttribute("lng")));
           	 	var gmarker = createMarker(point, county, alert,cfips,dis_id);
			//console.log(JSON.stringify(name));
			gmarker.setMap(map);
			markers.push(gmarker);
          	});
        },"xml");
    };

    function load() {
        map = new google.maps.Map(
		document.getElementById("map"),
		{
			mapTypeControlOptions: {
			      mapTypeIds: []
			    }, 
			center: new google.maps.LatLng(38.822591, -98.503906),
          		zoom: 4,
         		mapTypeId: google.maps.MapTypeId.ROADMAP,
			streetViewControl: false,
		});
        updateMapMarkers();
    }

    function createMarker(point, county, alert, cfips,dis_id) {
      var image = {url: "/images/caution.png",
        size: new google.maps.Size(32, 37),
        origin: new google.maps.Point(0,0),
        anchor: new google.maps.Point(16, 37)};

      var marker = new google.maps.Marker({
          position: point,
          map: map,
          icon: image,
      });
      var html = "<p align='left' style='font-size:normal;'><b>" + county + "</b> <br/>Alert: <b>" + alert+"</b><br/><a href='javascript:tweetsforCounty(\""+cfips+"\");' style='font-size:small;'>Show Tweets</a> | <a href='bycounty.php?<? echo isset($_GET['TR_ID'])?"TR_ID=".$_GET['TR_ID']."&":"" ?>COUNTY_FIPS="+cfips.toString()+"&DIS_ID="+dis_id+"' style='font-size:small;'>Details</a></p>";
      google.maps.event.addListener(marker, 'click', function() {
	if (infowindow) {
	        infowindow.close();
	    }
	infowindow = new google.maps.InfoWindow(
	      { content: html,
	      });
	infowindow.open(map,marker);
      });
	return marker;
     }

      google.maps.event.addDomListener(window, 'load', load);

    //]]>
  </script>
<style type="text/css">
<!--
-->
</style>
<!-- #EndEditable -->
<link rel="stylesheet" href="/images/style.css" type="text/css">
<!--[if IE]>
        <link rel="stylesheet" href="/images/style-ie8.css" type="text/css">
<![endif]-->

</head>
<body>
<div id="header">
<img id="headerimg" src="images/header2.jpg" width="900" height="48" border="0" usemap="#headerMap" alt="Natame Analytics - Disease trends">
</div>
<div id="navmenu">
<img id="navmenuimg" src="/images/menu3.jpg" width="900" height="21" border="0" usemap="#menuMap"></div>

<!-- #BeginEditable "body" --> 
<!--
<div id="ad">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins class="adsbygoogle"
     style="display:inline-block;width:970px;height:90px"
     data-ad-client="ca-pub-4523314683520205"
     data-ad-slot="8565154505"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>
-->
<!-- Natame - Banner -->
<div id="title">
	<div id = "titleText" style='padding:3px'><? echo isset($_GET['TR_ID'])?"Wild Nature Trends":"Contagious Disease Twitter Trends"?></div>
</div>
<div id="pagemenu">
	<div style='padding:2px'>Show alerts for:  <select name="menu" id="menu" tabindex="1"><option>-----------------------------------------</option></select>
	</div>
</div>
<div id="wrapper">
	<div id="map"></div>
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
