<?php

header('Content-Type: application/json');

require "vendor/autoload.php";

use Sunra\PhpSimple\HtmlDomParser;

include "function.php";

ini_set('max_execution_time', -1);

if (empty($_GET['q'])) {
	response(400, "Empty Parameter", NULL);
	exit();
}

$page = 0;
if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
	$page = 50*($_GET['page']-1);
}

$url = "https://myanimelist.net/character.php?q=" . $_GET['q'] . "&show=" . $page;

$file_headers = @get_headers($url);
if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    response(404, "Invalid id", NULL);
    exit();
}

$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->outertext;
$html = str_replace('&quot;', '\"', $html);
$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
$html = HtmlDomParser::str_get_html($html);

$data = [];
$result_table = $html->find('table', 0);
$result_area = $result_table->find('tr', 0)->next_sibling();
while (true) {
	$result = [];
	$name_area = $result_area->find('td', 1);

	// id
	$id = $name_area->find('a', 0)->href;
	$parsed_char_id = explode('/', $id);
	$id = $parsed_char_id[4];
	$result['id'] = $id;

	// name
	$name = $name_area->find('a', 0)->plaintext;
	$result['name'] = $name;

	// nickname
	$nickname = $name_area->find('small', 0);
	$nickname = $nickname ? substr($nickname->plaintext, 1, strlen($nickname->plaintext)-2) : '';
	$result['nickname'] = $nickname;

	// role
	$role = [];
	$role_area = $result_area->find('td', 2)->find('small', 0);
	foreach ($role_area->find('a') as $each_role) {
		$temp_role = [];

		// role id
		$role_id = $each_role->href;
		$parsed_role_id = explode('/', $role_id);
		$role_id = $parsed_role_id[2];
		$temp_role['id'] = $role_id;

		// role type
		$role_type = $parsed_role_id[1];

		// role title
		$role_title = $each_role->plaintext;
		$temp_role['title'] = $role_title;

		if ($role_title) {
			$role[$role_type][] = $temp_role;
		}
	}
	$result = array_merge($result, $role);

	$data[] = $result;

	$result_area = $result_area->next_sibling();
	if (!$result_area) {
		break;
	}
}


response(200, "Success", $data);
unset($data);
