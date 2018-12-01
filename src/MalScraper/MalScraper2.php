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
 * @version 1.4.0
 *
 * @license MIT https://opensource.org/licenses/MIT
 */

namespace MalScraper;

use Cache;
use MalScraper\Helper\Helper;

use MalScraper\Model\General\InfoModel as Info;
use MalScraper\Model\General\CharacterModel as Character;
use MalScraper\Model\General\PeopleModel as People;
use MalScraper\Model\General\ProducerModel as Producer;

use MalScraper\Model\Additional\CharacterStaffModel as CharacterStaff;
use MalScraper\Model\Additional\StatModel as Stat;
use MalScraper\Model\Additional\PictureModel as Picture;
use MalScraper\Model\Additional\CharacterPeoplePictureModel as CharacterPeoplePicture;

use MalScraper\Model\Lists\AllGenreModel as AllGenre;
use MalScraper\Model\Lists\AllProducerModel as AllProducer;

use MalScraper\Model\Search\SearchAnimeMangaModel as SearchAnimeManga;

/**
 * Class MalScraper.
 */
class MalScraper2
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
            $this->_cache->setCachePath(dirname(__FILE__).'/Cache/');

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
     * @return string
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
        if ($this->_to_api === true)
            return Helper::response($result);
        return Helper::toResponse($result);
    }

    /**
     * Get anime/manga information.
     *
     * @param string $type anime or manga
     * @param int    $id   id of the anime or manga
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
     * @param int    $id   id of the character
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
     * @param int    $id   id of the people
     *
     * @return array
     */
    private function getPeople($id)
    {
        return (new People($id))->getAllInfo();
    }

    /**
     * Get anime/manga character + staff complete list.
     *
     * @param string    $type   Either anime or manga
     * @param int    $id   id of the anime or manga
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
     * @param string    $type   Either anime or manga
     * @param int    $id   id of the anime or manga
     *
     * @return array
     */
    private function getStat($type, $id)
    {
        return (new Stat($type, $id))->getAllInfo();
    }

    /**
     * Get anime/manga additional pictures.
     *
     * @param string    $type   Either anime or manga
     * @param int    $id   id of the anime or manga
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
     * @param int    $id   id of the character
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
     * @param int    $id   id of the people
     *
     * @return array
     */
    private function getPeoplePicture($id)
    {
        return (new CharacterPeoplePicture('people', $id))->getAllInfo();
    }

    /**
     * Get all anime produced by the studio/producer.
     *
     * @param int    $id   id of the studio/producer
     * @param int    $page   (Optional) Page number
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
     * @param int    $id   id of the magazine
     * @param int    $page   (Optional) Page number
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
     * @param string    $type   Either anime or manga
     * @param int    $id   id of the genre
     * @param int    $page   (Optional) Page number
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
     * Get anime search result.
     *
     * @param string    $query   Search query
     * @param int    $page   (Optional) Page number
     *
     * @return array
     */
    private function searchAnime($query, $page=1)
    {
        return (new searchAnimeManga('anime', $query, $page))->getAllInfo();
    }

    /**
     * Get manga search result.
     *
     * @param string    $query   Search query
     * @param int    $page   (Optional) Page number
     *
     * @return array
     */
    private function searchManga($query, $page=1)
    {
        return (new searchAnimeManga('manga', $query, $page))->getAllInfo();
    }
}