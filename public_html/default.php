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

		<?php 
			// Get the tasks of a week	
			function getTasks($week) {	
				$list = array("Stoffen", "Stofzuigen", "WC");
				if ($week % 2 == 1)
					$list[0] = "Badkamer";
				$taak = array( 
					"M" => $list[$week%3],
					"J" => $list[($week+1)%3],
					"T" => $list[($week+2)%3],
				);

				return $taak;
			}
			
			// Get the states of the tasks from the file. '' or 'checked'
			function getTaskState($person) {
				if(file_exists('taskStates.txt')) {
					$taskStates = unserialize(file_get_contents('taskStates.txt'));
				} else {
					$taskStates = array(
									'taskCheck_M' => '',
									'taskCheck_J' => '',
									'taskCheck_T' => '',
								);
					file_put_contents('taskStates.txt', serialize($taskStates));
				}
				return $taskStates[$person];
			}
			
			function resetTaskStates() {
				if(file_exists('taskStates.txt')) {
					unlink('taskStates.txt');
				}
			}
			
			//Set the right timezone
			date_default_timezone_set("Europe/Amsterdam"); 			

			$week = date ( "W" ); 

			// Reset taskstates if they are from prev. week.
			// Ignore any POST in this case.
			if( $week != date("W", filemtime('taskStates.txt')) )	{
				include 'scripts/reset_taskStates.php';
				unset($_POST);
			}

			//handle the submission of tasks
			if(isset($_POST['posted'])) {
				$taskStates = array();
				foreach ( array("taskCheck_M", "taskCheck_J", "taskCheck_T") as $checkbox ) {
					if(isset($_POST[$checkbox])) {
						$taskStates[$checkbox] = "checked";
					} else {
						$taskStates[$checkbox] = "";
					}
				}
				file_put_contents('taskStates.txt', serialize($taskStates));
			} 

			$taak = getTasks($week);
		?>		
		
	</head>
	<body>
		<!--Titel-->
		<h1>AvS<span style="font-size:50%"> 179</span></h1>

			<!--Het schoonmaakrooster-->		
		<form action="default.php" method="post">
		<table>
			<input type="hidden" name="posted" value="true">
			<tr>
				<td>Martijn:</td>
				<td><?=$taak["M"]?></td>
				<td>
					<input
					type="checkbox"
					name="taskCheck_M"
					value="checked"
					onclick="this.form.submit()"
					<?=getTaskState("taskCheck_M")?>
					>
				</input></td>
			</tr>
			<tr>
				<td>Jorrit:</td>
				<td><?=$taak["J"]?></td>
				<td>
					<input
					type="checkbox"
					name="taskCheck_J"
					value="checked"
					onclick="this.form.submit()"
					<?=getTaskState("taskCheck_J")?>
					>
				</input></td>
			</tr>
			<tr>
				<td>Tom:</td>
				<td><?=$taak["T"]?></td>
				<td>
					<input
					type="checkbox"
					name="taskCheck_T"
					value="checked"
					onclick="this.form.submit()"
					<?=getTaskState("taskCheck_T")?>
					>
				</input></td>
			</tr>
		</table>
		</form>


		<!--The weather widget-->		
		<!--
		<hr>
<iframe src="https://www.meteoblue.com/en/weather/widget/three/delft_netherlands_2757345?geoloc=fixed&nocurrent=1&days=4&tempunit=CELSIUS&windunit=KILOMETER_PER_HOUR&layout=bright"  frameborder="0" scrolling="NO" allowtransparency="true" sandbox="allow-same-origin allow-scripts allow-popups" style="width: 460px;height: 495px"></iframe> -->
		<!--<div> DO NOT REMOVE THIS LINK <a href="https://www.meteoblue.com/en/weather/forecast/week/delft_netherlands_2757345?utm_source=weather_widget&utm_medium=linkus&utm_content=three&utm_campaign=Weather%2BWidget" target="_blank">meteoblue</a></div>-->
	</body>
</html>
