require(['dojox/cometd', 'dojo/dom', 'dojo/dom-construct', 'dojo/domReady!'],
function(cometd, dom, doc)
{
    cometd.configure({
//        url: location.protocol + '//' + location.host + config.contextPath + '/cometd',
	url: 'http://natame.com:8080/cometd',
        logLevel: 'info'
    });

    cometd.addListener('/meta/handshake', function(message)
    {
        if (message.successful)
        {
//            dom.byId('status').innerHTML += '<div>CometD handshake successful</div>';
            cometd.subscribe('/stock/INSTAGRAM', function(message)
            {
                var data = message.data;
                var symbol = data.symbol;
                var value = data.newValue;

                // Find the div for the given stock symbol
                var id = 'stock_' + symbol;
                var symbolDiv = dom.byId(id);
/*                if (!symbolDiv)
                {
                    symbolDiv = doc.place('<div id="' + id + '"></div>', dom.byId('stocks'));
                }
*/
		if (symbol == 'INSTAGRAM'){
			newPic =1;
			var i = pics.push(value);
//			alert(value +" " +pics[i-1]);
			if (i == 1){
		                symbolDiv.innerHTML = '<img src="' + pics[i-1] + '" align="middle">';
			};
//	                symbolDiv.innerHTML = '<img src="' + value + '" align="middle">';
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

