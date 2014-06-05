<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <script data-dojo-config="async: true, tlmSiblingOfDojo: true, deps: ['application.js']"
            src="/cometd/dojo/dojo.js.uncompressed.js"></script>
    <%--
    The reason to use a JSP is that it is very easy to obtain server-side configuration
    information (such as the contextPath) and pass it to the JavaScript environment on the client.
    --%>
    <script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
<style type="text/css">
#FOOTER{
position: absolute;
bottom: 0px;
height: 60px;
opacity:0.8;
width: 100%;
background-color: black;
color:white;
font-family: "Verdana";
font-size:45px;
padding-top:5px;
padding-bottom:5px;
text-align:center;
vertical-align:middle;
display: inline-block;
}

.imgfs {
//  position: fixed; 
//  top: 0; 
//  left: 0; 

  /* Preserve aspet ratio */
//  min-width: 100%;
//  min-height: 100%;
  max-width: 100%;
  max-height: 100%;
  display: block;
  margin: auto;
  vertical-align:middle;
}
.fillme{
  vertical-align:middle;
  overflow:hidden;
}
.fillme img.stretchx{
  height:auto;
  width:100%;
}
.fillme img.stretchy{
  height:100%;
  width:auto;
}
html,body{
	padding:0px;
	margin:0px;
vertical-align:middle;
}
</style>
    <script type="text/javascript">
function preloadPics(){
  var jqxhr = $.getJSON("/instagram/getpics.php?callback=?",
        { "HASHTAG": "sosvenezuela"
        },
        function(data) {
  		for ( var i=0, len=data.length; i<len; ++i ){
			pics.push(data[i].URL);
                        };
                newPic =1;
                return false;
                })
                .error(function(jqxhr, textStatus, errorThrown) {
                //console.log("incoming Text " + jqxhr.responseText);
                });
}


	function changePic(){
	   if (pics.length){
//		alert("newPic");
		var nextPic;
		if (newPic==1){
			nextPic = pics.length-1;
		} else {
			nextPic = Math.floor((Math.random()*pics.length)+1)-1;
//			alert(nextPic);
		}
//                var picDiv = dom.byId("stock_INSTAGRAM");
		var tmpImg = new Image();
		tmpImg.src = pics[nextPic];
		tmpImg.onload = function () {
			$("#stock_INSTAGRAM").html('<img src="' + pics[nextPic] + '" align="middle" class="imgfs" style="vertical-align:middle;">');
		}
/*   $('body').find(.fillme).each(function(){
      var fillmeval = $(this).width()/$(this).height();
      var imgval = $this.children('img').width()/$this.children('img').height();
      var imgClass;
      if(imgval > fillmeval){
          imgClass = "stretchy";
      }else{
          imgClass = "stretchx";
      }
      $(this).children('img').addClass(imgClass);
   });
*/
//		$("#stock_INSTAGRAM").html('<img id="imgfs" src="' + pics[nextPic] + '" align="middle">');
//		picDiv.innerHTML = 
		newPic = 0;
	  }
	};

	function set_body_height()
	{
	    var wh = $(window).height();
	    $('body').attr('style', 'height:' + wh + 'px;');
	};

        var pics = [];
	var newPic = 0;
        $(document).ready(function(){
//		set_body_height();
//		$(window).bind('resize', function() { set_body_height(); });
//		$("#stock_INSTAGRAM").html('<img src="http://pbs.twimg.com/media/Bgc0vSYIIAAMCNb.jpg" align="middle" class="imgfs" style="vertical-align:middle;">');
		preloadPics();
        	setInterval(changePic, 4000);
        });


//        var config = {
//            contextPath: '${pageContext.request.contextPath}'
//            contextPath: '/cometd/org'
//        };
    </script>
    <title>#SOSVenezuela</title>
</head>
<body bgColor="#000000">
<!--
    <h2>CometD Tutorial<br />Stock Price Notification</h2>

    <div id="status"></div>
    <div id="stocks" align="center"></div>
<div style="position: absolute; bottom: 0px; height: 90px;opacity:0.4; width: 100%;background-color: grey; color:white;">Tweet or Instagram your pic to #SOSVenezuela</div>
-->
    <div id="stock_INSTAGRAM" align="center" class="fillme"><img src="http://distilleryimage4.s3.amazonaws.com/7202d7a6959c11e3a7900e59ad914808_8.jpg" align="middle" class="imgfs" style="vertical-align:middle;"></div>
<div id="FOOTER">
<img src="https://g.twimg.com/Twitter_logo_white.png" width="61px" height="50px" style="vertical-align:middle;">
<img src="http://dragon.ak.fbcdn.net/hphotos-ak-prn1/t39.2365/851582_417171855069447_55288290_n.png" width="50px" height="50px" style="vertical-align:middle;">
#SOSVenezuela</div>
</body>
</html>
