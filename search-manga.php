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

$url = "https://myanimelist.net/manga.php?q=" . $_GET['q'] . "&show=" . $page;

$file_headers = @get_headers($url);
if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    response(404, "Invalid id", NULL);
    exit();
}

$html = HtmlDomParser::file_get_html($url)->find('div[class^=js-categories-seasonal]', 0)->outertext;
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
	$id = $name_area->find('div[id^=sarea]', 0)->id;
	$id = str_replace('sarea', '', $id);
	$result['id'] = $id;

	// title
	$title = $name_area->find('strong', 0)->plaintext;
	$result['title'] = $title;

	// summary
	$summary = $name_area->find('.pt4', 0)->plaintext;
	$result['summary'] = str_replace('read more.', '', $summary);

	// type
	$type = $result_area->find('td', 2)->plaintext;
	$result['type'] = trim($type);

	// episode
	$episode = $result_area->find('td', 3)->plaintext;
	$result['episode'] = trim($episode);

	// score
	$score = $result_area->find('td', 4)->plaintext;
	$result['score'] = trim($score);

	$data[] = $result;

	$result_area = $result_area->next_sibling();
	if (!$result_area) {
		break;
	}
}
unset($result_table);
unset($result_area);


response(200, "Success", $data);
unset($data);