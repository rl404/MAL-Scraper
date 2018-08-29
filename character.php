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

$url = "https://myanimelist.net/character/" . $_GET['id'];

$file_headers = @get_headers($url);
if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    response(404, "Page Not Found", NULL);
    exit();
}

$html = HtmlDomParser::file_get_html($url)->find('#contentWrapper', 0)->outertext;
$html = str_replace('&quot;', '\"', $html);
$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
$html = HtmlDomParser::str_get_html($html);

// nickname
$nickname = $html->find('h1', 0)->plaintext;

$html = $html->find('#content table tr', 0);
$left_area = $html->find('td', 0);
$right_area = $left_area->next_sibling();

// image
$image = $left_area->find('img', 0)->src;

// animeography
$animeography = [];
$animeography_index = 0;
$animeography_area = $left_area->find('table', 0);
$animeography_area = $animeography_area->find('tr');
if ($animeography_area) {
	foreach ($animeography_area as $each_anime) {
		$each_anime = $each_anime->find('td', 1);

		// id
		$anime_id = $each_anime->find('a', 0)->href;
		$parsed_anime_id = explode('/', $anime_id);
		$anime_id = $parsed_anime_id[4];
		$animeography[$animeography_index]['id'] = $anime_id;

		// url
		$anime_url = $each_anime->find('a', 0)->href;
		$animeography[$animeography_index]['url'] = $anime_url;

		// title
		$anime_title = $each_anime->find('a', 0)->plaintext;
		$animeography[$animeography_index]['title'] = $anime_title;

		// role
		$anime_role = $each_anime->find('div small', 0)->plaintext;
		$animeography[$animeography_index]['role'] = $anime_role;

		$animeography_index++;
	}
}
unset($animeography_area);
unset($animeography_index);

// mangaography
$mangaography = [];
$mangaography_index = 0;
$mangaography_area = $left_area->find('table', 1);
$mangaography_area = $mangaography_area->find('tr');
if ($mangaography_area) {
	foreach ($mangaography_area as $each_manga) {
		$each_manga = $each_manga->find('td', 1);

		// id
		$manga_id = $each_manga->find('a', 0)->href;
		$parsed_manga_id = explode('/', $manga_id);
		$manga_id = $parsed_manga_id[4];
		$mangaography[$mangaography_index]['id'] = $manga_id;

		// url
		$manga_url = $each_manga->find('a', 0)->href;
		$mangaography[$mangaography_index]['url'] = $manga_url;

		// title
		$manga_title = $each_manga->find('a', 0)->plaintext;
		$mangaography[$mangaography_index]['title'] = $manga_title;

		// role
		$manga_role = $each_manga->find('div small', 0)->plaintext;
		$mangaography[$mangaography_index]['role'] = $manga_role;

		$mangaography_index++;
	}
}
unset($mangaography_area);
unset($mangaography_index);

// favorite
$favorite = $left_area->plaintext;
preg_match('/(Member Favorites: ).+/', $favorite, $parsed_favorite);
$favorite = trim($parsed_favorite[0]);
$parsed_favorite = explode(': ', $favorite);
$favorite = str_replace(',', '', $parsed_favorite[1]);

// name
$name_area = $right_area->find('div[class=normal_header]', 0);
$name_kanji = $name_area->find('small', 0)->plaintext;

$name = trim(str_replace($name_kanji, '', $name_area->plaintext));
$name_kanji = preg_replace('/(\(|\))/', '', $name_kanji);

// about
preg_match('/(<div class="normal_header" style="height: 15px;">).*(<div class="normal_header">)/', $right_area->outertext, $about);

$about = str_replace($name_area->outertext, '', $about[0]);
$about = str_replace('<div class="normal_header">', '', $about);
$about = str_replace(['<br>', '<br />', '  '], ["\n", "\n", ' '], $about);
$about = strip_tags($about);
$about = preg_replace('/\n[^\S\n]*/', "\n", $about);

// va
$va = [];
$va_index = 0;
$va_area = $right_area->find('div[class=normal_header]', 1);
$va_area = $va_area->next_sibling();
if ($va_area->tag == 'table') {
	while (true) {

		// image
		$va_image = $va_area->find('img', 0)->src;
		$va[$va_index]['image'] = $va_image;

		// id
		$va_name_area = $va_area->find('td', 1);
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

		$va_area = $va_area->next_sibling();
		if ($va_area->tag != 'table') {
			break;
		} else {
			$va_index++;
		}
	}
}


$data = [
	'id' => $_GET['id'],
	'nickname' => $nickname,
	'image' => $image,
	'name' => $name,
	'name_kanji' => $name_kanji,
	'favorite' => $favorite,
	'about' => $about,
	'va' => $va,
	'animeography' => $animeography,
	'mangaography' => $mangaography,
];

response(200, "Success", $data);
unset($data);