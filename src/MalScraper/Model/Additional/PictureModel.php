<?php

namespace MalScraper\Model\Additional;

use MalScraper\Model\MainModel;

/**
 * PictureModel class.
 */
class PictureModel extends MainModel
{
    /**
     * Type of info. Either anime or manga.
     *
     * @var string
     */
    private $_type;

    /**
     * Id of the anime or manga.
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
        $this->_url = $this->_myAnimeListUrl.'/'.$type.'/'.$id.'/a/pics';
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
     * Get type (anime or manga).
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
     * Get anime/manga additional pictures.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $picture_table = $this->_parser->find('table', 0);
        if ($picture_table) {
            foreach ($picture_table->find('img') as $each_picture) {
                if ($each_picture) {
                    $data[] = $each_picture->src;
                }
            }
        }

        return $data;
    }
}
