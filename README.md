<p align=center><img src="https://raw.githubusercontent.com/rl404/MyAnimeList/master/Images/malscraper-logo.png"><br>
<a href="https://php.net/"><img src="https://img.shields.io/badge/php-%3E%3D5.4-8892BF.svg"></a>
<a href="https://styleci.io/repos/146173202"><img src="https://styleci.io/repos/146173202/shield?branch=master&style=flat" alt="StyleCI Status"></a>
<a href="https://www.codacy.com/app/rl404/MAL-Scraper?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=rl404/MAL-Scraper&amp;utm_campaign=Badge_Grade"><img src="https://api.codacy.com/project/badge/Grade/b91bdd9108c14b7bb434337d16bfde9b" alt="Codacy Status"></a>
<a href="https://packagist.org/packages/rl404/mal-scraper"><img src="https://poser.pugx.org/rl404/mal-scraper/v/stable" alt="Stable Version"></a>
<a href="https://packagist.org/packages/rl404/mal-scraper"><img src="https://poser.pugx.org/rl404/mal-scraper/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/rl404/mal-scraper"><img src="https://poser.pugx.org/rl404/mal-scraper/license" alt="License"></a></p>

_Mal-Scraper_ is an unofficial PHP API which scraps and parses page source of [MyAnimeList](https://myanimelist.net/).

Well, it is created to help people get MyAnimeList data without relying on MyAnimeList since they disabled/closed their API. It's working as long as the web is up and we can get its page source.

_Mal-Scraper_ is using [Sunra's](https://github.com/sunra/php-simple-html-dom-parser) HTML DOM parser and inspired by [Kylart's](https://github.com/Kylart/MalScraper) and [Jikan's](https://github.com/jikan-me/jikan) API.

### Features
- Get general information of anime, manga, charater (from anime and manga), or people (voice actor, author, staff, etc)
- Get additional information of anime or manga character and staff, statistic and score, or picture
- Get list of anime or manga of selected studio/producer, magazine, or genre
- Get list of all anime or manga genre, anime studio/producer or manga magazine
- Get list of character and staff involved in an anime or manga
- Get result of searching, anime, manga, character, people or user (pagination supported)
- Get seasonal anime
- Get list of top anime from various categories (all, airing, upcoming, etc) (pagination supported)
- Get list of top manga from various categories (all, manga, novel, etc) (pagination supported)
- Get information of user profile, friends, history, and anime/manga list
- Caching (using [Simple-PHP-Cache library](https://github.com/cosenary/Simple-PHP-Cache))
- Convertable return to http response (for API)
- (Bonus) Get all anime/manga cover from user list

_More will be coming soon..._

## Installation
1. `composer require rl404/mal-scraper`
2. That's it.

#### Dependencies
- PHP 5.4+
- rl404/simple-php-cache 1.6.1
- rl404/php-simple-html-dom-parser 1.5.3

## Usage
```php
require "vendor/autoload.php";
use MalScraper\MalScraper;

// Default (no caching, return json)
$myMalScraper = new MalScraper();

// Caching feature
$myMalScraper = new MalScraper([
    'enable_cache' => true,     // enable caching
    'cache_time' => 3600        // (optional) caching time in seconds (1 day as default)
]);

// Return http response
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

## Disclamer
All data (including anime, manga, people, etc) and MyAnimeList logos belong to their respective copyrights owners. Mal-Scraper does not have any affliation with content providers.

## License
MIT License

Copyright (c) rl404
