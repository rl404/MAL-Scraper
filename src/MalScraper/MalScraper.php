<?php
/**
 * rl404 - MalScraper.
 *
 * Unofficial PHP API which scraps and parses page source of MyAnimeList.
 * API Documentation: https://github.com/rl404/MAL-Scraper
 *
 * @author Axel Oktavian Antonio
 *
 * @since 26-09-2018
 *
 * @version 1.5.3
 *
 * @license MIT https://opensource.org/licenses/MIT
 */

namespace MalScraper;

use Cache;
use MalScraper\Helper\Helper;
use MalScraper\Model\Additional\AnimeMangaRecommendationModel as AnimeMangaRecommendation;
use MalScraper\Model\Additional\AnimeMangaReviewModel as AnimeMangaReview;
use MalScraper\Model\Additional\CharacterPeoplePictureModel as CharacterPeoplePicture;
use MalScraper\Model\Additional\CharacterStaffModel as CharacterStaff;
use MalScraper\Model\Additional\EpisodeModel as Episode;
use MalScraper\Model\Additional\PictureModel as Picture;
use MalScraper\Model\Additional\StatModel as Stat;
use MalScraper\Model\Additional\VideoModel as Video;
use MalScraper\Model\General\CharacterModel as Character;
use MalScraper\Model\General\InfoModel as Info;
use MalScraper\Model\General\PeopleModel as People;
use MalScraper\Model\General\ProducerModel as Producer;
use MalScraper\Model\General\RecommendationModel as Recommendation;
use MalScraper\Model\General\ReviewModel as Review;
use MalScraper\Model\Lists\AllGenreModel as AllGenre;
use MalScraper\Model\Lists\AllProducerModel as AllProducer;
use MalScraper\Model\Lists\AllRecommendationModel as AllRecommendation;
use MalScraper\Model\Lists\AllReviewModel as AllReview;
use MalScraper\Model\Search\SearchAnimeMangaModel as SearchAnimeManga;
use MalScraper\Model\Search\SearchCharacterPeopleModel as SearchCharacterPeople;
use MalScraper\Model\Search\SearchUserModel as SearchUser;
use MalScraper\Model\Seasonal\SeasonModel as Season;
use MalScraper\Model\Top\TopCharacterModel as TopCharacter;
use MalScraper\Model\Top\TopModel as Top;
use MalScraper\Model\Top\TopPeopleModel as TopPeople;
use MalScraper\Model\User\FriendModel as Friend;
use MalScraper\Model\User\HistoryModel as History;
use MalScraper\Model\User\UserCoverModel as UserCover;
use MalScraper\Model\User\UserListModel as UserList;
use MalScraper\Model\User\UserModel as User;

/**
 * Class MalScraper.
 */
class MalScraper
{
    /**
     * Cache class.
     *
     * @var Cache
     */
    private $_cache;

    /**
     * Cache feature.
     *
     * @var bool
     */
    private $_enable_cache = false;

    /**
     * Cache expiration time.
     *
     * @var int
     */
    private $_cache_time = 86400;

    /**
     * Cache path.
     *
     * @var string
     */
    private $_cache_path = __DIR__.'/Cache/';

    /**
     * Convert to http response.
     *
     * @var bool
     */
    private $_to_api = false;

    /**
     * Default constructor.
     *
     * @param array [optional] $config
     *
     * @return void
     */
    public function __construct($config = false)
    {
        if (!empty($config['enable_cache']) && $config['enable_cache'] === true) {
            // enable cache function
            $this->_enable_cache = $config['enable_cache'];

            // create cache class
            $this->_cache = new Cache();

            // set cache path
            if (!empty($config['cache_path'])) {
                $this->_cache_path = $config['cache_path'];
            }
            $this->_cache->setCachePath($this->_cache_path);

            // set cache time
            if (!empty($config['cache_time'])) {
                $this->_cache_time = $config['cache_time'];
            }
        }

        // to http response function
        if (!empty($config['to_api']) && $config['to_api'] === true) {
            $this->_to_api = $config['to_api'];
        }
    }

    /**
     * Default call.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return string|array
     */
    public function __call($method, $arguments)
    {
        $result = '';

        // if cache function enabled
        if ($this->_enable_cache === true) {
            $this->_cache->setCache(str_replace('get', '', $method));
            $this->_cache->eraseExpired($this->_cache_time);

            $cacheName = $method.'('.implode(',', $arguments).')';
            $isCached = $this->_cache->isCached($cacheName);

            // if cached
            if ($isCached) {
                $result = $this->_cache->retrieve($cacheName);
            } else {
                $data = call_user_func_array([$this, $method], $arguments);
                $this->_cache->store($cacheName, $data, $this->_cache_time);
                $result = $data;
            }
        } else {
            $result = call_user_func_array([$this, $method], $arguments);
        }

        // if to api function enabled
        if ($this->_to_api === true) {
            return Helper::response($result);
        }

        return Helper::toResponse($result);
    }

