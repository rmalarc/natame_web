require(['dojox/cometd', 'dojo/dom', 'dojo/dom-construct', 'dojo/domReady!'],
function(cometd, dom, doc)
{
    cometd.configure({
//        url: location.protocol + '//' + location.host + config.contextPath + '/cometd',
        url: 'http://natame.com:8080/cometd',
        logLevel: 'info'
    });

    mycometd = cometd;
    cometd.addListener('/meta/handshake', function(message)
    {
        if (message.successful)
        {
//            dom.byId('status').innerHTML += '<div>CometD handshake successful</div>';
            cometd.subscribe('/stock/TREE', function(message)
            {
//		return;
		$('#demo').jstree('refresh',-1);
		return;
                var data = message.data;
                var symbol = data.symbol;
                var value = data.newValue;

                // Find the div for the given stock symbol
                var id = 'stock_' + symbol;
                var symbolDiv = dom.byId(id);
		if (symbol == 'TREE'){
			newPic =1;
			var i = pics.push(value);
			if (i == 1){
		                symbolDiv.innerHTML = '<img src="' + pics[i-1] + '" align="middle">';
			};
		} else {
	                symbolDiv.innerHTML = '<span>' + symbol + ': ' + value + '</span>';
		}
            });
        }
        else
        {
//            dom.byId('status').innerHTML += '<div>CometD handshake failed</div>';
        }
    });

    cometd.handshake();
});

//$.support.cors = true; 


