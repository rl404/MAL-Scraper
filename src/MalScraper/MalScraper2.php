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
use MalScraper\Model\InfoModel as Info;

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
		return (new Info($type,$id))->getAllInfo();
	}
}