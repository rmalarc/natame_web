<html><!-- #BeginTemplate "/Templates/natame.dwt" --><!-- DW6 -->
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
var TR_ID = <? echo isset($_GET['TR_ID'])?$_GET['TR_ID']:1 ?>;;
var TAX_ID = 202;
var showDate ="";
 
function getMenu(){
  var jqxhr = $.getJSON("getmenutrain.php?callback=?", 
	{ "TR_ID": TR_ID
	, "DATE" : showDate
	 },
	function(data) {
		$('#menu > option').remove();
		for ( var i=0, len=data.length; i<len; ++i ){
			$('#menu').append('<option value="' + data[i].tax_id + '">' + data[i].disease+' - ' + data[i].term+ '</option>');
		}
		$('#menu').change(function() {
			TAX_ID = $('#menu').val();
			tweets=[];
			loadingTweets = 0;
			showTweets();
		});
	  	return false;
	  	})
	  	.error(function(jqxhr, textStatus, errorThrown) { 
	        console.log("error " + textStatus);
	        console.log("incoming Text " + jqxhr.responseText);
	        });
};
 
 
 
function getjsonData(){
  $('#images > div').remove();
  var getimages = 6;
  var imgloaded = 0;
  loadingTweets = 1;

  $("body").css("cursor", "progress");
  var jqxhr = $.getJSON("gettweetsbayes.php?callback=?", 
	{ "TAX_ID": TAX_ID
	, "DATE" : showDate
	},
	function(data) {
  		$("body").css("cursor", "auto");
		$('#images > div').remove();
	  	tweets = data;
	  	loadingTweets = 0; 
		showTweet = 0;
		showTweets();
	  	return false;
	  	})
//	  	.success(function() { alert("second success"); })
	  	.error(function(jqxhr, textStatus, errorThrown) { 
	        console.log("error " + textStatus);
	        console.log("incoming Text " + jqxhr.responseText);
	        });
//	  	.complete(function() { alert("complete"); });
}
 

function bclassify(n,isjunk,tid){
  $("body").css("cursor", "progress");
  var jqxhr = $.getJSON("bclassify.php?callback=?", 
	{ "TID": tid.toString()
	, "TISJUNK" : isjunk
	 },
	function(data) {
  		$("body").css("cursor", "auto");

//		alert("second success");
	  	return false;
	  	})
	  	.complete(function() { 	$("#images div:nth-child("+(n+1)+")").hide();})

//	  	.success(function() { alert("second success"); })
	  	.error(function(jqxhr, textStatus, errorThrown) { 
	        console.log("error " + textStatus);
	        console.log("incoming Text " + jqxhr.responseText);
	        });
}


