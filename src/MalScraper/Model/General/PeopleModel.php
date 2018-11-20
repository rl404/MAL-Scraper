<?php

namespace MalScraper\Model\General;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * PeopleModel class.
 */
class PeopleModel extends MainModel
{
    /**
     * Id of the people.
     *
     * @var string|int
     */
	private $_id;

    /**
     * Biodata area.
     *
     * @var string
     */
    private $_biodata;

    /**
     * Default constructor.
     *
     * @param string|int $id
     * @param string $parserArea
     *
     * @return void
     */
	public function __construct($id, $parserArea = '#contentWrapper')
    {
    	$this->_id = $id;
        $this->_url = $this->_myAnimeListUrl.'/people/'.$id;
    	$this->_parserArea = $parserArea;

        parent::errorCheck($this);

        if (!$this->_error)
            self::setBiodata();
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
     * Get people id.
     *
     * @return string
     */
    private function getId()
    {
        return $this->_id;
    }

    /**
     * Get people name.
     *
     * @return string
     */
    private function getName()
    {
        return $this->_parser->find('h1', 0)->plaintext;
    }

    /**
     * Get people image.
     *
     * @return string
     */
    private function getImage()
    {
        $image = $this->_parser->find('#content table tr', 0)->find('td', 0)->find('img', 0);
        return $image ? $image->src : '';
    }

    /**
     * Set people biodata.
     *
     * @return void
     */
    private function setBiodata()
    {
        $html = $this->_parser->find('#content table tr', 0)->find('td', 0);
        $biodata = $html->innertext;
        $useless_biodata = '';
        $useless_area = $html->find('div', 0);
        for ($i = 0; $i < 4; $i++) {
            $useless_biodata .= $useless_area->outertext;
            $useless_area = $useless_area->next_sibling();
        }
        $biodata = str_replace($useless_biodata, '', $biodata);
        $this->_biodata = preg_replace("/([\s])+/", ' ', $biodata);
    }

    /**
     * Get people biodata.
     *
     * @param string $type Biodata type
     *
     * @return string|array
     */
    private function getBiodata($type)
    {
        switch ($type) {
            case 'given_name':
                preg_match("/(Given name:<\/span>)[^<]*/", $this->_biodata, $biodata);
                break;
            case 'family_name':
                preg_match("/(Family name:<\/span>)[^<]*/", $this->_biodata, $biodata);
                break;
            case 'alternative_name':
                preg_match("/(Alternate names:<\/span>)[^<]*/", $this->_biodata, $biodata);
                break;
            case 'birthday':
                preg_match("/(Birthday:<\/span>)([^<])*/", $this->_biodata, $biodata);
                break;
            case 'website':
                preg_match("/(Website:<\/span> <a)([^<])*/", $this->_biodata, $biodata);
                break;
            case 'favorite':
                preg_match("/(Member Favorites:<\/span>)([^<])*/", $this->_biodata, $biodata);
                break;
            default:
                return null;
        }

        if ($biodata) {
            if ($type != 'website') {
                $biodata = strip_tags($biodata[0]);
                $biodata = explode(': ', $biodata);
                $biodata = trim($biodata[1]);
            }

            if ($type == 'given_name' || $type == 'family_name' || $type == 'birthday')
                return $biodata;

            if ($type == 'alternative_name')
                return explode(', ', $biodata);

            if ($type == 'favorite')
                return str_replace(',', '', $biodata);

            if ($type == 'website') {
                preg_match('/".+"/', $biodata[0], $biodata);
                if ($biodata[0] != '"http://"')
                    return str_replace('"', '', $biodata[0]);
            }
        }
        return null;
    }

    /**
     * Get people more information.
     *
     * @return string
     */
    private function getMore()
    {
        $more = $this->_parser->find('#content table tr', 0)->find('td', 0);
        $more = $more->find('div[class^=people-informantion-more]', 0)->plaintext;
        return preg_replace('/\n[^\S\n]*/', "\n", $more);
    }

    /**
     * Get people voice actor list.
     *
     * @return array
     */
    private function getVa()
    {
        $va = [];
        $va_index = 0;
        $html = $this->_parser->find('#content table tr', 0)->find('td', 0)->next_sibling();
        $va_area = $html->find('.normal_header', 0)->next_sibling();
        if ($va_area->tag == 'table') {
            if ($va_area->find('tr')) {
                foreach ($va_area->find('tr') as $each_va) {
                    // anime image
                    $anime_image = $each_va->find('td', 0)->find('img', 0)->getAttribute('data-src');
                    $va[$va_index]['anime']['image'] = Helper::imageUrlCleaner($anime_image);

                    $anime_area = $each_va->find('td', 1);

                    // anime id
                    $anime_id = $anime_area->find('a', 0)->href;
                    $parsed_anime_id = explode('/', $anime_id);
                    $anime_id = $parsed_anime_id[4];
                    $va[$va_index]['anime']['id'] = $anime_id;

                    // anime title
                    $anime_title = $anime_area->find('a', 0)->plaintext;
                    $va[$va_index]['anime']['title'] = $anime_title;

                    // character image
                    $character_image = $each_va->find('td', 3)->find('img', 0)->getAttribute('data-src');
                    $va[$va_index]['character']['image'] = Helper::imageUrlCleaner($character_image);

                    $character_area = $each_va->find('td', 2);

                    // character id
                    $character_id = $character_area->find('a', 0)->href;
                    $parsed_character_id = explode('/', $character_id);
                    $character_id = $parsed_character_id[4];
                    $va[$va_index]['character']['id'] = $character_id;

                    // character name
                    $character_name = $character_area->find('a', 0)->plaintext;
                    $va[$va_index]['character']['name'] = $character_name;

                    // character role
                    $character_role = $character_area->find('div', 0)->plaintext;
                    $va[$va_index]['character']['role'] = $character_role;

                    $va_index++;
                }
            }
        }
        return $va;
    }

    /**
     * Get people staff list.
     *
     * @return array
     */
    private function getStaff()
    {
        $staff = [];
        $staff_index = 0;
        $html = $this->_parser->find('#content table tr', 0)->find('td', 0)->next_sibling();
        $staff_area = $html->find('.normal_header', 1)->next_sibling();
        if ($staff_area->tag == 'table') {
            foreach ($staff_area->find('tr') as $each_staff) {
                $anime_image = $each_staff->find('td', 0)->find('img', 0)->getAttribute('data-src');
                $staff[$staff_index]['image'] = Helper::imageUrlCleaner($anime_image);

                $each_staff = $each_staff->find('td', 1);

                // anime id
                $anime_id = $each_staff->find('a', 0)->href;
                $parsed_anime_id = explode('/', $anime_id);
                $anime_id = $parsed_anime_id[4];
                $staff[$staff_index]['id'] = $anime_id;

                // anime title
                $anime_title = $each_staff->find('a', 0)->plaintext;
                $staff[$staff_index]['title'] = $anime_title;

                // role
                $role = $each_staff->find('small', 0)->plaintext;
                $staff[$staff_index]['role'] = $role;

                $staff_index++;
            }
        }
        return $staff;
    }

    /**
     * Get people published manga list.
     *
     * @return array
     */
    private function getManga()
    {
        $published_manga = [];
        $manga_index = 0;
        $html = $this->_parser->find('#content table tr', 0)->find('td', 0)->next_sibling();
        $manga_area = $html->find('.normal_header', 2)->next_sibling();
        if ($manga_area->tag == 'table') {
            foreach ($manga_area->find('tr') as $each_manga) {
                $manga_image = $each_manga->find('td', 0)->find('img', 0)->getAttribute('data-src');
                $published_manga[$manga_index]['image'] = Helper::imageUrlCleaner($manga_image);

                $each_manga = $each_manga->find('td', 1);

                // manga id
                $manga_id = $each_manga->find('a', 0)->href;
                $parsed_manga_id = explode('/', $manga_id);
                $manga_id = $parsed_manga_id[4];
                $published_manga[$manga_index]['id'] = $manga_id;

                // manga title
                $manga_title = $each_manga->find('a', 0)->plaintext;
                $published_manga[$manga_index]['title'] = $manga_title;

                // role
                $role = $each_manga->find('small', 0)->plaintext;
                $published_manga[$manga_index]['role'] = $role;

                $manga_index++;
            }
        }
        return $published_manga;
    }

    /**
     * Get people all information.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'id'               => self::getId(),
            'name'             => self::getName(),
            'image'            => self::getImage(),
            'given_name'       => self::getBiodata('given_name'),
            'family_name'      => self::getBiodata('family_name'),
            'alternative_name' => self::getBiodata('alternative_name'),
            'birthday'         => self::getBiodata('birthday'),
            'website'          => self::getBiodata('website'),
            'favorite'         => self::getBiodata('favorite'),
            'more'             => self::getMore(),
            'va'               => self::getVa(),
            'staff'            => self::getStaff(),
            'published_manga'  => self::getManga(),
        ];

        return $data;
    }
}