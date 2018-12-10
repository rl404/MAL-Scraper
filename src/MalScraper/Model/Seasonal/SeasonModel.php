<?php

namespace MalScraper\Model\Seasonal;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * SeasonModel class.
 */
class SeasonModel extends MainModel
{
    /**
     * Year of season.
     *
     * @var string
     */
	private $_year;

    /**
     * Season name. Either spring, summer, fall, or winter.
     *
     * @var string
     */
    private $_season;

    /**
     * Default constructor.
     *
     * @param string|int $year
     * @param string $season
     * @param string $parserArea
     *
     * @return void
     */
	public function __construct($year = false, $season = false, $parserArea = '#content .js-categories-seasonal')
    {
        $this->_year = !$year ? date('Y') : $year;
    	$this->_season = !$season ? Helper::getCurrentSeason() : $season;
        $this->_url = $this->_myAnimeListUrl.'/anime/season/'.$this->_year.'/'.$this->_season;
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
        if ($this->_error)
            return $this->_error;
        return call_user_func_array([$this, $method], $arguments);
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
        $temp_image = $each_anime->find('div[class=image]', 0)->find('img', 0);
        $image = $temp_image->src;
        $image = !$image ? $temp_image->getAttribute('data-src') : $image;
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
        $id = $name_area->find('p a', 0)->href;
        $parsed_char_id = explode('/', $id);
        return $parsed_char_id[4];
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
        return $name_area->find('p a', 0)->plaintext;
    }

    /**
     * Get producer.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $producer_area
     *
     * @return array
     */
    private function getProducer($producer_area)
    {
        $producer = [];
        $temp_producer = $producer_area->find('span[class=producer]', 0);
        foreach ($temp_producer->find('a') as $each_producer) {
            $temp_prod = [];

            $temp_prod['id'] = $this->getProducerId($each_producer);
            $temp_prod['name'] = $this->getProducerName($each_producer);

            $producer[] = $temp_prod;
        }
        return $producer;
    }

    /**
     * Get producer id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_producer
     *
     * @return string
     */
    private function getProducerId($each_producer)
    {
        $prod_id = $each_producer->href;
        $parsed_prod_id = explode('/', $prod_id);
        return $parsed_prod_id[3];
    }

    /**
     * Get producer name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_producer
     *
     * @return string
     */
    private function getProducerName($each_producer)
    {
        return $each_producer->plaintext;
    }

    /**
     * Get episode.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $producer_area
     *
     * @return string
     */
    private function getEpisode($producer_area)
    {
        $episode = $producer_area->find('div[class=eps]', 0)->plaintext;
        $episode = trim(str_replace(['eps', 'ep'], '', $episode));
        return $episode == '?' ? '' : $episode;
    }

    /**
     * Get source.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $producer_area
     *
     * @return string
     */
    private function getSource($producer_area)
    {
        return trim($producer_area->find('span[class=source]', 0)->plaintext);
    }

    /**
     * Get genre.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return array
     */
    private function getGenre($each_anime)
    {
        $genre = [];
        $genre_area = $each_anime->find('div[class="genres js-genre"]', 0);
        foreach ($genre_area->find('a') as $each_genre) {
            $genre[] = $each_genre->plaintext;
        }
        return $genre;
    }

    /**
     * Get synopsis.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return string
     */
    private function getSynopsis($each_anime)
    {
        $synopsis = $each_anime->find('div[class="synopsis js-synopsis"]', 0)->plaintext;
        preg_match('/(No synopsis)/', $synopsis, $temp_synopsis);
        if (!$temp_synopsis) {
            $synopsis = trim(preg_replace("/([\s])+/", ' ', $synopsis));
        } else {
            $synopsis = '';
        }
        return $synopsis;
    }

    /**
     * Get licensor.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return array
     */
    private function getLicensor($each_anime)
    {
        $temp_licensor = $each_anime->find('div[class="synopsis js-synopsis"] .licensors', 0)->getAttribute('data-licensors');
        $licensor = explode(',', $temp_licensor);
        return array_filter($licensor);
    }

    /**
     * Get type.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    private function getType($info_area)
    {
        $type = $info_area->find('.info', 0)->plaintext;
        $type = explode('-', $type);
        return trim($type[0]);
    }

    /**
     * Get airing start.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    private function getAiring($info_area)
    {
        $airing_start = $info_area->find('.info .remain-time', 0)->plaintext;
        return trim(str_replace(['?', ' ,'], ['', ','], $airing_start));
    }

    /**
     * Get member.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    private function getMember($info_area)
    {
        $member = $info_area->find('.scormem span[class^=member]', 0)->plaintext;
        return trim(str_replace(',', '', $member));
    }

    /**
     * Get score.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    private function getScore($info_area)
    {
        $score = $info_area->find('.scormem .score', 0)->plaintext;
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

        $anime_table = $this->_parser->find('div[class="seasonal-anime js-seasonal-anime"]');
        foreach ($anime_table as $each_anime) {
            $result = [];

            $name_area = $each_anime->find('div[class=title]', 0);
            $producer_area = $each_anime->find('div[class=prodsrc]', 0);
            $info_area = $each_anime->find('.information', 0);

            $result['image'] = $this->getImage($each_anime);
            $result['id'] = $this->getId($name_area);
            $result['title'] = $this->getTitle($name_area);
            $result['producer'] = $this->getProducer($producer_area);
            $result['episode'] = $this->getEpisode($producer_area);
            $result['source'] = $this->getSource($producer_area);
            $result['genre'] = $this->getGenre($each_anime);
            $result['synopsis'] = $this->getSynopsis($each_anime);
            $result['licensor'] = $this->getLicensor($each_anime);
            $result['type'] = $this->getType($info_area);
            $result['airing_start'] = $this->getAiring($info_area);
            $result['member'] = $this->getMember($info_area);
            $result['score'] = $this->getScore($info_area);

            $data[] = $result;
        }

        return $data;
    }

}