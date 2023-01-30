<?php

/**
 * Bandit Bulletin Board
 * 
 * By Sam Wilcox <sam@banditbb.com>
 * https://www.banditbb.com
 * 
 * You are bound by the terms of the user-end license agreement.
 * View the user-end license agreement at:
 * https://license.banditbb.com
 */

namespace BanditBB\Helpers;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Buttons extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getButtonUsingPermissions( $button, $forumId, $topicId = 0 ) {
        switch ( $button ) {
            case \BanditBB\Types\Buttons::NEW_TOPIC:
                return self::member()->haveForumPermission( $forumId, 'postTopics' ) ? self::output()->getPartial( 'ButtonsHelper', 'Button', 'NewTopic', [ 'url' => self::seo()->seoUrl( 'post', 'topic', [ 'id' => $forumId ] ) ] ) : '';
                break;

            case \BanditBB\Types\Buttons::POST_REPLY:
                return self::member()->haveForumPermission( $forumId, 'postReply' ) ? self::output()->getPartial( 'ButtonsHelper', 'Button', 'PostReply' ) : '';
                break;
        }
    }
}