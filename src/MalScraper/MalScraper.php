<?php
/**
 * rl404 - MalScraper
 *
 * Unofficial PHP API which scraps and parses page source of MyAnimeList.
 * API Documentation: https://github.com/rl404/MAL-Scraper
 *
 * @author Axel Oktavian Antonio
 * @since 26-09-2018
 * @version 1.2.0
 * @license MIT https://opensource.org/licenses/MIT
 */

namespace MalScraper;

require "scraper/mal_scraper.php";
require_once "scraper/cache.php";

use scraper\Cache;

/**
 * Class MalScraper
 *
 * @package MalScraper
 */
class MalScraper {

	/**
	 * Cache class
	 *
	 * @var class
	 */
	private $_cache;

	/**
	 * Cache feature
	 *
	 * @var boolean
	 */
	private $_enable_cache = false;

	/**
	 * Cache expiration time
	 *
	 * @var integer
	 */
	private $_cache_time = 86400;

	/**
	 * Convert to http response
	 *
	 * @var boolean
	 */
	private $_to_api = false;

	/**
	 * Default constructor
	 *
	 * @param array [optional] $config
	 * @return void
	 */
	function __construct($config=false)
	{
		if (!empty($config['enable_cache'])) {
			// enable cache function
			$this->_enable_cache = $config['enable_cache'];

			// create cache class
			$this->_cache = new Cache();
			$this->_cache->setCachePath(dirname(__FILE__) . "/cache/");
			$this->_cache->setCache("malScraper");
			$this->_cache->eraseExpired();

			// set cache time
			if (!empty($config['cache_time'])) {
				$this->_cache_time = $config['cache_time'];
				$this->_cache->eraseExpired($this->_cache_time);
			}
		}

		// to http response function
		if (!empty($config['to_api'])) {
			$this->_to_api = $config['to_api'];
		}
	}

	/**
	 * Default call
	 *
	 * @param string $method
	 * @param array $arguments
	 * @return json
	 */
	public function __call($method,$arguments)
	{
		$result = '';

		// if cache function enabled
		if ($this->_enable_cache === true) {
			$cacheName = $method . '(' . implode(',', $arguments) . ')';
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
	    	$result = self::response($result);
	    } else {
	    	$result = self::toResponse($result);
	    }

	    return $result;
    }

    /**
	 * Convert return result into easy-to-read result
	 *
	 * @param 	string/array $response
	 * @return 	string/array
	 */
    private function toResponse($response) {
    	switch ($response) {
    		case 400:
    			return "Search query needs at least 3 letters";
    		case 403:
    			return "Private user list";
    		case 404:
    			return "Page not found";
    		default:
    			return $response;
		}
    }

    /**
	 * Convert return result into http response
	 *
	 * @param 	string/array $response
	 * @return 	json
	 */
    private function response($response) {
    	if (is_numeric($response)) {
    		header("HTTP/1.1 " . $response);
    		$result['status'] = $response;
			$result['status_message'] = self::toResponse($response);
			$result['data'] = [];
    	} else {
    		header("HTTP/1.1 " . 200);
    		$result['status'] = 200;
			$result['status_message'] = 'Success';
			$result['data'] = self::superEncode($response);
    	}

		$json_response = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
		$json_response = str_replace("\\\\", "", $json_response);

		return $json_response;
	}

