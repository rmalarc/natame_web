<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<!--    <script src="http://code.jquery.com/jquery-1.7.2.min.js"></script> -->
    <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script type="text/javascript" src="http://static.jstree.com/v.1.0pre/jquery.cookie.js"></script>
<script type="text/javascript" src="http://static.jstree.com/v.1.0pre/jquery.hotkeys.js"></script>
<script type="text/javascript" src="http://static.jstree.com/v.1.0pre/jquery.jstree.js"></script>
<script src="http://malsup.github.io/jquery.blockUI.js"></script>
	<link rel="stylesheet" type="text/css" href="http://passportparking.com/prettyPhoto.css" />
	<link rel="stylesheet" type="text/css" href="http://passportparking.com/slides.global.css" />
	<link rel="stylesheet" type="text/css" href="http://passportparking.com/style.css" />
    <script data-dojo-config="async: true, tlmSiblingOfDojo: true, deps: ['tree.js']"
            src="cometd-javascript-dojo/dojo/dojo.js.uncompressed.js"></script>

    <script type="text/javascript">
	var mycometd;
	var parentnode;
	var snoozeRefresh = 0;
        $(document).ready(function(){
		$.support.cors = true;
		$("#factory_ok").click(function() {
			var name = $("#factory_name").val();
			var from = parseInt($("#factory_from").val());
			var to = parseInt($("#factory_to").val());
			var fullname = name + " ("+from+ " - "+ to +")";
			if (name && from>0 && to>0 && from < to){
		        	$("#demo").jstree("create", $("#node_2"), "first", { "attr" : { "rel" : "folder", "from" : from.toString(), "to" :to.toString() }, "data" : fullname },
		                          function() { $("#factory").hide();$("#mybody").css({"opacity": "1"}); }, true);
				$("#factory_error").text("");
				$("#factory_name").text("");
				$("#factory_from").text("");
				$("#factory_to").text("");
			} else  {
				$("#factory_error").text("Invalid values!");
			}
    		});
		$("#nodes_ok").click(function() {
			var obj = parentnode;
			var nodes_no = parseInt($("#nodes_no").val());
			var from, to, range;
			from = parseInt(obj.attr("from")) ;
			to = parseInt(obj.attr("to"));
 			range = to-from +1;
			if (nodes_no>0 && nodes_no<=15 && (nodes_no<=range) && obj.attr("rel") == "folder"){
				var existingNodes = {}, nodeCount = 0;
			         $('#'+obj.attr("id")).find("li").each( function( idx, listItem ) {
			             var child = $(listItem); // child object
					existingNodes["n_"+parseInt(child.text()).toString()] = 1;
					nodeCount++;
			         });
				var NodesAvailable = range - nodeCount;
				if (NodesAvailable<nodes_no){
					$("#nodes_error").text("I can't create "+nodes_no.toString()+" nodes!. There's space for up to "+NodesAvailable.toString()+" nodes");
					return;
				}
				snoozeRefresh = 1;
				for (var i=0;i<nodes_no;i++)
				{
					var number = Math.floor(Math.random() * range ) + from ;
					while (existingNodes["n_"+number.toString()] == 1){
						number = Math.floor(Math.random() * range ) + from ;
					}

			        	$("#demo").jstree("create", $('#'+obj.attr("id")), "inside", { "attr" : { "rel" : "default", "from" : obj.attr("from"), "to" :obj.attr("to") }, "data" : number.toString() },
			                          function() { $("#nodes").hide();$("#mybody").css({"opacity": "1"}); }, true);
					setTimeout(function(){return;}, 300);
					existingNodes["n_"+number.toString()] = 1;
				}
				snoozeRefresh = 0;
				mycometd.publish('/stock/TREE', { symbol: 'tree', value: 'changed' });
//				alert("done");
//				$("#nodes").hide();
				$("#nodes_error").text("");
				$("#nodes_no").val("");
			} else  {
				$("#nodes_error").text("Invalid value!");
			}
    		});


        });

/*        var config = {
            contextPath: 'cometd-javascript-dojo'
        };
*/

    </script>
<style type="text/css">
#demo, #demo input, .jstree-dnd-helper, #vakata-contextmenu { font-size:10px; font-family:Verdana; }
#demo { background:white;
border:2px solid;
border-radius:5px;
//    margin:-100px 0 0 -180px;
    margin:10px auto; 
	padding: 5px;
 }
#container { position:relative; }
#container .demo { width:740px; border:1px solid gray; padding:0; margin:10px 0; }
#container .code { width:738px; }
#container #demo { width:778px; float:none; height:400px; overflow:auto; border:1px solid gray; }
#menub { height:30px; overflow:auto; }
#text { margin-top:1px; }
#alog { font-size:9px !important; margin:5px; border:1px solid silver; }
#dhead { display:none; }
#content.demo { width:780px; border:0; }
.dialogBox{
    width:360px;
    height:200px;
    position:fixed;
    left:50%;
    top:50%;
    margin:-100px 0 0 -180px;
//    margin:5px auto; 
	padding: 10px;
    display:none;
    background:white;
border:2px solid;
border-radius:5px;
z-index:9999;
}

