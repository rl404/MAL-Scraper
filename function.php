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

function getTopType($type)
{
	$converted_type = '';
	switch ($type) {
		case "0":
			$converted_type = '';
			break;
		case "1":
			$converted_type = 'airing';
			break;
		case "2":
			$converted_type = 'upcoming';
			break;
		case "3":
			$converted_type = 'tv';
			break;
		case "4":
			$converted_type = 'movie';
			break;
		case "5":
			$converted_type = 'ova';
			break;
		case "6":
			$converted_type = 'special';
			break;
		case "7":
			$converted_type = 'bypopularity';
			break;
		case "8":
			$converted_type = 'favorite';
			break;
		default:
			$converted_type = '';
	}
	return $converted_type;
}