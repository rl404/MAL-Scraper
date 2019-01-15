<?php

namespace MalScraper\Model\Additional;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * CharacterStaffModel class.
 */
class CharacterStaffModel extends MainModel
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
        $this->_url = $this->_myAnimeListUrl.'/'.$type.'/'.$id.'/a/characters';
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
     * Get anime/manga character list.
     *
     * @return array
     */
    private function getCharacter()
    {
        $character = [];
        $character_index = 0;
        $char_table = $this->_parser->find('h2', 0);
        if ($char_table->next_sibling()->tag == 'table') {
            $char_table = $char_table->next_sibling();
            while (true) {
                $char_name_area = $char_table->find('td', 1);

                $character[$character_index]['image'] = $this->getCharacterImage($char_table);
                $character[$character_index]['id'] = $this->getCharacterId($char_name_area);
                $character[$character_index]['name'] = $this->getCharacterName($char_name_area);
                $character[$character_index]['role'] = $this->getCharacterRole($char_name_area);

                // va name + role
                $char_va_area = $char_table->find('td', 2);
                if ($char_va_area) {
                    $character[$character_index]['va'] = $this->getVa($char_va_area);
                }

                $char_table = $char_table->next_sibling();
                if ($char_table->tag == 'br' || $char_table->tag == 'a' || $char_table->tag == 'h2' || $char_table->tag == 'div') {
                    break;
                }
                $character_index++;
            }
        }

        return $character;
    }

    /**
     * Get anime/manga va character.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $char_va_area
     *
     * @return array
     */
    private function getVa($char_va_area)
    {
        $va = [];
        $va_index = 0;
        $char_va_area = $char_va_area->find('table', 0);
        foreach ($char_va_area->find('tr') as $each_va) {
            $va_name_area = $each_va->find('td', 0);

            $va[$va_index]['id'] = $this->getCharacterVaId($va_name_area);
            $va[$va_index]['name'] = $this->getCharacterVaName($va_name_area);
            $va[$va_index]['role'] = $this->getCharacterVaRole($va_name_area);
            $va[$va_index]['image'] = $this->getCharacterVaImage($each_va);

            $va_index++;
        }

        return $va;
    }

    /**
     * Get anime/manga character image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $char_table
     *
     * @return string
     */
    private function getCharacterImage($char_table)
    {
        $char_image = $char_table->find('td .picSurround img', 0)->getAttribute('data-src');

        return Helper::imageUrlCleaner($char_image);
    }

    /**
     * Get anime/manga character id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $char_name_area
     *
     * @return string
     */
    private function getCharacterId($char_name_area)
    {
        $char_id = $char_name_area->find('a', 0)->href;
        $char_id = explode('/', $char_id);

        return $char_id[4];
    }

    /**
     * Get anime/manga character name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $char_name_area
     *
     * @return string
     */
    private function getCharacterName($char_name_area)
    {
        return $char_name_area->find('a', 0)->plaintext;
    }

    /**
     * Get anime/manga character role.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $char_name_area
     *
     * @return string
     */
    private function getCharacterRole($char_name_area)
    {
        return $char_name_area->find('small', 0)->plaintext;
    }

    /**
     * Get anime/manga character va id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $va_name_area
     *
     * @return string
     */
    private function getCharacterVaId($va_name_area)
    {
        $va_id = $va_name_area->find('a', 0)->href;
        $va_id = explode('/', $va_id);

        return $va_id[4];
    }

    /**
     * Get anime/manga character va name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $va_name_area
     *
     * @return string
     */
    private function getCharacterVaName($va_name_area)
    {
        return $va_name_area->find('a', 0)->plaintext;
    }

    /**
     * Get anime/manga character va image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_va
     *
     * @return string
     */
    private function getCharacterVaImage($each_va)
    {
        $va_image = $each_va->find('td', 1)->find('img', 0)->getAttribute('data-src');

        return Helper::imageUrlCleaner($va_image);
    }

    /**
     * Get anime/manga character va role.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $va_name_area
     *
     * @return string
     */
    private function getCharacterVaRole($va_name_area)
    {
        return $va_name_area->find('small', 0)->plaintext;
    }

    /**
     * Get anime/manga staff list.
     *
     * @return array
     */
    private function getStaff()
    {
        $staff = [];
        $staff_index = 0;
        $staff_table = $this->_parser->find('h2', 1);
        if ($staff_table) {
            if ($staff_table->next_sibling()->tag == 'table') {
                $staff_table = $staff_table->next_sibling();
                while (true) {
                    $staff_name_area = $staff_table->find('td', 1);

                    $staff[$staff_index]['image'] = $this->getCharacterImage($staff_table);
                    $staff[$staff_index]['id'] = $this->getCharacterId($staff_name_area);
                    $staff[$staff_index]['name'] = $this->getCharacterName($staff_name_area);
                    $staff[$staff_index]['role'] = $this->getCharacterRole($staff_name_area);

                    $staff_table = $staff_table->next_sibling();
                    if (!$staff_table) {
                        break;
                    }
                    $staff_index++;
                }
            }
        }

        return $staff;
    }

    /**
     * Get anime/manga character + staff complete list.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'character' => $this->getCharacter(),
            'staff'     => $this->getStaff(),
        ];

        return $data;
    }
}