function callRebuild(){
  $("body").css("cursor", "progress");

  var jqxhr = $.getJSON("rebuildpisjunk.php?callback=?", 
	{ "TAX_ID": TAX_ID
	 },
	function(data) {
  		$("body").css("cursor", "auto");
		tweets=[];
		showTweets();

//		alert("second success");
	  	return false;
	  	})
//	  	.complete(function() { 	$("#images div:nth-child("+(n+1)+")").hide();})
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

function isJunk(n,isjunk,tid)
{
//	alert (n.toString()+' '+isjunk.toString()+'-'+ tid.toString() + "#images div:nth-child("+(n+1)+")");
//	alert(tid);
	bclassify(n,isjunk,tid);
//		$("#images div:nth-child("+(n+1)+")").hide();
} 
 

function rebuildIsJunk(){
	$('#images > div').remove();
	callRebuild();
//	alert("Not functioning yet, come agani soon!!!");
}

function showTweets(){
	if(tweets.length > 0 ){
// 		if ( swapColor ){
// 		} else {
// 			$("#images div:first").css({'padding': '9px', 'background-color' : '#EEEEEE' });
// 		}
	      var i=0;
	      for (i=0; i<tweets.length ;i++){
		$("#images").append("<div style='width:100%'></div>") ;
 		$("#images div:first").css({'padding': '9px','background-color' : '#FFFFFF' });
// 		$("#images div:last").hide();
// 		swapColor = !swapColor;
		var ct = tweets[showTweet];
		var d = dateFromUTC( ct.dtm, '-' ) ;
		var datestr = d.getMonthName('en') +' '+ d.getUTCDate();
 		$("#images div:last").prepend("<table cellpadding='5' cellspacing='5' style='width:100%;' class='cellunderlined'><tr><td style='width:70%;'><strong>@"+ct.screen_name+"</strong>&nbsp;"+" "+ct.text+"<strong> "+capitaliseFirstLetter(ct.city)+", "+ct.state.toUpperCase()+" - "+datestr+"</strong></td><td style='width:30%;font-size:small;align:right' align='right'>p(Junk): "+Math.round(ct.pisjunk*100)+"% <a href='javascript:isJunk("+showTweet+",0,\""+ct.tid.toString()+"\");'>Good!!</a></td><td style='width:10%;font-size:small;'><a href='javascript:isJunk("+showTweet+",1,\""+ct.tid.toString()+"\");'>BAD</a></td></tr></table>");
//		$("#images div:last").fadeIn();
		showTweet++;
              }
//		alert("showing tweets");
	} else if (tweets.isempty == "1"){
		$("#images").append("<div style='width:100%'><p align='center'>Nothing to show</p></div>") ;
		return false;
	}else {
		if (!loadingTweets){
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

function load(){
	return;
}

function GUnload(){
	return;
}
 
		$(document).ready(function(){
//			getjsonData();
//			alert(getUTCDate());
//			var today = new Date();
//			today.setDate = (today.getUTCDate() -1);
			var d = new Date();
//			d.setDate(d.getDate() -1);
//			d.setDate(today.getUTCYear(),today.getUTCMonth(),today.getUTCDate());
			for ( var i=0; i<30; ++i ){
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
//				getMenu();
//				TAX_ID = 0;
//				updateMapMarkers();
				tweets=[];
				showTweets();
				//alert(showDate);
			});
			getMenu();
			showTweets();
//			setInterval(showTweets, 5000);
//			$('#scrollbar1').tinyscrollbar();
		});
 
 
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
<body  onload="load()" onUnload="GUnload()">
<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center">
  <tr> 
      <td width="600">&nbsp;</td> 
    <td><img name="header" src="/images/header2.jpg" width="900" height="48" border="0" usemap="#headerMap" alt="Natame Analytics - Disease trends"></td>    
    <td width="600">&nbsp;</td> 
  </tr>
  <tr>     <td width="600" background="../images/menu_bg.gif">&nbsp;</td> 
    <td><img src="../images/menu2.gif" name="menu" width="900" height="21" border="0" usemap="#menuMap"></td>
  <td width="600" background="../images/menu_bg.gif">&nbsp;</td>   </tr>
  <tr> 
      <td width="600">&nbsp;</td> 
    <td bgcolor="#999999" background="../images/main_bg.gif"> <!-- #BeginEditable "body" --> 
 
          <table width="898" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#CCCCCC" class="cellunderlined">
  <tr>
    <td colspan="2" bgcolor="#FFC9BB" class="cellunderlined"><span class="style1 style2">Bayesian Classifier Results</span></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#CCCCCC" class="cellunderlined">
<table border="0" align="left" cellpadding="0" cellspacing="0">
<tr>
<td nowrap="nowrap" class="white-space: nowrap">Show alerts for:  <select name="menu" id="menu" tabindex="1"><option>-----------------------------------------</option></select> </td>
<td class="white-space: nowrap">&nbsp; on  <select name="date" id="date"  tabindex="2"></select></td>
<td class="white-space: nowrap">&nbsp; <button type="button" onClick="javascript:rebuildIsJunk();">Rebuild p(IsJunk)</button></td>
</tr>
</table>


	</td>
  </tr>
  <tr>
    <td width="100%" colspan="2" bgColor="#FFFFFF">
 	<div id="images" style="width:100%;height:400px;overflow-y:scroll;overflow-x: hidden;">
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
  <area shape="rect" coords="10,4,113,16" href="/index.php" alt="Contagious disease tracker"><area shape="rect" coords="129,4,226,16" href="/index.php?TR_ID=2" alt="Nature Tracker">
<area shape="rect" coords="243,5,282,17" href="#" alt="About Natame">
</map></body>
<!-- #EndTemplate --></html>
