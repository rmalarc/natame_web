<!DOCTYPE html>
<meta charset="utf-8">
<style>
.background {
  fill: none;
  pointer-events: all;
}

.states {
  fill: none;
  stroke: #ffff;
  stroke-linejoin: round;
}
.counties {
  fill: none;
  stroke: #aaaaaa;
}

#chart_div {
    width: 955px;
    height: 100px;
    background-color: #FFFFFF;
}

path {
				stroke: steelblue;
				stroke-width: 1;
				fill: none;
			}
			
			.axis {
			  shape-rendering: crispEdges;
			}
 
			.x.axis line {
			  stroke: lightgrey;
			}
 
			.x.axis .minor {
			  stroke-opacity: .5;
			}
 
			.x.axis path {
			  display: none;
			}
 
			.y.axis line, .y.axis path {
			  fill: none;
			  stroke: #000;
			}


html, body {
    font-family: Arial, Helvetica, sans-serif;
    height: 600px;
}

html {
    display: table;
    margin: auto;
}
body {
    display: table-cell;
    padding: 0px;
    vertical-align: middle;
    background-color: #FFFFFF;
}

</style>

<body onload="start()">

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script src="http://d3js.org/queue.v1.min.js"></script>
<script src="http://d3js.org/topojson.v1.min.js"></script>

<script>

var curStep,
	timedInterval,
	curDate = new Date,
	chart,
	trends;
var width = 955,
    height = 500,
    centered;

var color = d3.scale.threshold()
    .domain([2,2.5,3,3.5,4,4.5,5,10])
//    .range(["#FBEAE4","#F7CEC3","#F4B3A2","#F19882","#EE7D61", "#EB6241",  "#E84720", "#E52C00"]);
    .range(["#EEEF2D","#ECCE26","#EBAC20","#EA8A19","#E86813", "#E7460C",  "#E62406", "#E50300"]);
//    .range(["#FBEAE4","#F7CEC3","#F4B3A2","#F19882","#EE7D61","#EE7D61", "#EB6241",  "#E84720", "#E52C00"]);
//    .range(["#F2EEEE","#F2E3E6","#F0BAAF", "#EF9179",  "#EE6842", "#ED3F0C"]);

var projection = d3.geo.albersUsa()
    .scale(1070)
    .translate([width / 2, height / 2]);

var path = d3.geo.path()
    .projection(projection);

var svg = d3.select("body").append("svg")
    .attr("width", width)
    .attr("height", height);

svg.append("rect")
    .attr("class", "background")
    .attr("width", width)
    .attr("height", height)
    .on("click", clicked);

var g = svg.append("g");


function start(){
 queue()
    .defer(d3.json, "us.json")
//    .defer(d3.json, "/nt/getmapdata.php?DISEASE=Influenza")
//    .defer(d3.json, "/nt/getmapdata.php?DISEASE=Influenza&DATE=2013-02-23")
//    .defer(d3.json, "flu2012.json")
    .defer(d3.json, "<?echo isset($_GET['FLU2012'])?'flu2012.json':'flu_cur.json';?>")
    .await(ready);
}

function ready(error, us, tr) {
//  var rateById = {};

//  trends.forEach(function(d) { rateById[d.county_fips] = d; });
  g.append("path")
      .datum(topojson.mesh(us, us.objects.states, function(a, b) { return a.id !== b.id; }))
      .attr("class", "states")
      .style("fill", "#FFFFFF")
      .attr("d", path);

  g.append("g")
    .selectAll("path")
      .data(topojson.feature(us, us.objects.counties).features)
      .attr("class", "counties")
    .enter().append("path")
      .attr("d", path)
    .on("click", clicked)
      .style("stroke", "#aaaaaa")
      .style("fill", "#ffffff")
//      .style("fill", function(d) { return rateById[d.id] == undefined? "#cccccc":color(rateById[d.id].Score);})
   .append("svg:title")
//        .text(function(d) { return  rateById[d.id] == undefined?"":rateById[d.id].County + ', '+rateById[d.id].State+'\nTweets in 7 Days: '+rateById[d.id].Tweets7D+'\nTweets per million: '+rateById[d.id].TweetsPM+'\nTweet days: '+rateById[d.id].Days+'\nScore: '+rateById[d.id].Score; })
  ;
  trends = tr;
//  console.log(JSON.stringify(trends));
  drawChart();
  startLoop();

}


