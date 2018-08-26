<?php

function response($status,$status_message,$data)
{
	header("HTTP/1.1 " . $status);
	
	$response['status'] = $status;
	$response['status_message'] = $status_message;
	$response['data'] = $data;
	
	$json_response = json_encode($response, JSON_UNESCAPED_UNICODE);
	echo $json_response;
}