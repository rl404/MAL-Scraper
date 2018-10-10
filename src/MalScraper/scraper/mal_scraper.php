<?php

namespace scraper;

define('MAX_FILE_SIZE', 100000000);

use \DateTime;
use Sunra\PhpSimple\HtmlDomParser;

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

function imageUrlCleaner($str)
{
	preg_match('/(questionmark)|(qm_50)/', $str, $temp_image);
	$str = $temp_image ? '' : $str;
	$str = str_replace('v.jpg', '.jpg', $str);
	$str = str_replace('_thumb.jpg', '.jpg', $str);
	$str = str_replace('userimages/thumbs', 'userimages', $str);
	$str = preg_replace('/r\/\d{1,3}x\d{1,3}\//', '', $str);
	$str = preg_replace('/\?.+/', '', $str);

	return $str;
}

function getInfo($type,$id)
{
	$url = "https://myanimelist.net/" . $type . "/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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

	// english title
	preg_match('/(English:<\/span>)([^<]*)/', $anime_info->innertext, $english);
	$title2['english'] = trim($english ? $english[2] : '');

	// synonym title
	preg_match('/(Synonyms:<\/span>)([^<]*)/', $anime_info->innertext, $synonym);
	$title2['synonym'] = trim($synonym ? $synonym[2] : '');

	// japanese title
	preg_match('/(Japanese:<\/span>)([^<]*)/', $anime_info->innertext, $japanese);
	$title2['japanese'] = trim($japanese ? $japanese[2] : '');

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
		$clean_info_value = str_replace([", add some", '?', 'Not yet aired', 'Unknown'], "", $clean_info_value);

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
					$temp_id = explode('/', $each_info->href);
					$info_temp[$info_temp_index]['id'] = $clean_info_type == "authors" ? $temp_id[2] : $temp_id[3];
					$info_temp[$info_temp_index]['name'] = $each_info->plaintext;
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
	$score = $score != 'N/A' ? $score : '';

	// voter
	$voter = $html->find('div[class="fl-l score"]', 0)->getAttribute('data-user');
	$voter = trim(str_replace(['users', 'user', ','], '', $voter));

	// rank
	$rank = $html->find('span[class="numbers ranked"] strong', 0)->plaintext;
	$rank = $rank != 'N/A' ? $rank : '';
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
	} else {
		$synopsis = '';
	}

	// related
	$related = [];
	$related_area = $html->find('.anime_detail_related_anime', 0);
	if ($related_area) {
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
				$char_image = $each_char->find('tr td', 0)->find('img', 0)->getAttribute('data-src');
				$char_image = imageUrlCleaner($char_image);

				$char = $each_char->find('tr td', 1);

				$char_id = $char->find('a', 0)->href;
				$char_id = explode('/', $char_id);
				$char_id = $char_id[4];

				$char_name = trim(preg_replace('/\s+/', ' ', $char->find('a', 0)->plaintext));
				$char_role = trim($char->find('small', 0)->plaintext);

				$character[$char_index]['id'] = $char_id;
				$character[$char_index]['name'] = $char_name;
				$character[$char_index]['role'] = $char_role;
				$character[$char_index]['image'] = $char_image;

				$va = $each_char->find('table td', 0);
				if ($va) {
					$va_id = $va->find('a', 0)->href;
					$va_id = explode('/', $va_id);
					$va_id = $va_id[4];

					$va_name =  $va->find('a', 0)->plaintext;
					$va_role =  $va->find('small', 0)->plaintext;

					$va_image = $each_char->find('table td', 1)->find('img', 0)->getAttribute('data-src');
					$va_image = imageUrlCleaner($va_image);
				}

				$character[$char_index]['va_id'] = isset($va_id) ? $va_id : '';
				$character[$char_index]['va_name'] = isset($va_name) ? $va_name : '';
				$character[$char_index]['va_role'] = isset($va_role) ? $va_role : '';
				$character[$char_index]['va_image'] = isset($va_image) ? $va_image : '';

				$char_index++;
			}
		}
		unset($character_left);

		$character_right = $character_area->find('div[class*=fl-r]', 0);
		if ($character_right) {
			foreach ($character_right->find('table[width=100%]') as $each_char) {
				$char_image = $each_char->find('tr td', 0)->find('img', 0)->getAttribute('data-src');
				$char_image = imageUrlCleaner($char_image);

				$char = $each_char->find('tr td', 1);

				$char_id = $char->find('a', 0)->href;
				$char_id = explode('/', $char_id);
				$char_id = $char_id[4];

				$char_name = trim(preg_replace('/\s+/', ' ', $char->find('a', 0)->plaintext));
				$char_role = trim($char->find('small', 0)->plaintext);

				$character[$char_index]['id'] = $char_id;
				$character[$char_index]['name'] = $char_name;
				$character[$char_index]['role'] = $char_role;
				$character[$char_index]['image'] = $char_image;

				$va = $each_char->find('table td', 0);
				if ($va) {
					$va_id = $va->find('a', 0)->href;
					$va_id = explode('/', $va_id);
					$va_id = $va_id[4];

					$va_name =  $va->find('a', 0)->plaintext;
					$va_role =  $va->find('small', 0)->plaintext;

					$va_image = $each_char->find('table td', 1)->find('img', 0)->getAttribute('data-src');
					$va_image = imageUrlCleaner($va_image);

					$character[$char_index]['va_id'] = $va_id;
					$character[$char_index]['va_name'] = $va_name;
					$character[$char_index]['va_role'] = $va_role;
					$character[$char_index]['va_image'] = $va_image;
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
				$staff_image = $each_staff->find('tr td', 0)->find('img', 0)->getAttribute('data-src');
				$staff_image = imageUrlCleaner($staff_image);

				$st = $each_staff->find('tr td', 1);

				$staff_id = $st->find('a', 0)->href;
				$staff_id = explode('/', $staff_id);
				$staff_id = $staff_id[4];

				$staff_name = trim(preg_replace('/\s+/', ' ', $st->find('a', 0)->plaintext));
				$staff_role = trim($st->find('small', 0)->plaintext);

				$staff[$staff_index]['id'] = $staff_id;
				$staff[$staff_index]['name'] = $staff_name;
				$staff[$staff_index]['role'] = $staff_role;
				$staff[$staff_index]['image'] = $staff_image;

				$staff_index++;
			}
		}
		unset($staff_left);

		$staff_right = $staff_area->find('div[class*=fl-r]', 0);
		if ($staff_right) {
			foreach ($staff_right->find('table[width=100%]') as $each_staff) {
				$staff_image = $each_staff->find('tr td', 0)->find('img', 0)->getAttribute('data-src');
				$staff_image = imageUrlCleaner($staff_image);

				$st = $each_staff->find('tr td', 1);

				$staff_id = $st->find('a', 0)->href;
				$staff_id = explode('/', $staff_id);
				$staff_id = $staff_id[4];

				$staff_name = trim(preg_replace('/\s+/', ' ', $st->find('a', 0)->plaintext));
				$staff_role = trim($st->find('small', 0)->plaintext);

				$staff[$staff_index]['id'] = $staff_id;
				$staff[$staff_index]['name'] = $staff_name;
				$staff[$staff_index]['role'] = $staff_role;
				$staff[$staff_index]['image'] = $staff_image;

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
		'cover' => $cover,
		'title' => $title,
		'title2' => $title2,
		'synopsis' => $synopsis,
		'score' => $score,
		'voter' => $voter,
		'rank' => $rank,
		'popularity' => $popularity,
		'members' => $members,
		'favorite' => $favorite,
	];

	$data = array_merge($data, $info);

	$data2 = [
		'related' => $related,
		'character' => $character,
		'staff' => $staff,
		'song' => $song,
	];

	$data = array_merge($data, $data2);

	return $data;
}

function getCharacter($id)
{
	$url = "https://myanimelist.net/character/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('#contentWrapper', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	// nickname
	$nickname = $html->find('h1', 0)->plaintext;
	$nickname = trim(preg_replace('/\s+/', ' ', $nickname));
	preg_match('/\"([^"])*/', $nickname, $nickname);
	if ($nickname) {
		$nickname = substr($nickname[0], 1, strlen($nickname[0])-2);
	} else {
		$nickname = '';
	}

	$html = $html->find('#content table tr', 0);
	$left_area = $html->find('td', 0);
	$right_area = $left_area->next_sibling();

	// image
	$image = $left_area->find('div', 0)->find('a', 0);
	$image = $image->find('img', 0);
	$image = $image ? $image->src : '';

	// animeography
	$animeography = [];
	$animeography_index = 0;
	$animeography_area = $left_area->find('table', 0);
	$animeography_area = $animeography_area->find('tr');
	if ($animeography_area) {
		foreach ($animeography_area as $each_anime) {
			$anime_image = $each_anime->find('td', 0)->find('img', 0)->src;
			$anime_image = imageUrlCleaner($anime_image);
			$animeography[$animeography_index]['image'] = $anime_image;

			$each_anime = $each_anime->find('td', 1);

			// id
			$anime_id = $each_anime->find('a', 0)->href;
			$parsed_anime_id = explode('/', $anime_id);
			$anime_id = $parsed_anime_id[4];
			$animeography[$animeography_index]['id'] = $anime_id;

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
			$manga_image = $each_manga->find('td', 0)->find('img', 0)->src;
			$manga_image = imageUrlCleaner($manga_image);
			$mangaography[$mangaography_index]['image'] = $manga_image;

			$each_manga = $each_manga->find('td', 1);

			// id
			$manga_id = $each_manga->find('a', 0)->href;
			$parsed_manga_id = explode('/', $manga_id);
			$manga_id = $parsed_manga_id[4];
			$mangaography[$mangaography_index]['id'] = $manga_id;

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
	$name_kanji = $name_area->find('small', 0);
	$name_kanji = $name_kanji ? $name_kanji->plaintext : '';

	$name = trim(str_replace($name_kanji, '', $name_area->plaintext));
	$name_kanji = preg_replace('/(\(|\))/', '', $name_kanji);

	// about
	preg_match('/(<div class="normal_header" style="height: 15px;">).*(<div class="normal_header">)/', $right_area->outertext, $about);

	$about = str_replace($name_area->outertext, '', $about[0]);
	$about = str_replace('<div class="normal_header">', '', $about);

	preg_match('/(No biography written)/', $about, $temp_about);
	if (!$temp_about) {
		$about = str_replace(['<br>', '<br />', '  '], ["\n", "\n", ' '], $about);
		$about = strip_tags($about);
		$about = preg_replace('/\n[^\S\n]*/', "\n", $about);
	} else {
		$about = '';
	}

	// va
	$va = [];
	$va_index = 0;
	$va_area = $right_area->find('div[class=normal_header]', 1);
	$va_area = $va_area->next_sibling();
	if ($va_area->tag == 'table') {
		while (true) {

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

			// image
			$va_image = $va_area->find('img', 0)->src;
			$va_image = imageUrlCleaner($va_image);
			$va[$va_index]['image'] = $va_image;

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
		'image' => $image,
		'nickname' => $nickname,
		'name' => $name,
		'name_kanji' => $name_kanji,
		'favorite' => $favorite,
		'about' => $about,
		'animeography' => $animeography,
		'mangaography' => $mangaography,
		'va' => $va,
	];

	return $data;
}

function getPeople($id)
{
	$url = "https://myanimelist.net/people/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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
				// anime image
				$anime_image = $each_va->find('td', 0)->find('img', 0)->getAttribute('data-src');
				$va[$va_index]['anime']['image'] = imageUrlCleaner($anime_image);

				$anime_area = $each_va->find('td', 1);

				// anime id
				$anime_id = $anime_area->find('a', 0)->href;
				$parsed_anime_id = explode('/', $anime_id);
				$anime_id = $parsed_anime_id[4];
				$va[$va_index]['anime']['id'] = $anime_id;

				// anime title
				$anime_title = $anime_area->find('a', 0)->plaintext;
				$va[$va_index]['anime']['title'] = $anime_title;

				// character image
				$character_image = $each_va->find('td', 3)->find('img', 0)->getAttribute('data-src');
				$va[$va_index]['character']['image'] = imageUrlCleaner($character_image);

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
			$anime_image = $each_staff->find('td', 0)->find('img', 0)->getAttribute('data-src');
			$staff[$staff_index]['image'] = imageUrlCleaner($anime_image);

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
			$manga_image = $each_manga->find('td', 0)->find('img', 0)->getAttribute('data-src');
			$published_manga[$manga_index]['image'] = imageUrlCleaner($manga_image);

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

	return $data;
}

function getCharacterStaff($type,$id)
{
	$url = "https://myanimelist.net/" . $type . "/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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
			$character[$character_index]['image'] = imageUrlCleaner($char_image);

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
					$va[$va_index]['image'] = imageUrlCleaner($va_image);

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
				$staff[$staff_index]['image'] = imageUrlCleaner($staff_image);

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

	return $data;
}

function getStat($type,$id)
{
	$url = "https://myanimelist.net/" . $type . "/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('li a[href$=stats]', 0)->href;

	if ($type == 'manga') {
		$url = 'https://myanimelist.net' . $html;
	} else {
		$url = $html;
	}

	$html = HtmlDomParser::file_get_html($url)->find('.js-scrollfix-bottom-rel', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = str_replace('&nbsp;', ' ', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	// summary
	$summary = [];
	$summary_area = $html->find('h2', 0);
	$summary_area = $summary_area->next_sibling();
	if ($summary_area->tag == 'div') {
		while (true) {
			$each_summary = [];

			// status
			$temp_type = $summary_area->find('span', 0)->plaintext;
			$type = strtolower($temp_type);

			// count
			$status_area = $summary_area->plaintext;
			$count = str_replace($temp_type, '', $status_area);
			$each_summary[$type] = trim(str_replace(',', '', $count));

			$summary[] = $each_summary;

			$summary_area = $summary_area->next_sibling();
			if ($summary_area->tag != 'div') {
				break;
			}
		}
	}

	// score
	$score = [];
	$score_area = $html->find('h2', 1);
	$score_area = $score_area->next_sibling();
	if ($score_area->tag == 'table') {
		foreach ($score_area->find('tr') as $each_score) {
			$temp_score = [];

			// type
			$type = $each_score->find('td', 0)->plaintext;
			$temp_score['type'] = $type;

			// vote
			$temp_vote = $each_score->find('td', 1)->find('span small', 0)->plaintext;
			$vote = substr($temp_vote, 1, strlen($temp_vote)-2);
			$temp_score['vote'] = str_replace(' votes', '', $vote);

			// percent
			$percent = $each_score->find('td', 1)->find('span', 0)->plaintext;
			$percent = str_replace([$temp_vote, '%'], '', $percent);
			$temp_score['percent'] = trim($percent);

			$score[] = $temp_score;
		}
	}

	$data = [
		'summary' => $summary,
		'score' => $score,
	];

	return $data;
}

function getPicture($type,$id)
{
	$url = "https://myanimelist.net/" . $type . "/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('li a[href$=pics]', 0)->href;

	if ($type == 'manga') {
		$url = 'https://myanimelist.net' . $html;
	} else {
		$url = $html;
	}

	$html = HtmlDomParser::file_get_html($url)->find('.js-scrollfix-bottom-rel', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	$picture_table = $html->find('table', 0);
	if ($picture_table) {
		foreach ($picture_table->find('img') as $each_picture) {
			if ($each_picture) {
				$data[] = $each_picture->src;
			}
		}
	}

	return $data;
}

function getCharacterPicture($id)
{
	$url = "https://myanimelist.net/character/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('li a[href$=pictures]', 0)->href;
	$html = HtmlDomParser::file_get_html($html)->find('#content table tr td', 0)->next_sibling()->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	$picture_table = $html->find('table', 0);
	if ($picture_table) {
		foreach ($picture_table->find('img') as $each_picture) {
			if ($each_picture) {
				$data[] = $each_picture->src;
			}
		}
	}

	return $data;
}

function getPeoplePicture($id)
{
	$url = "https://myanimelist.net/people/" . $id;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('li a[href$=pictures]', 0)->href;
	$html = HtmlDomParser::file_get_html($html)->find('#content table tr td', 0)->next_sibling()->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	$picture_table = $html->find('table', 0);
	if ($picture_table) {
		foreach ($picture_table->find('img') as $each_picture) {
			if ($each_picture) {
				$data[] = $each_picture->src;
			}
		}
	}

	return $data;
}

function getStudioProducer($id,$page=1)
{
	$url = "https://myanimelist.net/anime/producer/" . $id . "/?page=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->find('.js-categories-seasonal', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	$anime_table = $html->find('div[class="seasonal-anime js-seasonal-anime"]');
	foreach ($anime_table as $each_anime) {
		$result = [];

		// image
		$image = $each_anime->find('div[class=image]', 0)->find('img', 0)->getAttribute('data-src');
		$result['image'] = imageUrlCleaner($image);

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

	return $data;
}

function getMagazine($id,$page=1)
{
	$url = "https://myanimelist.net/manga/magazine/" . $id . "/?page=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->find('.js-categories-seasonal', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	$anime_table = $html->find('div[class="seasonal-anime js-seasonal-anime"]');
	foreach ($anime_table as $each_anime) {
		$result = [];

		// image
		$image = $each_anime->find('div[class=image]', 0)->find('img', 0)->getAttribute('data-src');
		$result['image'] = imageUrlCleaner($image);

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

	return $data;
}

function getGenre($type,$id,$page=1)
{
	$url = "https://myanimelist.net/" . $type . "/genre/" . $id . "/?page=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->find('.js-categories-seasonal', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	$anime_table = $html->find('div[class="seasonal-anime js-seasonal-anime"]');
	foreach ($anime_table as $each_anime) {
		$result = [];

		// image
		$image = $each_anime->find('div[class=image]', 0)->find('img', 0)->getAttribute('data-src');
		$result['image'] = imageUrlCleaner($image);

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

	return $data;
}

function getAllAnimeGenre()
{
	$url = "https://myanimelist.net/anime.php";

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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
		preg_match('/\([0-9,]+\)/', $count, $count);
		$count = substr($count[0], 1, strlen($count[0])-2);
		$genre['count'] = str_replace(',', '', $count);

		$data[] = $genre;
 	}

	return $data;
}

function getAllMangaGenre()
{
	$url = "https://myanimelist.net/manga.php";

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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
		preg_match('/\([0-9,]+\)/', $count, $count);
		$count = substr($count[0], 1, strlen($count[0])-2);
		$genre['count'] = str_replace(',', '', $count);

		$data[] = $genre;
 	}

	return $data;
}

function getAllStudioProducer()
{
	$url = "https://myanimelist.net/anime/producer";

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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
		$name = $each_studio->plaintext;
		$studio['name'] = trim(preg_replace('/\([0-9,]+\)/', '', $name));

		// count
		$count = $each_studio->plaintext;
		preg_match('/\([0-9,]+\)/', $count, $count);
		$count = substr($count[0], 1, strlen($count[0])-2);
		$studio['count'] = str_replace(',', '', $count);

		$data[] = $studio;
 	}

	return $data;
}

function getAllMagazine()
{
	$url = "https://myanimelist.net/manga/magazine";

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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
		$name = $each_magazine->plaintext;
		$magazine['name'] = trim(preg_replace('/\([0-9,]+\)/', '', $name));

		// count
		$count = $each_magazine->plaintext;
		preg_match('/\([0-9,]+\)/', $count, $count);
		$count = substr($count[0], 1, strlen($count[0])-2);
		$magazine['count'] = str_replace(',', '', $count);

		$data[] = $magazine;
 	}

	return $data;
}

function searchAnime($q, $page=1)
{
	if (strlen($q) < 3) {
		return 400;
	}

	$page = 50*($page-1);

	$url = "https://myanimelist.net/anime.php?q=" . $q . "&show=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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

		// image
		$image = $result_area->find('td', 0)->find('a img', 0)->getAttribute('data-src');
		$result['image'] = imageUrlCleaner($image);

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
		$episode = trim($episode);
		$episode = $episode == '-' ? '' : $episode;
		$result['episode'] = $episode;

		// score
		$score = $result_area->find('td', 4)->plaintext;
		$score = trim($score);
		$score = $score == 'N/A' ? '' : $score;
		$result['score'] = $score;

		$data[] = $result;

		$result_area = $result_area->next_sibling();
		if (!$result_area) {
			break;
		}
	}
	unset($result_table);
	unset($result_area);

	return $data;
}

function searchManga($q,$page=1)
{
	if (strlen($q) < 3) {
		return 400;
	}

	$page = 50*($page-1);

	$url = "https://myanimelist.net/manga.php?q=" . $q . "&show=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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

		// image
		$image = $result_area->find('td', 0)->find('a img', 0)->getAttribute('data-src');
		$result['image'] = imageUrlCleaner($image);

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

	return $data;
}

function searchCharacter($q,$page=1)
{
	if (strlen($q) < 3) {
		return 400;
	}

	$url = "https://myanimelist.net/character.php?q=" . $q . "&show=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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

		// image
		$image = $result_area->find('td', 0)->find('a img', 0)->src;
		$result['image'] = imageUrlCleaner($image);

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

	return $data;
}

function searchPeople($q,$page=1)
{
	if (strlen($q) < 3) {
		return 400;
	}

	$page = 50*($page-1);

	$url = "https://myanimelist.net/people.php?q=" . $q . "&show=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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

		// image
		$image = $result_area->find('td', 0)->find('a img', 0)->src;
		$result['image'] = imageUrlCleaner($image);

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

	return $data;
}

function getSeason($year=false,$season=false)
{
	$year = !$year ? date('Y') : $year;
	$season = !$season ? getCurrentSeason() : $season;

	$param = '/' . $year . '/' . $season;

	$url = "https://myanimelist.net/anime/season" . $param;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->find('.js-categories-seasonal', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	$anime_table = $html->find('div[class="seasonal-anime js-seasonal-anime"]');
	foreach ($anime_table as $each_anime) {
		$result = [];

		// image
		$temp_image = $each_anime->find('div[class=image]', 0)->find('img', 0);
		$image = $temp_image->src;
		if (!$image) {
			$image = $temp_image->getAttribute('data-src');
		}
		$result['image'] = imageUrlCleaner($image);

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
		$episode = $episode == '?' ? '' : $episode;
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
		preg_match('/(No synopsis)/', $synopsis, $temp_synopsis);
		if (!$temp_synopsis) {
			$synopsis = trim(preg_replace("/([\s])+/", " ", $synopsis));
		} else {
			$synopsis = '';
		}
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
		$result['airing_start'] = trim(str_replace(['?', ' ,'], ['', ','], $airing_start));

		// member
		$member = $info_area->find('.scormem span[class^=member]', 0)->plaintext;
		$result['member'] = trim(str_replace(',', '', $member));

		// score
		$score = $info_area->find('.scormem .score', 0)->plaintext;
		$result['score'] = trim(str_replace('N/A', '', $score));

		$data[] = $result;
	}

	return $data;
}

function getTopAnime($type=0,$page=1)
{
	$page = 50*($page-1);
	$type = getTopAnimeType($type);

	$url = "https://myanimelist.net/topanime.php?type=" . $type . "&limit=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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

		// image
		$image = $each_anime->find('td', 1)->find('a img', 0)->getAttribute('data-src');
		$data[$data_index]['image'] = imageUrlCleaner($image);

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
		$episode = $episode == '?' ? '' : $episode;
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
		$data[$data_index]['score'] = trim(str_replace('N/A', '', $score));

		$data_index++;
	}
	unset($top_table);

	return $data;
}

function getTopManga($type=0,$page=1)
{
	$page = 50*($page-1);
	$type = getTopMangaType($type);

	$url = "https://myanimelist.net/topmanga.php?type=" . $type . "&limit=" . $page;

	$file_headers = @get_headers($url);
	if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
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

		// image
		$image = $each_anime->find('td', 1)->find('a img', 0)->getAttribute('data-src');
		$data[$data_index]['image'] = imageUrlCleaner($image);

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
		$volume = $volume == '?' ? '' : $volume;
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
		$data[$data_index]['score'] = trim(str_replace('N/A', '', $score));

		$data_index++;
	}
	unset($top_table);

	return $data;
}

function getUser($user)
{
	$url = "https://myanimelist.net/profile/" . $user;

	$file_headers = @get_headers($url);
	if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$left_area = $html->find('.container-left .user-profile', 0);
	$right_area = $html->find('.container-right', 0);

	// image
	$image = $left_area->find('.user-image img', 0);
	$profile_image = $image ? imageUrlCleaner($image->src) : '';

	// status
	$last_online = $gender = $birthday = $location = $joined_date = '';
	$status_area = $left_area->find('.user-status', 0);
	foreach ($status_area->find('li') as $each_status) {
		$status_type = trim($each_status->find('span', 0)->plaintext);
		$status_value = trim($each_status->find('span', 1)->plaintext);

		switch ($status_type) {
			// last online
			case "Last Online":
			$last_online = $status_value;
			break;

			// gender
			case "Gender":
			$gender = $status_value;
			break;

			// birthday
			case "Birthday":
			$birthday = $status_value;
			break;

			// location
			case "Location":
			$location = $status_value;
			break;

			// joined date
			case "Joined":
			$joined_date =$status_value;
			break;

			default:
			'';
		}
	}

	// forum post
	$status_area = $left_area->find('.user-status', 2);
	$forum_post = trim($status_area->find('li', 0)->find('span', 1)->plaintext);

	// review
	$review = trim($status_area->find('li', 1)->find('span', 1)->plaintext);

	// recommendation
	$recommendation = trim($status_area->find('li', 2)->find('span', 1)->plaintext);

	// blog post
	$blog_post = trim($status_area->find('li', 1)->find('span', 1)->plaintext);

	// club
	$club = trim($status_area->find('li', 1)->find('span', 1)->plaintext);

	// sns
	$sns = [];
	$sns_area = $left_area->find('.user-profile-sns', 0);
	foreach ($sns_area->find('a') as $each_sns) {
		if ($each_sns->class != 'di-ib mb8') {
			$sns[] = $each_sns->href;
		}
	}
	unset($sns_area);

	// about
	$about = $right_area->find('table tr td div[class=word-break]', 0);
	$about = $about ? trim($about->innertext) : '';

	// anime stats
	$anime_stat = [];
	$stat_area = $right_area->find('.user-statistics', 0);
	$a_stat_area = $stat_area->find('div[class="user-statistics-stats mt16"]', 0);
	$a_stat_score = $a_stat_area->find('.stat-score', 0);

	// days
	$days = $a_stat_score->find('div', 0);
	$temp_days = $days->find('span', 0)->plaintext;
	$days = str_replace($temp_days, '', $days->plaintext);
	$anime_stat['days'] = $days;

	// mean score
	$mean_score = $a_stat_score->find('div', 1);
	$temp_score = $mean_score->find('span', 0)->plaintext;
	$mean_score = str_replace($temp_score, '', $mean_score->plaintext);
	$anime_stat['mean_score'] = $mean_score;

	// anime status
	$temp_stat = [];
	$a_stat_status = $a_stat_area->find('ul[class=stats-status]', 0);
	$temp_stat['watching'] = trim($a_stat_status->find('span', 0)->plaintext);
	$temp_stat['completed'] = trim($a_stat_status->find('span', 1)->plaintext);
	$temp_stat['on_hold'] = trim($a_stat_status->find('span', 2)->plaintext);
	$temp_stat['dropped'] = trim($a_stat_status->find('span', 3)->plaintext);
	$temp_stat['plan_to_watch'] = trim($a_stat_status->find('span', 4)->plaintext);

	$a_stat_status = $a_stat_area->find('ul[class=stats-data]', 0);
	$temp_stat['total'] = str_replace(',', '', trim($a_stat_status->find('span', 1)->plaintext));
	$temp_stat['rewatched'] = str_replace(',', '', trim($a_stat_status->find('span', 3)->plaintext));
	$temp_stat['episode'] = str_replace(',', '', trim($a_stat_status->find('span', 5)->plaintext));

	$anime_stat['status'] = $temp_stat;

	// history
	$history = [];
	$a_history_area = $right_area->find('div[class="updates anime"]', 0);
	foreach ($a_history_area->find('.statistics-updates') as $each_history) {
		$temp_history = [];

		// image
		$image = $each_history->find('img', 0)->src;
		$temp_history['image'] = imageUrlCleaner($image);

		// id
		$history_data_area = $each_history->find('.data', 0);
		$id = $history_data_area->find('a', 0)->href;
		$id = explode('/', $id);
		$temp_history['id'] = $id[4];

		// title
		$title = $history_data_area->find('a', 0)->plaintext;
		$temp_history['title'] = $title;

		// date
		$date = $history_data_area->find('span', 0)->plaintext;
		$temp_history['date'] = trim($date);

		$history[] = $temp_history;
	}

	$anime_stat['history'] = $history;

	// manga stats
	$manga_stat = [];
	$m_stat_area = $stat_area->find('div[class="user-statistics-stats mt16"]', 1);
	$m_stat_score = $m_stat_area->find('.stat-score', 0);

	// days
	$days = $m_stat_score->find('div', 0);
	$temp_days = $days->find('span', 0)->plaintext;
	$days = str_replace($temp_days, '', $days->plaintext);
	$manga_stat['days'] = $days;

	// mean score
	$mean_score = $m_stat_score->find('div', 1);
	$temp_score = $mean_score->find('span', 0)->plaintext;
	$mean_score = str_replace($temp_score, '', $mean_score->plaintext);
	$manga_stat['mean_score'] = $mean_score;

	// manga status
	$temp_stat = [];
	$m_stat_status = $m_stat_area->find('ul[class=stats-status]', 0);
	$temp_stat['reading'] = trim($m_stat_status->find('span', 0)->plaintext);
	$temp_stat['completed'] = trim($m_stat_status->find('span', 1)->plaintext);
	$temp_stat['on_hold'] = trim($m_stat_status->find('span', 2)->plaintext);
	$temp_stat['dropped'] = trim($m_stat_status->find('span', 3)->plaintext);
	$temp_stat['plan_to_read'] = trim($m_stat_status->find('span', 4)->plaintext);

	$m_stat_status = $m_stat_area->find('ul[class=stats-data]', 0);
	$temp_stat['total'] = str_replace(',', '', trim($m_stat_status->find('span', 1)->plaintext));
	$temp_stat['reread'] = str_replace(',', '', trim($m_stat_status->find('span', 3)->plaintext));
	$temp_stat['chapter'] = str_replace(',', '', trim($m_stat_status->find('span', 5)->plaintext));
	$temp_stat['volume'] = str_replace(',', '', trim($m_stat_status->find('span', 7)->plaintext));

	$manga_stat['status'] = $temp_stat;

	// history
	$history = [];
	$m_history_area = $right_area->find('div[class="updates manga"]', 0);
	foreach ($m_history_area->find('.statistics-updates') as $each_history) {
		$temp_history = [];

		// image
		$image = $each_history->find('img', 0)->src;
		$temp_history['image'] = imageUrlCleaner($image);

		// id
		$history_data_area = $each_history->find('.data', 0);
		$id = $history_data_area->find('a', 0)->href;
		$id = explode('/', $id);
		$temp_history['id'] = $id[4];

		// title
		$title = $history_data_area->find('a', 0)->plaintext;
		$temp_history['title'] = $title;

		// date
		$date = $history_data_area->find('span', 0)->plaintext;
		$temp_history['date'] = trim($date);

		$history[] = $temp_history;
	}

	$manga_stat['history'] = $history;

	// favorite
	$favorite = [];
	$favorite_area = $right_area->find('.user-favorites-outer', 0);

	// favorite anime
	$favorite['anime'] = [];
	$anime_area = $favorite_area->find('ul[class="favorites-list anime"]', 0);
	if ($anime_area) {
		foreach ($anime_area->find('li') as $each_anime) {
			$temp_anime = [];

			// image
			$image = $each_anime->find('a', 0)->style;
			preg_match('/\'([^\'])*/', $image, $image);
			$image = substr($image[0], 1);
			$temp_anime['image'] = imageUrlCleaner($image);

			// id
			$id = $each_anime->find('a', 0)->href;
			$id = explode('/', $id);
			$temp_anime['id'] = $id[4];

			// title
			$title = $each_anime->find('a', 1)->plaintext;
			$temp_anime['title'] = $title;

			// type
			$temp_type = $each_anime->find('span', 0)->plaintext;
			$temp_type = explode('·', $temp_type);
			$temp_anime['type'] = trim($temp_type[0]);

			// year
			$temp_anime['year'] = trim($temp_type[1]);

			$favorite['anime'][] = $temp_anime;
		}
	}
	unset($anime_area);

	// favorite manga
	$favorite['manga'] = [];
	$manga_area = $favorite_area->find('ul[class="favorites-list manga"]', 0);
	if ($manga_area) {
		foreach ($manga_area->find('li') as $each_manga) {
			$temp_manga = [];

			// image
			$image = $each_manga->find('a', 0)->style;
			preg_match('/\'([^\'])*/', $image, $image);
			$image = substr($image[0], 1);
			$temp_manga['image'] = imageUrlCleaner($image);

			// id
			$id = $each_manga->find('a', 0)->href;
			$id = explode('/', $id);
			$temp_manga['id'] = $id[4];

			// title
			$title = $each_manga->find('a', 1)->plaintext;
			$temp_manga['title'] = $title;

			// type
			$temp_type = $each_manga->find('span', 0)->plaintext;
			$temp_type = explode('·', $temp_type);
			$temp_manga['type'] = trim($temp_type[0]);

			// year
			$temp_manga['year'] = trim($temp_type[1]);

			$favorite['manga'][] = $temp_manga;
		}
	}
	unset($manga_area);

	// favorite character
	$favorite['character'] = [];
	$char_area = $favorite_area->find('ul[class="favorites-list characters"]', 0);
	if ($char_area) {
		foreach ($char_area->find('li') as $each_char) {
			$temp_char = [];

			// image
			$image = $each_char->find('a', 0)->style;
			preg_match('/\'([^\'])*/', $image, $image);
			$image = substr($image[0], 1);
			$temp_char['image'] = imageUrlCleaner($image);

			// id
			$id = $each_char->find('a', 0)->href;
			$id = explode('/', $id);
			$temp_char['id'] = $id[4];

			// name
			$name = $each_char->find('a', 1)->plaintext;
			$temp_char['name'] = $name;

			// anime id
			$anime_id = $each_char->find('a', 2)->href;
			$anime_id = explode('/', $anime_id);
			$temp_char['anime_id'] = $anime_id[2];

			// anime title
			$anime_title = $each_char->find('a', 2)->plaintext;
			$temp_char['anime_title'] = trim($anime_title);

			$favorite['character'][] = $temp_char;
		}
	}
	unset($char_area);

	// favorite people
	$favorite['people'] = [];
	$people_area = $favorite_area->find('ul[class="favorites-list people"]', 0);
	if ($people_area) {
		foreach ($people_area->find('li') as $each_people) {
			$temp_people = [];

			// image
			$image = $each_people->find('a', 0)->style;
			preg_match('/\'([^\'])*/', $image, $image);
			$image = substr($image[0], 1);
			$temp_people['image'] = imageUrlCleaner($image);

			// id
			$id = $each_people->find('a', 0)->href;
			$id = explode('/', $id);
			$temp_people['id'] = $id[4];

			// name
			$name = $each_people->find('a', 1)->plaintext;
			$temp_people['name'] = $name;

			$favorite['people'][] = $temp_people;
		}
	}
	unset($people_area);

	$data = [
		'username' => $user,
		'image' => $profile_image,
		'last_online' => $last_online,
		'gender' => $gender,
		'birthday' => $birthday,
		'location' => $location,
		'joined_date' => $joined_date,
		'forum_post' => $forum_post,
		'review' => $review,
		'recommendation' => $recommendation,
		'blog_post' => $blog_post,
		'club' => $club,
		'sns' => $sns,
		'about' => $about,
		'anime_stat' => $anime_stat,
		'manga_stat' => $manga_stat,
		'favorite' => $favorite,
	];

	return $data;
}

function getUserFriend($user)
{
	$url = "https://myanimelist.net/profile/" . $user;

	$file_headers = @get_headers($url);
	if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$url = "https://myanimelist.net/profile/" . $user . "/friends";

	$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$friend = [];
	$friend_area = $html->find('.majorPad', 0);
	if ($friend_area) {
		foreach ($friend_area->find('.friendHolder') as $f) {
			$f_dump = [];
			$f = $f->find('.friendBlock', 0);

			// picture
			$f_dump['image'] = imageUrlCleaner($f->find('a img', 0)->src);

			// name
			$name_temp = $f->find('a',0)->href;
			$name_temp = explode('/', $name_temp);
			$f_dump['name'] = $name_temp[4];

			// last online
			$last_online = $f->find('strong', 0)->parent()->parent()->next_sibling();
			$f_dump['last_online'] = trim($last_online->plaintext);

			// friend since
			$friend_since = $last_online->next_sibling();
			$friend_since = str_replace('Friends since', '', $friend_since->plaintext);
			$f_dump['friend_since'] = trim($friend_since);

			$friend[] = $f_dump;
		}
	}

	$data = $friend;

	return $data;
}

function getUserHistory($user,$type=false)
{
	$url = "https://myanimelist.net/profile/" . $user;

	$file_headers = @get_headers($url);
	if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	$url = "https://myanimelist.net/history/" . $user;

	if ($type) {
		$url .= "/" . $type;
	}

	$html = HtmlDomParser::file_get_html($url)->find('#content', 0)->outertext;
	$html = str_replace('&quot;', '\"', $html);
	$html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
	$html = HtmlDomParser::str_get_html($html);

	$data = [];
	$history_area = $html->find('table', 0);
	if ($history_area) {
		foreach ($history_area->find('tr') as $history) {
			if ($history->find('td', 0)->class != 'borderClass') continue;
			$h_temp = [];

			$name_area = $history->find('td', 0);

			// id
			$temp_id = $name_area->find('a', 0)->href;
			$temp_id = explode('=', $temp_id);
			$h_temp['id'] = $temp_id[1];

			// title
			$h_temp['title'] = $name_area->find('a', 0)->plaintext;

			// type
			$type = $name_area->find('a', 0)->href;
			$type = explode('.php', $type);
			$h_temp['type'] = substr($type[0], 1);

			// number
			$progress = $name_area->find('strong', 0)->plaintext;
			$h_temp['progress'] = $progress;

			// date
			$date = $history->find('td', 1);
			$useless_date = $date->find('a', 0);
			$date = $date->plaintext;
			if ($useless_date) {
				$date = str_replace($useless_date, '', $date);
			}
			$h_temp['date'] = trim($date);

			$data[] = $h_temp;
		}
	}

	return $data;
}

function getUserList($user,$type='anime',$status=7)
{
	$url = "https://myanimelist.net/" . $type . "list/" . $user . "?status=" . $status;

	$file_headers = @get_headers($url);
	if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
	    return 404;
	}

	if(!$file_headers || $file_headers[0] == 'HTTP/1.1 403 Forbidden') {
	    return 403;
	}

	$data = [];
	$offset = 0;
	while (true) {
		$url = "https://myanimelist.net/" . $type . "list/" . $user . "/load.json?offset=" . $offset . "&status=" . $status;

		$content = json_decode(file_get_contents($url), true);

		if ($content) {
			for ($i = 0; $i < count($content); $i++) {
				if (!empty($content[$i]['anime_image_path'])) {
					$content[$i]['anime_image_path'] = imageUrlCleaner($content[$i]['anime_image_path']);
				} else {
					$content[$i]['manga_image_path'] = imageUrlCleaner($content[$i]['manga_image_path']);
				}
			}

			$data = array_merge($data, $content);

			$offset += 300;
		} else {
			break;
		}
	}

	return $data;
}