<html><!-- #BeginTemplate "/Templates/natame2.dwt" --><!-- DW6 -->
<head>
<!-- #BeginEditable "doctitle" --> 
<title>Natame - Contagious disease tracker</title>
    <script src="http://maps.google.com/maps?file=api&v=2&key=AIzaSyB7OiYDUiBLt7seggA-DBsOxJfknXtextU"
            type="text/javascript"></script>
  <script src="http://code.jquery.com/jquery-latest.js" type="text/javascript"></script> 
<script type="text/javascript"> 
var swapColor = 0;
var scrollbarDiv;
var tweetIndex =0;
var tweets = [];
var showTweet = 0;
var loadingTweets = 0;
var TR_ID = <? echo isset($_GET['TR_ID'])?$_GET['TR_ID']:1 ?>;
var DIS_ID = 0;
var COUNTY_FIPS = 0;
var showDate ="";

function getMenu(){
  $("body").css("cursor", "progress");
  var jqxhr = $.getJSON("/nt/getmenu.php?callback=?", 
	{ "TR_ID": TR_ID
	, "DATE" : showDate
	 },
	function(data) {
 	    $("body").css("cursor", "auto");
		$('#menu > option').remove();
		for ( var i=0, len=data.length; i<len; ++i ){
			$('#menu').append('<option value="' + data[i].dis_id + '">' + data[i].disease + '</option>');
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
	        console.log("error " + textStatus);
	        console.log("incoming Text " + jqxhr.responseText);
	        });
};



function getjsonData(){
  var getimages = 6;
  var imgloaded = 0;
  loadingTweets = 1;
  $("body").css("cursor", "progress");
  var jqxhr = $.getJSON("/nt/gettweets.php?callback=?", 
	{ "DIS_ID": DIS_ID
	 , "TR_ID": TR_ID
	 , "DATE" : showDate
	 , "COUNTY_FIPS" : COUNTY_FIPS
	},
	function(data) {
		$("body").css("cursor", "auto");
	  	tweets = data;
	  	loadingTweets = 0; 
		showTweet = 0;
  		$("#tweetdiv").animate({
		    opacity: 1
  		}, 800 );
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

function tweetsforCounty(CFIPS,mid){
	$("#tweetdiv").animate({
   		opacity: 0.4
  	}, 800 );
//	map.Fc[mid].close();
	COUNTY_FIPS = CFIPS;
	tweets=[];
	showTweets();
}


</script>

<script type="text/javascript">
    //<![CDATA[

    var iconBlue = new GIcon();
    iconBlue.image = 'http://labs.google.com/ridefinder/images/mm_20_blue.png';
    iconBlue.shadow = 'http://labs.google.com/ridefinder/images/mm_20_shadow.png';
    iconBlue.iconSize = new GSize(12, 20);
    iconBlue.shadowSize = new GSize(22, 20);
    iconBlue.iconAnchor = new GPoint(6, 20);
    iconBlue.infoWindowAnchor = new GPoint(5, 1);

    var iconRed = new GIcon();
    iconRed.image = 'http://labs.google.com/ridefinder/images/mm_20_red.png';
    iconRed.shadow = 'http://labs.google.com/ridefinder/images/mm_20_shadow.png';
    iconRed.iconSize = new GSize(12, 20);
    iconRed.shadowSize = new GSize(22, 20);
    iconRed.iconAnchor = new GPoint(6, 20);
    iconRed.infoWindowAnchor = new GPoint(5, 1);

    var customIcons = [];
    customIcons["restaurant"] = iconBlue;
    customIcons["bar"] = iconRed;
    var map;

   function updateMapMarkers(){
        map.clearOverlays();
//        map.invalidate();
	   $("body").css("cursor", "progress");

        GDownloadUrl("phpsqlajax_genxml.php?DIS_ID="+DIS_ID+"&DATE="+showDate+"&TR_ID="+TR_ID, function(data) {
		  $("body").css("cursor", "progress");
          var xml = GXml.parse(data);
          var markers = xml.documentElement.getElementsByTagName("marker");
          for (var i = 0; i < markers.length; i++) {
            var name = markers[i].getAttribute("name");
            var address = markers[i].getAttribute("address");
            var type = markers[i].getAttribute("type");
            var cfips = markers[i].getAttribute("county_fips");
            var dis_id = markers[i].getAttribute("dis_id");
            var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),
                                    parseFloat(markers[i].getAttribute("lng")));
            var marker = createMarker(point, name, address, type,cfips,i,dis_id);
            map.addOverlay(marker);
          }
        });
    }

    function load() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
		map.enableScrollWheelZoom();
		map.setMapType(G_NORMAL_MAP);
        map.setCenter(new GLatLng(38.822591, -98.503906), 4);
        updateMapMarkers();
      }
    }

    function createMarker(point, name, address, type, cfips,mid,dis_id) {
      var marker = new GMarker(point, customIcons[type]);
      var html = "<p align='left'><b>" + name + "</b> <br/>" + address+"<br/><a href='javascript:tweetsforCounty(\""+cfips+"\","+mid+");' style='font-size:x-small;'>Show Tweets</a> | <a href='bycounty.php?<? echo isset($_GET['TR_ID'])?"TR_ID=".$_GET['TR_ID']."&":"" ?>COUNTY_FIPS="+cfips.toString()+"&DIS_ID="+dis_id+"' style='font-size:x-small;'>Details</a></p>";
      GEvent.addListener(marker, 'click', function() {
        marker.openInfoWindowHtml(html);
      });
      return marker;
    }
    //]]>
  </script>
<style type="text/css">
<!--
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
<img name="menuimg" src="images/menu3.jpg" width="900" height="21" border="0" usemap="#menuMap">
</div>
<!-- #BeginEditable "body" --> 
<div id="title">
	<div style='padding:3px'><? echo isset($_GET['TR_ID'])?"Wild Nature Trends":"Contagious Disease Twitter Trends"?></div>
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
<img name="menuimg" src="images/spacer.gif" width="900" height="1" border="0">
</div>
<map name="headerMap">
  <area shape="rect" coords="13,5,206,44" href="/" alt="go to homepage">
</map>

<map name="menuMap">
  <area shape="rect" coords="10,4,113,16" href="/index.php" alt="Contagious disease tracker"><area shape="rect" coords="129,5,244,16" href="/bystate.php" alt="Get details by state of contagious disease twitter traffic">
<area shape="rect" coords="368,5,407,17" href="/about.php" alt="About Natame">
<area shape="rect" coords="263,5,356,17" href="/index.php?TR_ID=2"></map></body>
<!-- #EndTemplate --></html>

