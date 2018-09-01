# MAL-Scraper

> Scrap everything from MyAnimeList website

_Mal-Scraper_ is an unofficial PHP API which scrap and parse page source of [MyAnimeList](https://myanimelist.net/). 

Well, it is created to help people get MyAnimeList data without relying on MyAnimeList since they disabled/closed their API. It's working as long as the web is up and we can get its page source.

_Mal-Scraper_ is using [Sunra's](https://github.com/sunra/php-simple-html-dom-parser) HTML DOM parser and inspired by [Kylart's](https://github.com/Kylart/MalScraper) and  [Jikan's](https://github.com/jikan-me/jikan) API.

#### Table of Content
- [Features](#features)
- [Installation](#installation)
- [Use](#use)
- [Method](#method)
- [Data Model](#data-model)

### Features
- Get information of
    - Anime
    - Manga
    - Charater (from anime and manga)
    - People (voice actor, author, staff, etc)
- Get list of character and staff involved in an anime or manga
- Get result of searching (pagination supported)
    - Anime
    - Manga
    - Character
    - People
- Get seasonal anime
- Get list of top anime (pagination supported)
    - All anime
    - Airing
    - Upcoming
    - TV
    - Movie
    - OVA
    - Special
    - By popularity
    - By favorite
- Get list of top manga (pagination supported)
    - All (all type of book)
    - Manga
    - Novel
    - One-shot
    - Doujin
    - Manhwa
    - Manhua
    - By popularity
    - By favorite

## Installation
1. `composer require rl404/mal-scraper @dev`
2. That's it.
#### Dependencies
- PHP 5.3+
- [HTML DOM Parser](https://github.com/sunra/php-simple-html-dom-parser)

 > If you are using PHP 7.1+, please fix the DOM parser in `vendor\sunra\php-simple-html-dom-parser\Src\Sunra\PhpSimple\simplehtmldom_1_5\simple_html_dom.php` like [this](https://github.com/sunra/php-simple-html-dom-parser/issues/59)

## Use
```php
require "vendor/autoload.php";
use MalScraper\MalScraper;

$myMalScraper = new MalScraper();
```

## Method
### getInfo()
Getting anime or manga information from page like [this](https://myanimelist.net/anime/1).

Parameter | Type | Description
--- | --- | ---
type | string | Either `anime` or `manga`
id | integer | ID of the anime or manga

Usage example:
```php
$myScraper = new MalScraper();

$myAnimeInfo = $myScraper::getInfo('anime', 1);
// or
$myAnimeInfo = $myScraper->getInfo('anime', 1);
```
Return: JSON of a [response](response-data) with [anime data model](#anime-data-model) as data.

### getCharacter()
Getting character information from page like [this](https://myanimelist.net/character/1).

Parameter | Type | Description
--- | --- | ---
id | integer | ID of the character

Usage example:
```php
$myScraper = new MalScraper();

$myCharacterInfo = $myScraper::getCharacter(1);
// or
$myCharacterInfo = $myScraper->getCharacter(1);
```
Return: JSON of a [response](response-data) with [character data model](#character-data-model) as data.

### getPeople()
Getting people information from page like [this](https://myanimelist.net/people/1).

Parameter | Type | Description
--- | --- | ---
id | integer | ID of the people

Usage example:
```php
$myScraper = new MalScraper();

$myPeopleInfo = $myScraper::getPeople(1);
// or
$myPeopleInfo = $myScraper->getPeople(1);
```
Return: JSON of a [response](response-data) with [people data model](#people-data-model) as data.

### getCharacterStaff()
Getting list of characters and people involved in the anime or manga from page like [this](https://myanimelist.net/anime/1/Cowboy_Bebop/characters).

Parameter | Type | Description
--- | --- | ---
type | string | Either `anime` or `manga`
id | integer | ID of the anime or manga

Usage example:
```php
$myScraper = new MalScraper();

$myInfo = $myScraper::getCharacterStaff('anime', 1);
// or
$myInfo = $myScraper->getCharacterStaff('anime', 1);
```
Return: JSON of a [response](response-data) with [character and staff data model](#character-and-staff-data-model) as data.

### searchAnime()
Getting list of result searching anime title from page like [this](https://myanimelist.net/anime.php?q=clannad).

Parameter | Type | Description
--- | --- | ---
q | string | Query of your search
page | integer | (Optional) Page number of the result (1 as default)

Usage example:
```php
$myScraper = new MalScraper();

$myResult = $myScraper::searchAnime('clannad');
// or
$myResult = $myScraper->searchAnime('clannad');
```
Return: JSON of a [response](response-data) with [search anime data model](#search-anime-data-model) as data.

### searchManga()
Getting list of result searching manga title from page like [this](https://myanimelist.net/manga.php?q=clannad).

Parameter | Type | Description
--- | --- | ---
q | string | Query of your search
page | integer | (Optional) Page number of the result (1 as default)

Usage example:
```php
$myScraper = new MalScraper();

$myResult = $myScraper::searchManga('clannad');
// or
$myResult = $myScraper->searchManga('clannad');
```
Return: JSON of a [response](response-data) with [search manga data model](#search-manga-data-model) as data.

### searchCharacter()
Getting list of result searching character name from page like [this](https://myanimelist.net/character.php?q=shinobu).

Parameter | Type | Description
--- | --- | ---
q | string | Query of your search
page | integer | (Optional) Page number of the result (1 as default)

Usage example:
```php
$myScraper = new MalScraper();

$myResult = $myScraper::searchCharacter('shinobu');
// or
$myResult = $myScraper->searchCharacter('shinobu');
```
Return: JSON of a [response](response-data) with [search character data model](#search-character-data-model) as data.

### searchPeople()
Getting list of result searching people name from page like [this](https://myanimelist.net/people.php?q=hanazawa).

Parameter | Type | Description
--- | --- | ---
q | string | Query of your search
page | integer | (Optional) Page number of the result (1 as default)

Usage example:
```php
$myScraper = new MalScraper();

$myResult = $myScraper::searchPeople('hanazawa');
// or
$myResult = $myScraper->searchPeople('hanazawa');
```
Return: JSON of a [response](response-data) with [search people data model](#search-people-data-model) as data.

### getSeason()
Getting list of seasonal anime from page like [this](https://myanimelist.net/anime/season/2018/spring).

Parameter | Type | Description
--- | --- | ---
year | integer | The year
season | string | The season (`summer`, `spring`, `winter`, or `fall`)

Usage example:
```php
$myScraper = new MalScraper();

$mySeason = $myScraper::getSeason(2018, 'spring');
// or
$mySeason = $myScraper->getSeason(2018, 'spring');
```
Return: JSON of a [response](response-data) with [seasonal anime data model](#seasonal-anime-data-model) as data.

### getTopAnime()
Getting list of top anime from page like [this](https://myanimelist.net/topanime.php).

Parameter | Type | Description
--- | --- | ---
type | integer | (Optional) Category of top anime
page | integer | (Optional) Page number of the result (1 as default)

Top Anime Categories:

Category | Description
:---: | ---
0 | All anime
1 | Airing
2 | Upcoming
3 | TV
4 | Movie
5 | OVA
6 | Special
7 | By popularity
8 | By favorite

Usage example:
```php
$myScraper = new MalScraper();

$myTopAnime = $myScraper::getTopAnime();
// or
$myTopAnime = $myScraper->getTopAnime();
```
Return: JSON of a [response](response-data) with [top anime data model](#top-anime-data-model) as data.

### getTopManga()
Getting list of top manga from page like [this](https://myanimelist.net/topmanga.php).

Parameter | Type | Description
--- | --- | ---
type | integer | (Optional) Category of top manga
page | integer | (Optional) Page number of the result (1 as default)

Top Manga Categories:

Category | Description
:---: | ---
0 | All
1 | Manga
2 | Novels
3 | Oneshots
4 | Doujin
5 | Manhwa
6 | Manhua
7 | By popularity
8 | By favorite

Usage example:
```php
$myScraper = new MalScraper();

$myTopManga = $myScraper::getTopManga();
// or
$myTopManga = $myScraper->getTopManga();
```
Return: JSON of a [response](response-data) with [top manga data model](#top-manga-data-model) as data.