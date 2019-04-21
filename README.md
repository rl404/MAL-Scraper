<p align=center><img src="https://raw.githubusercontent.com/rl404/MyAnimeList/master/Images/malscraper-logo.png"><br>
<a href="https://php.net/"><img src="https://img.shields.io/badge/php-%3E%3D5.4-8892BF.svg"></a>
<a href="https://styleci.io/repos/146173202"><img src="https://styleci.io/repos/146173202/shield?branch=master&style=flat" alt="StyleCI Status"></a>
<a href="https://www.codefactor.io/repository/github/rl404/mal-scraper"><img src="https://www.codefactor.io/repository/github/rl404/mal-scraper/badge" alt="Code Factor"></a>
<a href="https://scrutinizer-ci.com/g/rl404/MAL-Scraper/?branch=master"><img src="https://scrutinizer-ci.com/g/rl404/MAL-Scraper/badges/quality-score.png?b=master" alt="Scrutinizer Score"></a>
<a href="https://packagist.org/packages/rl404/mal-scraper"><img src="https://poser.pugx.org/rl404/mal-scraper/v/stable" alt="Stable Version"></a>
<a href="https://packagist.org/packages/rl404/mal-scraper"><img src="https://poser.pugx.org/rl404/mal-scraper/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/rl404/mal-scraper"><img src="https://poser.pugx.org/rl404/mal-scraper/license" alt="License"></a></p>

