## Changelog

### v1.5.3 - Apr 21, 20199
- `Update` PHP DOM parser version

### v1.5.2 - Feb 05, 2019
- `Add` Feature: Configurable cache path

### v1.5.1 - Jan 18, 2019
- `Add` Feature: Get anime/manga recommendation
- `Add` Feature: Additional anime/manga recommendation
- `Add` Feature: Get all anime/manga recommendation list
- `Fix` Missing recommendation in `getInfo()`

### v1.5.0 - Jan 15, 2019
- `Add` Feature: Get anime/manga review
- `Add` Feature: Additional anime/manga review
- `Add` Feature: Get all anime/manga/best-voted review list
- `Fix` Missing review in `getInfo()`
- `Update` Use direct url for
    - `getCharacterStaff()`
    - `getStat()`
    - `getPicture()`
    - `getCharacterPicture()`
    - `getPeoplePicture()`
    - `getVideo()`
    - `getEpisode()`

### v1.4.2 - Jan 10, 2019
- `Add` Feature: Video list of anime
- `Add` Feature: Episode list of anime
- `Fix` Missing promotional video in `getInfo()`

### v1.4.1 - Jan 8, 2019
- `Add` Feature: Character most favorited list
- `Add` Feature: People most favorited list

### v1.4.0 - Dec 15, 2018
- `Add` Feature: `media_id`, `media_title`, and `media_type` in character favorite list of `getUser()`
- `Fix` Wrong user `blog_post` and `club` count in `getUser()`
- `Update` Refactor all method (now use 500kb less memory allocation)

### v1.3.4 - Nov 9, 2018
- `Update` Remove Cache and HTMLDomParser class

### v1.3.3 - Nov 8, 2018
- `Fix` Missing friend count in `getUser()`
- `Fix` Missing user update in `getStat()`
- `Fix` Summary array in `getStat()`
- `Update` Localize HtmlDomParser class

### v1.3.2 - Oct 27, 2018
- `Add` New library logo
- `Update` Improve Readme file
- `Update` Increase minimum PHP version from 5.3 to 5.4

### v1.3.1 - Oct 26, 2018
- `Fix` Missing friend in user profile
- `Fix` Missing progress history update in user profile

### v1.3.0 - Oct 20, 2018
- `Add` Feature: Search user
- `Add` Feature: Get all anime/manga cover in user list

### v1.2.0 - Oct 10, 2018
- `Add` Feature: Get user friend list
- `Add` Feature: Get user update history
- `Add` Feature: Get user anime or manga list
- `Add` Feature: Convert return to http response with header
- `Fix` Mal-scraper now return array (how library should normally be)

### v1.1.0 - Sep 12, 2018
- `Add` Feature: Cache
- `Add` Feature: Caching time
- `Add` Feature: Array as return data
- `Add` Feature: Array (data only) as return data
- `Add` Feature: Get user profile information
- `Add` License file
- `Add` A bit documentation in `MalScraper.php`
- `Update` Readme file

### v1.0.0 - Sep 6, 2018
- **Initial Release**