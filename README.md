# MAL-Scraper

> Scrap everything from MyAnimeList website

_Mal-Scraper_ is an unofficial PHP API which scraps and parses page source of [MyAnimeList](https://myanimelist.net/).

Well, it is created to help people get MyAnimeList data without relying on MyAnimeList since they disabled/closed their API. It's working as long as the web is up and we can get its page source.

_Mal-Scraper_ is using [Sunra's](https://github.com/sunra/php-simple-html-dom-parser) HTML DOM parser and inspired by [Kylart's](https://github.com/Kylart/MalScraper) and  [Jikan's](https://github.com/jikan-me/jikan) API.

### Features
- Get general information of anime, manga, charater (from anime and manga), or people (voice actor, author, staff, etc)
- Get additional information of anime or manga character and staff, statistic and score, or picture
- Get list of anime or manga of selected studio/producer, magazine, or genre
- Get list of all anime or manga genre, anime studio/producer or manga magazine
- Get list of character and staff involved in an anime or manga
- Get result of searching, anime, manga, character, or people (pagination supported)
- Get seasonal anime
- Get list of top anime from various categories (all, airing, upcoming, etc) (pagination supported)
- Get list of top manga from various categories (all, manga, novel, etc) (pagination supported)
- Get information of user profile, friends, history, and anime/manga list
- Caching (using [Simple-PHP-Cache library](https://github.com/cosenary/Simple-PHP-Cache))
- Convertable return to http response (for API)

## Installation
1. `composer require rl404/mal-scraper 1.2.0`
2. That's it.

#### Dependencies
- PHP 5.3+
- [HTML DOM Parser](https://github.com/sunra/php-simple-html-dom-parser)

 > If you are using PHP 7.1+, please fix the DOM parser in `vendor\sunra\php-simple-html-dom-parser\Src\Sunra\PhpSimple\simplehtmldom_1_5\simple_html_dom.php` like [this](https://github.com/sunra/php-simple-html-dom-parser/issues/59)

## Usage
```php
require "vendor/autoload.php";
use MalScraper\MalScraper;

// Default (no caching, return array)
$myMalScraper = new MalScraper();

// Caching feature
$myMalScraper = new MalScraper([
    'enable_cache' => true,     // enable caching
    'cache_time' => 3600        // (optional) caching time in seconds (1 day as default)
]);

// Return http response json
$myMalScraper = new MalScraper([
    'to_api' => true,         	// convert return to http response
]);
```
For more usage and methods, please go to the [wiki](https://github.com/rl404/MAL-Scraper/wiki)

## Contributing
1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request.

## License
MIT License

Copyright (c) rl404