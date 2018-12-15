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
     * @param string     $parserArea
     *
     * @return void
     */
    public function __construct($id, $parserArea = '#contentWrapper')
    {
        $this->_id = $id;
        $this->_url = $this->_myAnimeListUrl.'/people/'.$id;
        $this->_parserArea = $parserArea;

        parent::errorCheck($this);

        if (!$this->_error) {
            $this->setBiodata();
        }
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
     * @return string|bool
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
        if ($type == 'Website') {
            preg_match('/('.$type.":<\/span> <a)[^<]*/", $this->_biodata, $biodata);
            if ($biodata) {
                preg_match('/".+"/', $biodata[0], $biodata);
                if ($biodata[0] != '"http://"') {
                    return str_replace('"', '', $biodata[0]);
                }
            }
        }

        preg_match('/('.$type.":<\/span>)[^<]*/", $this->_biodata, $biodata);

        if ($biodata) {
            $biodata = strip_tags($biodata[0]);
            $biodata = explode(': ', $biodata);
            $biodata = trim($biodata[1]);

            if ($type == 'Alternate names') {
                return explode(', ', $biodata);
            }

            if ($type == 'Member Favorites') {
                return str_replace(',', '', $biodata);
            }

            return $biodata;
        }
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

                    // anime
                    $anime_image_area = $each_va->find('td', 0);
                    $anime_area = $each_va->find('td', 1);

                    $va[$va_index]['anime']['image'] = $this->getAnimeImage($anime_image_area);
                    $va[$va_index]['anime']['id'] = $this->getAnimeId($anime_area);
                    $va[$va_index]['anime']['title'] = $this->getAnimeTitle($anime_area);

                    // character
                    $character_image_area = $each_va->find('td', 3);
                    $character_area = $each_va->find('td', 2);

                    $va[$va_index]['character']['image'] = $this->getAnimeImage($character_image_area);
                    $va[$va_index]['character']['id'] = $this->getAnimeId($character_area);
                    $va[$va_index]['character']['name'] = $this->getAnimeTitle($character_area);
                    $va[$va_index]['character']['role'] = $this->getAnimeRole($character_area);

                    $va_index++;
                }
            }
        }

        return $va;
    }

    /**
     * Get anime id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $anime_area
     *
     * @return string
     */
    private function getAnimeId($anime_area)
    {
        $anime_id = $anime_area->find('a', 0)->href;
        $parsed_anime_id = explode('/', $anime_id);

        return $parsed_anime_id[4];
    }

    /**
     * Get anime title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $anime_area
     *
     * @return string
     */
    private function getAnimeTitle($anime_area)
    {
        return $anime_area->find('a', 0)->plaintext;
    }

    /**
     * Get anime image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $anime_image_area
     *
     * @return string
     */
    private function getAnimeImage($anime_image_area)
    {
        $anime_image_area = $anime_image_area->find('img', 0)->getAttribute('data-src');

        return Helper::imageUrlCleaner($anime_image_area);
    }

    /**
     * Get anime role.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $anime_area
     * @param bool                               $staff      (Optional)
     *
     * @return string
     */
    private function getAnimeRole($anime_area, $staff = false)
    {
        if ($staff) {
            return $anime_area->find('small', 0)->plaintext;
        }

        return $anime_area->find('div', 0)->plaintext;
    }

    /**
     * Get people staff list.
     *
     * @param bool $staff (Optional)
     *
     * @return array
     */
    private function getStaff($manga = false)
    {
        $staff = [];
        $staff_index = 0;
        $html = $this->_parser->find('#content table tr', 0)->find('td', 0)->next_sibling();
        if ($manga) {
            $staff_area = $html->find('.normal_header', 2)->next_sibling();
        } else {
            $staff_area = $html->find('.normal_header', 1)->next_sibling();
        }
        if ($staff_area->tag == 'table') {
            foreach ($staff_area->find('tr') as $each_staff) {
                $anime_image_area = $each_staff->find('td', 0);
                $staff_area = $each_staff->find('td', 1);

                $staff[$staff_index]['image'] = $this->getAnimeImage($anime_image_area);
                $staff[$staff_index]['id'] = $this->getAnimeId($staff_area);
                $staff[$staff_index]['title'] = $this->getAnimeTitle($staff_area);
                $staff[$staff_index]['role'] = $this->getAnimeRole($staff_area, true);

                $staff_index++;
            }
        }

        return $staff;
    }

    /**
     * Get people all information.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'id'               => $this->getId(),
            'name'             => $this->getName(),
            'image'            => $this->getImage(),
            'given_name'       => $this->getBiodata('Given name'),
            'family_name'      => $this->getBiodata('Family name'),
            'alternative_name' => $this->getBiodata('Alternate names'),
            'birthday'         => $this->getBiodata('Birthday'),
            'website'          => $this->getBiodata('Website'),
            'favorite'         => $this->getBiodata('Member Favorites'),
            'more'             => $this->getMore(),
            'va'               => $this->getVa(),
            'staff'            => $this->getStaff(),
            'published_manga'  => $this->getStaff(true),
        ];

        return $data;
    }
}
