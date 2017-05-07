<html>
	<head>

		<title>AvS</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">

		<!--refresh every 3 hours-->
		<meta http-equiv="refresh" content="10800"> 
		
		<!--make suitable for mobile-->
		<meta name="viewport" content="width=460">

		<!--A link to the used font-->
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,700&amp;subset=latin" rel="stylesheet" type="text/css">		

		<!-- Load jquery --!>
		<script
			src="https://code.jquery.com/jquery-3.1.1.min.js"
			integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
			crossorigin="anonymous">
		</script>
		<!-- Load D3 --!>
		<script src="https://d3js.org/d3.v4.min.js"></script>

		<style>
			h1 {
				font-weight: 700;
				font-family: 'Open Sans';
				font-size: 3.5em;
				margin-bottom: 0em;
			}
			table {
				width:auto;
				font-weight: 300;
				font-family: 'Open Sans';
				text-align:	left;
				font-size: 1.5em;
			}
			button {
				color: #000;
				border: 1px solid #000;
				background-color: #FFF;
				width: 2em;
				height: 2em;
				margin-top : 2em;
			}
		</style>
	</head>
	<body>
		<!--Titel-->
		<h1>AvS<span style="font-size:50%"> 179</span></h1>
		<div id="scheduleDiv"></div>


		<!-- Jquery to get the current tasks and states. --!>
		<script>
		// Get the year and week in server time.
		var year = <?php echo date('Y');?>;
		var week = <?php echo date('W');?>;

		// Function that adds the schedule to the dom.
		function makeSchedule(tasks, div) {
			var table = d3.select(div).append('table');
			
			// This variable holds the columns that have to be created from
			// the data. Each element in the array gives a column. The
			// elements should be objects with cl and html keys set. 
			// cl is just a string that will be given as the class of the
			// elements in the column.
			// html must be a function that takes the data that is assigned
			// to the row and returns a dom object that will be placed in
			// the table element.
			var columns = [
				// Name of the person.
				{cl: 'name', html: function(r) { 
					return document.createTextNode(r.name + ': '); 
					}
				},
				// Name of the task.
				{cl: 'task', html: function(r) { 
					return document.createTextNode(r.task); 
					}
				},
				// A checkbox to set the task a done (/undone).
				{cl: 'state', html: function(r) { 
					var cb = document.createElement('input');
					cb.type = "checkbox";
					cb.name = r.name;
					cb.checked = r.state;

					// Add a function that posts the change when a checkbox is
					// changed.
					$(cb).change(function() {
						toPost = {};
						toPost[r.name] = cb.checked;
						$.post('tasks/' + tasks.year + tasks.week,
							JSON.stringify(toPost));
					});
					return cb;
				}
			}];
			
			// Create the table using d3.
			table.selectAll('tr')
				  .data(tasks.data)
				.enter()
				  .append('tr')
				.selectAll('td')
				  .data(columns)
				.enter()
				  .append('td')
				  .attr('class', function(c) {return c.cl})
				  .append(function(c) {
					  r = d3.select(this.parentNode).datum();
					  return c.html(r);
				  });
		}
		

		// Retrieve the tasks of this week and run the function that adds
		// the schedule.
		$(document).ready(function() {
			$.ajax({
				url: "tasks/" + year + week
			})
			.then(function(tasks) {
				makeSchedule(tasks, "#scheduleDiv");
			});
		});

		</script>
	</body>
</html>
