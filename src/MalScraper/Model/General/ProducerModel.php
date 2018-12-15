<?php

namespace MalScraper\Model\General;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * ProducerModel class.
 */
class ProducerModel extends MainModel
{
    /**
     * Either anime or manga.
     *
     * @var string
     */
    private $_type;

    /**
     * Either producer or genre.
     *
     * @var string
     */
    private $_type2;

    /**
     * Id of the producer.
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
     * @param string     $type
     * @param string     $type2
     * @param string|int $id
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($type, $type2, $id, $page = 1, $parserArea = '#content .js-categories-seasonal')
    {
        $this->_type = $type;
        $this->_type2 = $type2;
        $this->_id = $id;
        $this->_page = $page;

        if ($type2 == 'producer') {
            if ($type == 'anime') {
                $this->_url = $this->_myAnimeListUrl.'/anime/producer/'.$id.'/?page='.$page;
            } else {
                $this->_url = $this->_myAnimeListUrl.'/manga/magazine/'.$id.'/?page='.$page;
            }
        } else {
            $this->_url = $this->_myAnimeListUrl.'/'.$type.'/genre/'.$id.'/?page='.$page;
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
     * Get anime image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return string
     */
    private function getAnimeImage($each_anime)
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
    private function getAnimeId($name_area)
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
    private function getAnimeTitle($name_area)
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
    private function getAnimeProducer($producer_area)
    {
        $producer = [];
        $producer_area = $producer_area->find('span[class=producer]', 0);
        foreach ($producer_area->find('a') as $each_producer) {
            $temp_prod = [];

            $temp_prod['id'] = $this->getAnimeProducerId($each_producer);
            $temp_prod['name'] = $this->getAnimeProducerName($each_producer);

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
    private function getAnimeProducerId($each_producer)
    {
        $prod_id = $each_producer->href;
        $prod_id = explode('/', $prod_id);
        if ($this->_type == 'anime') {
            return $prod_id[3];
        }

        return $prod_id[4];
    }

    /**
     * Get producer name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_producer
     *
     * @return string
     */
    private function getAnimeProducerName($each_producer)
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
    private function getAnimeEpisode($producer_area)
    {
        $episode = $producer_area->find('div[class=eps]', 0)->plaintext;

        return trim(str_replace(['eps', 'ep', 'vols', 'vol'], '', $episode));
    }

    /**
     * Get anime source.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $producer_area
     *
     * @return string
     */
    private function getAnimeSource($producer_area)
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
    private function getAnimeGenre($each_anime)
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
    private function getAnimeSynopsis($each_anime)
    {
        $synopsis = $each_anime->find('div[class="synopsis js-synopsis"]', 0)->plaintext;

        return trim(preg_replace("/([\s])+/", ' ', $synopsis));
    }

    /**
     * Get anime licensor.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_anime
     *
     * @return string|array
     */
    private function getAnimeLicensor($each_anime)
    {
        if ($this->_type == 'anime') {
            $licensor = $each_anime->find('div[class="synopsis js-synopsis"] .licensors', 0)->getAttribute('data-licensors');
            $licensor = explode(',', $licensor);

            return array_filter($licensor);
        } else {
            $serialization = $each_anime->find('div[class="synopsis js-synopsis"] .serialization a', 0);

            return $serialization ? $serialization->plaintext : '';
        }
    }

    /**
     * Get anime type.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $info_area
     *
     * @return string
     */
    private function getAnimeType($info_area)
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
    private function getAnimeStart($info_area)
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
    private function getAnimeScore($info_area)
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
    private function getAnimeMember($info_area)
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

            $result['image'] = $this->getAnimeImage($each_anime);
            $result['id'] = $this->getAnimeId($name_area);
            $result['title'] = $this->getAnimeTitle($name_area);
            $result['genre'] = $this->getAnimeGenre($each_anime);
            $result['synopsis'] = $this->getAnimeSynopsis($each_anime);
            $result['source'] = $this->getAnimeSource($producer_area);

            if ($this->_type == 'anime') {
                $result['producer'] = $this->getAnimeProducer($producer_area);
                $result['episode'] = $this->getAnimeEpisode($producer_area);
                $result['licensor'] = $this->getAnimeLicensor($each_anime);
                $result['type'] = $this->getAnimeType($info_area);
            } else {
                $result['author'] = $this->getAnimeProducer($producer_area);
                $result['volume'] = $this->getAnimeEpisode($producer_area);
                $result['serialization'] = $this->getAnimeLicensor($each_anime);
            }

            $result['airing_start'] = $this->getAnimeStart($info_area);
            $result['member'] = $this->getAnimeMember($info_area);
            $result['score'] = $this->getAnimeScore($info_area);

            $data[] = $result;
        }

        return $data;
    }
}
