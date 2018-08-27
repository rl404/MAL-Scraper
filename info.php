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
    response(400, "Invalid id", NULL);
    exit();
}

$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->outertext;
$html = str_replace('&quot;', '\"', $html);
$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
$html = HtmlDomParser::str_get_html($html);

// title, cover
$anime_cover = $html->find('img.ac', 0);
$title = $anime_cover ? $anime_cover->alt : '';
$cover = $anime_cover ? $anime_cover->src : '';
unset($anime_cover);

// id
$anime_id = $html->find('#myinfo_anime_id', 0);
$id = $anime_id->value;
unset($anime_id);

// anime info (left)
$anime_info = $html->find('.js-scrollfix-bottom', 0);

// alternative title
$title2 = [];
if (count($anime_info->find('h2')) > 2) {
	$alter_title = $anime_info->find('h2', 0);
	$next_title = $alter_title->next_sibling();
	while (true) {
		$title_type = $next_title->find('span', 0)->plaintext;
		$clean_title_type = strtolower(str_replace(": ", "", $title_type));

		$title_value = $next_title->plaintext;
		$clean_title_value = trim(str_replace($title_type, "", $title_value));
		$clean_title_value = preg_replace("/([\s])+/", " ", $clean_title_value);

		$title2[$clean_title_type] = $clean_title_value;

		$next_title = $next_title->next_sibling();
		if ($next_title->tag == 'h2' || $next_title->tag == 'br') {
			break;
		}
	}
	unset($alter_title);
	unset($next_title);
}

// other info
$info = [];
$other_info = (count($anime_info->find('h2')) > 2) ? $anime_info->find('h2', 1) : $anime_info->find('h2', 0);
$next_info = $other_info->next_sibling();
while (true) {
	$info_type = $next_info->find('span', 0)->plaintext;
	$clean_info_type = strtolower(str_replace(": ", "", $info_type));

	$info_value = $next_info->plaintext;
	$clean_info_value = trim(str_replace($info_type, "", $info_value));
	$clean_info_value = preg_replace("/([\s])+/", " ", $clean_info_value);
	$clean_info_value = str_replace(", add some", "", $clean_info_value);

	if ($clean_info_type == "published" || $clean_info_type == "aired") {
		$start_air = "";
		$end_air = "";
		if ($clean_info_value != "Not available") {
			$parsed_airing = explode(" to ", $clean_info_value);

			$start_air = ($parsed_airing[0] != "?") ? date('Y-m-d', strtotime($parsed_airing[0])) : "";
			$end_air = ($parsed_airing[1] != "?") ? date('Y-m-d', strtotime($parsed_airing[1])) : "";
		}

		$clean_info_value = [];
		$clean_info_value['start'] = $start_air;
		$clean_info_value['end'] = $end_air;
	}

	if ($clean_info_type == "producers"
		|| $clean_info_type == "licensors"
		|| $clean_info_type == "studios"
		|| $clean_info_type == "genres"
		|| $clean_info_type == "authors"
	) {
		$info_temp = [];
		$info_temp_index = 0;
		if ($clean_info_value != "None found") {
			foreach ($next_info->find('a') as $each_info) {
				$info_temp[$info_temp_index]['name'] = $each_info->plaintext;
				$info_temp[$info_temp_index]['link'] = "https://myanimelist.net" . $each_info->href;
				$info_temp_index++;
			}
		}
		$clean_info_value = $info_temp;
	}

	$info[$clean_info_type] = $clean_info_value;

	$next_info = $next_info->next_sibling();
	if ($next_info->tag == 'h2' || $next_info->tag == 'br') {
		break;
	}
}
unset($other_info);
unset($next_info);
unset($anime_info);

// score
$score = $html->find('div[class="fl-l score"]', 0)->plaintext;
$score = trim($score);

// rank
$rank = $html->find('span[class="numbers ranked"] strong', 0)->plaintext;
$rank = str_replace("#", "", $rank);

// popularity
$popularity = $html->find('span[class="numbers popularity"] strong', 0)->plaintext;
$popularity = str_replace("#", "", $popularity);

// members
$members = $html->find('span[class="numbers members"] strong', 0)->plaintext;
$members = str_replace(",", "", $members);

// favorite
$favorite = $html->find('div[data-id=info2]', 0)->next_sibling()->next_sibling()->next_sibling();
$favorite_title = $favorite->find('span', 0)->plaintext;
$favorite = $favorite->plaintext;
$favorite = trim(str_replace($favorite_title, "", $favorite));
$favorite = str_replace(",", "", $favorite);
$favorite = preg_replace("/([\s])+/", " ", $favorite);

// synopsis
$synopsis = $html->find('span[itemprop=description]', 0);
if ($synopsis) {
	$synopsis = $synopsis->plaintext;
	$synopsis = trim(preg_replace("/\s+/", " ", $synopsis));
}

// related
$related = [];
$related_area = $html->find('.anime_detail_related_anime', 0);
foreach ($related_area->find('tr') as $rel) {
	$rel_type = $rel->find('td', 0)->plaintext;
	$rel_type = trim(strtolower(str_replace(":", "", $rel_type)));

	$each_rel = [];
	$each_rel_index = 0;
	$rel_anime = $rel->find('td', 1);
	foreach ($rel_anime->find('a') as $r) {
		$rel_anime_link = $r->href;
		$separated_anime_link = explode('/', $rel_anime_link);

		$each_rel[$each_rel_index]['id'] = $separated_anime_link[2];
		$each_rel[$each_rel_index]['title'] = $r->plaintext;
		$each_rel[$each_rel_index]['type'] = $separated_anime_link[1];

		$each_rel_index++;
	}

	$related[$rel_type] = $each_rel;
}
unset($related_area);

