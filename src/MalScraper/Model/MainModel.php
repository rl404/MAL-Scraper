<?php

namespace MalScraper\Model;

define('MAX_FILE_SIZE', 100000000);

use HtmlDomParser;

/**
 * MainModel class.
 *
 * Base model for all model class.
 */
class MainModel
{
    /**
     * MyAnimeList main URL.
     *
     * @var string
     */
    protected $_myAnimeListUrl = 'https://myanimelist.net';

    /**
     * Trimmed HtmlDomParser.
     *
     * @var \simplehtmldom_1_5\simple_html_dom
     */
    protected $_parser;

    /**
     * Area to be parsed.
     *
     * @var string
     */
    protected $_parserArea;

    /**
     * Complete MyAnimeList page URL.
     *
     * @var string
     */
    protected $_url;

    /**
     * Error response.
     *
     * @var string|int
     */
    protected $_error;

    /**
     * Get URL header.
     *
     * @param string $url URL of full MyAnimeList page
     *
     * @return int
     */
    public static function getHeader($url)
    {
        $file_headers = @get_headers($url);
        if (empty($file_headers) || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            return 404;
        }

        if (empty($file_headers) || $file_headers[0] == 'HTTP/1.1 403 Forbidden') {
            return 403;
        }

        return 200;
    }

    /**
     * Get trimmed HtmlDomParser class.
     *
     * @param string $url        URL of full MyAnimeList page
     * @param string $contentDiv Specific area to be parsed
     *
     * @return \simplehtmldom_1_5\simple_html_dom
     */
    public static function getParser($url, $contentDiv, $additionalSetting = false)
    {
        $html = HtmlDomParser::file_get_html($url)->find($contentDiv, 0);
        $html = !$additionalSetting ? $html : $html->next_sibling();
        $html = $html->outertext;
        $html = str_replace('&quot;', '\"', $html);
        $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
        $html = HtmlDomParser::str_get_html($html);

        return $html;
    }

    /**
     * Header error check.
     *
     * @param MainModel $model Any model
     *
     * @return void
     */
    public static function errorCheck($model)
    {
        $className = self::getCleanClassName($model);

        if (strpos($className, 'Search') !== false) {
            if (strlen($model->_query) < 3) {
                $model->_error = 400;
            }
        }

        if (!$model->_error) {
            $header = self::getHeader($model->_url);
            if ($header == 200) {
                if ($className != 'UserListModel') {
                    $additionalSetting = ($className == 'CharacterPeoplePictureModel');
                    $model->_parser = self::getParser(self::getAbsoluteUrl($model), $model->_parserArea, $additionalSetting);
                }
            } else {
                $model->_error = $header;
            }
        }
    }

    /**
     * Get the correct url.
     *
     * @param MainModel $model Any model
     *
     * @return string
     */
    public static function getAbsoluteUrl($model)
    {
        $className = self::getCleanClassName($model);
        $additionalUrl = '';

        switch ($className) {
            case 'CharacterStaffModel':
                $area = 'li a[href$=characters]';
                break;
            case 'StatModel':
                $area = 'li a[href$=stats]';
                $additionalUrl = '?m=all&show=1';
                break;
            case 'PictureModel':
                $area = 'li a[href$=pics]';
                break;
            case 'VideoModel':
                $area = 'li a[href$=video]';
                $additionalUrl = '?p='.$model->getPage();
                break;
            case 'EpisodeModel':
                $area = 'li a[href$=episode]';
                $additionalUrl = '?offset='. (100 * ($model->getPage() - 1));
                break;
            case 'CharacterPeoplePictureModel':
                $area = 'li a[href$=pictures]';
                break;
            default:
                return $model->_url;
        }

        $html = HtmlDomParser::file_get_html($model->_url)->find($area, 0)->href;

        if ($model->getType() == 'manga') {
            return 'https://myanimelist.net'.$html.$additionalUrl;
        }

        return $html.$additionalUrl;
    }

    /**
     * Get clean class name.
     *
     * @param MainModel $model Any model
     *
     * @return string
     */
    public static function getCleanClassName($model)
    {
        $className = get_class($model);
        $className = explode('\\', $className);

        return $className[count($className) - 1];
    }
}