$(function () {

$("#demo")
	.bind("before.jstree", function (e, data) {
		$("#alog").append(data.func + "<br />");
	})
	.jstree({ 
		// List of active plugins
		"plugins" : [ 
			"themes","json_data","ui","crrm","cookies","dnd","search","types","hotkeys","contextmenu" 
		],
"contextmenu": {
        "items": function ($node) {
//            return {
		var items = {
                "Create_factory": {
                    "label": "Create a Factory",
                    "action": function (obj) {
			$("#mybody").css({"opacity": "0.5"});
			$('#factory').show();
			//$("#demo").jstree("create",2,"first","Enter a new name");
			//$("#demo").jstree("create", 2, 0, { "attr" : { "rel" : 1 } });
			//$("#demo").jstree("create",2,false,"No rename",false,true);
                        //this.create(obj,2);//,false,"No rename",false,true);
                    }
                },
                "Generate_nodes": {
                    "label": "Generate nodes",
                    "action": function (obj) {
			if ( obj.attr("rel") == "folder" ){
				parentnode = obj;
				$("#mybody").css({"opacity": "0.5"});
				$('#nodes').show();
			}
/*			if ( obj.attr("rel") == "default" ){
				parentnode = $('#demo')._get_parent(obj);
				alert(parentnode.attr("rel"));
				$("#mybody").css({"opacity": "0.5"});
				$('#nodes').show();
			} */
                    }
                },
                "Delete": {
                    "label": "Delete a node",
                    "action": function (obj) {
                        this.remove(obj);
                    }
                }
            };
	    if ($node.attr("rel") != "folder" ) {
		items.Generate_nodes._disabled = "true";
		}
	    return items;
        }
    },
		// I usually configure the plugin that handles the data first
		// This example uses JSON as it is most common
		"json_data" : { 
			// This tree is ajax enabled - as this is most common, and maybe a bit more complex
			// All the options are almost the same as jQuery's AJAX (read the docs)
			"ajax" : {
				// the URL to fetch the data
				"url" : "http://natame.com/tree/_demo/server.php",
				// the `data` function is executed in the instance's scope
				// the parameter is the node being loaded 
				// (may be -1, 0, or undefined when loading the root nodes)
				"data" : function (n) { 
					// the result is fed to the AJAX request `data` option
					return { 
						"operation" : "get_children", 
						"id" : n.attr ? n.attr("id").replace("node_","") : 1, 
					}; 
				}
			}
		},
		// Configuring the search plugin
		"search" : {
			// As this has been a common question - async search
			// Same as above - the `ajax` config option is actually jQuery's AJAX object
			"ajax" : {
				"url" : "http://natame.com/tree/_demo/server.php",
				// You get the search string as a parameter
				"data" : function (str) {
					return { 
						"operation" : "search", 
						"search_str" : str 
					}; 
				}
			}
		},
		// Using types - most of the time this is an overkill
		// read the docs carefully to decide whether you need types
		"types" : {
			// I set both options to -2, as I do not need depth and children count checking
			// Those two checks may slow jstree a lot, so use only when needed
			"max_depth" : -2,
			"max_children" : -2,
			// I want only `drive` nodes to be root nodes 
			// This will prevent moving or creating any other type as a root node
			"valid_children" : [ "drive" ],
			"types" : {
				// The default type
				"default" : {
					// I want this type to have no children (so only leaf nodes)
					// In my case - those are files
					"valid_children" : "none",
					// If we specify an icon for the default type it WILL OVERRIDE the theme icons
					"icon" : {
						"image" : "http://natame.com/tree/_demo/file.png"
					},
					"start_drag" : false,
					"move_node" : false,
				},
				// The `folder` type
				"folder" : {
					// can have files and other folders inside of it, but NOT `drive` nodes
					"valid_children" : [ "default", "folder" ],
					"icon" : {
						"image" : "http://natame.com/tree/_demo/folder.png"
					},
					"start_drag" : false,
					"move_node" : false,
				},
				// The `drive` nodes 
				"drive" : {
					// can have files and folders inside, but NOT other `drive` nodes
					"valid_children" : [ "default", "folder" ],
					"icon" : {
						"image" : "http://natame.com/tree/_demo/root.png"
					},
					// those prevent the functions with the same name to be used on `drive` nodes
					// internally the `before` event is used
					"start_drag" : false,
					"move_node" : false,
					"delete_node" : false,
					"remove" : false
				}
			}
		},
		// UI & core - the nodes to initially select and open will be overwritten by the cookie plugin

		// the UI plugin - it handles selecting/deselecting/hovering nodes
		"ui" : {
			// this makes the node with ID node_4 selected onload
			"initially_select" : [ "node_4" ]
		},
		// the core plugin - not many options here
		"core" : { 
			// just open those two nodes up
			// as this is an AJAX enabled tree, both will be downloaded from the server
			"initially_open" : [ "node_2" , "node_3" ] 
		}
	})
	.bind("create.jstree", function (e, data) {
		$.post(
			"http://natame.com/tree/_demo/server.php", 
			{ 
				"operation" : "create_node", 
				"id" : data.rslt.parent.attr("id").replace("node_",""), 
				"position" : data.rslt.position,
				"title" : data.rslt.name,
				"from" : data.rslt.obj.attr("from"),
				"to" : data.rslt.obj.attr("to"),
				"type" : data.rslt.obj.attr("rel")
			}, 
			function (r) {
				if (snoozeRefresh == 0){
					mycometd.publish('/stock/TREE', { symbol: 'tree', value: 'changed' });
				}
				if(r.status) {
					$(data.rslt.obj).attr("id", "node_" + r.id);
				}
				else {
					$.jstree.rollback(data.rlbk);
				}
			}
		);
	})
	.bind("remove.jstree", function (e, data) {
		data.rslt.obj.each(function () {
			$.ajax({
				async : false,
				type: 'POST',
				url: "http://natame.com/tree/_demo/server.php",
				data : { 
					"operation" : "remove_node", 
					"id" : this.id.replace("node_","")
				}, 
				success : function (r) {
					if (snoozeRefresh == 0){
						mycometd.publish('/stock/TREE', { symbol: 'tree', value: 'changed' });
					}
//					mycometd.publish('/stock/TREE', { symbol: 'tree', value: 'changed' });
					if(!r.status) {
						data.inst.refresh();
					}
				}
			});
		});
	})
	.bind("rename.jstree", function (e, data) {
		$.post(
			"http://natame.com/tree/_demo/server.php", 
			{ 
				"operation" : "rename_node", 
				"id" : data.rslt.obj.attr("id").replace("node_",""),
				"title" : data.rslt.new_name
			}, 
			function (r) {
				if (snoozeRefresh == 0){
					mycometd.publish('/stock/TREE', { symbol: 'tree', value: 'changed' });
				}
//					mycometd.publish('/stock/TREE', { symbol: 'tree', value: 'changed' });						
				if(!r.status) {
					$.jstree.rollback(data.rlbk);
				}
			}
		);
	})
	.bind("move_node.jstree", function (e, data) {
		data.rslt.o.each(function (i) {
			$.ajax({
				async : false,
				type: 'POST',
				url: "http://natame.com/tree/_demo/server.php",
				data : { 
					"operation" : "move_node", 
					"id" : $(this).attr("id").replace("node_",""), 
					"ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""), 
					"position" : data.rslt.cp + i,
					"title" : data.rslt.name,
					"copy" : data.rslt.cy ? 1 : 0
				},
				success : function (r) {
					if (snoozeRefresh == 0){
						mycometd.publish('/stock/TREE', { symbol: 'tree', value: 'changed' });
					}
//					mycometd.publish('/stock/TREE', { symbol: 'tree', value: 'changed' });
					if(!r.status) {
						$.jstree.rollback(data.rlbk);
					}
					else {
						$(data.rslt.oc).attr("id", "node_" + r.id);
						if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
							data.inst.refresh(data.inst._get_parent(data.rslt.oc));
						}
					}
					$("#analyze").click();
				}
			});
		});
	});

});

// Code for the menu buttons
$(function () { 
	$("#mmenu input").click(function () {
		switch(this.id) {
			case "add_default":
			case "add_folder":
				$("#demo").jstree("create", null, "last", { "attr" : { "rel" : this.id.toString().replace("add_", "") } });
				break;
			case "search":
				$("#demo").jstree("search", document.getElementById("text").value);
				break;
			case "text": break;
			default:
				$("#demo").jstree(this.id);
				break;
		}
	});
});
