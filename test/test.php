<?php

require_once '../vendor/autoload.php'; // Autoload files using Composer autoload

header('Content-Type: application/json');

use MalScraper\MalScraper;

ini_set('max_execution_time', 0);

$myscraper = new MalScraper([
	'enable_cache' => true,
	'cache_time' => 3600,
	'to_api' => true
]);

// get user info
// $result = $myscraper->getUser('rl404');
// $result = $myscraper->getUser('MozillaFennekin');
// $result = $myscraper->getUser('404');

// get user friend
$result = $myscraper->getUserFriend('rl404');

// get user history
// $result = $myscraper->getUserHistory('rl404');
// $result = $myscraper->getUserHistory('rl404','manga');

// get user list
// $result = $myscraper->getUserList('rl404');

// get anime info
// $result = $myscraper->getInfo('anime', 28221);
// $result = $myscraper->getInfo('anime', 38150

// get manga info
// $result = $myscraper->getInfo('manga', 66499);
// $result = $myscraper->getInfo('manga', 114470);

// get character
// $result = $myscraper->getCharacter(12);
// $result = $myscraper->getCharacter(163216);

// get people
// $result = $myscraper->getPeople(2009);
// $result = $myscraper->getPeople(37418);

// get list of all character + staff
// $result = $myscraper->getCharacterStaff('anime', 1);

// get detail stat
// $result = $myscraper->getStat('manga', 3850);

// get additional pic
// $result = $myscraper->getPicture('manga', 3850);

// get additional char pic
// $result = $myscraper->getCharacterPicture(3850);

// get additional people pic
// $result = $myscraper->getPeoplePicture(1);

// get what studio/producer produced
// $result = $myscraper->getStudioProducer(1);

// get what magazine produced
// $result = $myscraper->getMagazine(1);

// get which anime/manga has this genre
// $result = $myscraper->getGenre('manga', 1);

// get all anime genre
// $result = $myscraper->getAllAnimeGenre();

// get all manga genre
// $result = $myscraper->getAllMangaGenre();

// get all studio/producer
// $result = $myscraper->getAllStudioProducer();

// get all magazine
// $result = $myscraper->getAllMagazine();

// search anime
// $result = $myscraper->searchAnime('etotama');

// search manga
// $result = $myscraper->searchManga('non');

// search character
// $result = $myscraper->searchCharacter('ohu');

// search people
// $result = $myscraper->searchPeople('mas');

// get seasonal anime
// $result = $myscraper->getSeason(2019,'winter');

// get top anime
// $result = $myscraper->getTopAnime(2);

// get top manga
// $result = $myscraper->getTopManga();

print_r($result);