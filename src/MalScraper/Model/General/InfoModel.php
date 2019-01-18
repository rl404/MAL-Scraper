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
     * @param string     $type
     * @param string|int $id
     * @param string     $parserArea
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
        if ($this->_error) {
            return $this->_error;
        }

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
     * @return string|bool
     */
    private function getCover()
    {
        $anime_cover = $this->_parser->find('img.ac', 0);

        return $anime_cover ? $anime_cover->src : '';
    }

    /**
     * Get anime/manga title.
     *
     * @return string|bool
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

        $title2['english'] = $this->getTitle3($anime_info, 'English');
        $title2['synonym'] = $this->getTitle3($anime_info, 'Synonyms');
        $title2['japanese'] = $this->getTitle3($anime_info, 'Japanese');

        return $title2;
    }

    /**
     * Get anime/manga alternative title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $anime_info
     * @param string                             $type
     *
     * @return string
     */
    private function getTitle3($anime_info, $type)
    {
        preg_match('/('.$type.':<\/span>)([^<]*)/', $anime_info->innertext, $title);

        return trim($title ? $title[2] : '');
    }

    /**
     * Get anime/manga promotional video.
     *
     * @return string
     */
    private function getVideo()
    {
        $video_area = $this->_parser->find('.video-promotion', 0);
        if ($video_area) {
            $video = $video_area->find('a', 0)->href;

            return Helper::videoUrlCleaner($video);
        }

        return '';
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
            return;
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
        if ($other_info) {
            $next_info = $other_info->next_sibling();
            while (true) {
                $info_type = $next_info->find('span', 0)->plaintext;

                $clean_info_type = strtolower(str_replace(': ', '', $info_type));
                $clean_info_value = $this->getCleanInfo($info_type, $next_info);
                $clean_info_value = $this->getCleanerInfo1($clean_info_type, $clean_info_value);
                $clean_info_value = $this->getCleanerInfo2($next_info, $clean_info_type, $clean_info_value);

                $info[$clean_info_type] = $clean_info_value;

                $next_info = $next_info->next_sibling();
                if ($next_info->tag == 'h2' || $next_info->tag == 'br') {
                    break;
                }
            }
        }

        return $info;
    }

    /**
     * Get clean other info.
     *
     * @param string                             $info_type
     * @param \simplehtmldom_1_5\simple_html_dom $next_info
     *
     * @return string
     */
    private function getCleanInfo($info_type, $next_info)
    {
        $info_value = $next_info->plaintext;
        $clean_info_value = trim(str_replace($info_type, '', $info_value));
        $clean_info_value = preg_replace("/([\s])+/", ' ', $clean_info_value);

        return str_replace([', add some', '?', 'Not yet aired', 'Unknown'], '', $clean_info_value);
    }

    /**
     * Get cleaner other info.
     *
     * @param string $clean_info_type
     * @param string $clean_info_value
     *
     * @return string|array
     */
    private function getCleanerInfo1($clean_info_type, $clean_info_value)
    {
        if ($clean_info_type == 'published' || $clean_info_type == 'aired') {
            $start_air = $end_air = '';
            if ($clean_info_value != 'Not available') {
                $parsed_airing = explode(' to ', $clean_info_value);
                $start_air = ($parsed_airing[0] != '?') ? $parsed_airing[0] : '';
                if (count($parsed_airing) > 1) {
                    $end_air = ($parsed_airing[1] != '?') ? $parsed_airing[1] : '';
                }
            }

            $clean_info_value = [];
            $clean_info_value['start'] = $start_air;
            $clean_info_value['end'] = $end_air;
        }

        return $clean_info_value;
    }

    /**
     * Get cleaner other info.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $next_info
     * @param string                             $clean_info_type
     * @param string|array                       $clean_info_value
     *
     * @return string|array
     */
    private function getCleanerInfo2($next_info, $clean_info_type, $clean_info_value)
    {
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

            return $info_temp;
        }

        return $clean_info_value;
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
                    $each_rel[$each_rel_index] = $this->getRelatedDetail($r);
                    $each_rel_index++;
                }

                $related[$rel_type] = $each_rel;
            }
        }

        return $related;
    }

    /**
     * Get related detail.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $r
     *
     * @return array
     */
    private function getRelatedDetail($r)
    {
        $related = [];
        $rel_anime_link = $r->href;
        $separated_anime_link = explode('/', $rel_anime_link);

        $related['id'] = $separated_anime_link[2];
        $related['title'] = $r->plaintext;
        $related['type'] = $separated_anime_link[1];

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
                $character_area->find('div[class*=fl-r]', 0),
            ];
            foreach ($character_list as $character_side) {
                if ($character_side) {
                    foreach ($character_side->find('table[width=100%]') as $each_char) {
                        $char = $each_char->find('tr td', 1);
                        $va = $each_char->find('table td', 0);

                        $character[$char_index]['id'] = $this->getStaffId($char);
                        $character[$char_index]['name'] = $this->getStaffName($char);
                        $character[$char_index]['role'] = $this->getStaffRole($char);
                        $character[$char_index]['image'] = $this->getStaffImage($each_char);

                        $character[$char_index]['va_id'] = $character[$char_index]['va_name'] = '';
                        $character[$char_index]['va_role'] = $character[$char_index]['va_image'] = '';

                        if ($va) {
                            $character[$char_index]['va_id'] = $this->getStaffId($va);
                            $character[$char_index]['va_name'] = $this->getStaffName($va, true);
                            $character[$char_index]['va_role'] = $this->getStaffRole($va);
                            $character[$char_index]['va_image'] = $this->getStaffImage($each_char, true);
                        }

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
                $staff_area->find('div[class*=fl-r]', 0),
            ];
            foreach ($staff_list as $staff_side) {
                if ($staff_side) {
                    foreach ($staff_side->find('table[width=100%]') as $each_staff) {
                        $st = $each_staff->find('tr td', 1);

                        $staff[$staff_index]['id'] = $this->getStaffId($st);
                        $staff[$staff_index]['name'] = $this->getStaffName($st);
                        $staff[$staff_index]['role'] = $this->getStaffRole($st);
                        $staff[$staff_index]['image'] = $this->getStaffImage($each_staff);

                        $staff_index++;
                    }
                }
            }
        }

        return $staff;
    }

    /**
     * Get staff id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $st
     *
     * @return string
     */
    private function getStaffId($st)
    {
        $staff_id = $st->find('a', 0)->href;
        $staff_id = explode('/', $staff_id);

        return $staff_id[4];
    }

    /**
     * Get staff name.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $st
     * @param bool                               $va (Optional)
     *
     * @return string
     */
    private function getStaffName($st, $va = false)
    {
        if ($va) {
            return $st->find('a', 0)->plaintext;
        }

        return trim(preg_replace('/\s+/', ' ', $st->find('a', 0)->plaintext));
    }

    /**
     * Get staff role.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $st
     *
     * @return string
     */
    private function getStaffRole($st)
    {
        return trim($st->find('small', 0)->plaintext);
    }

    /**
     * Get staff image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_staff
     * @param bool                               $va         (Optional)
     *
     * @return string
     */
    private function getStaffImage($each_staff, $va = false)
    {
        if ($va) {
            $staff_image = $each_staff->find('table td', 1)->find('img', 0)->getAttribute('data-src');
        } else {
            $staff_image = $each_staff->find('tr td', 0)->find('img', 0)->getAttribute('data-src');
        }

        return Helper::imageUrlCleaner($staff_image);
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
     * Get anime/manga review.
     *
     * @return array
     */
    private function getReview()
    {
        $review = [];
        $review_area = $this->_parser->find('.js-scrollfix-bottom-rel', 0);
        $review_area = $review_area->find('table tr', 1);
        $review_area = $review_area->find('.borderDark');
        foreach ($review_area as $each_review) {
            $tmp = [];

            $top_area = $each_review->find('.spaceit', 0);
            $bottom_area = $top_area->next_sibling();
            $very_bottom_area = $bottom_area->next_sibling();

            $tmp['id'] = $this->getReviewId($very_bottom_area);
            $tmp['username'] = $this->getReviewUser($top_area);
            $tmp['image'] = $this->getReviewImage($top_area);
            $tmp['helpful'] = $this->getReviewHelpful($top_area);
            $tmp['date'] = $this->getReviewDate($top_area);
            if ($this->_type == 'anime') {
                $tmp['episode'] = $this->getReviewEpisode($top_area);
            } else {
                $tmp['chapter'] = $this->getReviewEpisode($top_area);
            }
            $tmp['score'] = $this->getReviewScore($bottom_area);
            $tmp['review'] = $this->getReviewText($bottom_area);

            $review[] = $tmp;
        }

        return $review;
    }

    /**
     * Get review user.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $very_bottom_area
     *
     * @return string
     */
    private function getReviewId($very_bottom_area)
    {
        $id = $very_bottom_area->find('a', 0)->href;
        $id = explode('?id=', $id);

        return $id[1];
    }

    /**
     * Get review id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return string
     */
    private function getReviewUser($top_area)
    {
        $user = $top_area->find('table', 0);

        return $user->find('td', 1)->find('a', 0)->plaintext;
    }

    /**
     * Get review image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return string
     */
    private function getReviewImage($top_area)
    {
        $image = $top_area->find('table', 0);
        $image = $image->find('td', 0)->find('img', 0)->src;

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get review helful.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return string
     */
    private function getReviewHelpful($top_area)
    {
        $helpful = $top_area->find('table', 0);
        $helpful = $helpful->find('td', 1)->find('strong', 0)->plaintext;

        return trim($helpful);
    }

    /**
     * Get review date.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return array
     */
    private function getReviewDate($top_area)
    {
        $date = $top_area->find('div div', 0);

        return [
            'date' => $date->plaintext,
            'time' => $date->title,
        ];
    }

    /**
     * Get review episode seen.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $top_area
     *
     * @return string
     */
    private function getReviewEpisode($top_area)
    {
        $episode = $top_area->find('div div', 1)->plaintext;
        $episode = str_replace(['episodes seen', 'chapters read'], '', $episode);

        return trim($episode);
    }

    /**
     * Get review score.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $bottom_area
     *
     * @return array
     */
    private function getReviewScore($bottom_area)
    {
        $score = [];
        $score_area = $bottom_area->find('table', 0);
        if ($score_area) {
            foreach ($score_area->find('tr') as $each_score) {
                $score_type = strtolower($each_score->find('td', 0)->plaintext);
                $score_value = $each_score->find('td', 1)->plaintext;
                $score[$score_type] = $score_value;
            }
        }

        return $score;
    }

    /**
     * Get review text.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $bottom_area
     *
     * @return string
     */
    private function getReviewText($bottom_area)
    {
        $useless_area_1 = $bottom_area->find('div', 0)->plaintext;
        $useless_area_2 = $bottom_area->find('div[id^=revhelp_output]', 0)->plaintext;
        $useless_area_3 = $bottom_area->find('a[id^=reviewToggle]', 0) ? $bottom_area->find('a[id^=reviewToggle]', 0)->plaintext : null;
        $text = str_replace([$useless_area_1, $useless_area_2, $useless_area_3], '', $bottom_area->plaintext);
        $text = str_replace('&lt;', '<', $text);

        return trim(preg_replace('/\h+/', ' ', $text));
    }

    /**
     * Get anime/manga recommendation.
     *
     * @return array
     */
    private function getRecommendation()
    {
        $recommendation = [];
        $recommendation_area = $this->_type == 'anime' ? $this->_parser->find('#anime_recommendation', 0) : $this->_parser->find('#manga_recommendation', 0);
        if ($recommendation_area) {
            foreach ($recommendation_area->find('li.btn-anime') as $each_recom) {
                $tmp = [];

                $tmp['id'] = $this->getRecomId($each_recom);
                $tmp['title'] = $this->getRecomTitle($each_recom);
                $tmp['image'] = $this->getRecomImage($each_recom);
                $tmp['user'] = $this->getRecomUser($each_recom);

                $recommendation[] = $tmp;
            }
        }

        return $recommendation;
    }

    /**
     * Get recommendation id.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getRecomId($each_recom)
    {
        $id = $each_recom->find('a', 0)->href;
        $id = explode('/', $id);
        $id = explode('-', $id[5]);
        if ($id[0] == $this->_id) {
            return $id[1];
        } else {
            return $id[0];
        }
    }

    /**
     * Get recommendation title.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getRecomTitle($each_recom)
    {
        return $each_recom->find('span', 0)->plaintext;
    }

    /**
     * Get recommendation image.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getRecomImage($each_recom)
    {
        $image = $each_recom->find('img', 0)->getAttribute('data-src');

        return Helper::imageUrlCleaner($image);
    }

    /**
     * Get recommendation user.
     *
     * @param \simplehtmldom_1_5\simple_html_dom $each_recom
     *
     * @return string
     */
    private function getRecomUser($each_recom)
    {
        $user = $each_recom->find('.users', 0)->plaintext;
        $user = str_replace(['Users', 'User'], '', $user);

        return trim($user);
    }

    /**
     * Get anime/manga all information.
     *
     * @return array
     */
    private function getAllInfo()
    {
        $data = [
            'id'        => $this->getId(),
            'cover'     => $this->getCover(),
            'title'     => $this->getTitle(),
            'title2'    => $this->getTitle2(),
            'video'     => $this->getVideo(),
            'synopsis'  => $this->getSynopsis(),
            'score'     => $this->getScore(),
            'voter'     => $this->getVoter(),
            'rank'      => $this->getRank(),
            'popularity'=> $this->getPopularity(),
            'members'   => $this->getMembers(),
            'favorite'  => $this->getFavorite(),
        ];

        $data = array_merge($data, $this->getOtherInfo());

        $data2 = [
            'related'        => $this->getRelated(),
            'character'      => $this->getCharacter(),
            'staff'          => $this->getStaff(),
            'song'           => $this->getSong(),
            'review'         => $this->getReview(),
            'recommendation' => $this->getRecommendation(),
        ];

        $data = array_merge($data, $data2);

        return $data;
    }
}
