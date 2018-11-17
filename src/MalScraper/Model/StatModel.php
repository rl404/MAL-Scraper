<?php

namespace MalScraper\Model;

use MalScraper\Helper\Helper;

/**
 * StatModel class.
 */
class StatModel extends MainModel
{
    /**
     * Type of info. Either anime or manga.
     *
     * @var string
     */
	private $_type;

    /**
     * Id of the anime or manga.
     *
     * @var string|int
     */
	private $_id;

    /**
     * Default constructor.
     *
     * @param string $type
     * @param string|int $id
     * @param string $parserArea
     *
     * @return void
     */
	public function __construct($type, $id, $parserArea = '.js-scrollfix-bottom-rel')
    {
    	$this->_type = $type;
    	$this->_id = $id;
        $this->_url = $this->_myAnimeListUrl.'/'.$type.'/'.$id;
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
     * Get type (anime or manga).
     *
     * @return string
     */
    private function getType()
    {
        return $this->_type;
    }

    /**
     * Get anime/manga id.
     *
     * @return string
     */
    private function getId()
    {
    	return $this->_id;
    }

    /**
     * Get anime/manga stat summary.
     *
     * @return array
     */
    private function getSummary()
    {
        $summary = [];
        $summary_area = $this->_parser->find('h2', 0)->next_sibling();
        if ($summary_area->tag == 'div') {
            while (true) {

                // status
                $temp_type = $summary_area->find('span', 0)->plaintext;
                $summary_type = trim(str_replace(':', '', strtolower($temp_type)));

                // count
                $status_area = $summary_area->plaintext;
                $count = str_replace($temp_type, '', $status_area);
                $summary[$summary_type] = trim(str_replace(',', '', $count));

                $summary_area = $summary_area->next_sibling();
                if ($summary_area->tag != 'div') {
                    break;
                }
            }
        }
        return $summary;
    }

    /**
     * Get anime/manga stat score.
     *
     * @return array
     */
    private function getScore()
    {
        $score = [];
        $score_area = $this->_parser->find('h2', 1)->next_sibling();
        if ($score_area->tag == 'table') {
            foreach ($score_area->find('tr') as $each_score) {
                $temp_score = [];

                // type
                $score_type = $each_score->find('td', 0)->plaintext;
                $temp_score['type'] = $score_type;

                // vote
                $temp_vote = $each_score->find('td', 1)->find('span small', 0)->plaintext;
                $vote = substr($temp_vote, 1, strlen($temp_vote) - 2);
                $temp_score['vote'] = str_replace(' votes', '', $vote);

                // percent
                $percent = $each_score->find('td', 1)->find('span', 0)->plaintext;
                $percent = str_replace([$temp_vote, '%'], '', $percent);
                $temp_score['percent'] = trim($percent);

                $score[] = $temp_score;
            }
        }
        return $score;
    }

    /**
     * Get anime/manga stat user.
     *
     * @return array
     */
    private function getUser()
    {
        $user = [];
        $user_area = $this->_parser->find('.table-recently-updated', 0);
        if ($user_area) {
            foreach ($user_area->find('tr') as $each_user) {
                if (!$each_user->find('td', 0)->find('div', 0))
                    continue;

                $temp_user = [];

                $username_area = $each_user->find('td', 0);

                $temp_user['image'] = self::getUserImage($username_area);
                $temp_user['username'] = self::getUsername($username_area);
                $temp_user['score'] = self::getUserScore($each_user);
                $temp_user['status'] = self::getUserStatus($each_user);

                if ($this->_type == 'anime') {
                    $temp_user['episode'] = self::getUserProgress($each_user, 3);
                    $temp_user['date'] = self::getUserDate($each_user, 4);
                } else {
                    $temp_user['volume'] = self::getUserProgress($each_user, 3);
                    $temp_user['chapter'] = self::getUserProgress($each_user, 4);
                    $temp_user['date'] = self::getUserDate($each_user, 5);
                }

                $user[] = $temp_user;
            }
        }
        return $user;
    }

    /**
     * Get username.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $username_area
     *
     * @return string
     */
    static private function getUsername($username_area)
    {
        return $username_area->find('a', 1)->plaintext;
    }

    /**
     * Get user image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $username_area
     *
     * @return string
     */
    static private function getUserImage($username_area)
    {
        $user_image = $username_area->find('a', 0)->style;
        $user_image = substr($user_image, 21, strlen($user_image) - 22);
        return Helper::imageUrlCleaner($user_image);
    }

    /**
     * Get user image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_user
     *
     * @return string
     */
    static private function getUserScore($each_user)
    {
        return $each_user->find('td', 1)->plaintext;
    }

    /**
     * Get user status.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_user
     *
     * @return string
     */
    static private function getUserStatus($each_user)
    {
        return strtolower($each_user->find('td', 2)->plaintext);
    }

    /**
     * Get user progress.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_user
     * @param int $count
     *
     * @return string
     */
    static private function getUserProgress($each_user, $count = 3)
    {
        $progress = $each_user->find('td', $count)->plaintext;
        return str_replace(' ', '', $progress);
    }

    /**
     * Get user date.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_user
     * @param int $count
     *
     * @return string
     */
    static private function getUserDate($each_user, $count = 4)
    {
        return $each_user->find('td', $count)->plaintext;
    }

    /**
     * Get anime/manga stat info.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'summary' => self::getSummary(),
            'score'   => self::getScore(),
            'user'    => self::getUser(),
        ];

        return $data;
    }
}