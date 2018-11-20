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
     * @param string $type
     * @param string|int $id
     * @param string $parserArea
     *
     * @return void
     */
	public function __construct($type, $id, $parserArea = '.js-scrollfix-bottom-rel')
    {
    	$this->_type = $type;
    	$this->_id = $id;
        $this->_url = $this->_myAnimeListUrl.'/'.$type.'/'.$id;
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

                $character[$character_index]['image'] = self::getCharacterImage($char_table);
                $character[$character_index]['id'] = self::getCharacterId($char_name_area);
                $character[$character_index]['name'] = self::getCharacterName($char_name_area);
                $character[$character_index]['role'] = self::getCharacterRole($char_name_area);

                // va name + role
                $va = [];
                $va_index = 0;
                $char_va_area = $char_table->find('td', 2);
                if ($char_va_area) {
                    $char_va_area = $char_va_area->find('table', 0);
                    foreach ($char_va_area->find('tr') as $each_va) {
                        $va_name_area = $each_va->find('td', 0);

                        $va[$va_index]['id'] = self::getCharacterVaId($va_name_area);
                        $va[$va_index]['name'] = self::getCharacterVaName($va_name_area);
                        $va[$va_index]['role'] = self::getCharacterVaRole($va_name_area);
                        $va[$va_index]['image'] = self::getCharacterVaImage($each_va);

                        $va_index++;
                    }
                    $character[$character_index]['va'] = $va;
                }

                $char_table = $char_table->next_sibling();
                if ($char_table->tag == 'br' || $char_table->tag == 'a' || $char_table->tag == 'h2' || $char_table->tag == 'div')
                    break;
                $character_index++;
            }
        }
        return $character;
    }

    /**
     * Get anime/manga character image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $char_table
     *
     * @return string
     */
    static private function getCharacterImage($char_table)
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
    static private function getCharacterId($char_name_area)
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
    static private function getCharacterName($char_name_area)
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
    static private function getCharacterRole($char_name_area)
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
    static private function getCharacterVaId($va_name_area)
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
    static private function getCharacterVaName($va_name_area)
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
    static private function getCharacterVaImage($each_va)
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
    static private function getCharacterVaRole($va_name_area)
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

                    $staff[$staff_index]['image'] = self::getStaffImage($staff_table);
                    $staff[$staff_index]['id'] = self::getStaffId($staff_name_area);
                    $staff[$staff_index]['name'] = self::getStaffName($staff_name_area);
                    $staff[$staff_index]['role'] = self::getStaffRole($staff_name_area);

                    $staff_table = $staff_table->next_sibling();
                    if (!$staff_table)
                        break;
                    $staff_index++;
                }
            }
        }
        return $staff;
    }

    /**
     * Get anime/manga staff image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $staff_table
     *
     * @return string
     */
    static private function getStaffImage($staff_table)
    {
        $staff_image = $staff_table->find('td .picSurround img', 0)->getAttribute('data-src');
        return Helper::imageUrlCleaner($staff_image);
    }

    /**
     * Get anime/manga staff id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $staff_name_area
     *
     * @return string
     */
    static private function getStaffId($staff_name_area)
    {
        $staff_id = $staff_name_area->find('a', 0)->href;
        $staff_id = explode('/', $staff_id);
        return $staff_id[4];
    }

    /**
     * Get anime/manga staff name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $staff_name_area
     *
     * @return string
     */
    static private function getStaffName($staff_name_area)
    {
        return $staff_name_area->find('a', 0)->plaintext;
    }

    /**
     * Get anime/manga staff role.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $staff_name_area
     *
     * @return string
     */
    static private function getStaffRole($staff_name_area)
    {
        return $staff_name_area->find('small', 0)->plaintext;
    }

    /**
     * Get anime/manga character + staff complete list.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'character' => self::getCharacter(),
            'staff'     => self::getStaff(),
        ];

        return $data;
    }
}