<?php

namespace MalScraper\Model\Additional;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * AnimeMangaReviewModel class.
 */
class AnimeMangaReviewModel extends MainModel
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
     * Page number.
     *
     * @var int|string
     */
    private $_page;

    /**
     * Default constructor.
     *
     * @param string     $type
     * @param string|int $id
     * @param string|int $page
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($type, $id, $page, $parserArea = '.js-scrollfix-bottom-rel')
    {
        $this->_type = $type;
        $this->_id = $id;
        $this->_page = $page;
        $this->_url = $this->_myAnimeListUrl.'/'.$type.'/'.$id.'/a/reviews?p='.$page;
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
     * Get page.
     *
     * @return string
     */
    private function getPage()
    {
        return $this->_page;
    }

    /**
     * Get review user.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $very_bottom_area
     *
     * @return string
     */
    private function getReviewId($very_bottom_area)
    {
        $id = $very_bottom_area->find('a', 0)->href;
        $id = explode('?id=', $id);

        return $id[1];
    }

    /**
     * Get review id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return string
     */
    private function getReviewUser($top_area)
    {
        $user = $top_area->find('table', 0);

        return $user->find('td', 1)->find('a', 0)->plaintext;
    }

    /**
     * Get review image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return string
     */
    private function getReviewImage($top_area)
    {
        $image = $top_area->find('table', 0);
        $image = $image->find('td', 0)->find('img', 0)->src;

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get review helful.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return string
     */
    private function getReviewHelpful($top_area)
    {
        $helpful = $top_area->find('table', 0);
        $helpful = $helpful->find('td', 1)->find('strong', 0)->plaintext;

        return trim($helpful);
    }

    /**
     * Get review date.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return array
     */
    private function getReviewDate($top_area)
    {
        $date = $top_area->find('div div', 0);

        return [
            'date' => $date->plaintext,
            'time' => $date->title,
        ];
    }

    /**
     * Get review episode seen.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return string
     */
    private function getReviewEpisode($top_area)
    {
        $episode = $top_area->find('div div', 1)->plaintext;
        $episode = str_replace(['episodes seen', 'chapters read'], '', $episode);

        return trim($episode);
    }

    /**
     * Get review score.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $bottom_area
     *
     * @return array
     */
    private function getReviewScore($bottom_area)
    {
        $score = [];
        $score_area = $bottom_area->find('table', 0);
        if ($score_area) {
            foreach ($score_area->find('tr') as $each_score) {
                $score_type = strtolower($each_score->find('td', 0)->plaintext);
                $score_value = $each_score->find('td', 1)->plaintext;
                $score[$score_type] = $score_value;
            }
        }

        return $score;
    }

    /**
     * Get review text.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $bottom_area
     *
     * @return string
     */
    private function getReviewText($bottom_area)
    {
        $useless_area_1 = $bottom_area->find('div', 0)->plaintext;
        $useless_area_2 = $bottom_area->find('div[id^=revhelp_output]', 0)->plaintext;
        $useless_area_3 = $bottom_area->find('a[id^=reviewToggle]', 0) ? $bottom_area->find('a[id^=reviewToggle]', 0)->plaintext : null;
        $text = str_replace([$useless_area_1, $useless_area_2, $useless_area_3], '', $bottom_area->plaintext);
        $text = str_replace('&lt;', '<', $text);

        return trim(preg_replace('/\h+/', ' ', $text));
    }

    /**
     * Get anime/mange review.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $review_area = $this->_parser->find('.borderDark');
        foreach ($review_area as $each_review) {
            $tmp = [];

            $top_area = $each_review->find('.spaceit', 0);
            $bottom_area = $top_area->next_sibling();
            $very_bottom_area = $bottom_area->next_sibling();

            $tmp['id'] = $this->getReviewId($very_bottom_area);
            $tmp['username'] = $this->getReviewUser($top_area);
            $tmp['image'] = $this->getReviewImage($top_area);
            $tmp['helpful'] = $this->getReviewHelpful($top_area);
            $tmp['date'] = $this->getReviewDate($top_area);
            if ($this->_type == 'anime') {
                $tmp['episode'] = $this->getReviewEpisode($top_area);
            } else {
                $tmp['chapter'] = $this->getReviewEpisode($top_area);
            }
            $tmp['score'] = $this->getReviewScore($bottom_area);
            $tmp['review'] = $this->getReviewText($bottom_area);

            $data[] = $tmp;
        }

        return $data;
    }
}
