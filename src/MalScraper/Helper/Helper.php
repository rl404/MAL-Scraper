<?php

namespace MalScraper\Helper;

/**
 *	Helper class.
 */
class Helper
{
    /**
     * Convert return result into easy-to-read result.
     *
     * @param string|array $response
     *
     * @return string|array
     */
    public static function toResponse($response)
    {
        switch ($response) {
            case 400:
                return 'Search query needs at least 3 letters';
            case 403:
                return 'Private user list';
            case 404:
                return 'Page not found';
            default:
                return $response;
        }
    }

    /**
     * Convert return result into http response.
     *
     * @param string|array $response
     *
     * @return string
     */
    public static function response($response)
    {
        $result = [];
        if (is_numeric($response)) {
            header('HTTP/1.1 '.$response);
            $result['status'] = $response;
            $result['status_message'] = self::toResponse($response);
            $result['data'] = [];
        } else {
            header('HTTP/1.1 '. 200);
            $result['status'] = 200;
            $result['status_message'] = 'Success';
            $result['data'] = self::superEncode($response);
        }

        $json_response = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $json_response = str_replace('\\\\', '', $json_response);

        return $json_response;
    }

    /**
     * Convert characters to UTF-8.
     *
     * @param array|string $array
     *
     * @return array|string
     */
    private static function superEncode($array)
    {
        if (is_array($array) && !empty($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = self::superEncode($value);
                } else {
                    $array[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            }
        }

        return $array;
    }

    /**
     * Get top anime code.
     *
     * @return array
     */
    public static function getTopAnimeType()
    {
        return [
            '',
            'airing',
            'upcoming',
            'tv',
            'movie',
            'ova',
            'special',
            'bypopularity',
            'favorite',
        ];
    }

    /**
     * Get top manga code.
     *
     * @return array
     */
    public static function getTopMangaType()
    {
        return [
            '',
            'manga',
            'novel',
            'oneshots',
            'doujin',
            'manhwa',
            'manhua',
            'bypopularity',
            'favorite',
        ];
    }

    /**
     * Get current season.
     *
     * @return string
     */
    public static function getCurrentSeason()
    {
        $currentMonth = date('m');

        if ($currentMonth >= '01' && $currentMonth < '04') {
            return 'winter';
        }
        if ($currentMonth >= '04' && $currentMonth < '07') {
            return 'spring';
        }
        if ($currentMonth >= '07' && $currentMonth < '10') {
            return 'summer';
        }

        return 'autumn';
    }

    /**
     * Clean image URL.
     *
     * @param string $str
     *
     * @return string
     */
    public static function imageUrlCleaner($str)
    {
        preg_match('/(questionmark)|(qm_50)/', $str, $temp_image);
        $str = $temp_image ? '' : $str;
        $str = str_replace(['v.jpg', 't.jpg'], '.jpg', $str);
        $str = str_replace('_thumb.jpg', '.jpg', $str);
        $str = str_replace('userimages/thumbs', 'userimages', $str);
        $str = preg_replace('/r\/\d{1,3}x\d{1,3}\//', '', $str);
        $str = preg_replace('/\?.+/', '', $str);

        return $str;
    }

    /**
     * Clean video URL.
     *
     * @param string $str
     *
     * @return string
     */
    public static function videoUrlCleaner($str)
    {
        $str = preg_replace('/\?.+/', '', $str);
        $str = str_replace('embed/', 'watch?v=', $str);

        return $str;
    }
}
