<?php

function response($status,$status_message,$data)
{
	header("HTTP/1.1 " . $status);
	
	$response['status'] = $status;
	$response['status_message'] = $status_message;
	$response['data'] = $data;
	
	$json_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	$json_response = str_replace("\\\\", "", $json_response);
	echo $json_response;
}