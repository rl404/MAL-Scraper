<?php

namespace MalScraper\Model\User;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * UserModel class.
 */
class UserModel extends MainModel
{
    /**
     * Username
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
        $this->_url = $this->_myAnimeListUrl.'/profile/'.$user;
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
        if ($this->_error)
            return $this->_error;
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
     * Get user image.
     *
     * @return string
     */
    private function getImage()
    {
        $image = $this->_parser->find('.container-left .user-profile', 0);
        $image = $image->find('.user-image img', 0);
        return $image ? Helper::imageUrlCleaner($image->src) : '';
    }

    /**
     * Get user status.
     *
     * @return string
     */
    private function getStatus()
    {
        $status = [];
        $status_area = $this->_parser->find('.container-left .user-profile', 0);
        $status_area = $status_area->find('.user-status', 0);
        foreach ($status_area->find('li') as $each_status) {
            $status_type = trim($each_status->find('span', 0)->plaintext);
            $status_value = trim($each_status->find('span', 1)->plaintext);

            $status[$status_type] = $status_value;
        }
        return $status;
    }

    /**
     * Get user status.
     *
     * @return string
     */
    private function getStatus2($status)
    {
        $status_area = $this->_parser->find('.container-left .user-profile', 0);
        $status_area = $status_area->find('.user-status', 2);

        $liNo = 0;
        switch ($status) {
            case 'forum':
            $liNo = 0;
            break;

            case 'review':
            $liNo = 1;
            break;

            case 'recommendation':
            $liNo = 2;
            break;

            case 'blog':
            $liNo = 3;
            break;

            case 'club':
            $liNo = 4;
            break;

            default:
            return '';
        }

        return trim($status_area->find('li', $liNo)->find('span', 1)->plaintext);
    }


    /**
     * Get user information.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $status = $this->getStatus();

        $data = [
            'username'      => $this->getUsername(),
            'image'         => $this->getImage(),
            'last_online'    => !empty($status['Last Online']) ? $status['Last Online'] : '',
            'gender'         => !empty($status['Gender']) ? $status['Gender'] : '',
            'birthday'       => !empty($status['Birthday']) ? $status['Birthday'] : '',
            'location'       => !empty($status['Location']) ? $status['Location'] : '',
            'joined_date'    => !empty($status['Joined']) ? $status['Joined'] : '',
            'forum_post'     => $this->getStatus2('forum'),
            'review'         => $this->getStatus2('review'),
            'recommendation' => $this->getStatus2('recommendation'),
            'blog_post'      => $this->getStatus2('blog'),
            'club'           => $this->getStatus2('club'),
            // 'sns'            => $sns,
            // 'friend'         => $friend,
            // 'about'          => $about,
            // 'anime_stat'     => $anime_stat,
            // 'manga_stat'     => $manga_stat,
            // 'favorite'       => $favorite,
        ];

        return $data;
    }
}