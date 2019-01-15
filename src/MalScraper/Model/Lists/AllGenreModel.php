<?php

namespace MalScraper\Model\Lists;

use MalScraper\Model\MainModel;

/**
 * AllGenreModel class.
 */
class AllGenreModel extends MainModel
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
    public function __construct($type, $parserArea = '.anime-manga-search .genre-link')
    {
        $this->_type = $type;
        if ($type == 'anime') {
            $this->_url = $this->_myAnimeListUrl.'/anime.php';
        } else {
            $this->_url = $this->_myAnimeListUrl.'/manga.php';
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
     * Get genre count.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_genre
     *
     * @return string
     */
    private function getGenreCount($each_genre)
    {
        $count = $each_genre->plaintext;
        preg_match('/\([0-9,]+\)/', $count, $cnt);
        $count = substr($cnt[0], 1, strlen($cnt[0]) - 2);

        return str_replace(',', '', $count);
    }

    /**
     * Get list of all genre.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        foreach ($this->_parser->find('.genre-list a') as $each_genre) {
            $genre = [];

            $link = $each_genre->href;
            $link = explode('/', $link);
            $id = $link[3];
            $genre['id'] = $id;

            $name = str_replace('_', ' ', $link[4]);
            $genre['name'] = $name;

            $genre['count'] = $this->getGenreCount($each_genre);

            $data[] = $genre;
        }

        return $data;
    }
}
