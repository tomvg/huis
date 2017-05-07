<?php 
//Load the abstract api class
require_once 'API.class.php';

//Set the right timezone
date_default_timezone_set("Europe/Amsterdam"); 			

class TaskManager extends API 
{
	private $tasks;
	private $names;
	private $db;

	function __construct($request, $input) {
		
		// Run the constructor of the abstract class.
		parent::__construct($request, $input);

		// Save the input
		$this->content = json_decode(
			$input, true);

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

	/**
	 * This function calculates the tasks and retrieves the states from the
	 * database. Then returns them as
	 * {tasks -> {name -> task}, 
	 *  states -> {name -> state}}
	 */
	private function getTasks($year, $week) {
		// Read the task states from the database.
		$sql = "SELECT ";
		foreach ($this->names as $name) {
			$sql .= $name . ", ";
		} // note there will be an extra ", " at the end.
		$sql = rtrim($sql, ", ") . " FROM taskmanager WHERE week=" .
			$year . $week . ";";	
		$res = $this->db->query($sql);
		// If indeed this row exists, save the result
		if($res->num_rows == 1) {
				$states = $res->fetch_assoc();
		} // Else, return all tasks unfinished without changing the database.
		else {
			$states = array();
			foreach ($this->names as $name) {
				$states[$name] = '0';
			}
		}

		// Put it in the right format and add the task name.
		$data = array();
		foreach ($this->names as $i => $name) {
			// Calculate the task.
			$i1 = ($week + $i) % count($this->names);
			$i2 = $week % count($this->tasks[$i1]);
			$task = $this->tasks[$i1][$i2];

			array_push($data, [
				"name" => $name,
				"task" => $task,
				"state" => $states[$name],
			]);
		}
		
		return [
		   	"data" => $data,
			"year" => $year,
			"week" => $week,
		];
				
	}

	/**
	 * Save the provided task states to the sql database.
	 * If a request key is an existing name, then its truth values will be
	 * used. If a name is not in de request then its value will be set to
	 * FALSE. All nonexisting names in the request are ignored.
	 */
	private function setTaskStates($year, $week) {
		// Make the SQL request.
		// Insert when table does not exist and give FALSE to names that
		// were not given.
		$sql = "INSERT INTO taskmanager (week";
		foreach ($this->names as $name) {
			$sql .= ", " . $name;
		}
		$sql .=	") VALUES (" .
			$year . $week;
		foreach ($this->names as $name) {
			if(array_key_exists($name, $this->content)) {
				if($this->content[$name]) {
					$sql .= ", TRUE";
				} else {
					$sql .= ", FALSE";
				}
			}
			else {
				$sql .= ", FALSE";
			} 
		}
		// When this weakyear index exists, update the names that were
		// given.
		$sql .= ") ON DUPLICATE KEY UPDATE week = ";
		$sql .= $year . $week; 	// Do this useless update so that we 
								// never have an empty list of things 
								// to update.
		// Update the columns (names) that were given.
		foreach ($this->names as $name) {
			if(array_key_exists($name, $this->content)) {
				$sql .= ", " . $name ." = ";
				if($this->content[$name]) {
					$sql .= "TRUE";
				} else {
					$sql .= "FALSE";
				}
			}
		}
		$sql .= ';';

		// Perform the SQL request.
		$this->db->query($sql);
		return [
			"data" => "Success",
			"status"=> 200,
		];
	}

	/**
	 * API endpoint tasks: Requires argument 'week'. Returns
	 * [person1->{task->task, state->state}, person2->{.. ]
	 * Only covers get and put. Put only works for the current week (in the
	 * timezone of the sever)
	 */
	public function tasks($args) {
		// The first argument should be the yearweek.
		$yearweek = array_shift($args);
		// Check if this is invalid. Note that only the really strange
		// cases are filtered out. There are still many number left that
		// are not actually weeks.
		if( ! ctype_digit($yearweek) or strlen($yearweek) > 6) {
			return [
				'status' => 400,
				'data' => "You have not provided a valid yearweek number. 
				A valid number is a string with the full year with the
				weeknumber concatenated to it.",
			];
			
		}
		// Separate year and week. *1 to make this integers.
		$year = substr($yearweek, 0, 4)*1;
		$week = substr($yearweek, 4)*1;
		
		switch($this->method) {
		case 'GET':
			return [
				'data' => $this->getTasks($year, $week),
				'status' => 200,
			];
			break;
		// PUT and POST do the same thing in this case. They just update
		// the statusses.
		case 'PUT':
		case 'POST':
			return [
				'data' => $this->setTaskStates($year, $week),
				'status' => 200,
			];
			break;
		default:
			return [
				'data' => 'Invalid Method',
				'status' => 405,
			];
			break;
		}
		
	}
}

$tm = new TaskManager($_REQUEST['request'], 
	file_get_contents('php://input'));
echo $tm->processAPI();

?>
