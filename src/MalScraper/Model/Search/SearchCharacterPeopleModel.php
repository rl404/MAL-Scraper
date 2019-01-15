<?php

namespace MalScraper\Model\Search;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * SearchCharacterPeopleModel class.
 */
class SearchCharacterPeopleModel extends MainModel
{
    /**
     * Either character or people.
     *
     * @var string
     */
    private $_type;

    /**
     * Search query.
     *
     * @var string
     */
    protected $_query;

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
     * @param string     $query
     * @param int|string $page
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($type, $query, $page, $parserArea = '#content')
    {
        $this->_type = $type;
        $this->_query = $query;
        $this->_page = 50 * ($page - 1);

        if ($type == 'character') {
            $this->_url = $this->_myAnimeListUrl.'/character.php?q='.$query.'&show='.$this->_page;
        } else {
            $this->_url = $this->_myAnimeListUrl.'/people.php?q='.$query.'&show='.$this->_page;
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
     * Get image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $result_area
     *
     * @return string
     */
    private function getImage($result_area)
    {
        $image = $result_area->find('td', 0)->find('a img', 0)->src;

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
        $id = $name_area->find('a', 0)->href;
        $parsed_char_id = explode('/', $id);

        return $this->_type == 'character' ? $parsed_char_id[4] : $parsed_char_id[2];
    }

    /**
     * Get name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getName($name_area)
    {
        return $name_area->find('a', 0)->plaintext;
    }

    /**
     * Get nickname.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $name_area
     *
     * @return string
     */
    private function getNickname($name_area)
    {
        $nickname = $name_area->find('small', 0);

        return $nickname ? substr($nickname->plaintext, 1, strlen($nickname->plaintext) - 2) : '';
    }

    /**
     * Get role.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $result_area
     *
     * @return array
     */
    private function getRole($result_area)
    {
        $role = [];
        $role['manga'] = $role['anime'] = [];
        $role_area = $result_area->find('td', 2)->find('small', 0);
        foreach ($role_area->find('a') as $each_role) {
            $temp_role = [];
            $parsed_role_id = explode('/', $each_role->href);

            $role_type = $parsed_role_id[1];
            $temp_role['id'] = $parsed_role_id[2];
            $temp_role['title'] = $each_role->plaintext;

            if ($temp_role['title']) {
                $role[$role_type][] = $temp_role;
            }
        }

        return $role;
    }

    /**
     * Get result list.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [];
        $result_table = $this->_parser->find('table', 0);
        $result_area = $result_table->find('tr', 0)->next_sibling();
        while (true) {
            $result = [];

            $name_area = $result_area->find('td', 1);

            $result['image'] = $this->getImage($result_area);
            $result['id'] = $this->getId($name_area);
            $result['name'] = $this->getName($name_area);
            $result['nickname'] = $this->getNickname($name_area);

            if ($this->_type == 'character') {
                $role = $this->getRole($result_area);
                $result = array_merge($result, $role);
            }

            $data[] = $result;

            $result_area = $result_area->next_sibling();
            if (!$result_area) {
                break;
            }
        }

        return $data;
    }
}
