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

namespace BanditBB\Core;

if ( ! defined( 'APP_STARTED' ) ) {
    \header( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 403 forbidden' );
    exit(1);
}

class Cookies {

    protected static $instance;

    public static function i() {
        if ( ! self::$instance ) self::$instance = new self;
        return self::$instance;
    }

    public static function newCookie( $name, $value, $expires = null ) {
        \setcookie( $name, $value, $expires != null ? $expires : ( \time() * 60 * 60 ), COOKIE_PATH, COOKIE_DOMAIN );
    }

    public static function deleteCookie( $name, $phpCookie = false ) {
        \unset( $_COOKIE[$name] );
        \setcookie( $name, ''. \time() - 3600, $phpCookie ? '' : COOKIE_PATH, $phpCookie ? '' : COOKIE_DOMAIN );
    }
}