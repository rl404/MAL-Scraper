<?php

namespace MalScraper\Model\Additional;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * PeoplePictureModel class.
 */
class PeoplePictureModel extends MainModel
{
    /**
     * Id of the people.
     *
     * @var string|int
     */
	private $_id;

    /**
     * Default constructor.
     *
     * @param string|int $id
     * @param string $parserArea
     *
     * @return void
     */
	public function __construct($id, $parserArea = '#content table tr td')
    {
    	$this->_id = $id;
        $this->_url = $this->_myAnimeListUrl.'/people/'.$id;
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
     * Get type.
     *
     * @return string
     */
    private function getType()
    {
        return null;
    }

    /**
     * Get people id.
     *
     * @return string
     */
    private function getId()
    {
    	return $this->_id;
    }

    /**
     * Get people additional pictures.
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