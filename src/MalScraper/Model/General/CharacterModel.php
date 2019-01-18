<?php

namespace MalScraper\Model\General;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * CharacterModel class.
 */
class CharacterModel extends MainModel
{
    /**
     * Id of the character.
     *
     * @var string|int
     */
    private $_id;

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
        $this->_url = $this->_myAnimeListUrl.'/character/'.$id;
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
     * Get character id.
     *
     * @return string
     */
    private function getId()
    {
        return $this->_id;
    }

    /**
     * Get character image.
     *
     * @return string|bool
     */
    private function getImage()
    {
        $image = $this->_parser->find('#content table tr', 0);
        $image = $image->find('td', 0)->find('div', 0)->find('a', 0)->find('img', 0);

        return $image ? $image->src : '';
    }

    /**
     * Get character image.
     *
     * @return string
     */
    private function getNickname()
    {
        $nickname = $this->_parser->find('h1', 0)->plaintext;
        $nickname = trim(preg_replace('/\s+/', ' ', $nickname));
        preg_match('/\"([^"])*/', $nickname, $nick);
        if ($nick) {
            return substr($nick[0], 1, strlen($nick[0]) - 2);
        }

        return '';
    }

    /**
     * Get character name.
     *
     * @param bool $isKanji
     *
     * @return string
     */
    private function getName($isKanji = false)
    {
        $html = $this->_parser->find('#content table tr', 0);
        $html = $html->find('td', 0)->next_sibling()->find('div[class=normal_header]', 0);

        $name_kanji = $html->find('small', 0);
        $name_kanji = $name_kanji ? $name_kanji->plaintext : '';

        if ($isKanji) {
            return preg_replace('/(\(|\))/', '', $name_kanji);
        }

        return trim(str_replace($name_kanji, '', $html->plaintext));
    }

    /**
     * Get number of user who favorite the character.
     *
     * @return string
     */
    private function getFavorite()
    {
        $favorite = $this->_parser->find('#content table tr', 0)->find('td', 0)->plaintext;
        preg_match('/(Member Favorites: ).+/', $favorite, $parsed_favorite);
        $favorite = trim($parsed_favorite[0]);
        $parsed_favorite = explode(': ', $favorite);

        return str_replace(',', '', $parsed_favorite[1]);
    }

    /**
     * Get character about.
     *
     * @return string
     */
    private function getAbout()
    {
        $html = $this->_parser->find('#content table tr', 0)->find('td', 0)->next_sibling();

        preg_match('/(<div class="normal_header" style="height: 15px;">).*(<div class="normal_header">)/', $html, $about);

        $html = $html->find('div[class=normal_header]', 0);
        $about = str_replace($html->outertext, '', $about[0]);
        $about = str_replace('<div class="normal_header">', '', $about);

        preg_match('/(No biography written)/', $about, $temp_about);
        if (!$temp_about) {
            $about = str_replace(['<br>', '<br />', '  '], ["\n", "\n", ' '], $about);
            $about = strip_tags($about);

            return preg_replace('/\n[^\S\n]*/', "\n", $about);
        } else {
            return;
        }
    }

    /**
     * Get character role in anime/manga.
     *
     * @param string $type Either anime or manga
     *
     * @return array
     */
    private function getMedia($type = 'anime')
    {
        $mediaography = [];
        $mediaography_index = 0;

        $html = $this->_parser->find('#content table tr', 0)->find('td', 0);
        $mediaography_area = $type == 'anime' ? $html->find('table', 0) : $html->find('table', 1);
        if ($mediaography_area) {
            $mediaography_area = $mediaography_area->find('tr');
            foreach ($mediaography_area as $each_media) {
                $media_image = $each_media->find('td', 0);
                $media_area = $each_media->find('td', 1);

                $mediaography[$mediaography_index]['image'] = $this->getVaImage($media_image);
                $mediaography[$mediaography_index]['id'] = $this->getVaId($media_area);
                $mediaography[$mediaography_index]['title'] = $this->getVaName($media_area);
                $mediaography[$mediaography_index]['role'] = $this->getVaRole($media_area);

                $mediaography_index++;
            }
        }

        return $mediaography;
    }

    /**
     * Get character voice actor list.
     *
     * @return array
     */
    private function getVa()
    {
        $va = [];
        $va_index = 0;
        $html = $this->_parser->find('#content table tr', 0)->find('td', 0)->next_sibling();
        $va_area = $html->find('div[class=normal_header]', 1)->next_sibling();
        if ($va_area->tag == 'table') {
            while (true) {
                $va_name_area = $va_area->find('td', 1);
                $va[$va_index]['id'] = $this->getVaId($va_name_area);
                $va[$va_index]['name'] = $this->getVaName($va_name_area);
                $va[$va_index]['role'] = $this->getVaRole($va_name_area);
                $va[$va_index]['image'] = $this->getVaImage($va_area);

                $va_area = $va_area->next_sibling();
                if ($va_area->tag != 'table') {
                    break;
                } else {
                    $va_index++;
                }
            }
        }

        return $va;
    }

    /**
     * Get Va Id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $va_name_area
     *
     * @return string
     */
    private function getVaId($va_name_area)
    {
        $va_id = $va_name_area->find('a', 0)->href;
        $parsed_va_id = explode('/', $va_id);

        return $parsed_va_id[4];
    }

    /**
     * Get Va Name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $va_name_area
     *
     * @return string
     */
    private function getVaName($va_name_area)
    {
        return $va_name_area->find('a', 0)->plaintext;
    }

    /**
     * Get Va role.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $va_name_area
     *
     * @return string
     */
    private function getVaRole($va_name_area)
    {
        return $va_name_area->find('div small', 0)->plaintext;
    }

    /**
     * Get Va image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $va_area
     *
     * @return string
     */
    private function getVaImage($va_area)
    {
        $va_image = $va_area->find('img', 0)->src;

        return Helper::imageUrlCleaner($va_image);
    }

    /**
     * Get character all information.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'id'           => $this->getId(),
            'image'        => $this->getImage(),
            'nickname'     => $this->getNickname(),
            'name'         => $this->getName(),
            'name_kanji'   => $this->getName(true),
            'favorite'     => $this->getFavorite(),
            'about'        => $this->getAbout(),
            'animeography' => $this->getMedia('anime'),
            'mangaography' => $this->getMedia('manga'),
            'va'           => $this->getVa(),
        ];

        return $data;
    }
}
