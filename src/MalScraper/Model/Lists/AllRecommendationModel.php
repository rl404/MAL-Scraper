<?php

namespace MalScraper\Model\Lists;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * AllRecommendationModel class.
 */
class AllRecommendationModel extends MainModel
{
    /**
     * Either anime or manga.
     *
     * @var string
     */
    private $_type;

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
     * @param string|int $page
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($type, $page, $parserArea = '#content')
    {
        $this->_type = $type;
        $this->_page = 100 * ($page - 1);
        $this->_url = $this->_myAnimeListUrl.'/recommendations.php?s=recentrecs&t='.$type.'&show='.$this->_page;
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
        return $this->_type;
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
     * Get username.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getUsername($each_recom)
    {
        return $each_recom->find('table', 0)->next_sibling()->next_sibling()->find('a', 1)->plaintext;
    }

    /**
     * Get date.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getDate($each_recom)
    {
        $date = $each_recom->find('table', 0)->next_sibling()->next_sibling()->plaintext;
        $date = explode('-', $date);

        return trim($date[1]);
    }

    /**
     * Get source.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return array
     */
    private function getSource($each_recom)
    {
        $source = [];
        $source_area = $each_recom->find('table tr', 0);
        $source['liked'] = $this->getSourceLiked($source_area, 0);
        $source['recommendation'] = $this->getSourceLiked($source_area, 1);

        return $source;
    }

    /**
     * Get source liked.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $source_area
     * @param int                                $key
     *
     * @return array
     */
    private function getSourceLiked($source_area, $key)
    {
        $liked = [];
        $source_area = $source_area->find('td', $key);
        $liked['id'] = $this->getSourceId($source_area);
        $liked['title'] = $this->getSourceTitle($source_area);
        $liked['type'] = $this->getType();
        $liked['image'] = $this->getSourceImage($source_area);

        return $liked;
    }

    /**
     * Get source id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $source_area
     *
     * @return string
     */
    private function getSourceId($source_area)
    {
        $id = $source_area->find('a', 0)->href;
        $id = explode('/', $id);

        return $id[4];
    }

    /**
     * Get source title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $source_area
     *
     * @return string
     */
    private function getSourceTitle($source_area)
    {
        return $source_area->find('strong', 0)->plaintext;
    }

    /**
     * Get source image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $source_area
     *
     * @return string
     */
    private function getSourceImage($source_area)
    {
        $image = $source_area->find('img', 0)->getAttribute('data-src');

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get recommendation text.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getRecomText($each_recom)
    {
        $text = $each_recom->find('.recommendations-user-recs-text', 0)->plaintext;
        $text = preg_replace('/\s{2,}/', "\n", $text);

        return $text;
    }

    /**
     * Get anime/mange recommendation.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $recommendation_area = $this->_parser->find('div[class="spaceit borderClass"]');
        foreach ($recommendation_area as $each_recom) {
            $tmp = [];

            $tmp['username'] = $this->getUsername($each_recom);
            $tmp['date'] = $this->getDate($each_recom);
            $tmp['source'] = $this->getSource($each_recom);
            $tmp['recommendation'] = $this->getRecomText($each_recom);

            $data[] = $tmp;
        }

        return $data;
    }
}
