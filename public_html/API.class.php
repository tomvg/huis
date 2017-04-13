<?php
/**
 * class API
 * Based on http://coreymaynard.com/blog/creating-a-restful-api-with-php.
 */
abstract class API
{
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';
    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';
    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';
    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $args = Array();
    /**
     * Property: file
     * Stores the input of the PUT request
     */
     protected $file = Null;

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct($request) {
		// Don't use CORS untill we have applied authentication.
        //header("Access-Control-Allow-Orgin: *"); //CORS: Allow all origins.
        //header("Access-Control-Allow-Methods: *"); //CORS: Allow all http methods.
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($request, '/'));
        $this->endpoint = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            $this->verb = array_shift($this->args);
        }

		// Determine the request method. DELETE and PUT look like POST but
		// have a different HTTP_X_HTTP_METHOD.
        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

		// Gather the input and clean it before assigning.
        switch($this->method) {
        case 'DELETE':
        case 'POST':
            $this->request = $this->_cleanInputs($_POST);
            break;
        case 'GET':
            $this->request = $this->_cleanInputs($_GET);
            break;
        case 'PUT':
            $this->request = $this->_cleanInputs($_GET);
            $this->file = file_get_contents("php://input");
            break;
        default:
            $this->_response('Invalid Method', 405);
            break;
        }
    }
	
	/**
	 * Method: processAPI
	 * Checks if the requested endpoint has a method assigned to it.
	 * If so, execute it, else return 404 error. The enpoint function
	 * should return an object with {data->data, status->status}. data will
	 * be converted to json and status will be the http status that is
	 * returned.
	 */
    public function processAPI() {
        if (method_exists($this, $this->endpoint)) {
			/**
			 * Make a reflection method (class that gives info about a
			 * method) to check if the function is public. Only regard
			 * public functions a 'existing' for the API.
			 */
			$ref = new ReflectionMethod($this, $this->endpoint);
			if ($ref->isPublic()) {
				$res = $this->{$this->endpoint}($this->args);
				return
					$this->_response($res['data'], $res['status']);
			}
        }
        return $this->_response("No Endpoint: $this->endpoint", 404);
    }
	
	/**
	 * Method: _response
	 * Set the return status in the header and return the json encoded
	 * response.
	 */
    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }
	
	/**
	 * Method: _cleanInputs
	 * Strip all html and php tags and remove whitespaces from
	 * the beginning and end of the input.
	 * If the input is an array then the above is done element wise.
	 */
    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

	/**
	 * Return error message of status code.
	 * If the code is not defined here then 500 is returned.
	 */
    private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }
}
?>
