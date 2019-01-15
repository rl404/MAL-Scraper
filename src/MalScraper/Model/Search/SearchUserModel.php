<?php

namespace MalScraper\Model\Search;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * SearchUserModel class.
 */
class SearchUserModel extends MainModel
{
    /**
     * Search query.
     *
     * @var string
     */
    protected $_query;

    /**
     * Page number.
     *
     * @var int|string
     */
    private $_page;

    /**
     * Default constructor.
     *
     * @param string     $query
     * @param int|string $page
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($query, $page, $parserArea = '#content')
    {
        $this->_query = $query;
        $this->_page = 24 * ($page - 1);
        $this->_url = $this->_myAnimeListUrl.'/users.php?q='.$query.'&show='.$this->_page;
        $this->_parserArea = $parserArea;

        parent::errorCheck($this);
    }

    /**
     * Default call.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return array|string|int
     */
    public function __call($method, $arguments)
    {
        if ($this->_error) {
            return $this->_error;
        }

        return call_user_func_array([$this, $method], $arguments);
    }

    /**
     * Get name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $user
     *
     * @return string
     */
    private function getName($user)
    {
        return $user->find('a', 0)->plaintext;
    }

    /**
     * Get image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $user
     *
     * @return string
     */
    private function getImage($user)
    {
        return Helper::imageUrlCleaner($user->find('img', 0)->src);
    }

    /**
     * Get last online.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $user
     *
     * @return string
     */
    private function getLastOnline($user)
    {
        return $user->find('small', 0)->plaintext;
    }

    /**
     * Get result list.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        foreach ($this->_parser->find('.borderClass') as $user) {
            if ($user->align != 'center') {
                continue;
            }

            $temp_user = [];

            $temp_user['name'] = $this->getName($user);
            $temp_user['image'] = $this->getImage($user);
            $temp_user['last_online'] = $this->getLastOnline($user);

            $data[] = $temp_user;
        }

        return $data;
    }
}
