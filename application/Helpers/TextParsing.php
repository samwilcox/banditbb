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

class TextParsing extends \BanditBB\Application {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function getPortionOfString( $string, $totalCharacters = 50 ) {
        if ( \strlen( $string ) <= $totalCharacters ) {
            return $string;
        }

        $newStr = \substr( $string, 0, $totalCharacters );

        if ( \substr( $newStr, -1, 1 ) != ' ' ) {
            $newStr = \substr( $newStr, 0, \strrpos( $newStr, ' ' ) );
        }

        return $newStr;
    }

    public static function getValueBeforeSlash( $haystack ) {
        return \substr( $haystack, 0, \strpos( $haystack, '-' ) );
    }

    public static function bbTagReplacement( $message, $forumId = false, $force = false ) {
        return $message;
    }

    public static function wordCensoring( $source, $forumId = false, $force = false ) {
        return $source;
    }
}