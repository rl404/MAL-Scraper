<?php

require_once '../vendor/autoload.php'; // Autoload files using Composer autoload

header('Content-Type: application/json');

use MalScraper\MalScraper;

$myscraper = new MalScraper();

// get anime info
// echo $myscraper->getInfo('anime', 28221);
// echo $myscraper->getInfo('anime', 38150);

// get manga info
// echo $myscraper->getInfo('manga', 12);
// echo $myscraper->getInfo('manga', 114470);

// get character
// echo $myscraper->getCharacter(12);
// echo $myscraper->getCharacter(163216);

// get people
// echo $myscraper->getPeople(2009);
// echo $myscraper->getPeople(37418);

// get list of all character + staff
// echo $myscraper->getCharacterStaff('anime', 1);

// get detail stat
// echo $myscraper->getStat('manga', 3850);

// get additional pic
// echo $myscraper->getPicture('manga', 3850);

// get additional char pic
// echo $myscraper->getCharacterPicture(3850);

// get additional people pic
// echo $myscraper->getPeoplePicture(1);

// get what studio/producer produced
// echo $myscraper->getStudioProducer(1);

// get what magazine produced
// echo $myscraper->getMagazine(1);

// get which anime/manga has this genre
// echo $myscraper->getGenre('manga', 1);

// get all anime genre
// echo $myscraper->getAllAnimeGenre();

// get all manga genre
// echo $myscraper->getAllMangaGenre();

// get all studio/producer
// echo $myscraper->getAllStudioProducer();

// get all magazine
// echo $myscraper->getAllMagazine();

// search anime
// echo $myscraper->searchAnime('etotama');

// search manga
// echo $myscraper->searchManga('non');

// search character
// echo $myscraper->searchCharacter('ohu');

// search people
// echo $myscraper->searchPeople('mas');

// get seasonal anime
// echo $myscraper->getSeason(2019,'winter');

// get top anime
// echo $myscraper->getTopAnime(2);

// get top manga
echo $myscraper->getTopManga();