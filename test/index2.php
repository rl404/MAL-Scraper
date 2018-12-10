<?php

require_once '../vendor/autoload.php';

header('Content-Type: application/json');

use MalScraper\MalScraper2;

ini_set('max_execution_time', 0);

//

$myscraper = new MalScraper2([
	// 'enable_cache' => true,
	// 'cache_time' => 2,
	// 'to_api' => true
]);

// $result = $myscraper->getInfo('anime', 37349);
// $result = $myscraper->getCharacter(62);
// $result = $myscraper->getPeople(1868);
// $result = $myscraper->getStudioProducer(1);
// $result = $myscraper->getMagazine(1);
// $result = $myscraper->getGenre('manga', 1, 2);

// $result = $myscraper->getCharacterStaff('anime',1);
// $result = $myscraper->getStat('manga',1);
// $result = $myscraper->getPicture('anime',1);
// $result = $myscraper->getCharacterPicture(1);
// $result = $myscraper->getPeoplePicture(1);

// $result = $myscraper->getAllAnimeGenre();
// $result = $myscraper->getAllMangaGenre();
// $result = $myscraper->getAllStudioProducer();
// $result = $myscraper->getAllMagazine();

// $result = $myscraper->searchAnime('naruto', 2);
// $result = $myscraper->searchManga('naruto', 2);
// $result = $myscraper->searchCharacter('naruto');
// $result = $myscraper->searchPeople('masashi', 2);
// $result = $myscraper->searchUser('rl404');

// $result = $myscraper->getSeason(2017,'summer');

// $result = $myscraper->getTopAnime(2,2);
$result = $myscraper->getTopManga();

print_r(memory_get_usage()."\n");
print_r($result);