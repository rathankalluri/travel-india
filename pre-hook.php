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
function processMessage($update) {
	if(empty($update["result"]["parameters"]["georaphic-location"])){$geoLoc = FALSE;}else{$geoLoc = $update["result"]["parameters"]["georaphic-location"];}
	if(empty($update["result"]["parameters"]["place-type"])){$placeType = FALSE;}else{$placeType = $update["result"]["parameters"]["place-type"];}
	if(empty($update["result"]["parameters"]["states"])){$states = FALSE;}else{$states =  $update["result"]["parameters"]["states"];}
	if(empty($update["result"]["parameters"]["weather"])){$weather = FALSE;}else{$weather = $update["result"]["parameters"]["weather"];}
	
	$url = 'http://rathankalluri.com/tr-in/hook.php';
	$data = array(
			"geo-location" => $geoLoc,
			"place-type" => $placeType,
			"states" => $states,
			"weather" => $weather
	);

	$result = do_post_request($url, $data);
	 sendMessage(array(
            "source" => $update["result"]["source"],
            "speech" => $result,
            "displayText" => $result,
            "contextOut" => array()
        ));
}

#respond back to API.AI
function sendMessage($parameters) {
	header('Content-Type: application/json');
    	echo json_encode($parameters);
}

#Get Data from API.AI
$update_response = file_get_contents("php://input");
$update = json_decode($update_response, true);
if (isset($update["result"]["action"])) {
	processMessage($update);
	
}

?>