#bg{
    width: 100%;
    height: 100%;
    min-width: 100%;
    min-height: 100%;
//    margin:5px auto; 
//    display:none;
   color: black;
}
</style>


  <title>Live tree update</title>
</head>
<body bgColor="#FFFFFF">
    <div id="stock_TREE" align="center"></div>
<div id="mybody" style="padding:15px">
<div>
<div id="logo" style="display:inline;float:left;"><img src="http://passportparking.com/images/logo.png" alt=""/></div>
<div id="description" style="display:inline;width:80%;"><h1>Factories Vs. Nodes Programming Challenge: Let the games begin!</h1>
<p>Here's a rough prototype of the Factores and Nodes challenge. It features the following technologies: PHP, JQuery, JSTree, MySQL, etc.</p>
<p>This version does not let you create duplicate nodes. The pool of numbers gets depleted as new nodes get allocated. Therefore, if you have a range from 3-10, the maximun number of nodes under that factory will be 8, all non-duplicate.</p>
<p>Interesting challenge, I hope you guys like it!</p>
</div>
</div>
<!--
<div id="mmenu" style="height:30px; overflow:auto;">
<input type="button" id="add_folder" value="add folder" style="display:block; float:left;"/>
<input type="button" id="add_default" value="add file" style="display:block; float:left;"/>
<input type="button" id="rename" value="rename" style="display:block; float:left;"/>
<input type="button" id="remove" value="remove" style="display:block; float:left;"/>
<input type="button" id="cut" value="cut" style="display:block; float:left;"/>
<input type="button" id="copy" value="copy" style="display:block; float:left;"/>
<input type="button" id="paste" value="paste" style="display:block; float:left;"/>
<input type="button" id="clear_search" value="clear" style="display:block; float:right;"/>
<input type="button" id="search" value="search" style="display:block; float:right;"/>
<input type="text" id="text" value="" style="display:block; float:right;" />
</div>
-->
<!-- the tree container (notice NOT an UL node) -->
<div id="demo" class="demo"></div>
<div style="height:30px; text-align:center;">
</div>
<!--
	<input type="button" style='width:170px; height:24px; margin:5px auto;' value="reconstruct" onclick="$.get('http://natame.com/tree/_demo/server.php?reconstruct', function () { $('#demo').jstree('refresh',-1); });" />
	<input type="button" style='width:170px; height:24px; margin:5px auto;' id="analyze" value="analyze" onclick="$('#alog').load('http://natame.com/tree/_demo/server.php?analyze');" />
	<input type="button" style='width:170px; height:24px; margin:5px auto;' value="refresh" onclick="$('#demo').jstree('refresh',-1);" />
	<div id='alog' style="border:1px solid gray; padding:5px; height:100px; margin-top:15px; overflow:auto; font-family:Monospace;"></div>
style='width:350px; height:240px; margin:5px auto; display:none;'>
<div id="nodes" style='width:350px; height:240px; margin:5px auto; display:none;'>
-->
</div>
<div id="factory" class="dialogBox">
	<p>Create New factory:</p>
	<p>Name: 
	<input type="text" id="factory_name" value="" size="15" style="display:inline;" />
	<br>
	from <input type="text" id="factory_from" value="" size="4" style="display:inline;" />
	to <input type="text" id="factory_to" value="" size="4" style="display:inline;" />
	</p>
	<div id="factory_error" style="display:block; text-align: center; color: red; height:24px; vertical-align:top;" align="center">
	</div>
	<div id="factory_buttons" style="display:block; text-align: center;" align="center">
	<input type="button" style='width:170px; height:24px; margin:5px auto;' id="factory_ok"  value="OK" />
	<input type="button" style='width:170px; height:24px; margin:5px auto;' id="factory_cancel" value="Cancel" onclick="$('#factory').hide();$('#mybody').css({'opacity': '1'});" />
	</div>
</div>
<div id="nodes" class="dialogBox">
	<p>Create nodes:</p>
	<p>Enter number of nodes: 
	<input type="text" id="nodes_no" value="" size="3" style="display:inline;" />
	</p>
	<div id="nodes_error" style="display:block; text-align: center; color: red; height:24px; vertical-align:top;" align="center">
	</div>
	<div id="nodes_buttons" style="display:block; text-align: center;" align="center">
	<input type="button" style='width:170px; height:24px; margin:5px auto;' id="nodes_ok"  value="OK" />
	<input type="button" style='width:170px; height:24px; margin:5px auto;' id="nodes_cancel" value="Cancel" onclick="$('#nodes').hide();$('#mybody').css({'opacity': '1'});" />
	</div>
<!-- JavaScript neccessary for the tree -->

</body>
</html>
