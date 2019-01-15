<?php

namespace MalScraper\Model\Search;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * SearchAnimeMangaModel class.
 */
class SearchAnimeMangaModel extends MainModel
{
    /**
     * Either anime or manga.
     *
     * @var string
     */
    private $_type;

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
     * @param string     $type
     * @param string     $query
     * @param int|string $page
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($type, $query, $page, $parserArea = 'div[class^=js-categories-seasonal]')
    {
        $this->_type = $type;
        $this->_query = $query;
        $this->_page = 50 * ($page - 1);

        if ($type == 'anime') {
            $this->_url = $this->_myAnimeListUrl.'/anime.php?q='.$query.'&show='.$this->_page;
        } else {
            $this->_url = $this->_myAnimeListUrl.'/manga.php?q='.$query.'&show='.$this->_page;
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
     * Get image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $result_area
     *
     * @return string
     */
    private function getImage($result_area)
    {
        $image = $result_area->find('td', 0)->find('a img', 0)->getAttribute('data-src');

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
        $id = $name_area->find('div[id^=sarea]', 0)->id;

        return str_replace('sarea', '', $id);
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
        return $name_area->find('strong', 0)->plaintext;
    }

    /**
     * Get summary.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getSummary($name_area)
    {
        $summary = $name_area->find('.pt4', 0)->plaintext;

        return str_replace('read more.', '', $summary);
    }

    /**
     * Get type.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $result_area
     *
     * @return string
     */
    private function getType($result_area)
    {
        $type = $result_area->find('td', 2)->plaintext;

        return trim($type);
    }

    /**
     * Get episode.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $result_area
     *
     * @return string
     */
    private function getEpisode($result_area)
    {
        $episode = $result_area->find('td', 3)->plaintext;
        $episode = trim($episode);

        return $episode == '-' ? '' : $episode;
    }

    /**
     * Get score.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $result_area
     *
     * @return string
     */
    private function getScore($result_area)
    {
        $score = $result_area->find('td', 4)->plaintext;
        $score = trim($score);

        return $score == 'N/A' ? '' : $score;
    }

    /**
     * Get result list.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $result_table = $this->_parser->find('table', 0);
        $result_area = $result_table->find('tr', 0)->next_sibling();
        while (true) {
            $result = [];

            $name_area = $result_area->find('td', 1);

            $result['image'] = $this->getImage($result_area);
            $result['id'] = $this->getId($name_area);
            $result['title'] = $this->getTitle($name_area);
            $result['summary'] = $this->getSummary($name_area);
            $result['type'] = $this->getType($result_area);

            if ($this->_type == 'anime') {
                $result['episode'] = $this->getEpisode($result_area);
            } else {
                $result['volume'] = $this->getEpisode($result_area);
            }

            $result['score'] = $this->getScore($result_area);

            $data[] = $result;

            $result_area = $result_area->next_sibling();
            if (!$result_area) {
                break;
            }
        }

        return $data;
    }
}
