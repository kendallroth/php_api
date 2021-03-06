<?php
require_once("/home/cabox/workspace/constants.php");
require_once(UTILITIES);

$status = 1;
$message = "API Error - info";
$data = "";

switch($this->method) {
	case "GET":
		$status = 0;
		$message = "Example API Usage";
		$data = array(
			"version" => $this->version,
			"requestIP" => $_SERVER["REMOTE_ADDR"],
			"currentTime" => date("Y/m/d H:i:s", time()),
			"prettyTime" => date("M-d-Y h:i:s A", time()),
			"apiStatusCodes" => array(
				"0 => Success",
				"1 => Error",
				"2 => Warning"
			)
		);
		break;
	case "POST":
	case "PUT":
	case "DELETE":
	default:
		$message = "Only GET methods are accepted for the information API";
		break;
}

//Return the proper API response
return new ApiResponse($status, $message, $data);
