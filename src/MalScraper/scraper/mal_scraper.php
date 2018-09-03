<?php

namespace scraper;

define('MAX_FILE_SIZE', 100000000);

use \DateTime;
use Sunra\PhpSimple\HtmlDomParser;

function response($status,$status_message,$data)
{
	header("HTTP/1.1 " . $status);

	$response['status'] = $status;
	$response['status_message'] = $status_message;
	$response['data'] = $data;

	$json_response = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	$json_response = str_replace("\\\\", "", $json_response);
	return $json_response;
}

function getTopAnimeType($type)
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

function getTopMangaType($type)
{
	$converted_type = '';
	switch ($type) {
		case "0":
			$converted_type = '';
			break;
		case "1":
			$converted_type = 'manga';
			break;
		case "2":
			$converted_type = 'novels';
			break;
		case "3":
			$converted_type = 'oneshots';
			break;
		case "4":
			$converted_type = 'doujin';
			break;
		case "5":
			$converted_type = 'manhwa';
			break;
		case "6":
			$converted_type = 'manhua';
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

function getCurrentSeason()
{
	$day = new DateTime();

	//  Days of spring
	$spring_starts = new DateTime("April 1");
	$spring_ends   = new DateTime("June 30");

   	//  Days of summer
	$summer_starts = new DateTime("July 1");
	$summer_ends   = new DateTime("September 30");

   	//  Days of autumn
	$autumn_starts = new DateTime("October 1");
	$autumn_ends   = new DateTime("December 31");

   	//  If $day is between the days of spring, summer, autumn, and winter
	if( $day >= $spring_starts && $day <= $spring_ends ) :
		$season = "spring";
	elseif( $day >= $summer_starts && $day <= $summer_ends ) :
		$season = "summer";
	elseif( $day >= $autumn_starts && $day <= $autumn_ends ) :
		$season = "fall";
	else :
		$season = "winter";
	endif;

	return $season;
}

function getInfo($type,$id)
{
	$url = "https://myanimelist.net/" . $type . "/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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
				if (count($parsed_airing) > 1) {
					$end_air = ($parsed_airing[1] != "?") ? date('Y-m-d', strtotime($parsed_airing[1])) : "";
				}
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
		$synopsis = trim(preg_replace('/\n[^\S\n]*/', "\n",  $synopsis));
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

	return response(200, "Success", $data);
	unset($data);
}

function getCharacter($id)
{
	$url = "https://myanimelist.net/character/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Page Not Found", NULL);
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
	$image = $left_area->find('img', 0);
	$image = $image ? $image->src : '';

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
		'id' => $id,
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

	return response(200, "Success", $data);
	unset($data);
}

function getCharacterStaff($type,$id)
{
	$url = "https://myanimelist.net/" . $type . "/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Page Not Found", NULL);
	    exit();
	}

	$html = HtmlDomParser::file_get_html($url)->find('li a[href$=characters]', 0)->href;

	if ($type == 'manga') {
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

	return response(200, "Success", $data);
	unset($data);
}

function getPeople($id)
{
	$url = "https://myanimelist.net/people/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Page Not Found", NULL);
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
	$image = $left_area->find('img', 0);
	$image = $image ? $image->src : '';

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
		'id' => $id,
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

	return response(200, "Success", $data);
	unset($data);
}

function getStudioProducer($id,$page=1)
{
	$url = "https://myanimelist.net/anime/producer/" . $id . "/?page=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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

	return response(200, "Success", $data);
	unset($data);
}

function getMagazine($id,$page=1)
{
	$url = "https://myanimelist.net/manga/magazine/" . $id . "/?page=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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

		// author
		$author = [];
		$producer_area = $each_anime->find('div[class=prodsrc]', 0);
		$temp_producer = $producer_area->find('span[class=producer]', 0);
		foreach ($temp_producer->find('a') as $each_producer) {
			$temp_prod = [];

			// prod id
			$prod_id = $each_producer->href;
			$parsed_prod_id = explode('/', $prod_id);
			$temp_prod['id'] = $parsed_prod_id[4];

			// prod name
			$prod_name = $each_producer->plaintext;
			$temp_prod['name'] = $prod_name;

			$author[] = $temp_prod;
		}
		$result['author'] = $author;

		// volume
		$volume = $producer_area->find('div[class=eps]', 0)->plaintext;
		$volume = trim(str_replace(['vols', 'vol'], '', $volume));
		$result['volume'] = $volume;

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

		// serialization
		$serialization = $each_anime->find('div[class="synopsis js-synopsis"] .serialization a', 0);
		$serialization = $serialization ? $serialization->plaintext : '';
		$result['serialization'] = $serialization;

		// airing start
		$info_area = $each_anime->find('.information', 0);
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

	return response(200, "Success", $data);
	unset($data);
}

