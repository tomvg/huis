<?php 
//Load the abstract api class
require_once 'API.class.php';

//Set the right timezone
date_default_timezone_set("Europe/Amsterdam"); 			

class TaskManager extends API 
{
	private $tasks;
	private $names;
	private $states;
	private $week;
	private $year;
	private $db;

	function __construct($request) {
		// Run the constructor of the abstract class.
		parent::__construct($request);


		$this->tasks = array(array("Stoffen", "Badkamer"), 
							 array("Stofzuigen"),
							 array( "WC"));
		$this->names = array("Martijn", "Jorrit", "Tom");

		$this->initialize_mysql();
	}

	// Build the mysql table
	private function initialize_mysql() {
		// sqlserver.php should contain the dbhost, dbuser,
		// dbpass and dbname variables.
		include('sqlserver.php');
		$this->db = new mysqli($dbhost, $dbuser,
							   $dbpass, $dbname);
		if($this->db->connect_errno > 0)
		{
			die('Could not connect: ' .
				$this->db->connect_error);
		}

		// Create table if not exists
		$sql = 
			"CREATE TABLE IF NOT EXISTS taskmanager(" .
			"week INT NOT NULL, ";
		foreach( $this->names as $name) {
			$sql .= $name . " BOOL NOT NULL, ";
		}
		$sql .= "PRIMARY KEY ( week ) );";
		$this->db->query($sql);
	}

	// Get the tasks of a week	
	private function getTasks() {
		$weekTasks = array();
		foreach ($this->names as $i => $name) {
			$i1 = ($this->week + $i) % count($this->names);
			$i2 = $this->week % count($this->tasks[$i1]);
			$weekTasks[$name] = $this->tasks[$i1][$i2];
		}
		return $weekTasks;
	}

	// Get the states of the tasks from the file. '' or 'checked'
	private function readTaskStates() {
		$sql = "SELECT ";
		foreach ($this->names as $name) {
			$sql .= $name . ", ";
		} // note there will be an extra ", " at the end.
		$sql = rtrim($sql, ", ") . " FROM taskmanager WHERE week=" .
			$this->year . $this->week . ";";	
		$res = $this->db->query($sql);
		
		if($res->num_rows == 1) {
				$this->states = $res->fetch_assoc();
		} else {
			$this->resetTaskStates();
		}
	}

	// Return the task state of the person.
	private function getTaskState($person) {
		if (empty($this->states)) {
			$this->readTaskStates();
		}
		if($this->states[$person])
			return 'checked';
		else
			return '';
	}
	
	// Save all task states.
	private function writeTaskStates() {
		$sql = "REPLACE INTO taskmanager (week";
		foreach ($this->names as $name) {
			$sql .= ", " . $name;
		}
		$sql .=	") VALUES (" .
			$this->year . $this->week;
		foreach ($this->names as $name) {
			if($this->states[$name])
				$sql .= ", TRUE";
			else
				$sql .= ", FALSE";
		}
		$sql .= ");";

		$this->db->query($sql);
	}

	// Set task states to given array.
	private function setTaskStates($taskStates) {
		$this->states = $taskStates;
		$this->writeTaskStates();
	}

	// Reset the states to '' and write.
	private function resetTaskStates() {
		$this->states = array();
		foreach ($this->names as $name) {
			$this->states[$name] = '';
		}
		$this->writeTaskStates();
	}

	/**
	 * API endpoint task: Requires argument 'week'. Returns
	 * [person1->{task->task, state->state}, person2->{.. ]
	 * Only covers get and put. Put only works for the current week (in the
	 * timezone of the sever)
	 */
	public function task($args) {
		// The first argument should be the yearweek.
		$yearweek = array_shift($args);
		// Check if this is invalid. Note that only the really strange
		// cases are filtered out. There are still many number left that
		// are not actually weeks.
		if( ! ctype_digit($yearweek) or strlen($yearweek) > 6) {
			return [
				"status" => 400,
				"data" => "You have not provided a valid yearweek number. A
				valid number is a string with the full year with the
				weeknumber concatenated to it.",
			];
			
		}
		// Separate year and week. *1 to make this integers.
		$this->year = substr($yearweek, 0, 4)*1;
		$this->week = substr($yearweek, 4)*1;
		$this->readTaskStates();
		return ['data' => $this->states, 'status' => 200,];
		
	}
}



$tm = new TaskManager($_REQUEST['request']);
echo $tm->processAPI();




/*handle the submission of tasks
if(isset($_POST['posted'])) {
	$taskStates = array();
	foreach ( array("Martijn", "Jorrit", "Tom") as $checkbox ) {
		if(isset($_POST[$checkbox])) {
			$taskStates[$checkbox] = TRUE;
		} else {
			$taskStates[$checkbox] = FALSE;
		}
	}
	$tm->setTaskStates($taskStates);
} 

$taak = $tm->getTasks();*/
?>