    /**
     * Get anime/manga information.
     *
     * @param string     $type anime or manga
     * @param int|string $id   id of the anime or manga
     *
     * @return array
     */
    private function getInfo($type, $id)
    {
        return (new Info($type, $id))->getAllInfo();
    }

    /**
     * Get character information.
     *
     * @param int|string $id id of the character
     *
     * @return array
     */
    private function getCharacter($id)
    {
        return (new Character($id))->getAllInfo();
    }

    /**
     * Get people information.
     *
     * @param int|string $id id of the people
     *
     * @return array
     */
    private function getPeople($id)
    {
        return (new People($id))->getAllInfo();
    }

    /**
     * Get review information.
     *
     * @param int|string $id id of the review
     *
     * @return array
     */
    private function getReview($id)
    {
        return (new Review($id))->getAllInfo();
    }

    /**
     * Get recommendation information.
     *
     * @param string     $type Either anime or manga
     * @param int|string $id   id of the first anime/manga
     * @param int|string $id   id of the second anime/manga
     *
     * @return array
     */
    private function getRecommendation($type, $id1, $id2)
    {
        return (new Recommendation($type, $id1, $id2))->getAllInfo();
    }

    /**
     * Get anime/manga character + staff complete list.
     *
     * @param string     $type Either anime or manga
     * @param int|string $id   id of the anime or manga
     *
     * @return array
     */
    private function getCharacterStaff($type, $id)
    {
        return (new CharacterStaff($type, $id))->getAllInfo();
    }

    /**
     * Get anime/manga detail stat.
     *
     * @param string     $type Either anime or manga
     * @param int|string $id   id of the anime or manga
     *
     * @return array
     */
    private function getStat($type, $id)
    {
        return (new Stat($type, $id))->getAllInfo();
    }

    /**
     * Get anime video.
     *
     * @param int|string $id   id of the anime
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getVideo($id, $page = 1)
    {
        return (new Video($id, $page))->getAllInfo();
    }

    /**
     * Get anime episode.
     *
     * @param int|string $id   id of the anime
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getEpisode($id, $page = 1)
    {
        return (new Episode($id, $page))->getAllInfo();
    }

    /**
     * Get anime/manga additional pictures.
     *
     * @param string     $type Either anime or manga
     * @param int|string $id   id of the anime or manga
     *
     * @return array
     */
    private function getPicture($type, $id)
    {
        return (new Picture($type, $id))->getAllInfo();
    }

    /**
     * Get character additional pictures.
     *
     * @param int|string $id id of the character
     *
     * @return array
     */
    private function getCharacterPicture($id)
    {
        return (new CharacterPeoplePicture('character', $id))->getAllInfo();
    }

    /**
     * Get people additional pictures.
     *
     * @param int|string $id id of the people
     *
     * @return array
     */
    private function getPeoplePicture($id)
    {
        return (new CharacterPeoplePicture('people', $id))->getAllInfo();
    }

    /**
     * Get anime additional review.
     *
     * @param int|string $id   id of the anime
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getAnimeReview($id, $page = 1)
    {
        return (new AnimeMangaReview('anime', $id, $page))->getAllInfo();
    }

    /**
     * Get manga additional review.
     *
     * @param int|string $id   id of the manga
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getMangaReview($id, $page = 1)
    {
        return (new AnimeMangaReview('manga', $id, $page))->getAllInfo();
    }

    /**
     * Get anime additional recommendation.
     *
     * @param int|string $id id of the anime
     *
     * @return array
     */
    private function getAnimeRecommendation($id)
    {
        return (new AnimeMangaRecommendation('anime', $id))->getAllInfo();
    }

    /**
     * Get manga additional recommendation.
     *
     * @param int|string $id id of the manga
     *
     * @return array
     */
    private function getMangaRecommendation($id)
    {
        return (new AnimeMangaRecommendation('manga', $id))->getAllInfo();
    }

    /**
     * Get all anime produced by the studio/producer.
     *
     * @param int|string $id   id of the studio/producer
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getStudioProducer($id, $page = 1)
    {
        return (new Producer('anime', 'producer', $id, $page))->getAllInfo();
    }

    /**
     * Get all manga serialized by the magazine.
     *
     * @param int|string $id   id of the magazine
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getMagazine($id, $page = 1)
    {
        return (new Producer('manga', 'producer', $id, $page))->getAllInfo();
    }

    /**
     * Get all anime or manga that has the genre.
     *
     * @param string     $type Either anime or manga
     * @param int|string $id   id of the genre
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getGenre($type, $id, $page = 1)
    {
        return (new Producer($type, 'genre', $id, $page))->getAllInfo();
    }

    /**
     * Get list of all anime genre.
     *
     * @return array
     */
    private function getAllAnimeGenre()
    {
        return (new AllGenre('anime'))->getAllInfo();
    }

