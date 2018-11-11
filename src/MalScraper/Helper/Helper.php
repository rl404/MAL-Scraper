<?php

namespace Helper;

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
    public function response($response)
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
     * @param array $array
     *
     * @return array
     */
    private function superEncode($array)
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
     * @param string|int $type
     *
     * @return string
     */
	public static function getTopAnimeType($type)
	{
	    $converted_type = '';
	    switch ($type) {
	        case '0':
	            $converted_type = '';
	            break;
	        case '1':
	            $converted_type = 'airing';
	            break;
	        case '2':
	            $converted_type = 'upcoming';
	            break;
	        case '3':
	            $converted_type = 'tv';
	            break;
	        case '4':
	            $converted_type = 'movie';
	            break;
	        case '5':
	            $converted_type = 'ova';
	            break;
	        case '6':
	            $converted_type = 'special';
	            break;
	        case '7':
	            $converted_type = 'bypopularity';
	            break;
	        case '8':
	            $converted_type = 'favorite';
	            break;
	        default:
	            $converted_type = '';
	    }

	    return $converted_type;
	}

	 /**
     * Get top manga code.
     *
     * @param string|int $type
     *
     * @return string
     */
	public static function getTopMangaType($type)
	{
	    $converted_type = '';
	    switch ($type) {
	        case '0':
	            $converted_type = '';
	            break;
	        case '1':
	            $converted_type = 'manga';
	            break;
	        case '2':
	            $converted_type = 'novels';
	            break;
	        case '3':
	            $converted_type = 'oneshots';
	            break;
	        case '4':
	            $converted_type = 'doujin';
	            break;
	        case '5':
	            $converted_type = 'manhwa';
	            break;
	        case '6':
	            $converted_type = 'manhua';
	            break;
	        case '7':
	            $converted_type = 'bypopularity';
	            break;
	        case '8':
	            $converted_type = 'favorite';
	            break;
	        default:
	            $converted_type = '';
	    }

	    return $converted_type;
	}

	 /**
     * Get current season.
     *
     * @return string
     */
	public static function getCurrentSeason()
	{
	    $day = new DateTime();

	    //  Days of spring
	    $spring_starts = new DateTime('April 1');
	    $spring_ends = new DateTime('June 30');

	    //  Days of summer
	    $summer_starts = new DateTime('July 1');
	    $summer_ends = new DateTime('September 30');

	    //  Days of autumn
	    $autumn_starts = new DateTime('October 1');
	    $autumn_ends = new DateTime('December 31');

	    //  If $day is between the days of spring, summer, autumn, and winter
	    if ($day >= $spring_starts && $day <= $spring_ends) :
	        $season = 'spring'; elseif ($day >= $summer_starts && $day <= $summer_ends) :
	        $season = 'summer'; elseif ($day >= $autumn_starts && $day <= $autumn_ends) :
	        $season = 'fall'; else :
	        $season = 'winter';
	    endif;

	    return $season;
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
	    $str = str_replace('v.jpg', '.jpg', $str);
	    $str = str_replace('_thumb.jpg', '.jpg', $str);
	    $str = str_replace('userimages/thumbs', 'userimages', $str);
	    $str = preg_replace('/r\/\d{1,3}x\d{1,3}\//', '', $str);
	    $str = preg_replace('/\?.+/', '', $str);

	    return $str;
	}
}