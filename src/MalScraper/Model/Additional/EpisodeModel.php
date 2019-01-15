<?php

namespace MalScraper\Model\Additional;

use MalScraper\Model\MainModel;

/**
 * EpisodeModel class.
 */
class EpisodeModel extends MainModel
{
    /**
     * Id of the anime.
     *
     * @var string|int
     */
    private $_id;

    /**
     * Page number.
     *
     * @var int
     */
    private $_page;

    /**
     * Default constructor.
     *
     * @param string|int $id
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($id, $page, $parserArea = '.js-scrollfix-bottom-rel')
    {
        $this->_id = $id;
        $this->_page = 100 * ($page - 1);
        $this->_url = $this->_myAnimeListUrl.'/anime/'.$id.'/a/episode?offset='.$this->_page;
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
     * Get type.
     *
     * @return string
     */
    private function getType()
    {
        return 'anime';
    }

    /**
     * Get anime id.
     *
     * @return string
     */
    private function getId()
    {
        return $this->_id;
    }

    /**
     * Get page.
     *
     * @return string
     */
    private function getPage()
    {
        return $this->_page;
    }

    /**
     * Get episode video link.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_episode
     *
     * @return string
     */
    private function getEpisodeLink($each_episode)
    {
        return $each_episode->find('.episode-video a', 0)->href;
    }

    /**
     * Get episode video number.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_episode
     *
     * @return string
     */
    private function getEpisodeNo($each_episode)
    {
        return $each_episode->find('.episode-number', 0)->plaintext;
    }

    /**
     * Get episode video title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_episode
     * @param string|bool                        $type
     *
     * @return string
     */
    private function getEpisodeTitle($each_episode, $type = false)
    {
        $title = $each_episode->find('.episode-title', 0);
        if ($type) {
            if ($title->find('span', 1)) {
                return trim($title->find('span', 1)->plaintext);
            }

            return trim($title->find('span', 0)->plaintext);
        } else {
            return $title->find('a', 0)->plaintext;
        }
    }

    /**
     * Get episode video aired date.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_episode
     *
     * @return string
     */
    private function getEpisodeAired($each_episode)
    {
        $aired = $each_episode->find('.episode-aired', 0)->plaintext;

        return str_replace('N/A', '', $aired);
    }

    /**
     * Get anime videos.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $episode_area = $this->_parser->find('table.episode_list', 0);
        if ($episode_area) {
            foreach ($episode_area->find('.episode-list-data') as $each_episode) {
                $temp = [];

                $temp['episode'] = $this->getEpisodeNo($each_episode);
                $temp['link'] = $this->getEpisodeLink($each_episode);
                $temp['title'] = $this->getEpisodeTitle($each_episode);
                $temp['japanese_title'] = $this->getEpisodeTitle($each_episode, true);
                $temp['aired'] = $this->getEpisodeAired($each_episode);

                $data[] = $temp;
            }
        }

        return $data;
    }
}
