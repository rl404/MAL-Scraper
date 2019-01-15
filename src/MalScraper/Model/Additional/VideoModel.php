<?php

namespace MalScraper\Model\Additional;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * VideoModel class.
 */
class VideoModel extends MainModel
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
        $this->_page = $page;
        $this->_url = $this->_myAnimeListUrl.'/anime/'.$id.'/a/video?p='.$page;
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
        return 'manga';
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
     * Get anime episode.
     *
     * @return array
     */
    private function getEpisode()
    {
        $episode = [];
        $episode_area = $this->_parser->find('.episode-video', 0);
        if ($episode_area) {
            foreach ($episode_area->find('.video-list-outer') as $v) {
                $temp = [];

                $link_area = $v->find('a', 0);
                $temp['episode'] = $this->getEpisodeTitle($link_area, 0);
                $temp['title'] = $this->getEpisodeTitle($link_area, 1);
                $temp['link'] = $link_area->href;

                $episode[] = $temp;
            }
        }

        return $episode;
    }

    /**
     * Get episode video title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $link_area
     * @param int                                $key
     *
     * @return string
     */
    private function getEpisodeTitle($link_area, $key)
    {
        $title = $link_area->find('span.title', 0)->plaintext;
        $title = explode("\n", $title);
        $title = $key == 0 ? str_replace('Episode ', '', $title[$key]) : $title[$key];

        return trim($title);
    }

    /**
     * Get anime promotion video.
     *
     * @return array
     */
    private function getPromotion()
    {
        $promotion = [];
        $promotion_area = $this->_parser->find('.promotional-video', 0);
        if ($promotion_area) {
            foreach ($promotion_area->find('.video-list-outer') as $v) {
                $temp = [];

                $link_area = $v->find('a', 0);
                $temp['title'] = $this->getPromotionTitle($link_area);
                $temp['link'] = $this->getPromotionLink($link_area);

                $promotion[] = $temp;
            }
        }

        return $promotion;
    }

    /**
     * Get promotional video title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $link_area
     *
     * @return string
     */
    private function getPromotionTitle($link_area)
    {
        return $link_area->find('span.title', 0)->plaintext;
    }

    /**
     * Get promotional video link.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $link_area
     *
     * @return string
     */
    private function getPromotionLink($link_area)
    {
        return Helper::videoUrlCleaner($link_area->href);
    }

    /**
     * Get anime videos.
     *
     * @return array
     */
    private function getAllInfo()
    {
        return [
            'episode'   => $this->getEpisode(),
            'promotion' => $this->getPromotion(),
        ];
    }
}
