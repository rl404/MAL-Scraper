<?php

header('Content-Type: application/json');

require "vendor/autoload.php";

use Sunra\PhpSimple\HtmlDomParser;

include "function.php";

ini_set('max_execution_time', -1);

$type = '';
$page = 0;

if (!empty($_GET['type'])) {
	$type = getTopType($_GET['type']);
}

if (!empty($_GET['page'])) {
	$page = 50*($_GET['page']-1);
}

$url = "https://myanimelist.net/topanime.php?type=" . $type . "&limit=" . $page;

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
$data_index = 0;
$top_table = $html->find('table', 0);
foreach ($top_table->find('tr[class=ranking-list]') as $each_anime) {

	// rank
	$rank = $each_anime->find('td span', 0)->plaintext;
	$data[$data_index]['rank'] = trim($rank);

	// id
	$name_area = $each_anime->find('td .detail', 0);
	$id = $name_area->find('div', 0)->id;
	$id = str_replace('area', '', $id);
	$data[$data_index]['id'] = $id;

	// title
	$title = $name_area->find('a', 0)->plaintext;
	$data[$data_index]['title'] = $title;

	// type
	$info_area = $name_area->find('div[class^=information]', 0);
	$parsed_info = explode('<br>', $info_area->innertext);
	$parsed_info[0] = trim(preg_replace("/([\s])+/", " ", $parsed_info[0]));
	$parsed_info_2 = explode(' ', $parsed_info[0]);
	$type = $parsed_info_2[0];
	$data[$data_index]['type'] = $type;

	// episode
	$episode = str_replace('(', '', $parsed_info_2[1]);
	$data[$data_index]['episode'] = $episode;

	// date
	$date = explode('-', $parsed_info[1]);
	$start_date = trim($date[0]);
	$end_date = trim($date[1]);
	$data[$data_index]['start_date'] = $start_date;
	$data[$data_index]['end_date'] = $end_date;

	// member
	$member = trim(str_replace(['members', 'favorites', ','], '', $parsed_info[2]));
	$data[$data_index]['member'] = $member;

	//score
	$score = $each_anime->find('td', 2)->plaintext;
	$data[$data_index]['score'] = trim($score);

	$data_index++;
}
unset($top_table);

response(200, "Success", $data);
unset($data);