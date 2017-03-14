<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <script language="javascript" src="http://sol.synchrotron.org.au/js/jquery.js?20120216"></script>
    <script language="javascript" src="http://sol.synchrotron.org.au/js/jquery-ui-1.8.2.custom.min.js?20120216"></script>
	<script language="javascript" src="http://sol.synchrotron.org.au/js/highcharts/js/highcharts.js"></script>
    <script language="javascript" src="http://sol.synchrotron.org.au/js/highcharts/js/modules/exporting.js"></script>
	
	<script type="text/javascript">
	window.onload = function () {
		//default chart arrays
		var x = [];
		var y = [];//pvs for orbit data
		var y2 = [];
		var savedX = [];//pvs for saved orbit data
		var savedY = [];
		var compX = [];//pvs to display compared data
		var compY = [];
		
		var displayX = false;//booleans for comparison pvs
		var displayY = false;
		
		//Populate both arrays
		for (var i = 0; i < 97; i++) {		
				x.push(i);
				y.push(0);
				y2.push(0);
				savedX.push(0);
				savedY.push(0);
				compX.push(0);
				compY.push(0);
			}
		var elem = this;
		var pvs = ["SR00BPM00:SA_X_ARRAY_MONITOR","SR00BPM00:SA_Y_ARRAY_MONITOR"];
		var buckets = 97;

		var chart = new Highcharts.Chart({
			chart: {
				type: 'line',
				renderTo : 'myChartH',
				backgroundColor: 'Black'
				},
				title: {
					text: 'Storage Ring Orbit'
					},
				xAxis: {
					categories: x,
					crosshair: true
				},
				yAxis: [{
					
					title: {
						text: 'nm'
					}
				},{
					title:{
						text: 'nm'
					},
					opposite: true
					
				}],
				tooltip: {
					headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
					pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
					'<td style="padding:0"><b>{point.y:.3f} </b></td></tr>',
					footerFormat: '</table>',
					shared: true,
					useHTML: true
					},
				plotOptions: {
					column: {
						pointPadding: 0.3,
						borderWidth: 0
					}
				},
				series: [{
						name: 'Live',
						color: 'red',
						yAxis: 0,
						data: y
						},{
						name: 'Subtracted',
						color: 'blue',
						yAxis: 0,
						data: compX}]
			});

		var chart2 = new Highcharts.Chart({
			chart: {
				type: 'line',
				renderTo : 'myChartV',
				backgroundColor: 'Black'
				},
				title: {
					text: 'Storage Ring Orbit'
					},
				xAxis: {
					categories: x,
					crosshair: true
				},
				yAxis: [{
					
					title: {
						text: 'nm'
					}
				},{
					title:{
						text: 'nm'
					},
					opposite: true
					
				}],
				tooltip: {
					headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
					pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
					'<td style="padding:0"><b>{point.y:.3f} </b></td></tr>',
					footerFormat: '</table>',
					shared: true,
					useHTML: true
					},
				plotOptions: {
					column: {
						pointPadding: 0.3,
						borderWidth: 0
					}
				},
				series: [{
						name: 'Live',
						color: 'red',
						yAxis: 0,
						data: y2
						},{
						name: 'Subtracted',
						color: 'blue',
						yAxis: 0,
						data: compY}]
			});
			
		//Get IP address from php and direct accordingly
		var ipadd = "<?php echo $_SERVER['REMOTE_ADDR']; ?>";
		if (ipadd.indexOf("10.17")!==-1){
			var ws = new WebSocket("ws://10.17.100.199:8888/monitor");
		}else{
			var ws = new WebSocket("ws://10.6.100.199:8888/monitor");
		}

		//Open WS connection
		ws.onopen = function() {
			for (var i = 0; i < pvs.length; i++) {		
				ws.send(pvs[i]);
			}
    		};
		//receive FPM data
		ws.onmessage = function(evt) {
      			var data = JSON.parse(evt.data);
      			if (data.msg_type === "monitor") {
					if (data.pvname === "SR00BPM00:SA_X_ARRAY_MONITOR"){
						chart.series[0].setData(data.value,true);
						y = data.value;
						for (var i = 0; i < 97; i++) {
							compX[i] = data.value[i]-savedX[i];
						}
						chart.series[1].setData(compX,true);
						
					}else if(data.pvname === "SR00BPM00:SA_Y_ARRAY_MONITOR"){
						chart2.series[0].setData(data.value,true);
						y2 = data.value;
						for (var i = 0; i < 97; i++) {
							compY[i] = data.value[i]-savedY[i];
						}
						chart2.series[1].setData(compY,true);
					}
				}
				
		}
	
	//functions to save stored orbit data for subtraction
	$('#xData').click(function (){
		for (var i = 0; i < 97; i++) {
			savedX[i] = y[i];
		}
		
	});
	
	$('#yData').click(function (){
		for (var i = 0; i < 97; i++) {
			savedY[i] = y2[i];
		}
	});
	}
	
	</script>
	
<link rel="stylesheet" type="text/css" href="./orbit.css">
</head>
<title>SR Orbit</title>
<div id="body">
<body bgcolor="#000000">
	<fieldset class="qtBorder"><legend class="qtLabel">Horizontal Orbit:</legend>
	<center><div id = "myChartH"></div></center></fieldset>
	<button id="xData">Save X-Data</button>
	
	<fieldset class="qtBorder"><legend class="qtLabel">Vertical Orbit:</legend>
	<center><div id = "myChartV"></div></center></fieldset>
	<button id="yData">Save Y-Data</button>
</body>
</div>
</html>