<?php

namespace MalScraper\Model\Top;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * TopModel class.
 */
class TopModel extends MainModel
{
    /**
     * Either anime or manga.
     *
     * @var string
     */
    private $_supertype;

    /**
     * Type.
     *
     * @var string
     */
    private $_type;

    /**
     * Page number.
     *
     * @var string
     */
    private $_page;

    /**
     * Default constructor.
     *
     * @param string     $supertype
     * @param string     $type
     * @param string|int $page
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($supertype, $type, $page, $parserArea = '#content')
    {
        $this->_supertype = $supertype;
        $this->_page = 50 * ($page - 1);
        if ($this->_supertype == 'anime') {
            $this->_type = Helper::getTopAnimeType()[$type];
            $this->_url = $this->_myAnimeListUrl.'/topanime.php?type='.$this->_type.'&limit='.$this->_page;
        } else {
            $this->_type = Helper::getTopMangaType()[$type];
            $this->_url = $this->_myAnimeListUrl.'/topmanga.php?type='.$this->_type.'&limit='.$this->_page;
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
     * Get rank.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return string
     */
    private function getRank($each_anime)
    {
        $rank = $each_anime->find('td span', 0)->plaintext;

        return trim($rank);
    }

    /**
     * Get image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return string
     */
    private function getImage($each_anime)
    {
        $image = $each_anime->find('td', 1)->find('a img', 0)->getAttribute('data-src');

        return Helper::imageUrlCleaner($image);
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
        $id = $name_area->find('div', 0)->id;

        return str_replace('area', '', $id);
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
     * @param array $parsed_info
     *
     * @return string
     */
    private function getType($parsed_info)
    {
        $parsed_info = trim(preg_replace("/([\s])+/", ' ', $parsed_info[0]));
        $parsed_info = explode(' ', $parsed_info);

        return $parsed_info[0];
    }

    /**
     * Get episode.
     *
     * @param array $parsed_info
     *
     * @return string
     */
    private function getEpisode($parsed_info)
    {
        $parsed_info = trim(preg_replace("/([\s])+/", ' ', $parsed_info[0]));
        $parsed_info = explode(' ', $parsed_info);
        $episode = str_replace('(', '', $parsed_info[1]);

        return $episode == '?' ? '' : $episode;
    }

    /**
     * Get date.
     *
     * @param array $parsed_info
     *
     * @return string
     */
    private function getDate($parsed_info, $type)
    {
        $date = explode('-', $parsed_info[1]);
        if ($type == 'start') {
            return trim($date[0]);
        } else {
            return trim($date[1]);
        }
    }

    /**
     * Get member.
     *
     * @param array $parsed_info
     *
     * @return string
     */
    private function getMember($parsed_info)
    {
        return trim(str_replace(['members', 'favorites', ','], '', $parsed_info[2]));
    }

    /**
     * Get score.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return string
     */
    private function getScore($each_anime)
    {
        $score = $each_anime->find('td', 2)->plaintext;

        return trim(str_replace('N/A', '', $score));
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
        $top_table = $this->_parser->find('table', 0);
        foreach ($top_table->find('tr[class=ranking-list]') as $each_anime) {
            $name_area = $each_anime->find('td .detail', 0);
            $info_area = $name_area->find('div[class^=information]', 0);
            $parsed_info = explode('<br>', $info_area->innertext);

            $data[$data_index]['rank'] = $this->getRank($each_anime);
            $data[$data_index]['image'] = $this->getImage($each_anime);
            $data[$data_index]['id'] = $this->getId($name_area);
            $data[$data_index]['title'] = $this->getTitle($name_area);
            $data[$data_index]['type'] = $this->getType($parsed_info);
            if ($this->_supertype == 'anime') {
                $data[$data_index]['episode'] = $this->getEpisode($parsed_info);
            } else {
                $data[$data_index]['volume'] = $this->getEpisode($parsed_info);
            }
            $data[$data_index]['start_date'] = $this->getDate($parsed_info, 'start');
            $data[$data_index]['end_date'] = $this->getDate($parsed_info, 'end');
            $data[$data_index]['member'] = $this->getMember($parsed_info);
            $data[$data_index]['score'] = $this->getScore($each_anime);

            $data_index++;
        }

        return $data;
    }
}
