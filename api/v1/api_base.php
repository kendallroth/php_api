<?php
require_once("/home/cabox/workspace/constants.php");

/**
 * @brief	Base API class for handling the requested data and response information/data
 */
abstract class API {
	protected $version = "";
    //The HTTP method this request was made in, either GET, POST, PUT or DELETE
    protected $method = "";
	//The Model requested in the URI. eg: /files
    protected $endpoint = "";
	//An optional additional descriptor about the endpoint, used for things that can not be handled by the basic methods.
	//	eg: /files/process
    protected $verb = "";
	//Any additional URI components after the endpoint and verb have been removed, in our case, an integer ID for the resource. 
	//	eg: /<endpoint>/<verb>/<arg0>/<arg1> or /<endpoint>/<arg0>
    protected $args = Array();
	//Stores the input of the PUT request
     protected $PUT = null;
	
    public function __construct($request) {
		//Set header information
		header("Access-Control-Allow-Orgin: *");
		header("Access-Control-Allow-Methods: *");
		header("Content-Type: application/json");

		//Get the request arguments
		$this->args = explode('/', rtrim($request, '/'));
		//Get the endpoint from the request
		$this->endpoint = array_shift($this->args);
		//Get the additional request verb
		if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
			$this->verb = array_shift($this->args);
		}

		//Detect the request method
		$this->method = $_SERVER['REQUEST_METHOD'];
		//Put and Delete methods are "hidden" in the Post method (need to extract them)
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$this->method = 'DELETE';
			} else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
				$this->method = 'PUT';
			} else {
				throw new Exception("Unexpected Header");
			}
		}

		//Process the request method
		switch($this->method) {
			case 'DELETE':
			case 'POST':
				$this->request = $this->cleanInputs($_POST);
				break;
			case 'GET':
				$this->request = $this->cleanInputs($_GET);
				break;
			case 'PUT':
				$this->request = $this->cleanInputs($_GET);
				//Parse the data from the input file (where PHP stores PUT data) into the "PUT" variable
				parse_str(file_get_contents("php://input"), $this->PUT);
				break;
			default:
				$this->response('Invalid Method', 405);
				break;
		}
    }
	
	/**
	 * @brief	Process the API request and return the resulting data
	 * @return	Response status and data
	 */
	public function processAPI() {
		//Get the api file that corresponds to the requested endpoint
		$apiFile = API_PATH . "/{$this->version}/api_" . strtolower($this->endpoint) . ".php";

		//Determine if file exists for the requested endpoint
		if ( file_exists($apiFile) ) {
			//Process the API and return the result to the user (ApiResponse object)
			$response = require_once($apiFile);
			return $response;
		}
		
		//Return endpoint error response
		$response = new ApiResponse(1, "ERROR: No Endpoint: $this->endpoint");
		$response->httpCode = 404;
		return $response;
	}

	/**
	 * @brief	Clean the request input data
	 * @param	$data	Request input data
	 * @return	Sanitized data
	 */
	private function cleanInputs($data) {
		$clean_input = Array();
		//Handle arrays by recursively calling the function
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$clean_input[$k] = $this->cleanInputs($v);
			}
		} else {
			//Remove tags from the data
			$clean_input = trim(strip_tags($data));
		}
		return $clean_input;
	}
}
