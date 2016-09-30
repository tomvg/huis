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
		<!--Het schoonmaakrooster-->		
		<?php 
			//Set the right timezone
			date_default_timezone_set("Europe/Amsterdam"); 			

			$week = date ( "W" ); 

			// Reset taskstates if they are from prev. week.
			// Ignore any POST in this case.
			if( $week != date("W", filemtime('taskStates.txt')) )	{
				include 'scripts/reset_taskStates.php';
				unset($_POST);
			}
			
			$taak = array(
				0 => "Stoffen",
				1 => "Stofzuigen",
				2 => "WC",
			);
			if ($week % 2 == 1)
				$taak[0] = "Badkamer";

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
		?>	
		<form action="default.php" method="post">
		<table>
			<input type="hidden" name="posted" value="true">
			<tr>
				<td>Martijn:</td>
				<td><?=$taak[$week%3]?></td>
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
				<td><?=$taak[($week+1)%3]?></td>
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
				<td><?=$taak[($week+2)%3]?></td>
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


		<hr>


		<!--The weather widget-->		
		<iframe src="https://www.meteoblue.com/en/weather/widget/three/delft_netherlands_2757345?geoloc=fixed&nocurrent=1&days=4&tempunit=CELSIUS&windunit=KILOMETER_PER_HOUR&layout=bright"  frameborder="0" scrolling="NO" allowtransparency="true" sandbox="allow-same-origin allow-scripts allow-popups" style="width: 460px;height: 495px"></iframe> 
		<!--<div> DO NOT REMOVE THIS LINK <a href="https://www.meteoblue.com/en/weather/forecast/week/delft_netherlands_2757345?utm_source=weather_widget&utm_medium=linkus&utm_content=three&utm_campaign=Weather%2BWidget" target="_blank">meteoblue</a></div>-->
	</body>
</html>
