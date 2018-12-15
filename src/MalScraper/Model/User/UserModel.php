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
     * @param string $status
     *
     * @return string
     */
    private function getStatus($status)
    {
        $status_area = $this->_parser->find('.container-left .user-profile', 0);
        $status_area = $status_area->find('.user-status', 0);
        foreach ($status_area->find('li') as $each_status) {
            $status_type = trim($each_status->find('span', 0)->plaintext);
            $status_value = trim($each_status->find('span', 1)->plaintext);

            if ($status == $status_type) {
                return $status_value;
            }
        }

        return '';
    }

    /**
     * Get user status.
     *
     * @param int $liNo
     *
     * @return string
     */
    private function getStatus2($liNo)
    {
        $status_area = $this->_parser->find('.container-left .user-profile', 0);
        $status_area = $status_area->find('.user-status', 2);

        return trim($status_area->find('li', $liNo)->find('span', 1)->plaintext);
    }

    /**
     * Get user sns.
     *
     * @return array
     */
    private function getSns()
    {
        $sns = [];
        $sns_area = $this->_parser->find('.container-left .user-profile', 0);
        $sns_area = $sns_area->find('.user-profile-sns', 0);
        foreach ($sns_area->find('a') as $each_sns) {
            if ($each_sns->class != 'di-ib mb8') {
                $sns[] = $each_sns->href;
            }
        }

        return $sns;
    }

    /**
     * Get user friend.
     *
     * @return array
     */
    private function getFriend()
    {
        $friend = [];
        $friend_area = $this->_parser->find('.container-left .user-profile', 0);
        $friend_area = $friend_area->find('.user-friends', 0);

        $friend_count = $friend_area->prev_sibling()->find('a', 0)->plaintext;
        preg_match('/\(\d+\)/', $friend_count, $friend_count);
        $friend['count'] = str_replace(['(', ')'], '', $friend_count[0]);

        $friend['data'] = [];
        foreach ($friend_area->find('a') as $f) {
            $temp_friend = [];

            $temp_friend['name'] = $f->plaintext;
            $temp_friend['image'] = Helper::imageUrlCleaner($f->getAttribute('data-bg'));

            $friend['data'][] = $temp_friend;
        }

        return $friend;
    }

    /**
     * Get user about.
     *
     * @return string
     */
    private function getAbout()
    {
        $about_area = $this->_parser->find('.container-right', 0);
        $about = $about_area->find('table tr td div[class=word-break]', 0);

        return $about ? trim($about->innertext) : '';
    }

    /**
     * Get user anime stat.
     *
     * @param string $type
     *
     * @return array
     */
    private function getStat($type)
    {
        $anime_stat = [];
        $right_area = $this->_parser->find('.container-right', 0);
        $stat_area = $right_area->find('.user-statistics', 0);
        if ($type == 'anime') {
            $a_stat_area = $stat_area->find('div[class="user-statistics-stats mt16"]', 0);
        } else {
            $a_stat_area = $stat_area->find('div[class="user-statistics-stats mt16"]', 1);
        }
        $a_stat_score = $a_stat_area->find('.stat-score', 0);

        $anime_stat['days'] = $this->getDays($a_stat_score);
        $anime_stat['mean_score'] = $this->getMeanScore($a_stat_score);
        $anime_stat['status'] = $this->getStatStatus($a_stat_area, $type);
        $anime_stat['history'] = $this->getHistory($right_area, $type);

        return $anime_stat;
    }

    /**
     * Get days stat.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $a_stat_score
     *
     * @return string
     */
    private function getDays($a_stat_score)
    {
        $days = $a_stat_score->find('div', 0);
        $temp_days = $days->find('span', 0)->plaintext;

        return str_replace($temp_days, '', $days->plaintext);
    }

    /**
     * Get mean score stat.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $a_stat_score
     *
     * @return string
     */
    private function getMeanScore($a_stat_score)
    {
        $mean_score = $a_stat_score->find('div', 1);
        $temp_score = $mean_score->find('span', 0)->plaintext;

        return str_replace($temp_score, '', $mean_score->plaintext);
    }

    /**
     * Get status stat.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $a_stat_area
     * @param string                             $type
     *
     * @return array
     */
    private function getStatStatus($a_stat_area, $type)
    {
        $temp_stat = [];
        $a_stat_status = $a_stat_area->find('ul[class=stats-status]', 0);
        if ($type == 'anime') {
            $temp_stat['watching'] = $this->getStatStatusCount($a_stat_status, 0);
            $temp_stat['completed'] = $this->getStatStatusCount($a_stat_status, 1);
            $temp_stat['on_hold'] = $this->getStatStatusCount($a_stat_status, 2);
            $temp_stat['dropped'] = $this->getStatStatusCount($a_stat_status, 3);
            $temp_stat['plan_to_watch'] = $this->getStatStatusCount($a_stat_status, 4);
        } else {
            $temp_stat['reading'] = $this->getStatStatusCount($a_stat_status, 0);
            $temp_stat['completed'] = $this->getStatStatusCount($a_stat_status, 1);
            $temp_stat['on_hold'] = $this->getStatStatusCount($a_stat_status, 2);
            $temp_stat['dropped'] = $this->getStatStatusCount($a_stat_status, 3);
            $temp_stat['plan_to_read'] = $this->getStatStatusCount($a_stat_status, 4);
        }

        $a_stat_status = $a_stat_area->find('ul[class=stats-data]', 0);
        $temp_stat['total'] = $this->getStatStatusCount($a_stat_status, 1);
        if ($type == 'anime') {
            $temp_stat['rewatched'] = $this->getStatStatusCount($a_stat_status, 3);
            $temp_stat['episode'] = $this->getStatStatusCount($a_stat_status, 5);
        } else {
            $temp_stat['reread'] = $this->getStatStatusCount($a_stat_status, 3);
            $temp_stat['chapter'] = $this->getStatStatusCount($a_stat_status, 5);
            $temp_stat['volume'] = $this->getStatStatusCount($a_stat_status, 7);
        }

        return $temp_stat;
    }

    /**
     * Get status stat count.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $a_stat_area
     * @param int                                $spanNo
     *
     * @return string
     */
    private function getStatStatusCount($a_stat_status, $spanNo)
    {
        return str_replace(',', '', trim($a_stat_status->find('span', $spanNo)->plaintext));
    }

    /**
     * Get history.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $right_area
     * @param string                             $type
     *
     * @return array
     */
    private function getHistory($right_area, $type)
    {
        $history = [];
        $a_history_area = $right_area->find('div[class="updates '.$type.'"]', 0);
        foreach ($a_history_area->find('.statistics-updates') as $each_history) {
            $temp_history = [];
            $history_data_area = $each_history->find('.data', 0);

            $temp_history['image'] = $this->getHistoryImage($each_history);
            $temp_history['id'] = $this->getHistoryId($history_data_area);
            $temp_history['title'] = $this->getHistoryTitle($history_data_area);
            $temp_history['date'] = $this->getHistoryDate($history_data_area);
            $progress = $this->getHistoryProgress($history_data_area);
            $temp_history = array_merge($temp_history, $progress);

            $history[] = $temp_history;
        }

        return $history;
    }

    /**
     * Get history image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_history
     *
     * @return string
     */
    private function getHistoryImage($each_history)
    {
        $image = $each_history->find('img', 0)->src;

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get history id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $history_data_area
     *
     * @return string
     */
    private function getHistoryId($history_data_area)
    {
        $id = $history_data_area->find('a', 0)->href;
        $id = explode('/', $id);

        return $id[4];
    }

    /**
     * Get history title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $history_data_area
     *
     * @return string
     */
    private function getHistoryTitle($history_data_area)
    {
        return $history_data_area->find('a', 0)->plaintext;
    }

    /**
     * Get history date.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $history_data_area
     *
     * @return string
     */
    private function getHistoryDate($history_data_area)
    {
        $date = $history_data_area->find('span', 0)->plaintext;

        return trim($date);
    }

    /**
     * Get history progress.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $history_data_area
     *
     * @return array
     */
    private function getHistoryProgress($history_data_area)
    {
        $progress = $history_data_area->find('.graph-content', 0)->next_sibling()->innertext;
        $progress = trim(preg_replace("/([\s])+/", ' ', strip_tags($progress)));
        $progress = explode('·', $progress);
        $p1 = explode(' ', $progress[0]);

        $temp_history = [];
        $temp_history['status'] = strtolower(count($p1) > 3 ? $progress[0] : $p1[0]);
        $temp_history['progress'] = count($p1) > 3 ? '-' : $p1[1];
        $temp_history['score'] = trim(str_replace('Scored', '', $progress[1]));

        return $temp_history;
    }

    /**
     * Get favorite.
     *
     * @return array
     */
    private function getFavorite()
    {
        $right_area = $this->_parser->find('.container-right', 0);
        $favorite_area = $right_area->find('.user-favorites-outer', 0);

        $favorite = [];
        $favorite['anime'] = $this->getFavList($favorite_area, 'anime');
        $favorite['manga'] = $this->getFavList($favorite_area, 'manga');
        $favorite['character'] = $this->getFavList($favorite_area, 'characters');
        $favorite['people'] = $this->getFavList($favorite_area, 'people');

        return $favorite;
    }

    /**
     * Get favorite list.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $favorite_area
     * @param string                             $type
     *
     * @return array
     */
    private function getFavList($favorite_area, $type)
    {
        $favorite = [];
        $favorite_area = $favorite_area->find('ul[class="favorites-list '.$type.'"]', 0);
        if ($favorite_area) {
            foreach ($favorite_area->find('li') as $each_fav) {
                $temp_fav = [];

                $temp_fav['image'] = $this->getFavImage($each_fav);
                $temp_fav['id'] = $this->getFavId($each_fav);

                if ($type == 'anime' || $type == 'manga') {
                    $temp_fav['title'] = $this->getFavTitle($each_fav);
                    $temp_fav['type'] = $this->getFavType($each_fav);
                    $temp_fav['year'] = $this->getFavYear($each_fav);
                } else {
                    $temp_fav['name'] = $this->getFavTitle($each_fav);

                    if ($type == 'characters') {
                        $temp_fav['media_id'] = $this->getFavMedia($each_fav, 2);
                        $temp_fav['media_title'] = $this->getFavMediaTitle($each_fav);
                        $temp_fav['media_type'] = $this->getFavMedia($each_fav, 1);
                    }
                }

                $favorite[] = $temp_fav;
            }
        }

        return $favorite;
    }

    /**
     * Get favorite image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_fav
     *
     * @return string
     */
    private function getFavImage($each_fav)
    {
        $image = $each_fav->find('a', 0)->style;
        preg_match('/\'([^\'])*/', $image, $image);
        $image = substr($image[0], 1);

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get favorite id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_fav
     *
     * @return string
     */
    private function getFavId($each_fav)
    {
        $id = $each_fav->find('a', 0)->href;
        $id = explode('/', $id);

        return $id[4];
    }

    /**
     * Get favorite title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_fav
     *
     * @return string
     */
    private function getFavTitle($each_fav)
    {
        return $each_fav->find('a', 1)->plaintext;
    }

    /**
     * Get favorite type.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_fav
     *
     * @return string
     */
    private function getFavType($each_fav)
    {
        $temp_type = $each_fav->find('span', 0)->plaintext;
        $temp_type = explode('·', $temp_type);

        return trim($temp_type[0]);
    }

    /**
     * Get favorite year.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_fav
     *
     * @return string
     */
    private function getFavYear($each_fav)
    {
        $temp_type = $each_fav->find('span', 0)->plaintext;
        $temp_type = explode('·', $temp_type);

        return trim($temp_type[1]);
    }

    /**
     * Get favorite anime/manga id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_fav
     *
     * @return string
     */
    private function getFavMedia($each_fav, $key)
    {
        $media_id = $each_fav->find('a', 2)->href;
        $media_id = explode('/', $media_id);

        return $media_id[$key];
    }

    /**
     * Get favorite anime/manga title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_fav
     *
     * @return string
     */
    private function getFavMediaTitle($each_fav)
    {
        $anime_title = $each_fav->find('a', 2)->plaintext;

        return trim($anime_title);
    }

    /**
     * Get user information.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'username'       => $this->getUsername(),
            'image'          => $this->getImage(),
            'last_online'    => $this->getStatus('Last Online'),
            'gender'         => $this->getStatus('Gender'),
            'birthday'       => $this->getStatus('Birthday'),
            'location'       => $this->getStatus('Location'),
            'joined_date'    => $this->getStatus('Joined'),
            'forum_post'     => $this->getStatus2(0),
            'review'         => $this->getStatus2(1),
            'recommendation' => $this->getStatus2(2),
            'blog_post'      => $this->getStatus2(3),
            'club'           => $this->getStatus2(4),
            'sns'            => $this->getSns(),
            'friend'         => $this->getFriend(),
            'about'          => $this->getAbout(),
            'anime_stat'     => $this->getStat('anime'),
            'manga_stat'     => $this->getStat('manga'),
            'favorite'       => $this->getFavorite(),
        ];

        return $data;
    }
}