    /**
     * Get list of all manga genre.
     *
     * @return array
     */
    private function getAllMangaGenre()
    {
        return (new AllGenre('manga'))->getAllInfo();
    }

    /**
     * Get list of all anime studio/producer.
     *
     * @return array
     */
    private function getAllStudioProducer()
    {
        return (new AllProducer('anime'))->getAllInfo();
    }

    /**
     * Get list of all manga magazine.
     *
     * @return array
     */
    private function getAllMagazine()
    {
        return (new AllProducer('manga'))->getAllInfo();
    }

    /**
     * Get list of all review.
     *
     * @param int|string $type Type of review
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getAllReview($type = 'anime', $page = 1)
    {
        return (new AllReview($type, $page))->getAllInfo();
    }

    /**
     * Get list of all recommendation.
     *
     * @param int|string $type Either anime or manga
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getAllRecommendation($type = 'anime', $page = 1)
    {
        return (new AllRecommendation($type, $page))->getAllInfo();
    }

    /**
     * Get anime search result.
     *
     * @param string     $query Search query
     * @param int|string $page  (Optional) Page number
     *
     * @return array
     */
    private function searchAnime($query, $page = 1)
    {
        return (new SearchAnimeManga('anime', $query, $page))->getAllInfo();
    }

    /**
     * Get manga search result.
     *
     * @param string     $query Search query
     * @param int|string $page  (Optional) Page number
     *
     * @return array
     */
    private function searchManga($query, $page = 1)
    {
        return (new SearchAnimeManga('manga', $query, $page))->getAllInfo();
    }

    /**
     * Get character search result.
     *
     * @param string     $query Search query
     * @param int|string $page  (Optional) Page number
     *
     * @return array
     */
    private function searchCharacter($query, $page = 1)
    {
        return (new SearchCharacterPeople('character', $query, $page))->getAllInfo();
    }

    /**
     * Get people search result.
     *
     * @param string     $query Search query
     * @param int|string $page  (Optional) Page number
     *
     * @return array
     */
    private function searchPeople($query, $page = 1)
    {
        return (new SearchCharacterPeople('people', $query, $page))->getAllInfo();
    }

    /**
     * Get user search result.
     *
     * @param string     $query Search query
     * @param int|string $page  (Optional) Page number
     *
     * @return array
     */
    private function searchUser($query, $page = 1)
    {
        return (new SearchUser($query, $page))->getAllInfo();
    }

    /**
     * Get seasonal anime.
     *
     * @param string|int|bool $year   (Optional) Season year
     * @param string|bool     $season (Optional) Season (summer,spring,fall,winter)
     *
     * @return array
     */
    private function getSeason($year = false, $season = false)
    {
        return (new Season($year, $season))->getAllInfo();
    }

    /**
     * Get top anime.
     *
     * @param string     $type (Optional) Type of anime
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getTopAnime($type = 0, $page = 1)
    {
        return (new Top('anime', $type, $page))->getAllInfo();
    }

    /**
     * Get top manga.
     *
     * @param string     $type (Optional) Type of manga
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getTopManga($type = 0, $page = 1)
    {
        return (new Top('manga', $type, $page))->getAllInfo();
    }

    /**
     * Get top character.
     *
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getTopCharacter($page = 1)
    {
        return (new TopCharacter($page))->getAllInfo();
    }

    /**
     * Get top people.
     *
     * @param int|string $page (Optional) Page number
     *
     * @return array
     */
    private function getTopPeople($page = 1)
    {
        return (new TopPeople($page))->getAllInfo();
    }

    /**
     * Get user info.
     *
     * @param string $user Username
     *
     * @return array
     */
    private function getUser($user)
    {
        return (new User($user))->getAllInfo();
    }

    /**
     * Get user friend list.
     *
     * @param string $user Username
     *
     * @return array
     */
    private function getUserFriend($user)
    {
        return (new Friend($user))->getAllInfo();
    }

    /**
     * Get user history.
     *
     * @param string      $user Username
     * @param string|bool $type (Optional) Either anime or manga
     *
     * @return array
     */
    private function getUserHistory($user, $type = false)
    {
        return (new History($user, $type))->getAllInfo();
    }

    /**
     * Get user list.
     *
     * @param string $user   Username
     * @param string $type   (Optional) Either anime or manga
     * @param string $status (Optional) Anime/manga status
     *
     * @return array
     */
    private function getUserList($user, $type = 'anime', $status = 7)
    {
        return (new UserList($user, $type, $status))->getAllInfo();
    }

    /**
     * Get user cover.
     *
     * @param string      $user  Username
     * @param string      $type  (Optional) Either anime or manga
     * @param string|bool $style (Optional) CSS style for the cover
     *
     * @return string
     */
    private function getUserCover($user, $type = 'anime', $style = false)
    {
        return (new UserCover($user, $type, $style))->getAllInfo();
    }
}