	/**
	 * Convert characters to UTF-8
	 *
	 * @param 	array 	$array
	 * @return 	array
	 */
	private function superEncode($array) {
		if ($array) {
		    foreach ($array as $key => $value) {
		        if (is_array($value)) {
		            $array[$key] = self::superEncode($value);
		        } else {
		            $array[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
		        }
		    }
		}

	    return $array;
	}

	/**
	 * Get anime/manga information
	 *
	 * @param 	string 	$type 	anime or manga
	 * @param 	integer	$id 	id of the anime or manga
	 * @return 	json 	\scraper\getInfo
	 */
	private function getInfo() {
		return call_user_func_array ( '\scraper\getInfo' , func_get_args() );
	}

	/**
	 * Get character information
	 *
	 * @param 	integer	$id 	id of character
	 * @return 	json 	\scraper\getCharacter
	 */
	private function getCharacter() {
		return call_user_func_array ( '\scraper\getCharacter' , func_get_args() );
	}

	/**
	 * Get people information
	 *
	 * @param 	integer	$id 	id of people
	 * @return 	json 	\scraper\getPeople
	 */
	private function getPeople() {
		return call_user_func_array ( '\scraper\getPeople' , func_get_args() );
	}

	/**
	 * Get complete list of character and staff of anime or manga
	 *
	 * @param 	string	$type 	anime or manga
	 * @param 	integer	$id 	id of the anime or manga
	 * @return 	json 	\scraper\getCharacterStaff
	 */
	private function getCharacterStaff() {
		return call_user_func_array ( '\scraper\getCharacterStaff' , func_get_args() );
	}

	/**
	 * Get detail stat of anime or manga
	 *
	 * @param 	string	$type 	anime or manga
	 * @param 	integer	$id 	id of the anime or manga
	 * @return 	json 	\scraper\getStat
	 */
	private function getStat() {
		return call_user_func_array ( '\scraper\getStat' , func_get_args() );
	}

	/**
	 * Get addition picture of anime or manga
	 *
	 * @param 	string	$type 	anime or manga
	 * @param 	integer	$id 	id of the anime or manga
	 * @return 	json 	\scraper\getPicture
	 */
	private function getPicture() {
		return call_user_func_array ( '\scraper\getPicture' , func_get_args() );
	}

	/**
	 * Get addition picture of character
	 *
	 * @param 	integer	$id 	id of the character
	 * @return 	json 	\scraper\getCharacterPicture
	 */
	private function getCharacterPicture() {
		return call_user_func_array ( '\scraper\getCharacterPicture' , func_get_args() );
	}

	/**
	 * Get addition picture of people
	 *
	 * @param 	integer	$id 	id of the people
	 * @return 	json 	\scraper\getPeoplePicture
	 */
	private function getPeoplePicture() {
		return call_user_func_array ( '\scraper\getPeoplePicture' , func_get_args() );
	}

	/**
	 * Get list of anime produced by selected studio/producer
	 *
	 * @param 	integer	$id 	id of studio/producer
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\getStudioProducer
	 */
	private function getStudioProducer() {
		return call_user_func_array ( '\scraper\getStudioProducer' , func_get_args() );
	}

	/**
	 * Get list of manga produced by selected magazine
	 *
	 * @param 	integer	$id 	id of magazine
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\getMagazine
	 */
	private function getMagazine() {
		return call_user_func_array ( '\scraper\getMagazine' , func_get_args() );
	}

	/**
	 * Get list of anime contain selected genre
	 *
	 * @param 	string	$type 	anime or manga
	 * @param 	integer	$id 	id of genre
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\getGenre
	 */
	private function getGenre() {
		return call_user_func_array ( '\scraper\getGenre' , func_get_args() );
	}

	/**
	 * Get list of all anime genre
	 *
	 * @return 	json 	\scraper\getAllAnimeGenre
	 */
	private function getAllAnimeGenre() {
		return call_user_func_array ( '\scraper\getAllAnimeGenre' , func_get_args() );
	}

	/**
	 * Get list of all manga genre
	 *
	 * @return 	json 	\scraper\getAllMangaGenre
	 */
	private function getAllMangaGenre() {
		return call_user_func_array ( '\scraper\getAllMangaGenre' , func_get_args() );
	}

	/**
	 * Get list of all anime studio/producer
	 *
	 * @return 	json 	\scraper\getAllStudioProducer
	 */
	private function getAllStudioProducer() {
		return call_user_func_array ( '\scraper\getAllStudioProducer' , func_get_args() );
	}

	/**
	 * Get list of all manga magazine
	 *
	 * @return 	json 	\scraper\getAllMagazine
	 */
	private function getAllMagazine() {
		return call_user_func_array ( '\scraper\getAllMagazine' , func_get_args() );
	}

	/**
	 * Get list of result of anime search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchAnime
	 */
	private function searchAnime() {
		return call_user_func_array ( '\scraper\searchAnime' , func_get_args() );
	}

	/**
	 * Get list of result of manga search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchManga
	 */
	private function searchManga() {
		return call_user_func_array ( '\scraper\searchManga' , func_get_args() );
	}

	/**
	 * Get list of result of character search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchCharacter
	 */
	private function searchCharacter() {
		return call_user_func_array ( '\scraper\searchCharacter' , func_get_args() );
	}

	/**
	 * Get list of result of people search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchPeople
	 */
	private function searchPeople() {
		return call_user_func_array ( '\scraper\searchPeople' , func_get_args() );
	}

	/**
	 * Get list of result of user search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchUser
	 */
	private function searchUser() {
		return call_user_func_array ( '\scraper\searchUser' , func_get_args() );
	}

	/**
	 * Get list of anime of the season
	 *
	 * @param 	string	$year 		year of the season (current year for default)
	 * @param 	string	$season 	summer, spring, fall, winter (current season for default)
	 * @return 	json 	\scraper\getSeason
	 */
	private function getSeason() {
		return call_user_func_array ( '\scraper\getSeason' , func_get_args() );
	}

	/**
	 * Get list of top anime
	 *
	 * @param 	integer	$type 	type of top anime
	 * @param 	integer	$page 	page of top list
	 * @return 	json 	\scraper\getTopAnime
	 */
	private function getTopAnime() {
		return call_user_func_array ( '\scraper\getTopAnime' , func_get_args() );
	}

	/**
	 * Get list of top manga
	 *
	 * @param 	integer	$type 	type of top manga
	 * @param 	integer	$page 	page of top list
	 * @return 	json 	\scraper\getTopManga
	 */
	private function getTopManga() {
		return call_user_func_array ( '\scraper\getTopManga' , func_get_args() );
	}

	/**
	 * Get user information
	 *
	 * @param 	string	$user 	username
	 * @return 	json 	\scraper\getUser
	 */
	private function getUser() {
		return call_user_func_array ( '\scraper\getUser' , func_get_args() );
	}

	/**
	 * Get user's friend list
	 *
	 * @param 	string	$user 	username
	 * @return 	json 	\scraper\getUserFriend
	 */
	private function getUserFriend() {
		return call_user_func_array ( '\scraper\getUserFriend' , func_get_args() );
	}

	/**
	 * Get user's history information
	 *
	 * @param 	string	$user 	username
	 * @param 	string	$type 	anime or manga (optional) (both for default)
	 * @return 	json 	\scraper\getUserHistory
	 */
	private function getUserHistory() {
		return call_user_func_array ( '\scraper\getUserHistory' , func_get_args() );
	}

	/**
	 * Get user's anime or manga list
	 *
	 * @param 	string	$user 	username
	 * @param 	string	$type 	anime or manga (optional) (anime for default)
	 * @param 	integer	$status watching,completed,on hold, etc (optional)
	 * @return 	json 	\scraper\getUserList
	 */
	private function getUserList() {
		return call_user_func_array ( '\scraper\getUserList' , func_get_args() );
	}
}