// character + va
$character = [];
$char_index = 0;
$character_area = $html->find('div[class^=detail-characters-list]', 0);
if ($character_area) {
	$character_left = $character_area->find('div[class*=fl-l]', 0);
	if ($character_left) {
		foreach ($character_left->find('table[width=100%]') as $each_char) {
			$char = $each_char->find('tr td', 1);

			$char_name = trim(preg_replace('/\s+/', ' ', $char->find('a', 0)->plaintext));
			$char_role = trim($char->find('small', 0)->plaintext);

			$character[$char_index]['name'] = $char_name;
			$character[$char_index]['role'] = $char_role;

			$va = $each_char->find('table td', 0);
			if ($va) {
				$va_name =  $va->find('a', 0)->plaintext;
				$va_role =  $va->find('small', 0)->plaintext;

				$character[$char_index]['va_name'] = $va_name;
				$character[$char_index]['va_role'] = $va_role;
			}

			$char_index++;
		}
	}
	unset($character_left);

	$character_right = $character_area->find('div[class*=fl-r]', 0);
	if ($character_right) {
		foreach ($character_right->find('table[width=100%]') as $each_char) {
			$char = $each_char->find('tr td', 1);

			$char_name = trim(preg_replace('/\s+/', ' ', $char->find('a', 0)->plaintext));
			$char_role = trim($char->find('small', 0)->plaintext);

			$character[$char_index]['name'] = $char_name;
			$character[$char_index]['role'] = $char_role;

			$va = $each_char->find('table td', 0);
			if ($va) {
				$va_name =  $va->find('a', 0)->plaintext;
				$va_role =  $va->find('small', 0)->plaintext;

				$character[$char_index]['va_name'] = $va_name;
				$character[$char_index]['va_role'] = $va_role;
			}

			$char_index++;
		}
	}
	unset($character_right);
}
unset($character_area);
unset($char_index);

// staff
$staff = [];
$staff_index = 0;
$staff_area = $html->find('div[class^=detail-characters-list]', 1);
if ($staff_area) {
	$staff_left = $staff_area->find('div[class*=fl-l]', 0);
	if ($staff_left) {
		foreach ($staff_left->find('table[width=100%]') as $each_staff) {
			$st = $each_staff->find('tr td', 1);

			$staff_name = trim(preg_replace('/\s+/', ' ', $st->find('a', 0)->plaintext));
			$staff_role = trim($st->find('small', 0)->plaintext);

			$staff[$staff_index]['name'] = $staff_name;
			$staff[$staff_index]['role'] = $staff_role;

			$va = $each_staff->find('table td', 0);
			if ($va) {
				$va_name =  $va->find('a', 0)->plaintext;
				$va_role =  $va->find('small', 0)->plaintext;

				$staff[$staff_index]['va_name'] = $va_name;
				$staff[$staff_index]['va_role'] = $va_role;
			}

			$staff_index++;
		}
	}
	unset($staff_left);

	$staff_right = $staff_area->find('div[class*=fl-r]', 0);
	if ($staff_right) {
		foreach ($staff_right->find('table[width=100%]') as $each_staff) {
			$st = $each_staff->find('tr td', 1);

			$staff_name = trim(preg_replace('/\s+/', ' ', $st->find('a', 0)->plaintext));
			$staff_role = trim($st->find('small', 0)->plaintext);

			$staff[$staff_index]['name'] = $staff_name;
			$staff[$staff_index]['role'] = $staff_role;

			$va = $each_staff->find('table td', 0);
			if ($va) {
				$va_name =  $va->find('a', 0)->plaintext;
				$va_role =  $va->find('small', 0)->plaintext;

				$staff[$staff_index]['va_name'] = $va_name;
				$staff[$staff_index]['va_role'] = $va_role;
			}

			$staff_index++;
		}
	}
	unset($staff_right);
}
unset($staff_area);
unset($staff_index);

// song
$song = [];
$song_area = $html->find('div[class*="theme-songs opnening"]', 0);
if ($song_area) {
	foreach ($song_area->find('span.theme-song') as $each_song) {
		$each_song = trim(preg_replace('/#\d*:\s/', '', $each_song->plaintext));
		$song['opening'][] = $each_song;
	}
}

$song_area = $html->find('div[class*="theme-songs ending"]', 0);
if ($song_area) {
	foreach ($song_area->find('span.theme-song') as $each_song) {
		$each_song = trim(preg_replace('/#\d*:\s/', '', $each_song->plaintext));
		$song['closing'][] = $each_song;
	}
}
unset($song_area);

$html->clear();
unset($html);

// combine all data
$data = [
	'id' => $id,
	'title' => $title,
	'title2' => $title2,
	'cover' => $cover
];

$data = array_merge($data, $info);

$data2 = [
	'score' => $score,
	'rank' => $rank,
	'popularity' => $popularity,
	'members' => $members,
	'favorite' => $favorite,
	'synopsis' => $synopsis,
	'related' => $related,
	'character' => $character,
	'staff' => $staff,
	'song' => $song,
];

$data = array_merge($data, $data2);

response(200, "Success", $data);
unset($data);