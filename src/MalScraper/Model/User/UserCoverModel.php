<?php

namespace MalScraper\Model\User;

use MalScraper\Model\User\UserListModel as UserList;

/**
 * UserCoverModel class.
 */
class UserCoverModel
{
    /**
     * Username.
     *
     * @var string
     */
    private $_user;

    /**
     * Either anime or manga.
     *
     * @var string
     */
    private $_type;

    /**
     * CSS style.
     *
     * @var string
     */
    private $_style;

    /**
     * Default constructor.
     *
     * @param string $user
     * @param string $type
     * @param string $style
     * @param string $parserArea
     *
     * @return void
     */
    public function __construct($user, $type, $style)
    {
        $this->_user = $user;
        $this->_type = $type;
        if ($style) {
            $this->_style = $style;
        } else {
            $this->_style = "tr:hover .animetitle[href*='/{id}/']:before{background-image:url({url})}";
        }
    }

    /**
     * Get user cover.
     *
     * @return string
     */
    public function getAllInfo()
    {
        $list = (new UserList($this->_user, $this->_type, 7))->getAllInfo();

        $cover = '';
        foreach ($list as $c) {
            if ($this->_type == 'anime') {
                $temp = str_replace(['{id}', '{url}'], [$c['anime_id'], $c['anime_image_path']], $this->_style);
            } else {
                $temp = str_replace(['{id}', '{url}'], [$c['manga_id'], $c['manga_image_path']], $this->_style);
            }
            $cover .= $temp."\n";
        }

        return $cover;
    }
}
