<?php

namespace MalScraper\Model\User;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * FriendModel class.
 */
class FriendModel extends MainModel
{
    /**
     * Username.
     *
     * @var string
     */
    private $_user;

    /**
     * Default constructor.
     *
     * @param string $user
     * @param string $parserArea
     *
     * @return void
     */
    public function __construct($user, $parserArea = '#content')
    {
        $this->_user = $user;
        $this->_url = $this->_myAnimeListUrl.'/profile/'.$user.'/friends';
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
     * Get username.
     *
     * @return string
     */
    private function getUsername()
    {
        return $this->_user;
    }

    /**
     * Get friend image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $f
     *
     * @return string
     */
    private function getImage($f)
    {
        return Helper::imageUrlCleaner($f->find('a img', 0)->src);
    }

    /**
     * Get friend name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $f
     *
     * @return string
     */
    private function getName($f)
    {
        $name_temp = $f->find('a', 0)->href;
        $name_temp = explode('/', $name_temp);

        return $name_temp[4];
    }

    /**
     * Get friend last online.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $f
     *
     * @return string
     */
    private function getLastOnline($f)
    {
        $last_online = $f->find('strong', 0)->parent()->parent()->next_sibling();

        return trim($last_online->plaintext);
    }

    /**
     * Get friend since.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $f
     *
     * @return string
     */
    private function getFriendSince($f)
    {
        $friend_since = $f->find('strong', 0)->parent()->parent()->next_sibling()->next_sibling();
        $friend_since = str_replace('Friends since', '', $friend_since->plaintext);

        return trim($friend_since);
    }

    /**
     * Get user friend list.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $friend = [];
        $friend_area = $this->_parser->find('.majorPad', 0);
        if ($friend_area) {
            foreach ($friend_area->find('.friendHolder') as $f) {
                $f_dump = [];
                $f = $f->find('.friendBlock', 0);

                $f_dump['image'] = $this->getImage($f);
                $f_dump['name'] = $this->getName($f);
                $f_dump['last_online'] = $this->getLastOnline($f);
                $f_dump['friend_since'] = $this->getFriendSince($f);

                $friend[] = $f_dump;
            }
        }

        return $friend;
    }
}
