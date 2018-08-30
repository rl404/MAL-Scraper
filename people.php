<?php

header('Content-Type: application/json');

require "vendor/autoload.php";

use Sunra\PhpSimple\HtmlDomParser;

include "function.php";

ini_set('max_execution_time', -1);

if (empty($_GET['id'])) {
	response(400, "Empty Parameter", NULL);
	exit();
}

$url = "https://myanimelist.net/people/" . $_GET['id'];

$file_headers = @get_headers($url);
if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    response(404, "Page Not Found", NULL);
    exit();
}

$html = HtmlDomParser::file_get_html($url)->find('#contentWrapper', 0)->outertext;
$html = str_replace('&quot;', '\"', $html);
$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
$html = HtmlDomParser::str_get_html($html);

// name
$name = $html->find('h1', 0)->plaintext;

$html = $html->find('#content table tr', 0);
$left_area = $html->find('td', 0);
$right_area = $left_area->next_sibling();

// image
$image = $left_area->find('img', 0)->src;

// biodata
$biodata = $left_area->innertext;
$useless_biodata = '';
$useless_area = $left_area->find('div', 0);
for ($i = 0; $i < 4; $i++) {
	$useless_biodata .= $useless_area->outertext;
	$useless_area = $useless_area->next_sibling();
}
$biodata = str_replace($useless_biodata, '', $biodata);
$biodata = preg_replace("/([\s])+/", " ", $biodata);

// given name
$given_name = '';
preg_match("/(Given name:<\/span>)[^<]*/", $biodata, $temp_given_name);
if ($temp_given_name) {
	$given_name = strip_tags($temp_given_name[0]);
	$parsed_given_name = explode(": ", $given_name);
	$given_name = trim($parsed_given_name[1]);
}

// family name
$family_name = '';
preg_match("/(Family name:<\/span>)([^<])*/", $biodata, $temp_family_name);
if ($temp_family_name) {
	$family_name = strip_tags($temp_family_name[0]);
	$parsed_family_name = explode(": ", $family_name);
	$family_name = trim($parsed_family_name[1]);
}

// alternative name
$alternative_name = '';
preg_match("/(Alternate names:<\/span>)([^<])*/", $biodata, $temp_alternative_name);
if ($temp_alternative_name) {
	$alternative_name = strip_tags($temp_alternative_name[0]);
	$parsed_alternative_name = explode(": ", $alternative_name);
	$alternative_name = trim($parsed_alternative_name[1]);
	$alternative_name = explode(', ', $alternative_name);
}

// birthday
$birthday = '';
preg_match("/(Birthday:<\/span>)([^<])*/", $biodata, $temp_birthday);
if ($temp_birthday) {
	$birthday = strip_tags($temp_birthday[0]);
	$parsed_alternative_name = explode(": ", $birthday);
	$birthday = trim($parsed_alternative_name[1]);
}

// website
$website = '';
preg_match("/(Website:<\/span> <a)([^<])*/", $biodata, $temp_website);
if ($temp_website) {
	preg_match("/\".+\"/", $temp_website[0], $temp_website);
	if ($temp_website[0] != '"http://"') {
		$website = str_replace('"', '', $temp_website[0]);
	}
}

// favorite
$favorite = '';
preg_match("/(Member Favorites:<\/span>)([^<])*/", $biodata, $temp_favorite);
if ($temp_favorite) {
	$favorite = strip_tags($temp_favorite[0]);
	$parsed_favorite = explode(": ", $favorite);
	$favorite = trim($parsed_favorite[1]);
	$favorite = str_replace(',', '', $favorite);
}

// more
$more = $left_area->find('div[class^=people-informantion-more]', 0)->plaintext;
$more = preg_replace('/\n[^\S\n]*/', "\n", $more);

// va
$va = [];
$va_index = 0;
$va_area = $right_area->find('.normal_header', 0)->next_sibling();
if ($va_area->tag == 'table') {
	if ($va_area->find('tr')) {
		foreach ($va_area->find('tr') as $each_va) {
			$anime_area = $each_va->find('td', 1);

			// anime id
			$anime_id = $anime_area->find('a', 0)->href;
			$parsed_anime_id = explode('/', $anime_id);
			$anime_id = $parsed_anime_id[4];
			$va[$va_index]['anime']['id'] = $anime_id;

			// anime title
			$anime_title = $anime_area->find('a', 0)->plaintext;
			$va[$va_index]['anime']['title'] = $anime_title;

			$character_area = $each_va->find('td', 2);

			// character id
			$character_id = $character_area->find('a', 0)->href;
			$parsed_character_id = explode('/', $character_id);
			$character_id = $parsed_character_id[4];
			$va[$va_index]['character']['id'] = $character_id;

			// character name
			$character_name = $character_area->find('a', 0)->plaintext;
			$va[$va_index]['character']['name'] = $character_name;

			// character role
			$character_role = $character_area->find('div', 0)->plaintext;
			$va[$va_index]['character']['role'] = $character_role;

			$va_index++;
		}
	}
}
unset($va_area);

// staff
$staff = [];
$staff_index = 0;
$staff_area = $right_area->find('.normal_header', 1)->next_sibling();
if ($staff_area->tag == 'table') {
	foreach ($staff_area->find('tr') as $each_staff) {
		$each_staff = $each_staff->find('td', 1);

		// anime id
		$anime_id = $each_staff->find('a', 0)->href;
		$parsed_anime_id = explode('/', $anime_id);
		$anime_id = $parsed_anime_id[4];
		$staff[$staff_index]['id'] = $anime_id;

		// anime title
		$anime_title = $each_staff->find('a', 0)->plaintext;
		$staff[$staff_index]['title'] = $anime_title;

		// role
		$role = $each_staff->find('small', 0)->plaintext;
		$staff[$staff_index]['role'] = $role;

		$staff_index++;
	}
}
unset($staff_area);

// manga
$published_manga = [];
$manga_index = 0;
$manga_area = $right_area->find('.normal_header', 2)->next_sibling();
if ($manga_area->tag == 'table') {
	foreach ($manga_area->find('tr') as $each_manga) {
		$each_manga = $each_manga->find('td', 1);

		// manga id
		$manga_id = $each_manga->find('a', 0)->href;
		$parsed_manga_id = explode('/', $manga_id);
		$manga_id = $parsed_manga_id[4];
		$published_manga[$manga_index]['id'] = $manga_id;

		// manga title
		$manga_title = $each_manga->find('a', 0)->plaintext;
		$published_manga[$manga_index]['title'] = $manga_title;

		// role
		$role = $each_manga->find('small', 0)->plaintext;
		$published_manga[$manga_index]['role'] = $role;

		$manga_index++;
	}
}
unset($manga_area);

$data = [
	'id' => $_GET['id'],
	'name' => $name,
	'image' => $image,
	'given_name' => $given_name,
	'family_name' => $family_name,
	'alternative_name' => $alternative_name,
	'birthday' => $birthday,
	'website' => $website,
	'favorite' => $favorite,
	'more' => $more,
	'va' => $va,
	'staff' => $staff,
	'published_manga' => $published_manga,
];

response(200, "Success", $data);
unset($data);