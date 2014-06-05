<!doctype html>
 
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>jQuery UI Dialog - Modal form</title>
  <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css" />
  <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
  <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<!--  <link rel="stylesheet" href="/resources/demos/style.css" /> -->
  <style>
    body { font-size: 62.5%; }
    label, input { display:block; }
    input.text { margin-bottom:12px; width:95%; padding: .4em; }
    fieldset { padding:0; border:0; margin-top:25px; }
    h1 { font-size: 1.2em; margin: .6em 0; }
    div#users-contain { width: 350px; margin: 20px 0; }
    div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
    div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
    .ui-dialog .ui-state-error { padding: .3em; }
    .validateTips { border: 1px solid transparent; padding: 0.3em; }
  </style>
  <script>
  $(function() {
    var name = $( "#name" ),
      email = $( "#email" ),
      password = $( "#password" ),
      hashtag = $( "#hashtag" ),
//      allFields = $( [] ).add( name ).add( email ).add( password ),
      allFields = $( [] ).add( hashtag ),
      tips = $( ".validateTips" );
    PopulateHashtags();

    function PopulateHashtags () {
	  var request = $.ajax({
	   dataType:"json",
	   url: "subscription.php",
	   type: "GET",
	   data: {list : 1 , session : 12312321},
	   async: true
	  });

	  request.done(function(msg) {
	   	if ( msg.meta.code != 200 ) {
           		hashtag.addClass( "ui-state-error" );
           		updateTips( "Problem registering hashtag!. Please try again.");
	   		bValid = false;
	   	}
		for (var key in msg.data){
//			debugger
		    $('#empty-row').hide();
	            $( "#users tbody" ).append( "<tr>" +
        	      "<td>" + msg.data[key].object_id + "</td>" +
	              "<td>" + msg.data[key].type + "</td>" +
        	      "<td>" + msg.data[key].id + "</td>" +
         	   "</tr>" );
		}
	   	$("#log").append( JSON.stringify(msg,'')+'<br>' );
	   	//$("#log").append( msg.meta.code+'<br>' );
	  });
    } ;

    function AddHashtag (hashtag) {
        var errorCode = 0;
	var request = $.ajax({
	   dataType:"json",
	   url: "subscription.php",
	   type: "GET",
	   data: {subscribe : hashtag.val() , session : 12312321},
	   async: false
	  });

	request.done(function(msg) {
	   	if ( msg.meta.code != 200 ) {
           		hashtag.addClass( "ui-state-error" );
           		updateTips( "Problem registering hashtag!. Please try again.");
	   		errorCode = 1;
	   	}
	   	$("#log").append( JSON.stringify(msg,'')+'<br>' );
	   	$("#log").append( msg.meta.code+'<br>' );
	});

	request.fail(function(jqXHR, textStatus) {
		debugger
           	hashtag.addClass( "ui-state-error" );
           	updateTips( "Request failed: " + textStatus +". Please try again!" );
	   	errorCode = 2;
		alert(jqXHR.responseText);
		if (jqXHR.responseText.indexOf("Oops, an error occurred.") != -1){
			errorCode = 3;
		}
	});
	return errorCode;
    }

    function updateTips( t ) {
      tips
        .text( t )
        .addClass( "ui-state-highlight" );
      setTimeout(function() {
        tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
    }
 
    function checkLength( o, n, min, max ) {
      if ( o.val().length > max || o.val().length < min ) {
        o.addClass( "ui-state-error" );
        updateTips( "Length of " + n + " must be between " +
          min + " and " + max + "." );
        return false;
      } else {
        return true;
      }
    }
 
    function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
        o.addClass( "ui-state-error" );
        updateTips( n );
        return false;
      } else {
        return true;
      }
    }
 
    $( "#dialog-form" ).dialog({
      autoOpen: false,
      height: 300,
      width: 350,
      modal: true,
      buttons: {
        "Create a channel": function() {
          var bValid = true;
          allFields.removeClass( "ui-state-error" );
 
//          bValid = bValid && checkLength( name, "username", 3, 16 );
//          bValid = bValid && checkLength( email, "email", 6, 80 );
//          bValid = bValid && checkLength( password, "password", 5, 16 );
 
//          bValid = bValid && checkRegexp( hashtag, /^[0-9a-z]([0-9a-z_])+$/i, "Hashtag may consist of a-z, 0-9, underscores, begin with a letter." );
          bValid = bValid && checkRegexp( hashtag, /[\S]+/i, "Hashtag may consist of a-z, 0-9, underscores, begin with a letter." );
          // From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
//          bValid = bValid && checkRegexp( email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "eg. ui@jquery.com" );
//          bValid = bValid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "Password field only allow : a-z 0-9" );
	  if (bValid){
		  var aht_error = AddHashtag(hashtag);
		  switch (aht_error)
		  {
			case 0: break;
			case 1: bValid = false;
				break;
			case 2: bValid = false;
				break;
			case 3: bValid = false;
				break;
		  }
	  }

          if ( bValid ) {
            $( "#users tbody" ).append( "<tr>" +
              "<td>" + hashtag.val() + "</td>" +
              "<td>" + email.val() + "</td>" +
              "<td>" + password.val() + "</td>" +
            "</tr>" );
            $( this ).dialog( "close" );
          }
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      },
      close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
      }
    });
 
    $( "#create-user" )
      .button()
      .click(function() {
        $( "#dialog-form" ).dialog( "open" );
      });

    $( "#launch-show" )
      .button()
      .click(function() {
//        $( "#dialog-form" ).dialog( "open" );
	window.open('slideshow.php','','fullscreen=yes')
      });
  });
  </script>
</head>
<body>
 
<div id="dialog-form" title="Create a Channel">
  <p class="validateTips">All form fields are required.</p>
 
  <form>
  <fieldset>
    <label for="hashtag">Instagram hashtag:</label>
    <input type="text" name="hashtag" id="hashtag" class="text ui-widget-content ui-corner-all" />
<!--    <label for="email">Email</label>
    <input type="text" name="email" id="email" value="" class="text ui-widget-content ui-corner-all" />
    <label for="password">Password</label>
    <input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
-->  </fieldset>
  </form>
</div>
 
 
<div id="users-contain" class="ui-widget">
  <h1>Existing channels:</h1>
  <table id="users" class="ui-widget ui-widget-content">
    <thead>
      <tr class="ui-widget-header ">
        <th>Hashtag</th>
        <th>Email</th>
        <th>Password</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td colspan="3" id="empty-row">No active hashtags</td>
      </tr>
    </tbody>
  </table>
</div>
<button id="create-user">Add hashtag channel</button>
<button id="launch-show">Launch Slideshow</button>

<div id=class="ui-widget">
<h2>Message log:</h2>
<div id="log" class="ui-state-highlight ui-corner-all"  style="margin-top: 20px; padding: 5px;"></div> 
</div>
 
</body>
</html>
