<html><!-- #BeginTemplate "/Templates/mainpage-nopic.dwt" --><!-- DW6 -->
<head>
<!-- #BeginEditable "doctitle" --> 
<title>Natame - Contagious disease tracker</title>
    <script src="http://maps.google.com/maps?file=api&v=2&key=AIzaSyB7OiYDUiBLt7seggA-DBsOxJfknXtextU"
            type="text/javascript"></script>
  <script src="http://code.jquery.com/jquery-1.7.2.min.js"></script> 
<script> 
var swapColor = 0;
var scrollbarDiv;
var tweetIndex =0;
var tweets = [];
var showTweet = 0;
var loadingTweets = 0;
var TR_ID = 1;
var DIS_ID = 0;
var showDate ="";

function getMenu(){

  var jqxhr = $.getJSON("/nt/getmenu.php?callback=?", 
	{ "TR_ID": TR_ID
	, "DATE" : showDate
	 },
	function(data) {
		$('#menu > option').remove();
		for ( var i=0, len=data.length; i<len; ++i ){
			$('#menu').append('<option value="' + data[i].dis_id + '">' + data[i].disease + '</option>');
		}
		$('#menu').change(function() {
 			$("#images").animate({
   			 opacity: 0.4
  			}, 800 );
			DIS_ID = $('#menu').val();
			tweets=[];
			showTweets();
			updateMapMarkers();
		});
//#		$('#menu').append('<option value="' + data[i].dis_id + '">' + data[i].disease + '</option>');
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
  var jqxhr = $.getJSON("gettweets.php?callback=?", 
	{ "DIS_ID": DIS_ID
	 , "DATE" : showDate
	},
	function(data) {
	  	tweets = data;
	  	loadingTweets = 0; 
		showTweet = 0;
  		$("#images").animate({
		    opacity: 1
  		}, 800 );
	  	return false;
	  	})
//	  	.success(function() { alert("second success"); })
	  	.error(function(jqxhr, textStatus, errorThrown) { 
	        console.log("error " + textStatus);
	        console.log("incoming Text " + jqxhr.responseText);
	        });
//	  	.complete(function() { alert("complete"); });
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
		$("#images").prepend("<div style='width:100%'></div>") ;
 		$("#images div:first").hide();
 		if ( swapColor ){
 			$("#images div:first").css({'padding': '9px','background-color' : '#FFFFFF' });
 		} else {
 			$("#images div:first").css({'padding': '9px', 'background-color' : '#EEEEEE' });
 		}
 		swapColor = !swapColor;
		var ct = tweets[showTweet];
		var d = dateFromUTC( ct.dtm, '-' ) ;
		var datestr = d.getMonthName('en') +' '+ d.getUTCDate();
 		$("#images div:first").prepend("<div style='width:270px;font-size:small;'><strong>@"+ct.screen_name+"</strong> "+" "+ct.text+" <strong>"+capitaliseFirstLetter(ct.city)+", "+ct.state.toUpperCase()+" - "+datestr+"</strong></div>");
		$("#images div:first").fadeIn();
		showTweet++;
		if(tweets.length == showTweet ){
			showTweet = 0;
		}
//		tweets.pop();
	}else {
		if (!loadingTweets){
//			$('#images > div').remove();
//			$('#images').prepend("<div id='loading' style='width:100%;height:100%;vertical-align:middle'><img src='/images/loading.gif' style='display:block; position:relative; top:50%;' /></div>");
//			$('#images > div').remove();
			$("#images").prepend("<div style='width:100%'></div>") ;
 			$("#images div:first").css({'padding': '9px','background-color' : '#CCCCCC' });
// 			$("#images div:first").hide();
// 			if ( swapColor ){
 //				$("#images div:first").css({'padding': '9px','background-color' : '#FFFFFF' });
 //			} else {
 //				$("#images div:first").css({'padding': '9px', 'background-color' : '#EEEEEE' });
 //			}
//	 		swapColor = !swapColor;
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
//			getjsonData();
//			alert(getUTCDate());
//			var today = new Date();
//			today.setDate = (today.getUTCDate() -1);
			var d = new Date();
			d.setDate(d.getDate() -1);
//			d.setDate(today.getUTCYear(),today.getUTCMonth(),today.getUTCDate());
			for ( var i=0; i<14; ++i ){
				var optionstr = d.getMonthName('en') +' '+ d.getUTCDate();
				var optionval = d.getUTCFullYear()+'-'+('0'+(d.getUTCMonth()+1)).slice(-2)+'-'+ ('0'+d.getUTCDate()).slice(-2);
				if (i ==0 ){
//					optionstr = optionstr +' (today)';
					if (!showDate){
						showDate = optionval;
					}
				}
				$('#date').append('<option value="' +optionval + '">' + optionstr + '</option>');
//				$('#date').append('<option value="' +d.getUTCFullYear()+'-'+('0'+(d.getUTCMonth()+1)).slice(-2)+'-'+ ('0'+d.getUTCDate()).slice(-2) + '">' + d.getMonthName('en') +' '+ d.getUTCDate() + '</option>');
//				$('#date').append('<option value="' +dstr +'">' + d.getMonthName('en') +' '+ d.getUTCDate() + '</option>');
				d.setDate(d.getDate() -1);
			}
			$('#date').change(function() {
				showDate= $('#date').val();
				//alert(showDate);
 				$("#images").animate({
   				 opacity: 0.4
  				}, 800 );
				tweets=[];
				getMenu();
				DIS_ID = 0;
				updateMapMarkers();
				showTweets();
				//alert(showDate);
			});
			getMenu();
			showTweets();
			setInterval(showTweets, 5000);
//			$('#scrollbar1').tinyscrollbar();
		});


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
        GDownloadUrl("phpsqlajax_genxml.php?DIS_ID="+DIS_ID+"&DATE="+showDate, function(data) {
          var xml = GXml.parse(data);
          var markers = xml.documentElement.getElementsByTagName("marker");
          for (var i = 0; i < markers.length; i++) {
            var name = markers[i].getAttribute("name");
            var address = markers[i].getAttribute("address");
            var type = markers[i].getAttribute("type");
            var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),
                                    parseFloat(markers[i].getAttribute("lng")));
            var marker = createMarker(point, name, address, type);
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

    function createMarker(point, name, address, type) {
      var marker = new GMarker(point, customIcons[type]);
      var html = "<b>" + name + "</b> <br/>" + address;
      GEvent.addListener(marker, 'click', function() {
        marker.openInfoWindowHtml(html);
      });
      return marker;
    }
    //]]>
  </script>
<style type="text/css">
<!--
.style1 {
	font-size: x-large;
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
<!-- #EndEditable -->
<meta http-equiv="Content-Type" content="text/html;">
<link rel="stylesheet" href="../images/style.css" type="text/css">
</head>
<body  onload="load()" onunload="GUnload()">
<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
  <tr> 
      <td width="600" bgcolor="#FFFFFF">&nbsp;</td> 
    <td><img name="header" src="../images/header2.jpg" width="900" height="48" border="0" usemap="#headerMap" alt="Natame Analytics - Disease trends"></td>    
    <td width="600" bgcolor="#FFFFFF">&nbsp;</td> 
  </tr>
  <tr>     <td width="600" background="../images/menu_bg.gif">&nbsp;</td> 
    <td><img name="menu" src="../images/menu.jpg" width="900" height="21" border="0" usemap="#menuMap"></td>
  <td width="600" background="../images/menu_bg.gif">&nbsp;</td>   </tr>
  <tr> 
      <td width="600">&nbsp;</td> 
    <td bgcolor="#999999" background="../images/main_bg.gif"> <!-- #BeginEditable "body" --> 

          <table width="898" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#CCCCCC" class="cellunderlined">
  <tr>
    <td colspan="2" bgcolor="#FFC9BB" class="cellunderlined"><span class="style1 style2">Contageous Disease Twitter Trends</span></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC" class="cellunderlined">

<table border="0" align="left" cellpadding="3" cellspacing="0">
<tr><td nowrap="nowrap" class="white-space: nowrap">Show alerts for:  <select name="menu" id="menu" tabindex="1"><option>-----------------------------------------</option></select> </td><td class="white-space: nowrap">&nbsp; on  <select name="date" id="date" tabindex="2"></select></td></tr>
</table>
	</td>
  </tr>
  <tr>
    <td width="70%"> <div id="map" style="width: 600px; height: 400px"></div></td>
    <td width="30%" bgColor="#FFFFFF">
 	<div id="images" style="width:300px;height:400px;overflow-y:scroll;overflow-x: hidden;">
</div>

	</div>
    </td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#F0F0F0" class="cellunderlined">&nbsp;    </td>
  </tr>
</table>

          
     <table width="100%" border="0" cellspacing="0" cellpadding="15">
        <tr> 
          <td>&nbsp;</td>
        </tr>
      </table>
            <!-- #EndEditable -->
    </td>
	    <td width="600">&nbsp;</td> 
  </tr>
  <tr> 
      <td width="600">&nbsp;</td> 
    <td><img name="footer" src="../images/footer.gif" width="900" height="90" border="0" alt="Natame Analytics - Disease trends"></td>
	    <td width="600">&nbsp;</td> 
  </tr>
</table>
<map name="headerMap">
  <area shape="rect" coords="13,5,206,44" href="/">
</map>

<map name="menuMap">
  <area shape="rect" coords="10,4,113,16" href="/index.php" alt="Contageous disease tracker"><area shape="rect" coords="129,4,229,16" href="/bystate.php" alt="Get details by state of contagious disease twitter traffic">
<area shape="rect" coords="247,5,286,17" href="about.php" alt="About Natame">
</map></body>
<!-- #EndTemplate --></html>
