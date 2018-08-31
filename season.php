<?php

header('Content-Type: application/json');

require "vendor/autoload.php";

use Sunra\PhpSimple\HtmlDomParser;

include "function.php";

ini_set('max_execution_time', -1);

if (empty($_GET['year']) xor empty($_GET['season'])) {
	response(400, "Empty Parameter", NULL);
	exit();
}

$param = '';
if (!empty($_GET['year']) && !empty($_GET['season'])) {
	$year = $_GET['year'];
	$season = $_GET['season'];
	$param = '/' . $year . '/' . $season;
}

$url = "https://myanimelist.net/anime/season" . $param;

$file_headers = @get_headers($url);
if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    response(404, "Invalid id", NULL);
    exit();
}

$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->find('.js-categories-seasonal', 0)->outertext;
$html = str_replace('&quot;', '\"', $html);
$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
$html = HtmlDomParser::str_get_html($html);

$data = [];
$anime_table = $html->find('div[class="seasonal-anime js-seasonal-anime"]');
foreach ($anime_table as $each_anime) {
	$result = [];

	// id
	$name_area = $each_anime->find('div[class=title]', 0);
	$id = $name_area->find('p a', 0)->href;
	$parsed_char_id = explode('/', $id);
	$id = $parsed_char_id[4];
	$result['id'] = $id;

	// title
	$title = $name_area->find('p a', 0)->plaintext;
	$result['title'] = $title;

	// producer
	$producer = [];
	$producer_area = $each_anime->find('div[class=prodsrc]', 0);
	$temp_producer = $producer_area->find('span[class=producer]', 0);
	foreach ($temp_producer->find('a') as $each_producer) {
		$temp_prod = [];

		// prod id
		$prod_id = $each_producer->href;
		$parsed_prod_id = explode('/', $prod_id);
		$temp_prod['id'] = $parsed_prod_id[3];

		// prod name
		$prod_name = $each_producer->plaintext;
		$temp_prod['name'] = $prod_name;

		$producer[] = $temp_prod;
	}
	$result['producer'] = $producer;

	// episode
	$episode = $producer_area->find('div[class=eps]', 0)->plaintext;
	$episode = trim(str_replace(['eps', 'ep'], '', $episode));
	$result['episode'] = $episode;

	// source
	$source = $producer_area->find('span[class=source]', 0)->plaintext;
	$result['source'] = trim($source);

	// genre
	$genre = [];
	$genre_area = $each_anime->find('div[class="genres js-genre"]', 0);
	foreach ($genre_area->find('a') as $each_genre) {
		$genre[] = $each_genre->plaintext;
	}
	$result['genre'] = $genre;

	// synopsis
	$synopsis = $each_anime->find('div[class="synopsis js-synopsis"]', 0)->plaintext;
	$synopsis = trim(preg_replace("/([\s])+/", " ", $synopsis));
	$result['synopsis'] = $synopsis;

	// licensor
	$licensor = [];
	$temp_licensor = $each_anime->find('div[class="synopsis js-synopsis"] .licensors', 0)->getAttribute('data-licensors');
	$licensor = explode(',', $temp_licensor);
	$result['licensor'] = array_filter($licensor);

	// type
	$info_area = $each_anime->find('.information', 0);
	$type = $info_area->find('.info', 0)->plaintext;
	$type = explode('-', $type);
	$type = trim($type[0]);
	$result['type'] = $type;

	// airing start
	$airing_start = $info_area->find('.info .remain-time', 0)->plaintext;
	$result['airing_start'] = trim($airing_start);

	// member
	$member = $info_area->find('.scormem span[class^=member]', 0)->plaintext;
	$result['member'] = trim(str_replace(',', '', $member));

	// score
	$score = $info_area->find('.scormem .score', 0)->plaintext;
	$result['score'] = trim($score);

	$data[] = $result;
}

response(200, "Success", $data);
unset($data);