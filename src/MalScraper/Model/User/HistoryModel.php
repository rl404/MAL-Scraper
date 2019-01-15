<?php

namespace MalScraper\Model\User;

use MalScraper\Model\MainModel;

/**
 * HistoryModel class.
 */
class HistoryModel extends MainModel
{
    /**
     * Username.
     *
     * @var string
     */
    private $_user;

    /**
     * Either anime or manga.
     *
     * @var string|bool
     */
    private $_type;

    /**
     * Default constructor.
     *
     * @param string      $user
     * @param string|bool $type
     * @param string      $parserArea
     *
     * @return void
     */
    public function __construct($user, $type, $parserArea = '#content')
    {
        $this->_user = $user;
        $this->_type = $type;
        $this->_url = $this->_myAnimeListUrl.'/history/'.$user;
        if ($this->_type) {
            $this->_url .= '/'.$type;
        }
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
     * Get id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getId($name_area)
    {
        $temp_id = $name_area->find('a', 0)->href;
        $temp_id = explode('=', $temp_id);

        return $temp_id[1];
    }

    /**
     * Get title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getTitle($name_area)
    {
        return $name_area->find('a', 0)->plaintext;
    }

    /**
     * Get type.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getType($name_area)
    {
        $type = $name_area->find('a', 0)->href;
        $type = explode('.php', $type);

        return substr($type[0], 1);
    }

    /**
     * Get progress.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getProgress($name_area)
    {
        return $name_area->find('strong', 0)->plaintext;
    }

    /**
     * Get date.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $history
     *
     * @return string
     */
    private function getDate($history)
    {
        $date = $history->find('td', 1);
        $useless_date = $date->find('a', 0);
        $date = $date->plaintext;
        if ($useless_date) {
            $date = str_replace($useless_date, '', $date);
        }

        return trim($date);
    }

    /**
     * Get user history list.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $history_area = $this->_parser->find('table', 0);
        if ($history_area) {
            foreach ($history_area->find('tr') as $history) {
                if ($history->find('td', 0)->class != 'borderClass') {
                    continue;
                }
                $h_temp = [];
                $name_area = $history->find('td', 0);

                $h_temp['id'] = $this->getId($name_area);
                $h_temp['title'] = $this->getTitle($name_area);
                $h_temp['type'] = $this->getType($name_area);
                $h_temp['progress'] = $this->getProgress($name_area);
                $h_temp['date'] = $this->getDate($history);

                $data[] = $h_temp;
            }
        }

        return $data;
    }
}
