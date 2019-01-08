<?php

namespace MalScraper\Model\Top;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * TopPeopleModel class.
 */
class TopPeopleModel extends MainModel
{
    /**
     * Page number.
     *
     * @var string
     */
    private $_page;

    /**
     * Default constructor.
     *
     * @param string|int $page
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($page, $parserArea = '#content')
    {
        $this->_page = 50 * ($page - 1);
        $this->_url = $this->_myAnimeListUrl.'/people.php?limit='.$this->_page;
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
     * Get rank.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_char
     *
     * @return string
     */
    private function getRank($each_char)
    {
        $rank = $each_char->find('td span', 0)->plaintext;

        return trim($rank);
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
        $id = $name_area->find('a', 0)->href;
        $id = explode('/', $id);

        return $id[4];
    }

    /**
     * Get name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getName($name_area)
    {
        return $name_area->find('.information', 0)->find('a', 0)->plaintext;
    }

    /**
     * Get japanese name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getJapaneseName($name_area)
    {
        $name = $name_area->find('.information', 0)->find('span', 0);
        if ($name) {
            $name = $name->plaintext;

            return substr($name, 1, strlen($name) - 3);
        }

        return '';
    }

    /**
     * Get image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getImage($name_area)
    {
        $image = $name_area->find('img', 0)->getAttribute('data-src');

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get birthday.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_char
     *
     * @return array
     */
    private function getBirthday($each_char)
    {
        $day = $each_char->find('.birthday', 0)->plaintext;
        $day = preg_replace('/\s+/', ' ', $day);
        $day = trim($day);

        return $day == 'Unknown' ? '' : $day;
    }

    /**
     * Get favorite.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_char
     *
     * @return string
     */
    private function getFavorite($each_char)
    {
        $fav = $each_char->find('.favorites', 0)->plaintext;
        $fav = str_replace(',', '', $fav);

        return trim($fav);
    }

    /**
     * Get result list.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $data_index = 0;
        $top_table = $this->_parser->find('.people-favorites-ranking-table', 0);
        foreach ($top_table->find('tr[class=ranking-list]') as $each_char) {
            $name_area = $each_char->find('.people', 0);

            $data[$data_index]['rank'] = $this->getRank($each_char);
            $data[$data_index]['id'] = $this->getId($name_area);
            $data[$data_index]['name'] = $this->getName($name_area);
            $data[$data_index]['japanese_name'] = $this->getJapaneseName($name_area);
            $data[$data_index]['image'] = $this->getImage($name_area);
            $data[$data_index]['birthday'] = $this->getBirthday($each_char);
            $data[$data_index]['favorite'] = $this->getFavorite($each_char);

            $data_index++;
        }

        return $data;
    }
}