function getGenre($type,$id,$page=1)
{
	$url = "https://myanimelist.net/" . $type . "/genre/" . $id . "/?page=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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
			$temp_prod['id'] = ($type == 'anime') ? $parsed_prod_id[3] : $parsed_prod_id[4];

			// prod name
			$prod_name = $each_producer->plaintext;
			$temp_prod['name'] = $prod_name;

			$producer[] = $temp_prod;
		}

		if ($type == 'anime') {
			$result['producer'] = $producer;
		} else {
			$result['author'] = $producer;
		}

		// episode
		$episode = $producer_area->find('div[class=eps]', 0)->plaintext;
		$episode = trim(str_replace(['eps', 'ep', 'vols', 'vol'], '', $episode));
		if ($type == 'anime') {
			$result['episode'] = $episode;
		} else {
			$result['volume'] = $episode;
		}

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

		if ($type == 'anime') {
			// licensor
			$licensor = [];
			$temp_licensor = $each_anime->find('div[class="synopsis js-synopsis"] .licensors', 0)->getAttribute('data-licensors');
			$licensor = explode(',', $temp_licensor);
			$result['licensor'] = array_filter($licensor);
		} else {
			// serialization
			$serialization = $each_anime->find('div[class="synopsis js-synopsis"] .serialization a', 0);
			$serialization = $serialization ? $serialization->plaintext : '';
			$result['serialization'] = $serialization;
		}

		$info_area = $each_anime->find('.information', 0);

		if ($type == 'anime') {
			// type
			$type = $info_area->find('.info', 0)->plaintext;
			$type = explode('-', $type);
			$type = trim($type[0]);
			$result['type'] = $type;
		}

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

	return response(200, "Success", $data);
	unset($data);
}

function getAllAnimeGenre()
{
	$url = "https://myanimelist.net/anime.php";

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
	    exit();
	}

	$html = HtmlDomParser::file_get_html($url)->find('.anime-manga-search', 0)->find('.genre-link', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	foreach ($html->find('.genre-list a') as $each_genre) {
		$genre = [];

		// id
		$link = $each_genre->href;
		$link = explode('/', $link);
		$id = $link[3];
		$genre['id'] = $id;

		// name
		$name = str_replace('_', ' ', $link[4]);
		$genre['name'] = $name;

		// count
		$count = $each_genre->plaintext;
		preg_match('/\(.+\)/', $count, $count);
		$count = substr($count[0], 1, strlen($count[0])-2);
		$genre['count'] = str_replace(',', '', $count);

		$data[] = $genre;
 	}

	return response(200, "Success", $data);
	unset($data);
}

function getAllMangaGenre()
{
	$url = "https://myanimelist.net/manga.php";

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
	    exit();
	}

	$html = HtmlDomParser::file_get_html($url)->find('.anime-manga-search', 0)->find('.genre-link', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	foreach ($html->find('.genre-list a') as $each_genre) {
		$genre = [];

		// id
		$link = $each_genre->href;
		$link = explode('/', $link);
		$id = $link[3];
		$genre['id'] = $id;

		// name
		$name = str_replace('_', ' ', $link[4]);
		$genre['name'] = $name;

		// count
		$count = $each_genre->plaintext;
		preg_match('/\(.+\)/', $count, $count);
		$count = substr($count[0], 1, strlen($count[0])-2);
		$genre['count'] = str_replace(',', '', $count);

		$data[] = $genre;
 	}

	return response(200, "Success", $data);
	unset($data);
}

function getAllStudioProducer()
{
	$url = "https://myanimelist.net/anime/producer";

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
	    exit();
	}

	$html = HtmlDomParser::file_get_html($url)->find('.anime-manga-search', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	foreach ($html->find('.genre-list a') as $each_studio) {
		$studio = [];

		// id
		$link = $each_studio->href;
		$link = explode('/', $link);
		$id = $link[3];
		$studio['id'] = $id;

		// name
		$name = str_replace('_', ' ', $link[4]);
		$studio['name'] = $name;

		// count
		$count = $each_studio->plaintext;
		preg_match('/\(.+\)/', $count, $count);
		$count = substr($count[0], 1, strlen($count[0])-2);
		$studio['count'] = str_replace(',', '', $count);

		$data[] = $studio;
 	}

	return response(200, "Success", $data);
	unset($data);
}

