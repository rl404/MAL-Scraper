<?php

namespace MalScraper\Model\General;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * RecommendationModel class.
 */
class RecommendationModel extends MainModel
{
    /**
     * Either anime or manga.
     *
     * @var string
     */
    private $_type;

    /**
     * Id of the first anime/manga.
     *
     * @var int|string
     */
    private $_id1;

    /**
     * Id of the first anime/manga.
     *
     * @var int|string
     */
    private $_id2;

    /**
     * Default constructor.
     *
     * @param string     $type
     * @param string|int $id1
     * @param string|int $id2
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($type, $id1, $id2, $parserArea = '#content')
    {
        $this->_type = $type;
        $this->_id1 = $id1;
        $this->_id2 = $id2;
        $this->_url = $this->_myAnimeListUrl.'/recommendations/'.$type.'/'.$id1.'-'.$id2;
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
     * Get id1.
     *
     * @return string
     */
    private function getId1()
    {
        return $this->_id1;
    }

    /**
     * Get id2.
     *
     * @return string
     */
    private function getId2()
    {
        return $this->_id2;
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
        $image = $source_area->find('img', 0)->src;

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get recommendation text.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $recommendation_area
     *
     * @return array
     */
    private function getRecom($recommendation_area)
    {
        $recommendation = [];
        foreach ($recommendation_area->find('.borderClass') as $each_recom) {
            $tmp = [];

            $tmp['username'] = $this->getRecomUser($each_recom);
            $tmp['recommendation'] = $this->getRecomText($each_recom);

            $recommendation[] = $tmp;
        }

        return $recommendation;
    }

    /**
     * Get recommendation user.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getRecomUser($each_recom)
    {
        return $each_recom->find('a', 1)->plaintext;
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
        $text = $each_recom->find('span', 0)->plaintext;
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
        $recommendation_area = $this->_parser->find('.borderDark', 0);

        $data['source'] = $this->getSource($recommendation_area);
        $data['recommendation'] = $this->getRecom($recommendation_area);

        return $data;
    }
}
