<?php

header("Content-type: text/css; charset: UTF-8");

require "vendor/autoload.php";

use Sunra\PhpSimple\HtmlDomParser;

include "function.php";

ini_set('max_execution_time', -1);

if (empty($_GET['user'])) {
	response(400, "Empty Parameter", NULL);
	exit();
}

$_GET['status'] = empty($_GET['status']) ? 7 : $_GET['status'];

$url = "https://myanimelist.net/animelist/" . $_GET['user'] . "?status=" . $_GET['status'];

// $file_headers = @get_headers($url);
// if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
//     response(400, "Invalid id", NULL);
//     exit();
// }

$html = HtmlDomParser::file_get_html($url);

$data = [];
$anime_list = $html->find('#list_surround', 0);
foreach ($anime_list->find('table') as $anime) {
	$is_anime = $anime->find('tr td[class^=td]', 1);
	if ($is_anime) {
		$separated_link = explode("/", $anime->find('.animetitle', 0)->href);
		$anime_id = $separated_link[2];

		$info_url = 'http://' . $_SERVER['SERVER_NAME'] . '/mal-scraper/info/anime/' . $anime_id;
		$image = file_get_contents($info_url);
		$image = json_decode($image, true);
		$image = $image['data']['cover'];

		$style0 = "tr:hover .animetitle[href*='/" . $anime_id;
		$style1 = "/']:before{background-image: url(" . $image . ")}";

		$style = $style0 . $style1;
		echo $style . "\n";
	}
}

$html->clear(); 
unset($html);