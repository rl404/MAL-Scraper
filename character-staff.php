<?php

header('Content-Type: application/json');

require "vendor/autoload.php";

use Sunra\PhpSimple\HtmlDomParser;

include "function.php";

ini_set('max_execution_time', -1);

if (empty($_GET['type']) || empty($_GET['id'])) {
	response(400, "Empty Parameter", NULL);
	exit();
}

$url = "https://myanimelist.net/" . $_GET['type'] . "/" . $_GET['id'];

$file_headers = @get_headers($url);
if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    response(404, "Page Not Found", NULL);
    exit();
}

$html = HtmlDomParser::file_get_html($url)->find('li a[href$=characters]', 0)->href;

if ($_GET['type'] == 'manga') {
	$url = 'https://myanimelist.net' . $html;
} else {
	$url = $html;
}

$html = HtmlDomParser::file_get_html($url)->find('.js-scrollfix-bottom-rel', 0)->outertext;
$html = str_replace('&quot;', '\"', $html);
$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
$html = HtmlDomParser::str_get_html($html);

// character
$character = [];
$character_index = 0;
$char_table = $html->find('h2', 0);
if ($char_table->next_sibling()->tag == 'table') {
	$char_table = $char_table->next_sibling();
	while (true) {

		// image
		$char_image = $char_table->find('td .picSurround img', 0)->getAttribute('data-src');
		$character[$character_index]['image'] = $char_image;

		// id
		$char_name_area = $char_table->find('td', 1);
		$char_id = $char_name_area->find('a', 0)->href;
		$parsed_char_id = explode('/', $char_id);
		$char_id = $parsed_char_id[4];
		$character[$character_index]['id'] = $char_id;

		// name
		$char_name = $char_name_area->find('a', 0)->plaintext;
		$character[$character_index]['name'] = $char_name;

		// role
		$char_role = $char_name_area->find('small', 0)->plaintext;
		$character[$character_index]['role'] = $char_role;

		// va name + role
		$va = [];
		$va_index = 0;
		$char_va_area = $char_table->find('td', 2);
		if ($char_va_area) {
			$char_va_area = $char_va_area->find('table', 0);
			foreach ($char_va_area->find('tr') as $each_va) {
				$va_name_area = $each_va->find('td', 0);

				// id
				$va_id = $va_name_area->find('a', 0)->href;
				$parsed_va_id = explode('/', $va_id);
				$va_id = $parsed_va_id[4];
				$va[$va_index]['id'] = $va_id;

				// name
				$va_name = $va_name_area->find('a', 0)->plaintext;
				$va[$va_index]['name'] = $va_name;

				// role
				$va_role = $va_name_area->find('small', 0)->plaintext;
				$va[$va_index]['role'] = $va_role;

				// image
				$va_image = $each_va->find('td', 1)->find('img', 0)->getAttribute('data-src');
				$va[$va_index]['image'] = $va_image;

				$va_index++;
			}
			$character[$character_index]['va'] = $va;
			unset($char_va_area);
		}

		$char_table = $char_table->next_sibling();
		if ($char_table->tag == "br" || $char_table->tag == "a" || $char_table->tag == "h2" || $char_table->tag == "div") {
			break;
		} else {
			$character_index++;
		}
	}
}
unset($char_table);

// staff
$staff = [];
$staff_index = 0;
$staff_table = $html->find('h2', 1);
if ($staff_table) {
	if ($staff_table->next_sibling()->tag == 'table') {
		$staff_table = $staff_table->next_sibling();
		while (true) {
			// image
			$staff_image = $staff_table->find('td .picSurround img', 0)->getAttribute('data-src');
			$staff[$staff_index]['image'] = $staff_image;

			// id
			$staff_name_area = $staff_table->find('td', 1);
			$staff_id = $staff_name_area->find('a', 0)->href;
			$parsed_staff_id = explode('/', $staff_id);
			$staff_id = $parsed_staff_id[4];
			$staff[$staff_index]['id'] = $staff_id;

			// name
			$staff_name = $staff_name_area->find('a', 0)->plaintext;
			$staff[$staff_index]['name'] = $staff_name;

			// role
			$staff_role = $staff_name_area->find('small', 0)->plaintext;
			$staff[$staff_index]['role'] = $staff_role;

			$staff_table = $staff_table->next_sibling();
			if (!$staff_table) {
				break;
			} else {
				$staff_index++;
			}
		}
	}
}
unset($staff_table);

$data = [
	'character' => $character,
	'staff' => $staff
];

response(200, "Success", $data);
unset($data);