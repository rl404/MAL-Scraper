<?php

namespace MalScraper\Model;

use MalScraper\Helper\Helper;

/**
 * StudioProducerModel class.
 */
class StudioProducerModel extends MainModel
{
    /**
     * Id of the studio.
     *
     * @var string|int
     */
	private $_id;

    /**
     * Page number.
     *
     * @var string|int
     */
    private $_page;

    /**
     * Default constructor.
     *
     * @param string|int $id
     * @param string $parserArea
     *
     * @return void
     */
	public function __construct($id, $page = 1, $parserArea = '#content .js-categories-seasonal')
    {
        $this->_id = $id;
    	$this->_page = $page;
        $this->_url = $this->_myAnimeListUrl.'/anime/producer/'.$id.'/?page='.$page;
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
     * Get anime image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return string
     */
    static private function getAnimeImage($each_anime)
    {
        $image = $each_anime->find('div[class=image]', 0)->find('img', 0)->getAttribute('data-src');
        return  Helper::imageUrlCleaner($image);
    }

    /**
     * Get anime id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    static private function getAnimeId($name_area)
    {
        $anime_id = $name_area->find('p a', 0)->href;
        $anime_id = explode('/', $anime_id);
        return $anime_id[4];
    }

    /**
     * Get anime title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    static private function getAnimeTitle($name_area)
    {
        return $name_area->find('p a', 0)->plaintext;
    }

    /**
     * Get producer name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $producer_area
     *
     * @return array
     */
    static private function getAnimeProducer($producer_area)
    {
        $producer = [];
        $producer_area = $producer_area->find('span[class=producer]', 0);
        foreach ($producer_area->find('a') as $each_producer) {
            $temp_prod = [];

            $temp_prod['id'] = self::getAnimeProducerId($each_producer);
            $temp_prod['name'] = self::getAnimeProducerName($each_producer);

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
    static private function getAnimeProducerId($each_producer)
    {
        $prod_id = $each_producer->href;
        $prod_id = explode('/', $prod_id);
        return $prod_id[3];
    }

    /**
     * Get producer name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_producer
     *
     * @return string
     */
    static private function getAnimeProducerName($each_producer)
    {
        return $each_producer->plaintext;
    }

    /**
     * Get anime episode.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $producer_area
     *
     * @return string
     */
    static private function getAnimeEpisode($producer_area)
    {
        $episode = $producer_area->find('div[class=eps]', 0)->plaintext;
        return trim(str_replace(['eps', 'ep'], '', $episode));
    }

    /**
     * Get anime source.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $producer_area
     *
     * @return string
     */
    static private function getAnimeSource($producer_area)
    {
        $source = $producer_area->find('span[class=source]', 0)->plaintext;
        return trim($source);
    }

    /**
     * Get anime genre.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return array
     */
    static private function getAnimeGenre($each_anime)
    {
        $genre = [];
        $genre_area = $each_anime->find('div[class="genres js-genre"]', 0);
        foreach ($genre_area->find('a') as $each_genre) {
            $genre[] = $each_genre->plaintext;
        }
        return $genre;
    }

    /**
     * Get anime synopsis.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return string
     */
    static private function getAnimeSynopsis($each_anime)
    {
        $synopsis = $each_anime->find('div[class="synopsis js-synopsis"]', 0)->plaintext;
        return trim(preg_replace("/([\s])+/", ' ', $synopsis));
    }

    /**
     * Get anime licensor.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return array
     */
    static private function getAnimeLicensor($each_anime)
    {
        $licensor = $each_anime->find('div[class="synopsis js-synopsis"] .licensors', 0)->getAttribute('data-licensors');
        $licensor = explode(',', $licensor);
        return array_filter($licensor);
    }

    /**
     * Get anime type.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    static private function getAnimeType($info_area)
    {
        $type = $info_area->find('.info', 0)->plaintext;
        $type = explode('-', $type);
        return trim($type[0]);
    }

    /**
     * Get anime start.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    static private function getAnimeStart($info_area)
    {
        $airing_start = $info_area->find('.info .remain-time', 0)->plaintext;
        return trim($airing_start);
    }

    /**
     * Get anime member.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    static private function getAnimeScore($info_area)
    {
        $score = $info_area->find('.scormem .score', 0)->plaintext;
        return trim($score);
    }

    /**
     * Get anime score.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    static private function getAnimeMember($info_area)
    {
        $member = $info_area->find('.scormem span[class^=member]', 0)->plaintext;
        return trim(str_replace(',', '', $member));
    }

    /**
     * Get all anime produced by the studio/producer.
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

            $result['image'] = self::getAnimeImage($each_anime);
            $result['id'] = self::getAnimeId($name_area);
            $result['title'] = self::getAnimeTitle($name_area);
            $result['producer'] = self::getAnimeProducer($producer_area);
            $result['episode'] = self::getAnimeEpisode($producer_area);
            $result['source'] = self::getAnimeSource($producer_area);
            $result['genre'] = self::getAnimeGenre($each_anime);
            $result['synopsis'] = self::getAnimeSynopsis($each_anime);
            $result['licensor'] = self::getAnimeLicensor($each_anime);
            $result['type'] = self::getAnimeType($info_area);
            $result['airing_start'] = self::getAnimeStart($info_area);
            $result['member'] = self::getAnimeMember($info_area);
            $result['score'] = self::getAnimeScore($info_area);

            $data[] = $result;
        }
        return $data;
    }
}