function clicked(d) {
  var x, y, k;
//console.log(JSON.stringify(d));
  if (d && centered !== d) {
    var centroid = path.centroid(d);
    x = centroid[0];
    y = centroid[1];
    k = 8;
    centered = d;
  } else {
    x = width / 2;
    y = height / 2;
    k = 1;
    centered = null;
  }


  g.transition()
      .duration(750)
      .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")scale(" + k + ")translate(" + -x + "," + -y + ")")
      .style("stroke-width", 1 / k + "px");
}

//function startLoop(date, steps){
function startLoop(){
//  console.log(data.dates.length);return;
  curStep = trends.range;
//  console.log(curStep);
  timedDisplay();
/*  queue()
//    .defer(d3.tsv, "<?echo isset($_GET['FLU2012'])?'disease-flu2012.tsv':'disease.tsv';?>")
//    .defer(d3.json, "/nt/getmapdata.php?DISEASE=Influenza<?echo isset($_GET['DATE'])?"&DATE=".$_GET['DATE']:"";?>")
    .defer(d3.json, "/nt/getmapdata.php?DISEASE=Influenza&DATE="+date)
    .await(trendsready);
*/
/*   curDate= new Date(date);
		console.log(curDate.toLocaleDateString());
*/
   timedInterval=setInterval(function(){timedDisplay()},1000);
   return;

}

function timedDisplay(){
	if (curStep >= 0){
		chart.setSelection([{"row":curStep}]);
		var rateById = {};
//		console.log(JSON.stringify(trends.data[6]));
//		curStep=-1;return;
		console.log(curStep);
		console.log(trends.data[curStep].dt);
  		trends.data[curStep].data.forEach(function(d) { rateById[d.COUNTY_FIPS] = d; });
  		g.selectAll("path")
//		      .transition()
		      .style("stroke", "#aaaaaa")
//		       .duration(500)
		      .style("fill", function(d) 
				{ 
					if(d.type == "Feature"){
						return rateById[d.id] == undefined? "#cccccc":color(rateById[d.id].Score);
					} 
					else {
						return "#ffffff";
					}
				});

		  g.selectAll("title")
		        .text(function(d) { return  rateById[d.id] == undefined?"":rateById[d.id].County + ', '+rateById[d.id].State+'\nTweets in 7 Days: '+rateById[d.id].Tweets7D+'\nTweets per million: '+rateById[d.id].TweetsPM+'\nTweet days: '+rateById[d.id].Days+'\nScore: '+rateById[d.id].Score; })
		  ;

		curStep--;
	} else {
		clearInterval(timedInterval);
	}
}

function drawChart() {

	console.log(trends.chartData);
        var data = new google.visualization.DataTable();
	data.addColumn("date","Date");
	data.addColumn("number","Weekly tweets");
	trends.chartData.forEach(function(d) { data.addRow([new Date(d[0]),d[1]]); });


        var options = {
//          title: 'Company Performance',
//	   hAxis: {title:'Weekly Tweet Volume'},
	   legend: {position:'none'},
	   isStacked: true,
	   vAxis: {title: 'Weekly Tweets', minValue: 0, maxValue:12000 , gridlines : { count: 3}},
           tooltip: {textStyle: {fill: '#FF0000'}, showColorCode: true},
        };
//	debugger;

        chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
	google.visualization.events.addListener(chart, 'select', selectHandler);
        chart.draw(data, options);
      }
  google.load("visualization", "1", {packages:["corechart"]});
  google.setOnLoadCallback();

function selectHandler(e) {
	clearInterval(timedInterval);
	var step = chart.getSelection();
//	console.log("entered"+JSON.stringify(step));
	console.log(JSON.stringify(step));
	curStep = step[0].row;
	timedDisplay();
//	console.log(JSON.stringify(chart.getSelection()));
	return false;
}
    </script>
<div id="chart_div"></div>	
</body>
