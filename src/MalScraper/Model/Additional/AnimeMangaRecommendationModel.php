<?php

namespace MalScraper\Model\Additional;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * AnimeMangaRecommendationModel class.
 */
class AnimeMangaRecommendationModel extends MainModel
{
    /**
     * Either anime or manga.
     *
     * @var string
     */
    private $_type;

    /**
     * Id of the anime/manga.
     *
     * @var string|int
     */
    private $_id;

    /**
     * Default constructor.
     *
     * @param string     $type
     * @param string|int $id
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($type, $id, $parserArea = '.js-scrollfix-bottom-rel')
    {
        $this->_type = $type;
        $this->_id = $id;
        $this->_url = $this->_myAnimeListUrl.'/'.$type.'/'.$id.'/a/userrecs';
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
     * Get anime/manga id.
     *
     * @return string
     */
    private function getId()
    {
        return $this->_id;
    }

    /**
     * Get recommendation anime/manga id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $content_area
     *
     * @return string
     */
    private function getRecomId($content_area)
    {
        $id = $content_area->find('a', 0)->href;
        $id = explode('/', $id);

        return $id[4];
    }

    /**
     * Get recommendation anime/manga title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $content_area
     *
     * @return string
     */
    private function getRecomTitle($content_area)
    {
        return $content_area->find('strong', 0)->plaintext;
    }

    /**
     * Get recommendation anime/manga image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getRecomImage($each_recom)
    {
        $image = $each_recom->find('img', 0)->getAttribute('data-src');

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get recommendation anime/manga user.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $content_area
     *
     * @return string
     */
    private function getRecomUsername($content_area)
    {
        return $content_area->find('.borderClass', 0)->find('.spaceit_pad', 1)->find('a', 1)->plaintext;
    }

    /**
     * Get recommendation anime/manga text.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $content_area
     *
     * @return string
     */
    private function getRecomText($content_area)
    {
        $text = $content_area->find('.borderClass', 0)->find('.spaceit_pad', 0)->plaintext;
        $useless_area = $content_area->find('.js-toggle-recommendation-button', 0) ? $content_area->find('.js-toggle-recommendation-button', 0)->plaintext : null;
        $text = str_replace($useless_area, '', $text);
        $text = str_replace('&lt;', '<', $text);
        $text = preg_replace('/\s{2,}/', "\n", $text);

        return $text;
    }

    /**
     * Get recommendation anime/manga other recommendation.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $other_area
     *
     * @return array
     */
    private function getRecomOther($other_area)
    {
        $other = [];
        if (!empty($other_area)) {
            foreach ($other_area->find('.borderClass') as $each_other) {
                $tmp = [];

                $tmp['username'] = $this->getOtherUser($each_other);
                $tmp['recommendation'] = $this->getOtherRecom($each_other);

                $other[] = $tmp;
            }
        }

        return $other;
    }

    /**
     * Get recommendation anime/manga other user.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_other
     *
     * @return string
     */
    private function getOtherUser($each_other)
    {
        return $each_other->find('.spaceit_pad', 1)->find('a', 1)->plaintext;
    }

    /**
     * Get recommendation anime/manga other recommendation.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_other
     *
     * @return string
     */
    private function getOtherRecom($each_other)
    {
        $text = $each_other->find('.spaceit_pad', 0)->plaintext;
        $useless_area = $each_other->find('.js-toggle-recommendation-button', 0) ? $each_other->find('.js-toggle-recommendation-button', 0)->plaintext : null;
        $text = str_replace($useless_area, '', $text);
        $text = str_replace('&lt;', '<', $text);
        $text = preg_replace('/\s{2,}/', "\n", $text);

        return $text;
    }

    /**
     * Get anime/mange review.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $recommendation_area = $this->_parser->find('.borderClass table');
        foreach ($recommendation_area as $each_recom) {
            $tmp = [];

            $content_area = $each_recom->find('td', 1);
            $other_area = $each_recom->find('div[id^=simaid]', 0);

            $tmp['id'] = $this->getRecomId($content_area);
            $tmp['title'] = $this->getRecomTitle($content_area);
            $tmp['image'] = $this->getRecomImage($each_recom);
            $tmp['username'] = $this->getRecomUsername($content_area);
            $tmp['recommendation'] = $this->getRecomText($content_area);
            $tmp['other'] = $this->getRecomOther($other_area);

            $data[] = $tmp;
        }

        return $data;
    }
}
