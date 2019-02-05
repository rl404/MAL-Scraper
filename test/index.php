<?php

require_once '../vendor/autoload.php';

header('Content-Type: application/json');

use MalScraper\MalScraper;

ini_set('max_execution_time', 0);

//

$myscraper = new MalScraper([
    'enable_cache' => true,
    'cache_path'   => '../cache/',
    'cache_time'   => 2,
    // 'to_api' => true
]);

$result = $myscraper->getInfo('anime', 1);
// $result = $myscraper->getCharacter(20000);
// $result = $myscraper->getPeople(185);
// $result = $myscraper->getStudioProducer(1);
// $result = $myscraper->getMagazine(1);
// $result = $myscraper->getGenre('manga', 1, 2);
// $result = $myscraper->getReview(100141);
// $result = $myscraper->getRecommendation('anime', 31859, 31859);

// $result = $myscraper->getCharacterStaff('manga',21479);
// $result = $myscraper->getStat('manga', 2);
// $result = $myscraper->getPicture('manga',1);
// $result = $myscraper->getCharacterPicture(1);
// $result = $myscraper->getPeoplePicture(1);
// $result = $myscraper->getVideo(34566, 2);
// $result = $myscraper->getEpisode(1735, 2);
// $result = $myscraper->getAnimeReview(37430);
// $result = $myscraper->getMangaReview(21,2);
// $result = $myscraper->getAnimeRecommendation(1);
// $result = $myscraper->getMangaRecommendation(87609);

// $result = $myscraper->getAllAnimeGenre();
// $result = $myscraper->getAllMangaGenre();
// $result = $myscraper->getAllStudioProducer();
// $result = $myscraper->getAllMagazine();
// $result = $myscraper->getAllReview('anime');
// $result = $myscraper->getAllRecommendation('anime');

// $result = $myscraper->searchAnime('naruto', 2);
// $result = $myscraper->searchManga('naruto', 2);
// $result = $myscraper->searchCharacter('naruto');
// $result = $myscraper->searchPeople('masashi', 2);
// $result = $myscraper->searchUser('rl404');

// $result = $myscraper->getSeason(2017, 'winter');

// $result = $myscraper->getTopAnime(1);
// $result = $myscraper->getTopManga();
// $result = $myscraper->getTopCharacter();
// $result = $myscraper->getTopPeople();

// $result = $myscraper->getUser('rl404');
// $result = $myscraper->getUserFriend('rl404');
// $result = $myscraper->getUserHistory('rl404', 'manga');
// $result = $myscraper->getUserList('rl404', 'anime', 4);
// $result = $myscraper->getUserCover('rl404', 'manga');

print_r($result);