function getAllMagazine()
{
	$url = "https://myanimelist.net/manga/magazine";

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
	    exit();
	}

	$html = HtmlDomParser::file_get_html($url)->find('.anime-manga-search', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	foreach ($html->find('.genre-list a') as $each_magazine) {
		$magazine = [];

		// id
		$link = $each_magazine->href;
		$link = explode('/', $link);
		$id = $link[3];
		$magazine['id'] = $id;

		// name
		$name = str_replace('_', ' ', $link[4]);
		$magazine['name'] = $name;

		// count
		$count = $each_magazine->plaintext;
		preg_match('/\(.+\)/', $count, $count);
		$count = substr($count[0], 1, strlen($count[0])-2);
		$magazine['count'] = str_replace(',', '', $count);

		$data[] = $magazine;
 	}

	return response(200, "Success", $data);
	unset($data);
}

function searchAnime($q, $page=1)
{
	$page = 50*($page-1);

	$url = "https://myanimelist.net/anime.php?q=" . $q . "&show=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
	    exit;
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

	return response(200, "Success", $data);
	unset($data);
}

function searchManga($q,$page=1)
{
	$page = 50*($page-1);

	$url = "https://myanimelist.net/manga.php?q=" . $q . "&show=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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

		// volume
		$volume = $result_area->find('td', 3)->plaintext;
		$result['volume'] = trim($volume);

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


	return response(200, "Success", $data);
	unset($data);
}

function searchPeople($q,$page=1)
{
	$page = 50*($page-1);

	$url = "https://myanimelist.net/people.php?q=" . $q . "&show=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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
		$id = $parsed_char_id[2];
		$result['id'] = $id;

		// name
		$name = $name_area->find('a', 0)->plaintext;
		$result['name'] = $name;

		// nickname
		$nickname = $name_area->find('small', 0);
		$nickname = $nickname ? substr($nickname->plaintext, 1, strlen($nickname->plaintext)-2) : '';
		$result['nickname'] = $nickname;

		$data[] = $result;

		$result_area = $result_area->next_sibling();
		if (!$result_area) {
			break;
		}
	}
	unset($result_table);
	unset($result_area);

	return response(200, "Success", $data);
	unset($data);
}

function searchCharacter($q,$page=1)
{
	$url = "https://myanimelist.net/character.php?q=" . $q . "&show=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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
		$role['manga'] = $role['anime'] = [];
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

	return response(200, "Success", $data);
	unset($data);
}

function getSeason($year=false,$season=false)
{
	$year = !$year ? date('Y') : $year;
	$season = !$season ? getCurrentSeason() : $season;

	$param = '/' . $year . '/' . $season;

	$url = "https://myanimelist.net/anime/season" . $param;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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

	return response(200, "Success", $data);
	unset($data);
}

function getTopAnime($type=0,$page=1)
{
	$page = 50*($page-1);
	$type = getTopAnimeType($type);

	$url = "https://myanimelist.net/topanime.php?type=" . $type . "&limit=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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

	return response(200, "Success", $data);
	unset($data);
}

function getTopManga($type=0,$page=1)
{
	$page = 50*($page-1);
	$type = getTopMangaType($type);

	$url = "https://myanimelist.net/topmanga.php?type=" . $type . "&limit=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(404, "Invalid id", NULL);
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

		// volume
		$volume = str_replace('(', '', $parsed_info_2[1]);
		$data[$data_index]['volume'] = $volume;

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

	return response(200, "Success", $data);
	unset($data);
}

function getCover($user,$status=7)
{
	$url = "https://myanimelist.net/animelist/" . $user . "?status=" . $status;

	$file_headers = @get_headers($url);
	if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return response(400, "Invalid id", NULL);
	    exit();
	}

	$html = HtmlDomParser::file_get_html($url);

	$data = [];
	$anime_list = $html->find('#list_surround', 0);
	foreach ($anime_list->find('table') as $anime) {
		$is_anime = $anime->find('tr td[class^=td]', 1);
		if ($is_anime) {
			$separated_link = explode("/", $anime->find('.animetitle', 0)->href);
			$anime_id = $separated_link[2];

			$image = getInfo('anime', $anime_id);
			// $image = file_get_contents($info_url);
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
}