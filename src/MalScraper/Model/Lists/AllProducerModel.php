<?php

namespace MalScraper\Model\Lists;

use MalScraper\Model\MainModel;

/**
 * AllProducerModel class.
 */
class AllProducerModel extends MainModel
{
    /**
     * Either anime or manga.
     *
     * @var string
     */
    private $_type;

    /**
     * Default constructor.
     *
     * @param string $type
     * @param string $parserArea
     *
     * @return void
     */
    public function __construct($type, $parserArea = '.anime-manga-search')
    {
        $this->_type = $type;
        if ($type == 'anime') {
            $this->_url = $this->_myAnimeListUrl.'/anime/producer';
        } else {
            $this->_url = $this->_myAnimeListUrl.'/manga/magazine';
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
     * Get producer id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_studio
     *
     * @return string
     */
    private function getProducerId($each_studio)
    {
        $link = $each_studio->href;
        $link = explode('/', $link);

        return $link[3];
    }

    /**
     * Get producer name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_studio
     *
     * @return string
     */
    private function getProducerName($each_studio)
    {
        $name = $each_studio->plaintext;

        return trim(preg_replace('/\([0-9,]+\)/', '', $name));
    }

    /**
     * Get producer count.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_studio
     *
     * @return string
     */
    private function getProducerCount($each_studio)
    {
        $count = $each_studio->plaintext;
        preg_match('/\([0-9,]+\)/', $count, $cnt);
        $count = substr($cnt[0], 1, strlen($cnt[0]) - 2);

        return str_replace(',', '', $count);
    }

    /**
     * Get list of all [producer].
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        foreach ($this->_parser->find('.genre-list a') as $each_studio) {
            $studio = [];

            $studio['id'] = $this->getProducerId($each_studio);
            $studio['name'] = $this->getProducerName($each_studio);
            $studio['count'] = $this->getProducerCount($each_studio);

            $data[] = $studio;
        }

        return $data;
    }
}
