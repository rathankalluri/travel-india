<?php
header('Content-Type: application/json');
#Helper to POST data to webhook
function do_post_request($url, $data, $optional_headers = null)
{
  $params = array('http' => array(
              'method' => 'POST',
              'content' => http_build_query($data)
            ));
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
    throw new Exception("Problem with $url, $php_errormsg");
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
    throw new Exception("Problem reading data from $url, $php_errormsg");
  }
  return $response;
}
#Process Data from your webhook.
function processPlacesMessage($update) {
	if(empty($update["result"]["parameters"]["georaphic-location"])){$geoLoc = FALSE;}else{$geoLoc = $update["result"]["parameters"]["georaphic-location"];}
	if(empty($update["result"]["parameters"]["place-type"])){$placeType = FALSE;}else{$placeType = $update["result"]["parameters"]["place-type"];}
	if(empty($update["result"]["parameters"]["states"])){$states = FALSE;}else{$states =  $update["result"]["parameters"]["states"];}
	if(empty($update["result"]["parameters"]["weather"])){$weather = FALSE;}else{$weather = $update["result"]["parameters"]["weather"];}
	
	$url = 'http://rathankalluri.com/tr-in/hook.php';
	$data = array(
			"geo-location" => $geoLoc,
			"place-type" => $placeType,
			"states" => $states,
			"weather" => $weather,
			"source" => $update["originalRequest"]["source"],
		        "dataFromSource" => $update["originalRequest"]
	);
	$result = do_post_request($url, $data);
	 sendMessage($result);
}
function processDetailsMessage($update) {
	
	$url = 'http://rathankalluri.com/tr-in/detail-hook.php';
	$data = array(
			"detail" => $update["result"]["resolvedQuery"],
			"source" => $update["originalRequest"]["source"],
	);
	$result = do_post_request($url, $data);
	sendMessage($result);
}
#respond back to API.AI
function sendMessage($parameters) {
	header('Content-Type: application/json');
    	//echo json_encode($parameters);
	  echo $parameters;
}
#Get Data from API.AI
$update_response = file_get_contents("php://input");
error_log("raw data:".$update_response, 0);
$update = json_decode($update_response, true);
if (isset($update["result"]["action"])){
if ($update["result"]["metadata"]["intentName"] == "Find places") {
	processPlacesMessage($update);
	
}
else if ($update["result"]["metadata"]["intentName"] == "details") {
	processDetailsMessage($update);
	
}
}
?>
