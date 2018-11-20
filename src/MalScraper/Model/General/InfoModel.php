<?php

namespace MalScraper\Model\General;

use MalScraper\Helper\Helper;
use MalScraper\Model\MainModel;

/**
 * InfoModel class.
 */
class InfoModel extends MainModel
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
	public function __construct($type, $id, $parserArea = '#content')
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
     * Get anime/manga id.
     *
     * @return string
     */
    private function getId()
    {
    	return $this->_id;
    }

    /**
     * Get anime/manga cover.
     *
     * @return string
     */
    private function getCover()
    {
        $anime_cover = $this->_parser->find('img.ac', 0);
        return $anime_cover ? $anime_cover->src : '';
    }

    /**
     * Get anime/manga title.
     *
     * @return string
     */
    private function getTitle()
    {
        $anime_cover = $this->_parser->find('img.ac', 0);
        return $anime_cover ? $anime_cover->alt : '';
    }

    /**
     * Get anime/manga alternative title.
     *
     * @return array
     */
    private function getTitle2()
    {
        $title2 = [];

        $anime_info = $this->_parser->find('.js-scrollfix-bottom', 0);

        preg_match('/(English:<\/span>)([^<]*)/', $anime_info->innertext, $english);
        $title2['english'] = trim($english ? $english[2] : '');

        preg_match('/(Synonyms:<\/span>)([^<]*)/', $anime_info->innertext, $synonym);
        $title2['synonym'] = trim($synonym ? $synonym[2] : '');

        preg_match('/(Japanese:<\/span>)([^<]*)/', $anime_info->innertext, $japanese);
        $title2['japanese'] = trim($japanese ? $japanese[2] : '');

        return $title2;
    }

    /**
     * Get anime/manga synopsis.
     *
     * @return string
     */
    private function getSynopsis()
    {
        $synopsis = $this->_parser->find('span[itemprop=description]', 0);
        if ($synopsis) {
            $synopsis = $synopsis->plaintext;
            return trim(preg_replace('/\n[^\S\n]*/', "\n", $synopsis));
        } else {
            return null;
        }
    }

    /**
     * Get anime/manga score.
     *
     * @return string
     */
    private function getScore()
    {
        $score = $this->_parser->find('div[class="fl-l score"]', 0)->plaintext;
        $score = trim($score);
        return $score != 'N/A' ? $score : null;
    }

    /**
     * Get number of user who give score.
     *
     * @return string
     */
    private function getVoter()
    {
        $voter = $this->_parser->find('div[class="fl-l score"]', 0)->getAttribute('data-user');
        return trim(str_replace(['users', 'user', ','], '', $voter));
    }

    /**
     * Get anime/manga rank.
     *
     * @return string
     */
    private function getRank()
    {
        $rank = $this->_parser->find('span[class="numbers ranked"] strong', 0)->plaintext;
        $rank = $rank != 'N/A' ? $rank : '';
        return str_replace('#', '', $rank);
    }

    /**
     * Get anime/manga popularity.
     *
     * @return string
     */
    private function getPopularity()
    {
        $popularity = $this->_parser->find('span[class="numbers popularity"] strong', 0)->plaintext;
        return str_replace('#', '', $popularity);
    }

    /**
     * Get number of user who watch/read the anime/manga.
     *
     * @return string
     */
    private function getMembers()
    {
        $member = $this->_parser->find('span[class="numbers members"] strong', 0)->plaintext;
        return str_replace(',', '', $member);
    }

    /**
     * Get number of user who favorite the anime/manga.
     *
     * @return string
     */
    private function getFavorite()
    {
        $favorite = $this->_parser->find('div[data-id=info2]', 0)->next_sibling()->next_sibling()->next_sibling();
        $favorite_title = $favorite->find('span', 0)->plaintext;
        $favorite = $favorite->plaintext;
        $favorite = trim(str_replace($favorite_title, '', $favorite));
        $favorite = str_replace(',', '', $favorite);
        return preg_replace("/([\s])+/", ' ', $favorite);
    }

    /**
     * Get anime/manga detail info.
     *
     * @return array
     */
    private function getOtherInfo()
    {
        $info = [];

        $anime_info = $this->_parser->find('.js-scrollfix-bottom', 0);
        $other_info = (count($anime_info->find('h2')) > 2) ? $anime_info->find('h2', 1) : $anime_info->find('h2', 0);
        $next_info = $other_info->next_sibling();
        while (true) {
            $info_type = $next_info->find('span', 0)->plaintext;
            $clean_info_type = strtolower(str_replace(': ', '', $info_type));

            $info_value = $next_info->plaintext;
            $clean_info_value = trim(str_replace($info_type, '', $info_value));
            $clean_info_value = preg_replace("/([\s])+/", ' ', $clean_info_value);
            $clean_info_value = str_replace([', add some', '?', 'Not yet aired', 'Unknown'], '', $clean_info_value);

            if ($clean_info_type == 'published' || $clean_info_type == 'aired') {
                $start_air = '';
                $end_air = '';
                if ($clean_info_value != 'Not available') {
                    $parsed_airing = explode(' to ', $clean_info_value);

                    $start_air = ($parsed_airing[0] != '?') ? date('Y-m-d', strtotime($parsed_airing[0])) : '';
                    if (count($parsed_airing) > 1) {
                        $end_air = ($parsed_airing[1] != '?') ? date('Y-m-d', strtotime($parsed_airing[1])) : '';
                    }
                }

                $clean_info_value = [];
                $clean_info_value['start'] = $start_air;
                $clean_info_value['end'] = $end_air;
            }

            if ($clean_info_type == 'producers'
                || $clean_info_type == 'licensors'
                || $clean_info_type == 'studios'
                || $clean_info_type == 'genres'
                || $clean_info_type == 'authors'
            ) {
                $info_temp = [];
                $info_temp_index = 0;
                if ($clean_info_value != 'None found') {
                    foreach ($next_info->find('a') as $each_info) {
                        $temp_id = explode('/', $each_info->href);
                        $info_temp[$info_temp_index]['id'] = $clean_info_type == 'authors' ? $temp_id[2] : $temp_id[3];
                        $info_temp[$info_temp_index]['name'] = $each_info->plaintext;
                        $info_temp_index++;
                    }
                }
                $clean_info_value = $info_temp;
            }

            $info[$clean_info_type] = $clean_info_value;

            $next_info = $next_info->next_sibling();
            if ($next_info->tag == 'h2' || $next_info->tag == 'br') {
                break;
            }
        }
        return $info;
    }

    /**
     * Get anime/manga relation.
     *
     * @return array
     */
    private function getRelated()
    {
        $related = [];
        $related_area = $this->_parser->find('.anime_detail_related_anime', 0);
        if ($related_area) {
            foreach ($related_area->find('tr') as $rel) {
                $rel_type = $rel->find('td', 0)->plaintext;
                $rel_type = trim(strtolower(str_replace(':', '', $rel_type)));

                $each_rel = [];
                $each_rel_index = 0;
                $rel_anime = $rel->find('td', 1);
                foreach ($rel_anime->find('a') as $r) {
                    $rel_anime_link = $r->href;
                    $separated_anime_link = explode('/', $rel_anime_link);

                    $each_rel[$each_rel_index]['id'] = $separated_anime_link[2];
                    $each_rel[$each_rel_index]['title'] = $r->plaintext;
                    $each_rel[$each_rel_index]['type'] = $separated_anime_link[1];

                    $each_rel_index++;
                }

                $related[$rel_type] = $each_rel;
            }
        }
        return $related;
    }

    /**
     * Get anime/manga character and its va.
     *
     * @return array
     */
    private function getCharacter()
    {
        $character = [];
        $char_index = 0;
        $character_area = $this->_parser->find('div[class^=detail-characters-list]', 0);
        if ($character_area) {
            $character_list = [
                $character_area->find('div[class*=fl-l]', 0),
                $character_area->find('div[class*=fl-r]', 0)
            ];
            foreach ($character_list as $character_side) {
                if ($character_side) {
                    foreach ($character_side->find('table[width=100%]') as $each_char) {
                        $char_image = $each_char->find('tr td', 0)->find('img', 0)->getAttribute('data-src');
                        $char_image = Helper::imageUrlCleaner($char_image);

                        $char = $each_char->find('tr td', 1);

                        $char_id = $char->find('a', 0)->href;
                        $char_id = explode('/', $char_id);
                        $char_id = $char_id[4];

                        $char_name = trim(preg_replace('/\s+/', ' ', $char->find('a', 0)->plaintext));
                        $char_role = trim($char->find('small', 0)->plaintext);

                        $character[$char_index]['id'] = $char_id;
                        $character[$char_index]['name'] = $char_name;
                        $character[$char_index]['role'] = $char_role;
                        $character[$char_index]['image'] = $char_image;

                        $va = $each_char->find('table td', 0);
                        if ($va) {
                            $va_id = $va->find('a', 0)->href;
                            $va_id = explode('/', $va_id);
                            $va_id = $va_id[4];

                            $va_name = $va->find('a', 0)->plaintext;
                            $va_role = $va->find('small', 0)->plaintext;

                            $va_image = $each_char->find('table td', 1)->find('img', 0)->getAttribute('data-src');
                            $va_image = Helper::imageUrlCleaner($va_image);
                        }

                        $character[$char_index]['va_id'] = isset($va_id) ? $va_id : '';
                        $character[$char_index]['va_name'] = isset($va_name) ? $va_name : '';
                        $character[$char_index]['va_role'] = isset($va_role) ? $va_role : '';
                        $character[$char_index]['va_image'] = isset($va_image) ? $va_image : '';

                        $char_index++;
                    }
                }
            }
        }
        return $character;
    }

    /**
     * Get anime/manga staff involved.
     *
     * @return array
     */
    private function getStaff()
    {
        $staff = [];
        $staff_index = 0;
        $staff_area = $this->_parser->find('div[class^=detail-characters-list]', 1);
        if ($staff_area) {
            $staff_list = [
                $staff_area->find('div[class*=fl-l]', 0),
                $staff_area->find('div[class*=fl-r]', 0)
            ];
            foreach ($staff_list as $staff_side) {
                if ($staff_side) {
                    foreach ($staff_side->find('table[width=100%]') as $each_staff) {
                        $staff_image = $each_staff->find('tr td', 0)->find('img', 0)->getAttribute('data-src');
                        $staff_image = Helper::imageUrlCleaner($staff_image);

                        $st = $each_staff->find('tr td', 1);

                        $staff_id = $st->find('a', 0)->href;
                        $staff_id = explode('/', $staff_id);
                        $staff_id = $staff_id[4];

                        $staff_name = trim(preg_replace('/\s+/', ' ', $st->find('a', 0)->plaintext));
                        $staff_role = trim($st->find('small', 0)->plaintext);

                        $staff[$staff_index]['id'] = $staff_id;
                        $staff[$staff_index]['name'] = $staff_name;
                        $staff[$staff_index]['role'] = $staff_role;
                        $staff[$staff_index]['image'] = $staff_image;

                        $staff_index++;
                    }
                }
            }
        }
        return $staff;
    }

    /**
     * Get anime/manga opening and ending song.
     *
     * @return array
     */
    private function getSong()
    {
        $song = [];
        $song_area = $this->_parser->find('div[class*="theme-songs opnening"]', 0);
        if ($song_area) {
            foreach ($song_area->find('span.theme-song') as $each_song) {
                $each_song = trim(preg_replace('/#\d*:\s/', '', $each_song->plaintext));
                $song['opening'][] = $each_song;
            }
        }

        $song_area = $this->_parser->find('div[class*="theme-songs ending"]', 0);
        if ($song_area) {
            foreach ($song_area->find('span.theme-song') as $each_song) {
                $each_song = trim(preg_replace('/#\d*:\s/', '', $each_song->plaintext));
                $song['closing'][] = $each_song;
            }
        }
        return $song;
    }

    /**
     * Get anime/manga all information.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'id'        => self::getId(),
            'cover'     => self::getCover(),
            'title'     => self::getTitle(),
            'title2'    => self::getTitle2(),
            'synopsis'  => self::getSynopsis(),
            'score'     => self::getScore(),
            'voter'     => self::getVoter(),
            'rank'      => self::getRank(),
            'popularity'=> self::getPopularity(),
            'members'   => self::getMembers(),
            'favorite'  => self::getFavorite(),
        ];

        $data = array_merge($data, self::getOtherInfo());

        $data2 = [
            'related'   => self::getRelated(),
            'character' => self::getCharacter(),
            'staff'     => self::getStaff(),
            'song'      => self::getSong()
        ];

        $data = array_merge($data, $data2);

    	return $data;
    }
}