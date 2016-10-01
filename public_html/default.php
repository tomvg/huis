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
			//Set the right timezone
			date_default_timezone_set("Europe/Amsterdam"); 			
			
			class TaskManager {
				private $tasks;
				private $names;
				private $states;
				private $week;

				function __construct() {
					$this->tasks = array(array("Stoffen", "Badkamer"), 
										 array("Stofzuigen"),
										 array( "WC"));
					$this->names = array("Martijn", "Jorrit", "Tom");
					$this->week = date( "W" );
				}

				// Get the tasks of a week	
				function getTasks() {
					$weekTasks = array();
					foreach ($this->names as $i => $name) {
						$i1 = ($this->week + $i) % count($this->names);
						$i2 = $this->week % count($this->tasks[$i1]);
						$weekTasks[$name] = $this->tasks[$i1][$i2];
					}
					return $weekTasks;
				}

				// Get the states of the tasks from the file. '' or 'checked'
				protected function readTaskStates() {
					if(file_exists('taskStates.txt')) {
						if ($this->week == date("W", filemtime('taskStates.txt'))) {
							$this->states = unserialize(file_get_contents('taskStates.txt'));
						}
					} else {
						$this->resetTaskStates();
					}
				}

				// Return the task state of the person.
				function getTaskState($person) {
					if (empty($this->states)) {
						$this->readTaskStates();
					}
					return $this->states[$person];
				}
				
				// Save all task states.
				protected function writeTaskStates() {
					file_put_contents('taskStates.txt', serialize($this->states));
				}

				// Set task states to given array.
				function setTaskStates($taskStates) {
					$this->states = $taskStates;
					$this->writeTaskStates();
				}

				// Reset the states to '' and write.
				function resetTaskStates() {
					$this->states = array();
					foreach ($this->names as $name) {
						$this->states[$name] = '';
					}
					$this->writeTaskStates();
				}
			}
			


			$tm = new TaskManager();

			//handle the submission of tasks
			if(isset($_POST['posted'])) {
				$taskStates = array();
				foreach ( array("Martijn", "Jorrit", "Tom") as $checkbox ) {
					if(isset($_POST[$checkbox])) {
						$taskStates[$checkbox] = "checked";
					} else {
						$taskStates[$checkbox] = "";
					}
				}
				$tm->setTaskStates($taskStates);
			} 

			$taak = $tm->getTasks();
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
				<td><?=$taak["Martijn"]?></td>
				<td>
					<input
					type="checkbox"
					name="Martijn"
					value="checked"
					onclick="this.form.submit()"
					<?=$tm->getTaskState("Martijn")?>
					>
				</input></td>
			</tr>
			<tr>
				<td>Jorrit:</td>
				<td><?=$taak["Jorrit"]?></td>
				<td>
					<input
					type="checkbox"
					name="Jorrit"
					value="checked"
					onclick="this.form.submit()"
					<?=$tm->getTaskState("Jorrit")?>
					>
				</input></td>
			</tr>
			<tr>
				<td>Tom:</td>
				<td><?=$taak["Tom"]?></td>
				<td>
					<input
					type="checkbox"
					name="Tom"
					value="checked"
					onclick="this.form.submit()"
					<?=$tm->getTaskState("Tom")?>
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