_Mal-Scraper_ is an unofficial PHP API which scraps and parses page source of [MyAnimeList](https://myanimelist.net/).

Well, it is created to help people get MyAnimeList data without relying on MyAnimeList since they disabled/closed their API. It's working as long as the web is up and we can get its page source.

_Mal-Scraper_ is using [Sunra's](https://github.com/sunra/php-simple-html-dom-parser) HTML DOM parser and inspired by [Kylart's](https://github.com/Kylart/MalScraper) and [Jikan's](https://github.com/jikan-me/jikan) API.

For those who want the **REST API** one, please come [here](https://github.com/rl404/MAL-Scraper-API).

### Features
- Get general information of anime, manga, character (from anime and manga), people (voice actor, author, staff, etc), review or recommendation
- Get additional information of anime or manga video, episode, review, recommendation, character and staff, statistic and score, or picture
- Get list of anime or manga of selected studio/producer, magazine, or genre
- Get list of all anime or manga genre, anime studio/producer or manga magazine
- Get list of character and staff involved in an anime or manga
- Get list of anime, manga or best-voted review (pagination supported)
- Get list of anime or manga recommendation (pagination supported)
- Get result of searching, anime, manga, character, people or user (pagination supported)
- Get seasonal anime
- Get list of top anime from various categories (all, airing, upcoming, etc) (pagination supported)
- Get list of top manga from various categories (all, manga, novel, etc) (pagination supported)
- Get list of most favorited character and people (pagination supported)
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
- rl404/simple-php-cache 1.6.1+
- rl404/php-simple-html-dom-parser 1.5.3+

## Usage
```php
require "vendor/autoload.php";
use MalScraper\MalScraper;

// Default (no caching, return array)
$myMalScraper = new MalScraper();

// Caching feature
$myMalScraper = new MalScraper([
    'enable_cache' => true,     // enable caching
    'cache_time' => 3600,       // (optional) caching time in seconds (1 day as default)
    'cache_path' => '../Cache/' // (optional) caching path (/src/MalScraper/Cache/ as default)
]);

// Return http response
$myMalScraper = new MalScraper([
    'to_api' => true,         	// convert return to http response
]);
```

> If you set the `cache_path`, don't forget the last slash ('/') of the folder location.

### Example
```php
$cowboyBebopInfo = $myMalScraper->getInfo('anime', 1);
print_r($cowboyBebopInfo);
```
<details>
    <summary>Result</summary>
    <pre>
Array
(
    [id] => 1
    [cover] => https://cdn.myanimelist.net/images/anime/4/19644.jpg
    [title] => Cowboy Bebop
    [title2] => Array
        (
            [english] => Cowboy Bebop
            [synonym] =>
            [japanese] => カウボーイビバップ
        )
    [synopsis] => In the year 2071, humanity has colonized several of the planets and moons of the solar system leaving the now uninhabitable surface of planet Earth behind. The Inter Solar System Police attempts to keep peace in the galaxy, aided in part by outlaw bounty hunters, referred to as \"Cowboys.\" The ragtag team aboard the spaceship Bebop are two such individuals.
Mellow and carefree Spike Spiegel is balanced by his boisterous, pragmatic partner Jet Black as the pair makes a living chasing bounties and collecting rewards. Thrown off course by the addition of new members that they meet in their travels—Ein, a genetically engineered, highly intelligent Welsh Corgi; femme fatale Faye Valentine, an enigmatic trickster with memory loss; and the strange computer whiz kid Edward Wong—the crew embarks on thrilling adventures that unravel each member's dark and mysterious past little by little.
Well-balanced with high density action and light-hearted comedy, Cowboy Bebop is a space Western classic and an homage to the smooth and improvised music it is named after.
[Written by MAL Rewrite]
    [score] => 8.81
    [voter] => 397445
    [rank] => 27
    [popularity] => 39
    [members] => 777210
    [favorite] => 42552
    [type] => TV
    [episodes] => 26
    [status] => Finished Airing
    [aired] => Array
        (
            [start] => 1998-04-03
            [end] => 1999-04-24
        )
    [premiered] => Spring 1998
    [broadcast] => Saturdays at 01:00 (JST)
    [producers] => Array
        (
            [0] => Array
                (
                    [id] => 23
                    [name] => Bandai Visual
                )
        )
    [licensors] => Array
        (
            [0] => Array
                (
                    [id] => 102
                    [name] => Funimation
                )
            [1] => Array
                (
                    [id] => 233
                    [name] => Bandai Entertainment
                )
        )
    [studios] => Array
        (
            [0] => Array
                (
                    [id] => 14
                    [name] => Sunrise
                )
        )
    [source] => Original
    [genres] => Array
        (
            [0] => Array
                (
                    [id] => 1
                    [name] => Action
                )
            [1] => Array
                (
                    [id] => 2
                    [name] => Adventure
                )
            [2] => Array
                (
                    [id] => 4
                    [name] => Comedy
                )
            [3] => Array
                (
                    [id] => 8
                    [name] => Drama
                )
            [4] => Array
                (
                    [id] => 24
                    [name] => Sci-Fi
                )
            [5] => Array
                (
                    [id] => 29
                    [name] => Space
                )
        )
    [duration] => 24 min. per ep.
    [rating] => R - 17+ (violence & profanity)
    [related] => Array
        (
            [adaptation] => Array
                (
                    [0] => Array
                        (
                            [id] => 173
                            [title] => Cowboy Bebop
                            [type] => manga
                        )
                    [1] => Array
                        (
                            [id] => 174
                            [title] => Shooting Star Bebop: Cowboy Bebop
                            [type] => manga
                        )
                )
            [side story] => Array
                (
                    [0] => Array
                        (
                            [id] => 5
                            [title] => Cowboy Bebop: Tengoku no Tobira
                            [type] => anime
                        )
                    [1] => Array
                        (
                            [id] => 17205
                            [title] => Cowboy Bebop: Ein no Natsuyasumi
                            [type] => anime
                        )
                )
            [summary] => Array
                (
                    [0] => Array
                        (
                            [id] => 4037
                            [title] => Cowboy Bebop: Yose Atsume Blues
                            [type] => anime
                        )
                )
        )
    [character] => Array
        (
            [0] => Array
                (
                    [id] => 1
                    [name] => Spiegel, Spike
                    [role] => Main
                    [image] => https://cdn.myanimelist.net/images/characters/4/50197.jpg
                    [va_name] => Yamadera, Kouichi
                    [va_id] => 11
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/3/44674.jpg
                    [va_role] => Japanese
                )
            [1] => Array
                (
                    [id] => 16
                    [name] => Wong Hau Pepelu Tivrusky IV, Edward
                    [role] => Main
                    [image] => https://cdn.myanimelist.net/images/characters/16/30533.jpg
                    [va_name] => Tada, Aoi
                    [va_id] => 658
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/2/27665.jpg
                    [va_role] => Japanese
                )
            [2] => Array
                (
                    [id] => 2
                    [name] => Valentine, Faye
                    [role] => Main
                    [image] => https://cdn.myanimelist.net/images/characters/15/264961.jpg
                    [va_name] => Hayashibara, Megumi
                    [va_id] => 14
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/1/54011.jpg
                    [va_role] => Japanese
                )
            [3] => Array
                (
                    [id] => 3
                    [name] => Black, Jet
                    [role] => Main
                    [image] => https://cdn.myanimelist.net/images/characters/11/253723.jpg
                    [va_name] => Ishizuka, Unshou
                    [va_id] => 357
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/2/17135.jpg
                    [va_role] => Japanese
                )
            [4] => Array
                (
                    [id] => 4
                    [name] => Ein
                    [role] => Supporting
                    [image] => https://cdn.myanimelist.net/images/characters/5/30624.jpg
                    [va_name] => Yamadera, Kouichi
                    [va_id] => 11
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/3/44674.jpg
                    [va_role] => Japanese
                )
            [5] => Array
                (
                    [id] => 2734
                    [name] => Vicious
                    [role] => Supporting
                    [image] => https://cdn.myanimelist.net/images/characters/4/284773.jpg
                    [va_name] => Wakamoto, Norio
                    [va_id] => 84
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/3/46186.jpg
                    [va_role] => Japanese
                )
            [6] => Array
                (
                    [id] => 2736
                    [name] => Eckener, Grencia Mars Elijah Guo
                    [role] => Supporting
                    [image] => https://cdn.myanimelist.net/images/characters/13/213557.jpg
                    [va_name] => Horiuchi, Kenyuu
                    [va_id] => 262
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/2/49692.jpg
                    [va_role] => Japanese
                )
            [7] => Array
                (
                    [id] => 2735
                    [name] => Julia
                    [role] => Supporting
                    [image] => https://cdn.myanimelist.net/images/characters/9/52297.jpg
                    [va_name] => Takashima, Gara
                    [va_id] => 497
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/3/46185.jpg
                    [va_role] => Japanese
                )
            [8] => Array
                (
                    [id] => 23740
                    [name] => Von de Oniyate, Andy
                    [role] => Supporting
                    [image] => https://cdn.myanimelist.net/images/characters/3/213563.jpg
                    [va_name] => Ebara, Masashi
                    [va_id] => 179
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/3/49817.jpg
                    [va_role] => Japanese
                )
            [9] => Array
                (
                    [id] => 29313
                    [name] => Mad Pierrot
                    [role] => Supporting
                    [image] => https://cdn.myanimelist.net/images/characters/11/212087.jpg
                    [va_name] => Ginga, Banjou
                    [va_id] => 330
                    [va_image] => https://cdn.myanimelist.net/images/voiceactors/1/44678.jpg
                    [va_role] => Japanese
                )
        )
    [staff] => Array
        (
            [0] => Array
                (
                    [id] => 40009
                    [name] => Maseba, Yutaka
                    [role] => Producer
                    [image] => https://cdn.myanimelist.net/images/voiceactors/3/40216.jpg
                )
            [1] => Array
                (
                    [id] => 6519
                    [name] => Minami, Masahiko
                    [role] => Producer
                    [image] => https://cdn.myanimelist.net/images/voiceactors/2/39506.jpg
                )
            [2] => Array
                (
                    [id] => 2009
                    [name] => Watanabe, Shinichiro
                    [role] => Director, Script, Storyboard
                    [image] => https://cdn.myanimelist.net/images/voiceactors/1/54604.jpg
                )
            [3] => Array
                (
                    [id] => 20050
                    [name] => Kobayashi, Katsuyoshi
                    [role] => Sound Director
                    [image] =>
                )
        )
    [song] => Array
        (
            [opening] => Array
                (
                    [0] => \"Tank!\" by The Seatbelts (eps 1-25)
                )
            [closing] => Array
                (
                    [0] => \"The Real Folk Blues\" by The Seatbelts feat. Mai Yamane (eps 1-12, 14-25)
                    [1] => \"Space Lion\" by The Seatbelts (ep 13)
                    [2] => \"Blue\" by The Seatbelts feat. Mai Yamane (ep 26)
                )
        )
)
    </pre>
</details>

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
