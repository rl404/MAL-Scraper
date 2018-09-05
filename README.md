# MAL-Scraper

> Scrap everything from MyAnimeList website

_Mal-Scraper_ is an unofficial PHP API which scraps and parses page source of [MyAnimeList](https://myanimelist.net/).

Well, it is created to help people get MyAnimeList data without relying on MyAnimeList since they disabled/closed their API. It's working as long as the web is up and we can get its page source.

_Mal-Scraper_ is using [Sunra's](https://github.com/sunra/php-simple-html-dom-parser) HTML DOM parser and inspired by [Kylart's](https://github.com/Kylart/MalScraper) and  [Jikan's](https://github.com/jikan-me/jikan) API.

### Features
- Get information of
    - Anime
    - Manga
    - Charater (from anime and manga)
    - People (voice actor, author, staff, etc)
- Get list of anime or manga of selected
    - Studio/producer
    - Magazine
    - Genre
- Get list of all
    - Anime genre
    - Manga genre
    - Anime studio/producer
    - Manga magazine
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
1. `composer require rl404/mal-scraper 1.0.0`
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

For more information, please go to the [wiki](https://github.com/rl404/MAL-Scraper/wiki)
